<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Abstract Class User
 * Parent class bagi seluruh entitas pengguna di dalam sistem E-Library.
 * Mengimplementasikan Enkapsulasi penuh dengan atribut bertipe dan visibility protected.
 */
abstract class User
{
    /**
     * @param string $id ID unik pengguna (misal: USR-001)
     * @param string $name Nama lengkap pengguna
     * @param string $email Alamat email pengguna (harus unik)
     * @param string $passwordHash Hash password menggunakan password_hash()
     * @param string $role Peran pengguna ('admin' atau 'member')
     */
    public function __construct(
        protected string $id,
        protected string $name,
        protected string $email,
        protected string $passwordHash,
        protected string $role
    ) {
    }

    /**
     * Mendapatkan ID pengguna.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Mendapatkan nama lengkap pengguna.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Mendapatkan alamat email pengguna.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Mendapatkan hash password pengguna.
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * Mendapatkan peran (role) pengguna.
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Memverifikasi apakah plain-text password sesuai dengan hash yang tersimpan.
     * Menggunakan fungsi bawaan PHP password_verify().
     *
     * @param string $plainPassword
     * @return bool
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    /**
     * Mengubah data model menjadi array representasi penyimpanan.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->passwordHash,
            'role' => $this->role,
        ];
    }

    /**
     * Method abstrak untuk mendemonstrasikan Polimorfisme (Polymorphism).
     * Setiap subclass (Member & Admin) wajib meng-override method ini
     * untuk memberikan deskripsi peran yang spesifik.
     *
     * @return string
     */
    abstract public function getRoleDescription(): string;
}
