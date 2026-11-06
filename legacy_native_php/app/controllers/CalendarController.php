<?php

declare(strict_types=1);

class CalendarController extends Controller
{
    public function index(): void
    {
        view('calendar');
    }

    /** JSON agenda per bulan (publik, read-only). Data pribadi tidak diekspos. */
    public function api(): void
    {
        $m = (string) ($_GET['month'] ?? date('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m)) {
            json_out(['error' => 'Format bulan tidak valid'], 400);
        }
        $st = db()->prepare("SELECT e.id,e.event_name,e.`date`,e.start_time,e.end_time,e.status,e.room_id,r.room_name
                             FROM events e JOIN rooms r ON r.id=e.room_id
                             WHERE e.status IN ('Disetujui','Diajukan') AND DATE_FORMAT(e.`date`,'%Y-%m')=?
                             ORDER BY e.`date`,e.start_time");
        $st->execute([$m]);
        $rows = array_map(fn ($r) => ['id' => (int) $r['id'], 'name' => $r['event_name'], 'date' => $r['date'],
            'start' => jam($r['start_time']), 'end' => jam($r['end_time']), 'status' => $r['status'],
            'room_id' => (int) $r['room_id'], 'room' => $r['room_name']], $st->fetchAll());
        json_out(['month' => $m, 'events' => $rows]);
    }
}
