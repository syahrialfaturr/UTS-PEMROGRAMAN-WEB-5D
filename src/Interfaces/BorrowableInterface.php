<?php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Interface BorrowableInterface
 * Mendefinisikan kontrak polimorfik untuk setiap entitas koleksi perpustakaan
 * yang memiliki status ketersediaan dan dapat dipinjam/dikembalikan.
 */
interface BorrowableInterface
{
    /**
     * Memeriksa apakah item saat ini tersedia untuk dipinjam.
     */
    public function isAvailable(): bool;

    /**
     * Menandai status item sebagai sedang dipinjam (tidak tersedia).
     */
    public function markBorrowed(): void;

    /**
     * Menandai status item sebagai telah dikembalikan (tersedia kembali).
     */
    public function markReturned(): void;
}
