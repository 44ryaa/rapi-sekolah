<?php

// Konfigurasi aplikasi RAPI-Sekolah. Sesuaikan dengan lingkungan lokal (XAMPP/Laragon/Docker).
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'rapi_sekolah',
        'user' => 'root',
        'pass' => '',
    ],
    'app' => [
        'name' => 'RAPI-Sekolah',
        'timezone' => 'Asia/Jakarta',
        'escalation_hours' => 24,   // batas "Belum Diperbaiki" sebelum Waka boleh kirim peringatan WA
        'min_password' => 8,
    ],
    'upload' => [
        'dir' => __DIR__.'/../public/uploads/complaints',
        'max_bytes' => 8 * 1024 * 1024,
        'max_dim' => 1280,        // sisi terpanjang foto setelah kompresi (px)
        'quality' => 75,          // kualitas JPEG
    ],
];
