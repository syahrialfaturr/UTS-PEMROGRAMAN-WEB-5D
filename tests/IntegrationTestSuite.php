<?php

declare(strict_types=1);

/**
 * Suite Pengujian Integrasi End-to-End (E2E) & QA Sistem E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Penanggung Jawab Integrasi & QA: Arkaan
 *
 * Menguji integrasi seluruh modul:
 * 1. Autentikasi & Session (Syahrial)
 * 2. Katalog & CRUD E-Book/Kategori (Mail)
 * 3. Transaksi Peminjaman & Pengembalian (Farid)
 * 4. Front-End Contracts, Dashboard & QA Integrasi (Robi & Arkaan)
 * 5. Lima Konsep Utama PHP OOP (Class/Object, Encapsulation, Inheritance, Polymorphism, Namespace)
 *
 * Jalankan: php tests/IntegrationTestSuite.php
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\JsonStorage;
use App\Core\SessionManager;
use App\Interfaces\BorrowableInterface;
use App\Models\Admin;
use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BookService;
use App\Services\LoanService;

final class IntegrationTestSuite
{
    private string $tempDir;
    private JsonStorage $userStorage;
    private JsonStorage $bookStorage;
    private JsonStorage $categoryStorage;
    private JsonStorage $transactionStorage;

    private AuthService $authService;
    private BookService $bookService;
    private LoanService $loanService;

    private int $passCount = 0;
    private int $failCount = 0;

    public function __construct()
    {
        $this->tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'temp_integration_storage';
        $this->setupStorage();
    }

    private function setupStorage(): void
    {
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
        array_map('unlink', glob($this->tempDir . '/*') ?: []);

        $p = fn(string $file): string => $this->tempDir . DIRECTORY_SEPARATOR . $file;

        $this->userStorage = new JsonStorage($p('users.json'));
        $this->categoryStorage = new JsonStorage($p('categories.json'));
        $this->bookStorage = new JsonStorage($p('books.json'));
        $this->transactionStorage = new JsonStorage($p('transactions.json'));

        // Seed users
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $memberHash = password_hash('member123', PASSWORD_DEFAULT);
        $this->userStorage->saveAll([
            [
                'id' => 'USR-001',
                'name' => 'Administrator E-Library',
                'email' => 'admin@example.com',
                'password' => $adminHash,
                'role' => 'admin',
                'created_at' => '2026-11-01 10:00:00',
            ],
            [
                'id' => 'USR-002',
                'name' => 'Syahrial Member',
                'email' => 'member1@example.com',
                'password' => $memberHash,
                'role' => 'member',
                'created_at' => '2026-11-02 11:00:00',
            ],
            [
                'id' => 'USR-003',
                'name' => 'Farid Member',
                'email' => 'member2@example.com',
                'password' => $memberHash,
                'role' => 'member',
                'created_at' => '2026-11-03 12:00:00',
            ],
        ]);

        // Seed categories
        $this->categoryStorage->saveAll([
            ['id' => 'CAT-001', 'name' => 'Rekayasa Perangkat Lunak'],
            ['id' => 'CAT-002', 'name' => 'Kecerdasan Buatan'],
            ['id' => 'CAT-003', 'name' => 'Keamanan Siber'],
        ]);

        // Seed books
        $this->bookStorage->saveAll([
            [
                'id' => 'BOOK-001',
                'title' => 'Arsitektur PHP Modern OOP',
                'author' => 'Rasmus Lerdorf',
                'category_id' => 'CAT-001',
                'year' => 2024,
                'description' => 'Panduan OOP PHP 8.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-002',
                'title' => 'Pemrograman Web Interaktif',
                'author' => 'Tim UMM',
                'category_id' => 'CAT-001',
                'year' => 2025,
                'description' => 'Panduan HTML, CSS, JS.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-003',
                'title' => 'Machine Learning Fundamental',
                'author' => 'Andrew Ng',
                'category_id' => 'CAT-002',
                'year' => 2023,
                'description' => 'Konsep dasar ML.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
        ]);

        $this->transactionStorage->saveAll([]);

        $this->authService = new AuthService($this->userStorage);
        $this->bookService = new BookService($this->bookStorage, $this->categoryStorage, $this->transactionStorage);
        $this->loanService = new LoanService($this->transactionStorage, $this->bookService, $this->authService, 7);
    }

    private function assert(string $title, bool $condition, string $details = ''): void
    {
        if ($condition) {
            $this->passCount++;
            echo "  [PASS] {$title}\n";
        } else {
            $this->failCount++;
            echo "  [FAIL] {$title}" . ($details !== '' ? " -> {$details}" : '') . "\n";
        }
    }

    public function runAll(): int
    {
        echo "=================================================================\n";
        echo "🚀 MENJALANKAN SUITE PENGUJIAN INTEGRASI LENGKAP (E2E & QA)\n";
        echo "   Lead Developer & Software Integrator: Arkaan\n";
        echo "=================================================================\n\n";

        $this->testCoreAutoloadAndConstants();
        $this->testOOPArchitectureAndPolymorphism();
        $this->testAuthAndSessionLifecycle();
        $this->testCatalogFilterAndSearch();
        $this->testCategoryManagementRules();
        $this->testBookCrudAndIntegrityProtection();
        $this->testEndToEndLoanAndReturnFlow();
        $this->testSecurityAndTransactionOwnership();
        $this->testDashboardAggregationIntegrity();
        $this->testJsonContractAndAtomicity();

        $this->cleanup();

        echo "\n=================================================================\n";
        echo "HASIL AKHIR: {$this->passCount} LULUS, {$this->failCount} GAGAL\n";
        echo "=================================================================\n";

        return $this->failCount === 0 ? 0 : 1;
    }

    private function testCoreAutoloadAndConstants(): void
    {
        echo "--- TAHAP 1: AUTOLOAD, KONSTANTA & HELPER GLOBAL ---\n";

        $this->assert(
            '1.1 Autoloader berhasil me-resolve namespace App\\Core, App\\Models, App\\Services, App\\Interfaces',
            class_exists(JsonStorage::class) &&
            class_exists(User::class) &&
            class_exists(AuthService::class) &&
            interface_exists(BorrowableInterface::class)
        );

        $this->assert(
            '1.2 Helper e() meng-escape karakter berbahaya XSS secara tepat',
            e('<script>alert("xss")</script>') === '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;'
        );

        $this->assert(
            '1.3 Konstanta STORAGE_PATH dan CONFIG_PATH terdefinisi',
            defined('STORAGE_PATH') && defined('CONFIG_PATH') && defined('ROOT_PATH')
        );
    }

    private function testOOPArchitectureAndPolymorphism(): void
    {
        echo "\n--- TAHAP 2: PENERAPAN LIMA KONSEP PHP OOP ---\n";

        // 1. Class & Object
        $cat = new Category('CAT-999', 'IoT & Robotika');
        $this->assert(
            '2.1 Class & Object: Instansiasi objek Category dengan atribut tervalidasi',
            $cat instanceof Category && $cat->getName() === 'IoT & Robotika'
        );

        // 2. Encapsulation
        $book = new Book('BOOK-999', 'Buku Bersih', 'Penulis A', 'CAT-999', 2026, 'Deskripsi', 'cover.png', true);
        $this->assert(
            '2.2 Encapsulation: Atribut model Book dilindungi dan diakses via getter/setter',
            $book->getId() === 'BOOK-999' && $book->isAvailable() === true
        );

        // 3. Inheritance
        $member = new Member('USR-100', 'Budi', 'budi@x.id', 'hash', 3);
        $admin = new Admin('USR-101', 'Dewi', 'dewi@x.id', 'hash');
        $this->assert(
            '2.3 Inheritance: Member dan Admin mewarisi parent class abstract User',
            $member instanceof User && $admin instanceof User
        );

        // 4. Polymorphism
        /** @var User[] $userArray */
        $userArray = [$member, $admin];
        $descMember = $userArray[0]->getRoleDescription();
        $descAdmin = $userArray[1]->getRoleDescription();
        $this->assert(
            '2.4 Polymorphism: Pemanggilan method getRoleDescription() menghasilkan output dinamis sesuai child class',
            str_contains($descMember, 'Member E-Library') && str_contains($descAdmin, 'Administrator E-Library')
        );

        // 5. Interface
        $this->assert(
            '2.5 Interface: Book mengimplementasikan BorrowableInterface secara konsisten',
            $book instanceof BorrowableInterface
        );
    }

    private function testAuthAndSessionLifecycle(): void
    {
        echo "\n--- TAHAP 3: SIKLUS AUTENTIKASI, VALIDASI & ROLE GUARD ---\n";

        // Registrasi sukses
        $regResult = $this->authService->register([
            'name' => 'Ahmad Baru',
            'email' => 'ahmad@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ]);
        $this->assert('3.1 Registrasi user baru sukses dan tersimpan', $regResult['success'] === true);

        // Registrasi email duplikat ditolak
        $dupResult = $this->authService->register([
            'name' => 'Ahmad Kloning',
            'email' => 'ahmad@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
        ]);
        $this->assert('3.2 Registrasi email terdaftar ditolak di backend', $dupResult['success'] === false);

        // Login gagal password salah
        $wrongLogin = $this->authService->login('ahmad@example.com', 'wrongpass');
        $this->assert('3.3 Login dengan password salah ditolak', $wrongLogin['success'] === false);

        // Login sukses
        $correctLogin = $this->authService->login('ahmad@example.com', 'password123');
        $this->assert('3.4 Login dengan password benar berhasil', $correctLogin['success'] === true);

        // Verifikasi password hash
        $userObj = $this->authService->findUserByEmail('ahmad@example.com');
        $this->assert(
            '3.5 Password di-hash aman dengan password_hash() dan diverifikasi dengan password_verify()',
            $userObj !== null && $userObj->verifyPassword('password123') && !str_contains($userObj->getPasswordHash(), 'password123')
        );
    }

    private function testCatalogFilterAndSearch(): void
    {
        echo "\n--- TAHAP 4: KATALOG E-BOOK, PENCARIAN & FILTER KATEGORI ---\n";

        $allBooks = $this->bookService->getAll();
        $this->assert('4.1 Mengambil seluruh koleksi buku dari storage', count($allBooks) >= 3);

        // Pencarian judul / penulis
        $searchByTitle = $this->bookService->search('php modern');
        $this->assert('4.2 Pencarian buku berdasarkan judul bekerja akurat', count($searchByTitle) === 1);

        $searchByAuthor = $this->bookService->search('andrew ng');
        $this->assert('4.3 Pencarian buku berdasarkan penulis bekerja akurat', count($searchByAuthor) === 1);

        // Filter kategori
        $filtered = $this->bookService->filterByCategory('CAT-001');
        $this->assert('4.4 Filter buku berdasarkan kategori bekerja akurat', count($filtered) === 2);
    }

    private function testCategoryManagementRules(): void
    {
        echo "\n--- TAHAP 5: MANAJEMEN KATEGORI & INTEGRITAS DATA ---\n";

        // Tambah kategori baru
        $createCat = $this->bookService->createCategory('Komputasi Awan (Cloud)');
        $this->assert('5.1 Admin dapat menambah kategori baru', $createCat['success'] === true);

        $newCatId = $createCat['category']?->getId() ?? '';
        $renameCat = $this->bookService->updateCategory($newCatId, 'Cloud Computing Enterprise');
        $this->assert('5.2 Admin dapat mengubah nama kategori', $renameCat['success'] === true);

        // Kategori yang masih dipakai buku ditolak untuk dihapus
        $deleteUsedCat = $this->bookService->deleteCategory('CAT-001');
        $this->assert('5.3 Kategori yang masih digunakan buku aktif ditolak untuk dihapus', $deleteUsedCat['success'] === false);

        // Kategori tanpa buku aman dihapus
        $deleteEmptyCat = $this->bookService->deleteCategory($newCatId);
        $this->assert('5.4 Kategori kosong berhasil dihapus dari sistem', $deleteEmptyCat['success'] === true);
    }

    private function testBookCrudAndIntegrityProtection(): void
    {
        echo "\n--- TAHAP 6: CRUD E-BOOK & PERLINDUNGAN RELASI TRANSAKSI ---\n";

        // Admin Tambah E-Book
        $newBookResult = $this->bookService->create([
            'title' => 'DevOps Modern & Docker',
            'author' => 'Solomon Hykes',
            'category_id' => 'CAT-001',
            'year' => 2024,
            'description' => 'Panduan containerization modern.',
            'cover' => 'assets/images/default-book.png',
            'available' => true,
        ]);
        $this->assert('6.1 Admin dapat menambahkan e-book baru dengan ID otomatis', $newBookResult['success'] === true);

        $createdBook = $newBookResult['book'];
        $createdId = $createdBook?->getId() ?? '';

        // Admin Edit E-Book
        $updateResult = $this->bookService->update($createdId, [
            'title' => 'DevOps Modern, Docker & Kubernetes',
            'author' => 'Solomon Hykes & Joe Beda',
            'category_id' => 'CAT-001',
            'year' => 2025,
            'description' => 'Panduan containerization dan orkestrasi lengkap.',
            'cover' => 'assets/images/default-book.png',
            'available' => true,
        ]);
        $this->assert('6.2 Admin dapat mengedit data e-book', $updateResult['success'] === true);

        $updatedBook = $this->bookService->getById($createdId);
        $this->assert(
            '6.3 Perubahan data tersimpan secara persisten',
            $updatedBook !== null && $updatedBook->getTitle() === 'DevOps Modern, Docker & Kubernetes'
        );

        // Hapus buku tanpa transaksi aktif
        $deleteResult = $this->bookService->delete($createdId);
        $this->assert('6.4 Admin dapat menghapus e-book yang aman dihapus', $deleteResult['success'] === true);
    }

    private function testEndToEndLoanAndReturnFlow(): void
    {
        echo "\n--- TAHAP 7: TRANSAKSI PEMINJAMAN & PENGEMBALIAN E-BOOK ---\n";

        // Member meminjam buku tersedia
        $loanRes = $this->loanService->borrow('USR-002', 'BOOK-001');
        $this->assert('7.1 Member dapat meminjam e-book yang tersedia', $loanRes['success'] === true);

        /** @var Transaction $tx */
        $tx = $loanRes['transaction'];
        $txId = $tx->getId();

        // Status buku menjadi tidak tersedia
        $book = $this->bookService->getById('BOOK-001');
        $this->assert('7.2 Status ketersediaan buku otomatis berubah menjadi tidak tersedia', $book?->isAvailable() === false);

        // Member lain mencoba meminjam buku yang sama ditolak
        $secondLoan = $this->loanService->borrow('USR-003', 'BOOK-001');
        $this->assert('7.3 Member lain ditolak saat meminjam buku yang sedang dipinjam', $secondLoan['success'] === false);

        // Proteksi: Buku yang sedang dipinjam tidak boleh dihapus oleh Admin
        $delBorrowed = $this->bookService->delete('BOOK-001');
        $this->assert('7.4 E-book yang sedang dipinjam ditolak untuk dihapus oleh Admin', $delBorrowed['success'] === false);

        // Member mengembalikan buku miliknya
        $returnRes = $this->loanService->returnBook('USR-002', $txId);
        $this->assert('7.5 Member berhasil mengembalikan pinjaman miliknya', $returnRes['success'] === true);

        // Status buku kembali tersedia
        $bookAfterReturn = $this->bookService->getById('BOOK-001');
        $this->assert('7.6 Status buku otomatis kembali tersedia (available = true)', $bookAfterReturn?->isAvailable() === true);

        // Buku yang sudah dikembalikan dapat dipinjam kembali
        $loanAgain = $this->loanService->borrow('USR-003', 'BOOK-001');
        $this->assert('7.7 Buku yang telah dikembalikan dapat dipinjam oleh member lain', $loanAgain['success'] === true);
    }

    private function testSecurityAndTransactionOwnership(): void
    {
        echo "\n--- TAHAP 8: KEAMANAN TRANSAKSI, KUOTA & KEPEMILIKAN ---\n";

        // Member B meminjam BOOK-002
        $loanB = $this->loanService->borrow('USR-003', 'BOOK-002');
        $txBId = $loanB['transaction']?->getId() ?? '';

        // Member A mencoba mengembalikan transaksi milik Member B
        $hijackReturn = $this->loanService->returnBook('USR-002', $txBId);
        $this->assert(
            '8.1 Member A ditolak keras saat mencoba mengembalikan pinjaman milik Member B',
            $hijackReturn['success'] === false
        );

        // Pengembalian ganda ditolak
        $validReturn = $this->loanService->returnBook('USR-003', $txBId);
        $this->assert('8.2 Pengembalian sah oleh pemilik transaksi berhasil', $validReturn['success'] === true);

        $doubleReturn = $this->loanService->returnBook('USR-003', $txBId);
        $this->assert('8.3 Transaksi berstatus returned ditolak jika dikembalikan ulang', $doubleReturn['success'] === false);

        // Guest atau Admin tidak boleh meminjam buku
        $guestLoan = $this->loanService->borrow('', 'BOOK-003');
        $this->assert('8.4 Permintaan pinjam tanpa login (Guest) ditolak', $guestLoan['success'] === false);

        $adminLoan = $this->loanService->borrow('USR-001', 'BOOK-003');
        $this->assert('8.5 Akun Admin ditolak meminjam karena hak peminjaman eksklusif Member', $adminLoan['success'] === false);
    }

    private function testDashboardAggregationIntegrity(): void
    {
        echo "\n--- TAHAP 9: KONSISTENSI AGREGASI DASHBOARD MEMBER & ADMIN ---\n";

        // Dashboard Admin
        $totalBooks = count($this->bookService->getAll());
        $allUsers = $this->authService->getAllUsers();
        $members = array_filter($allUsers, static fn(User $u): bool => $u->getRole() === 'member');
        $allTx = $this->loanService->getAllTransactions();
        $activeTx = $this->loanService->getAllTransactions(Transaction::STATUS_BORROWED);

        $this->assert('9.1 Dashboard Admin: Perhitungan total koleksi buku akurat', $totalBooks >= 3);
        $this->assert('9.2 Dashboard Admin: Perhitungan total member terdaftar akurat', count($members) >= 2);
        $this->assert('9.3 Dashboard Admin: Perhitungan total transaksi dan pinjaman aktif akurat', count($allTx) >= 2 && count($activeTx) >= 1);

        // Dashboard Member
        $memberLoans = $this->loanService->getMemberLoans('USR-003');
        $memberHistory = $this->loanService->getMemberHistory('USR-003');
        $this->assert(
            '9.4 Dashboard Member: Agregasi pinjaman aktif dan riwayat selesai sesuai data riil',
            count($memberLoans) === 1 && count($memberHistory) === 2
        );
    }

    private function testJsonContractAndAtomicity(): void
    {
        echo "\n--- TAHAP 10: INTEGRITAS KONTRAK DATA JSON & ATOMISITAS ---\n";

        // Verifikasi kontrak users.json
        $usersRaw = $this->userStorage->all();
        $userKeys = array_keys($usersRaw[0]);
        $expectedUserKeys = ['id', 'name', 'email', 'password', 'role', 'created_at'];
        $this->assert('10.1 Kontrak data users.json sesuai spesifikasi tim', empty(array_diff($expectedUserKeys, $userKeys)));

        // Verifikasi kontrak books.json
        $booksRaw = $this->bookStorage->all();
        $bookKeys = array_keys($booksRaw[0]);
        $expectedBookKeys = ['id', 'title', 'author', 'category_id', 'year', 'description', 'cover', 'available'];
        $this->assert('10.2 Kontrak data books.json sesuai spesifikasi tim', empty(array_diff($expectedBookKeys, $bookKeys)));

        // Verifikasi kontrak categories.json
        $catsRaw = $this->categoryStorage->all();
        $catKeys = array_keys($catsRaw[0]);
        $expectedCatKeys = ['id', 'name'];
        $this->assert('10.3 Kontrak data categories.json sesuai spesifikasi tim', empty(array_diff($expectedCatKeys, $catKeys)));

        // Verifikasi kontrak transactions.json
        $txsRaw = $this->transactionStorage->all();
        $txKeys = array_keys($txsRaw[0]);
        $expectedTxKeys = ['id', 'user_id', 'book_id', 'borrowed_at', 'due_at', 'returned_at', 'status'];
        $this->assert('10.4 Kontrak data transactions.json sesuai spesifikasi tim', empty(array_diff($expectedTxKeys, $txKeys)));
    }

    private function cleanup(): void
    {
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*') ?: []);
            @rmdir($this->tempDir);
        }
    }
}

// Jalankan suite pengujian
$suite = new IntegrationTestSuite();
exit($suite->runAll());
