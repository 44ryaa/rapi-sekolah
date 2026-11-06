<?php

declare(strict_types=1);

const ROLE_ADMIN = 1, ROLE_PIC = 2, ROLE_GURU = 3;

function cfg(string $key): array
{
    return $GLOBALS['cfg'][$key];
}
function db(): PDO
{
    return Database::pdo();
}
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}
function url(string $route = '', array $q = []): string
{
    $q = $route === '' ? $q : ['r' => $route] + $q;

    return base_path().'/index.php'.($q ? '?'.http_build_query($q) : '');
}
function asset(string $p): string
{
    return base_path().'/'.ltrim($p, '/');
}

function redirect(string $route, array $q = []): never
{
    header('Location: '.url($route, $q));
    exit;
}
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}
function json_out(array $d, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- Flash & CSRF ---------- */
function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = [$type, $msg];
}
function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $f;
}
function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="'.csrf_token().'">';
}
function csrf_ok(): bool
{
    return hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['_csrf'] ?? ''));
}

/* ---------- Auth ---------- */
function auth(): ?array
{
    static $u = false;
    if ($u === false) {
        $u = null;
        if (! empty($_SESSION['uid'])) {
            $st = db()->prepare('SELECT u.id,u.username,u.fullname,u.phone_number,u.role_id,r.role_name
                                 FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?');
            $st->execute([$_SESSION['uid']]);
            $u = $st->fetch() ?: null;
        }
    }

    return $u;
}
function role_is(int ...$ids): bool
{
    $u = auth();

    return $u && in_array((int) $u['role_id'], $ids, true);
}

/* ---------- Tampilan ---------- */
function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__.'/../views/layout/header.php';
    require __DIR__."/../views/$name.php";
    require __DIR__.'/../views/layout/footer.php';
}

/* ---------- Waktu & WhatsApp ---------- */
function tgl(string $d): string
{
    $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($d);

    return date('j', $t).' '.$bln[(int) date('n', $t)].' '.date('Y', $t);
}
function jam(string $t): string
{
    return substr($t, 0, 5);
}

/** 0812-345 / +62812 / 62812 -> 62812345 (format internasional wa.me) */
function normalize_phone(string $p): string
{
    $p = preg_replace('/\D+/', '', $p);
    if (str_starts_with($p, '0')) {
        return '62'.substr($p, 1);
    }
    if (str_starts_with($p, '8')) {
        return '62'.$p;
    }

    return $p;
}
/** Tombol WhatsApp API (click-to-chat): https://wa.me/<nomor>?text=<pesan> */
function wa_link(string $phone, string $text): string
{
    return 'https://wa.me/'.normalize_phone($phone).'?text='.rawurlencode($text);
}

/* ---------- Notifikasi ---------- */
function notify(int $userId, string $msg): void
{
    db()->prepare('INSERT INTO notifications (user_id,message) VALUES (?,?)')->execute([$userId, $msg]);
}

/* ---------- Upload foto + kompresi ---------- */
function save_photo(array $f): string
{
    $c = cfg('upload');
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Foto bukti wajib diunggah.');
    }
    if ($f['size'] > $c['max_bytes']) {
        throw new RuntimeException('Ukuran foto maksimal 8 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $loader = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/webp' => 'imagecreatefromwebp'];
    if (! isset($loader[$mime])) {
        throw new RuntimeException('Format foto harus JPG, PNG, atau WebP.');
    }
    if (! is_dir($c['dir']) && ! mkdir($c['dir'], 0755, true)) {
        throw new RuntimeException('Folder unggahan tidak dapat dibuat.');
    }

    $name = bin2hex(random_bytes(12));
    if (! function_exists('imagejpeg')) {            // fallback bila ekstensi GD belum aktif
        $name .= ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp'][$mime];
        move_uploaded_file($f['tmp_name'], "{$c['dir']}/$name");

        return "uploads/complaints/$name";
    }
    $img = @($loader[$mime])($f['tmp_name']);
    if (! $img) {
        throw new RuntimeException('Foto tidak dapat dibaca. Coba ambil ulang.');
    }
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {   // tegakkan foto dari kamera
        $deg = [3 => 180, 6 => -90, 8 => 90][@exif_read_data($f['tmp_name'])['Orientation'] ?? 1] ?? 0;
        if ($deg) {
            $img = imagerotate($img, $deg, 0);
        }
    }
    [$w, $h] = [imagesx($img), imagesy($img)];
    $scale = min(1, $c['max_dim'] / max($w, $h));
    if ($scale < 1) {
        $img = imagescale($img, (int) round($w * $scale), (int) round($h * $scale));
    }
    $name .= '.jpg';
    imagejpeg($img, "{$c['dir']}/$name", $c['quality']);

    return "uploads/complaints/$name";
}
