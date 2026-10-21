<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Notification;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) return redirect()->route('calendar');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $fails = session('login_fails', ['n' => 0, 't' => time()]);
        if (time() - $fails['t'] > 300) $fails = ['n' => 0, 't' => time()];
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
        return redirect()->route('login')->with('success', 'Anda sudah keluar.');
    }

    public function forgotForm()
    {
        return view('auth.forgot');
    }

    public function forgot(Request $request)
    {
        $input = trim($request->input('username', ''));
        $phoneInput = $this->normalizePhone($request->input('phone', ''));

        if ($input === '' || $phoneInput === '') {
            return back()->withErrors(['username' => 'Username dan nomor HP wajib diisi.'])->withInput();
        }

        // Cari user berdasarkan username atau email
        $user = User::where('username', $input)->orWhere('email', $input)->first();

        if ($user && $this->normalizePhone($user->phone_number ?? '') === $phoneInput) {
            $token = bin2hex(random_bytes(32));
            \DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            \DB::table('password_reset_tokens')->insert([
                'email'      => $user->email,
                'token'      => hash('sha256', $token),
                'created_at' => now(),
            ]);

            return redirect()->route('reset', ['token' => $token])
                ->with('success', 'Identitas terverifikasi. Silakan buat password baru Anda.');
        }

        return back()->withErrors(['username' => 'Username/Email atau nomor HP tidak cocok dengan data terdaftar.'])->withInput();
    }

    public function resetForm(Request $request)
    {
        $token = $request->query('token') ?? session('reset_token') ?? session('token') ?? old('token') ?? '';
        if (!$token) {
            return redirect()->route('forgot')->withErrors(['token' => 'Sesi reset password tidak ditemukan. Silakan verifikasi ulang.']);
        }

        $row = \DB::table('password_reset_tokens')->where('token', hash('sha256', $token))->first();
        if (!$row || now()->diffInMinutes($row->created_at) > 30) {
            return redirect()->route('forgot')->withErrors(['token' => 'Tautan reset tidak valid atau sudah kedaluwarsa (maks. 30 menit).']);
        }

        return view('auth.reset', compact('token'));
    }

    public function reset(Request $request)
    {
        $token = $request->input('token', '');
        $row = \DB::table('password_reset_tokens')->where('token', hash('sha256', $token))->first();
        if (!$row || now()->diffInMinutes($row->created_at) > 30) {
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

        return redirect()->route('login')->with('success', 'Password berhasil diperbarui! Silakan masuk dengan password baru.');
    }

    public function profileForm()
    {
        return view('auth.profile');
    }

    public function profileSave(Request $request)
    {
        $name  = trim($request->input('fullname', ''));
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
        if (!Hash::check($request->input('current_password', ''), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }
        $new = $request->input('new_password', '');
        if (strlen($new) < 6) return back()->withErrors(['new_password' => 'Password baru minimal 6 karakter.']);
        if ($new !== $request->input('new_password2', '')) return back()->withErrors(['new_password2' => 'Konfirmasi password baru tidak sama.']);
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
        if (str_starts_with($p, '08')) $p = '628' . substr($p, 2);
        return $p;
    }
}
