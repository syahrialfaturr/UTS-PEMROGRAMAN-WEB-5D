<?php

declare(strict_types=1);

/**
 * Action Handler: Peminjaman E-Book
 * Modul Transaksi (Tanggung Jawab: Farid)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\LoanService;

// Keamanan 1: hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SessionManager::setFlash('error', 'Metode request tidak diizinkan.');
    redirect('../catalog.php');
}

// Keamanan 2: wajib login sebagai Member
if (!SessionManager::isLoggedIn()) {
    SessionManager::setFlash('error', 'Silakan login sebagai Member untuk meminjam e-book.');
    redirect('../login.php');
}
SessionManager::requireRole('member', '../index.php');

// Keamanan 3: user_id SELALU dari session, tidak pernah dari form
$userId = (string)SessionManager::userId();
$bookId = trim((string)($_POST['book_id'] ?? ''));

$loanService = new LoanService(loanDurationDays: (int)($appConfig['loan_duration_days'] ?? 7));
$result = $loanService->borrow($userId, $bookId);

SessionManager::setFlash($result['success'] ? 'success' : 'error', $result['message']);

if ($result['success']) {
    redirect('../member/loans.php');
}
redirect($bookId !== '' ? '../detail.php?id=' . urlencode($bookId) : '../catalog.php');
