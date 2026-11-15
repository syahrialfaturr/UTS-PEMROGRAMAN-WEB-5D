<?php

declare(strict_types=1);

/**
 * Action Handler: Hapus Kategori
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Kategori Admin (Tanggung Jawab: Mail)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\BookService;

// Keamanan 1: Wajib POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SessionManager::setFlash('error', 'Metode request tidak diizinkan.');
    redirect('../admin/categories.php');
}

// Keamanan 2: Proteksi Role Admin
SessionManager::requireRole('admin', '../index.php');

$categoryId = isset($_POST['category_id']) ? trim((string)$_POST['category_id']) : '';

if ($categoryId === '') {
    SessionManager::setFlash('error', 'ID kategori tidak valid.');
    redirect('../admin/categories.php');
}

$bookService = new BookService();
$result = $bookService->deleteCategory($categoryId);

if ($result['success']) {
    SessionManager::setFlash('success', $result['message']);
} else {
    SessionManager::setFlash('error', $result['message']);
}

redirect('../admin/categories.php');
