<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function users(): void
    {
        $users = db()->query('SELECT u.*,r.role_name,(SELECT COUNT(*) FROM rooms WHERE pic_id=u.id) AS room_count FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.role_id,u.fullname')->fetchAll();
        view('admin/users', ['users' => $users, 'roles' => db()->query('SELECT * FROM roles')->fetchAll()]);
    }

    public function userSave(): void
    {
        $id = $this->id();
        $un = $this->str('username');
        $fn = $this->str('fullname');
        $ph = $this->str('phone_number');
        $role = $this->id('role_id');
        $pw = (string) ($_POST['password'] ?? '');
        if (! preg_match('/^[A-Za-z0-9_.]{3,50}$/', $un) || $fn === '' || strlen(normalize_phone($ph)) < 9 || ! in_array($role, [1, 2, 3], true)) {
            $this->back('admin/users', 'error', 'Lengkapi data: username 3–50 karakter (huruf/angka/_/.), nama, nomor HP, dan role.');
        }
        if (($id === 0 || $pw !== '') && strlen($pw) < cfg('app')['min_password']) {
            $this->back('admin/users', 'error', 'Password minimal '.cfg('app')['min_password'].' karakter.');
        }
        try {
            if ($id === 0) {
                db()->prepare('INSERT INTO users (username,password,fullname,phone_number,role_id) VALUES (?,?,?,?,?)')
                    ->execute([$un, password_hash($pw, PASSWORD_BCRYPT), $fn, $ph, $role]);
            } else {
                if ($id === (int) $this->user()['id'] && $role !== ROLE_ADMIN) {
                    $this->back('admin/users', 'error', 'Anda tidak dapat menurunkan role akun sendiri.');
                }
                db()->prepare('UPDATE users SET username=?,fullname=?,phone_number=?,role_id=? WHERE id=?')->execute([$un, $fn, $ph, $role, $id]);
                if ($pw !== '') {
                    db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($pw, PASSWORD_BCRYPT), $id]);
                }
                if ($role !== ROLE_PIC) {
                    db()->prepare('UPDATE rooms SET pic_id=NULL WHERE pic_id=?')->execute([$id]);
                }
            }
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $this->back('admin/users', 'error', 'Username sudah dipakai.');
            }
            throw $ex;
        }
        $this->back('admin/users', 'ok', 'Data pengguna disimpan.');
    }

    public function userDelete(): void
    {
        $id = $this->id();
        if ($id === (int) $this->user()['id']) {
            $this->back('admin/users', 'error', 'Akun sendiri tidak dapat dihapus.');
        }
        try {
            db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $this->back('admin/users', 'error', 'Pengguna masih memiliki agenda/pengaduan. Ubah saja datanya.');
            }
            throw $ex;
        }
        $this->back('admin/users', 'ok', 'Pengguna dihapus.');
    }

    public function rooms(): void
    {
        $rooms = db()->query('SELECT r.*,u.fullname AS pic_name FROM rooms r LEFT JOIN users u ON u.id=r.pic_id ORDER BY r.room_name')->fetchAll();
        $pics = db()->query('SELECT id,fullname FROM users WHERE role_id='.ROLE_PIC.' ORDER BY fullname')->fetchAll();
        view('admin/rooms', ['rooms' => $rooms, 'pics' => $pics]);
    }

    public function roomSave(): void
    {
        $id = $this->id();
        $name = $this->str('room_name');
        $cap = $this->id('capacity');
        $pic = $this->id('pic_id') ?: null;
        if ($name === '') {
            $this->back('admin/rooms', 'error', 'Nama ruang wajib diisi.');
        }
        if ($pic) {   // delegasi hanya boleh ke user ber-role PIC Ruang
            $c = db()->prepare('SELECT 1 FROM users WHERE id=? AND role_id=?');
            $c->execute([$pic, ROLE_PIC]);
            if (! $c->fetch()) {
                $this->back('admin/rooms', 'error', 'PIC harus pengguna ber-role PIC Ruang.');
            }
        }
        $active = isset($_POST['is_active']) ? 1 : 0;
        try {
            if ($id === 0) {
                db()->prepare('INSERT INTO rooms (room_name,capacity,location,pic_id,is_active) VALUES (?,?,?,?,?)')->execute([$name, $cap, $this->str('location'), $pic, $active]);
            } else {
                db()->prepare('UPDATE rooms SET room_name=?,capacity=?,location=?,pic_id=?,is_active=? WHERE id=?')->execute([$name, $cap, $this->str('location'), $pic, $active, $id]);
            }
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $this->back('admin/rooms', 'error', 'Nama ruang sudah ada.');
            }
            throw $ex;
        }
        $this->back('admin/rooms', 'ok', 'Data ruang disimpan.');
    }
}
