<?php

declare(strict_types=1);

/**
 * Dashboard Administrator - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Integrasi & Dashboard Admin (Tanggung Jawab: Arkaan)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BookService;
use App\Services\LoanService;

// Gatekeeper Proteksi Akses: Hanya Administrator yang diizinkan!
// Jika member mencoba masuk, akan dialihkan ke dashboard member
SessionManager::requireRole('admin', '../member/dashboard.php');

$currentUserSession = SessionManager::user();
$authService = new AuthService();
$bookService = new BookService();
$loanService = new LoanService(bookService: $bookService, authService: $authService);

// Statistik Keseluruhan Sistem
$allBooks = $bookService->getAll();
$totalBooks = count($allBooks);
$totalCategories = count($bookService->getAllCategories());

$allUsers = $authService->getAllUsers();
$allMembers = array_filter($allUsers, static fn(User $u): bool => $u->getRole() === 'member');
$totalMembers = count($allMembers);

$allTransactions = $loanService->getAllTransactions();
$totalTransactions = count($allTransactions);
$activeLoans = array_filter($allTransactions, static fn(Transaction $t): bool => $t->isActive());
$activeLoansCount = count($activeLoans);

// Demonstrasi Polimorfisme: Instansiasi objek bertipe parent User
/** @var User|null $adminModel */
$adminModel = $authService->findUserById((string)$currentUserSession['id']);

// Peta nama pengguna dan judul buku untuk tabel transaksi ringkas
$userNameMap = [];
foreach ($allUsers as $u) {
    $userNameMap[$u->getId()] = $u->getName();
}
$bookTitleMap = [];
foreach ($allBooks as $b) {
    $bookTitleMap[$b->getId()] = $b->getTitle();
}

// Ambil 5 transaksi terbaru
$recentTransactions = array_slice($allTransactions, 0, 5);

