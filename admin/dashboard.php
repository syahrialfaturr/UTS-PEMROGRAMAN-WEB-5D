<?php

declare(strict_types=1);

/**
 * Dashboard Administrator - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\User;
use App\Services\AuthService;

// Gatekeeper Proteksi Akses: Hanya Administrator yang diizinkan!
// Jika member mencoba masuk, akan dialihkan ke dashboard member
SessionManager::requireRole('admin', '../member/dashboard.php');

$currentUserSession = SessionManager::user();
$authService = new AuthService();

// Demonstrasi Polimorfisme: Instansiasi objek bertipe parent User
/** @var User|null $adminModel */
$adminModel = $authService->findUserById((string)$currentUserSession['id']);

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
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
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
                <p class="card-subtitle">Selamat bertugas, <?= e($currentUserSession['name']) ?>. Anda memiliki hak akses manajemen penuh.</p>
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

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #fafafa;">
                    <h3 style="font-size: 1rem; color: var(--secondary);">Wewenang Kelola Buku</h3>
                    <p style="font-size: 1.1rem; font-weight: 700; color: var(--success); margin-top: 0.5rem;">
                        <?= ($adminModel instanceof \App\Models\Admin && $adminModel->canManageBooks()) ? 'Tersedia (Aktif)' : 'Nonaktif' ?>
                    </p>
                </div>
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #fafafa;">
                    <h3 style="font-size: 1rem; color: var(--secondary);">Wewenang Kelola Member</h3>
                    <p style="font-size: 1.1rem; font-weight: 700; color: var(--success); margin-top: 0.5rem;">
                        <?= ($adminModel instanceof \App\Models\Admin && $adminModel->canManageMembers()) ? 'Tersedia (Aktif)' : 'Nonaktif' ?>
                    </p>
                </div>
                <div style="border: 1px solid var(--border); padding: 1.25rem; border-radius: var(--radius-sm); background: #fafafa;">
                    <h3 style="font-size: 1rem; color: var(--secondary);">Admin Terotentikasi</h3>
                    <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-main); margin-top: 0.5rem;"><?= e($currentUserSession['email']) ?></p>
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
