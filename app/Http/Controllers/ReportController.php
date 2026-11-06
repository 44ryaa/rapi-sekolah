<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the Sarpras reports dashboard (daily, weekly, monthly).
     */
    public function index(Request $request): View
    {
        Carbon::setLocale('id');

        $data = $this->buildReportData($request);

        return view('reports.index', $data);
    }

    /**
     * Display the print-optimized report page with official letterhead.
     */
    public function print(Request $request): View
    {
        Carbon::setLocale('id');

        $data = $this->buildReportData($request);

        return view('reports.print', $data);
    }

    /**
     * Export the filtered report to CSV format for spreadsheets.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        Carbon::setLocale('id');

        $data = $this->buildReportData($request);
        $period = $data['period'];
        $periodLabelSlug = str_replace([' ', '/', ':', '–'], '-', strtolower($data['periodLabel']));
        $filename = "laporan-sarpras-{$period}-".now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Report Header
            fputcsv($handle, ['LAPORAN SARANA DAN PRASARANA SEKOLAH (RAPI-SEKOLAH)']);
            fputcsv($handle, ['Tipe Laporan', strtoupper($data['period'])]);
            fputcsv($handle, ['Periode', $data['periodLabel']]);
            fputcsv($handle, ['Filter Ruang', $data['selectedRoom'] ? $data['selectedRoom']->room_name : 'Semua Ruangan']);
            fputcsv($handle, ['Waktu Unduh', now()->translatedFormat('d F Y H:i:s')]);
            fputcsv($handle, ['Diunduh Oleh', auth()->user()?->fullname ?? auth()->user()?->username]);
            fputcsv($handle, []);

            // Summary Stats
            fputcsv($handle, ['--- RINGKASAN STATISTIK ---']);
            fputcsv($handle, ['Total Peminjaman/Agenda', $data['cntEventsTotal']]);
            fputcsv($handle, ['Agenda Disetujui', $data['cntEventsApproved']]);
            fputcsv($handle, ['Agenda Menunggu Persetujuan', $data['cntEventsPending']]);
            fputcsv($handle, ['Agenda Ditolak', $data['cntEventsRejected']]);
            fputcsv($handle, ['Total Pengaduan Kerusakan', $data['cntComplaintsTotal']]);
            fputcsv($handle, ['Perbaikan Selesai', $data['cntRepairDone']]);
            fputcsv($handle, ['Perbaikan Menunggu Spare Part', $data['cntRepairWaiting']]);
            fputcsv($handle, ['Kerusakan Belum Diperbaiki', $data['cntRepairPending']]);
            fputcsv($handle, ['Tingkat Penyelesaian Perbaikan', $data['repairRate'].'%']);
            fputcsv($handle, []);

            // Events Section
            fputcsv($handle, ['--- DAFTAR PEMINJAMAN & AGENDA RUANG ---']);
            fputcsv($handle, ['No', 'Tanggal', 'Waktu Mulai', 'Waktu Selesai', 'Nama Agenda / Kegiatan', 'Ruangan', 'Peminjam / PIC', 'Status Verifikasi', 'Disetujui Oleh', 'Catatan / Alasan']);

            $i = 1;
            foreach ($data['events'] as $ev) {
                fputcsv($handle, [
                    $i++,
                    $ev->date ? $ev->date->format('Y-m-d') : '-',
                    substr($ev->start_time, 0, 5),
                    substr($ev->end_time, 0, 5),
                    $ev->event_name,
                    $ev->room?->room_name ?? '-',
                    $ev->requester?->fullname ?? $ev->requester?->username ?? '-',
                    $ev->status,
                    $ev->approver?->fullname ?? '-',
                    $ev->move_reason ?: ($ev->reject_reason ?: '-'),
                ]);
            }
            fputcsv($handle, []);

            // Complaints Section
            fputcsv($handle, ['--- DAFTAR PENGADUAN & PERBAIKAN FASILITAS ---']);
            fputcsv($handle, ['No', 'Tanggal Lapor', 'Ruangan', 'Pelapor', 'Deskripsi Masalah / Kerusakan', 'Status Verifikasi', 'Status Perbaikan', 'Teknisi / PIC Penangan', 'Catatan Perbaikan']);

            $j = 1;
            foreach ($data['complaints'] as $comp) {
                fputcsv($handle, [
                    $j++,
                    Carbon::parse($comp->reported_at)->format('Y-m-d H:i'),
                    $comp->room?->room_name ?? '-',
                    $comp->reporter?->fullname ?? $comp->reporter?->username ?? '-',
                    $comp->description,
                    $comp->status,
                    $comp->status_repair,
                    $comp->handler?->fullname ?? '-',
                    $comp->repair_note ?: ($comp->reject_reason ?: '-'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Compute and aggregate report data based on period and filters.
     *
     * @return array<string, mixed>
     */
    protected function buildReportData(Request $request): array
    {
        $period = (string) $request->input('period', 'daily');
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'daily';
        }

        $now = now();
        $roomId = (int) $request->input('room_id', 0);
        $selectedRoom = $roomId > 0 ? Room::find($roomId) : null;

        // Date calculations
        if ($period === 'daily') {
            $rawDate = (string) $request->input('date', $now->toDateString());
            try {
                $refDate = Carbon::parse($rawDate);
            } catch (\Exception $e) {
                $refDate = $now->copy();
            }

            $startDate = $refDate->copy()->startOfDay();
            $endDate = $refDate->copy()->endOfDay();

            $prevDate = $refDate->copy()->subDay()->toDateString();
            $nextDate = $refDate->copy()->addDay()->toDateString();
            $currentDate = $refDate->toDateString();

            $periodLabel = 'Harian · '.$refDate->translatedFormat('l, d F Y');
            $periodSub = 'Rekapitulasi aktivitas sarpras pada 1 hari kerja';
            $navQueryPrev = ['period' => 'daily', 'date' => $prevDate, 'room_id' => $roomId];
            $navQueryNext = ['period' => 'daily', 'date' => $nextDate, 'room_id' => $roomId];
            $navQueryCurrent = ['period' => 'daily', 'date' => $now->toDateString(), 'room_id' => $roomId];

        } elseif ($period === 'weekly') {
            $rawDate = (string) $request->input('date', $now->toDateString());
            try {
                $refDate = Carbon::parse($rawDate);
            } catch (\Exception $e) {
                $refDate = $now->copy();
            }

            $startDate = $refDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endDate = $refDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            $prevDate = $refDate->copy()->subWeek()->toDateString();
            $nextDate = $refDate->copy()->addWeek()->toDateString();
            $currentDate = $refDate->toDateString();

            $periodLabel = 'Mingguan · '.$startDate->translatedFormat('d M Y').' – '.$endDate->translatedFormat('d M Y').' (Minggu ke-'.$startDate->weekOfYear.')';
            $periodSub = 'Rekapitulasi aktivitas sarpras selama 7 hari kalender';
            $navQueryPrev = ['period' => 'weekly', 'date' => $prevDate, 'room_id' => $roomId];
            $navQueryNext = ['period' => 'weekly', 'date' => $nextDate, 'room_id' => $roomId];
            $navQueryCurrent = ['period' => 'weekly', 'date' => $now->toDateString(), 'room_id' => $roomId];

        } else { // monthly
            $month = (int) $request->input('month', $now->month);
            $year = (int) $request->input('year', $now->year);

            if ($month < 1 || $month > 12) {
                $month = (int) $now->month;
            }
            if ($year < 2020 || $year > 2040) {
                $year = (int) $now->year;
            }

            $refMonth = Carbon::createFromDate($year, $month, 1);
            $startDate = $refMonth->copy()->startOfMonth()->startOfDay();
            $endDate = $refMonth->copy()->endOfMonth()->endOfDay();

            $prevMonthDate = $refMonth->copy()->subMonth();
            $nextMonthDate = $refMonth->copy()->addMonth();

            $periodLabel = 'Bulanan · '.$refMonth->translatedFormat('F Y');
            $periodSub = 'Rekapitulasi menyeluruh sarpras selama 1 bulan penuh';
            $navQueryPrev = ['period' => 'monthly', 'month' => $prevMonthDate->month, 'year' => $prevMonthDate->year, 'room_id' => $roomId];
            $navQueryNext = ['period' => 'monthly', 'month' => $nextMonthDate->month, 'year' => $nextMonthDate->year, 'room_id' => $roomId];
            $navQueryCurrent = ['period' => 'monthly', 'month' => $now->month, 'year' => $now->year, 'room_id' => $roomId];
            $currentDate = $now->toDateString();
        }

        // Fetch events
        $eventsQuery = Event::with(['room', 'requester', 'approver'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($roomId > 0) {
            $eventsQuery->where('room_id', $roomId);
        }

        $events = $eventsQuery->orderBy('date')->orderBy('start_time')->get();

        // Fetch complaints
        $complaintsQuery = Complaint::with(['room', 'reporter', 'handler'])
            ->whereBetween('reported_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);

        if ($roomId > 0) {
            $complaintsQuery->where('room_id', $roomId);
        }

        $complaints = $complaintsQuery->orderBy('reported_at', 'desc')->get();

        // Metrics - Events
        $cntEventsTotal = $events->count();
        $cntEventsApproved = $events->where('status', 'Disetujui')->count();
        $cntEventsPending = $events->where('status', 'Diajukan')->count();
        $cntEventsRejected = $events->where('status', 'Ditolak')->count();

        // Top rooms used
        $topRooms = $events->where('status', 'Disetujui')
            ->groupBy('room_id')
            ->map(function ($items) {
                return [
                    'room_name' => $items->first()->room?->room_name ?? 'Ruang Lain',
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        // Metrics - Complaints
        $cntComplaintsTotal = $complaints->count();
        $cntComplaintsReceived = $complaints->where('status', 'Diterima')->count();
        $cntComplaintsPending = $complaints->where('status', 'Diajukan')->count();
        $cntComplaintsRejected = $complaints->where('status', 'Ditolak')->count();

        $cntRepairDone = $complaints->where('status_repair', 'Sudah Diperbaiki')->count();
        $cntRepairWaiting = $complaints->where('status_repair', 'Menunggu Spare Part')->count();
        $cntRepairPending = $complaints->where('status_repair', 'Belum Diperbaiki')->count();

        $repairRate = $cntComplaintsTotal > 0
            ? round(($cntRepairDone / $cntComplaintsTotal) * 100)
            : 100;

        $rooms = Room::where('is_active', true)->orderBy('room_name')->get();

        return [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodLabel' => $periodLabel,
            'periodSub' => $periodSub,
            'currentDate' => $currentDate,
            'selectedDate' => $startDate->toDateString(),
            'selectedMonth' => $startDate->month,
            'selectedYear' => $startDate->year,
            'roomId' => $roomId,
            'selectedRoom' => $selectedRoom,
            'rooms' => $rooms,
            'navQueryPrev' => $navQueryPrev,
            'navQueryNext' => $navQueryNext,
            'navQueryCurrent' => $navQueryCurrent,
            'events' => $events,
            'complaints' => $complaints,
            'cntEventsTotal' => $cntEventsTotal,
            'cntEventsApproved' => $cntEventsApproved,
            'cntEventsPending' => $cntEventsPending,
            'cntEventsRejected' => $cntEventsRejected,
            'topRooms' => $topRooms,
            'cntComplaintsTotal' => $cntComplaintsTotal,
            'cntComplaintsReceived' => $cntComplaintsReceived,
            'cntComplaintsPending' => $cntComplaintsPending,
            'cntComplaintsRejected' => $cntComplaintsRejected,
            'cntRepairDone' => $cntRepairDone,
            'cntRepairWaiting' => $cntRepairWaiting,
            'cntRepairPending' => $cntRepairPending,
            'repairRate' => $repairRate,
        ];
    }
}
