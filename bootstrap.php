<?php

declare(strict_types=1);

/**
 * Bootstrap Aplikasi E-Library
 * UTS Pemrograman Web - Semester Ganjil 2026/2027
 * Mengatur konstanta jalur, PSR-4-like autoloader untuk namespace App\,
 * dan helper global yang aman.
 */

define('ROOT_PATH', __DIR__);
define('STORAGE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'storage');
define('CONFIG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'config');

// Registrasi Autoloader sederhana tanpa Composer
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load App Config
$appConfig = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'app.php';

// Inisialisasi SessionManager secara aman
\App\Core\SessionManager::start();

/**
 * Helper untuk mencegah serangan XSS dengan htmlspecialchars.
 *
 * @param string|null $value
 * @return string
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper untuk mendapatkan base URL atau path aplikasi.
 *
 * @param string $path
 * @return string
 */
function app_url(string $path = ''): string
{
    global $appConfig;
    $base = rtrim($appConfig['base_url'] ?? '', '/');
    $path = ltrim($path, '/');
    return $base . ($path !== '' ? '/' . $path : '');
}

/**
 * Helper untuk melakukan redirect HTTP yang aman.
 *
 * @param string $url
 * @return never
 */
function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

/**
 * Helper untuk memastikan storage folder dan users.json tersedia dengan seed data awal.
 */
function ensure_initial_seed(): void
{
    $usersFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'users.json';
    if (!file_exists($usersFile) || filesize($usersFile) === 0) {
        $initialUsers = [
            [
                'id' => 'USR-001',
                'name' => 'Administrator E-Library',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 'USR-002',
                'name' => 'Syahrial Member',
                'email' => 'member@example.com',
                'password' => password_hash('member123', PASSWORD_DEFAULT),
                'role' => 'member',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $dir = dirname($usersFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($usersFile, json_encode($initialUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

// Pastikan seed data users.json tersedia
ensure_initial_seed();
