<?php

declare(strict_types=1);

/**
 * Halaman Katalog Publik - E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Katalog E-book & Filter Interaktif (Tanggung Jawab: Mail)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Book;
use App\Services\BookService;

$bookService = new BookService();
$categories = $bookService->getAllCategories();
$categoryMap = $bookService->getCategoryMap();

// Ambil parameter filter dari GET
$searchQuery = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$selectedCategory = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
$selectedStatus = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

// Saring buku berdasarkan input pencarian dan filter
$books = $bookService->filterAndSearch(
    categoryId: $selectedCategory !== '' ? $selectedCategory : null,
    keyword: $searchQuery !== '' ? $searchQuery : null,
    availability: $selectedStatus !== '' ? $selectedStatus : null
);

$currentUser = SessionManager::user();
$totalFound = count($books);
$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog E-Book - E-Library Perpustakaan Digital</title>
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

    <!-- Konten Utama Katalog -->
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

        <!-- Header Judul Katalog -->
        <section class="catalog-header">
            <div>
                <h1 class="catalog-header-title">Katalog E-Book</h1>
                <p class="catalog-header-subtitle">
                    Jelajahi beragam koleksi buku digital berkualitas tinggi untuk mendukung pembelajaran Anda.
                </p>
            </div>
            <div>
                <span class="badge badge-year" style="font-size: 0.9rem; padding: 0.45rem 1rem;">
                    Ditemukan: <strong><?= $totalFound ?></strong> buku
                </span>
            </div>
        </section>

        <!-- Kartu Filter & Pencarian -->
        <section class="catalog-filter-card">
            <form action="catalog.php" method="GET" class="filter-form-grid">
                <!-- Input Pencarian -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="searchQuery" class="form-label">Cari Judul atau Penulis</label>
                    <input 
                        type="text" 
                        id="searchQuery" 
                        name="q" 
                        class="form-input" 
                        placeholder="Ketik judul buku atau nama pengarang..." 
                        value="<?= e($searchQuery) ?>"
                    >
                </div>

                <!-- Dropdown Kategori -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="categoryFilter" class="form-label">Kategori</label>
                    <select id="categoryFilter" name="category" class="form-input">
                        <option value="all">-- Semua Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat->getId()) ?>" <?= $selectedCategory === $cat->getId() ? 'selected' : '' ?>>
                                <?= e($cat->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Dropdown Ketersediaan -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="statusFilter" class="form-label">Status Ketersediaan</label>
                    <select id="statusFilter" name="status" class="form-input">
                        <option value="all" <?= $selectedStatus === 'all' || $selectedStatus === '' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="available" <?= $selectedStatus === 'available' ? 'selected' : '' ?>>Hanya yang Tersedia</option>
                        <option value="borrowed" <?= $selectedStatus === 'borrowed' ? 'selected' : '' ?>>Sedang Dipinjam</option>
                    </select>
                </div>

                <!-- Tombol Aksi Filter -->
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">
                        🔍 Filter
                    </button>
                    <?php if ($searchQuery !== '' || ($selectedCategory !== '' && $selectedCategory !== 'all') || ($selectedStatus !== '' && $selectedStatus !== 'all')): ?>
                        <a href="catalog.php" class="btn btn-outline" style="padding: 0.75rem 1rem;" title="Reset filter">
                            ↺ Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Kategori Cepat (Pills) -->
            <div class="filter-pills">
                <span style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted); align-self: center;">Kategori Pilihan:</span>
                <a href="catalog.php" class="filter-pill <?= empty($selectedCategory) || $selectedCategory === 'all' ? 'active' : '' ?>">
                    Semua
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a 
                        href="catalog.php?category=<?= urlencode($cat->getId()) ?><?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
                        class="filter-pill <?= $selectedCategory === $cat->getId() ? 'active' : '' ?>"
                    >
                        <?= e($cat->getName()) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Grid Daftar E-Book -->
        <section>
            <?php if (empty($books)): ?>
                <!-- Tampilan Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">📖</div>
                    <h2 class="empty-state-title">Tidak Ada E-Book yang Ditemukan</h2>
                    <p class="empty-state-text">
                        Maaf, tidak ada e-book yang sesuai dengan kriteria pencarian "<strong><?= e($searchQuery) ?></strong>"
                        atau filter kategori yang Anda pilih. Silakan gunakan kata kunci lain atau setel ulang filter.
                    </p>
                    <a href="catalog.php" class="btn btn-primary" style="width: auto; padding: 0.65rem 1.5rem;">
                        Kembali ke Semua E-Book
                    </a>
                </div>
            <?php else: ?>
                <div class="book-grid">
                    <?php foreach ($books as $book): ?>
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
                                <h2 class="book-card-title">
                                    <a href="detail.php?id=<?= urlencode($book->getId()) ?>">
                                        <?= e($book->getTitle()) ?>
                                    </a>
                                </h2>
                                <p class="book-card-author">
                                    ✍️ <?= e($book->getAuthor()) ?> &bull; 🗓️ <?= e((string)$book->getYear()) ?>
                                </p>
                                <p class="book-card-desc">
                                    <?= e($book->getDescription()) ?>
                                </p>
                                <div class="book-card-footer">
                                    <?php if ($book->isAvailable()): ?>
                                        <span class="badge badge-available">
                                            ● Tersedia
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-borrowed">
                                            ● Dipinjam
                                        </span>
                                    <?php endif; ?>

                                    <a href="detail.php?id=<?= urlencode($book->getId()) ?>" class="btn btn-outline btn-sm">
                                        Lihat Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
