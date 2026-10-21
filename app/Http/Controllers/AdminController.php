<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Room;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

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
        $id   = (int)$request->input('id', 0);
        $un   = trim($request->input('username', ''));
        $fn   = trim($request->input('fullname', ''));
        $ph   = trim($request->input('phone_number', ''));
        $role = (int)$request->input('role_id', 0);
        $pw   = (string)$request->input('password', '');

        if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $un) || $fn === '' || strlen($this->normalizePhone($ph)) < 9 || !in_array($role, [Role::ADMIN, Role::PIC, Role::GURU, Role::WAKA], true)) {
            return back()->withErrors(['username' => 'Lengkapi data: username 3–50 karakter, nama, nomor HP, dan role.'])->withInput();
        }
        if (($id === 0 || $pw !== '') && strlen($pw) < 6) {
            return back()->withErrors(['password' => 'Password minimal 6 karakter.'])->withInput();
        }

        try {
            if ($id === 0) {
                User::create([
                    'username'     => $un,
                    'email'        => $un . '@rapi-sekolah.local',
                    'password'     => Hash::make($pw),
                    'fullname'     => $fn,
                    'phone_number' => $ph,
                    'role_id'      => $role,
                ]);
            } else {
                $user = User::findOrFail($id);
                if ($id === $request->user()->id && $role !== Role::ADMIN) {
                    return back()->withErrors(['role_id' => 'Anda tidak dapat menurunkan role akun sendiri.']);
                }
                $user->update(['username' => $un, 'fullname' => $fn, 'phone_number' => $ph, 'role_id' => $role]);
                if ($pw !== '') $user->update(['password' => Hash::make($pw)]);
                if ($role !== Role::PIC) Room::where('pic_id', $id)->update(['pic_id' => null]);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            if ($ex->getCode() === '23000') return back()->withErrors(['username' => 'Username sudah dipakai.'])->withInput();
            throw $ex;
        }

        return back()->with('success', 'Data pengguna disimpan.');
    }

    public function userDelete(Request $request)
    {
        $id = (int)$request->input('id', 0);
        if ($id === $request->user()->id) {
            return back()->withErrors(['id' => 'Akun sendiri tidak dapat dihapus.']);
        }
        try {
            User::findOrFail($id)->delete();
        } catch (\Illuminate\Database\QueryException $ex) {
            if ($ex->getCode() === '23000') return back()->withErrors(['id' => 'Pengguna masih memiliki agenda/pengaduan. Ubah saja datanya.']);
            throw $ex;
        }
        return back()->with('success', 'Pengguna dihapus.');
    }

    // ── Rooms ──────────────────────────────────────────────────────────────

    public function rooms()
    {
        $rooms = Room::with('pic')->orderBy('room_name')->get();
        $pics  = User::where('role_id', Role::PIC)->orderBy('fullname')->get();
        return view('admin.rooms', compact('rooms', 'pics'));
    }

    public function roomSave(Request $request)
    {
        $id     = (int)$request->input('id', 0);
        $name   = trim($request->input('room_name', ''));
        $cap    = (int)$request->input('capacity', 0);
        $picId  = (int)$request->input('pic_id', 0) ?: null;
        $active = $request->boolean('is_active');

        if ($name === '') return back()->withErrors(['room_name' => 'Nama ruang wajib diisi.'])->withInput();
        if ($picId && !User::where('id', $picId)->where('role_id', Role::PIC)->exists()) {
            return back()->withErrors(['pic_id' => 'PIC harus pengguna ber-role PIC Ruang.'])->withInput();
        }

        try {
            if ($id === 0) {
                Room::create(['room_name' => $name, 'capacity' => $cap, 'location' => $request->input('location'), 'pic_id' => $picId, 'is_active' => $active]);
            } else {
                Room::findOrFail($id)->update(['room_name' => $name, 'capacity' => $cap, 'location' => $request->input('location'), 'pic_id' => $picId, 'is_active' => $active]);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            if ($ex->getCode() === '23000') return back()->withErrors(['room_name' => 'Nama ruang sudah ada.'])->withInput();
            throw $ex;
        }

        return back()->with('success', 'Data ruang disimpan.');
    }

    private function normalizePhone(string $p): string
    {
        $p = preg_replace('/\D/', '', $p);
        if (str_starts_with($p, '08')) $p = '628' . substr($p, 2);
        return $p;
    }
}
