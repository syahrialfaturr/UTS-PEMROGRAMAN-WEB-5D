<?php

declare(strict_types=1);

/**
 * Action: login-process.php
 * UTS Pemrograman Web - E-Library
 * Memproses autentikasi login pengguna melalui method POST.
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\AuthService;

// Hanya izinkan HTTP POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SessionManager::setFlash('error', 'Metode permintaan tidak valid. Autentikasi wajib menggunakan POST.');
    redirect('../login.php');
}

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$authService = new AuthService();
$result = $authService->login($email, $password);

if ($result['success']) {
    SessionManager::clearOldInput();
    SessionManager::setFlash('success', $result['message']);

    $user = $result['user'];
    if ($user->getRole() === 'admin') {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../member/dashboard.php');
    }
} else {
    SessionManager::setOldInput($_POST);
    SessionManager::setFlash('error', $result['message']);
    redirect('../login.php');
}

