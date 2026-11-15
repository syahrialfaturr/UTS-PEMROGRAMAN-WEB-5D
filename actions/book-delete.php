<?php

declare(strict_types=1);

/**
 * Action Handler: Hapus E-Book
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul CRUD Admin (Tanggung Jawab: Mail)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\BookService;

// Keamanan 1: Wajib POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SessionManager::setFlash('error', 'Metode request tidak diizinkan.');
    redirect('../admin/books.php');
}

// Keamanan 2: Proteksi Role Admin
SessionManager::requireRole('admin', '../index.php');

$bookId = isset($_POST['book_id']) ? trim((string)$_POST['book_id']) : '';

if ($bookId === '') {
    SessionManager::setFlash('error', 'ID e-book yang ingin dihapus tidak valid.');
    redirect('../admin/books.php');
}

$bookService = new BookService();
$result = $bookService->delete($bookId);

if ($result['success']) {
    SessionManager::setFlash('success', $result['message']);
} else {
    SessionManager::setFlash('error', $result['message']);
}

redirect('../admin/books.php');
