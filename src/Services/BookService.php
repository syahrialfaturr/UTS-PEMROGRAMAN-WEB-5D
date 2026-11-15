<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\JsonStorage;
use App\Models\Book;
use App\Models\Category;

/**
 * Class BookService
 * Menangani business logic manajemen data buku dan kategori e-book.
 * Berinteraksi dengan JsonStorage untuk persistensi dan mengembalikan objek model (Book/Category).
 */
class BookService
{
    private JsonStorage $bookStorage;
    private JsonStorage $categoryStorage;
    private ?JsonStorage $transactionStorage;

    /**
     * @param JsonStorage|null $bookStorage
     * @param JsonStorage|null $categoryStorage
     * @param JsonStorage|null $transactionStorage
     */
    public function __construct(
        ?JsonStorage $bookStorage = null,
        ?JsonStorage $categoryStorage = null,
        ?JsonStorage $transactionStorage = null
    ) {
        $storageDir = defined('STORAGE_PATH')
            ? STORAGE_PATH
            : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage';

        $this->bookStorage = $bookStorage ?? new JsonStorage($storageDir . DIRECTORY_SEPARATOR . 'books.json');
        $this->categoryStorage = $categoryStorage ?? new JsonStorage($storageDir . DIRECTORY_SEPARATOR . 'categories.json');
        
        $transPath = $storageDir . DIRECTORY_SEPARATOR . 'transactions.json';
        $this->transactionStorage = $transactionStorage ?? (file_exists($transPath) ? new JsonStorage($transPath) : null);
    }

    // =========================================================================
    // Modul Operasi Buku (Book)
    // =========================================================================

    /**
     * Mengambil seluruh e-book yang tersimpan dan menginstansiasinya sebagai objek Book.
     *
     * @return array<int, Book>
     */
    public function getAll(): array
    {
        $records = $this->bookStorage->all();
        $books = [];

        foreach ($records as $record) {
            $books[] = Book::fromArray($record);
        }

        return $books;
    }

    /**
     * Mencari e-book berdasarkan ID unik.
     *
     * @param string $id
     * @return Book|null
     */
    public function getById(string $id): ?Book
    {
        $record = $this->bookStorage->findById($id);
        if ($record === null) {
            return null;
        }

        return Book::fromArray($record);
    }

    /**
     * Mencari e-book berdasarkan judul atau nama penulis (case-insensitive).
     *
     * @param string $keyword
     * @return array<int, Book>
     */
    public function search(string $keyword): array
    {
        $trimmed = trim($keyword);
        if ($trimmed === '') {
            return $this->getAll();
        }

        $allBooks = $this->getAll();
        $needle = mb_strtolower($trimmed);

        return array_values(array_filter($allBooks, static function (Book $book) use ($needle): bool {
            $titleMatch = mb_stripos($book->getTitle(), $needle) !== false;
            $authorMatch = mb_stripos($book->getAuthor(), $needle) !== false;
            return $titleMatch || $authorMatch;
        }));
    }

    /**
     * Menyaring e-book berdasarkan ID kategori.
     *
     * @param string $categoryId
     * @return array<int, Book>
     */
    public function filterByCategory(string $categoryId): array
    {
        $trimmed = trim($categoryId);
        if ($trimmed === '' || $trimmed === 'all') {
            return $this->getAll();
        }

        $allBooks = $this->getAll();
        return array_values(array_filter($allBooks, static function (Book $book) use ($trimmed): bool {
            return $book->getCategoryId() === $trimmed;
        }));
    }

    /**
     * Menyaring dan mencari e-book sekaligus berdasarkan kategori, kata kunci, dan status ketersediaan.
     *
     * @param string|null $categoryId
     * @param string|null $keyword
     * @param string|null $availability 'all', 'available', atau 'borrowed'
     * @return array<int, Book>
     */
    public function filterAndSearch(?string $categoryId = null, ?string $keyword = null, ?string $availability = null): array
    {
        $books = $this->getAll();

        // 1. Filter Kategori
        if ($categoryId !== null && trim($categoryId) !== '' && $categoryId !== 'all') {
            $catId = trim($categoryId);
            $books = array_filter($books, static fn(Book $b): bool => $b->getCategoryId() === $catId);
        }

        // 2. Filter Pencarian Keyword (Judul / Penulis)
        if ($keyword !== null && trim($keyword) !== '') {
            $needle = mb_strtolower(trim($keyword));
            $books = array_filter($books, static function (Book $b) use ($needle): bool {
                return mb_stripos($b->getTitle(), $needle) !== false
                    || mb_stripos($b->getAuthor(), $needle) !== false;
            });
        }

        // 3. Filter Status Ketersediaan
        if ($availability !== null && $availability !== 'all' && $availability !== '') {
            if ($availability === 'available') {
                $books = array_filter($books, static fn(Book $b): bool => $b->isAvailable() === true);
            } elseif ($availability === 'borrowed') {
                $books = array_filter($books, static fn(Book $b): bool => $b->isAvailable() === false);
            }
        }

        return array_values($books);
    }

