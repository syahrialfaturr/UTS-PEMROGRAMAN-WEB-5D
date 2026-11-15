<?php

declare(strict_types=1);

/**
 * Dashboard Member - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\User;
use App\Services\AuthService;

// Gatekeeper Proteksi Akses: Hanya pengguna dengan peran 'member' yang diizinkan
SessionManager::requireRole('member', '../login.php');

$currentUserSession = SessionManager::user();
$authService = new AuthService();

// Demonstrasi Polimorfisme: Instansiasi objek bertipe parent User
/** @var User|null $userModel */
$userModel = $authService->findUserById((string)$currentUserSession['id']);

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
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="loans.php" class="nav-link">Pinjaman Saya</a></li>
                <li><a href="history.php" class="nav-link">Riwayat</a></li>
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

        <section class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="text-align: left;">
                <h1 class="card-title">Selamat Datang, <?= e($currentUserSession['name']) ?>!</h1>
                <p class="card-subtitle">Akun Anda terdaftar dengan ID: <code><?= e($currentUserSession['id']) ?></code></p>
            </div>

            <!-- Demonstrasi Nyata Polimorfisme OOP -->
            <div class="alert alert-info" style="display: block;">
                <strong>🔍 Demonstrasi Polimorfisme PHP OOP (<code>$userModel->getRoleDescription()</code>):</strong>
                <p style="margin-top: 0.5rem; font-style: italic;">
                    "<?= $userModel ? e($userModel->getRoleDescription()) : 'Memuat data pengguna...' ?>"
                </p>
                <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                    *Method <code>getRoleDescription()</code> di-override oleh class <code>App\Models\Member</code> dari parent <code>App\Models\User</code>.
                </small>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #fafafa;">
                    <h3 style="font-size: 1rem; color: var(--secondary);">Status Akun</h3>
                    <p style="font-size: 1.25rem; font-weight: 700; color: var(--success); margin-top: 0.5rem;">Aktif (Member)</p>
                </div>
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #fafafa;">
                    <h3 style="font-size: 1rem; color: var(--secondary);">Email Terdaftar</h3>
                    <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-main); margin-top: 0.5rem;"><?= e($currentUserSession['email']) ?></p>
                </div>
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #fafafa;">
                    <h3 style="font-size: 1rem; color: var(--secondary);">Batas Pinjaman Simultan</h3>
                    <p style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-top: 0.5rem;">
                        <?= ($userModel instanceof \App\Models\Member) ? $userModel->getLoanLimit() : 3 ?> Buku
                    </p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

</body>
</html>
