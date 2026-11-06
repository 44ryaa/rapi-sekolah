<?php

declare(strict_types=1);

$GLOBALS['cfg'] = require __DIR__.'/../config/config.php';
date_default_timezone_set($GLOBALS['cfg']['app']['timezone']);

require __DIR__.'/core/Database.php';
require __DIR__.'/core/helpers.php';
require __DIR__.'/core/Controller.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