    /**
     * Menambahkan e-book baru ke dalam katalog perpustakaan.
     *
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string, book: Book|null, errors: array<string, string>}
     */
    public function create(array $data): array
    {
        $errors = $this->validateBookInput($data);
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validasi gagal. Silakan periksa kolom yang ditandai merah.',
                'book' => null,
                'errors' => $errors,
            ];
        }

        $id = $this->generateUniqueBookId();
        $title = trim((string)$data['title']);
        $author = trim((string)$data['author']);
        $categoryId = trim((string)$data['category_id']);
        $year = (int)$data['year'];
        $description = trim((string)$data['description']);
        $cover = !empty($data['cover']) ? trim((string)$data['cover']) : 'assets/images/default-book.png';
        $available = isset($data['available']) ? (bool)$data['available'] : true;

        $newBook = new Book(
            id: $id,
            title: $title,
            author: $author,
            categoryId: $categoryId,
            year: $year,
            description: $description,
            cover: $cover,
            available: $available
        );

        $saved = $this->bookStorage->insert($newBook->toArray());
        if (!$saved) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan buku ke database JSON.',
                'book' => null,
                'errors' => ['storage' => 'Terjadi kesalahan I/O saat menyimpan berkas.'],
            ];
        }

        return [
            'success' => true,
            'message' => "E-book '{$title}' berhasil ditambahkan ke katalog.",
            'book' => $newBook,
            'errors' => [],
        ];
    }

    /**
     * Memperbarui metadata e-book yang sudah ada.
     *
     * @param string $id
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string, book: Book|null, errors: array<string, string>}
     */
    public function update(string $id, array $data): array
    {
        $existing = $this->getById($id);
        if ($existing === null) {
            return [
                'success' => false,
                'message' => "E-book dengan ID '{$id}' tidak ditemukan.",
                'book' => null,
                'errors' => ['id' => 'Buku tidak ditemukan.'],
            ];
        }

        $errors = $this->validateBookInput($data, $id);
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validasi gagal. Silakan periksa kembali data buku.',
                'book' => null,
                'errors' => $errors,
            ];
        }

        $cover = !empty($data['cover']) ? trim((string)$data['cover']) : $existing->getCover();
        
        $existing->updateDetails(
            title: (string)$data['title'],
            author: (string)$data['author'],
            categoryId: (string)$data['category_id'],
            year: (int)$data['year'],
            description: (string)$data['description'],
            cover: $cover
        );

        // Jika ada penyesuaian ketersediaan eksplisit oleh Admin
        if (isset($data['available'])) {
            $existing->setAvailable((bool)$data['available']);
        }

        $updated = $this->bookStorage->update($id, $existing->toArray());
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui berkas penyimpanan buku.',
                'book' => null,
                'errors' => ['storage' => 'Terjadi kendala penyimpanan sistem.'],
            ];
        }

        return [
            'success' => true,
            'message' => "E-book '{$existing->getTitle()}' berhasil diperbarui.",
            'book' => $existing,
            'errors' => [],
        ];
    }

    /**
     * Menghapus e-book dengan pemeriksaan keamanan data (data safety guard).
     * Mencegah penghapusan jika buku sedang dipinjam atau memiliki transaksi peminjaman aktif.
     *
     * @param string $id
     * @return array{success: bool, message: string}
     */
    public function delete(string $id): array
    {
        $book = $this->getById($id);
        if ($book === null) {
            return [
                'success' => false,
                'message' => "E-book dengan ID '{$id}' tidak ditemukan.",
            ];
        }

        // Keamanan: Cek status ketersediaan internal buku
        if (!$book->isAvailable()) {
            return [
                'success' => false,
                'message' => "E-book '{$book->getTitle()}' tidak dapat dihapus karena saat ini berstatus sedang dipinjam oleh Member.",
            ];
        }

        // Keamanan: Cek apakah ada transaksi peminjaman aktif di transactions.json
        if ($this->hasActiveTransaction($id)) {
            return [
                'success' => false,
                'message' => "E-book '{$book->getTitle()}' tidak dapat dihapus karena memiliki rekaman transaksi peminjaman aktif.",
            ];
        }

        $deleted = $this->bookStorage->delete($id);
        if (!$deleted) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus data buku dari penyimpanan.',
            ];
        }

        return [
            'success' => true,
            'message' => "E-book '{$book->getTitle()}' berhasil dihapus dari katalog.",
        ];
    }

    /**
     * Mengatur ketersediaan buku (tersedia/dipinjam).
     * Disediakan khusus untuk integrasi mulus dengan modul transaksi Farid (LoanService).
     *
     * @param string $bookId
     * @param bool $available
     * @return bool
     */
    public function setAvailability(string $bookId, bool $available): bool
    {
        $book = $this->getById($bookId);
        if ($book === null) {
            return false;
        }

        $book->setAvailable($available);
        return $this->bookStorage->update($bookId, $book->toArray());
    }

    /**
     * Menandai buku sebagai dipinjam (available = false).
     */
    public function markAsBorrowed(string $bookId): bool
    {
        return $this->setAvailability($bookId, false);
    }

    /**
     * Menandai buku sebagai dikembalikan (available = true).
     */
    public function markAsReturned(string $bookId): bool
    {
        return $this->setAvailability($bookId, true);
    }

    // =========================================================================
    // Modul Operasi Kategori (Category)
    // =========================================================================

    /**
     * Mengambil seluruh daftar kategori buku.
     *
     * @return array<int, Category>
     */
    public function getAllCategories(): array
    {
        $records = $this->categoryStorage->all();
        $categories = [];

        foreach ($records as $record) {
            $categories[] = Category::fromArray($record);
        }

        return $categories;
    }

    /**
     * Mencari kategori berdasarkan ID.
     */
    public function getCategoryById(string $id): ?Category
    {
        $record = $this->categoryStorage->findById($id);
        if ($record === null) {
            return null;
        }

        return Category::fromArray($record);
    }

    /**
     * Menghasilkan peta asosiatif [categoryId => categoryName] untuk kemudahan tampilan di view.
     *
     * @return array<string, string>
     */
    public function getCategoryMap(): array
    {
        $categories = $this->getAllCategories();
        $map = [];
        foreach ($categories as $cat) {
            $map[$cat->getId()] = $cat->getName();
        }
        return $map;
    }

    /**
     * Menambahkan kategori baru.
     *
     * @param string $name
     * @return array{success: bool, message: string, category: Category|null, error?: string}
     */
    public function createCategory(string $name): array
    {
        $trimmed = trim($name);
        if (mb_strlen($trimmed) < 2) {
            return [
                'success' => false,
                'message' => 'Nama kategori minimal harus memiliki 2 karakter.',
                'category' => null,
                'error' => 'Nama kategori terlalu pendek.',
            ];
        }

        // Cek duplikasi nama kategori
        foreach ($this->getAllCategories() as $existing) {
            if (mb_strtolower($existing->getName()) === mb_strtolower($trimmed)) {
                return [
                    'success' => false,
                    'message' => "Kategori dengan nama '{$trimmed}' sudah terdaftar.",
                    'category' => null,
                    'error' => 'Nama kategori sudah ada.',
                ];
            }
        }

        $id = $this->generateUniqueCategoryId();
        $category = new Category($id, $trimmed);

        if (!$this->categoryStorage->insert($category->toArray())) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan kategori baru.',
                'category' => null,
            ];
        }

        return [
            'success' => true,
            'message' => "Kategori '{$trimmed}' berhasil dibuat.",
            'category' => $category,
        ];
    }

    /**
     * Memperbarui nama kategori yang ada.
     *
     * @param string $id
     * @param string $newName
     * @return array{success: bool, message: string, category: Category|null}
     */
    public function updateCategory(string $id, string $newName): array
    {
        $cat = $this->getCategoryById($id);
        if ($cat === null) {
            return [
                'success' => false,
                'message' => "Kategori dengan ID '{$id}' tidak ditemukan.",
                'category' => null,
            ];
        }

        $trimmed = trim($newName);
        if (mb_strlen($trimmed) < 2) {
            return [
                'success' => false,
                'message' => 'Nama kategori minimal 2 karakter.',
                'category' => null,
            ];
        }

        $cat->rename($trimmed);
        if (!$this->categoryStorage->update($id, $cat->toArray())) {
            return [
                'success' => false,
                'message' => 'Gagal memperbarui kategori.',
                'category' => null,
            ];
        }

        return [
            'success' => true,
            'message' => "Kategori berhasil diperbarui menjadi '{$trimmed}'.",
            'category' => $cat,
        ];
    }

    /**
     * Menghapus kategori jika tidak sedang digunakan oleh e-book manapun.
     *
     * @param string $id
     * @return array{success: bool, message: string}
     */
    public function deleteCategory(string $id): array
    {
        $cat = $this->getCategoryById($id);
        if ($cat === null) {
            return [
                'success' => false,
                'message' => "Kategori dengan ID '{$id}' tidak ditemukan.",
            ];
        }

        $count = $this->getBookCountByCategory($id);
        if ($count > 0) {
            return [
                'success' => false,
                'message' => "Kategori '{$cat->getName()}' tidak dapat dihapus karena masih digunakan oleh {$count} e-book.",
            ];
        }

        if (!$this->categoryStorage->delete($id)) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus kategori dari penyimpanan.',
            ];
        }

        return [
            'success' => true,
            'message' => "Kategori '{$cat->getName()}' berhasil dihapus.",
        ];
    }

    /**
     * Menghitung jumlah buku yang terdaftar pada kategori tertentu.
     */
    public function getBookCountByCategory(string $categoryId): int
    {
        $books = $this->getAll();
        $count = 0;
        foreach ($books as $b) {
            if ($b->getCategoryId() === $categoryId) {
                $count++;
            }
        }
        return $count;
    }

    // =========================================================================
    // Helper Internal & Validasi
    // =========================================================================

    /**
     * Memvalidasi input data form buku.
     *
     * @param array<string, mixed> $data
     * @param string|null $ignoreId
     * @return array<string, string>
     */
    private function validateBookInput(array $data, ?string $ignoreId = null): array
    {
        $errors = [];

        // 1. Judul
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            $errors['title'] = 'Judul buku wajib diisi.';
        } elseif (mb_strlen($title) < 2 || mb_strlen($title) > 255) {
            $errors['title'] = 'Judul buku harus antara 2 hingga 255 karakter.';
        }

        // 2. Penulis
        $author = trim((string)($data['author'] ?? ''));
        if ($author === '') {
            $errors['author'] = 'Nama penulis / pengarang wajib diisi.';
        } elseif (mb_strlen($author) < 2 || mb_strlen($author) > 255) {
            $errors['author'] = 'Nama penulis harus antara 2 hingga 255 karakter.';
        }

        // 3. Kategori
        $categoryId = trim((string)($data['category_id'] ?? ''));
        if ($categoryId === '') {
            $errors['category_id'] = 'Kategori wajib dipilih.';
        } else {
            $cat = $this->getCategoryById($categoryId);
            if ($cat === null) {
                $errors['category_id'] = 'Kategori yang dipilih tidak valid atau tidak terdaftar.';
            }
        }

        // 4. Tahun Publikasi
        $yearRaw = $data['year'] ?? null;
        $currentYear = (int)date('Y');
        if ($yearRaw === null || $yearRaw === '') {
            $errors['year'] = 'Tahun publikasi wajib diisi.';
        } elseif (!is_numeric($yearRaw)) {
            $errors['year'] = 'Tahun publikasi harus berupa angka bulat.';
        } else {
            $year = (int)$yearRaw;
            if ($year < 1500 || $year > ($currentYear + 2)) {
                $errors['year'] = "Tahun publikasi harus masuk akal (antara 1500 - " . ($currentYear + 2) . ").";
            }
        }

        // 5. Deskripsi
        $description = trim((string)($data['description'] ?? ''));
        if ($description === '') {
            $errors['description'] = 'Deskripsi atau sinopsis buku wajib diisi.';
        } elseif (mb_strlen($description) < 5) {
            $errors['description'] = 'Deskripsi buku minimal harus memiliki 5 karakter.';
        }

        return $errors;
    }

    /**
     * Memeriksa apakah ada transaksi aktif (status = 'borrowed') untuk book_id ini.
     */
    private function hasActiveTransaction(string $bookId): bool
    {
        if ($this->transactionStorage === null) {
            return false;
        }

        $allTransactions = $this->transactionStorage->all();
        foreach ($allTransactions as $t) {
            if (isset($t['book_id']) && $t['book_id'] === $bookId) {
                if (isset($t['status']) && $t['status'] === 'borrowed') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Menghasilkan ID buku unik bertingkat (misal: BOOK-001, BOOK-002).
     */
    private function generateUniqueBookId(): string
    {
        $all = $this->bookStorage->all();
        $maxNum = 0;

        foreach ($all as $item) {
            $id = (string)($item['id'] ?? '');
            if (preg_match('/^BOOK-(\d+)$/', $id, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return sprintf('BOOK-%03d', $maxNum + 1);
    }

    /**
     * Menghasilkan ID kategori unik bertingkat (misal: CAT-001, CAT-002).
     */
    private function generateUniqueCategoryId(): string
    {
        $all = $this->categoryStorage->all();
        $maxNum = 0;

        foreach ($all as $item) {
            $id = (string)($item['id'] ?? '');
            if (preg_match('/^CAT-(\d+)$/', $id, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return sprintf('CAT-%03d', $maxNum + 1);
    }
}
