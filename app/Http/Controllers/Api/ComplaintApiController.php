<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplaintApiController extends Controller
{
    private const REPAIR_STATUSES = ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'];

    /**
     * GET /api/complaints
     * Query: ?status=&room_id=
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Complaint::with(['room:id,room_name,location', 'reporter:id,fullname,username', 'handler:id,fullname,username'])
            ->orderByRaw("CASE status WHEN 'Diajukan' THEN 0 WHEN 'Diterima' THEN 1 ELSE 2 END")
            ->orderByDesc('reported_at')
            ->limit(200);

        // Filter by role
        if ($user->isPic()) {
            $query->whereHas('room', fn ($q) => $q->where('pic_id', $user->id));
        }
        if ($user->isGuru()) {
            $query->where('reporter_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('status_repair')) {
            $query->where('status_repair', $request->status_repair);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', (int) $request->room_id);
        }

        return response()->json([
            'data' => $query->get()->map(fn ($c) => $this->complaintResource($c)),
        ]);
    }

    /**
     * GET /api/complaints/{id}
     */
    public function show(int $id, Request $request)
    {
        $complaint = Complaint::with(['room', 'reporter', 'handler'])->findOrFail($id);
        $user = $request->user();

        if ($user->isGuru() && $complaint->reporter_id !== $user->id) {
            abort(403, 'Bukan laporan Anda.');
        }

        return response()->json(['data' => $this->complaintResource($complaint)]);
    }

    /**
     * POST /api/complaints
     * Body: multipart/form-data  { room_id, description, photo? (file) }
     * atau JSON { room_id, description, camera_photo_data? (base64) }
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'description' => 'required|string|min:10',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:5120',
            'camera_photo_data' => 'nullable|string',
        ], [
            'photo.mimes' => 'Format file bukti kerusakan harus berupa foto (JPG, JPEG, PNG, atau WEBP). Selain foto tidak diizinkan.',
            'photo.mimetypes' => 'Format file bukti kerusakan harus berupa foto (JPG, JPEG, PNG, atau WEBP). Berkas dokumen atau HTML ditolak.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $room = Room::where('id', $request->room_id)->where('is_active', 1)->first();
        if (! $room) {
            return response()->json(['message' => 'Ruang tidak valid.'], 422);
        }

        $photoPath = null;
        try {
            if ($request->filled('camera_photo_data')) {
                $data = (string) $request->camera_photo_data;
                if (preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/s', $data, $matches)) {
                    $raw = base64_decode($matches[2], true);
                    $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
                    if ($raw && strlen($raw) > 0) {
                        $imageInfo = @getimagesizefromstring($raw);
                        if ($imageInfo !== false && in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
                            $photoPath = 'complaints/livecam_'.uniqid().'.'.$ext;
                            Storage::disk('public')->put($photoPath, $raw);
                        } else {
                            return response()->json(['message' => 'Berkas bukan gambar valid. File HTML/dokumen ditolak.'], 422);
                        }
                    }
                }
            } elseif ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $file = $request->file('photo');
                $sample = @file_get_contents($file->getRealPath(), false, null, 0, 1024);
                if ($sample && preg_match('/<html|<!doctype|<script|<body|<\?php/i', $sample)) {
                    return response()->json(['message' => 'Berkas yang diunggah bukan foto valid. File HTML atau dokumen ditolak.'], 422);
                }
                $photoPath = $file->store('complaints', 'public');
            }
        } catch (\Throwable $e) {
            Log::error('API complaint upload error: '.$e->getMessage());
        }

        $complaint = Complaint::create([
            'room_id' => $request->room_id,
            'reporter_id' => $request->user()->id,
            'description' => $request->description,
            'photo_path' => $photoPath,
            'status' => 'Diterima',
        ]);

        // Notify admin & waka
        User::whereIn('role_id', [Role::ADMIN, Role::WAKA])->each(function ($staff) use ($complaint, $room) {
            Notification::create([
                'user_id' => $staff->id,
                'message' => "Pengaduan baru di {$room->room_name}: ".mb_strimwidth($complaint->description, 0, 60, '…'),
            ]);
        });

        if ($room->pic_id) {
            Notification::create([
                'user_id' => $room->pic_id,
                'message' => "Pengaduan baru di {$room->room_name}: ".mb_strimwidth($complaint->description, 0, 90, '…'),
            ]);
        }

        return response()->json([
            'data' => $this->complaintResource($complaint->load(['room', 'reporter'])),
            'message' => 'Pengaduan kerusakan terkirim ke PIC ruang.',
        ], 201);
    }

    /**
     * PATCH /api/complaints/{id}/status  [PIC only]
     * Body: { status_repair, repair_note? }
     */
    public function updateStatus(Request $request, int $id)
    {
        $user = $request->user();
        if (! $user->isPic()) {
            abort(403, 'Hanya PIC ruang yang dapat mengubah status perbaikan.');
        }

        $request->validate([
            'status_repair' => 'required|in:Belum Diperbaiki,Menunggu Spare Part,Sudah Diperbaiki',
            'repair_note' => 'nullable|string|max:500',
            'completion_photo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'completion_camera_photo_data' => 'nullable|string',
        ], [
            'completion_photo.mimes' => 'Format file bukti perbaikan harus berupa foto (JPG, JPEG, PNG, atau WEBP). Selain foto tidak diizinkan.',
            'completion_photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $complaint = Complaint::where('id', $id)
            ->whereHas('room', fn ($q) => $q->where('pic_id', $user->id))
            ->with(['room', 'reporter'])
            ->firstOrFail();

        $completionPhotoPath = $complaint->completion_photo_path;

        if ($request->filled('completion_camera_photo_data')) {
            $data = (string) $request->input('completion_camera_photo_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $ext = strtolower($type[1]);
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $decoded = base64_decode($data);
                    if ($decoded !== false) {
                        $ext = ($ext === 'jpeg') ? 'jpg' : $ext;
                        $filename = 'complaints/repair_livecam_'.uniqid().'.'.$ext;
                        Storage::disk('public')->put($filename, $decoded);
                        if ($completionPhotoPath && Storage::disk('public')->exists($completionPhotoPath)) {
                            Storage::disk('public')->delete($completionPhotoPath);
                        }
                        $completionPhotoPath = $filename;
                    }
                }
            }
        } elseif ($request->hasFile('completion_photo') && $request->file('completion_photo')->isValid()) {
            $file = $request->file('completion_photo');
            if ($completionPhotoPath && Storage::disk('public')->exists($completionPhotoPath)) {
                Storage::disk('public')->delete($completionPhotoPath);
            }
            $completionPhotoPath = $file->store('complaints', 'public');
        }

        if ($request->status_repair === 'Sudah Diperbaiki' && empty($completionPhotoPath)) {
            return response()->json([
                'message' => 'Wajib melampirkan foto bukti fisik perbaikan untuk status Sudah Diperbaiki.',
            ], 422);
        }

        $complaint->update([
            'status' => 'Diterima',
            'status_repair' => $request->status_repair,
            'handler_id' => $user->id,
            'repair_note' => $request->repair_note,
            'completion_photo_path' => $completionPhotoPath,
            'completed_at' => ($request->status_repair === 'Sudah Diperbaiki') ? ($complaint->completed_at ?? now()) : null,
        ]);

        if ($complaint->reporter_id) {
            Notification::create([
                'user_id' => $complaint->reporter_id,
                'message' => "Pengaduan di {$complaint->room->room_name} kini berstatus: {$request->status_repair}.".($request->status_repair === 'Sudah Diperbaiki' ? ' Foto bukti perbaikan telah diunggah oleh PIC.' : ''),
            ]);
        }

        return response()->json([
            'data' => $this->complaintResource($complaint->fresh(['room', 'reporter', 'handler'])),
            'message' => "Status perbaikan diperbarui: {$request->status_repair}.",
        ]);
    }

    /**
     * DELETE /api/complaints/{id}
     */
    public function destroy(int $id, Request $request)
    {
        $complaint = Complaint::findOrFail($id);
        $user = $request->user();

        if ($user->isGuru() && $complaint->reporter_id !== $user->id) {
            abort(403, 'Anda hanya dapat menghapus laporan milik sendiri.');
        }

        if ($complaint->photo_path) {
            Storage::disk('public')->delete($complaint->photo_path);
        }
        if ($complaint->completion_photo_path) {
            Storage::disk('public')->delete($complaint->completion_photo_path);
        }
        $complaint->delete();

        return response()->json(['message' => 'Pengaduan berhasil dihapus.']);
    }

    private function complaintResource(Complaint $c): array
    {
        return [
            'id' => $c->id,
            'description' => $c->description,
            'status' => $c->status,
            'status_repair' => $c->status_repair,
            'repair_note' => $c->repair_note,
            'reject_reason' => $c->reject_reason,
            'photo_url' => $c->photo_path ? url('storage/'.$c->photo_path) : null,
            'completion_photo_url' => $c->completion_photo_path ? url('storage/'.$c->completion_photo_path) : null,
            'completed_at' => $c->completed_at?->toISOString(),
            'room' => $c->room ? ['id' => $c->room->id, 'room_name' => $c->room->room_name] : null,
            'reporter' => $c->reporter ? ['id' => $c->reporter->id, 'fullname' => $c->reporter->fullname] : null,
            'handler' => $c->handler ? ['id' => $c->handler->id,  'fullname' => $c->handler->fullname] : null,
            'reported_at' => $c->reported_at?->toISOString(),
            'updated_at' => $c->updated_at?->toISOString(),
        ];
    }
}
