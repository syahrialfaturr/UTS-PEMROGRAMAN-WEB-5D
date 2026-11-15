<?php

declare(strict_types=1);

/**
 * Halaman Detail E-Book - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Tampilan Detail E-book (Tanggung Jawab: Mail)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Book;
use App\Services\BookService;

$bookService = new BookService();

$bookId = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
$book = $bookId !== '' ? $bookService->getById($bookId) : null;
$category = $book ? $bookService->getCategoryById($book->getCategoryId()) : null;

$currentUser = SessionManager::user();
$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $book ? e($book->getTitle()) : 'E-Book Tidak Ditemukan' ?> - E-Library</title>
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
                <li><a href="catalog.php" class="nav-link active">Katalog</a></li>
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li><a href="admin/dashboard.php" class="nav-link">Dashboard Admin</a></li>
                        <li><a href="admin/books.php" class="nav-link">Kelola Buku</a></li>
                    <?php else: ?>
                        <li><a href="member/dashboard.php" class="nav-link">Dashboard Member</a></li>
                    <?php endif; ?>
                    <li>
                        <span class="user-badge">
                            👤 <?= e($currentUser['name']) ?>
                        </span>
                    </li>
                    <li><a href="logout.php" class="btn btn-outline btn-sm">Keluar</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Masuk</a></li>
                    <li><a href="register.php" class="btn btn-primary btn-sm">Daftar Akun</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="main-container">

        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" role="alert">
                <span>✓ <?= e($flashSuccess) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-danger" role="alert">
                <span>⚠️ <?= e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!$book): ?>
            <!-- Jika Buku Tidak Ditemukan -->
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <h1 class="empty-state-title">E-Book Tidak Ditemukan</h1>
                <p class="empty-state-text">
                    Buku digital dengan ID "<strong><?= e($bookId) ?></strong>" tidak terdaftar atau mungkin telah dihapus dari koleksi perpustakaan.
                </p>
                <a href="catalog.php" class="btn btn-primary" style="width: auto; padding: 0.65rem 1.5rem;">
                    &larr; Kembali ke Katalog
                </a>
            </div>
        <?php else: ?>
            <!-- Navigasi Breadcrumbs -->
            <nav style="margin-bottom: 1.5rem; font-size: 0.9rem; color: var(--text-muted);" aria-label="Breadcrumb">
                <a href="index.php" class="nav-link" style="display: inline;">Beranda</a> &rsaquo;
                <a href="catalog.php" class="nav-link" style="display: inline;">Katalog</a> &rsaquo;
                <span style="color: var(--text-main); font-weight: 600;"><?= e($book->getTitle()) ?></span>
            </nav>

            <article class="card" style="padding: 2.5rem;">
                <div class="detail-grid">
                    <!-- Kolom Kiri: Cover Buku -->
                    <div class="detail-cover-box">
                        <img 
                            src="<?= e(!empty($book->getCover()) ? $book->getCover() : 'assets/images/default-book.png') ?>" 
                            alt="Sampul <?= e($book->getTitle()) ?>"
                            class="detail-cover-img"
                            onerror="this.src='assets/images/default-book.png';"
                        >
                    </div>

                    <!-- Kolom Kanan: Informasi & Metadata Buku -->
                    <div class="detail-content">
                        <!-- Label Kategori & Status -->
                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap;">
                            <span class="badge badge-category">
                                📁 <?= e($category ? $category->getName() : 'Umum') ?>
                            </span>
                            <?php if ($book->isAvailable()): ?>
                                <span class="badge badge-available">
                                    ● Tersedia untuk Dipinjam
                                </span>
                            <?php else: ?>
                                <span class="badge badge-borrowed">
                                    ● Sedang Dipinjam
                                </span>
                            <?php endif; ?>
                            <span class="badge badge-year">
                                🗓️ Tahun <?= e((string)$book->getYear()) ?>
                            </span>
                        </div>

                        <!-- Judul Buku -->
                        <h1 class="detail-title"><?= e($book->getTitle()) ?></h1>
                        <p style="color: var(--secondary); font-size: 1.05rem; margin-bottom: 1rem;">
                            Penulis: <strong><?= e($book->getAuthor()) ?></strong>
                        </p>

                        <!-- Tabel Spesifikasi Buku -->
                        <table class="detail-meta-table">
                            <tbody>
                                <tr>
                                    <th>Kode E-Book</th>
                                    <td><code><?= e($book->getId()) ?></code></td>
                                </tr>
                                <tr>
                                    <th>Kategori</th>
                                    <td><?= e($category ? $category->getName() : '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Tahun Terbit</th>
                                    <td><?= e((string)$book->getYear()) ?></td>
                                </tr>
                                <tr>
                                    <th>Status Koleksi</th>
                                    <td>
                                        <?= $book->isAvailable() ? 'Tersedia di Rak Digital' : 'Sedang Dipinjam oleh Anggota Lain' ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Sinopsis / Deskripsi Buku -->
                        <section class="detail-description-section">
                            <h3>Sinopsis & Ringkasan</h3>
                            <div class="detail-description-text">
                                <?= e($book->getDescription()) ?>
                            </div>
                        </section>

                        <!-- Kotak Aksi Peminjaman / Manajemen -->
                        <div class="detail-actions-box">
                            <?php if (!$currentUser): ?>
                                <!-- Kondisi 1: Guest (Belum Login) -->
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                    <div>
                                        <h4 style="font-size: 1rem; color: var(--text-main); margin-bottom: 0.25rem;">
                                            Tertarik membaca e-book ini?
                                        </h4>
                                        <p style="font-size: 0.875rem; color: var(--text-muted);">
                                            Silakan masuk sebagai Member untuk melakukan peminjaman buku digital ini.
                                        </p>
                                    </div>
                                    <div style="display: flex; gap: 0.75rem;">
                                        <a href="login.php" class="btn btn-primary" style="width: auto;">
                                            🔑 Masuk untuk Meminjam
                                        </a>
                                        <a href="catalog.php" class="btn btn-outline" style="width: auto;">
                                            &larr; Kembali
                                        </a>
                                    </div>
                                </div>
                            <?php elseif ($currentUser['role'] === 'member'): ?>
                                <!-- Kondisi 2: Member Login -->
                                <?php if ($book->isAvailable()): ?>
                                    <form action="actions/borrow.php" method="POST" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                        <input type="hidden" name="book_id" value="<?= e($book->getId()) ?>">
                                        <div>
                                            <h4 style="font-size: 1.05rem; color: var(--success); margin-bottom: 0.25rem;">
                                                ✓ E-Book Siap Dipinjam
                                            </h4>
                                            <p style="font-size: 0.875rem; color: var(--text-muted);">
                                                Masa pinjam standar 7 hari. Anda dapat membacanya langsung setelah konfirmasi.
                                            </p>
                                        </div>
                                        <div style="display: flex; gap: 0.75rem;">
                                            <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.85rem 1.75rem;">
                                                📥 Pinjam E-Book Sekarang
                                            </button>
                                            <a href="catalog.php" class="btn btn-outline" style="width: auto;">
                                                &larr; Kembali ke Katalog
                                            </a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                        <div>
                                            <h4 style="font-size: 1.05rem; color: var(--danger); margin-bottom: 0.25rem;">
                                                ⚠️ E-Book Sedang Tidak Tersedia
                                            </h4>
                                            <p style="font-size: 0.875rem; color: var(--text-muted);">
                                                Buku ini sedang dipinjam oleh anggota lain. Silakan periksa kembali beberapa hari lagi.
                                            </p>
                                        </div>
                                        <div style="display: flex; gap: 0.75rem;">
                                            <button type="button" class="btn btn-outline" disabled style="opacity: 0.6; cursor: not-allowed; width: auto;">
                                                🚫 Tidak Dapat Dipinjam
                                            </button>
                                            <a href="catalog.php" class="btn btn-outline" style="width: auto;">
                                                &larr; Cari Buku Lain
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($currentUser['role'] === 'admin'): ?>
                                <!-- Kondisi 3: Administrator Login -->
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                    <div>
                                        <h4 style="font-size: 1.05rem; color: var(--primary); margin-bottom: 0.25rem;">
                                            🛠️ Manajemen Administrator
                                        </h4>
                                        <p style="font-size: 0.875rem; color: var(--text-muted);">
                                            Anda masuk sebagai Administrator. Anda dapat mengedit metadata atau mengatur status buku ini.
                                        </p>
                                    </div>
                                    <div style="display: flex; gap: 0.75rem;">
                                        <a href="admin/book-form.php?id=<?= urlencode($book->getId()) ?>" class="btn btn-primary" style="width: auto;">
                                            ✏️ Edit E-Book
                                        </a>
                                        <a href="admin/books.php" class="btn btn-outline" style="width: auto;">
                                            📋 Kelola Katalog Admin
                                        </a>
                                        <a href="catalog.php" class="btn btn-outline" style="width: auto;">
                                            &larr; Katalog Publik
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </article>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
