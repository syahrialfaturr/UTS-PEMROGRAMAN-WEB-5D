<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Transaction
 * Model entitas transaksi peminjaman e-book.
 * Seluruh atribut bersifat private (Encapsulation); perubahan status hanya
 * dapat dilakukan melalui method markReturned() sehingga aturan bisnis terjaga.
 */
class Transaction
{
    public const STATUS_BORROWED = 'borrowed';
    public const STATUS_RETURNED = 'returned';
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private string $id,
        private string $userId,
        private string $bookId,
        private string $borrowedAt,
        private string $dueAt,
        private ?string $returnedAt = null,
        private string $status = self::STATUS_BORROWED
    ) {
        if (!in_array($status, [self::STATUS_BORROWED, self::STATUS_RETURNED], true)) {
            throw new \InvalidArgumentException("Status transaksi tidak valid: {$status}");
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getBookId(): string
    {
        return $this->bookId;
    }

    public function getBorrowedAt(): string
    {
        return $this->borrowedAt;
    }

    public function getDueAt(): string
    {
        return $this->dueAt;
    }

    public function getReturnedAt(): ?string
    {
        return $this->returnedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Transaksi aktif = e-book masih dipinjam (belum dikembalikan).
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_BORROWED;
    }

    /**
     * Apakah transaksi aktif sudah melewati tanggal jatuh tempo.
     */
    public function isOverdue(?\DateTimeImmutable $now = null): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        $now ??= new \DateTimeImmutable();
        return $now > new \DateTimeImmutable($this->dueAt);
    }

    /**
     * Apakah transaksi ini milik pengguna tertentu.
     */
    public function belongsTo(string $userId): bool
    {
        return $this->userId === $userId;
    }

    /**
     * Menandai transaksi sebagai dikembalikan.
     * Menolak pengembalian kedua kali.
     *
     * @throws \LogicException jika transaksi sudah berstatus returned
     */
    public function markReturned(?\DateTimeImmutable $at = null): void
    {
        if (!$this->isActive()) {
            throw new \LogicException('Transaksi ini sudah dikembalikan sebelumnya.');
        }
        $this->returnedAt = ($at ?? new \DateTimeImmutable())->format(self::DATE_FORMAT);
        $this->status = self::STATUS_RETURNED;
    }

    /**
     * Representasi array sesuai kontrak transactions.json.
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'book_id' => $this->bookId,
            'borrowed_at' => $this->borrowedAt,
            'due_at' => $this->dueAt,
            'returned_at' => $this->returnedAt,
            'status' => $this->status,
        ];
    }

    /**
     * Factory dari rekaman JSON.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $returnedAt = $data['returned_at'] ?? null;

        return new self(
            id: (string)($data['id'] ?? ''),
            userId: (string)($data['user_id'] ?? ''),
            bookId: (string)($data['book_id'] ?? ''),
            borrowedAt: (string)($data['borrowed_at'] ?? ''),
            dueAt: (string)($data['due_at'] ?? ''),
            returnedAt: $returnedAt === null || $returnedAt === '' ? null : (string)$returnedAt,
            status: (string)($data['status'] ?? self::STATUS_BORROWED)
        );
    }
}
