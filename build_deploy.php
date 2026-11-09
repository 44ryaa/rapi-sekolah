<?php
$logo = base64_encode(file_get_contents(__DIR__ . '/public/assets/img/logo-smk.png'));
$landing = base64_encode(file_get_contents(__DIR__ . '/resources/views/landing.blade.php'));
$layout = base64_encode(file_get_contents(__DIR__ . '/resources/views/layouts/app.blade.php'));
$login = base64_encode(file_get_contents(__DIR__ . '/resources/views/auth/login.blade.php'));
$print = base64_encode(file_get_contents(__DIR__ . '/resources/views/reports/print.blade.php'));
$auth = base64_encode(file_get_contents(__DIR__ . '/app/Http/Controllers/AuthController.php'));
$forgot = base64_encode(file_get_contents(__DIR__ . '/resources/views/auth/forgot.blade.php'));
$eventApi = base64_encode(file_get_contents(__DIR__ . '/app/Http/Controllers/Api/EventApiController.php'));

$payload = [
    'logo' => $logo,
    'landing' => $landing,
    'layout' => $layout,
    'login' => $login,
    'print' => $print,
    'auth' => $auth,
    'forgot' => $forgot,
    'eventApi' => $eventApi,
];

$json = json_encode($payload, JSON_UNESCAPED_SLASHES);

$template = <<<'PHP'
<?php
header("Content-Type: text/html; charset=utf-8");
echo "<style>body{font-family:sans-serif;padding:30px;background:#0d0a07;color:#f5f2ea;} pre{background:#1f150c;padding:15px;border-radius:8px;border:1px solid #412d15;color:#e1dcc9;} .badge{background:#e1dcc9;color:#1f150c;padding:4px 8px;border-radius:4px;font-weight:bold;} .error{color:#ef4444;} h2{color:#e1dcc9;}</style>";
echo "<h2>Memperbarui Seluruh Sistem RAPI-Sekolah (Logo, Landing Mobile, Reset Password, Multi-Day, & Login Mobile)...</h2><pre>";

$laravelRoot = "/home/rapiseko/rapi-sekolah";
$publicRoot = "/home/rapiseko/public_html";

$payload = json_decode(<<<'JSONDATA'
__PAYLOAD_JSON__
JSONDATA
, true);

// 1. Simpan Logo SMK
$imgData = base64_decode($payload['logo']);
$dirs = [
    $publicRoot . "/assets/img",
    $laravelRoot . "/public/assets/img"
];
foreach ($dirs as $d) {
    if (!is_dir($d)) { @mkdir($d, 0755, true); }
    file_put_contents($d . "/logo-smk.png", $imgData);
    file_put_contents($d . "/logo-smk.jpg", $imgData);
}
echo "[OK] Logo SMK Negeri 1 Bantul berhasil disimpan di assets/img/\n";

// 2. MySQL DB Update (personal_access_tokens)
$envFile = $laravelRoot . "/.env";
$dbHost = "localhost";
$dbName = "rapiseko_db";
$dbUser = "rapiseko_db";
$dbPass = "kontolgerigi1";

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/DB_HOST=(.*)/', $envContent, $m)) $dbHost = trim($m[1]);
    if (preg_match('/DB_DATABASE=(.*)/', $envContent, $m)) $dbName = trim($m[1]);
    if (preg_match('/DB_USERNAME=(.*)/', $envContent, $m)) $dbUser = trim($m[1]);
    if (preg_match('/DB_PASSWORD=(.*)/', $envContent, $m)) $dbPass = trim($m[1]);
}

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
          `tokenable_id` bigint(20) unsigned NOT NULL,
          `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
          `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
          `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `last_used_at` timestamp NULL DEFAULT NULL,
          `expires_at` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
          KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
          KEY `personal_access_tokens_expires_at_index` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] Tabel personal_access_tokens aktif di MySQL (Fix Login Mobile)\n";
} catch (\Throwable $e) {
    echo "[WARN] MySQL table update: " . $e->getMessage() . "\n";
}

// 3. Tulis File Kode & View
$filesToUpdate = [
    $laravelRoot . "/resources/views/landing.blade.php" => $payload['landing'],
    $laravelRoot . "/resources/views/layouts/app.blade.php" => $payload['layout'],
    $laravelRoot . "/resources/views/auth/login.blade.php" => $payload['login'],
    $laravelRoot . "/resources/views/reports/print.blade.php" => $payload['print'],
    $laravelRoot . "/app/Http/Controllers/AuthController.php" => $payload['auth'],
    $laravelRoot . "/resources/views/auth/forgot.blade.php" => $payload['forgot'],
    $laravelRoot . "/app/Http/Controllers/Api/EventApiController.php" => $payload['eventApi'],
];

foreach ($filesToUpdate as $path => $b64) {
    $dir = dirname($path);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    file_put_contents($path, base64_decode($b64));
    echo "[OK] Diperbarui: " . basename($path) . "\n";
}

// 4. Bersihkan Cache View
$viewCacheDir = $laravelRoot . "/storage/framework/views";
if (is_dir($viewCacheDir)) {
    $files = glob($viewCacheDir . "/*.php");
    foreach ($files as $f) {
        @unlink($f);
    }
    echo "[OK] Cache view dikosongkan (" . count($files) . " file)\n";
}

echo "</pre><h3 style='color:#10b981;'>PEMBARUAN SELESAI DENGAN SUKSES!</h3>";
echo "<p>Semua pembaruan sudah aktif:</p>
<ul>
  <li><b>Logo SMK Negeri 1 Bantul</b> resolusi tajam di Favicon, Landing Page, Sidebar, Login, & Kop Surat Cetak Laporan</li>
  <li><b>Tampilan Mobile Landing Page</b> rapi: teks judul tidak terpotong, tombol pas layar ponsel, mode malam mewah aktif secara default</li>
  <li><b>Reset Password</b> wajib menggunakan alamat email resmi yang terdaftar di database</li>
  <li><b>Login Mobile Sanctum</b> diperbaiki (tabel token siap digunakan)</li>
  <li><b>Peminjaman Multi-Hari</b> aktif di Aplikasi Mobile Android & Web</li>
</ul>
<p style='color:#f59e0b;'><b>PENTING:</b> Silakan hapus file deploy_update_all.php ini dari cPanel public_html/ setelah selesai.</p>";
PHP;

$output = str_replace('__PAYLOAD_JSON__', $json, $template);
file_put_contents(__DIR__ . '/public/deploy_update_all.php', $output);
echo "Deploy script generated: public/deploy_update_all.php (Size: " . strlen($output) . " bytes)\n";

