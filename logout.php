<?php

declare(strict_types=1);

/**
 * Logout Handler - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\AuthService;

$authService = new AuthService();
$authService->logout();

// Mulai kembali sesi kosong hanya untuk menyematkan notifikasi logout sukses
SessionManager::start();
SessionManager::setFlash('success', 'Anda telah berhasil keluar dari akun.');

redirect('login.php');
