<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IndonesianHolidayService
{
    /**
     * Database Hari Libur Nasional & Cuti Bersama Resmi (SKB 3 Menteri)
     */
    private const OFFICIAL_HOLIDAYS = [
        // ── 2026 (SKB 3 Menteri Resmi) ──────────────────────────────────────
        '2026-01-01' => ['name' => 'Tahun Baru 2026 Masehi', 'is_cuti' => false],
        '2026-01-16' => ['name' => 'Isra Mikraj Nabi Muhammad SAW', 'is_cuti' => false],
        '2026-02-16' => ['name' => 'Cuti Bersama Tahun Baru Imlek 2577', 'is_cuti' => true],
        '2026-02-17' => ['name' => 'Tahun Baru Imlek 2577 Kongzili', 'is_cuti' => false],
        '2026-03-18' => ['name' => 'Cuti Bersama Hari Suci Nyepi', 'is_cuti' => true],
        '2026-03-19' => ['name' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'is_cuti' => false],
        '2026-03-20' => ['name' => 'Cuti Bersama Idul Fitri 1447 H', 'is_cuti' => true],
        '2026-03-21' => ['name' => 'Hari Raya Idul Fitri 1447 H (Hari 1)', 'is_cuti' => false],
        '2026-03-22' => ['name' => 'Hari Raya Idul Fitri 1447 H (Hari 2)', 'is_cuti' => false],
        '2026-03-23' => ['name' => 'Cuti Bersama Idul Fitri 1447 H', 'is_cuti' => true],
        '2026-03-24' => ['name' => 'Cuti Bersama Idul Fitri 1447 H', 'is_cuti' => true],
        '2026-04-03' => ['name' => 'Wafat Yesus Kristus (Jumat Agung)', 'is_cuti' => false],
        '2026-04-05' => ['name' => 'Kebangkitan Yesus Kristus (Paskah)', 'is_cuti' => false],
        '2026-05-01' => ['name' => 'Hari Buruh Internasional', 'is_cuti' => false],
        '2026-05-14' => ['name' => 'Kenaikan Yesus Kristus', 'is_cuti' => false],
        '2026-05-15' => ['name' => 'Cuti Bersama Kenaikan Yesus Kristus', 'is_cuti' => true],
        '2026-05-27' => ['name' => 'Hari Raya Idul Adha 1447 H', 'is_cuti' => false],
        '2026-05-28' => ['name' => 'Cuti Bersama Idul Adha 1447 H', 'is_cuti' => true],
        '2026-05-31' => ['name' => 'Hari Raya Waisak 2570 BE', 'is_cuti' => false],
        '2026-06-01' => ['name' => 'Hari Lahir Pancasila', 'is_cuti' => false],
        '2026-06-16' => ['name' => 'Tahun Baru Islam 1448 H', 'is_cuti' => false],
        '2026-08-17' => ['name' => 'Hari Proklamasi Kemerdekaan RI', 'is_cuti' => false],
        '2026-08-25' => ['name' => 'Maulid Nabi Muhammad SAW', 'is_cuti' => false],
        '2026-12-24' => ['name' => 'Cuti Bersama Hari Raya Natal', 'is_cuti' => true],
        '2026-12-25' => ['name' => 'Hari Raya Natal', 'is_cuti' => false],

        // ── 2027 (Prakiraan Kalender Nasional Indonesia) ────────────────────
        '2027-01-01' => ['name' => 'Tahun Baru 2027 Masehi', 'is_cuti' => false],
        '2027-01-05' => ['name' => 'Isra Mikraj Nabi Muhammad SAW', 'is_cuti' => false],
        '2027-02-06' => ['name' => 'Tahun Baru Imlek 2578 Kongzili', 'is_cuti' => false],
        '2027-03-09' => ['name' => 'Hari Raya Idul Fitri 1448 H (Hari 1)', 'is_cuti' => false],
        '2027-03-10' => ['name' => 'Hari Raya Idul Fitri 1448 H (Hari 2)', 'is_cuti' => false],
        '2027-03-11' => ['name' => 'Hari Suci Nyepi (Tahun Baru Saka 1949)', 'is_cuti' => false],
        '2027-03-26' => ['name' => 'Wafat Yesus Kristus (Jumat Agung)', 'is_cuti' => false],
        '2027-03-28' => ['name' => 'Kebangkitan Yesus Kristus (Paskah)', 'is_cuti' => false],
        '2027-05-01' => ['name' => 'Hari Buruh Internasional', 'is_cuti' => false],
        '2027-05-06' => ['name' => 'Kenaikan Yesus Kristus', 'is_cuti' => false],
        '2027-05-16' => ['name' => 'Hari Raya Idul Adha 1448 H', 'is_cuti' => false],
        '2027-05-21' => ['name' => 'Hari Raya Waisak 2571 BE', 'is_cuti' => false],
        '2027-06-01' => ['name' => 'Hari Lahir Pancasila', 'is_cuti' => false],
        '2027-06-06' => ['name' => 'Tahun Baru Islam 1449 H', 'is_cuti' => false],
        '2027-08-15' => ['name' => 'Maulid Nabi Muhammad SAW', 'is_cuti' => false],
        '2027-08-17' => ['name' => 'Hari Proklamasi Kemerdekaan RI', 'is_cuti' => false],
        '2027-12-25' => ['name' => 'Hari Raya Natal', 'is_cuti' => false],
    ];

    /**
     * Dapatkan semua hari libur pada bulan tertentu (format YYYY-MM).
     * Mendukung sinkronisasi dinamis dari tahun 2026 hingga seterusnya.
     *
     * @return array<string, array{name: string, is_cuti: bool}>
     */
    public static function getHolidaysForMonth(string $yearMonth): array
    {
        [$yearStr, $monthStr] = explode('-', $yearMonth);
        $year = (int) $yearStr;
        $allYearHolidays = self::getHolidaysForYear($year);

        $result = [];
        foreach ($allYearHolidays as $date => $info) {
            if (str_starts_with($date, $yearMonth)) {
                $result[$date] = $info;
            }
        }

        return $result;
    }

    /**
     * Dapatkan semua hari libur pada tahun tertentu.
     * Caching 30 hari untuk efisiensi tinggi.
     *
     * @return array<string, array{name: string, is_cuti: bool}>
     */
    public static function getHolidaysForYear(int $year): array
    {
        $cacheKey = "indonesia_holidays_{$year}";

        return Cache::remember($cacheKey, 3600 * 24 * 30, function () use ($year) {
            $holidays = [];

            // 1. Ambil data built-in lokal yang 100% akurat SKB 3 Menteri
            foreach (self::OFFICIAL_HOLIDAYS as $date => $info) {
                if (str_starts_with($date, (string) $year)) {
                    $holidays[$date] = $info;
                }
            }

            // 2. Tambahkan hari libur nasional berulang (fixed-date) untuk tahun berjalan
            $fixedHolidays = [
                sprintf('%04d-01-01', $year) => ['name' => "Tahun Baru {$year} Masehi", 'is_cuti' => false],
                sprintf('%04d-05-01', $year) => ['name' => 'Hari Buruh Internasional', 'is_cuti' => false],
                sprintf('%04d-06-01', $year) => ['name' => 'Hari Lahir Pancasila', 'is_cuti' => false],
                sprintf('%04d-08-17', $year) => ['name' => 'Hari Kemerdekaan Republik Indonesia', 'is_cuti' => false],
                sprintf('%04d-12-25', $year) => ['name' => 'Hari Raya Natal', 'is_cuti' => false],
            ];

            foreach ($fixedHolidays as $date => $info) {
                if (! isset($holidays[$date])) {
                    $holidays[$date] = $info;
                }
            }

            // 3. Coba sinkronisasi online via API publik hari libur Indonesia (jika ada koneksi internet)
            try {
                $response = Http::timeout(2)->get("https://dayoffapi.vercel.app/api?year={$year}");
                if ($response->successful()) {
                    $apiData = $response->json();
                    if (is_array($apiData)) {
                        foreach ($apiData as $item) {
                            $d = $item['holiday_date'] ?? null;
                            $n = $item['holiday_name'] ?? null;
                            $isCuti = (bool) ($item['is_cuti'] ?? false);
                            if ($d && $n) {
                                $holidays[$d] = [
                                    'name' => $n,
                                    'is_cuti' => $isCuti,
                                ];
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Gunakan dataset lokal jika offline / intranet sekolah
            }

            ksort($holidays);

            return $holidays;
        });
    }

    /**
     * Cek apakah suatu tanggal adalah hari libur nasional atau cuti bersama.
     * Mengembalikan nama hari libur jika ya, atau null jika bukan.
     */
    public static function checkHoliday(string $date): ?array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $year = (int) substr($date, 0, 4);
        $yearHolidays = self::getHolidaysForYear($year);

        return $yearHolidays[$date] ?? null;
    }
}
