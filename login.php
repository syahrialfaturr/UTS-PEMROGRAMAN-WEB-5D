<?php

declare(strict_types=1);

/**
 * Halaman Login - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;

// Jika sudah login, langsung alihkan sesuai role pengguna
if (SessionManager::isLoggedIn()) {
    $role = SessionManager::userRole();
    if ($role === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('member/dashboard.php');
    }
}

$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
$oldEmail = SessionManager::getOldInput('email');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - E-Library</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header & Navigasi -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">
                📚 <span>E-Library</span>
            </a>
            <ul class="navbar-nav">
                <li><a href="index.php" class="nav-link">Beranda</a></li>
                <li><a href="catalog.php" class="nav-link">Katalog</a></li>
                <li><a href="login.php" class="nav-link active">Masuk</a></li>
                <li><a href="register.php" class="nav-link">Daftar</a></li>
            </ul>
        </nav>
    </header>

    <!-- Konten Utama -->
    <main class="main-container">
        <section class="auth-wrapper">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Masuk ke E-Library</h1>
                    <p class="card-subtitle">Silakan masukkan kredensial akun terdaftar Anda.</p>
                </div>

                <!-- Notifikasi Flash Message -->
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

                <!-- Formulir Login -->
                <form id="loginForm" action="actions/login-process.php" method="POST" novalidate>
                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="nama@email.com"
                            value="<?= e($oldEmail) ?>"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-password-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Masukkan kata sandi Anda"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-password-btn" data-target="password" aria-label="Tampilkan password">
                                Tampilkan
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="loginSubmitBtn" class="btn btn-primary">
                        Masuk Sekarang
                    </button>
                </form>

                <!-- Informasi Akun Percobaan (Demo Seed) -->
                <div class="demo-box">
                    <h4>Demo Akun Pengujian:</h4>
                    <div class="demo-account">
                        <span><strong>Admin:</strong> <code>admin@example.com</code></span>
                        <span>Pass: <code>admin123</code></span>
                    </div>
                    <div class="demo-account">
                        <span><strong>Member:</strong> <code>member@example.com</code></span>
                        <span>Pass: <code>member123</code></span>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                    Belum memiliki akun? <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Daftar Akun Baru</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <!-- JavaScript Vanilla -->
    <script src="assets/js/app.js"></script>
</body>
</html>
