<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Admin
 * Mewarisi (Inheritance) dari User sebagai pengelola sistem perpustakaan.
 */
class Admin extends User
{
    /**
     * @param string $id
     * @param string $name
     * @param string $email
     * @param string $passwordHash
     */
    public function __construct(
        string $id,
        string $name,
        string $email,
        string $passwordHash
    ) {
        parent::__construct($id, $name, $email, $passwordHash, 'admin');
    }

    /**
     * Memeriksa apakah role admin memiliki izin mengelola katalog buku.
     */
    public function canManageBooks(): bool
    {
        return true;
    }

    /**
     * Memeriksa apakah role admin memiliki izin mengelola anggota/user perpustakaan.
     */
    public function canManageMembers(): bool
    {
        return true;
    }

    /**
     * Demonstrasi Polimorfisme: Override method getRoleDescription() dari parent class User.
     *
     * @return string
     */
    public function getRoleDescription(): string
    {
        return 'Administrator E-Library: Pengelola sistem dengan wewenang penuh atas katalog e-book, kategori, data pengguna, dan seluruh transaksi.';
    }
}

