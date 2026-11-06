<?php

declare(strict_types=1);

class EventController extends Controller
{
    private const ACTIVE = "('Diajukan','Disetujui')";

    /** Inti logika anti-tabrakan: dua rentang beririsan bila  mulai_baru < selesai_lama  DAN  selesai_baru > mulai_lama. */
    private function conflict(int $room, string $date, string $s, string $e, int $ignore = 0): ?array
    {
        $st = db()->prepare('SELECT event_name,start_time,end_time FROM events
            WHERE room_id=? AND `date`=? AND status IN '.self::ACTIVE.' AND id<>? AND ? < end_time AND ? > start_time LIMIT 1');
        $st->execute([$room, $date, $ignore, $s, $e]);

        return $st->fetch() ?: null;
    }

    private function conflictMsg(array $c): string
    {
        return "Ruang sudah dipesan untuk \"{$c['event_name']}\" pukul ".jam($c['start_time']).'–'.jam($c['end_time']).'. Pilih jam atau ruang lain.';
    }

    public function index(): void
    {
        $u = $this->user();
        $sql = 'SELECT e.*,r.room_name,p.fullname AS requester FROM events e
                JOIN rooms r ON r.id=e.room_id JOIN users p ON p.id=e.pic_id';
        $args = [];
        if ($u['role_id'] == ROLE_GURU) {
            $sql .= ' WHERE e.pic_id=?';
            $args[] = $u['id'];
        }
        $st = db()->prepare($sql.' ORDER BY e.`date` DESC, e.start_time DESC LIMIT 200');
        $st->execute($args);
        $rooms = db()->query('SELECT id,room_name FROM rooms WHERE is_active=1 ORDER BY room_name')->fetchAll();
        view('events/list', ['events' => $st->fetchAll(), 'rooms' => $rooms]);
    }

    public function create(): void
    {
        $rooms = db()->query('SELECT id,room_name,capacity,location FROM rooms WHERE is_active=1 ORDER BY room_name')->fetchAll();
        view('events/form', ['rooms' => $rooms]);
    }

    /** Slot terisi untuk ruang+tanggal tertentu (dipakai UI untuk mengunci input). */
    public function slots(): void
    {
        $st = db()->prepare('SELECT event_name,start_time,end_time FROM events WHERE room_id=? AND `date`=? AND status IN '.self::ACTIVE.' ORDER BY start_time');
        $st->execute([$this->id('room_id'), (string) ($_GET['date'] ?? '')]);
        json_out(['slots' => array_map(fn ($r) => ['name' => $r['event_name'], 'start' => jam($r['start_time']), 'end' => jam($r['end_time'])], $st->fetchAll())]);
    }

    public function store(): void
    {
        $u = $this->user();
        $room = $this->id('room_id');
        $name = $this->str('event_name');
        $date = $this->str('date');
        $s = $this->str('start_time');
        $e = $this->str('end_time');

        $err = null;
        if ($name === '' || mb_strlen($name) > 150) {
            $err = 'Nama kegiatan wajib diisi (maks. 150 karakter).';
        } elseif (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
            $err = 'Tanggal tidak valid.';
        } elseif ($date < date('Y-m-d')) {
            $err = 'Tanggal tidak boleh di masa lalu.';
        } elseif (! preg_match('/^\d{2}:\d{2}$/', $s) || ! preg_match('/^\d{2}:\d{2}$/', $e)) {
            $err = 'Jam mulai dan selesai wajib diisi.';
        } elseif ($e <= $s) {
            $err = 'Jam selesai harus setelah jam mulai.';
        }
        if ($err) {
            $this->back('event/create', 'error', $err);
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();
            // Kunci baris ruang agar dua pengajuan bersamaan diproses berurutan (anti race-condition).
            $chk = $pdo->prepare('SELECT id FROM rooms WHERE id=? AND is_active=1 FOR UPDATE');
            $chk->execute([$room]);
            if (! $chk->fetch()) {
                $pdo->rollBack();
                $this->back('event/create', 'error', 'Ruang tidak tersedia.');
            }
            if ($c = $this->conflict($room, $date, $s, $e)) {
                $pdo->rollBack();
                $this->back('event/create', 'error', $this->conflictMsg($c));
            }

            $admin = (int) $u['role_id'] === ROLE_ADMIN;
            $pdo->prepare('INSERT INTO events (room_id,pic_id,event_name,`date`,start_time,end_time,status,approved_by) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$room, $u['id'], $name, $date, $s, $e, $admin ? 'Disetujui' : 'Diajukan', $admin ? $u['id'] : null]);
            $pdo->commit();
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($ex->getCode() === '45000') {
                $this->back('event/create', 'error', 'Ruang dan waktu tersebut baru saja dipesan pengguna lain.');
            }
            throw $ex;
        }
        $this->back('event/index', 'ok', $admin ? 'Agenda disimpan dan langsung disetujui.' : 'Pengajuan terkirim. Menunggu persetujuan Waka.');
    }

    public function review(): void
    {
        $ok = ($_POST['decision'] ?? '') === 'approve';
        $st = db()->prepare('SELECT * FROM events WHERE id=? AND status=?');
        $st->execute([$this->id(), 'Diajukan']);
        if (! ($ev = $st->fetch())) {
            $this->back('event/index', 'error', 'Pengajuan tidak ditemukan atau sudah diproses.');
        }
        db()->prepare('UPDATE events SET status=?, approved_by=? WHERE id=?')->execute([$ok ? 'Disetujui' : 'Ditolak', $this->user()['id'], $ev['id']]);
        if ($ev['pic_id'] != $this->user()['id']) {
            notify((int) $ev['pic_id'], 'Pengajuan "'.$ev['event_name'].'" ('.tgl($ev['date']).') '.($ok ? 'DISETUJUI' : 'DITOLAK').' oleh Waka.');
        }
        $this->back('event/index', 'ok', $ok ? 'Pengajuan disetujui.' : 'Pengajuan ditolak.');
    }

    /** Override Waka: pindah ruang + alasan wajib, otomatis menulis move_reason dan notifikasi ke peminjam. */
    public function move(): void
    {
        $new = $this->id('new_room_id');
        $reason = $this->str('reason');
        if (mb_strlen($reason) < 5) {
            $this->back('event/index', 'error', 'Alasan pemindahan wajib diisi (minimal 5 karakter).');
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare('SELECT e.*,r.room_name AS old_room FROM events e JOIN rooms r ON r.id=e.room_id WHERE e.id=? AND e.status IN '.self::ACTIVE.' FOR UPDATE');
            $st->execute([$this->id()]);
            $ev = $st->fetch();
            $nr = $pdo->prepare('SELECT id,room_name FROM rooms WHERE id=? AND is_active=1 FOR UPDATE');
            $nr->execute([$new]);
            $room = $nr->fetch();
            if (! $ev || ! $room || $new == $ev['room_id']) {
                $pdo->rollBack();
                $this->back('event/index', 'error', 'Pilih ruang tujuan yang berbeda dan aktif.');
            }
            if ($c = $this->conflict($new, $ev['date'], $ev['start_time'], $ev['end_time'], (int) $ev['id'])) {
                $pdo->rollBack();
                $this->back('event/index', 'error', 'Ruang tujuan bentrok. '.$this->conflictMsg($c));
            }
            $pdo->prepare('UPDATE events SET room_id=?, move_reason=? WHERE id=?')->execute([$new, $reason, $ev['id']]);
            if ($ev['pic_id'] != $this->user()['id']) {
                notify((int) $ev['pic_id'], "Agenda \"{$ev['event_name']}\" ({$ev['date']} ".jam($ev['start_time']).'–'.jam($ev['end_time']).") dipindahkan dari {$ev['old_room']} ke {$room['room_name']}. Alasan Waka: $reason");
            }
            $pdo->commit();
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($ex->getCode() === '45000') {
                $this->back('event/index', 'error', 'Ruang tujuan bentrok dengan agenda lain.');
            }
            throw $ex;
        }
        $this->back('event/index', 'ok', 'Agenda dipindahkan ke '.$room['room_name'].'. Peminjam diberi notifikasi.');
    }
}
