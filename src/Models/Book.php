<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\BorrowableInterface;

/**
 * Class Book
 * Model entitas e-book di dalam katalog perpustakaan digital E-Library.
 * Mengimplementasikan Enkapsulasi penuh dan mengimplementasikan BorrowableInterface
 * sebagai wujud Polimorfisme untuk item yang dapat dipinjam.
 */
class Book implements BorrowableInterface
{
    /**
     * @param string $id ID unik buku (misal: BOOK-001)
     * @param string $title Judul e-book
     * @param string $author Nama penulis / pengarang
     * @param string $categoryId ID kategori yang terdaftar (misal: CAT-001)
     * @param int $year Tahun terbit
     * @param string $description Sinopsis atau deskripsi buku
     * @param string $cover Path gambar cover lokal atau URL
     * @param bool $available Status ketersediaan untuk dipinjam (default: true)
     */
    public function __construct(
        private string $id,
        private string $title,
        private string $author,
        private string $categoryId,
        private int $year,
        private string $description,
        private string $cover,
        private bool $available = true
    ) {
    }

    /**
     * Mendapatkan ID e-book.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Mendapatkan judul e-book.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Mendapatkan nama penulis.
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Mendapatkan ID kategori e-book.
     */
    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    /**
     * Mendapatkan tahun publikasi.
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Mendapatkan deskripsi / ringkasan buku.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Mendapatkan path atau URL gambar cover e-book.
     */
    public function getCover(): string
    {
        return $this->cover;
    }

    /**
     * Memeriksa apakah e-book saat ini tersedia untuk dipinjam.
     * Implementasi dari BorrowableInterface.
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Alias getter untuk kompatibilitas properti boolean.
     */
    public function getAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Menandai status buku menjadi sedang dipinjam (tidak tersedia).
     * Implementasi dari BorrowableInterface.
     */
    public function markBorrowed(): void
    {
        $this->available = false;
    }

    /**
     * Menandai status buku menjadi telah dikembalikan (tersedia kembali).
     * Implementasi dari BorrowableInterface.
     */
    public function markReturned(): void
    {
        $this->available = true;
    }

    /**
     * Mengatur status ketersediaan buku secara langsung.
     */
    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    /**
     * Memperbarui metadata e-book.
     *
     * @param string $title
     * @param string $author
     * @param string $categoryId
     * @param int $year
     * @param string $description
     * @param string|null $cover Jika null, tetap menggunakan cover sebelumnya
     */
    public function updateDetails(
        string $title,
        string $author,
        string $categoryId,
        int $year,
        string $description,
        ?string $cover = null
    ): void {
        $this->title = trim($title);
        $this->author = trim($author);
        $this->categoryId = trim($categoryId);
        $this->year = $year;
        $this->description = trim($description);

        if ($cover !== null && trim($cover) !== '') {
            $this->cover = trim($cover);
        }
    }

    /**
     * Mengubah objek Book menjadi bentuk array untuk persistensi JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'category_id' => $this->categoryId,
            'year' => $this->year,
            'description' => $this->description,
            'cover' => $this->cover,
            'available' => $this->available,
        ];
    }

    /**
     * Factory method untuk membuat instance Book dari data array mentah JSON.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string)($data['id'] ?? ''),
            title: (string)($data['title'] ?? ''),
            author: (string)($data['author'] ?? ''),
            categoryId: (string)($data['category_id'] ?? ''),
            year: (int)($data['year'] ?? 0),
            description: (string)($data['description'] ?? ''),
            cover: (string)($data['cover'] ?? 'assets/images/default-book.png'),
            available: (bool)($data['available'] ?? true)
        );
    }
}
