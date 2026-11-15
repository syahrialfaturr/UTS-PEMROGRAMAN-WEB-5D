<?php

declare(strict_types=1);

/**
 * Pinjaman Aktif Member - E-Library
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

$loans = $loanService->getMemberLoans((string)$currentUser['id']);

$bookTitles = [];
foreach ($bookService->getAll() as $book) {
    $bookTitles[$book->getId()] = $book->getTitle();
}

$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjaman Saya - E-Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header>
        <nav class="navbar">
            <a href="../index.php" class="navbar-brand">📚 <span>E-Library Member Area</span></a>
            <ul class="navbar-nav">
                <li><a href="../catalog.php" class="nav-link">Katalog Buku</a></li>
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="loans.php" class="nav-link active" aria-current="page">Pinjaman Saya</a></li>
                <li><a href="history.php" class="nav-link">Riwayat</a></li>
                <li><span class="user-badge">👤 <?= e($currentUser['name']) ?></span></li>
                <li><a href="../logout.php" class="btn btn-outline btn-sm">Keluar</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-container">
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" role="alert"><span>✓</span><div><?= e($flashSuccess) ?></div></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger" role="alert"><span>⚠️</span><div><?= e($flashError) ?></div></div>
        <?php endif; ?>

        <section class="admin-header">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800;">Pinjaman Aktif</h1>
                <p style="color: var(--secondary);">E-book yang sedang Anda pinjam. Masa pinjam 7 hari sejak tanggal pinjam.</p>
            </div>
            <div><a href="../catalog.php" class="btn btn-primary">📖 Pinjam E-Book Lain</a></div>
        </section>

        <?php if (empty($loans)): ?>
            <section class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h2 class="empty-state-title">Belum ada pinjaman aktif</h2>
                <p class="empty-state-text">Jelajahi katalog dan pinjam e-book yang tersedia.</p>
            </section>
        <?php else: ?>
            <section class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">ID Transaksi</th>
                            <th scope="col">Judul E-Book</th>
                            <th scope="col">Tanggal Pinjam</th>
                            <th scope="col">Jatuh Tempo</th>
                            <th scope="col" style="text-align: center;">Status</th>
                            <th scope="col" style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): ?>
                            <?php $title = $bookTitles[$loan->getBookId()] ?? $loan->getBookId() . ' (dihapus)'; ?>
                            <tr>
                                <td><code><?= e($loan->getId()) ?></code></td>
                                <td><strong><?= e($title) ?></strong></td>
                                <td><time datetime="<?= e($loan->getBorrowedAt()) ?>"><?= e($loan->getBorrowedAt()) ?></time></td>
                                <td><time datetime="<?= e($loan->getDueAt()) ?>"><?= e($loan->getDueAt()) ?></time></td>
                                <td style="text-align: center;">
                                    <?php if ($loan->isOverdue()): ?>
                                        <span class="badge badge-borrowed">Terlambat</span>
                                    <?php else: ?>
                                        <span class="badge badge-available">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="../actions/return.php" method="POST" class="table-actions form-confirm-return"
                                          data-item-name="<?= e($title) ?>" style="justify-content: flex-end;">
                                        <input type="hidden" name="transaction_id" value="<?= e($loan->getId()) ?>">
                                        <button type="submit" class="btn btn-outline btn-sm">↩️ Kembalikan</button>
                                    </form>
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

    <script>
        // Konfirmasi sebelum mengembalikan (validasi tetap di backend)
        document.querySelectorAll('.form-confirm-return').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var name = form.dataset.itemName || 'e-book ini';
                if (!window.confirm('Kembalikan "' + name + '" sekarang?')) {
                    event.preventDefault();
                    return;
                }
                var btn = form.querySelector('button[type="submit"]');
                if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }
            });
        });
    </script>
</body>
</html>
