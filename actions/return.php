<?php

declare(strict_types=1);

/**
 * Action Handler: Pengembalian E-Book
 * Modul Transaksi (Tanggung Jawab: Farid)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\LoanService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SessionManager::setFlash('error', 'Metode request tidak diizinkan.');
    redirect('../member/loans.php');
}

if (!SessionManager::isLoggedIn()) {
    SessionManager::setFlash('error', 'Silakan login terlebih dahulu.');
    redirect('../login.php');
}
SessionManager::requireRole('member', '../index.php');

// Kepemilikan transaksi diverifikasi di LoanService terhadap ID dari session
$userId = (string)SessionManager::userId();
$transactionId = trim((string)($_POST['transaction_id'] ?? ''));

$loanService = new LoanService();
$result = $loanService->returnBook($userId, $transactionId);

SessionManager::setFlash($result['success'] ? 'success' : 'error', $result['message']);
redirect('../member/loans.php');
