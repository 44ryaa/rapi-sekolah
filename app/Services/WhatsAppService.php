<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Kirim pesan OTP via WhatsApp.
     * Otomatis menggunakan gateway Fonnte jika token tersedia,
     * dengan fallback ke URL wa.me.
     *
     * @return array{auto_sent: bool, phone: string, wa_url: string, message: string}
     */
    public static function sendOtp(string $phone, string $otp, User $user): array
    {
        $normalizedPhone = self::normalizePhone($phone);
        $message = "*RAPI-Sekolah* — Sistem Sarpras Terpadu\n\n"
            ."Halo *{$user->fullname}*,\n"
            ."Kode OTP reset password akun Anda (*{$user->username}*) adalah:\n\n"
            ."*{$otp}*\n\n"
            .'Kode ini berlaku selama 15 menit. Harap tidak membagikan kode ini kepada siapa pun demi keamanan akun Anda.';

        $waUrl = 'https://wa.me/'.$normalizedPhone.'?text='.urlencode($message);
        $token = env('WHATSAPP_TOKEN', env('FONNTE_TOKEN'));

        // Jika token API gateway tersedia, kirim langsung di latar belakang
        if (! empty($token)) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->withHeaders([
                        'Authorization' => $token,
                    ])
                    ->post('https://api.fonnte.com/send', [
                        'target' => $normalizedPhone,
                        'message' => $message,
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (($json['status'] ?? false) === true || ($json['status'] ?? '') === 'true') {
                        Log::info("WhatsApp OTP berhasil dikirim otomatis ke {$normalizedPhone} via Fonnte.");

                        return [
                            'auto_sent' => true,
                            'phone' => $normalizedPhone,
                            'wa_url' => $waUrl,
                            'message' => 'Kode OTP berhasil dikirimkan secara otomatis ke WhatsApp nomor '.$normalizedPhone,
                        ];
                    }
                }

                Log::warning('Respon gagal dari WhatsApp gateway: '.$response->body());
            } catch (\Throwable $e) {
                Log::warning('Gagal menghubungi WhatsApp gateway: '.$e->getMessage());
            }
        }

        // Fallback: Kirim via tautan interaktif WhatsApp Web / App
        return [
            'auto_sent' => false,
            'phone' => $normalizedPhone,
            'wa_url' => $waUrl,
            'message' => 'Kode OTP disiapkan untuk WhatsApp nomor '.$normalizedPhone,
        ];
    }

    /**
     * Kirim pesan teks WhatsApp generik (otomatis jika ada token, fallback wa.me)
     */
    public static function sendMessage(string $phone, string $message): array
    {
        $normalizedPhone = self::normalizePhone($phone);
        $waUrl = 'https://wa.me/'.$normalizedPhone.'?text='.urlencode($message);
        $token = env('WHATSAPP_TOKEN', env('FONNTE_TOKEN'));

        if (! empty($token)) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->withHeaders(['Authorization' => $token])
                    ->post('https://api.fonnte.com/send', [
                        'target' => $normalizedPhone,
                        'message' => $message,
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (($json['status'] ?? false) === true || ($json['status'] ?? '') === 'true') {
                        Log::info("Pesan WhatsApp berhasil dikirim ke {$normalizedPhone}");

                        return ['auto_sent' => true, 'phone' => $normalizedPhone, 'wa_url' => $waUrl];
                    }
                }
                Log::warning('Respon gagal dari gateway WA: '.$response->body());
            } catch (\Throwable $e) {
                Log::warning('Gagal menghubungi gateway WA: '.$e->getMessage());
            }
        }

        return ['auto_sent' => false, 'phone' => $normalizedPhone, 'wa_url' => $waUrl];
    }

    /**
     * Notifikasi pengajuan peminjaman ruangan baru ke Waka/PIC
     */
    public static function notifyNewEvent($event): void
    {
        // Cari Waka atau Admin
        $recipients = User::whereIn('role_id', [Role::WAKA, Role::ADMIN])
            ->whereNotNull('phone_number')
            ->get();

        $roomName = $event->room->room_name ?? 'Ruangan';
        $requesterName = $event->requester->fullname ?? 'Peminjam';
        $dateStr = date('d/m/Y', strtotime($event->date));

        $msg = "*RAPI-Sekolah* 🔔 *Pengajuan Ruangan Baru*\n\n"
            ."Halo Bapak/Ibu Waka/Admin,\n"
            ."Terdapat pengajuan peminjaman ruangan baru:\n\n"
            ."📌 *Ruang:* {$roomName}\n"
            ."👤 *Peminjam:* {$requesterName}\n"
            ."📝 *Kegiatan:* {$event->event_name}\n"
            ."📅 *Tanggal:* {$dateStr}\n"
            ."⏰ *Waktu:* {$event->start_time} - {$event->end_time}\n\n"
            ."Silakan login ke sistem untuk meninjau dan menyetujui pengajuan:\n"
            .'https://rapi-sekolah.web.id/events';

        foreach ($recipients as $r) {
            if (! empty($r->phone_number)) {
                self::sendMessage($r->phone_number, $msg);
            }
        }
    }

    /**
     * Notifikasi status persetujuan (ACC/Tolak) ke Peminjam
     */
    public static function notifyEventStatus($event, bool $isApproved, ?string $reason = null): void
    {
        $requester = $event->requester;
        if (! $requester || empty($requester->phone_number)) {
            return;
        }

        $roomName = $event->room->room_name ?? 'Ruangan';
        $statusText = $isApproved ? 'DISETUJUI ✅' : 'DITOLAK ❌';
        $dateStr = date('d/m/Y', strtotime($event->date));

        $msg = "*RAPI-Sekolah* — Status Peminjaman Ruangan\n\n"
            ."Halo *{$requester->fullname}*,\n"
            ."Pengajuan peminjaman ruangan Anda:\n\n"
            ."📌 *Ruang:* {$roomName}\n"
            ."📝 *Kegiatan:* {$event->event_name}\n"
            ."📅 *Tanggal:* {$dateStr}\n"
            ."📊 *Status:* *{$statusText}*\n";

        if (! $isApproved && ! empty($reason)) {
            $msg .= "⚠️ *Alasan:* {$reason}\n";
        }

        $msg .= "\nTerima kasih atas kerja samanya.";

        self::sendMessage($requester->phone_number, $msg);
    }

    /**
     * Normalisasi nomor telepon ke format internasional (62xxxx)
     */
    public static function normalizePhone(string $p): string
    {
        $p = preg_replace('/\D/', '', $p);
        if (str_starts_with($p, '08')) {
            $p = '628'.substr($p, 2);
        } elseif (str_starts_with($p, '8')) {
            $p = '628'.substr($p, 1);
        }

        return $p;
    }
}
