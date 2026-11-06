<?php
$u = auth();
$unread = [];
if ($u) {
    $s = db()->prepare('SELECT id,message,created_at FROM notifications WHERE user_id=? AND is_read=0 ORDER BY id DESC LIMIT 10');
    $s->execute([$u['id']]);
    $unread = $s->fetchAll();
}
$route = $_GET['r'] ?? 'calendar/index';
$nav = [['calendar/index', 'Kalender']];
if ($u) {
    if ($u['role_id'] == ROLE_ADMIN) {
        array_push($nav, ['event/index', 'Agenda'], ['complaint/index', 'Pengaduan'], ['admin/rooms', 'Ruang'], ['admin/users', 'Pengguna']);
    }
    if ($u['role_id'] == ROLE_PIC) {
        array_push($nav, ['complaint/index', 'Pengaduan']);
    }
    if ($u['role_id'] == ROLE_GURU) {
        array_push($nav, ['event/index', 'Agenda'], ['complaint/index', 'Pengaduan']);
    }
}
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#1d3557">
<title><?= e(cfg('app')['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body>
<header class="top">
  <a class="brand" href="<?= url('calendar/index') ?>"><span class="mark">R</span>RAPI-Sekolah</a>
  <div class="top-actions">
    <?php if ($u) { ?>
      <a class="who" href="<?= url('auth/profile') ?>" title="Profil"><?= e(explode(' ', $u['fullname'])[0]) ?><small><?= e($u['role_name']) ?></small></a>
      <form method="post" action="<?= url('auth/logout') ?>"><?= csrf_field() ?><button class="btn ghost sm" type="submit">Keluar</button></form>
    <?php } else { ?>
      <a class="btn sm" href="<?= url('auth/login') ?>">Masuk</a>
    <?php } ?>
  </div>
</header>
<nav class="tabs" aria-label="Menu utama">
  <?php foreach ($nav as [$r, $label]) { ?>
    <a href="<?= url($r) ?>" class="<?= explode('/', $r)[0] === explode('/', $route)[0] ? 'on' : '' ?>"><?= e($label) ?></a>
  <?php } ?>
</nav>
<main class="wrap">
<?php foreach (flashes() as [$t, $m]) { ?><div class="flash <?= e($t) ?>" role="status"><?= e($m) ?></div><?php } ?>