$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - E-Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Header & Navigasi Admin -->
    <header>
        <nav class="navbar" style="border-bottom: 2px solid var(--primary);">
            <a href="dashboard.php" class="navbar-brand">
                ⚙️ <span>E-Library Admin Panel</span>
            </a>
            <ul class="navbar-nav">
                <li><a href="dashboard.php" class="nav-link active" aria-current="page">Dashboard</a></li>
                <li><a href="books.php" class="nav-link">Kelola Buku</a></li>
                <li><a href="categories.php" class="nav-link">Kategori</a></li>
                <li><a href="members.php" class="nav-link">Daftar Member</a></li>
                <li><a href="transactions.php" class="nav-link">Transaksi</a></li>
                <li>
                    <span class="user-badge" style="background-color: #fee2e2; color: #991b1b;">
                        🛡️ <?= e($currentUserSession['name']) ?> (ADMIN)
                    </span>
                </li>
                <li>
                    <a href="../logout.php" class="btn btn-outline btn-sm">Keluar</a>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Konten Utama -->
    <main class="main-container">
        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" role="alert">
                <span>✓</span>
                <div><?= e($flashSuccess) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger" role="alert">
                <span>⚠️</span>
                <div><?= e($flashError) ?></div>
            </div>
        <?php endif; ?>

        <section class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="text-align: left;">
                <h1 class="card-title">Panel Kontrol Administrator</h1>
                <p class="card-subtitle">Selamat bertugas, <?= e($currentUserSession['name']) ?>. Anda memiliki hak akses manajemen penuh atas sistem perpustakaan.</p>
            </div>

            <!-- Demonstrasi Nyata Polimorfisme OOP -->
            <div class="alert alert-info" style="display: block;">
                <strong>🔍 Demonstrasi Polimorfisme PHP OOP (<code>$adminModel->getRoleDescription()</code>):</strong>
                <p style="margin-top: 0.5rem; font-style: italic;">
                    "<?= $adminModel ? e($adminModel->getRoleDescription()) : 'Memuat data administrator...' ?>"
                </p>
                <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                    *Method <code>getRoleDescription()</code> di-override oleh class <code>App\Models\Admin</code> dari parent <code>App\Models\User</code>.
                </small>
            </div>

            <!-- Grid Metrik Statistik Utama Admin -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-top: 1.5rem;">
                <!-- Total E-Book -->
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Total E-Book</h3>
                        <span style="font-size: 1.5rem;">📚</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0;">
                        <?= $totalBooks ?>
                    </p>
                    <a href="books.php" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Kelola Katalog &rarr;</a>
                </div>

                <!-- Total Member -->
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Jumlah Member</h3>
                        <span style="font-size: 1.5rem;">👥</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: var(--success); margin: 0.5rem 0;">
                        <?= $totalMembers ?>
                    </p>
                    <a href="members.php" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Daftar Member &rarr;</a>
                </div>

                <!-- Total Transaksi -->
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Total Transaksi</h3>
                        <span style="font-size: 1.5rem;">📑</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0.5rem 0;">
                        <?= $totalTransactions ?>
                    </p>
                    <a href="transactions.php" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Lihat Transaksi &rarr;</a>
                </div>

                <!-- Pinjaman Aktif -->
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Pinjaman Aktif</h3>
                        <span style="font-size: 1.5rem;">⏳</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: var(--warning); margin: 0.5rem 0;">
                        <?= $activeLoansCount ?>
                    </p>
                    <a href="transactions.php?status=borrowed" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Filter Pinjaman Aktif &rarr;</a>
                </div>

                <!-- Kategori E-Book -->
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Kategori</h3>
                        <span style="font-size: 1.5rem;">📁</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: var(--info); margin: 0.5rem 0;">
                        <?= $totalCategories ?>
                    </p>
                    <a href="categories.php" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Kelola Kategori &rarr;</a>
                </div>
            </div>

            <!-- Tautan Navigasi Cepat Admin -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
                <a href="book-form.php" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    ➕ Tambah E-Book Baru
                </a>
                <a href="books.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    📚 Manajemen Katalog
                </a>
                <a href="categories.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    📁 Manajemen Kategori
                </a>
                <a href="members.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    👥 Kelola Data Member
                </a>
                <a href="transactions.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    📊 Seluruh Transaksi
                </a>
            </div>
        </section>

        <!-- Tabel Transaksi Terbaru Sistem -->
        <section class="card">
            <div class="card-header" style="text-align: left; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 class="card-title" style="font-size: 1.25rem; margin-bottom: 0.25rem;">Transaksi Terbaru Perpustakaan</h2>
                    <p class="card-subtitle">Ringkasan aktivitas peminjaman dan pengembalian buku digital paling mutakhir.</p>
                </div>
                <a href="transactions.php" class="btn btn-outline btn-sm">Lihat Seluruh Transaksi &rarr;</a>
            </div>

            <?php if (empty($recentTransactions)): ?>
                <div class="empty-state" style="padding: 3rem 1.5rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📑</div>
                    <h3 style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 0.5rem;">Belum Ada Transaksi</h3>
                    <p style="color: var(--secondary); max-width: 480px; margin: 0 auto;">
                        Belum ada simulasi transaksi peminjaman atau pengembalian yang tercatat di dalam sistem.
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="margin-top: 1rem; overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                <th style="padding: 0.75rem;">ID Transaksi</th>
                                <th style="padding: 0.75rem;">Peminjam</th>
                                <th style="padding: 0.75rem;">Judul E-Book</th>
                                <th style="padding: 0.75rem;">Tanggal Pinjam</th>
                                <th style="padding: 0.75rem;">Jatuh Tempo</th>
                                <th style="padding: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $tx): ?>
                                <?php
                                $userName = $userNameMap[$tx->getUserId()] ?? 'User #' . $tx->getUserId();
                                $bookTitle = $bookTitleMap[$tx->getBookId()] ?? 'E-Book #' . $tx->getBookId();
                                $isBorrowed = $tx->isActive();
                                $isOverdue = $tx->isOverdue();
                                ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.75rem; font-family: monospace; font-size: 0.85rem;">
                                        <?= e($tx->getId()) ?>
                                    </td>
                                    <td style="padding: 0.75rem; font-weight: 600;">
                                        <?= e($userName) ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">
                                            <?= e($tx->getUserId()) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <strong><?= e($bookTitle) ?></strong>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem;">
                                        <?= e(date('d M Y, H:i', strtotime($tx->getBorrowedAt()))) ?>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem;">
                                        <span style="<?= ($isBorrowed && $isOverdue) ? 'color: var(--danger); font-weight: 700;' : '' ?>">
                                            <?= e(date('d M Y, H:i', strtotime($tx->getDueAt()))) ?>
                                        </span>
                                        <?php if ($isBorrowed && $isOverdue): ?>
                                            <span class="badge badge-danger" style="margin-left: 0.25rem;">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php if ($isBorrowed): ?>
                                            <span class="badge badge-warning">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="../assets/js/app.js"></script>
</body>
</html>
