<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\JsonStorage;
use App\Core\SessionManager;
use App\Models\Admin;
use App\Models\Member;
use App\Models\User;

/**
 * Class AuthService
 * Bertanggung jawab atas business logic autentikasi, registrasi pengguna,
 * validasi kredensial, verifikasi password hash, dan manajemen siklus login/logout.
 */
class AuthService
{
    private JsonStorage $storage;

    /**
     * @param JsonStorage|null $storage Jika null, menggunakan lokasi storage default users.json
     */
    public function __construct(?JsonStorage $storage = null)
    {
        if ($storage === null) {
            $defaultPath = defined('STORAGE_PATH')
                ? STORAGE_PATH . DIRECTORY_SEPARATOR . 'users.json'
                : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'users.json';
            $this->storage = new JsonStorage($defaultPath);
        } else {
            $this->storage = $storage;
        }
    }

    /**
     * Mencari pengguna berdasarkan alamat email.
     * Mengembalikan objek polimorfik bertipe User (Admin atau Member).
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        $normalizedEmail = strtolower(trim($email));
        $users = $this->storage->all();

        foreach ($users as $record) {
            if (isset($record['email']) && strtolower(trim((string)$record['email'])) === $normalizedEmail) {
                return $this->hydrateUser($record);
            }
        }

        return null;
    }

    /**
     * Mencari pengguna berdasarkan ID.
     *
     * @param string $id
     * @return User|null
     */
    public function findUserById(string $id): ?User
    {
        $record = $this->storage->findById($id);
        if ($record === null) {
            return null;
        }

        return $this->hydrateUser($record);
    }

    /**
     * Mengambil seluruh data user sebagai array of User objects.
     *
     * @return array<int, User>
     */
    public function getAllUsers(): array
    {
        $records = $this->storage->all();
        $users = [];

        foreach ($records as $record) {
            $user = $this->hydrateUser($record);
            if ($user !== null) {
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * Memproses pendaftaran akun baru.
     * Mencegah email duplikat, menghash password dengan password_hash(),
     * dan menerapkan validasi backend yang ketat.
     *
     * @param array<string, mixed> $data
     * @return array{success: bool, message: string, user?: User, errors?: array<string, string>}
     */
    public function register(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        $confirmPassword = (string)($data['confirm_password'] ?? ($data['password_confirmation'] ?? ''));
        $role = trim((string)($data['role'] ?? 'member'));

        $errors = [];

        // Validasi Nama
        if ($name === '') {
            $errors['name'] = 'Nama lengkap wajib diisi.';
        } elseif (mb_strlen($name) < 3) {
            $errors['name'] = 'Nama lengkap minimal 3 karakter.';
        }

        // Validasi Email
        if ($email === '') {
            $errors['email'] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format alamat email tidak valid.';
        } elseif ($this->findUserByEmail($email) !== null) {
            $errors['email'] = 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.';
        }

        // Validasi Password
        if ($password === '') {
            $errors['password'] = 'Password wajib diisi.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password minimal terdiri dari 6 karakter.';
        }

        // Validasi Konfirmasi Password
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Konfirmasi password tidak cocok.';
        }

        // Validasi Role (secara default registrasi publik adalah 'member')
        if (!in_array($role, ['member', 'admin'], true)) {
            $role = 'member';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validasi pendaftaran gagal. Mohon periksa kembali input Anda.',
                'errors' => $errors,
            ];
        }

        // Hashing password secara aman
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $newId = $this->generateUserId();

        $userRecord = [
            'id' => $newId,
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $saved = $this->storage->insert($userRecord);
        if (!$saved) {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan data pengguna ke berkas penyimpanan.',
            ];
        }

        $userObject = $this->hydrateUser($userRecord);

        return [
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan masuk dengan akun baru Anda.',
            'user' => $userObject,
        ];
    }

    /**
     * Memproses autentikasi pengguna dengan email dan plain-text password.
     * Jika valid, membuat sesi baru via SessionManager.
     *
     * @param string $email
     * @param string $password
     * @return array{success: bool, message: string, user?: User}
     */
    public function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));

        if ($email === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Email dan password tidak boleh kosong.',
            ];
        }

        $user = $this->findUserByEmail($email);

        // Pencegahan User Enumeration: pesan seragam jika email tak ditemukan atau password salah
        if ($user === null || !$user->verifyPassword($password)) {
            return [
                'success' => false,
                'message' => 'Email atau password yang Anda masukkan salah.',
            ];
        }

        // Simpan ke SessionManager dan regenerasi session id
        SessionManager::login($user);

        return [
            'success' => true,
            'message' => 'Login berhasil. Selamat datang kembali, ' . $user->getName() . '!',
            'user' => $user,
        ];
    }

    /**
     * Mengakhiri sesi pengguna yang aktif.
     */
    public function logout(): void
    {
        SessionManager::logout();
    }

    /**
     * Menghasilkan ID unik berurutan dengan format USR-XXX.
     */
    private function generateUserId(): string
    {
        $all = $this->storage->all();
        $maxNum = 0;

        foreach ($all as $item) {
            if (isset($item['id']) && preg_match('/^USR-(\d+)$/', (string)$item['id'], $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return sprintf('USR-%03d', $nextNum);
    }

    /**
     * Mengonversi rekaman array dari JSON menjadi instance objek Polimorfik (Admin atau Member).
     *
     * @param array<string, mixed> $record
     * @return User|null
     */
    private function hydrateUser(array $record): ?User
    {
        $id = (string)($record['id'] ?? '');
        $name = (string)($record['name'] ?? '');
        $email = (string)($record['email'] ?? '');
        $password = (string)($record['password'] ?? '');
        $role = (string)($record['role'] ?? 'member');

        if ($id === '' || $email === '' || $password === '') {
            return null;
        }

        if ($role === 'admin') {
            return new Admin($id, $name, $email, $password);
        }

        // Default role: Member
        $loanLimit = isset($record['loan_limit']) ? (int)$record['loan_limit'] : 3;
        return new Member($id, $name, $email, $password, $loanLimit);
    }
}
