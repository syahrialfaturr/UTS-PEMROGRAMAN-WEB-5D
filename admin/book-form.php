<?php

declare(strict_types=1);

/**
 * Form Tambah / Edit E-Book - Area Administrator
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul CRUD E-Book Admin (Tanggung Jawab: Mail)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Book;
use App\Services\BookService;

// Proteksi Akses: Hanya Administrator!
SessionManager::requireRole('admin', '../index.php');

$bookService = new BookService();
$categories = $bookService->getAllCategories();

$bookId = isset($_GET['id']) ? trim((string)$_GET['id']) : '';
$isEdit = false;
$book = null;

if ($bookId !== '') {
    $book = $bookService->getById($bookId);
    if (!$book) {
        SessionManager::setFlash('error', "E-book dengan kode '{$bookId}' tidak ditemukan.");
        redirect('books.php');
    }
    $isEdit = true;
}

$currentUser = SessionManager::user();
$flashError = SessionManager::getFlash('error');

// Nilai form awal (mengutamakan old input jika validasi sebelumnya gagal)
$valTitle = SessionManager::getOldInput('title', $book ? $book->getTitle() : '');
$valAuthor = SessionManager::getOldInput('author', $book ? $book->getAuthor() : '');
$valCategory = SessionManager::getOldInput('category_id', $book ? $book->getCategoryId() : '');
$valYear = SessionManager::getOldInput('year', $book ? (string)$book->getYear() : (string)date('Y'));
$valDesc = SessionManager::getOldInput('description', $book ? $book->getDescription() : '');
$valCover = SessionManager::getOldInput('cover', $book ? $book->getCover() : 'assets/images/default-book.png');
$valAvailable = $book ? $book->isAvailable() : true;

SessionManager::clearOldInput();

$coverPreviewSrc = !empty($valCover) ? '../' . ltrim($valCover, '/') : '../assets/images/default-book.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit E-Book' : 'Tambah E-Book Baru' ?> - Admin E-Library</title>
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

    <main class="main-container">

        <!-- Flash Alert -->
        <?php if ($flashError): ?>
            <div class="alert alert-danger" role="alert">
                <span>⚠️ <?= e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Navigasi Breadcrumbs -->
        <nav style="margin-bottom: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
            <a href="dashboard.php" class="nav-link" style="display: inline;">Dashboard</a> &rsaquo;
            <a href="books.php" class="nav-link" style="display: inline;">Kelola Buku</a> &rsaquo;
            <span style="color: var(--text-main); font-weight: 600;"><?= $isEdit ? 'Edit E-Book' : 'Tambah Buku Baru' ?></span>
        </nav>

        <section class="card" style="max-width: 820px; margin: 0 auto;">
            <div class="card-header" style="text-align: left; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">
                <h1 class="card-title">
                    <?= $isEdit ? '✏️ Edit Metadata E-Book' : '➕ Tambah E-Book Baru' ?>
                </h1>
                <p class="card-subtitle">
                    <?= $isEdit 
                        ? "Memperbarui data e-book dengan ID: " . e($book->getId()) 
                        : "Lengkapi formulir di bawah ini untuk menambahkan e-book baru ke dalam katalog." ?>
                </p>
            </div>

            <form action="../actions/book-save.php" method="POST" id="bookForm" style="margin-top: 1.5rem;">
                <!-- Hidden inputs -->
                <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                <?php if ($isEdit && $book): ?>
                    <input type="hidden" name="id" value="<?= e($book->getId()) ?>">
                <?php endif; ?>

                <!-- Judul Buku -->
                <div class="form-group">
                    <label for="title" class="form-label">Judul E-Book <span style="color: var(--danger);">*</span></label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-input" 
                        placeholder="Contoh: Arsitektur PHP Modern: OOP & Clean Code" 
                        required 
                        minlength="2"
                        maxlength="255"
                        value="<?= e($valTitle) ?>"
                    >
                    <span class="form-helper">Masukkan judul buku secara lengkap dan jelas.</span>
                </div>

                <!-- Penulis & Kategori (Form Row) -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="author" class="form-label">Nama Penulis / Pengarang <span style="color: var(--danger);">*</span></label>
                        <input 
                            type="text" 
                            id="author" 
                            name="author" 
                            class="form-input" 
                            placeholder="Contoh: Rasmus Lerdorf" 
                            required 
                            minlength="2"
                            maxlength="255"
                            value="<?= e($valAuthor) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label">Kategori E-Book <span style="color: var(--danger);">*</span></label>
                        <select id="category_id" name="category_id" class="form-input" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat->getId()) ?>" <?= $valCategory === $cat->getId() ? 'selected' : '' ?>>
                                    <?= e($cat->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Tahun & Status Ketersediaan (Form Row) -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="year" class="form-label">Tahun Terbit <span style="color: var(--danger);">*</span></label>
                        <input 
                            type="number" 
                            id="year" 
                            name="year" 
                            class="form-input" 
                            placeholder="2025" 
                            required 
                            min="1500" 
                            max="<?= (int)date('Y') + 2 ?>"
                            value="<?= e($valYear) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Ketersediaan</label>
                        <div style="margin-top: 0.65rem;">
                            <label class="form-checkbox-label">
                                <input 
                                    type="checkbox" 
                                    name="available" 
                                    value="1" 
                                    class="form-checkbox"
                                    <?= $valAvailable ? 'checked' : '' ?>
                                >
                                <span>Tersedia untuk Dipinjam oleh Member</span>
                            </label>
                        </div>
                        <span class="form-helper">Hilangkan centang jika buku sedang dalam perbaikan atau tidak dapat dipinjam.</span>
                    </div>
                </div>

                <!-- URL Cover & Preview -->
                <div class="form-row" style="align-items: center;">
                    <div class="form-group" style="flex: 1;">
                        <label for="cover" class="form-label">Path / URL Sampul E-Book</label>
                        <input 
                            type="text" 
                            id="cover" 
                            name="cover" 
                            class="form-input" 
                            placeholder="assets/images/default-book.png atau URL gambar eksternal" 
                            value="<?= e($valCover) ?>"
                        >
                        <span class="form-helper">Biarkan default atau masukkan path gambar sampul.</span>
                    </div>
                    <div style="text-align: center; padding-top: 0.5rem;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Preview Sampul:</span>
                        <img 
                            id="coverPreview" 
                            src="<?= e($coverPreviewSrc) ?>" 
                            alt="Preview" 
                            style="width: 60px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);"
                        >
                    </div>
                </div>

                <!-- Deskripsi / Sinopsis -->
                <div class="form-group">
                    <label for="description" class="form-label">Sinopsis & Deskripsi Lengkap <span style="color: var(--danger);">*</span></label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-textarea" 
                        placeholder="Tuliskan ulasan ringkas mengenai isi dan materi utama e-book..." 
                        required 
                        minlength="5"
                    ><?= e($valDesc) ?></textarea>
                </div>

                <!-- Tombol Submit & Batal -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.25rem;">
                    <a href="books.php" class="btn btn-outline" style="width: auto; padding: 0.75rem 1.5rem;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="saveBookBtn" style="width: auto; padding: 0.75rem 2rem;">
                        💾 Simpan Data E-Book
                    </button>
                </div>
            </form>
        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="../assets/js/app.js"></script>
</body>
</html>
