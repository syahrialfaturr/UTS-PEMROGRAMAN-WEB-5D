<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\JsonStorage;
use App\Models\Member;
use App\Models\Transaction;

/**
 * Class LoanService
 * Business logic peminjaman & pengembalian e-book.
 *
 * Dependensi modul anggota tim lain (tidak dibuat ulang):
 * - BookService::getById(), markAsBorrowed(), markAsReturned()  (Mail)
 * - AuthService::findUserById()                                 (Syahrial)
 * - JsonStorage::all(), insert(), update(), delete(), getFilePath()
 *
 * Konsistensi data: transaksi & ketersediaan buku ditulis berpasangan.
 * Jika langkah kedua gagal, langkah pertama di-rollback. Seluruh mutasi
 * dijalankan di bawah file lock agar dua request bersamaan tidak bisa
 * meminjam buku yang sama.
 */
class LoanService
{
    private JsonStorage $transactionStorage;
    private BookService $bookService;
    private AuthService $authService;
    private int $loanDurationDays;

    public function __construct(
        ?JsonStorage $transactionStorage = null,
        ?BookService $bookService = null,
        ?AuthService $authService = null,
        int $loanDurationDays = 7
    ) {
        $storageDir = defined('STORAGE_PATH')
            ? STORAGE_PATH
            : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage';

        $this->transactionStorage = $transactionStorage
            ?? new JsonStorage($storageDir . DIRECTORY_SEPARATOR . 'transactions.json');
        $this->bookService = $bookService ?? new BookService(transactionStorage: $this->transactionStorage);
        $this->authService = $authService ?? new AuthService();
        $this->loanDurationDays = max(1, $loanDurationDays);
    }

    // =========================================================================
    // Mutasi
    // =========================================================================

