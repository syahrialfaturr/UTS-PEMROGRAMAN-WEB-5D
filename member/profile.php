<?php

declare(strict_types=1);

/**
 * Profil Anggota (Member) - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Integrasi & Profil Member (Tanggung Jawab: Arkaan)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Member;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BookService;
use App\Services\LoanService;

// Gatekeeper: Hanya member yang boleh mengakses halaman ini
SessionManager::requireRole('member', '../login.php');

$currentUserSession = SessionManager::user();
$authService = new AuthService();
$bookService = new BookService();
$loanService = new LoanService(bookService: $bookService, authService: $authService);

/** @var User|null $userModel */
$userModel = $authService->findUserById((string)$currentUserSession['id']);
$memberId = (string)$currentUserSession['id'];

$activeLoans = $loanService->getMemberLoans($memberId);
$historyLoans = $loanService->getMemberHistory($memberId);
$activeCount = count($activeLoans);
$historyCount = count($historyLoans);
$totalLoansEver = $activeCount + $historyCount;

$loanLimit = ($userModel instanceof Member) ? $userModel->getLoanLimit() : 3;
$canBorrowMore = ($userModel instanceof Member) ? $userModel->canBorrow($activeCount) : false;

// Cari tanggal bergabung dari storage
$userRaw = (new \App\Core\JsonStorage(STORAGE_PATH . DIRECTORY_SEPARATOR . 'users.json'))->findById($memberId);
$createdAt = $userRaw['created_at'] ?? '2026-11-15 00:00:00';

$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Anggota - E-Library</title>
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
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="loans.php" class="nav-link">Pinjaman Saya</a></li>
                <li><a href="history.php" class="nav-link">Riwayat</a></li>
                <li><a href="profile.php" class="nav-link active" aria-current="page">Profil</a></li>
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

        <section class="admin-header">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main);">
                    Profil Anggota Perpustakaan
                </h1>
                <p style="color: var(--secondary); font-size: 0.95rem;">
                    Informasi akun, hak akses peminjaman, dan riwayat aktivitas keanggotaan Anda.
                </p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline" style="padding: 0.8rem 1.4rem;">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </section>

        <!-- Demonstrasi Nyata Polimorfisme OOP -->
        <div class="alert alert-info" style="display: block; margin-bottom: 2rem;">
            <strong>🔍 Demonstrasi Polimorfisme PHP OOP (<code>$userModel->getRoleDescription()</code>):</strong>
            <p style="margin-top: 0.5rem; font-style: italic;">
                "<?= $userModel ? e($userModel->getRoleDescription()) : 'Memuat data profil...' ?>"
            </p>
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                *Objek diinstansiasi sebagai <code>App\Models\Member</code> turunan (Inheritance) dari <code>App\Models\User</code>.
            </small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
            <!-- Kolom Informasi Pribadi -->
            <section class="card">
                <div class="card-header" style="text-align: left;">
                    <h2 class="card-title" style="font-size: 1.25rem;">Informasi Akun</h2>
                    <p class="card-subtitle">Data akun terdaftar Anda pada sistem E-Library.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">ID Anggota</span>
                        <code><?= e($userModel?->getId() ?? $memberId) ?></code>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">Nama Lengkap</span>
                        <strong><?= e($userModel?->getName() ?? $currentUserSession['name']) ?></strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">Alamat Email</span>
                        <span><?= e($userModel?->getEmail() ?? $currentUserSession['email']) ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">Peran Sistem</span>
                        <span class="badge badge-primary"><?= strtoupper(e($userModel?->getRole() ?? 'MEMBER')) ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem;">
                        <span style="color: var(--secondary);">Tanggal Terdaftar</span>
                        <span><?= e(date('d F Y, H:i', strtotime($createdAt))) ?></span>
                    </div>
                </div>
            </section>

            <!-- Kolom Kuota dan Statistik Peminjaman -->
            <section class="card">
                <div class="card-header" style="text-align: left;">
                    <h2 class="card-title" style="font-size: 1.25rem;">Statistik & Kuota Peminjaman</h2>
                    <p class="card-subtitle">Hak dan batas peminjaman buku digital aktif Anda.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">Batas Maksimal Simultan</span>
                        <strong><?= $loanLimit ?> E-Book</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">Pinjaman Sedang Aktif</span>
                        <span style="font-weight: 700; color: var(--primary);"><?= $activeCount ?> E-Book</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">Buku Sudah Dikembalikan</span>
                        <span style="font-weight: 700; color: var(--success);"><?= $historyCount ?> Transaksi</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                        <span style="color: var(--secondary);">Total Transaksi Sepanjang Waktu</span>
                        <strong><?= $totalLoansEver ?> Transaksi</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.5rem;">
                        <span style="color: var(--secondary);">Hak Meminjam Buku Baru</span>
                        <?php if ($canBorrowMore): ?>
                            <span class="badge badge-success">Bisa Meminjam</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Batas Tercapai</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                    <a href="loans.php" class="btn btn-primary btn-sm">Buka Pinjaman Aktif</a>
                    <a href="history.php" class="btn btn-outline btn-sm">Buka Riwayat</a>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="../assets/js/app.js"></script>
</body>
</html>
