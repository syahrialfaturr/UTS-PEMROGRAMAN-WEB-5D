<?php

declare(strict_types=1);

/**
 * Konfigurasi Utama Aplikasi E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 */

return [
    'app_name' => 'E-Library',
    'base_url' => '/UTS', // Sesuaikan jika diletakkan di root atau alias virtual host
    'loan_duration_days' => 7,
    'default_loan_limit' => 3,
    'storage' => [
        'path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage',
    ],
    'roles' => [
        'ADMIN' => 'admin',
        'MEMBER' => 'member',
    ],
];

