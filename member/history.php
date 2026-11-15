<?php

declare(strict_types=1);

/**
 * Riwayat Transaksi Member - E-Library
 * Modul Transaksi (Tanggung Jawab: Farid)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\BookService;
use App\Services\LoanService;

if (!SessionManager::isLoggedIn()) {
    SessionManager::setFlash('error', 'Silakan login terlebih dahulu.');
    redirect('../login.php');
}
SessionManager::requireRole('member', '../index.php');

$currentUser = SessionManager::user();
$bookService = new BookService();
$loanService = new LoanService(bookService: $bookService);

$history = $loanService->getMemberHistory((string)$currentUser['id']);

$bookTitles = [];
foreach ($bookService->getAll() as $book) {
    $bookTitles[$book->getId()] = $book->getTitle();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - E-Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="../index.php" class="navbar-brand">📚 <span>E-Library Member Area</span></a>
            <ul class="navbar-nav">
                <li><a href="../catalog.php" class="nav-link">Katalog Buku</a></li>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="loans.php" class="nav-link">Pinjaman Saya</a></li>
                <li><a href="history.php" class="nav-link active" aria-current="page">Riwayat</a></li>
                <li><span class="user-badge">👤 <?= e($currentUser['name']) ?></span></li>
                <li><a href="../logout.php" class="btn btn-outline btn-sm">Keluar</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-container">
        <section class="admin-header">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800;">Riwayat Transaksi</h1>
                <p style="color: var(--secondary);">Seluruh peminjaman Anda, terbaru di atas. Total: <?= count($history) ?> transaksi.</p>
            </div>
        </section>

        <?php if (empty($history)): ?>
            <section class="empty-state">
                <div class="empty-state-icon">🗂️</div>
                <h2 class="empty-state-title">Belum ada riwayat</h2>
                <p class="empty-state-text">Riwayat akan muncul setelah Anda meminjam e-book.</p>
            </section>
        <?php else: ?>
            <section class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">ID Transaksi</th>
                            <th scope="col">Judul E-Book</th>
                            <th scope="col">Dipinjam</th>
                            <th scope="col">Jatuh Tempo</th>
                            <th scope="col">Dikembalikan</th>
                            <th scope="col" style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $t): ?>
                            <tr>
                                <td><code><?= e($t->getId()) ?></code></td>
                                <td><?= e($bookTitles[$t->getBookId()] ?? $t->getBookId() . ' (dihapus)') ?></td>
                                <td><?= e($t->getBorrowedAt()) ?></td>
                                <td><?= e($t->getDueAt()) ?></td>
                                <td><?= e($t->getReturnedAt() ?? '—') ?></td>
                                <td style="text-align: center;">
                                    <?php if ($t->isActive()): ?>
                                        <span class="badge badge-borrowed">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge badge-available">Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>
</body>
</html>
