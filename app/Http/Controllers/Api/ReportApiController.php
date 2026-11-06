<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportApiController extends Controller
{
    /**
     * GET /api/reports
     * Query: ?period=daily|weekly|monthly&date=Y-m-d&month=&year=&room_id=
     *
     * Returns aggregated statistics + raw lists for events & complaints.
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $period = $request->input('period', 'daily');
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'daily';
        }

        $now = now();
        $roomId = (int) $request->input('room_id', 0);

        // ── Date range ──────────────────────────────────────────────
        if ($period === 'daily') {
            $refDate = Carbon::parse($request->input('date', $now->toDateString()));
            $startDate = $refDate->copy()->startOfDay();
            $endDate = $refDate->copy()->endOfDay();
            $label = $refDate->translatedFormat('l, d F Y');
        } elseif ($period === 'weekly') {
            $refDate = Carbon::parse($request->input('date', $now->toDateString()));
            $startDate = $refDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endDate = $refDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $label = $startDate->translatedFormat('d M Y').' – '.$endDate->translatedFormat('d M Y');
        } else { // monthly
            $month = (int) $request->input('month', $now->month);
            $year = (int) $request->input('year', $now->year);
            $refMonth = Carbon::createFromDate($year, $month, 1);
            $startDate = $refMonth->copy()->startOfMonth()->startOfDay();
            $endDate = $refMonth->copy()->endOfMonth()->endOfDay();
            $label = $refMonth->translatedFormat('F Y');
        }

        // ── Queries ─────────────────────────────────────────────────
        $evQuery = Event::with(['room:id,room_name', 'requester:id,fullname', 'approver:id,fullname'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        $cQuery = Complaint::with(['room:id,room_name', 'reporter:id,fullname', 'handler:id,fullname'])
            ->whereBetween('reported_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);

        if ($roomId > 0) {
            $evQuery->where('room_id', $roomId);
            $cQuery->where('room_id', $roomId);
        }

        $events = $evQuery->orderBy('date')->orderBy('start_time')->get();
        $complaints = $cQuery->orderByDesc('reported_at')->get();

        // ── Metrics ─────────────────────────────────────────────────
        $cntEventsTotal = $events->count();
        $cntApproved = $events->where('status', 'Disetujui')->count();
        $cntPending = $events->where('status', 'Diajukan')->count();
        $cntRejected = $events->where('status', 'Ditolak')->count();

        $cntComplaintsTotal = $complaints->count();
        $cntRepairDone = $complaints->where('status_repair', 'Sudah Diperbaiki')->count();
        $cntRepairWaiting = $complaints->where('status_repair', 'Menunggu Spare Part')->count();
        $cntRepairPending = $complaints->where('status_repair', 'Belum Diperbaiki')->count();
        $repairRate = $cntComplaintsTotal > 0 ? round($cntRepairDone / $cntComplaintsTotal * 100) : 100;

        $topRooms = $events->where('status', 'Disetujui')
            ->groupBy('room_id')
            ->map(fn ($items) => [
                'room_name' => $items->first()->room?->room_name ?? '-',
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(5);

        return response()->json([
            'meta' => [
                'period' => $period,
                'label' => $label,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'room_id' => $roomId ?: null,
                'room_name' => $roomId > 0 ? Room::find($roomId)?->room_name : null,
            ],
            'summary' => [
                'events' => [
                    'total' => $cntEventsTotal,
                    'approved' => $cntApproved,
                    'pending' => $cntPending,
                    'rejected' => $cntRejected,
                    'top_rooms' => $topRooms,
                ],
                'complaints' => [
                    'total' => $cntComplaintsTotal,
                    'repair_done' => $cntRepairDone,
                    'repair_waiting' => $cntRepairWaiting,
                    'repair_pending' => $cntRepairPending,
                    'repair_rate' => $repairRate,
                ],
            ],
            'events' => $events->map(fn ($ev) => [
                'id' => $ev->id,
                'event_name' => $ev->event_name,
                'date' => $ev->date?->format('Y-m-d'),
                'start_time' => substr($ev->start_time, 0, 5),
                'end_time' => substr($ev->end_time, 0, 5),
                'status' => $ev->status,
                'room' => $ev->room?->room_name,
                'requester' => $ev->requester?->fullname,
                'approver' => $ev->approver?->fullname,
            ]),
            'complaints' => $complaints->map(fn ($c) => [
                'id' => $c->id,
                'description' => $c->description,
                'status' => $c->status,
                'status_repair' => $c->status_repair,
                'room' => $c->room?->room_name,
                'reporter' => $c->reporter?->fullname,
                'handler' => $c->handler?->fullname,
                'reported_at' => $c->reported_at?->toISOString(),
            ]),
        ]);
    }
}
