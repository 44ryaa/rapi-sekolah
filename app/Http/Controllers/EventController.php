<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Notification;
use App\Models\Room;
use App\Services\IndonesianHolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Daftar sesi tetap sekolah.
     * Label, jam mulai, jam selesai.
     */
    public const SESSIONS = [
        1 => ['label' => 'Sesi 1  — 07:00 – 08:30', 'start' => '07:00', 'end' => '08:30'],
        2 => ['label' => 'Sesi 2  — 08:30 – 10:00', 'start' => '08:30', 'end' => '10:00'],
        3 => ['label' => 'Sesi 3  — 10:15 – 11:45', 'start' => '10:15', 'end' => '11:45'],
        4 => ['label' => 'Sesi 4  — 13:00 – 14:30', 'start' => '13:00', 'end' => '14:30'],
        5 => ['label' => 'Sesi 5  — 14:30 – 16:00', 'start' => '14:30', 'end' => '16:00'],
        6 => ['label' => 'Sesi Penuh — 07:00 – 16:00', 'start' => '07:00', 'end' => '16:00'],
    ];

    /** Tanggal minimal pengajuan: minimal H+1 dan lewati Sabtu/Minggu */
    private function minDate(): string
    {
        $d = now()->addDay();
        while ($d->isWeekend()) {
            $d->addDay();
        }

        return $d->format('Y-m-d');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $today = date('Y-m-d');
        $currentTime = date('H:i');

        // Urutkan sesuai tanggal sekarang (Hari ini & mendatang di urutan teratas)
        $query = Event::with(['room', 'requester'])
            ->orderByRaw('CASE WHEN date >= ? THEN 0 ELSE 1 END, date ASC, start_time ASC', [$today])
            ->limit(200);

        if ($user->isGuru()) {
            $query->where('pic_id', $user->id);
        }

        $events = $query->get()->map(function ($ev) use ($today, $currentTime) {
            // Label sesi
            $ev->session_label = $this->guessSessionLabel($ev->start_time, $ev->end_time);

            // Kategori waktu (Guru: Hari Ini, Yang Akan Datang, Terkini/Selesai)
            $evDate = $ev->date->format('Y-m-d');
            $evStart = substr($ev->start_time, 0, 5);
            $evEnd = substr($ev->end_time, 0, 5);

            if ($evDate === $today) {
                $ev->time_group = 'today';
            } elseif ($evDate > $today) {
                $ev->time_group = 'upcoming';
            } else {
                $ev->time_group = 'past';
            }

            // Status pelaksanaan operasional (PIC: Sedang Berlangsung, Belum Terlaksana, Sudah Terlaksana)
            if ($evDate > $today) {
                $ev->exec_status = 'Belum Terlaksana';
                $ev->exec_color = 'warn';
            } elseif ($evDate < $today) {
                $ev->exec_status = 'Sudah Terlaksana';
                $ev->exec_color = 'neutral';
            } else {
                if ($currentTime < $evStart) {
                    $ev->exec_status = 'Belum Terlaksana';
                    $ev->exec_color = 'warn';
                } elseif ($currentTime >= $evStart && $currentTime <= $evEnd) {
                    $ev->exec_status = 'Sedang Berlangsung';
                    $ev->exec_color = 'live';
                } else {
                    $ev->exec_status = 'Sudah Terlaksana';
                    $ev->exec_color = 'neutral';
                }
            }

            return $ev;
        });

        $rooms = Room::where('is_active', 1)->orderBy('room_name')->get();

        return view('events.list', compact('events', 'rooms'));
    }

    public function create()
    {
        $rooms = Room::where('is_active', 1)->orderBy('room_name')->get();
        $sessions = self::SESSIONS;
        $minDate = $this->minDate();

        return view('events.form', compact('rooms', 'sessions', 'minDate'));
    }

    /** Kembalikan sesi yang sudah terpakai di ruang+tanggal tertentu (untuk UI). */
    public function slots(Request $request)
    {
        $roomId = (int) $request->input('room_id', 0);
        $date = $request->query('date', '');

        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $dayOfWeek = (int) date('N', strtotime($date));
            if ($dayOfWeek >= 6) {
                return response()->json([
                    'weekend' => true,
                    'error' => 'Peminjaman ruangan tidak tersedia pada hari Sabtu dan Minggu.',
                    'sessions' => [],
                ]);
            }

            $holiday = IndonesianHolidayService::checkHoliday($date);
            if ($holiday) {
                $holidayLabel = $holiday['is_cuti'] ? 'Cuti Bersama' : 'Hari Libur Nasional';

                return response()->json([
                    'holiday' => true,
                    'holiday_name' => $holiday['name'],
                    'error' => "Tanggal ini bertepatan dengan {$holidayLabel}: {$holiday['name']}.",
                    'sessions' => [],
                ]);
            }
        }

        $booked = Event::where('room_id', $roomId)
            ->where('date', $date)
            ->whereIn('status', ['Diajukan', 'Disetujui'])
            ->orderBy('start_time')
            ->get(['event_name', 'start_time', 'end_time']);

        // Tandai setiap sesi apakah konflik dengan booking yang ada
        $sessionStatus = [];
        foreach (self::SESSIONS as $num => $s) {
            $taken = false;
            $takenBy = '';
            foreach ($booked as $b) {
                // Sesi dianggap bentrok jika ada irisan waktu
                if ($s['start'] < substr($b->end_time, 0, 5) && $s['end'] > substr($b->start_time, 0, 5)) {
                    $taken = true;
                    $takenBy = $b->event_name;
                    break;
                }
            }
            $sessionStatus[$num] = ['label' => $s['label'], 'taken' => $taken, 'by' => $takenBy];
        }

        return response()->json(['sessions' => $sessionStatus]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $room = (int) $request->input('room_id', 0);
        $name = trim($request->input('event_name', ''));
        $date = $request->input('date', '');
        $sesStart = (int) $request->input('session_start', 0);
        $sesEnd = (int) $request->input('session_end', 0);

        // Validasi nama
        if ($name === '' || mb_strlen($name) > 150) {
            return back()->withErrors(['event_name' => 'Nama kegiatan wajib diisi (maks. 150 karakter).'])->withInput();
        }

        // Validasi tanggal: minimal H+1
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            ! checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
            return back()->withErrors(['date' => 'Tanggal tidak valid.'])->withInput();
        }

        if ($date <= date('Y-m-d')) {
            return back()->withErrors(['date' => 'Pengajuan harus dilakukan minimal 1 hari sebelum tanggal kegiatan (H-1).'])->withInput();
        }

        $dayOfWeek = (int) date('N', strtotime($date));
        if ($dayOfWeek >= 6) {
            return back()->withErrors(['date' => 'Pengajuan peminjaman ruangan tidak dapat dilakukan pada hari Sabtu atau Minggu.'])->withInput();
        }

        $holiday = IndonesianHolidayService::checkHoliday($date);
        if ($holiday && ! $user->isAdmin() && ! $user->isWaka()) {
            return back()->withErrors([
                'date' => "Tanggal kegiatan bertepatan dengan {$holiday['name']}. Peminjaman ruangan ditiadakan pada hari libur nasional.",
            ])->withInput();
        }

        // Validasi sesi
        if (! isset(self::SESSIONS[$sesStart])) {
            return back()->withErrors(['session_start' => 'Pilih sesi mulai yang valid.'])->withInput();
        }

        if ($sesStart === 6) {
            // Sesi Penuh: 07:00 - 16:00 (sesi selesai tidak usah digunakan)
            $sesEnd = 6;
            $start = '07:00';
            $end = '16:00';
        } else {
            if (! isset(self::SESSIONS[$sesEnd])) {
                return back()->withErrors(['session_end' => 'Pilih sesi selesai yang valid.'])->withInput();
            }

            if ($sesEnd === 6) {
                return back()->withErrors(['session_end' => 'Pilih sesi selesai normal (1–5).'])->withInput();
            }

            // Sesi normal: end harus >= start
            if (self::SESSIONS[$sesEnd]['end'] <= self::SESSIONS[$sesStart]['start']) {
                return back()->withErrors(['session_end' => 'Sesi selesai harus setelah sesi mulai.'])->withInput();
            }

            $start = self::SESSIONS[$sesStart]['start'];
            $end = self::SESSIONS[$sesEnd]['end'];
        }

        // Validasi ruang
        if (! Room::where('id', $room)->where('is_active', 1)->exists()) {
            return back()->withErrors(['room_id' => 'Ruang tidak tersedia.'])->withInput();
        }

        // Dukungan Peminjaman Multi-Hari
        $endDate = $request->input('end_date');
        $dates = [];
        $curr = Carbon::parse($date);
        $last = $endDate ? Carbon::parse($endDate) : $curr->copy();

        if ($last->lt($curr)) {
            return back()->withErrors(['end_date' => 'Tanggal selesai harus sama atau setelah tanggal mulai kegiatan.'])->withInput();
        }

        if ($curr->diffInDays($last) > 14) {
            return back()->withErrors(['end_date' => 'Maksimal rentang peminjaman multi-hari adalah 14 hari.'])->withInput();
        }

        while ($curr->lte($last)) {
            if (! $curr->isWeekend()) {
                $dates[] = $curr->format('Y-m-d');
            }
            $curr->addDay();
        }

        if (empty($dates)) {
            return back()->withErrors(['date' => 'Rentang tanggal yang dipilih hanya mencakup akhir pekan (Sabtu/Minggu).'])->withInput();
        }

        // Cek konflik untuk semua tanggal
        foreach ($dates as $d) {
            $conflict = Event::hasConflict($room, $d, $start, $end);
            if ($conflict) {
                $dFormatted = date('d/m/Y', strtotime($d));
                $sl = $this->guessSessionLabel($conflict->start_time, $conflict->end_time);

                return back()->withErrors([
                    'session_start' => "Bentrok pada tanggal {$dFormatted}: Ruang sudah dipesan untuk \"{$conflict->event_name}\" ({$sl}). Pilih tanggal atau ruang lain.",
                ])->withInput();
            }
        }

        $canAutoApprove = $user->isAdmin() || $user->isWaka();
        $totalDays = count($dates);

        foreach ($dates as $idx => $d) {
            $daySuffix = $totalDays > 1 ? ' (Hari '.($idx + 1).'/'.$totalDays.')' : '';
            Event::create([
                'room_id' => $room,
                'pic_id' => $user->id,
                'event_name' => $name.$daySuffix,
                'date' => $d,
                'start_time' => $start,
                'end_time' => $end,
                'status' => $canAutoApprove ? 'Disetujui' : 'Diajukan',
                'approved_by' => $canAutoApprove ? $user->id : null,
            ]);
        }

        $msgSuccess = $totalDays > 1
            ? "Peminjaman multi-hari ({$totalDays} hari) berhasil diajukan".($canAutoApprove ? ' dan disetujui.' : '.')
            : ($canAutoApprove ? 'Agenda disimpan dan langsung disetujui.' : 'Pengajuan terkirim. Menunggu persetujuan Waka.');

        return redirect()->route('events.index')->with('success', $msgSuccess);
    }

    public function review(Request $request)
    {
        $ok = $request->input('decision') === 'approve';
        $ev = Event::where('id', (int) $request->input('id'))->where('status', 'Diajukan')->firstOrFail();

        $reason = null;
        if (! $ok) {
            $reason = trim($request->input('reject_reason', ''));
            if (mb_strlen($reason) < 5) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Alasan penolakan wajib diisi (minimal 5 karakter).'], 422);
                }

                return back()->withErrors(['reject_reason' => 'Alasan penolakan peminjaman wajib diisi (minimal 5 karakter).']);
            }
        }

        $ev->update([
            'status' => $ok ? 'Disetujui' : 'Ditolak',
            'approved_by' => $request->user()->id,
            'reject_reason' => $reason,
        ]);

        if ($ev->pic_id != $request->user()->id) {
            $approverTitle = $request->user()->isWaka() ? 'Waka Sarpras' : 'Admin';
            $msg = $ok
                ? "Pengajuan peminjaman \"{$ev->event_name}\" (".$ev->date->format('d M Y').") DISETUJUI ✓ oleh {$approverTitle}."
                : "Pengajuan peminjaman \"{$ev->event_name}\" (".$ev->date->format('d M Y').") DITOLAK ✗ oleh {$approverTitle}. Alasan: {$reason}";

            Notification::create([
                'user_id' => $ev->pic_id,
                'message' => $msg,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $ok ? 'Pengajuan peminjaman disetujui.' : 'Pengajuan peminjaman ditolak.',
                'status' => $ok ? 'Disetujui' : 'Ditolak',
                'reject_reason' => $reason,
                'id' => $ev->id,
            ]);
        }

        return back()->with('success', $ok ? 'Pengajuan peminjaman disetujui.' : 'Pengajuan peminjaman ditolak.');
    }

    public function move(Request $request)
    {
        $newRoom = (int) $request->input('new_room_id', 0);
        $reason = trim($request->input('reason', ''));
        if (mb_strlen($reason) < 5) {
            return back()->withErrors(['reason' => 'Alasan pemindahan wajib diisi (minimal 5 karakter).']);
        }

        $ev = Event::whereIn('status', ['Diajukan', 'Disetujui'])->findOrFail((int) $request->input('id'));
        $room = Room::where('id', $newRoom)->where('is_active', 1)->firstOrFail();

        if ($newRoom == $ev->room_id) {
            return back()->withErrors(['new_room_id' => 'Pilih ruang tujuan yang berbeda.']);
        }

        $conflict = Event::hasConflict($newRoom, $ev->date->format('Y-m-d'), $ev->start_time, $ev->end_time, $ev->id);
        if ($conflict) {
            return back()->withErrors(['new_room_id' => "Ruang tujuan bentrok dengan \"{$conflict->event_name}\"."]);
        }

        $ev->update(['room_id' => $newRoom, 'move_reason' => $reason]);

        if ($ev->pic_id != $request->user()->id) {
            $sl = $this->guessSessionLabel($ev->start_time, $ev->end_time);
            Notification::create([
                'user_id' => $ev->pic_id,
                'message' => "Agenda \"{$ev->event_name}\" ({$ev->date->format('d M Y')} {$sl}) dipindahkan ke {$room->room_name}. Alasan: {$reason}",
            ]);
        }

        return back()->with('success', 'Agenda dipindahkan ke '.$room->room_name.'. Peminjam diberi notifikasi.');
    }

    /** Cari label sesi berdasarkan jam mulai & selesai yang tersimpan. */
    private function guessSessionLabel(string $start, string $end): string
    {
        $s = substr($start, 0, 5);
        $e = substr($end, 0, 5);
        foreach (self::SESSIONS as $sess) {
            if ($sess['start'] === $s && $sess['end'] === $e) {
                return $sess['label'];
            }
        }

        return "{$s}–{$e}";
    }
}
