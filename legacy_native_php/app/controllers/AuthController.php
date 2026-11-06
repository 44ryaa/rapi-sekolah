<?php

declare(strict_types=1);

class AuthController extends Controller
{
    public function login(): void
    {
        if (auth()) {
            redirect('calendar/index');
        }
        if (is_post()) {
            $f = $_SESSION['login_fail'] ?? ['n' => 0, 't' => time()];
            if (time() - $f['t'] > 300) {
                $f = ['n' => 0, 't' => time()];
            }
            if ($f['n'] >= 5) {
                $this->back('auth/login', 'error', 'Terlalu banyak percobaan gagal. Coba lagi dalam 5 menit.');
            }

            $st = db()->prepare('SELECT id,password FROM users WHERE username=?');
            $st->execute([$this->str('username')]);
            $u = $st->fetch();
            if ($u && password_verify((string) ($_POST['password'] ?? ''), $u['password'])) {
                session_regenerate_id(true);
                $_SESSION['uid'] = (int) $u['id'];
                unset($_SESSION['login_fail']);
                redirect('calendar/index');
            }
            $f['n']++;
            $_SESSION['login_fail'] = $f;
            $this->back('auth/login', 'error', 'Username atau password salah.');
        }
        view('auth/login');
    }

    public function logout(): void
    {
        if (! is_post()) {
            redirect('calendar/index');
        }
        $_SESSION = [];
        session_destroy();
        session_start();
        flash('ok', 'Anda sudah keluar.');
        redirect('auth/login');
    }

    /** Langkah 1: verifikasi username + nomor HP terdaftar, lalu terbitkan token sekali pakai. */
    public function forgot(): void
    {
        if (is_post()) {
            $st = db()->prepare('SELECT id,phone_number FROM users WHERE username=?');
            $st->execute([$this->str('username')]);
            $u = $st->fetch();
            if ($u && normalize_phone($this->str('phone')) === normalize_phone($u['phone_number'])) {
                $token = bin2hex(random_bytes(32));
                db()->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([$u['id']]);
                db()->prepare('INSERT INTO password_resets (user_id,token_hash,expires_at) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 30 MINUTE))')
                    ->execute([$u['id'], hash('sha256', $token)]);
                redirect('auth/reset', ['token' => $token]);
            }
            $this->back('auth/forgot', 'error', 'Username dan nomor HP tidak cocok dengan data kami.');
        }
        view('auth/forgot');
    }

    /** Langkah 2: setel password baru dengan token. */
    public function reset(): void
    {
        $token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
        $st = db()->prepare('SELECT id,user_id FROM password_resets WHERE token_hash=? AND expires_at>NOW()');
        $st->execute([hash('sha256', $token)]);
        $row = $st->fetch();
        if (! $row) {
            $this->back('auth/forgot', 'error', 'Tautan reset tidak valid atau sudah kedaluwarsa. Ulangi verifikasi.');
        }

        if (is_post()) {
            $p = (string) ($_POST['password'] ?? '');
            if (strlen($p) < cfg('app')['min_password']) {
                $this->back('auth/reset', 'error', 'Password minimal '.cfg('app')['min_password'].' karakter.', ['token' => $token]);
            }
            if ($p !== (string) ($_POST['password2'] ?? '')) {
                $this->back('auth/reset', 'error', 'Konfirmasi password tidak sama.', ['token' => $token]);
            }
            db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($p, PASSWORD_BCRYPT), $row['user_id']]);
            db()->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([$row['user_id']]);
            $this->back('auth/login', 'ok', 'Password diperbarui. Silakan masuk.');
        }
        view('auth/reset', ['token' => $token]);
    }

    public function profile(): void
    {
        view('auth/profile');
    }

    public function profileSave(): void
    {
        $name = $this->str('fullname');
        $phone = $this->str('phone_number');
        if ($name === '' || strlen(normalize_phone($phone)) < 9) {
            $this->back('auth/profile', 'error', 'Nama dan nomor HP yang valid wajib diisi.');
        }
        db()->prepare('UPDATE users SET fullname=?, phone_number=? WHERE id=?')->execute([$name, $phone, $this->user()['id']]);
        $this->back('auth/profile', 'ok', 'Profil disimpan.');
    }

    public function passwordSave(): void
    {
        $st = db()->prepare('SELECT password FROM users WHERE id=?');
        $st->execute([$this->user()['id']]);
        $new = (string) ($_POST['new_password'] ?? '');
        if (! password_verify((string) ($_POST['current_password'] ?? ''), $st->fetchColumn())) {
            $this->back('auth/profile', 'error', 'Password saat ini salah.');
        }
        if (strlen($new) < cfg('app')['min_password']) {
            $this->back('auth/profile', 'error', 'Password baru minimal '.cfg('app')['min_password'].' karakter.');
        }
        if ($new !== (string) ($_POST['new_password2'] ?? '')) {
            $this->back('auth/profile', 'error', 'Konfirmasi password baru tidak sama.');
        }
        db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new, PASSWORD_BCRYPT), $this->user()['id']]);
        $this->back('auth/profile', 'ok', 'Password berhasil diganti.');
    }

    public function readNotifications(): void
    {
        db()->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$this->user()['id']]);
        redirect('calendar/index');
    }
}
