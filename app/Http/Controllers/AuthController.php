<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordOtpMail;
use App\Models\Notification;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('calendar');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $fails = session('login_fails', ['n' => 0, 't' => time()]);
        if (time() - $fails['t'] > 300) {
            $fails = ['n' => 0, 't' => time()];
        }
        if ($fails['n'] >= 5) {
            return back()->withErrors(['username' => 'Terlalu banyak percobaan gagal. Coba lagi dalam 5 menit.']);
        }

        $user = User::where('username', $request->input('username'))->first();
        if ($user && Hash::check($request->input('password', ''), $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            session()->forget('login_fails');
            $request->session()->regenerate();

            return redirect()->route('calendar');
        }

        $fails['n']++;
        session(['login_fails' => $fails]);

        return back()->withErrors(['username' => 'Username atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('calendar')->with('success', 'Anda sudah keluar dari sistem.');
    }

    public function forgotForm()
    {
        return view('auth.forgot');
    }

    public function forgot(Request $request)
    {
        $input = trim($request->input('username', ''));
        $channel = $request->input('channel', 'email'); // 'email' atau 'whatsapp'
        $customPhone = trim($request->input('custom_phone', ''));
        $customEmail = trim($request->input('custom_email', ''));

        if ($input === '') {
            return back()->withErrors(['username' => 'Username atau Email wajib diisi.'])->withInput();
        }

        // Cari user berdasarkan username atau email
        $user = User::where('username', $input)->orWhere('email', $input)->first();
        if (! $user) {
            return back()->withErrors(['username' => 'Akun dengan username atau email tersebut tidak ditemukan.'])->withInput();
        }

        // Generate 6 digit angka OTP
        $otp = (string) random_int(100000, 999999);

        // Hapus token lama & simpan hash OTP baru (berlaku 15 menit)
        \DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $otp),
            'created_at' => now(),
        ]);

        // Simpan state reset di session
        session([
            'reset_email' => $user->email,
            'reset_channel' => $channel,
            'reset_user_id' => $user->id,
        ]);

        $waUrl = null;
        if ($channel === 'whatsapp') {
            // Tentukan nomor WhatsApp: gunakan custom_phone jika diisi, jika tidak gunakan phone_number dari user atau default '0895611029000'
            $targetPhone = ! empty($customPhone) ? $customPhone : ($user->phone_number ?: '0895611029000');
            $normalizedPhone = WhatsAppService::normalizePhone($targetPhone);

            if (strlen($normalizedPhone) < 9) {
                return back()->withErrors(['custom_phone' => 'Nomor WhatsApp tidak valid. Silakan masukkan nomor WhatsApp yang benar.'])->withInput();
            }

            session(['reset_target_phone' => $normalizedPhone]);

            // Kirim via WhatsApp Service (Otomatis Fonnte jika token diset, atau fallback ke wa.me)
            $waResult = WhatsAppService::sendOtp($normalizedPhone, $otp, $user);
            $waUrl = $waResult['wa_url'];
            session([
                'reset_wa_url' => $waUrl,
                'reset_wa_auto_sent' => $waResult['auto_sent'],
            ]);

            $msg = $waResult['auto_sent']
                ? "Kode OTP telah otomatis terkirim ke WhatsApp nomor {$normalizedPhone}!"
                : "Kode OTP siap dikirim ke WhatsApp nomor {$normalizedPhone}.";
        } else {
            // Pengiriman via Gmail / Email (bisa email terdaftar atau email yang diketik user)
            $targetEmail = ! empty($customEmail) ? $customEmail : $user->email;
            if (! filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                return back()->withErrors(['custom_email' => 'Alamat email penerima tidak valid.'])->withInput();
            }

            session(['reset_target_email' => $targetEmail]);

            try {
                Mail::to($targetEmail)->send(new ResetPasswordOtpMail($user, $otp));
                $msg = "Kode OTP telah dikirimkan ke: {$targetEmail}. Silakan cek kotak masuk atau folder spam.";
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim email reset password: '.$e->getMessage());
                $msg = "Kode OTP berhasil digenerate untuk email {$targetEmail}.";
            }
        }

        return redirect()->route('verify.otp')
            ->with('success', $msg)
            ->with('dev_otp', $otp)
            ->with('wa_url', $waUrl);
    }

    public function verifyOtpForm(Request $request)
    {
        $email = session('reset_email');
        if (! $email) {
            return redirect()->route('forgot')->withErrors(['username' => 'Sesi reset password tidak ditemukan. Silakan masukkan akun Anda.']);
        }

        $user = User::where('email', $email)->first();
        $channel = session('reset_channel', 'email');
        $phone = session('reset_target_phone', $user?->phone_number);
        $targetEmail = session('reset_target_email', $email);
        $waUrl = session('reset_wa_url');

        return view('auth.verify_otp', compact('email', 'targetEmail', 'channel', 'phone', 'waUrl', 'user'));
    }

    public function verifyOtp(Request $request)
    {
        $email = session('reset_email');
        if (! $email) {
            return redirect()->route('forgot')->withErrors(['username' => 'Sesi kedaluwarsa. Silakan ulangi.']);
        }

        $otp = trim($request->input('otp', ''));
        if (strlen($otp) < 6) {
            return back()->withErrors(['otp' => 'Masukkan 6 digit kode OTP yang valid.'])->withInput();
        }

        $row = \DB::table('password_reset_tokens')->where('email', $email)->first();
        if (! $row || now()->diffInMinutes($row->created_at) > 15) {
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa (maks. 15 menit). Silakan minta kode baru.']);
        }

        if ($row->token !== hash('sha256', $otp)) {
            return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah. Silakan periksa kembali.'])->withInput();
        }

        // Tandai OTP terverifikasi dan generate reset token
        $resetToken = bin2hex(random_bytes(32));
        \DB::table('password_reset_tokens')->where('email', $email)->update([
            'token' => hash('sha256', $resetToken),
            'created_at' => now(),
        ]);

        session(['reset_verified_token' => $resetToken]);

        return redirect()->route('reset', ['token' => $resetToken])
            ->with('success', 'Kode OTP berhasil diverifikasi! Silakan buat password baru Anda.');
    }

    public function resendOtp(Request $request)
    {
        $email = session('reset_email');
        if (! $email) {
            return redirect()->route('forgot');
        }

        $user = User::where('email', $email)->firstOrFail();
        $channel = session('reset_channel', 'email');
        $customPhone = trim($request->input('custom_phone', ''));

        $otp = (string) random_int(100000, 999999);
        \DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $otp),
            'created_at' => now(),
        ]);

        $waUrl = null;
        if ($channel === 'whatsapp') {
            $targetPhone = ! empty($customPhone) ? $customPhone : (session('reset_target_phone') ?: ($user->phone_number ?: '0895611029000'));
            $normalizedPhone = WhatsAppService::normalizePhone($targetPhone);

            if (strlen($normalizedPhone) < 9) {
                return back()->withErrors(['custom_phone' => 'Nomor WhatsApp tidak valid.']);
            }

            session(['reset_target_phone' => $normalizedPhone]);

            $waResult = WhatsAppService::sendOtp($normalizedPhone, $otp, $user);
            $waUrl = $waResult['wa_url'];
            session([
                'reset_wa_url' => $waUrl,
                'reset_wa_auto_sent' => $waResult['auto_sent'],
            ]);

            $msg = $waResult['auto_sent']
                ? "Kode OTP baru telah otomatis terkirim ke WhatsApp nomor {$normalizedPhone}!"
                : "Kode OTP baru telah disiapkan untuk WhatsApp nomor {$normalizedPhone}.";
        } else {
            $customEmail = trim($request->input('custom_email', ''));
            $targetEmail = ! empty($customEmail) ? $customEmail : (session('reset_target_email') ?: $user->email);
            session(['reset_target_email' => $targetEmail]);

            try {
                Mail::to($targetEmail)->send(new ResetPasswordOtpMail($user, $otp));
                $msg = "Kode OTP baru telah dikirimkan ke Gmail {$targetEmail}.";
            } catch (\Throwable $e) {
                $msg = 'Kode OTP baru telah dibuat.';
            }
        }

        return redirect()->route('verify.otp')
            ->with('success', $msg)
            ->with('dev_otp', $otp)
            ->with('wa_url', $waUrl);
    }

    public function resetForm(Request $request)
    {
        $token = $request->query('token') ?? session('reset_verified_token') ?? old('token') ?? '';
        if (! $token) {
            return redirect()->route('forgot')->withErrors(['token' => 'Sesi reset password tidak ditemukan. Silakan verifikasi ulang.']);
        }

        $row = \DB::table('password_reset_tokens')->where('token', hash('sha256', $token))->first();
        if (! $row || now()->diffInMinutes($row->created_at) > 30) {
            return redirect()->route('forgot')->withErrors(['token' => 'Tautan reset tidak valid atau sudah kedaluwarsa (maks. 30 menit).']);
        }

        return view('auth.reset', compact('token'));
    }

    public function reset(Request $request)
    {
        $token = $request->input('token', '');
        $row = \DB::table('password_reset_tokens')->where('token', hash('sha256', $token))->first();
        if (! $row || now()->diffInMinutes($row->created_at) > 30) {
            return redirect()->route('forgot')->withErrors(['token' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
        }
        $user = User::where('email', $row->email)->firstOrFail();

        $pw = $request->input('password', '');
        if (strlen($pw) < 6) {
            return back()->withErrors(['password' => 'Password baru minimal 6 karakter.'])
                ->withInput()
                ->with('token', $token);
        }
        if ($pw !== $request->input('password2', '')) {
            return back()->withErrors(['password2' => 'Konfirmasi password tidak cocok.'])
                ->withInput()
                ->with('token', $token);
        }

        $user->update(['password' => Hash::make($pw)]);
        \DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        session()->forget(['reset_email', 'reset_channel', 'reset_user_id', 'reset_target_phone', 'reset_wa_url', 'reset_verified_token']);

        return redirect()->route('login')->with('success', 'Password berhasil diperbarui! Silakan masuk dengan password baru.');
    }

    public function profileForm()
    {
        return view('auth.profile');
    }

    public function profileSave(Request $request)
    {
        $name = trim($request->input('fullname', ''));
        $phone = trim($request->input('phone_number', ''));
        if ($name === '' || strlen($this->normalizePhone($phone)) < 9) {
            return back()->withErrors(['fullname' => 'Nama dan nomor HP yang valid wajib diisi.']);
        }
        $request->user()->update(['fullname' => $name, 'phone_number' => $phone]);

        return back()->with('success', 'Profil disimpan.');
    }

    public function passwordSave(Request $request)
    {
        $user = $request->user();
        if (! Hash::check($request->input('current_password', ''), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }
        $new = $request->input('new_password', '');
        if (strlen($new) < 6) {
            return back()->withErrors(['new_password' => 'Password baru minimal 6 karakter.']);
        }
        if ($new !== $request->input('new_password2', '')) {
            return back()->withErrors(['new_password2' => 'Konfirmasi password baru tidak sama.']);
        }
        $user->update(['password' => Hash::make($new)]);

        return back()->with('success', 'Password berhasil diganti.');
    }

    public function readNotifications(Request $request)
    {
        Notification::where('user_id', $request->user()->id)->update(['is_read' => 1]);

        return redirect()->route('calendar');
    }

    private function normalizePhone(string $p): string
    {
        $p = preg_replace('/\D/', '', $p);
        if (str_starts_with($p, '08')) {
            $p = '628'.substr($p, 2);
        }

        return $p;
    }
}
