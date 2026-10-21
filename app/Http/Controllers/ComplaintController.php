<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\Room;
use App\Models\Notification;
use App\Models\Role;

class ComplaintController extends Controller
{
    private const STATUSES = ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'];

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Complaint::with(['room', 'reporter', 'room.pic'])
            ->orderByRaw("CASE status WHEN 'Diajukan' THEN 0 WHEN 'Diterima' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE status_repair WHEN 'Belum Diperbaiki' THEN 0 WHEN 'Menunggu Spare Part' THEN 1 ELSE 2 END")
            ->orderByDesc('reported_at')
            ->limit(200);

        if ($user->isPic())  $query->whereHas('room', fn($q) => $q->where('pic_id', $user->id));
        if ($user->isGuru()) $query->where('reporter_id', $user->id);

        $complaints = $query->get()->map(function ($c) use ($user) {
            $age = now()->diffInSeconds($c->reported_at);
            $c->overdue = $c->status_repair === 'Belum Diperbaiki' && $age > 48 * 3600;
            $c->age_h   = (int)floor($age / 3600);
            $c->wa      = null;
            if (($user->isAdmin() || $user->isWaka()) && $c->room->pic && $c->room->pic->phone_number) {
                $phone = preg_replace('/\D/', '', $c->room->pic->phone_number);
                if (str_starts_with($phone, '08')) $phone = '628' . substr($phone, 2);
                $text = urlencode("Halo {$c->room->pic->fullname}, pengaduan kerusakan di {$c->room->room_name} (\"" . mb_strimwidth($c->description, 0, 80, '…') . "\") dilaporkan dan masih berstatus Belum Diperbaiki selama {$c->age_h} jam. Mohon segera ditindaklanjuti.");
                $c->wa = "https://wa.me/{$phone}?text={$text}";
            }
            return $c;
        });

        return view('complaints.list', ['complaints' => $complaints, 'statuses' => self::STATUSES, 'limitH' => 48]);
    }

    public function create()
    {
        $rooms = Room::where('is_active', 1)->orderBy('room_name')->get();
        return view('complaints.form', compact('rooms'));
    }

    public function store(Request $request)
    {
        $roomId = (int)$request->input('room_id', 0);
        $desc   = trim($request->input('description', ''));

        $room = Room::where('id', $roomId)->where('is_active', 1)->first();
        if (!$room) return back()->withErrors(['room_id' => 'Pilih ruang yang valid.'])->withInput();
        if (mb_strlen($desc) < 10) return back()->withErrors(['description' => 'Jelaskan kerusakan minimal 10 karakter.'])->withInput();

        $photoPath = null;
        try {
            // Check for real-time camera snapshot (base64)
            if ($request->filled('camera_photo_data')) {
                $data = (string)$request->input('camera_photo_data');
                if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                    $data = substr($data, strpos($data, ',') + 1);
                    $ext = strtolower($type[1]);
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $decoded = base64_decode($data);
                        if ($decoded !== false) {
                            $ext = ($ext === 'jpeg') ? 'jpg' : $ext;
                            $filename = 'complaints/livecam_' . uniqid() . '.' . $ext;
                            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                            $photoPath = $filename;
                        }
                    }
                }
            }

            // Fallback to standard file upload if no camera snapshot
            if (!$photoPath && $request->hasFile('photo') && $request->file('photo')->isValid()) {
                $file = $request->file('photo');
                if ($file->getSize() > 5 * 1024 * 1024) return back()->withErrors(['photo' => 'Ukuran foto maksimal 5 MB.'])->withInput();
                $photoPath = $file->store('complaints', 'public');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Upload error: ' . $e->getMessage());
        }

        $complaint = Complaint::create([
            'room_id'     => $roomId,
            'reporter_id' => $request->user()->id,
            'description' => $desc,
            'photo_path'  => $photoPath,
            'status'      => 'Diterima',
        ]);

        // Notify admins & waka about new complaint
        $staffToNotify = \App\Models\User::whereIn('role_id', [Role::ADMIN, Role::WAKA])->get();
        foreach ($staffToNotify as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'message' => "Pengaduan baru di {$room->room_name} dari {$request->user()->fullname}: " . mb_strimwidth($desc, 0, 60, '…'),
            ]);
        }

        if ($room->pic_id) {
            Notification::create([
                'user_id' => $room->pic_id,
                'message' => 'Pengaduan baru di ' . $room->room_name . ': ' . mb_strimwidth($desc, 0, 90, '…'),
            ]);
        }

        return redirect()->route('complaints.index')->with('success', 'Pengaduan kerusakan terkirim ke PIC ruang.');
    }

    /** Status pengaduan: HANYA PIC yang dapat mengubah status */
    public function status(Request $request)
    {
        $user = $request->user();
        if (!$user->isPic()) {
            abort(403, 'Hanya PIC ruang yang berhak mengubah status pengaduan.');
        }

        $new = $request->input('status_repair', '');
        if (!in_array($new, self::STATUSES, true)) {
            return back()->withErrors(['status_repair' => 'Status tidak valid.']);
        }

        $complaint = Complaint::where('id', (int)$request->input('id'))
            ->whereHas('room', fn($q) => $q->where('pic_id', $user->id))
            ->with(['room', 'reporter'])
            ->first();

        if (!$complaint) {
            abort(403, 'Ruangan ini bukan tanggung jawab Anda sebagai PIC.');
        }

        $complaint->update([
            'status'        => 'Diterima',
            'status_repair' => $new,
            'handler_id'    => $user->id,
            'repair_note'   => $request->input('repair_note') ?: null,
        ]);

        if ($complaint->reporter_id) {
            Notification::create([
                'user_id' => $complaint->reporter_id,
                'message' => "Pengaduan kerusakan di {$complaint->room->room_name} kini berstatus: {$new}.",
            ]);
        }

        return back()->with('success', "Status perbaikan pengaduan berhasil diperbarui oleh PIC: {$new}.");
    }

    public function delete(Request $request)
    {
        $user = $request->user();
        $complaint = Complaint::findOrFail((int)$request->input('id'));

        if ($user->isGuru() && $complaint->reporter_id !== $user->id) {
            return back()->withErrors(['id' => 'Anda hanya dapat menghapus laporan milik sendiri.']);
        }

        if ($complaint->photo_path) {
            \Storage::disk('public')->delete($complaint->photo_path);
        }

        $complaint->delete();
        return back()->with('success', 'Pengaduan berhasil dihapus.');
    }
}
