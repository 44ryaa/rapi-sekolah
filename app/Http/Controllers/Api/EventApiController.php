<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Room;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    private const SESSIONS = [
        1 => ['label' => 'Sesi 1 — 07:00–08:30',       'start' => '07:00', 'end' => '08:30'],
        2 => ['label' => 'Sesi 2 — 08:30–10:00',       'start' => '08:30', 'end' => '10:00'],
        3 => ['label' => 'Sesi 3 — 10:15–11:45',       'start' => '10:15', 'end' => '11:45'],
        4 => ['label' => 'Sesi 4 — 13:00–14:30',       'start' => '13:00', 'end' => '14:30'],
        5 => ['label' => 'Sesi 5 — 14:30–16:00',       'start' => '14:30', 'end' => '16:00'],
        6 => ['label' => 'Sesi Penuh — 07:00–16:00',   'start' => '07:00', 'end' => '16:00'],
    ];

    /**
     * GET /api/events
     * Query: ?room_id=&status=&date_from=&date_to=
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Event::with(['room:id,room_name,location', 'requester:id,fullname,username'])
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->limit(200);

        // Guru hanya melihat agenda miliknya
        if ($user->isGuru()) {
            $query->where('pic_id', $user->id);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', (int) $request->room_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        return response()->json([
            'data' => $query->get()->map(fn ($ev) => $this->eventResource($ev)),
        ]);
    }

    /**
     * GET /api/events/{id}
     */
    public function show(int $id, Request $request)
    {
        $event = Event::with(['room', 'requester', 'approver'])->findOrFail($id);

        if ($request->user()->isGuru() && $event->pic_id !== $request->user()->id) {
            abort(403, 'Bukan agenda Anda.');
        }

        return response()->json(['data' => $this->eventResource($event)]);
    }

    /**
     * GET /api/events/slots?room_id=&date=
     */
    public function slots(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $roomId = (int) $request->room_id;
        $date = $request->date;

        $dayOfWeek = (int) date('N', strtotime($date));
        if ($dayOfWeek >= 6) {
            return response()->json([
                'weekend' => true,
                'error' => 'Peminjaman tidak tersedia pada Sabtu dan Minggu.',
                'sessions' => [],
            ]);
        }

        $booked = Event::where('room_id', $roomId)
            ->where('date', $date)
            ->whereIn('status', ['Diajukan', 'Disetujui'])
            ->orderBy('start_time')
            ->get(['event_name', 'start_time', 'end_time']);

        $sessions = [];
        foreach (self::SESSIONS as $num => $s) {
            $taken = false;
            $takenBy = '';
            foreach ($booked as $b) {
                if ($s['start'] < substr($b->end_time, 0, 5) && $s['end'] > substr($b->start_time, 0, 5)) {
                    $taken = true;
                    $takenBy = $b->event_name;
                    break;
                }
            }
            $sessions[$num] = ['label' => $s['label'], 'taken' => $taken, 'taken_by' => $takenBy];
        }

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * POST /api/events
     * Body: { room_id, event_name, date, session_start, session_end? }
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer',
            'event_name' => 'required|string|max:150',
            'date' => 'required|date_format:Y-m-d|after:today',
            'session_start' => 'required|integer|between:1,6',
            'session_end' => 'nullable|integer|between:1,5',
        ]);

        $user = $request->user();
        $date = $request->date;
        $sesStart = (int) $request->session_start;
        $sesEnd = (int) ($request->session_end ?? $sesStart);

        $dayOfWeek = (int) date('N', strtotime($date));
        if ($dayOfWeek >= 6) {
            return response()->json(['message' => 'Peminjaman tidak tersedia pada Sabtu dan Minggu.'], 422);
        }

        if ($sesStart === 6) {
            $start = '07:00';
            $end = '16:00';
        } else {
            if (! isset(self::SESSIONS[$sesEnd]) || $sesEnd === 6) {
                return response()->json(['message' => 'Sesi selesai tidak valid.'], 422);
            }
            if (self::SESSIONS[$sesEnd]['end'] <= self::SESSIONS[$sesStart]['start']) {
                return response()->json(['message' => 'Sesi selesai harus setelah sesi mulai.'], 422);
            }
            $start = self::SESSIONS[$sesStart]['start'];
            $end = self::SESSIONS[$sesEnd]['end'];
        }

        if (! Room::where('id', $request->room_id)->where('is_active', 1)->exists()) {
            return response()->json(['message' => 'Ruang tidak tersedia.'], 422);
        }

        $conflict = Event::hasConflict((int) $request->room_id, $date, $start, $end);
        if ($conflict) {
            return response()->json([
                'message' => "Ruang sudah dipesan untuk \"{$conflict->event_name}\". Pilih sesi atau ruang lain.",
            ], 422);
        }

        $canAutoApprove = $user->isAdmin() || $user->isWaka();

        $event = Event::create([
            'room_id' => $request->room_id,
            'pic_id' => $user->id,
            'event_name' => $request->event_name,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $canAutoApprove ? 'Disetujui' : 'Diajukan',
            'approved_by' => $canAutoApprove ? $user->id : null,
        ]);

        return response()->json([
            'data' => $this->eventResource($event->load(['room', 'requester'])),
            'message' => $canAutoApprove ? 'Agenda disimpan dan langsung disetujui.' : 'Pengajuan terkirim. Menunggu persetujuan.',
        ], 201);
    }

    /**
     * POST /api/events/{id}/review  [Admin/Waka only]
     * Body: { decision: 'approve'|'reject', reject_reason? }
     */
    public function review(Request $request, int $id)
    {
        if (! $request->user()->canApprove()) {
            abort(403, 'Hanya Admin atau Waka yang dapat menyetujui/menolak agenda.');
        }

        $request->validate([
            'decision' => 'required|in:approve,reject',
            'reject_reason' => 'required_if:decision,reject|string|min:5',
        ]);

        $event = Event::where('id', $id)->where('status', 'Diajukan')->firstOrFail();
        $ok = $request->decision === 'approve';
        $reason = $ok ? null : $request->reject_reason;

        $event->update([
            'status' => $ok ? 'Disetujui' : 'Ditolak',
            'approved_by' => $request->user()->id,
            'reject_reason' => $reason,
        ]);

        if ($event->pic_id !== $request->user()->id) {
            $approverTitle = $request->user()->isWaka() ? 'Waka Sarpras' : 'Admin';
            $msg = $ok
                ? "Pengajuan \"{$event->event_name}\" (".$event->date->format('d M Y').") DISETUJUI ✓ oleh {$approverTitle}."
                : "Pengajuan \"{$event->event_name}\" (".$event->date->format('d M Y').") DITOLAK ✗. Alasan: {$reason}";

            Notification::create(['user_id' => $event->pic_id, 'message' => $msg]);
        }

        return response()->json([
            'data' => $this->eventResource($event->fresh(['room', 'requester', 'approver'])),
            'message' => $ok ? 'Agenda disetujui.' : 'Agenda ditolak.',
        ]);
    }

    /**
     * DELETE /api/events/{id}
     */
    public function destroy(int $id, Request $request)
    {
        $event = Event::findOrFail($id);
        $user = $request->user();

        if ($user->isGuru() && $event->pic_id !== $user->id) {
            abort(403, 'Bukan agenda Anda.');
        }
        if (! $user->isAdmin() && ! $user->isWaka() && $event->status === 'Disetujui') {
            return response()->json(['message' => 'Agenda yang sudah disetujui tidak dapat dihapus sendiri.'], 422);
        }

        $event->delete();

        return response()->json(['message' => 'Agenda berhasil dihapus.']);
    }

    private function eventResource(Event $ev): array
    {
        return [
            'id' => $ev->id,
            'event_name' => $ev->event_name,
            'date' => $ev->date?->format('Y-m-d'),
            'start_time' => substr($ev->start_time, 0, 5),
            'end_time' => substr($ev->end_time, 0, 5),
            'status' => $ev->status,
            'reject_reason' => $ev->reject_reason,
            'move_reason' => $ev->move_reason,
            'room' => $ev->room ? ['id' => $ev->room->id, 'room_name' => $ev->room->room_name, 'location' => $ev->room->location] : null,
            'requester' => $ev->requester ? ['id' => $ev->requester->id, 'fullname' => $ev->requester->fullname] : null,
            'approver' => $ev->approver ? ['id' => $ev->approver->id,  'fullname' => $ev->approver->fullname] : null,
            'created_at' => $ev->created_at?->toISOString(),
        ];
    }
}
