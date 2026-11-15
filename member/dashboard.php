<?php

declare(strict_types=1);

/**
 * Dashboard Member - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Integrasi & Dashboard Member (Tanggung Jawab: Arkaan)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BookService;
use App\Services\LoanService;

// Gatekeeper Proteksi Akses: Hanya pengguna dengan peran 'member' yang diizinkan
SessionManager::requireRole('member', '../login.php');

$currentUserSession = SessionManager::user();
$authService = new AuthService();
$bookService = new BookService();
$loanService = new LoanService(bookService: $bookService, authService: $authService);

// Demonstrasi Polimorfisme: Instansiasi objek bertipe parent User
/** @var User|null $userModel */
$userModel = $authService->findUserById((string)$currentUserSession['id']);
$memberId = (string)$currentUserSession['id'];

// Ambil data transaksi pinjaman member
$activeLoans = $loanService->getMemberLoans($memberId);
$historyLoans = $loanService->getMemberHistory($memberId);
$activeCount = count($activeLoans);
$historyCount = count($historyLoans);
$loanLimit = ($userModel instanceof Member) ? $userModel->getLoanLimit() : 3;
$remainingQuota = max(0, $loanLimit - $activeCount);

// Map judul buku untuk efisiensi tampilan
$bookMap = [];
foreach ($bookService->getAll() as $book) {
    $bookMap[$book->getId()] = $book;
}

