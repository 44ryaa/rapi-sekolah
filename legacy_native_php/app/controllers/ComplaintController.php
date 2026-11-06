<?php

declare(strict_types=1);

class ComplaintController extends Controller
{
    private const STATUSES = ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'];

    public function index(): void
    {
        $u = $this->user();
        $where = '';
        $args = [];
        if ($u['role_id'] == ROLE_PIC) {
            $where = 'WHERE r.pic_id=?';
            $args[] = $u['id'];
        }   // delegasi PIC -> hanya ruangannya
        if ($u['role_id'] == ROLE_GURU) {
            $where = 'WHERE c.reporter_id=?';
            $args[] = $u['id'];
        }
        $st = db()->prepare("SELECT c.*,r.room_name,r.pic_id,rp.fullname AS reporter,pic.fullname AS pic_name,pic.phone_number AS pic_phone
                             FROM complaints c JOIN rooms r ON r.id=c.room_id
                             JOIN users rp ON rp.id=c.reporter_id LEFT JOIN users pic ON pic.id=r.pic_id
                             $where ORDER BY FIELD(c.status_repair,'Belum Diperbaiki','Menunggu Spare Part','Sudah Diperbaiki'), c.reported_at DESC LIMIT 200");
        $st->execute($args);
        $rows = $st->fetchAll();
        $limit = cfg('app')['escalation_hours'] * 3600;
        foreach ($rows as &$c) {
            $age = time() - strtotime($c['reported_at']);
            $c['overdue'] = $c['status_repair'] === 'Belum Diperbaiki' && $age > $limit;
            $c['age_h'] = (int) floor($age / 3600);
            $c['wa'] = ($u['role_id'] == ROLE_ADMIN && $c['pic_phone'])
                ? wa_link($c['pic_phone'], "Halo {$c['pic_name']}, pengaduan kerusakan di {$c['room_name']} (\"".mb_strimwidth($c['description'], 0, 80, '…').'") dilaporkan '.tgl($c['reported_at'])." dan masih berstatus Belum Diperbaiki selama {$c['age_h']} jam. Mohon segera ditindaklanjuti. Terima kasih. - Waka Sarpras")
                : null;
        }
        view('complaints/list', ['complaints' => $rows, 'statuses' => self::STATUSES, 'limitH' => cfg('app')['escalation_hours']]);
    }

    public function create(): void
    {
        view('complaints/form', ['rooms' => db()->query('SELECT id,room_name,location FROM rooms WHERE is_active=1 ORDER BY room_name')->fetchAll()]);
    }

    public function store(): void
    {
        $room = $this->id('room_id');
        $desc = $this->str('description');
        $r = db()->prepare('SELECT room_name,pic_id FROM rooms WHERE id=? AND is_active=1');
        $r->execute([$room]);
        if (! ($rm = $r->fetch())) {
            $this->back('complaint/create', 'error', 'Pilih ruang yang valid.');
        }
        if (mb_strlen($desc) < 10) {
            $this->back('complaint/create', 'error', 'Jelaskan kerusakan minimal 10 karakter.');
        }
        try {
            $path = save_photo($_FILES['photo'] ?? []);
        } catch (RuntimeException $ex) {
            $this->back('complaint/create', 'error', $ex->getMessage());
        }

        db()->prepare('INSERT INTO complaints (room_id,reporter_id,description,photo_path) VALUES (?,?,?,?)')
            ->execute([$room, $this->user()['id'], $desc, $path]);
        if ($rm['pic_id']) {
            notify((int) $rm['pic_id'], 'Pengaduan baru di '.$rm['room_name'].': '.mb_strimwidth($desc, 0, 90, '…'));
        }
        $this->back('complaint/index', 'ok', 'Pengaduan terkirim ke PIC ruang.');
    }

    /** Hanya PIC yang menjadi penanggung jawab ruang tersebut yang boleh mengubah status. */
    public function status(): void
    {
        $new = $this->str('status_repair');
        if (! in_array($new, self::STATUSES, true)) {
            $this->back('complaint/index', 'error', 'Status tidak valid.');
        }
        $st = db()->prepare('SELECT c.id,c.reporter_id,r.room_name FROM complaints c JOIN rooms r ON r.id=c.room_id WHERE c.id=? AND r.pic_id=?');
        $st->execute([$this->id(), $this->user()['id']]);
        if (! ($c = $st->fetch())) {
            http_response_code(403);
            $this->back('complaint/index', 'error', 'Ruangan ini bukan tanggung jawab Anda.');
        }

        db()->prepare('UPDATE complaints SET status_repair=?, handler_id=?, repair_note=?, updated_at=NOW() WHERE id=?')
            ->execute([$new, $this->user()['id'], $this->str('repair_note') ?: null, $c['id']]);
        notify((int) $c['reporter_id'], "Pengaduan di {$c['room_name']} kini berstatus: $new.");
        $this->back('complaint/index', 'ok', "Status diperbarui: $new.");
    }

    /** Admin boleh hapus semua; Guru hanya boleh hapus laporan miliknya sendiri. */
    public function delete(): void
    {
        $id = $this->id();
        $u = $this->user();

        // Ambil data pengaduan
        $st = db()->prepare('SELECT id, reporter_id, photo_path FROM complaints WHERE id=?');
        $st->execute([$id]);
        $c = $st->fetch();

        if (! $c) {
            $this->back('complaint/index', 'error', 'Pengaduan tidak ditemukan.');
        }

        // Otorisasi: Admin boleh hapus semua, Guru hanya miliknya sendiri
        if ((int) $u['role_id'] === ROLE_GURU && (int) $c['reporter_id'] !== (int) $u['id']) {
            $this->back('complaint/index', 'error', 'Anda hanya dapat menghapus laporan milik sendiri.');
        }

        // Hapus foto dari disk jika ada
        if (! empty($c['photo_path'])) {
            $file = __DIR__.'/../../public/'.$c['photo_path'];
            if (is_file($file)) {
                @unlink($file);
            }
        }

        db()->prepare('DELETE FROM complaints WHERE id=?')->execute([$id]);
        $this->back('complaint/index', 'ok', 'Pengaduan berhasil dihapus.');
    }
}
