<?php

declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';

foreach (glob(__DIR__.'/../app/controllers/*.php') as $f) {
    require $f;
}

/*  rute => [Controller, method, akses]
    akses: null = publik | [] = semua yang login | [ROLE...] = role tertentu */
$A = [ROLE_ADMIN];
$P = [ROLE_PIC];
$G = [ROLE_GURU];
$routes = [
    'calendar/index' => ['CalendarController',  'index',        null],
    'calendar/api' => ['CalendarController',  'api',          null],

    'auth/login' => ['AuthController', 'login',        null],
    'auth/logout' => ['AuthController', 'logout',       []],
    'auth/forgot' => ['AuthController', 'forgot',       null],
    'auth/reset' => ['AuthController', 'reset',        null],
    'auth/profile' => ['AuthController', 'profile',      []],
    'auth/profileSave' => ['AuthController', 'profileSave',  []],
    'auth/passwordSave' => ['AuthController', 'passwordSave', []],
    'notification/read' => ['AuthController', 'readNotifications', []],

    'event/index' => ['EventController', 'index',  [ROLE_ADMIN, ROLE_GURU]],
    'event/create' => ['EventController', 'create', [ROLE_ADMIN, ROLE_GURU]],
    'event/store' => ['EventController', 'store',  [ROLE_ADMIN, ROLE_GURU]],
    'event/slots' => ['EventController', 'slots',  [ROLE_ADMIN, ROLE_GURU]],
    'event/review' => ['EventController', 'review', $A],
    'event/move' => ['EventController', 'move',   $A],

    'complaint/index' => ['ComplaintController', 'index',  []],
    'complaint/create' => ['ComplaintController', 'create', $G],
    'complaint/store' => ['ComplaintController', 'store',  $G],
    'complaint/status' => ['ComplaintController', 'status', $P],
    'complaint/delete' => ['ComplaintController', 'delete', [ROLE_ADMIN, ROLE_GURU]],

    'admin/users' => ['AdminController', 'users',      $A],
    'admin/userSave' => ['AdminController', 'userSave',   $A],
    'admin/userDelete' => ['AdminController', 'userDelete', $A],
    'admin/rooms' => ['AdminController', 'rooms',      $A],
    'admin/roomSave' => ['AdminController', 'roomSave',   $A],
];

$r = trim((string) ($_GET['r'] ?? ''), '/');
if ($r === '') {
    redirect('calendar/index');
}

if (! isset($routes[$r])) {
    http_response_code(404);
    view('error', ['code' => 404, 'msg' => 'Halaman tidak ditemukan.']);
    exit;
}
[$class, $method, $access] = $routes[$r];

if ($access !== null) {
    if (! auth()) {
        flash('error', 'Silakan masuk terlebih dahulu.');
        redirect('auth/login');
    }
    if ($access && ! role_is(...$access)) {
        http_response_code(403);
        view('error', ['code' => 403, 'msg' => 'Anda tidak memiliki akses ke halaman ini.']);
        exit;
    }
}
if (is_post() && ! csrf_ok()) {
    http_response_code(419);
    view('error', ['code' => 419, 'msg' => 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.']);
    exit;
}

try {
    (new $class)->$method();
} catch (PDOException $ex) {
    error_log('[RAPI] '.$ex->getMessage());
    http_response_code(500);
    view('error', ['code' => 500, 'msg' => 'Terjadi kesalahan pada basis data. Periksa log server.']);
}