$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member - E-Library</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Header & Navigasi Member -->
    <header>
        <nav class="navbar">
            <a href="../index.php" class="navbar-brand">
                📚 <span>E-Library Member Area</span>
            </a>
            <ul class="navbar-nav">
                <li><a href="../catalog.php" class="nav-link">Katalog Buku</a></li>
                <li><a href="dashboard.php" class="nav-link active" aria-current="page">Dashboard</a></li>
                <li><a href="loans.php" class="nav-link">Pinjaman Saya</a></li>
                <li><a href="history.php" class="nav-link">Riwayat</a></li>
                <li><a href="profile.php" class="nav-link">Profil</a></li>
                <li>
                    <span class="user-badge">
                        👤 <?= e($currentUserSession['name']) ?> (<?= e(strtoupper($currentUserSession['role'])) ?>)
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

        <!-- Welcome Banner & OOP Polymorphism Card -->
        <section class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="text-align: left;">
                <h1 class="card-title">Selamat Datang, <?= e($currentUserSession['name']) ?>!</h1>
                <p class="card-subtitle">ID Anggota: <code><?= e($currentUserSession['id']) ?></code> &bull; Email: <strong><?= e($currentUserSession['email']) ?></strong></p>
            </div>

            <!-- Demonstrasi Nyata Polimorfisme OOP -->
            <div class="alert alert-info" style="display: block; margin-top: 1rem;">
                <strong>🔍 Demonstrasi Polimorfisme PHP OOP (<code>$userModel->getRoleDescription()</code>):</strong>
                <p style="margin-top: 0.5rem; font-style: italic;">
                    "<?= $userModel ? e($userModel->getRoleDescription()) : 'Memuat data pengguna...' ?>"
                </p>
                <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                    *Method <code>getRoleDescription()</code> di-override oleh class <code>App\Models\Member</code> dari parent <code>App\Models\User</code>.
                </small>
            </div>

            <!-- Kartu Statistik Utama Dashboard Member -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1.5rem;">
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Pinjaman Aktif</h3>
                        <span style="font-size: 1.5rem;">📖</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: var(--primary); margin: 0.5rem 0;">
                        <?= $activeCount ?> <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">/ <?= $loanLimit ?></span>
                    </p>
                    <a href="loans.php" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Lihat Pinjaman Saya &rarr;</a>
                </div>

                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Riwayat Selesai</h3>
                        <span style="font-size: 1.5rem;">✅</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: var(--success); margin: 0.5rem 0;">
                        <?= $historyCount ?>
                    </p>
                    <a href="history.php" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Lihat Riwayat &rarr;</a>
                </div>

                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Sisa Kuota Pinjam</h3>
                        <span style="font-size: 1.5rem;">🎫</span>
                    </div>
                    <p style="font-size: 2rem; font-weight: 800; color: <?= $remainingQuota > 0 ? 'var(--info)' : 'var(--danger)' ?>; margin: 0.5rem 0;">
                        <?= $remainingQuota ?> <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted);">Buku</span>
                    </p>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">
                        <?= $remainingQuota > 0 ? 'Dapat meminjam e-book' : 'Batas kuota tercapai' ?>
                    </span>
                </div>

                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #ffffff; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 0.95rem; color: var(--secondary); margin: 0;">Status Keanggotaan</h3>
                        <span style="font-size: 1.5rem;">👤</span>
                    </div>
                    <p style="font-size: 1.15rem; font-weight: 700; color: var(--success); margin: 0.75rem 0 0.5rem 0;">
                        Anggota Aktif
                    </p>
                    <a href="profile.php" class="nav-link" style="font-size: 0.85rem; font-weight: 600; padding: 0;">Lihat Profil Lengkap &rarr;</a>
                </div>
            </div>

            <!-- Tautan Navigasi Cepat -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
                <a href="../catalog.php" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    🔍 Jelajahi Katalog E-Book
                </a>
                <a href="loans.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    📚 Kelola Pinjaman Saya (<?= $activeCount ?>)
                </a>
                <a href="history.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    📜 Riwayat Transaksi (<?= $historyCount ?>)
                </a>
                <a href="profile.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                    👤 Profil Anggota
                </a>
            </div>
        </section>

        <!-- Daftar Pinjaman Aktif Terbaru -->
        <section class="card">
            <div class="card-header" style="text-align: left; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 class="card-title" style="font-size: 1.25rem; margin-bottom: 0.25rem;">Pinjaman Aktif Terbaru</h2>
                    <p class="card-subtitle">E-book yang sedang Anda pinjam dan harus dikembalikan sebelum batas waktu.</p>
                </div>
                <?php if ($activeCount > 0): ?>
                    <a href="loans.php" class="btn btn-outline btn-sm">Lihat Semua di Pinjaman Saya &rarr;</a>
                <?php endif; ?>
            </div>

            <?php if ($activeCount === 0): ?>
                <div class="empty-state" style="padding: 3rem 1.5rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                    <h3 style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 0.5rem;">Tidak Ada Pinjaman Aktif</h3>
                    <p style="color: var(--secondary); max-width: 480px; margin: 0 auto 1.5rem auto;">
                        Anda saat ini tidak memiliki e-book yang sedang dipinjam. Jelajahi katalog perpustakaan kami dan temukan berbagai e-book berkualitas!
                    </p>
                    <a href="../catalog.php" class="btn btn-primary">
                        Jelajahi Katalog E-Book Sekarang
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="margin-top: 1rem; overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                <th style="padding: 0.75rem;">ID Transaksi</th>
                                <th style="padding: 0.75rem;">Judul E-Book</th>
                                <th style="padding: 0.75rem;">Tanggal Pinjam</th>
                                <th style="padding: 0.75rem;">Batas Kembali (Due)</th>
                                <th style="padding: 0.75rem;">Status</th>
                                <th style="padding: 0.75rem; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeLoans as $loan): ?>
                                <?php
                                $book = $bookMap[$loan->getBookId()] ?? null;
                                $isOverdue = $loan->isOverdue();
                                ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.75rem; font-family: monospace; font-size: 0.85rem;">
                                        <?= e($loan->getId()) ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <strong><?= $book ? e($book->getTitle()) : 'E-Book #' . e($loan->getBookId()) ?></strong>
                                        <?php if ($book): ?>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= e($book->getAuthor()) ?> (<?= $book->getYear() ?>)</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem;">
                                        <?= e(date('d M Y, H:i', strtotime($loan->getBorrowedAt()))) ?>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem;">
                                        <span style="<?= $isOverdue ? 'color: var(--danger); font-weight: 700;' : '' ?>">
                                            <?= e(date('d M Y, H:i', strtotime($loan->getDueAt()))) ?>
                                        </span>
                                        <?php if ($isOverdue): ?>
                                            <span class="badge badge-danger" style="margin-left: 0.25rem;">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <span class="badge badge-warning">Dipinjam</span>
                                    </td>
                                    <td style="padding: 0.75rem; text-align: right;">
                                        <form method="POST" action="../actions/return.php" style="display: inline;"
                                              onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan e-book <?= $book ? e(addslashes($book->getTitle())) : '' ?>?');">
                                            <input type="hidden" name="transaction_id" value="<?= e($loan->getId()) ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="color: var(--success); border-color: var(--success);">
                                                Kembalikan
                                            </button>
                                        </form>
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
