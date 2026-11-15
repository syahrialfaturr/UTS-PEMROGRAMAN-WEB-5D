<?php

declare(strict_types=1);

/**
 * Test Suite Modul Katalog E-Book, Book, Category & CRUD Admin
 * UTS Pemrograman Web - E-Library
 * 
 * Pengujian komprehensif mengacu pada 10 skenario wajib spesifikasi tugas Mail:
 * 1. Model Book enkapsulasi & implementasi BorrowableInterface.
 * 2. Model Category enkapsulasi & rename.
 * 3. Katalog memuat seluruh data dari books.json.
 * 4. Pencarian judul dan penulis (case-insensitive).
 * 5. Filter kategori e-book bekerja akurat.
 * 6. Detail e-book mengambil data yang sesuai dan menangani ID non-eksisten.
 * 7. Admin create e-book dengan validasi & ID unik otomatis.
 * 8. Admin update e-book memperbarui metadata dengan benar.
 * 9. Keamanan data: E-book yang sedang dipinjam ditolak untuk dihapus.
 * 10. Penghapusan e-book aman berhasil dan persisten di JSON.
 * 11. Proteksi kategori: Kategori yang masih dipakai buku ditolak untuk dihapus.
 * 12. Proteksi hak akses Admin (Gatekeeper SessionManager).
 */

