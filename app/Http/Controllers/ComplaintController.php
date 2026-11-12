<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComplaintController extends Controller
{
    private const STATUSES = ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'];

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Complaint::with(['room', 'reporter', 'room.pic'])
            ->orderByRaw("CASE status WHEN 'Diajukan' THEN 0 WHEN 'Diterima' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE status_repair WHEN 'Belum Diperbaiki' THEN 0 WHEN 'Menunggu Spare Part' THEN 1 ELSE 2 END")
            ->orderByDesc('reported_at');

        if ($user->isPic()) {
            $query->whereHas('room', fn ($q) => $q->where('pic_id', $user->id));
        }
        if ($user->isGuru()) {
            $query->where('reporter_id', $user->id);
        }

        // Total counter untuk banner ringkasan
        $cntTotal = (clone $query)->count();
        $cntPending = (clone $query)->where('status_repair', 'Belum Diperbaiki')->count();
        $cntProcess = (clone $query)->where('status_repair', 'Menunggu Spare Part')->count();
        $cntDone = (clone $query)->where('status_repair', 'Sudah Diperbaiki')->count();

        // Pagination 5 laporan per halaman sesuai revisi SRS
        $complaints = $query->paginate(5)->through(function ($c) use ($user) {
            $age = now()->diffInSeconds($c->reported_at);
            $c->overdue = $c->status_repair === 'Belum Diperbaiki' && $age > 48 * 3600;
            $c->age_h = (int) floor($age / 3600);
            $c->wa = null;
            if (($user->isAdmin() || $user->isWaka()) && $c->room->pic && $c->room->pic->phone_number) {
                $phone = preg_replace('/\D/', '', $c->room->pic->phone_number);
                if (str_starts_with($phone, '08')) {
                    $phone = '628'.substr($phone, 2);
                }
                $text = urlencode("Halo {$c->room->pic->fullname}, pengaduan kerusakan di {$c->room->room_name} (\"".mb_strimwidth($c->description, 0, 80, '…')."\") dilaporkan dan masih berstatus Belum Diperbaiki selama {$c->age_h} jam. Mohon segera ditindaklanjuti.");
                $c->wa = "https://wa.me/{$phone}?text={$text}";
            }

            return $c;
        });

        return view('complaints.list', [
            'complaints' => $complaints,
            'cntTotal' => $cntTotal,
            'cntPending' => $cntPending,
            'cntProcess' => $cntProcess,
            'cntDone' => $cntDone,
            'statuses' => self::STATUSES,
            'limitH' => 48,
        ]);
    }

    public function create()
    {
        $rooms = Room::where('is_active', 1)->orderBy('room_name')->get();

        return view('complaints.form', compact('rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'description' => 'required|string|min:10',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:5120',
        ], [
            'photo.mimes' => 'Format file bukti kerusakan harus berupa foto (JPG, JPEG, PNG, atau WEBP). Selain foto tidak diizinkan.',
            'photo.mimetypes' => 'Format file bukti kerusakan harus berupa foto (JPG, JPEG, PNG, atau WEBP). Berkas dokumen atau HTML ditolak.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $roomId = (int) $request->input('room_id', 0);
        $desc = trim($request->input('description', ''));

        $room = Room::where('id', $roomId)->where('is_active', 1)->first();
        if (! $room) {
            return back()->withErrors(['room_id' => 'Pilih ruang yang valid.'])->withInput();
        }

        $photoPath = null;
        try {
            // Check for real-time camera snapshot (base64)
            if ($request->filled('camera_photo_data')) {
                $data = (string) $request->input('camera_photo_data');
                if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/s', $data, $matches)) {
                    $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
                    $decoded = base64_decode($matches[2], true);
                    if ($decoded !== false && strlen($decoded) > 0) {
                        // Verifikasi konten biner benar-benar file gambar valid (anti HTML / shell script)
                        $imageInfo = @getimagesizefromstring($decoded);
                        if ($imageInfo !== false && in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
                            $filename = 'complaints/livecam_'.uniqid().'.'.$ext;
                            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                            $photoPath = $filename;
                        } else {
                            return back()->withErrors(['photo' => 'Berkas yang diunggah terdeteksi bukan gambar valid. File HTML/dokumen tidak diizinkan.'])->withInput();
                        }
                    }
                } else {
                    return back()->withErrors(['photo' => 'Format foto kamera tidak valid. Gunakan format foto (JPG, PNG, WEBP).'])->withInput();
                }
            }

            // Fallback to standard file upload if no camera snapshot
            if (! $photoPath && $request->hasFile('photo') && $request->file('photo')->isValid()) {
                $file = $request->file('photo');
                // Tolak jika terdeteksi file HTML / script terselubung
                $sample = @file_get_contents($file->getRealPath(), false, null, 0, 1024);
                if ($sample && preg_match('/<html|<!doctype|<script|<body|<\?php/i', $sample)) {
                    return back()->withErrors(['photo' => 'Berkas yang diunggah bukan foto valid. File HTML atau dokumen ditolak.'])->withInput();
                }
                $photoPath = $file->store('complaints', 'public');
            }
        } catch (\Throwable $e) {
            Log::error('Upload error: '.$e->getMessage());
        }

        $complaint = Complaint::create([
            'room_id' => $roomId,
            'reporter_id' => $request->user()->id,
            'description' => $desc,
            'photo_path' => $photoPath,
            'status' => 'Diterima',
        ]);

        // Notify admins & waka about new complaint
        $staffToNotify = User::whereIn('role_id', [Role::ADMIN, Role::WAKA])->get();
        foreach ($staffToNotify as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'message' => "Pengaduan baru di {$room->room_name} dari {$request->user()->fullname}: ".mb_strimwidth($desc, 0, 60, '…'),
            ]);
        }

        if ($room->pic_id) {
            Notification::create([
                'user_id' => $room->pic_id,
                'message' => 'Pengaduan baru di '.$room->room_name.': '.mb_strimwidth($desc, 0, 90, '…'),
            ]);

            // Kirim WhatsApp otomatis ke PIC ruangan
            $pic = $room->pic;
            if ($pic && $pic->phone_number) {
                $msg = "*RAPI-Sekolah* — Pengaduan Kerusakan Baru\n\n"
                    ."Halo *{$pic->fullname}*,\n"
                    ."Terdapat laporan kerusakan baru di ruangan yang Anda tangani:\n\n"
                    ."📍 *Ruang:* {$room->room_name}\n"
                    ."👤 *Pelapor:* {$request->user()->fullname}\n"
                    .'📝 *Kerusakan:* '.mb_strimwidth($desc, 0, 100, '…')."\n\n"
                    ."Silakan login ke sistem untuk menangani pengaduan ini:\n"
                    .'https://rapi-sekolah.web.id/complaints';
                WhatsAppService::sendMessage($pic->phone_number, $msg);
            }
        }

        return redirect()->route('complaints.index')->with('success', 'Pengaduan kerusakan terkirim ke PIC ruang.');
    }

    /** Status pengaduan: HANYA PIC yang dapat mengubah status */
    public function status(Request $request)
    {
        $user = $request->user();
        if (! $user->isPic()) {
            abort(403, 'Hanya PIC ruang yang berhak mengubah status pengaduan.');
        }

        $new = $request->input('status_repair', '');
        if (! in_array($new, self::STATUSES, true)) {
            return back()->withErrors(['status_repair' => 'Status tidak valid.']);
        }

        $complaint = Complaint::where('id', (int) $request->input('id'))
            ->whereHas('room', fn ($q) => $q->where('pic_id', $user->id))
            ->with(['room', 'reporter'])
            ->first();

        if (! $complaint) {
            abort(403, 'Ruangan ini bukan tanggung jawab Anda sebagai PIC.');
        }

        // Validasi format file foto bukti selesai: Foto only
        $request->validate([
            'completion_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:5120',
        ], [
            'completion_photo.mimes' => 'Format bukti perbaikan harus berupa foto (JPG, JPEG, PNG, atau WEBP). Selain foto tidak diizinkan.',
            'completion_photo.mimetypes' => 'Format bukti perbaikan harus berupa foto (JPG, JPEG, PNG, atau WEBP). Berkas dokumen atau HTML ditolak.',
            'completion_photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $completionPhotoPath = $complaint->completion_photo_path;

        // Process uploaded completion photo (file or camera base64)
        try {
            if ($request->filled('completion_camera_photo_data')) {
                $data = (string) $request->input('completion_camera_photo_data');
                if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/s', $data, $matches)) {
                    $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
                    $decoded = base64_decode($matches[2], true);
                    if ($decoded !== false && strlen($decoded) > 0) {
                        $imageInfo = @getimagesizefromstring($decoded);
                        if ($imageInfo !== false && in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
                            $filename = 'complaints/repair_livecam_'.uniqid().'.'.$ext;
                            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                            if ($completionPhotoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($completionPhotoPath)) {
                                \Illuminate\Support\Facades\Storage::disk('public')->delete($completionPhotoPath);
                            }
                            $completionPhotoPath = $filename;
                        } else {
                            return back()->withErrors(['completion_photo' => 'Berkas bukti perbaikan terdeteksi bukan gambar valid. File HTML/dokumen ditolak.'])->withInput();
                        }
                    }
                } else {
                    return back()->withErrors(['completion_photo' => 'Format foto kamera bukti perbaikan tidak valid. Gunakan format foto (JPG, PNG, WEBP).'])->withInput();
                }
            } elseif ($request->hasFile('completion_photo') && $request->file('completion_photo')->isValid()) {
                $file = $request->file('completion_photo');
                // Tolak jika terdeteksi file HTML / script terselubung
                $sample = @file_get_contents($file->getRealPath(), false, null, 0, 1024);
                if ($sample && preg_match('/<html|<!doctype|<script|<body|<\?php/i', $sample)) {
                    return back()->withErrors(['completion_photo' => 'Berkas yang diunggah bukan foto valid. File HTML atau dokumen ditolak.'])->withInput();
                }
                if ($completionPhotoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($completionPhotoPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($completionPhotoPath);
                }
                $completionPhotoPath = $file->store('complaints', 'public');
            }
        } catch (\Throwable $e) {
            Log::error('Upload completion photo error: '.$e->getMessage());
        }

        // Rule: Jika status "Sudah Diperbaiki", WAJIB melampirkan foto bukti fisik perbaikan
        if ($new === 'Sudah Diperbaiki' && empty($completionPhotoPath)) {
            return back()->withErrors(['completion_photo' => 'Wajib melampirkan foto bukti fisik perbaikan untuk status Sudah Diperbaiki.'])->withInput();
        }

        $complaint->update([
            'status' => 'Diterima',
            'status_repair' => $new,
            'handler_id' => $user->id,
            'repair_note' => $request->input('repair_note') ?: null,
            'completion_photo_path' => $completionPhotoPath,
            'completed_at' => ($new === 'Sudah Diperbaiki') ? ($complaint->completed_at ?? now()) : null,
        ]);

        if ($complaint->reporter_id) {
            Notification::create([
                'user_id' => $complaint->reporter_id,
                'message' => "Pengaduan kerusakan di {$complaint->room->room_name} kini berstatus: {$new}.".($new === 'Sudah Diperbaiki' ? ' Foto bukti perbaikan telah diunggah oleh PIC.' : ''),
            ]);
        }

        return back()->with('success', "Status perbaikan pengaduan berhasil diperbarui oleh PIC: {$new}.".($new === 'Sudah Diperbaiki' ? ' Bukti foto perbaikan telah tersimpan.' : ''));
    }

    public function delete(Request $request)
    {
        $user = $request->user();
        $complaint = Complaint::findOrFail((int) $request->input('id'));

        if ($user->isGuru() && $complaint->reporter_id !== $user->id) {
            return back()->withErrors(['id' => 'Anda hanya dapat menghapus laporan milik sendiri.']);
        }

        if ($complaint->photo_path) {
            \Storage::disk('public')->delete($complaint->photo_path);
        }
        if ($complaint->completion_photo_path) {
            \Storage::disk('public')->delete($complaint->completion_photo_path);
        }

        $complaint->delete();

        return back()->with('success', 'Pengaduan berhasil dihapus.');
    }
}
