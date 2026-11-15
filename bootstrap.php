<?php

declare(strict_types=1);

/**
 * Bootstrap Aplikasi E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Mengatur konstanta jalur, PSR-4-like autoloader untuk namespace App\,
 * dan helper global yang aman.
 */

define('ROOT_PATH', __DIR__);
define('STORAGE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'storage');
define('CONFIG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'config');

// Registrasi Autoloader sederhana tanpa Composer
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load App Config
$appConfig = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'app.php';

// Inisialisasi SessionManager secara aman
\App\Core\SessionManager::start();

/**
 * Helper untuk mencegah serangan XSS dengan htmlspecialchars.
 *
 * @param string|null $value
 * @return string
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper untuk mendapatkan base URL atau path aplikasi.
 *
 * @param string $path
 * @return string
 */
function app_url(string $path = ''): string
{
    global $appConfig;
    $base = rtrim($appConfig['base_url'] ?? '', '/');
    $path = ltrim($path, '/');
    return $base . ($path !== '' ? '/' . $path : '');
}

/**
 * Helper untuk melakukan redirect HTTP yang aman.
 *
 * @param string $url
 * @return never
 */
function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

/**
 * Helper untuk memastikan storage folder dan file seed data awal tersedia.
 */
function ensure_initial_seed(): void
{
    $dir = STORAGE_PATH;
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $usersFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'users.json';
    if (!file_exists($usersFile) || filesize($usersFile) === 0) {
        $initialUsers = [
            [
                'id' => 'USR-001',
                'name' => 'Administrator E-Library',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'USR-002',
                'name' => 'Syahrial Member',
                'email' => 'member@example.com',
                'password' => password_hash('member123', PASSWORD_DEFAULT),
                'role' => 'member',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        file_put_contents($usersFile, json_encode($initialUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $categoriesFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'categories.json';
    if (!file_exists($categoriesFile) || filesize($categoriesFile) === 0) {
        $initialCategories = [
            ['id' => 'CAT-001', 'name' => 'Rekayasa Perangkat Lunak & Web'],
            ['id' => 'CAT-002', 'name' => 'Kecerdasan Buatan & Data Science'],
            ['id' => 'CAT-003', 'name' => 'Jaringan Komputer & Keamanan Siber'],
            ['id' => 'CAT-004', 'name' => 'Algoritma & Struktur Data'],
            ['id' => 'CAT-005', 'name' => 'Sistem Informasi & Bisnis Digital'],
        ];
        file_put_contents($categoriesFile, json_encode($initialCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $booksFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'books.json';
    if (!file_exists($booksFile) || filesize($booksFile) === 0) {
        $initialBooks = [
            [
                'id' => 'BOOK-001',
                'title' => 'Arsitektur PHP Modern: OOP, Design Patterns & Clean Code',
                'author' => 'Rasmus Lerdorf & Martin Fowler',
                'category_id' => 'CAT-001',
                'year' => 2024,
                'description' => 'Panduan mendalam arsitektur pemrograman berorientasi objek modern PHP 8.x, prinsip SOLID, pemisahan tanggung jawab, dan desain sistem modular tanpa ketergantungan framework.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-002',
                'title' => 'Dasar Pemrograman Web Interaktif: HTML5, CSS3 & JavaScript Vanilla',
                'author' => 'Tim Laboratorium Informatika UMM',
                'category_id' => 'CAT-001',
                'year' => 2025,
                'description' => 'Buku referensi lengkap membangun antarmuka web modern dengan elemen semantik HTML5, CSS responsif fleksibel, serta manipulasi DOM dan validasi form interaktif vanilla JavaScript.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-003',
                'title' => 'Machine Learning Praktis: Konsep dan Penerapan Nyata',
                'author' => 'Dr. Andrew Ng & Tim AI',
                'category_id' => 'CAT-002',
                'year' => 2024,
                'description' => 'Pembahasan komprehensif algoritma Supervised Learning, Unsupervised Learning, evaluasi model, dan implementasi pipeline kecerdasan buatan untuk studi kasus industri.',
                'cover' => 'assets/images/default-book.png',
                'available' => false,
            ],
            [
                'id' => 'BOOK-004',
                'title' => 'Deep Learning dan Representasi Neural Networks',
                'author' => 'Ian Goodfellow & Yoshua Bengio',
                'category_id' => 'CAT-002',
                'year' => 2023,
                'description' => 'Teori dan matematika fundamental jaringan syaraf tiruan, backpropagation, CNN untuk pemrosesan citra, RNN/Transformer untuk NLP, dan generative models.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-005',
                'title' => 'Keamanan Jaringan Komputer dan Ethical Hacking',
                'author' => 'Kevin Mitnick & Bruce Schneier',
                'category_id' => 'CAT-003',
                'year' => 2024,
                'description' => 'Eksplorasi teknik analisis kerentanan sistem jaringan, kriptografi praktis, pertahanan terhadap serangan siber, dan audit keamanan berlapis.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-006',
                'title' => 'Struktur Data dan Analisis Kompleksitas Algoritma',
                'author' => 'Thomas H. Cormen & Ronald Rivest',
                'category_id' => 'CAT-004',
                'year' => 2023,
                'description' => 'Rujukan utama analisis algoritma, notasi Big-O, struktur data pohon (trees), graf (graphs), divide-and-conquer, dan pemrograman dinamis berkinerja tinggi.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-007',
                'title' => 'Analisis dan Perancangan Sistem Informasi Berorientasi Objek',
                'author' => 'Grady Booch & James Rumbaugh',
                'category_id' => 'CAT-005',
                'year' => 2022,
                'description' => 'Metodologi perancangan perangkat lunak enterprise dengan UML, diagram alur sistem, perancangan database relasional, dan manajemen proyek sistem informasi terpadu.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-008',
                'title' => 'UI/UX Design Thinking untuk Aplikasi Web Modern',
                'author' => 'Don Norman & Steve Krug',
                'category_id' => 'CAT-001',
                'year' => 2025,
                'description' => 'Prinsip-prinsip desain pengalaman pengguna, aksesibilitas (a11y), usability testing, perancangan antarmuka intuitif, dan kepuasan interaksi pengguna.',
                'cover' => 'assets/images/default-book.png',
                'available' => false,
            ],
        ];
        file_put_contents($booksFile, json_encode($initialBooks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $transactionsFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'transactions.json';
    if (!file_exists($transactionsFile) || filesize($transactionsFile) === 0 || trim((string)file_get_contents($transactionsFile)) === '[]') {
        $initialTransactions = [
            [
                'id' => 'TRX-20261110-A1B2C3',
                'user_id' => 'USR-002',
                'book_id' => 'BOOK-003',
                'borrowed_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'due_at' => date('Y-m-d H:i:s', strtotime('+4 days')),
                'returned_at' => null,
                'status' => 'borrowed',
            ],
            [
                'id' => 'TRX-20261112-D4E5F6',
                'user_id' => 'USR-002',
                'book_id' => 'BOOK-008',
                'borrowed_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
                'due_at' => date('Y-m-d H:i:s', strtotime('+6 days')),
                'returned_at' => null,
                'status' => 'borrowed',
            ],
        ];
        file_put_contents($transactionsFile, json_encode($initialTransactions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// Pastikan seed data users.json tersedia
ensure_initial_seed();

