<?php

declare(strict_types=1);

/**
 * Action Handler: Simpan (Create / Update) E-Book
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul CRUD Admin (Tanggung Jawab: Mail)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\BookService;

// Keamanan 1: Wajib POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SessionManager::setFlash('error', 'Metode request tidak valid. Perubahan data wajib menggunakan POST.');
    redirect('../admin/books.php');
}

// Keamanan 2: Proteksi Role Admin
SessionManager::requireRole('admin', '../index.php');

$bookService = new BookService();
$action = isset($_POST['action']) ? trim((string)$_POST['action']) : 'create';
$id = isset($_POST['id']) ? trim((string)$_POST['id']) : '';

$data = [
    'title' => $_POST['title'] ?? '',
    'author' => $_POST['author'] ?? '',
    'category_id' => $_POST['category_id'] ?? '',
    'year' => $_POST['year'] ?? '',
    'description' => $_POST['description'] ?? '',
    'cover' => $_POST['cover'] ?? '',
    'available' => isset($_POST['available']) ? true : false,
];

if ($action === 'update' && $id !== '') {
    $result = $bookService->update($id, $data);
    if ($result['success']) {
        SessionManager::setFlash('success', $result['message']);
        redirect('../admin/books.php');
    } else {
        SessionManager::setFlash('error', $result['message']);
        SessionManager::setOldInput($data);
        redirect("../admin/book-form.php?id=" . urlencode($id));
    }
} else {
    $result = $bookService->create($data);
    if ($result['success']) {
        SessionManager::setFlash('success', $result['message']);
        redirect('../admin/books.php');
    } else {
        SessionManager::setFlash('error', $result['message']);
        SessionManager::setOldInput($data);
        redirect('../admin/book-form.php');
    }
}
