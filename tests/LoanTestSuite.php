<?php

declare(strict_types=1);

/**
 * Test Suite Modul Transaksi (Farid) - E-Library
 * Menjalankan 11 skenario pengujian wajib + kasus tepi pada storage terisolasi.
 * Jalankan: php tests/LoanTestSuite.php
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\JsonStorage;
use App\Models\Transaction;
use App\Services\AuthService;
use App\Services\BookService;
use App\Services\LoanService;

final class LoanTestSuite
{
    private string $dir;
    private JsonStorage $books;
    private JsonStorage $transactions;
    private BookService $bookService;
    private LoanService $loan;
    private int $pass = 0;
    private int $fail = 0;

    public function __construct()
    {
        $this->dir = __DIR__ . DIRECTORY_SEPARATOR . 'temp_loan_storage';
        $this->reset();
    }

    private function reset(): void
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
        array_map('unlink', glob($this->dir . '/*') ?: []);

        $p = fn(string $f): string => $this->dir . DIRECTORY_SEPARATOR . $f;
        $hash = password_hash('secret123', PASSWORD_DEFAULT);

        $users = new JsonStorage($p('users.json'));
        $users->saveAll([
            ['id' => 'USR-001', 'name' => 'Admin', 'email' => 'a@x.id', 'password' => $hash, 'role' => 'admin', 'created_at' => '2026-01-01 00:00:00'],
            ['id' => 'USR-002', 'name' => 'Member A', 'email' => 'm1@x.id', 'password' => $hash, 'role' => 'member', 'created_at' => '2026-01-01 00:00:00'],
            ['id' => 'USR-003', 'name' => 'Member B', 'email' => 'm2@x.id', 'password' => $hash, 'role' => 'member', 'created_at' => '2026-01-01 00:00:00'],
        ]);
        $categories = new JsonStorage($p('categories.json'));
        $categories->saveAll([['id' => 'CAT-001', 'name' => 'Web']]);

        $this->books = new JsonStorage($p('books.json'));
        $bookRows = [];
        for ($i = 1; $i <= 5; $i++) {
            $bookRows[] = ['id' => sprintf('BOOK-%03d', $i), 'title' => "Buku {$i}", 'author' => 'Penulis', 'category_id' => 'CAT-001',
                'year' => 2024, 'description' => 'Deskripsi', 'cover' => 'assets/images/default-book.png', 'available' => true];
        }
        $this->books->saveAll($bookRows);

        $this->transactions = new JsonStorage($p('transactions.json'));
        $this->bookService = new BookService($this->books, $categories, $this->transactions);
        $this->loan = new LoanService($this->transactions, $this->bookService, new AuthService($users));
    }

    private function check(string $name, bool $cond, string $detail = ''): void
    {
        if ($cond) {
            $this->pass++;
            echo "  [PASS] {$name}\n";
        } else {
            $this->fail++;
            echo "  [FAIL] {$name}" . ($detail !== '' ? " -> {$detail}" : '') . "\n";
        }
    }

    public function run(): int
    {
        echo "=== LoanTestSuite ===\n";

        // 1. Member meminjam e-book tersedia
        $r = $this->loan->borrow('USR-002', 'BOOK-001');
        $this->check('1. Member meminjam e-book tersedia', $r['success'] === true, $r['message']);
        /** @var Transaction $t */
        $t = $r['transaction'];
        $raw = $this->transactions->findById($t->getId());
        $this->check('1a. Transaksi tersimpan dengan field kontrak JSON',
            $raw !== null && array_keys($raw) === ['id', 'user_id', 'book_id', 'borrowed_at', 'due_at', 'returned_at', 'status']);
        $this->check('1b. Status awal borrowed, returned_at null', $raw['status'] === 'borrowed' && $raw['returned_at'] === null);
        $diff = (new DateTimeImmutable($raw['borrowed_at']))->diff(new DateTimeImmutable($raw['due_at']));
        $this->check('1c. due_at = borrowed_at + 7 hari', $diff->days === 7 && $diff->h === 0 && $diff->i === 0);
        $this->check('1d. ID transaksi berformat TRX-YYYYMMDD-XXXXXX', (bool)preg_match('/^TRX-\d{8}-[0-9A-F]{6}$/', $raw['id']));

        // 2. Status e-book berubah menjadi tidak tersedia
        $this->check('2. E-book menjadi tidak tersedia', $this->bookService->getById('BOOK-001')?->isAvailable() === false);

        // 3. Member lain tidak dapat meminjam e-book yang sama
        $r = $this->loan->borrow('USR-003', 'BOOK-001');
        $this->check('3. Member lain ditolak meminjam e-book yang sama', $r['success'] === false, $r['message']);
        $r = $this->loan->borrow('USR-002', 'BOOK-001');
        $this->check('3a. Member yang sama tidak bisa meminjam dua kali', $r['success'] === false);

        // 4. Guest tidak dapat meminjam
        $r = $this->loan->borrow('', 'BOOK-002');
        $this->check('4. Guest (tanpa user id) ditolak', $r['success'] === false);
        $r = $this->loan->borrow('USR-999', 'BOOK-002');
        $this->check('4a. User id tidak terdaftar ditolak', $r['success'] === false);
        $this->check('4b. E-book tetap tersedia setelah penolakan', $this->bookService->getById('BOOK-002')?->isAvailable() === true);

        // 5. Member melihat pinjaman miliknya
        $this->loan->borrow('USR-003', 'BOOK-002');
        $loansA = $this->loan->getMemberLoans('USR-002');
        $this->check('5. Member A hanya melihat pinjaman miliknya',
            count($loansA) === 1 && $loansA[0]->getBookId() === 'BOOK-001');
        $loansB = $this->loan->getMemberLoans('USR-003');
        $this->check('5a. Member B hanya melihat pinjaman miliknya',
            count($loansB) === 1 && $loansB[0]->getBookId() === 'BOOK-002');

        // 7. Member tidak dapat mengembalikan transaksi orang lain
        $r = $this->loan->returnBook('USR-003', $t->getId());
        $this->check('7. Member B ditolak mengembalikan transaksi milik A', $r['success'] === false, $r['message']);
        $this->check('7a. Transaksi A tetap borrowed', $this->loan->findById($t->getId())?->isActive() === true);
        $this->check('7b. E-book A tetap tidak tersedia', $this->bookService->getById('BOOK-001')?->isAvailable() === false);

        // 6. Member mengembalikan pinjaman sendiri
        $r = $this->loan->returnBook('USR-002', $t->getId());
        $this->check('6. Member A mengembalikan pinjamannya', $r['success'] === true, $r['message']);
        $raw = $this->transactions->findById($t->getId());
        $this->check('6a. Status returned & returned_at terisi', $raw['status'] === 'returned' && is_string($raw['returned_at']) && $raw['returned_at'] !== '');

        // 8. Transaksi returned tidak dapat dikembalikan lagi
        $r = $this->loan->returnBook('USR-002', $t->getId());
        $this->check('8. Pengembalian kedua kali ditolak', $r['success'] === false, $r['message']);
        $threw = false;
        try {
            Transaction::fromArray($raw)->markReturned();
        } catch (LogicException) {
            $threw = true;
        }
        $this->check('8a. Model Transaction::markReturned() menolak status returned', $threw);

        // 9. E-book tersedia kembali
        $this->check('9. E-book tersedia kembali setelah pengembalian', $this->bookService->getById('BOOK-001')?->isAvailable() === true);
        $r = $this->loan->borrow('USR-003', 'BOOK-001');
        $this->check('9a. E-book bisa dipinjam member lain setelah dikembalikan', $r['success'] === true);

        // 10. Admin dapat melihat semua transaksi
        $all = $this->loan->getAllTransactions();
        $this->check('10. getAllTransactions() memuat seluruh transaksi (3)', count($all) === 3);
        $this->check('10a. Filter status returned', count($this->loan->getAllTransactions('returned')) === 1);
        $this->check('10b. Riwayat member A berisi 1 transaksi returned',
            count($h = $this->loan->getMemberHistory('USR-002')) === 1 && !$h[0]->isActive());

        // 11. Percobaan tanpa hak akses ditolak backend (level service)
        $r = $this->loan->borrow('USR-001', 'BOOK-003');
        $this->check('11. Admin ditolak meminjam (bukan Member)', $r['success'] === false);
        $r = $this->loan->returnBook('USR-002', 'TRX-TIDAK-ADA');
        $this->check('11a. Transaksi tidak ada ditolak', $r['success'] === false);
        $r = $this->loan->borrow('USR-002', 'BOOK-404');
        $this->check('11b. E-book tidak ada ditolak', $r['success'] === false);

        // Kasus tepi: batas pinjaman (Member::canBorrow, default 3)
        $this->loan->borrow('USR-002', 'BOOK-003');
        $this->loan->borrow('USR-002', 'BOOK-004');
        $this->loan->borrow('USR-002', 'BOOK-005');
        $r = $this->loan->borrow('USR-002', 'BOOK-002');
        $this->check('E1. Batas pinjaman simultan (3) ditegakkan', count($this->loan->getMemberLoans('USR-002')) === 3);

        // Kasus tepi: JSON transaksi rusak -> mutasi ditolak, file tidak ditimpa
        $path = $this->transactions->getFilePath();
        file_put_contents($path, '{rusak');
        $r = $this->loan->returnBook('USR-002', 'apa-saja');
        $this->check('E2. JSON rusak: mutasi ditolak', $r['success'] === false && str_contains($r['message'], 'rusak'));
        $this->check('E2a. JSON rusak tidak ditimpa', file_get_contents($path) === '{rusak');

        // Kasus tepi: file transaksi hilang -> dibuat ulang, tidak error
        unlink($path);
        $this->check('E3. File transaksi hilang: query mengembalikan []', $this->loan->getAllTransactions() === []);

        echo "\nHasil: {$this->pass} lulus, {$this->fail} gagal\n";
        array_map('unlink', glob($this->dir . '/*') ?: []);
        @rmdir($this->dir);
        return $this->fail === 0 ? 0 : 1;
    }
}

exit((new LoanTestSuite())->run());
