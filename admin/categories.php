<?php

declare(strict_types=1);

/**
 * Manajemen Kategori E-Book - Area Administrator
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Pengelolaan Kategori (Tanggung Jawab: Mail)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\SessionManager;
use App\Models\Category;
use App\Services\BookService;

// Proteksi Akses: Hanya Administrator!
SessionManager::requireRole('admin', '../index.php');

$bookService = new BookService();
$categories = $bookService->getAllCategories();

$currentUser = SessionManager::user();
$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');

// Mode edit inline jika ada param edit_id
$editId = isset($_GET['edit']) ? trim((string)$_GET['edit']) : '';
$editCategory = $editId !== '' ? $bookService->getCategoryById($editId) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori E-Book - Admin E-Library</title>
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
                <li><a href="books.php" class="nav-link">Kelola Buku</a></li>
                <li><a href="categories.php" class="nav-link active">Kategori</a></li>
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
                    Kelola Kategori E-Book
                </h1>
                <p style="color: var(--secondary); font-size: 0.95rem;">
                    Atur taksonomi dan klasifikasi koleksi buku digital agar pencarian lebih terstruktur.
                </p>
            </div>
            <div>
                <a href="books.php" class="btn btn-outline" style="padding: 0.8rem 1.4rem;">
                    &larr; Kembali ke Kelola Buku
                </a>
            </div>
        </section>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.75rem; align-items: flex-start;">
            <!-- Kolom Kiri: Form Tambah / Edit Kategori -->
            <section class="card" style="padding: 1.75rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main);">
                    <?= $editCategory ? '✏️ Ubah Nama Kategori' : '➕ Tambah Kategori Baru' ?>
                </h2>

                <form action="../actions/category-save.php" method="POST">
                    <input type="hidden" name="action" value="<?= $editCategory ? 'update' : 'create' ?>">
                    <?php if ($editCategory): ?>
                        <input type="hidden" name="id" value="<?= e($editCategory->getId()) ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="category_name" class="form-label">Nama Kategori <span style="color: var(--danger);">*</span></label>
                        <input 
                            type="text" 
                            id="category_name" 
                            name="name" 
                            class="form-input" 
                            placeholder="Contoh: Pemrograman Mobile" 
                            required 
                            minlength="2"
                            maxlength="100"
                            value="<?= e($editCategory ? $editCategory->getName() : '') ?>"
                        >
                        <span class="form-helper">Minimal 2 karakter, nama kategori harus unik.</span>
                    </div>

                    <div style="display: flex; gap: 0.5rem; margin-top: 1.25rem;">
                        <button type="submit" class="btn btn-primary">
                            💾 <?= $editCategory ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
                        </button>
                        <?php if ($editCategory): ?>
                            <a href="categories.php" class="btn btn-outline" style="width: auto;">
                                Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <!-- Kolom Kanan: Tabel Daftar Kategori -->
            <section class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">ID</th>
                            <th>Nama Kategori</th>
                            <th style="text-align: center; width: 140px;">Jumlah E-Book</th>
                            <th style="text-align: right; width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                    Belum ada kategori yang terdaftar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php $count = $bookService->getBookCountByCategory($cat->getId()); ?>
                                <tr>
                                    <td>
                                        <code><?= e($cat->getId()) ?></code>
                                    </td>
                                    <td>
                                        <strong style="color: var(--text-main); font-size: 0.95rem;">
                                            <?= e($cat->getName()) ?>
                                        </strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-year">
                                            <?= $count ?> buku
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions" style="justify-content: flex-end;">
                                            <a href="categories.php?edit=<?= urlencode($cat->getId()) ?>" class="btn btn-outline btn-sm" title="Edit Kategori">
                                                ✏️ Edit
                                            </a>
                                            <?php if ($count === 0): ?>
                                                <form action="../actions/category-delete.php" method="POST" class="form-confirm-delete" data-item-name="<?= e($cat->getName()) ?>" style="display: inline;">
                                                    <input type="hidden" name="category_id" value="<?= e($cat->getId()) ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Kategori">
                                                        🗑️ Hapus
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.4; cursor: not-allowed;" title="Kategori ini sedang digunakan oleh <?= $count ?> e-book">
                                                    🔒 Digunakan
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
        </div>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="../assets/js/app.js"></script>
</body>
</html>
