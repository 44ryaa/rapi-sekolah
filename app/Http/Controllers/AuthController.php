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

        if ($input === '') {
            return back()->withErrors(['username' => 'Username atau Email akun wajib diisi.'])->withInput();
        }

        // Cari user berdasarkan username atau email terdaftar di database
        $user = User::where('username', $input)->orWhere('email', $input)->first();
        if (! $user) {
            return back()->withErrors(['username' => 'Akun dengan username atau email tersebut tidak ditemukan di database.'])->withInput();
        }

        if (empty($user->email)) {
            return back()->withErrors(['username' => 'Akun ini belum memiliki alamat email yang tersimpan di database. Silakan hubungi Administrator.'])->withInput();
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
            $targetPhone = $user->phone_number;
            if (empty($targetPhone)) {
                return back()->withErrors(['channel' => 'Akun ini belum memiliki nomor WhatsApp terdaftar di database. Silakan gunakan metode Email.'])->withInput();
            }

            $normalizedPhone = WhatsAppService::normalizePhone($targetPhone);
            session(['reset_target_phone' => $normalizedPhone]);

            // Kirim via WhatsApp Service
            $waResult = WhatsAppService::sendOtp($normalizedPhone, $otp, $user);
            $waUrl = $waResult['wa_url'];
            session([
                'reset_wa_url' => $waUrl,
                'reset_wa_auto_sent' => $waResult['auto_sent'],
            ]);

            $msg = $waResult['auto_sent']
                ? "Kode OTP telah otomatis terkirim ke WhatsApp nomor terdaftar ({$normalizedPhone})!"
                : "Kode OTP siap dikirim ke WhatsApp nomor terdaftar ({$normalizedPhone}).";
        } else {
            // Pengiriman via Gmail / Email (HANYA ke email resmi yang terdaftar di database)
            $targetEmail = $user->email;
            session(['reset_target_email' => $targetEmail]);
            $maskedEmail = $this->maskEmail($targetEmail);

            try {
                Mail::to($targetEmail)->send(new ResetPasswordOtpMail($user, $otp));
                $msg = "Kode OTP telah dikirimkan ke email resmi terdaftar ({$maskedEmail}). Silakan cek kotak masuk atau folder spam.";
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim email reset password: '.$e->getMessage());
                $msg = "Kode OTP berhasil diproses untuk email terdaftar ({$maskedEmail}).";
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
            $targetPhone = session('reset_target_phone') ?: $user->phone_number;
            $normalizedPhone = WhatsAppService::normalizePhone($targetPhone);
            session(['reset_target_phone' => $normalizedPhone]);

            $waResult = WhatsAppService::sendOtp($normalizedPhone, $otp, $user);
            $waUrl = $waResult['wa_url'];
            session([
                'reset_wa_url' => $waUrl,
                'reset_wa_auto_sent' => $waResult['auto_sent'],
            ]);

            $msg = $waResult['auto_sent']
                ? "Kode OTP baru telah otomatis terkirim ke WhatsApp nomor terdaftar ({$normalizedPhone})!"
                : "Kode OTP baru telah disiapkan untuk WhatsApp nomor terdaftar ({$normalizedPhone}).";
        } else {
            // Strictly send to registered email from database
            $targetEmail = $user->email;
            session(['reset_target_email' => $targetEmail]);
            $maskedEmail = $this->maskEmail($targetEmail);

            try {
                Mail::to($targetEmail)->send(new ResetPasswordOtpMail($user, $otp));
                $msg = "Kode OTP baru telah dikirimkan ke email terdaftar di database ({$maskedEmail}).";
            } catch (\Throwable $e) {
                $msg = "Kode OTP baru telah digenerate untuk email terdaftar ({$maskedEmail}).";
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

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $maskedName = substr($name, 0, 1).'*';
        } else {
            $maskedName = substr($name, 0, 2).str_repeat('*', max(strlen($name) - 3, 2)).substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }
}
