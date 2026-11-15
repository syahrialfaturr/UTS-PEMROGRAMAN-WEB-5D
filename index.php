<?php

declare(strict_types=1);

/**
 * Beranda Publik - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Services\BookService;

$user = SessionManager::user();
$bookService = new BookService();
$featuredBooks = array_slice($bookService->getAll(), 0, 4);
$categories = $bookService->getAllCategories();
$categoryMap = $bookService->getCategoryMap();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - E-Library Perpustakaan Digital</title>
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
                <li><a href="index.php" class="nav-link active">Beranda</a></li>
                <li><a href="catalog.php" class="nav-link">Katalog</a></li>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li><a href="admin/dashboard.php" class="nav-link">Dashboard Admin</a></li>
                    <?php else: ?>
                        <li><a href="member/dashboard.php" class="nav-link">Dashboard Member</a></li>
                    <?php endif; ?>
                    <li>
                        <span class="user-badge">
                            👤 <?= e($user['name']) ?>
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

    <!-- Hero Section -->
    <main class="main-container">
        <section class="card" style="text-align: center; padding: 3.5rem 1.5rem; margin-top: 1rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem;">
                Selamat Datang di E-Library
            </h1>
            <p style="font-size: 1.15rem; color: var(--secondary); max-width: 650px; margin: 0 auto 2rem;">
                Platform perpustakaan digital interaktif berbasis modern PHP 8.x OOP tanpa framework.
                Temukan katalog buku menarik, lakukan peminjaman mudah, dan kelola koleksi bacaan Anda.
            </p>
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-primary" style="width: auto; padding: 0.85rem 1.75rem;">
                            Buka Panel Admin
                        </a>
                    <?php else: ?>
                        <a href="member/dashboard.php" class="btn btn-primary" style="width: auto; padding: 0.85rem 1.75rem;">
                            Buka Dashboard Member
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary" style="width: auto; padding: 0.85rem 1.75rem;">
                        Masuk ke Akun
                    </a>
                    <a href="register.php" class="btn btn-outline" style="width: auto; padding: 0.85rem 1.75rem;">
                        Daftar Sebagai Member
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Kategori Populer -->
        <section style="margin-top: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">📁 Kategori E-Book</h2>
                    <p style="color: var(--secondary); font-size: 0.9rem;">Pilih topik bidang minat yang ingin Anda pelajari</p>
                </div>
                <a href="catalog.php" class="nav-link" style="font-weight: 600;">Lihat Semua &rarr;</a>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                <?php foreach ($categories as $cat): ?>
                    <a href="catalog.php?category=<?= urlencode($cat->getId()) ?>" class="filter-pill" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;">
                        📁 <?= e($cat->getName()) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- E-Book Unggulan Terbaru -->
        <section style="margin-top: 3.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">📖 Rekomendasi E-Book Pilihan</h2>
                    <p style="color: var(--secondary); font-size: 0.9rem;">Koleksi bacaan terpopuler untuk mahasiswa dan praktisi</p>
                </div>
                <a href="catalog.php" class="btn btn-outline btn-sm">Buka Seluruh Katalog &rarr;</a>
            </div>

            <div class="book-grid">
                <?php foreach ($featuredBooks as $book): ?>
                    <?php 
                        $catName = $categoryMap[$book->getCategoryId()] ?? 'Umum';
                        $coverImg = !empty($book->getCover()) ? $book->getCover() : 'assets/images/default-book.png';
                    ?>
                    <article class="book-card">
                        <div class="book-card-cover-wrapper">
                            <img 
                                src="<?= e($coverImg) ?>" 
                                alt="Sampul <?= e($book->getTitle()) ?>" 
                                class="book-card-cover"
                                onerror="this.src='assets/images/default-book.png';"
                                loading="lazy"
                            >
                        </div>
                        <div class="book-card-body">
                            <div class="book-card-category">
                                📁 <?= e($catName) ?>
                            </div>
                            <h3 class="book-card-title">
                                <a href="detail.php?id=<?= urlencode($book->getId()) ?>">
                                    <?= e($book->getTitle()) ?>
                                </a>
                            </h3>
                            <p class="book-card-author">
                                ✍️ <?= e($book->getAuthor()) ?> &bull; 🗓️ <?= e((string)$book->getYear()) ?>
                            </p>
                            <p class="book-card-desc">
                                <?= e($book->getDescription()) ?>
                            </p>
                            <div class="book-card-footer">
                                <?php if ($book->isAvailable()): ?>
                                    <span class="badge badge-available">● Tersedia</span>
                                <?php else: ?>
                                    <span class="badge badge-borrowed">● Dipinjam</span>
                                <?php endif; ?>
                                <a href="detail.php?id=<?= urlencode($book->getId()) ?>" class="btn btn-outline btn-sm">
                                    Detail &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

</body>
</html>

