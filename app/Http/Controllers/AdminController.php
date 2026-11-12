<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ── Users ──────────────────────────────────────────────────────────────

    public function users()
    {
        $users = User::with('role')
            ->withCount('rooms as room_count')
            ->orderBy('role_id')->orderBy('fullname')
            ->get();
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    public function userSave(Request $request)
    {
        $id = (int) $request->input('id', 0);
        $un = trim($request->input('username', ''));
        $fn = trim($request->input('fullname', ''));
        $ph = trim($request->input('phone_number', ''));
        $role = (int) $request->input('role_id', 0);
        $pw = (string) $request->input('password', '');

        if (! preg_match('/^[A-Za-z0-9_.]{3,50}$/', $un) || $fn === '' || strlen($this->normalizePhone($ph)) < 9 || ! in_array($role, [Role::ADMIN, Role::PIC, Role::GURU, Role::WAKA], true)) {
            return back()->withErrors(['username' => 'Lengkapi data: username 3–50 karakter, nama, nomor HP, dan role.'])->withInput();
        }
        if (($id === 0 || $pw !== '') && strlen($pw) < 6) {
            return back()->withErrors(['password' => 'Password minimal 6 karakter.'])->withInput();
        }

        try {
            if ($id === 0) {
                User::create([
                    'username' => $un,
                    'email' => $un.'@rapi-sekolah.local',
                    'password' => Hash::make($pw),
                    'fullname' => $fn,
                    'phone_number' => $ph,
                    'role_id' => $role,
                ]);
            } else {
                $user = User::findOrFail($id);
                if ($id === $request->user()->id && $role !== Role::ADMIN) {
                    return back()->withErrors(['role_id' => 'Anda tidak dapat menurunkan role akun sendiri.']);
                }
                $user->update(['username' => $un, 'fullname' => $fn, 'phone_number' => $ph, 'role_id' => $role]);
                if ($pw !== '') {
                    $user->update(['password' => Hash::make($pw)]);
                }
                if ($role !== Role::PIC) {
                    Room::where('pic_id', $id)->update(['pic_id' => null]);
                }
            }
        } catch (QueryException $ex) {
            if ($ex->getCode() === '23000') {
                return back()->withErrors(['username' => 'Username sudah dipakai.'])->withInput();
            }
            throw $ex;
        }

        return back()->with('success', 'Data pengguna disimpan.');
    }

    public function userDelete(Request $request)
    {
        $id = (int) $request->input('id', 0);
        if ($id === $request->user()->id) {
            return back()->withErrors(['id' => 'Akun sendiri tidak dapat dihapus.']);
        }
        try {
            User::findOrFail($id)->delete();
        } catch (QueryException $ex) {
            if ($ex->getCode() === '23000') {
                return back()->withErrors(['id' => 'Pengguna masih memiliki agenda/pengaduan. Ubah saja datanya.']);
            }
            throw $ex;
        }

        return back()->with('success', 'Pengguna dihapus.');
    }

    public function userTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_pengguna_rapi.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens characters properly
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['username', 'fullname', 'phone_number', 'role', 'password']);
            fputcsv($handle, ['guru_ahmad', 'Ahmad Fauzi S.Kom', '081234567890', 'Guru', 'guru123']);
            fputcsv($handle, ['pic_hendra', 'Hendra Gunawan S.T', '0895611029000', 'PIC Ruang', 'pic123']);
            fputcsv($handle, ['osis_ketua', 'Pengurus OSIS', '087812345678', 'Organisasi', 'osis123']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function userImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:3072',
        ], [
            'file.required' => 'Pilih file template CSV/Excel terlebih dahulu.',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['file' => 'Gagal membuka file template.']);
        }

        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->withErrors(['file' => 'File CSV kosong atau tidak memiliki baris judul kolom.']);
        }

        $colIndex = [];
        foreach ($header as $idx => $col) {
            $colIndex[strtolower(trim($col))] = $idx;
        }

        $imported = 0;
        $updated = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }

            $username = trim($row[$colIndex['username'] ?? 0] ?? '');
            $fullname = trim($row[$colIndex['fullname'] ?? 1] ?? '');
            $phone = trim($row[$colIndex['phone_number'] ?? 2] ?? '');
            $roleStr = strtolower(trim($row[$colIndex['role'] ?? 3] ?? 'guru'));
            $password = trim($row[$colIndex['password'] ?? 4] ?? 'password123');

            if ($username === '' || $fullname === '') {
                continue;
            }

            $roleId = match (true) {
                str_contains($roleStr, 'admin') => Role::ADMIN,
                str_contains($roleStr, 'pic') => Role::PIC,
                str_contains($roleStr, 'waka') => Role::WAKA,
                str_contains($roleStr, 'organisasi') || str_contains($roleStr, 'osis') || str_contains($roleStr, 'pramuka') => Role::ORGANISASI,
                default => Role::GURU,
            };

            $existing = User::where('username', $username)->first();
            if ($existing) {
                $existing->update([
                    'fullname' => $fullname,
                    'phone_number' => $phone ?: $existing->phone_number,
                    'role_id' => $roleId,
                ]);
                if ($password !== '' && $password !== 'password123') {
                    $existing->update(['password' => Hash::make($password)]);
                }
                $updated++;
            } else {
                User::create([
                    'username' => $username,
                    'email' => $username.'@rapi-sekolah.local',
                    'fullname' => $fullname,
                    'phone_number' => $phone ?: '0895611029000',
                    'role_id' => $roleId,
                    'password' => Hash::make($password ?: 'password123'),
                ]);
                $imported++;
            }
        }

        fclose($handle);

        return back()->with('success', "Proses impor selesai: {$imported} pengguna baru ditambahkan, {$updated} diperbarui.");
    }

    // ── Rooms ──────────────────────────────────────────────────────────────

    public function rooms()
    {
        $rooms = Room::with('pic')->orderBy('room_name')->get();
        $pics = User::where('role_id', Role::PIC)->orderBy('fullname')->get();

        return view('admin.rooms', compact('rooms', 'pics'));
    }

    public function roomSave(Request $request)
    {
        $id = (int) $request->input('id', 0);
        $name = trim($request->input('room_name', ''));
        $cap = (int) $request->input('capacity', 0);
        $picId = (int) $request->input('pic_id', 0) ?: null;
        $active = $request->boolean('is_active');

        if ($name === '') {
            return back()->withErrors(['room_name' => 'Nama ruang wajib diisi.'])->withInput();
        }
        if ($picId && ! User::where('id', $picId)->where('role_id', Role::PIC)->exists()) {
            return back()->withErrors(['pic_id' => 'PIC harus pengguna ber-role PIC Ruang.'])->withInput();
        }

        try {
            if ($id === 0) {
                Room::create(['room_name' => $name, 'capacity' => $cap, 'location' => $request->input('location'), 'pic_id' => $picId, 'is_active' => $active]);
            } else {
                Room::findOrFail($id)->update(['room_name' => $name, 'capacity' => $cap, 'location' => $request->input('location'), 'pic_id' => $picId, 'is_active' => $active]);
            }
        } catch (QueryException $ex) {
            if ($ex->getCode() === '23000') {
                return back()->withErrors(['room_name' => 'Nama ruang sudah ada.'])->withInput();
            }
            throw $ex;
        }

        return back()->with('success', 'Data ruang disimpan.');
    }

    public function roomDelete(int $id)
    {
        $room = Room::findOrFail($id);
        $roomName = $room->room_name;

        DB::transaction(function () use ($room) {
            // Hapus semua foto fisik dari pengaduan ruangan ini di storage
            foreach ($room->complaints as $c) {
                if ($c->photo_path && Storage::disk('public')->exists($c->photo_path)) {
                    Storage::disk('public')->delete($c->photo_path);
                }
                if ($c->completion_photo_path && Storage::disk('public')->exists($c->completion_photo_path)) {
                    Storage::disk('public')->delete($c->completion_photo_path);
                }
            }

            // Bersihkan secara tuntas seluruh acara/peminjaman (baik sebelum maupun sesudah) dan pengaduan
            $room->events()->delete();
            $room->complaints()->delete();

            // Hapus ruangan permanen
            $room->delete();
        });

        return back()->with('success', "Ruangan '{$roomName}' beserta seluruh riwayat kegiatan dan pengaduannya berhasil dihapus bersih (clear).");
    }

    private function normalizePhone(string $p): string
    {
        $p = preg_replace('/\D/', '', $p);
        if (str_starts_with($p, '08')) {
            $p = '628'.substr($p, 2);
        }

        return $p;
    }
}
