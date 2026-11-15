<?php

declare(strict_types=1);

/**
 * Daftar Anggota (Member) - Area Administrator
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Modul Integrasi & Manajemen Pengguna (Tanggung Jawab: Arkaan)
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\JsonStorage;
use App\Core\SessionManager;
use App\Models\Member;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BookService;
use App\Services\LoanService;

// Gatekeeper Proteksi Akses: Hanya Administrator yang diizinkan!
SessionManager::requireRole('admin', '../index.php');

$currentUserSession = SessionManager::user();
$authService = new AuthService();
$bookService = new BookService();
$loanService = new LoanService(bookService: $bookService, authService: $authService);

// Ambil seluruh user objek polimorfik (App\Models\User)
$allUsers = $authService->getAllUsers();

// Ambil metadata tanggal terdaftar dari users.json secara konsisten
$userStorage = new JsonStorage(STORAGE_PATH . DIRECTORY_SEPARATOR . 'users.json');
$userRawList = $userStorage->all();
$userCreatedMap = [];
foreach ($userRawList as $raw) {
    if (isset($raw['id'])) {
        $userCreatedMap[$raw['id']] = $raw['created_at'] ?? '-';
    }
}

// Filter pencarian jika ada parameter GET ?q=...
$search = trim((string)($_GET['q'] ?? ''));
$filteredUsers = $allUsers;
if ($search !== '') {
    $needle = mb_strtolower($search);
    $filteredUsers = array_values(array_filter($allUsers, static function (User $u) use ($needle): bool {
        return mb_stripos($u->getName(), $needle) !== false
            || mb_stripos($u->getEmail(), $needle) !== false
            || mb_stripos($u->getId(), $needle) !== false;
    }));
}

$flashSuccess = SessionManager::getFlash('success');
$flashError = SessionManager::getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Member - Admin E-Library</title>
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
                <li><a href="categories.php" class="nav-link">Kategori</a></li>
                <li><a href="members.php" class="nav-link active" aria-current="page">Daftar Member</a></li>
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

        <!-- Header Panel -->
        <section class="admin-header">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-main);">
                    Daftar Pengguna & Member
                </h1>
                <p style="color: var(--secondary); font-size: 0.95rem;">
                    Kelola dan pantau seluruh akun terdaftar, batas peminjaman, serta status aktivitas anggota perpustakaan.
                </p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline" style="padding: 0.8rem 1.4rem;">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </section>

        <!-- Bar Pencarian & Filter -->
        <section class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <form method="GET" action="members.php" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="q" class="form-input" placeholder="Cari nama, email, atau ID member..." value="<?= e($search) ?>" style="margin-bottom: 0;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
                    Cari Pengguna
                </button>
                <?php if ($search !== ''): ?>
                    <a href="members.php" class="btn btn-outline" style="padding: 0.75rem 1.2rem;">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </section>

        <!-- Tabel Pengguna & Member -->
        <section class="card">
            <div class="card-header" style="text-align: left; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h2 class="card-title" style="font-size: 1.25rem;">Semua Akun Terdaftar (<?= count($filteredUsers) ?>)</h2>
                    <p class="card-subtitle">Menampilkan akun dengan role Admin dan Member.</p>
                </div>
            </div>

            <?php if (empty($filteredUsers)): ?>
                <div class="empty-state" style="padding: 3rem 1.5rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                    <h3 style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 0.5rem;">Tidak Ada Pengguna Ditemukan</h3>
                    <p style="color: var(--secondary); max-width: 480px; margin: 0 auto 1.5rem auto;">
                        Tidak ditemukan pengguna yang cocok dengan kata kunci pencarian Anda.
                    </p>
                    <a href="members.php" class="btn btn-outline">Lihat Semua Pengguna</a>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="margin-top: 1rem; overflow-x: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                <th style="padding: 0.75rem;">ID User</th>
                                <th style="padding: 0.75rem;">Nama Lengkap</th>
                                <th style="padding: 0.75rem;">Email</th>
                                <th style="padding: 0.75rem;">Peran (Role)</th>
                                <th style="padding: 0.75rem;">Batas Pinjam</th>
                                <th style="padding: 0.75rem;">Pinjaman Aktif</th>
                                <th style="padding: 0.75rem;">Terdaftar Sejak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredUsers as $user): ?>
                                <?php
                                $isMember = $user instanceof Member;
                                $activeLoans = $isMember ? $loanService->getMemberLoans($user->getId()) : [];
                                $activeCount = count($activeLoans);
                                $createdAt = $userCreatedMap[$user->getId()] ?? '-';
                                ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.75rem; font-family: monospace; font-size: 0.85rem;">
                                        <?= e($user->getId()) ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <strong><?= e($user->getName()) ?></strong>
                                    </td>
                                    <td style="padding: 0.75rem; color: var(--text-secondary);">
                                        <?= e($user->getEmail()) ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php if ($user->getRole() === 'admin'): ?>
                                            <span class="badge" style="background-color: #fee2e2; color: #991b1b;">ADMIN</span>
                                        <?php else: ?>
                                            <span class="badge badge-primary">MEMBER</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?= $isMember ? e((string)$user->getLoanLimit()) . ' Buku' : '<span style="color: var(--text-muted);">-</span>' ?>
                                    </td>
                                    <td style="padding: 0.75rem;">
                                        <?php if ($isMember): ?>
                                            <span style="font-weight: 700; color: <?= $activeCount > 0 ? 'var(--warning)' : 'var(--text-muted)' ?>;">
                                                <?= $activeCount ?> Buku
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem; font-size: 0.9rem; color: var(--text-muted);">
                                        <?= $createdAt !== '-' ? e(date('d M Y, H:i', strtotime($createdAt))) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 E-Library System. UTS Pemrograman Web — Informatika UMM.</p>
    </footer>

    <script src="../assets/js/app.js"></script>
</body>
</html>
