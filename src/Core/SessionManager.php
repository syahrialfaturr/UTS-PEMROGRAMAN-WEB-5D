<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Class SessionManager
 * Mengelola siklus hidup sesi PHP, regenerasi session ID, autentikasi state,
 * pesan flash, serta pemeriksaan hak akses (Access Control List / Gatekeeper).
 */
class SessionManager
{
    private const SESSION_USER_KEY = 'auth_user';
    private const SESSION_FLASH_KEY = 'flash_messages';
    private const SESSION_OLD_INPUT_KEY = 'old_inputs';

    /**
     * Memulai sesi PHP dengan opsi penyimpanan dan cookie yang aman bila belum dimulai.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Arahkan session save path ke direktori lokal storage/sessions agar independen dari konfigurasi OS
            $sessionDir = defined('STORAGE_PATH')
                ? STORAGE_PATH . DIRECTORY_SEPARATOR . 'sessions'
                : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';

            if (!is_dir($sessionDir)) {
                @mkdir($sessionDir, 0777, true);
            }
            if (is_dir($sessionDir)) {
                @session_save_path($sessionDir);
            }

            // Konfigurasi aman untuk CLI dan Web Server
            if (php_sapi_name() === 'cli') {
                @ini_set('session.use_cookies', '0');
                @session_cache_limiter('');
            } else {
                @ini_set('session.cookie_httponly', '1');
                @ini_set('session.use_only_cookies', '1');
            }

            @session_start();
        }
    }

    /**
     * Mendaftarkan data pengguna ke dalam sesi setelah proses autentikasi berhasil.
     * Meregenerasi Session ID untuk mencegah serangan Session Fixation.
     * Hanya menyimpan data minimum (id, name, email, role), TIDAK menyimpan password/hash.
     *
     * @param User $user
     */
    public static function login(User $user): void
    {
        self::start();

        // Wajib meregenerasi session ID demi keamanan (Session Fixation Prevention)
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!headers_sent()) {
                @session_regenerate_id(true);
            } else {
                // Di lingkungan CLI saat output header sudah terkirim ke stdout
                $backup = $_SESSION;
                $newId = bin2hex(random_bytes(13));
                @session_write_close();
                session_id($newId);
                @session_start();
                $_SESSION = $backup;
            }
        }

        $_SESSION[self::SESSION_USER_KEY] = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'logged_at' => time(),
        ];
    }

    /**
     * Mengakhiri sesi pengguna secara aman dan menghapus cookie sesi.
     */
    public static function logout(): void
    {
        self::start();

        $_SESSION = [];

        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    /**
     * Memeriksa apakah terdapat pengguna yang sedang login.
     */
    public static function isLoggedIn(): bool
    {
        self::start();
        return isset($_SESSION[self::SESSION_USER_KEY]) && is_array($_SESSION[self::SESSION_USER_KEY]);
    }

    /**
     * Mengambil data pengguna aktif dari session.
     *
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        self::start();
        return $_SESSION[self::SESSION_USER_KEY] ?? null;
    }

    /**
     * Mengambil ID pengguna aktif.
     */
    public static function userId(): ?string
    {
        $u = self::user();
        return $u['id'] ?? null;
    }

    /**
     * Mengambil Role pengguna aktif.
     */
    public static function userRole(): ?string
    {
        $u = self::user();
        return $u['role'] ?? null;
    }

    /**
     * Melindungi halaman privat: memastikan pengunjung sudah login.
     * Jika belum, alihkan ke halaman login.
     *
     * @param string $redirectTo
     * @return void
     */
    public static function requireLogin(string $redirectTo = 'login.php'): void
    {
        self::start();
        if (!self::isLoggedIn()) {
            self::setFlash('error', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
            if (!headers_sent()) {
                header("Location: {$redirectTo}");
            }
            exit;
        }
    }

    /**
     * Melindungi halaman berdasarkan role tertentu (misal: 'admin' atau 'member').
     * Jika bukan role yang diizinkan, tolak akses dan alihkan.
     *
     * @param string|array<int, string> $roles
     * @param string $forbiddenRedirect
     * @return void
     */
    public static function requireRole(string|array $roles, string $forbiddenRedirect = 'index.php'): void
    {
        self::start();
        if (!self::isLoggedIn()) {
            self::requireLogin();
        }

        $allowedRoles = is_array($roles) ? $roles : [$roles];
        $currentRole = self::userRole();

        if ($currentRole === null || !in_array($currentRole, $allowedRoles, true)) {
            self::setFlash('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk membuka halaman ini.');
            if (!headers_sent()) {
                header("Location: {$forbiddenRedirect}");
            }
            exit;
        }
    }

    /**
     * Menetapkan pesan flash (hanya berlaku untuk 1 siklus request berikutnya).
     */
    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION[self::SESSION_FLASH_KEY][$type] = $message;
    }

    /**
     * Memeriksa apakah pesan flash dengan tipe tertentu ada.
     */
    public static function hasFlash(string $type): bool
    {
        self::start();
        return isset($_SESSION[self::SESSION_FLASH_KEY][$type]);
    }

    /**
     * Mengambil pesan flash dan langsung menghapusnya dari session.
     */
    public static function getFlash(string $type): ?string
    {
        self::start();
        if (isset($_SESSION[self::SESSION_FLASH_KEY][$type])) {
            $msg = $_SESSION[self::SESSION_FLASH_KEY][$type];
            unset($_SESSION[self::SESSION_FLASH_KEY][$type]);
            return $msg;
        }
        return null;
    }

    /**
     * Menyimpan nilai input form sebelumnya untuk diisikan kembali saat validasi gagal.
     *
     * @param array<string, mixed> $input
     */
    public static function setOldInput(array $input): void
    {
        self::start();
        // Hapus password agar tidak pernah tersimpan di old input
        unset($input['password'], $input['confirm_password'], $input['password_confirmation']);
        $_SESSION[self::SESSION_OLD_INPUT_KEY] = $input;
    }

    /**
     * Mengambil nilai input lama dan mengosongkannya.
     */
    public static function getOldInput(string $key, string $default = ''): string
    {
        self::start();
        $val = $_SESSION[self::SESSION_OLD_INPUT_KEY][$key] ?? $default;
        return is_string($val) ? $val : $default;
    }

    /**
     * Membersihkan seluruh data old input.
     */
    public static function clearOldInput(): void
    {
        self::start();
        unset($_SESSION[self::SESSION_OLD_INPUT_KEY]);
    }
}
