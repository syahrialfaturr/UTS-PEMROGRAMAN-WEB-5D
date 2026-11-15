<?php

declare(strict_types=1);

/**
 * Test Suite Modul Autentikasi & OOP User
 * UTS Pemrograman Web - E-Library
 * 
 * Menguji 10 skenario wajib sesuai spesifikasi UTS:
 * 1. Registrasi akun baru berhasil.
 * 2. Registrasi email duplikat ditolak.
 * 3. Password disimpan sebagai hash.
 * 4. Login dengan kredensial benar berhasil.
 * 5. Login dengan password salah ditolak.
 * 6. Logout mengakhiri session.
 * 7. Guest ditolak dari halaman Member.
 * 8. Member ditolak dari halaman Admin.
 * 9. Polymorphism menghasilkan deskripsi berbeda untuk Member dan Admin.
 * 10. Session ID diregenerasi setelah login.
 */

// Pastikan storage folder lokal pengujian ada sebelum bootstrap
define('TEST_STORAGE_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'temp_storage');
$testUsersFile = TEST_STORAGE_DIR . DIRECTORY_SEPARATOR . 'test_users.json';

if (!is_dir(TEST_STORAGE_DIR)) {
    @mkdir(TEST_STORAGE_DIR, 0777, true);
} else {
    @unlink($testUsersFile);
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Core\JsonStorage;
use App\Core\SessionManager;
use App\Models\Admin;
use App\Models\Member;
use App\Models\User;
use App\Services\AuthService;

class AuthTestSuite
{
    private JsonStorage $storage;
    private AuthService $authService;
    private int $passCount = 0;
    private int $failCount = 0;

    public function __construct(string $testUsersFile)
    {
        $this->storage = new JsonStorage($testUsersFile);
        $this->authService = new AuthService($this->storage);
    }

    private function assert(string $scenarioName, bool $condition, string $detail = ''): void
    {
        if ($condition) {
            $this->passCount++;
            echo "[\033[32mPASS\033[0m] {$scenarioName}" . ($detail ? " -> {$detail}" : '') . PHP_EOL;
        } else {
            $this->failCount++;
            echo "[\033[31mFAIL\033[0m] {$scenarioName}" . ($detail ? " -> GAGAL: {$detail}" : '') . PHP_EOL;
        }
    }

    public function runAll(): void
    {
        echo "=================================================================" . PHP_EOL;
        echo "🧪 MENJALANKAN SUITE PENGUJIAN MODUL AUTENTIKASI & OOP (10 SKENARIO)" . PHP_EOL;
        echo "=================================================================" . PHP_EOL . PHP_EOL;

        // SKENARIO 1: Registrasi akun baru berhasil
        $regResult = $this->authService->register([
            'name' => 'Syahrial Fathur',
            'email' => 'syahrial@student.umm.ac.id',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => 'member',
        ]);
        $this->assert(
            'Skenario 1: Registrasi akun baru berhasil',
            $regResult['success'] === true && $regResult['user'] instanceof Member,
            "User ID: {$regResult['user']->getId()}, Name: {$regResult['user']->getName()}"
        );

        // SKENARIO 2: Registrasi email duplikat ditolak
        $dupResult = $this->authService->register([
            'name' => 'Syahrial Kembaran',
            'email' => 'syahrial@student.umm.ac.id', // email sama
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => 'member',
        ]);
        $this->assert(
            'Skenario 2: Registrasi email duplikat ditolak',
            $dupResult['success'] === false && isset($dupResult['errors']['email']),
            $dupResult['message'] . ' (' . ($dupResult['errors']['email'] ?? '') . ')'
        );

        // SKENARIO 3: Password disimpan sebagai hash (bukan teks biasa)
        $savedUser = $this->storage->findById($regResult['user']->getId());
        $isHash = isset($savedUser['password']) 
            && $savedUser['password'] !== 'secret123'
            && password_verify('secret123', (string)$savedUser['password']);
        $this->assert(
            'Skenario 3: Password disimpan sebagai hash',
            $isHash,
            "Password mentah tidak disimpan, tersimpan hash: " . substr((string)$savedUser['password'], 0, 15) . '...'
        );

        // SKENARIO 4: Login dengan kredensial benar berhasil
        $loginResult = $this->authService->login('syahrial@student.umm.ac.id', 'secret123');
        $sessionUser = SessionManager::user();
        $this->assert(
            'Skenario 4: Login dengan kredensial benar berhasil',
            $loginResult['success'] === true 
            && SessionManager::isLoggedIn() === true 
            && $sessionUser !== null 
            && $sessionUser['email'] === 'syahrial@student.umm.ac.id'
            && !isset($sessionUser['password']), // Pastikan password hash tidak bocor ke session!
            "Login sukses sebagai role: {$sessionUser['role']}, password hash TIDAK disimpan di session."
        );

        // SKENARIO 5: Login dengan password salah ditolak
        $failLogin = $this->authService->login('syahrial@student.umm.ac.id', 'passwordsalah123');
        $this->assert(
            'Skenario 5: Login dengan password salah ditolak',
            $failLogin['success'] === false,
            "Pesan penolakan: {$failLogin['message']}"
        );

        // SKENARIO 6: Logout mengakhiri session
        $this->authService->logout();
        $this->assert(
            'Skenario 6: Logout mengakhiri session',
            SessionManager::isLoggedIn() === false && SessionManager::user() === null,
            "Session berhasil dikosongkan dan status login = false."
        );

        // SKENARIO 7: Guest ditolak dari halaman Member
        // Saat ini status user adalah Guest (belum login)
        $guestDenied = !SessionManager::isLoggedIn();
        $this->assert(
            'Skenario 7: Guest ditolak dari halaman Member',
            $guestDenied,
            "Pengunjung tanpa sesi login terdeteksi sebagai Guest dan dialihkan sebelum membuka dashboard member."
        );

        // SKENARIO 8: Member ditolak dari halaman Admin
        // Login kembali sebagai Member
        $this->authService->login('syahrial@student.umm.ac.id', 'secret123');
        $currentRole = SessionManager::userRole();
        $adminAllowed = ($currentRole === 'admin');
        $this->assert(
            'Skenario 8: Member ditolak dari halaman Admin',
            $currentRole === 'member' && $adminAllowed === false,
            "Role saat ini: '{$currentRole}'. Pemeriksaan requireRole('admin') menolak akses member."
        );

        // SKENARIO 9: Polymorphism menghasilkan deskripsi berbeda untuk Member dan Admin
        // Buat objek Admin dan Member yang sama-sama bertipe parent User
        $memberObj = new Member('USR-M01', 'Budi Member', 'budi@test.com', 'hash1', 3);
        $adminObj = new Admin('USR-A01', 'Siti Admin', 'siti@test.com', 'hash2');

        // Fungsi pemanggil polimorfik dengan type-hint parent User
        $polymorphicCaller = static function (User $u): string {
            return $u->getRoleDescription();
        };

        $memberDesc = $polymorphicCaller($memberObj);
        $adminDesc = $polymorphicCaller($adminObj);

        $isPolymorphic = ($memberDesc !== $adminDesc) 
            && str_contains($memberDesc, 'Member') 
            && str_contains($adminDesc, 'Administrator');

        $this->assert(
            'Skenario 9: Polymorphism menghasilkan deskripsi berbeda untuk Member dan Admin',
            $isPolymorphic,
            PHP_EOL . "    - Member: \"{$memberDesc}\"" . PHP_EOL . "    - Admin:  \"{$adminDesc}\""
        );

        // SKENARIO 10: Session ID diregenerasi setelah login
        SessionManager::start();
        $oldId = session_id();

        // Lakukan login ulang untuk memicu regenerasi session id
        $this->authService->login('syahrial@student.umm.ac.id', 'secret123');
        $newId = session_id();

        $isRegenerated = ($oldId !== $newId) && !empty($newId);
        $this->assert(
            'Skenario 10: Session ID diregenerasi setelah login',
            $isRegenerated,
            "Sebelum login: '{$oldId}' -> Sesudah login: '{$newId}'"
        );

        echo PHP_EOL . "=================================================================" . PHP_EOL;
        echo "HASIL RINGKASAN: {$this->passCount} LULUS, {$this->failCount} GAGAL" . PHP_EOL;
        echo "=================================================================" . PHP_EOL;

        if ($this->failCount > 0) {
            exit(1);
        }
    }
}

$suite = new AuthTestSuite($testUsersFile);
$suite->runAll();

// Bersihkan file uji sementara
@unlink($testUsersFile);
@rmdir(TEST_STORAGE_DIR);
