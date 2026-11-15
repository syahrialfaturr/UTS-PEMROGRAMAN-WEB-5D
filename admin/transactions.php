<?php

declare(strict_types=1);

/**
 * Seluruh Transaksi - Area Administrator
 * Modul Transaksi (Tanggung Jawab: Farid)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Transaction;
use App\Services\AuthService;
use App\Services\BookService;
use App\Services\LoanService;

if (!SessionManager::isLoggedIn()) {
    SessionManager::setFlash('error', 'Silakan login terlebih dahulu.');
    redirect('../login.php');
}
SessionManager::requireRole('admin', '../index.php');

$currentUser = SessionManager::user();
$bookService = new BookService();
$authService = new AuthService();
$loanService = new LoanService(bookService: $bookService, authService: $authService);

// Filter status (whitelist)
$status = (string)($_GET['status'] ?? '');
if (!in_array($status, [Transaction::STATUS_BORROWED, Transaction::STATUS_RETURNED], true)) {
    $status = '';
}

$transactions = $loanService->getAllTransactions($status !== '' ? $status : null);
$all = $status === '' ? $transactions : $loanService->getAllTransactions();
$activeCount = count(array_filter($all, static fn(Transaction $t): bool => $t->isActive()));
$overdueCount = count(array_filter($all, static fn(Transaction $t): bool => $t->isOverdue()));

$bookTitles = [];
foreach ($bookService->getAll() as $book) {
    $bookTitles[$book->getId()] = $book->getTitle();
}
$userNames = [];
foreach ($authService->getAllUsers() as $user) {
    $userNames[$user->getId()] = $user->getName();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seluruh Transaksi - Admin E-Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header>
        <nav class="navbar" style="border-bottom: 2px solid var(--primary);">
            <a href="dashboard.php" class="navbar-brand">⚙️ <span>E-Library Admin Panel</span></a>
            <ul class="navbar-nav">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="books.php" class="nav-link">Kelola Buku</a></li>
                <li><a href="categories.php" class="nav-link">Kategori</a></li>
                <li><a href="members.php" class="nav-link">Daftar Member</a></li>
                <li><a href="transactions.php" class="nav-link active" aria-current="page">Transaksi</a></li>
                <li><span class="user-badge" style="background-color: #fee2e2; color: #991b1b;">🛡️ <?= e($currentUser['name']) ?> (ADMIN)</span></li>
                <li><a href="../logout.php" class="btn btn-outline btn-sm">Keluar</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-container">
        <section class="admin-header">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800;">Seluruh Transaksi</h1>
                <p style="color: var(--secondary);">
                    Total <?= count($all) ?> transaksi · <?= $activeCount ?> aktif · <?= $overdueCount ?> terlambat
                </p>
            </div>
        </section>

        <section class="catalog-filter-card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
            <form action="transactions.php" method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="trxStatus" class="form-label">Status</label>
                    <select id="trxStatus" name="status" class="form-input" onchange="this.form.submit()">
                        <option value="" <?= $status === '' ? 'selected' : '' ?>>Semua</option>
                        <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Dipinjam</option>
                        <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Dikembalikan</option>
                    </select>
                </div>
                <noscript><button type="submit" class="btn btn-primary">Filter</button></noscript>
            </form>
        </section>

        <section class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">ID Transaksi</th>
                        <th scope="col">Member</th>
                        <th scope="col">E-Book</th>
                        <th scope="col">Dipinjam</th>
                        <th scope="col">Jatuh Tempo</th>
                        <th scope="col">Dikembalikan</th>
                        <th scope="col" style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                📭 Belum ada transaksi.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td><code><?= e($t->getId()) ?></code></td>
                                <td>
                                    <?= e($userNames[$t->getUserId()] ?? 'Tidak dikenal') ?>
                                    <br><small style="color: var(--text-muted);"><?= e($t->getUserId()) ?></small>
                                </td>
                                <td><?= e($bookTitles[$t->getBookId()] ?? $t->getBookId() . ' (dihapus)') ?></td>
                                <td><?= e($t->getBorrowedAt()) ?></td>
                                <td><?= e($t->getDueAt()) ?></td>
                                <td><?= e($t->getReturnedAt() ?? '—') ?></td>
                                <td style="text-align: center;">
                                    <?php if ($t->isOverdue()): ?>
                                        <span class="badge badge-borrowed">Terlambat</span>
                                    <?php elseif ($t->isActive()): ?>
                                        <span class="badge badge-borrowed">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge badge-available">Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="../assets/js/app.js"></script>
</body>
</html>
