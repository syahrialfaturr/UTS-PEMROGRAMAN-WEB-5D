<?php

declare(strict_types=1);

/**
 * Action Handler: Simpan (Create / Update) Kategori
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

$bookService = new BookService();
$action = isset($_POST['action']) ? trim((string)$_POST['action']) : 'create';
$id = isset($_POST['id']) ? trim((string)$_POST['id']) : '';
$name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';

if ($action === 'update' && $id !== '') {
    $result = $bookService->updateCategory($id, $name);
} else {
    $result = $bookService->createCategory($name);
}

if ($result['success']) {
    SessionManager::setFlash('success', $result['message']);
} else {
    SessionManager::setFlash('error', $result['message']);
}

redirect('../admin/categories.php');
