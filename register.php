<?php

declare(strict_types=1);

/**
 * Halaman Registrasi Member Baru - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;

// Jika sudah login, alihkan ke dashboard masing-masing
if (SessionManager::isLoggedIn()) {
    $role = SessionManager::userRole();
    if ($role === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('member/dashboard.php');
    }
}

$flashError = SessionManager::getFlash('error');
$oldName = SessionManager::getOldInput('name');
$oldEmail = SessionManager::getOldInput('email');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Member - E-Library</title>
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
                <li><a href="login.php" class="nav-link">Masuk</a></li>
                <li><a href="register.php" class="nav-link active">Daftar</a></li>
            </ul>
        </nav>
    </header>

    <!-- Konten Utama -->
    <main class="main-container">
        <section class="auth-wrapper">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Daftar Anggota Baru</h1>
                    <p class="card-subtitle">Buat akun Member E-Library untuk mulai meminjam e-book.</p>
                </div>

                <!-- Notifikasi Flash Error -->
                <?php if ($flashError): ?>
                    <div class="alert alert-danger" role="alert">
                        <span>⚠️</span>
                        <div><?= e($flashError) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Formulir Pendaftaran -->
                <form id="registerForm" action="actions/register-process.php" method="POST" novalidate>
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            placeholder="Contoh: Syahrial Fathur"
                            value="<?= e($oldName) ?>"
                            minlength="3"
                            required
                            autocomplete="name"
                        >
                        <span class="form-helper">Minimal 3 karakter.</span>
                    </div>

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
                        <span class="form-helper">Email digunakan untuk masuk dan harus unik.</span>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-password-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Minimal 6 karakter"
                                minlength="6"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password-btn" data-target="password" aria-label="Tampilkan password">
                                Tampilkan
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi</label>
                        <div class="input-password-wrapper">
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Ulangi kata sandi Anda"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password-btn" data-target="confirm_password" aria-label="Tampilkan konfirmasi password">
                                Tampilkan
                            </button>
                        </div>
                        <span id="confirmPasswordFeedback" class="form-feedback"></span>
                    </div>

                    <!-- Hidden input default role = member -->
                    <input type="hidden" name="role" value="member">

                    <button type="submit" id="registerSubmitBtn" class="btn btn-primary" style="margin-top: 1rem;">
                        Daftar Sebagai Member
                    </button>
                </form>

                <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                    Sudah memiliki akun? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Masuk di sini</a>
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
