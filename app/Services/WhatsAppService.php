<?php

namespace App\Services;

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
