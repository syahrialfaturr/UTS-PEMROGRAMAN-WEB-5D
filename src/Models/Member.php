<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Member
 * Mewarisi (Inheritance) dari User sebagai representasi anggota perpustakaan.
 */
class Member extends User
{
    /**
     * @param string $id
     * @param string $name
     * @param string $email
     * @param string $passwordHash
     * @param int $loanLimit Batas maksimal buku yang dapat dipinjam secara bersamaan
     */
    public function __construct(
        string $id,
        string $name,
        string $email,
        string $passwordHash,
        private int $loanLimit = 3
    ) {
        parent::__construct($id, $name, $email, $passwordHash, 'member');
    }

    /**
     * Mendapatkan kuota batas maksimal peminjaman.
     */
    public function getLoanLimit(): int
    {
        return $this->loanLimit;
    }

    /**
     * Memeriksa apakah member masih berhak meminjam buku baru berdasarkan jumlah pinjaman aktif saat ini.
     *
     * @param int $currentActiveLoans Jumlah transaksi buku yang sedang dipinjam (status 'borrowed')
     * @return bool
     */
    public function canBorrow(int $currentActiveLoans): bool
    {
        return $currentActiveLoans < $this->loanLimit;
    }

    /**
     * Demonstrasi Polimorfisme: Override method getRoleDescription() dari parent class User.
     *
     * @return string
     */
    public function getRoleDescription(): string
    {
        return "Member E-Library: Anggota terdaftar dengan hak peminjaman e-book aktif maksimal {$this->loanLimit} item sekaligus.";
    }

    /**
     * Mengubah data model menjadi array representasi penyimpanan.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['loan_limit'] = $this->loanLimit;
        return $data;
    }
}