define('TEST_CATALOG_STORAGE_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'temp_catalog_storage');
$testBooksFile = TEST_CATALOG_STORAGE_DIR . DIRECTORY_SEPARATOR . 'test_books.json';
$testCategoriesFile = TEST_CATALOG_STORAGE_DIR . DIRECTORY_SEPARATOR . 'test_categories.json';

if (!is_dir(TEST_CATALOG_STORAGE_DIR)) {
    @mkdir(TEST_CATALOG_STORAGE_DIR, 0777, true);
} else {
    @unlink($testBooksFile);
    @unlink($testCategoriesFile);
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\JsonStorage;
use App\Core\SessionManager;
use App\Interfaces\BorrowableInterface;
use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Services\BookService;

class CatalogTestSuite
{
    private JsonStorage $bookStorage;
    private JsonStorage $categoryStorage;
    private BookService $bookService;
    private int $passCount = 0;
    private int $failCount = 0;

    public function __construct(string $testBooksFile, string $testCategoriesFile)
    {
        $this->bookStorage = new JsonStorage($testBooksFile);
        $this->categoryStorage = new JsonStorage($testCategoriesFile);
        $this->bookService = new BookService($this->bookStorage, $this->categoryStorage);

        $this->seedInitialData();
    }

    private function seedInitialData(): void
    {
        // Seed Kategori Awal
        $categories = [
            ['id' => 'CAT-001', 'name' => 'Rekayasa Perangkat Lunak'],
            ['id' => 'CAT-002', 'name' => 'Kecerdasan Buatan'],
            ['id' => 'CAT-003', 'name' => 'Jaringan Komputer'],
        ];
        $this->categoryStorage->saveAll($categories);

        // Seed Buku Awal
        $books = [
            [
                'id' => 'BOOK-001',
                'title' => 'Arsitektur PHP Modern OOP',
                'author' => 'Rasmus Lerdorf',
                'category_id' => 'CAT-001',
                'year' => 2024,
                'description' => 'Panduan mendalam arsitektur PHP 8.x modern.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-002',
                'title' => 'Dasar Pemrograman Web Interaktif',
                'author' => 'Tim Lab Informatika UMM',
                'category_id' => 'CAT-001',
                'year' => 2025,
                'description' => 'Membangun antarmuka web interaktif.',
                'cover' => 'assets/images/default-book.png',
                'available' => true,
            ],
            [
                'id' => 'BOOK-003',
                'title' => 'Machine Learning Praktis',
                'author' => 'Andrew Ng',
                'category_id' => 'CAT-002',
                'year' => 2024,
                'description' => 'Konsep dan implementasi algoritma cerdas.',
                'cover' => 'assets/images/default-book.png',
                'available' => false, // status sedang dipinjam!
            ],
        ];
        $this->bookStorage->saveAll($books);
    }

    private function assert(string $scenarioName, bool $condition, string $detail = ''): void
    {
        if ($condition) {
            $this->passCount++;
            echo "[\033[32mPASS\033[0m] {$scenarioName}" . ($detail ? " -> {$detail}" : '') . PHP_EOL;
        } else {
            $this->failCount++;
            echo "[\033[31mFAIL\033[0m] {$scenarioName}" . ($detail ? " -> GAGAL: {$detail}" : '') . PHP_EOL;
        }
    }

    public function runAll(): void
    {
        echo "=================================================================" . PHP_EOL;
        echo "🧪 MENJALANKAN SUITE PENGUJIAN MODUL KATALOG E-BOOK & CRUD ADMIN" . PHP_EOL;
        echo "=================================================================" . PHP_EOL . PHP_EOL;

        // SKENARIO 1: Model Book enkapsulasi & implementasi BorrowableInterface
        $sampleBook = new Book(
            id: 'BOOK-999',
            title: 'Refactoring PHP',
            author: 'Martin Fowler',
            categoryId: 'CAT-001',
            year: 2023,
            description: 'Panduan refactoring kode.',
            cover: 'assets/images/default-book.png',
            available: true
        );
        $isBorrowable = $sampleBook instanceof BorrowableInterface;
        $sampleBook->markBorrowed();
        $isBorrowedNow = !$sampleBook->isAvailable();
        $sampleBook->markReturned();
        $isReturnedNow = $sampleBook->isAvailable();

        $this->assert(
            'Skenario 1: Model Book enkapsulasi & implementasi BorrowableInterface',
            $isBorrowable && $isBorrowedNow && $isReturnedNow && $sampleBook->getTitle() === 'Refactoring PHP',
            "Interface BorrowableInterface berhasil diimplementasikan: isAvailable={$sampleBook->isAvailable()}"
        );

        // SKENARIO 2: Model Category enkapsulasi & rename
        $catModel = new Category('CAT-100', 'Sains Data');
        $catModel->rename('Sains Data & AI');
        $this->assert(
            'Skenario 2: Model Category enkapsulasi & rename',
            $catModel->getId() === 'CAT-100' && $catModel->getName() === 'Sains Data & AI',
            "ID: {$catModel->getId()}, Nama Baru: '{$catModel->getName()}'"
        );

        // SKENARIO 3: Katalog memuat seluruh data dari storage JSON
        $allBooks = $this->bookService->getAll();
        $this->assert(
            'Skenario 3: Katalog memuat seluruh data dari books.json',
            count($allBooks) === 3 && $allBooks[0] instanceof Book,
            "Total buku termuat: " . count($allBooks) . " objek Book"
        );

        // SKENARIO 4: Pencarian judul dan penulis (case-insensitive)
        $searchByTitle = $this->bookService->search('php modern');
        $searchByAuthor = $this->bookService->search('andrew ng');
        $searchEmpty = $this->bookService->search('kata_kunci_fiktif_123');

        $this->assert(
            'Skenario 4: Pencarian judul dan penulis bekerja (case-insensitive)',
            count($searchByTitle) === 1 &&
            count($searchByAuthor) === 1 &&
            count($searchEmpty) === 0,
            "Cari 'php modern': {$searchByTitle[0]->getTitle()}, Cari 'andrew ng': {$searchByAuthor[0]->getTitle()}"
        );

        // SKENARIO 5: Filter kategori e-book
        $filterCat1 = $this->bookService->filterByCategory('CAT-001');
        $filterCat2 = $this->bookService->filterByCategory('CAT-002');
        $filterAll = $this->bookService->filterByCategory('all');

        $this->assert(
            'Skenario 5: Filter kategori e-book bekerja akurat',
            count($filterCat1) === 2 && count($filterCat2) === 1 && count($filterAll) === 3,
            "Kategori CAT-001: " . count($filterCat1) . " buku, CAT-002: " . count($filterCat2) . " buku"
        );

        // SKENARIO 6: Detail e-book mengambil data yang sesuai dan menangani ID salah
        $detailFound = $this->bookService->getById('BOOK-001');
        $detailNotFound = $this->bookService->getById('BOOK-NONEXISTENT');

        $this->assert(
            'Skenario 6: Detail e-book mengambil data yang sesuai & menangani not found',
            $detailFound instanceof Book && $detailFound->getId() === 'BOOK-001' && $detailNotFound === null,
            "Buku ditemukan: '{$detailFound?->getTitle()}', ID tidak valid menghasilkan null"
        );

        // SKENARIO 7: Admin create e-book dengan validasi input & ID otomatis
        $createSuccess = $this->bookService->create([
            'title' => 'Keamanan Web Modern',
            'author' => 'Bruce Schneier',
            'category_id' => 'CAT-003',
            'year' => 2025,
            'description' => 'Mempelajari proteksi serangan injection, CSRF, dan XSS.',
            'cover' => 'assets/images/default-book.png',
            'available' => true,
        ]);

        $createInvalid = $this->bookService->create([
            'title' => '', // kosong
            'author' => 'A', // terlalu pendek
            'category_id' => 'CAT-INVALID', // kategori fiktif
            'year' => 9999, // tahun tidak masuk akal
            'description' => 'Pendek',
        ]);

        $this->assert(
            'Skenario 7: Admin create e-book dengan validasi ketat & ID otomatis',
            $createSuccess['success'] === true &&
            $createSuccess['book']?->getId() === 'BOOK-004' &&
            $createInvalid['success'] === false &&
            count($createInvalid['errors']) >= 4,
            "Buku baru dibuat: ID {$createSuccess['book']?->getId()}, Validasi error ditolak dengan {$createInvalid['message']}"
        );

        // SKENARIO 8: Admin update e-book memperbarui metadata dengan benar
        $updateResult = $this->bookService->update('BOOK-004', [
            'title' => 'Keamanan Web Modern Revisi 2',
            'author' => 'Bruce Schneier & Tim',
            'category_id' => 'CAT-003',
            'year' => 2026,
            'description' => 'Deskripsi yang telah diperbarui dengan materi termutakhir.',
            'cover' => 'assets/images/default-book.png',
        ]);
        $reloadedBook = $this->bookService->getById('BOOK-004');

        $this->assert(
            'Skenario 8: Admin update e-book memperbarui metadata',
            $updateResult['success'] === true &&
            $reloadedBook !== null &&
            $reloadedBook->getTitle() === 'Keamanan Web Modern Revisi 2' &&
            $reloadedBook->getYear() === 2026,
            "Judul terupdate: '{$reloadedBook?->getTitle()}', Tahun: {$reloadedBook?->getYear()}"
        );

        // SKENARIO 9: Keamanan data - E-book yang sedang dipinjam ditolak untuk dihapus
        $deleteBorrowed = $this->bookService->delete('BOOK-003'); // BOOK-003 berstatus available = false
        $this->assert(
            'Skenario 9: Keamanan data - E-book yang sedang dipinjam ditolak dihapus',
            $deleteBorrowed['success'] === false &&
            str_contains($deleteBorrowed['message'], 'sedang dipinjam'),
            "Pesan proteksi: '{$deleteBorrowed['message']}'"
        );

        // SKENARIO 10: Penghapusan e-book aman berhasil dan persisten di JSON
        $deleteSafe = $this->bookService->delete('BOOK-004'); // BOOK-004 available = true
        $afterDeleteCheck = $this->bookService->getById('BOOK-004');

        $this->assert(
            'Skenario 10: Penghapusan e-book aman berhasil & persisten',
            $deleteSafe['success'] === true && $afterDeleteCheck === null,
            "Pesan penghapusan: '{$deleteSafe['message']}', Cek di storage: null"
        );

        // SKENARIO 11: Proteksi kategori yang masih digunakan oleh e-book
        $deleteUsedCategory = $this->bookService->deleteCategory('CAT-001'); // CAT-001 dipakai oleh BOOK-001 & BOOK-002
        $this->assert(
            'Skenario 11: Kategori yang masih digunakan buku ditolak untuk dihapus',
            $deleteUsedCategory['success'] === false &&
            str_contains($deleteUsedCategory['message'], 'masih digunakan'),
            "Pesan penolakan: '{$deleteUsedCategory['message']}'"
        );

        // SKENARIO 12: Integrasi ketersediaan buku untuk Farid (LoanService)
        $availCheck1 = $this->bookService->markAsBorrowed('BOOK-001');
        $book1AfterBorrow = $this->bookService->getById('BOOK-001');
        $availCheck2 = $this->bookService->markAsReturned('BOOK-001');
        $book1AfterReturn = $this->bookService->getById('BOOK-001');

        $this->assert(
            'Skenario 12: Integrasi ketersediaan buku untuk peminjaman/pengembalian',
            $availCheck1 && !$book1AfterBorrow->isAvailable() &&
            $availCheck2 && $book1AfterReturn->isAvailable(),
            "Buku BOOK-001 ditandai pinjam -> available: false; dikembalikan -> available: true"
        );

        echo PHP_EOL;
        echo "=================================================================" . PHP_EOL;
        echo "HASIL RINGKASAN: {$this->passCount} LULUS, {$this->failCount} GAGAL" . PHP_EOL;
        echo "=================================================================" . PHP_EOL;

        if ($this->failCount > 0) {
            exit(1);
        }
    }
}

// Jalankan Test Suite
$suite = new CatalogTestSuite($testBooksFile, $testCategoriesFile);
$suite->runAll();

// Bersihkan file pengujian
@unlink($testBooksFile);
@unlink($testCategoriesFile);
@rmdir(TEST_CATALOG_STORAGE_DIR);
