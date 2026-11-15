<?php

declare(strict_types=1);

/**
 * Manajemen Katalog E-Book - Area Administrator
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul CRUD E-Book Admin (Tanggung Jawab: Mail)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Book;
use App\Services\BookService;

// Proteksi Akses: Hanya Administrator yang diizinkan!
SessionManager::requireRole('admin', '../index.php');

$bookService = new BookService();
$categories = $bookService->getAllCategories();
$categoryMap = $bookService->getCategoryMap();

// Filter & Pencarian
$keyword = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$categoryId = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

$books = $bookService->filterAndSearch(
    categoryId: $categoryId !== '' ? $categoryId : null,
    keyword: $keyword !== '' ? $keyword : null,
    availability: $status !== '' ? $status : null
);

$currentUser = SessionManager::user();
$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Katalog E-Book - Admin E-Library</title>
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
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="books.php" class="nav-link active">Kelola Buku</a></li>
                <li><a href="categories.php" class="nav-link">Kategori</a></li>
                <li><a href="members.php" class="nav-link">Daftar Member</a></li>
                <li><a href="transactions.php" class="nav-link">Transaksi</a></li>
                <li>
                    <span class="user-badge" style="background-color: #fee2e2; color: #991b1b;">
                        🛡️ <?= e($currentUser['name']) ?> (ADMIN)
                    </span>
                </li>
                <li>
                    <a href="../logout.php" class="btn btn-outline btn-sm">Keluar</a>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Konten Utama Manajemen Buku -->
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

        <!-- Header Panel -->
        <section class="admin-header">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main);">
                    Manajemen Katalog E-Book
                </h1>
                <p style="color: var(--secondary); font-size: 0.95rem;">
                    Kelola koleksi e-book perpustakaan: tambah, ubah metadata, pantau ketersediaan, dan hapus dengan aman.
                </p>
            </div>
            <div>
                <a href="book-form.php" class="btn btn-primary" style="padding: 0.8rem 1.4rem;">
                    ➕ Tambah E-Book Baru
                </a>
            </div>
        </section>

        <!-- Filter & Search Bar Admin -->
        <section class="catalog-filter-card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
            <form action="books.php" method="GET" class="filter-form-grid">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="adminSearch" class="form-label">Cari Judul / Penulis</label>
                    <input 
                        type="text" 
                        id="adminSearch" 
                        name="q" 
                        class="form-input" 
                        placeholder="Ketik kata kunci..." 
                        value="<?= e($keyword) ?>"
                    >
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="adminCategory" class="form-label">Kategori</label>
                    <select id="adminCategory" name="category" class="form-input">
                        <option value="all">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat->getId()) ?>" <?= $categoryId === $cat->getId() ? 'selected' : '' ?>>
                                <?= e($cat->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="adminStatus" class="form-label">Status Ketersediaan</label>
                    <select id="adminStatus" name="status" class="form-input">
                        <option value="all" <?= $status === 'all' || $status === '' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Sedang Dipinjam</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.25rem;">
                        🔍 Filter
                    </button>
                    <?php if ($keyword !== '' || ($categoryId !== '' && $categoryId !== 'all') || ($status !== '' && $status !== 'all')): ?>
                        <a href="books.php" class="btn btn-outline" style="padding: 0.75rem 1rem;">
                            ↺ Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Tabel Data E-Book -->
        <section class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Cover</th>
                        <th style="width: 100px;">ID</th>
                        <th>Judul E-Book</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th style="text-align: center;">Tahun</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                📭 Tidak ada data e-book yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $b): ?>
                            <?php 
                                $catName = $categoryMap[$b->getCategoryId()] ?? 'Umum';
                                $coverSrc = !empty($b->getCover()) ? '../' . ltrim($b->getCover(), '/') : '../assets/images/default-book.png';
                            ?>
                            <tr>
                                <td>
                                    <img 
                                        src="<?= e($coverSrc) ?>" 
                                        alt="Cover" 
                                        class="table-thumb"
                                        onerror="this.src='../assets/images/default-book.png';"
                                    >
                                </td>
                                <td>
                                    <code><?= e($b->getId()) ?></code>
                                </td>
                                <td>
                                    <strong style="color: var(--text-main); font-size: 0.95rem;">
                                        <?= e($b->getTitle()) ?>
                                    </strong>
                                </td>
                                <td style="color: var(--secondary);">
                                    <?= e($b->getAuthor()) ?>
                                </td>
                                <td>
                                    <span class="badge badge-category">
                                        <?= e($catName) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <?= e((string)$b->getYear()) ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($b->isAvailable()): ?>
                                        <span class="badge badge-available">
                                            ● Tersedia
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-borrowed" title="Sedang dipinjam oleh Member">
                                            ● Dipinjam
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a href="../detail.php?id=<?= urlencode($b->getId()) ?>" class="btn btn-outline btn-sm" title="Lihat Tampilan Publik" target="_blank">
                                            👁️
                                        </a>
                                        <a href="book-form.php?id=<?= urlencode($b->getId()) ?>" class="btn btn-outline btn-sm" title="Edit Data E-Book">
                                            ✏️ Edit
                                        </a>
                                        <?php if ($b->isAvailable()): ?>
                                            <form action="../actions/book-delete.php" method="POST" class="form-confirm-delete" data-item-name="<?= e($b->getTitle()) ?>" style="display: inline;">
                                                <input type="hidden" name="book_id" value="<?= e($b->getId()) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus E-Book">
                                                    🗑️ Hapus
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.4; cursor: not-allowed;" title="Buku sedang dipinjam, tidak dapat dihapus!">
                                                🔒 Terkunci
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="../assets/js/app.js"></script>
</body>
</html>
