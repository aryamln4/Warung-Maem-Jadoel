<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = trim((string) env('DB_HOST', ''));
        $name = trim((string) env('DB_NAME', ''));
        $user = trim((string) env('DB_USER', ''));
        $pass = (string) env('DB_PASS', '');
        $port = (int) env('DB_PORT', 3306);
        if ($port <= 0 || $port > 65535) {
            $port = 3306;
        }

        if ($host === '' || $name === '' || $user === '') {
            throw new RuntimeException(
                'Konfigurasi database belum lengkap. Buat file .env dari .env.example lalu isi DB_HOST, DB_NAME, DB_USER, dan DB_PASS dari panel hosting.'
            );
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $name
        );

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_PERSISTENT => false,
                // Jangan biarkan halaman menggantung lama saat host database salah.
                PDO::ATTR_TIMEOUT => 8,
            ]);
        } catch (PDOException $e) {
            error_log('[Database] Koneksi gagal ke ' . $host . ': ' . $e->getMessage());
            throw new RuntimeException(
                'Koneksi ke database gagal. Periksa DB_HOST, DB_PORT, DB_NAME, DB_USER, dan DB_PASS pada file .env. Pastikan database sudah dibuat dan server database aktif.',
                0,
                $e
            );
        }

        return self::$pdo;
    }
}
