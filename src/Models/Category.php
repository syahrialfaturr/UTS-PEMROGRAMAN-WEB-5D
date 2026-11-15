<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class Category
 * Model untuk entitas kategori e-book dalam katalog perpustakaan.
 * Menerapkan prinsip Enkapsulasi penuh dengan atribut private dan method akses.
 */
class Category
{
    /**
     * @param string $id ID unik kategori (misal: CAT-001)
     * @param string $name Nama kategori (misal: Teknologi)
     */
    public function __construct(
        private string $id,
        private string $name
    ) {
    }

    /**
     * Mendapatkan ID kategori.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Mendapatkan nama kategori.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Mengubah nama kategori (rename).
     *
     * @param string $newName
     */
    public function rename(string $newName): void
    {
        $trimmed = trim($newName);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Nama kategori tidak boleh kosong.');
        }
        $this->name = $trimmed;
    }

    /**
     * Mengubah objek Category menjadi array representasi penyimpanan JSON.
     *
     * @return array{id: string, name: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    /**
     * Factory method untuk membuat objek Category dari data array JSON.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (string)($data['id'] ?? ''),
            (string)($data['name'] ?? '')
        );
    }
}