    /**
     * Meminjam e-book. $userId WAJIB berasal dari session, bukan dari form.
     *
     * @return array{success: bool, message: string, transaction?: Transaction}
     */
    public function borrow(string $userId, string $bookId): array
    {
        $bookId = trim($bookId);
        if ($userId === '' || $bookId === '') {
            return $this->fail('Data peminjaman tidak lengkap.');
        }

        $user = $this->authService->findUserById($userId);
        if (!$user instanceof Member) {
            return $this->fail('Hanya Member terdaftar yang dapat meminjam e-book.');
        }

        return $this->withLock(function () use ($user, $bookId): array {
            if (($err = $this->storageError()) !== null) {
                return $this->fail($err);
            }

            $book = $this->bookService->getById($bookId);
            if ($book === null) {
                return $this->fail('E-book tidak ditemukan.');
            }
            if (!$book->isAvailable() || $this->hasActiveLoanForBook($bookId)) {
                return $this->fail("E-book '{$book->getTitle()}' sedang dipinjam dan tidak tersedia.");
            }

            $activeCount = count($this->getMemberLoans($user->getId()));
            if (!$user->canBorrow($activeCount)) {
                return $this->fail("Batas pinjaman tercapai ({$user->getLoanLimit()} e-book). Kembalikan salah satu terlebih dahulu.");
            }

            $now = new \DateTimeImmutable();
            $transaction = new Transaction(
                id: $this->generateId($now),
                userId: $user->getId(),
                bookId: $bookId,
                borrowedAt: $now->format(Transaction::DATE_FORMAT),
                dueAt: $now->modify("+{$this->loanDurationDays} days")->format(Transaction::DATE_FORMAT)
            );

            if (!$this->transactionStorage->insert($transaction->toArray())) {
                return $this->fail('Gagal menyimpan transaksi. Coba lagi.');
            }

            if (!$this->bookService->markAsBorrowed($bookId)) {
                // Rollback agar transaksi & ketersediaan buku tetap konsisten
                $this->transactionStorage->delete($transaction->getId());
                return $this->fail('Gagal memperbarui status e-book. Peminjaman dibatalkan.');
            }

            return [
                'success' => true,
                'message' => "Berhasil meminjam '{$book->getTitle()}'. Jatuh tempo: {$transaction->getDueAt()}.",
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Mengembalikan e-book. Hanya pemilik transaksi yang boleh mengembalikan.
     *
     * @return array{success: bool, message: string, transaction?: Transaction}
     */
    public function returnBook(string $userId, string $transactionId): array
    {
        $transactionId = trim($transactionId);
        if ($userId === '' || $transactionId === '') {
            return $this->fail('Data pengembalian tidak lengkap.');
        }

        return $this->withLock(function () use ($userId, $transactionId): array {
            if (($err = $this->storageError()) !== null) {
                return $this->fail($err);
            }

            $transaction = $this->findById($transactionId);
            if ($transaction === null) {
                return $this->fail('Transaksi tidak ditemukan.');
            }
            if (!$transaction->belongsTo($userId)) {
                return $this->fail('Anda tidak berhak mengembalikan transaksi milik orang lain.');
            }
            if (!$transaction->isActive()) {
                return $this->fail('Transaksi ini sudah dikembalikan sebelumnya.');
            }

            $original = $transaction->toArray();
            $transaction->markReturned();

            if (!$this->transactionStorage->update($transactionId, $transaction->toArray())) {
                return $this->fail('Gagal memperbarui transaksi. Coba lagi.');
            }

            if (!$this->bookService->markAsReturned($transaction->getBookId())) {
                $book = $this->bookService->getById($transaction->getBookId());
                if ($book !== null) {
                    // Buku masih ada tapi gagal ditulis: rollback transaksi
                    $this->transactionStorage->update($transactionId, $original);
                    return $this->fail('Gagal memperbarui status e-book. Pengembalian dibatalkan.');
                }
                // Buku sudah dihapus dari katalog: transaksi tetap ditutup
            }

            return [
                'success' => true,
                'message' => 'E-book berhasil dikembalikan. Terima kasih!',
                'transaction' => $transaction,
            ];
        });
    }

    // =========================================================================
    // Query
    // =========================================================================

    /**
     * Pinjaman aktif milik member (status borrowed), jatuh tempo terdekat dulu.
     *
     * @return array<int, Transaction>
     */
    public function getMemberLoans(string $userId): array
    {
        $loans = array_filter(
            $this->getMemberHistory($userId),
            static fn(Transaction $t): bool => $t->isActive()
        );
        usort($loans, static fn(Transaction $a, Transaction $b): int => strcmp($a->getDueAt(), $b->getDueAt()));
        return $loans;
    }

    /**
     * Seluruh transaksi milik member, terbaru dulu.
     *
     * @return array<int, Transaction>
     */
    public function getMemberHistory(string $userId): array
    {
        return array_values(array_filter(
            $this->getAllTransactions(),
            static fn(Transaction $t): bool => $t->belongsTo($userId)
        ));
    }

    /**
     * Seluruh transaksi (untuk Admin), terbaru dulu. Opsional filter status.
     *
     * @return array<int, Transaction>
     */
    public function getAllTransactions(?string $status = null): array
    {
        $transactions = [];
        foreach ($this->transactionStorage->all() as $record) {
            if (!is_array($record)) {
                continue;
            }
            try {
                $t = Transaction::fromArray($record);
            } catch (\InvalidArgumentException) {
                continue; // abaikan rekaman rusak
            }
            if ($status === null || $t->getStatus() === $status) {
                $transactions[] = $t;
            }
        }

        usort($transactions, static fn(Transaction $a, Transaction $b): int => strcmp($b->getBorrowedAt(), $a->getBorrowedAt()));
        return $transactions;
    }

    public function findById(string $transactionId): ?Transaction
    {
        $record = $this->transactionStorage->findById($transactionId);
        if ($record === null) {
            return null;
        }
        try {
            return Transaction::fromArray($record);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    // =========================================================================
    // Helper internal
    // =========================================================================

    private function hasActiveLoanForBook(string $bookId): bool
    {
        foreach ($this->getAllTransactions(Transaction::STATUS_BORROWED) as $t) {
            if ($t->getBookId() === $bookId) {
                return true;
            }
        }
        return false;
    }

    /**
     * JsonStorage::all() mengembalikan [] untuk JSON rusak. Menulis di atasnya
     * akan menghapus seluruh riwayat, jadi mutasi ditolak bila file tidak valid.
     */
    private function storageError(): ?string
    {
        $path = $this->transactionStorage->getFilePath();
        if (!file_exists($path)) {
            return null; // akan dibuat saat insert
        }
        $fp = @fopen($path, 'a');
        if ($fp === false) {
            return 'Berkas transaksi tidak dapat ditulis. Hubungi administrator.';
        }
        fclose($fp);

        $raw = file_get_contents($path);
        if ($raw === false) {
            return 'Berkas transaksi tidak dapat dibaca.';
        }
        if (trim($raw) !== '' && !is_array(json_decode($raw, true))) {
            return 'Berkas transaksi rusak (JSON tidak valid). Transaksi dibatalkan untuk mencegah kehilangan data.';
        }
        return null;
    }

    /**
     * Menjalankan callback secara eksklusif (flock) agar read-check-write atomik.
     *
     * @param callable(): array{success: bool, message: string, transaction?: Transaction} $callback
     * @return array{success: bool, message: string, transaction?: Transaction}
     */
    private function withLock(callable $callback): array
    {
        $lockPath = $this->transactionStorage->getFilePath() . '.lock';
        $handle = @fopen($lockPath, 'c');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            return $this->fail('Sistem sedang sibuk. Silakan coba lagi.');
        }
        try {
            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function generateId(\DateTimeImmutable $now): string
    {
        do {
            $id = 'TRX-' . $now->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while ($this->transactionStorage->findById($id) !== null);
        return $id;
    }

    /**
     * @return array{success: false, message: string}
     */
    private function fail(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
