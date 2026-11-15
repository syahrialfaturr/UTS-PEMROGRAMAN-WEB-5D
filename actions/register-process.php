<?php

declare(strict_types=1);

/**
 * Action: register-process.php
 * UTS Pemrograman Web - E-Library
 * Memproses pendaftaran pengguna secara aman melalui method POST.
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\AuthService;

// Hanya izinkan HTTP POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SessionManager::setFlash('error', 'Metode permintaan tidak valid. Pendaftaran wajib menggunakan POST.');
    redirect('../register.php');
}

$authService = new AuthService();
$result = $authService->register($_POST);

if ($result['success']) {
    SessionManager::clearOldInput();
    SessionManager::setFlash('success', $result['message']);
    redirect('../login.php');
} else {
    // Simpan input form (kecuali password) untuk repopulasi
    SessionManager::setOldInput($_POST);
    
    // Rangkai pesan error jika ada detail error validasi
    $errorMessage = $result['message'];
    if (!empty($result['errors'])) {
        $errorMessage .= ' (' . implode(', ', $result['errors']) . ')';
    }
    
    SessionManager::setFlash('error', $errorMessage);
    redirect('../register.php');
}
