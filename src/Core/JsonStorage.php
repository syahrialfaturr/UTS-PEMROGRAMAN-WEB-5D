<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class JsonStorage
 * Bertanggung jawab atas persistensi data berbasis file JSON lokal.
 * Menerapkan enkapsulasi manipulasi data JSON secara konsisten.
 */
class JsonStorage
{
    private string $filePath;

    /**
     * @param string $filePath Path absolut atau relatif ke file JSON
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->ensureFileExists();
    }

    /**
     * Memastikan direktori dan file JSON ada.
     */
    private function ensureFileExists(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    /**
     * Mengambil seluruh data dari file JSON sebagai array asosiatif.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $raw = file_get_contents($this->filePath);
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Mencari satu rekaman data berdasarkan ID.
     *
     * @param string $id
     * @return array<string, mixed>|null
     */
    public function findById(string $id): ?array
    {
        $items = $this->all();
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Menyimpan seluruh array data ke dalam file JSON secara atomik.
     *
     * @param array<int, array<string, mixed>> $data
     * @return bool
     */
    public function saveAll(array $data): bool
    {
        $encoded = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            return false;
        }

        $tempFile = $this->filePath . '.tmp.' . bin2hex(random_bytes(4));
        if (file_put_contents($tempFile, $encoded, LOCK_EX) === false) {
            return false;
        }

        return rename($tempFile, $this->filePath);
    }

    /**
     * Menambahkan satu rekaman baru ke dalam file JSON.
     *
     * @param array<string, mixed> $record
     * @return bool
     */
    public function insert(array $record): bool
    {
        $items = $this->all();
        $items[] = $record;
        return $this->saveAll($items);
    }

    /**
     * Memperbarui rekaman data yang ada berdasarkan ID.
     *
     * @param string $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        $items = $this->all();
        $found = false;

        foreach ($items as $index => $item) {
            if (isset($item['id']) && $item['id'] === $id) {
                $items[$index] = array_merge($item, $data);
                $found = true;
                break;
            }
        }

        if (!$found) {
            return false;
        }

        return $this->saveAll($items);
    }

    /**
     * Menghapus satu rekaman data berdasarkan ID.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $items = $this->all();
        $initialCount = count($items);

        $filtered = array_filter($items, static function (array $item) use ($id): bool {
            return !isset($item['id']) || $item['id'] !== $id;
        });

        if (count($filtered) === $initialCount) {
            return false;
        }

        return $this->saveAll(array_values($filtered));
    }

    /**
     * Mengambil path file penyimpanan saat ini.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }
}

