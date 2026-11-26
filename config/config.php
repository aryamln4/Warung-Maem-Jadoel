<?php
declare(strict_types=1);
// Cookie sesi yang aman: tidak bisa dibaca JS, tidak dikirim lintas situs.
if (session_status() === PHP_SESSION_NONE) {
    $https = (($_ENV['APP_ENV'] ?? '') === 'production');
    session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $https,
    ]);
}
session_start();
// Header keamanan dasar untuk semua respons.
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 0');
}
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile,
    FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line),
        '#') || !str_contains($line,
        '=')) continue;
        [$key,
        $value] = array_map('trim',
        explode('=',
        $line,
        2));
        $_ENV[$key] = trim($value,
        "\"'");
    }
}
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta');
define('BASE_PATH',
dirname(__DIR__));
define('APP_URL',
rtrim($_ENV['APP_URL'] ?? 'http://localhost/warung-maem-jadoel/public',
'/'));
define('MAX_IMAGE_UPLOAD_BYTES',
6 * 1024 * 1024);
function env(string $key,
mixed $default = null): mixed {
    return $_ENV[$key] ?? $default;
}
function e(?string $value): string {
    return htmlspecialchars($value ?? '',
    ENT_QUOTES,
    'UTF-8');
}
/**
 * Ikon SVG inline (gaya outline 24x24) — satu sumber ikon untuk seluruh aplikasi.
 * Pemakaian: <?= icon('search') ?> atau <?= icon('star', ['class' => 'is-empty']) ?>
 * Ikon mewarisi warna teks (currentColor) sehingga selalu selaras dengan tema.
 */
function icon(string $name,
array $attrs = []): string {
    static $set = null;
    if ($set === null) {
        $set = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'home' => '<path d="m3 10 9-7 9 7"/><path d="M5 9v11a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9"/>',
        'chat' => '<path d="M21 12a8 8 0 0 1-8 8H4l1.5-3A8 8 0 1 1 21 12Z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/>',
        'truck' => '<path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
        'shopping-cart' => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.4 12.2a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.6L21 7H6"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'chevron-up' => '<path d="m6 15 6-6 6 6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'arrow-up-right'=> '<path d="M7 17 17 7"/><path d="M8 7h9v9"/>',
        'external-link' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M10.6 5.1A9.8 9.8 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-2.3 3.2M6.6 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 4.4-1"/><path d="m2 2 20 20"/><path d="M9.9 9.9a3 3 0 1 0 4.2 4.2"/>',
        'map-pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'utensils' => '<path d="M7 3v7a2 2 0 0 1-2 2v9"/><path d="M3 3v4a2 2 0 0 0 4 0V3"/><path d="M17 3c-1.7 0-3 1.8-3 4s1.3 4 3 4v10"/><path d="M20 3c0 2.2-1.3 4-3 4"/>',
        'shopping-bag' => '<path d="M6 7h12l1 13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1L6 7Z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/>',
        'receipt' => '<path d="M5 3h14v18l-2.5-1.5L14 21l-2-1.5L10 21l-2.5-1.5L5 21V3Z"/><path d="M9 8h6M9 12h6"/>',
        'package' => '<path d="m12 3 9 5v8l-9 5-9-5V8l9-5Z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'wallet' => '<path d="M20 7H5a2 2 0 0 1 0-4h13v4"/><path d="M20 7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M17 13h.01"/>',
        'star' => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z" fill="currentColor" stroke="none"/>',
        'check' => '<path d="m5 13 4 4L19 7"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5.5"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/>',
        'info-circle' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
        'trash' => '<path d="M4 7h16"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/><path d="M10 11v6M14 11v6"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/>',
        'rotate-ccw' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
        'qr-code' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14h1M14 20h1M18 18l3 3"/>',
        'banknote' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
        'trending-up' => '<path d="m3 17 6-6 4 4 8-8"/><path d="M14 7h7v7"/>',
        'trending-down' => '<path d="m3 7 6 6 4-4 8 8"/><path d="M21 10v7h-7"/>',
        'gift' => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"/><path d="M12 8v13"/><path d="M12 8c-4 0-5-1.5-5-3a2 2 0 0 1 4 0c0 1.5-1 3-3 3"/><path d="M12 8c4 0 5-1.5 5-3a2 2 0 0 0-4 0c0 1.5 1 3 3 3"/>',
        'store' => '<path d="M3 9 4.5 4h15L21 9"/><path d="M4 9v11a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M9 21v-6h6v6"/>',
        'message' => '<path d="M21 12a8 8 0 0 1-8 8H4l1.5-3A8 8 0 1 1 21 12Z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/>',
        'phone' => '<path d="M5 3h4l1.5 5L8 10a12 12 0 0 0 6 6l2-2.5L21 15v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        'filter' => '<path d="M3 5h18l-7 8v6l-4-2v-4L3 5Z"/>',
        'download' => '<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M4 21h16"/>',
        'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 8.2-8.2"/><path d="M16 7l2 2"/><path d="M19 4l2 2"/>',
        'shield' => '<path d="M12 3 5 6v5c0 5 3.5 8.5 7 10 3.5-1.5 7-5 7-10V6l-7-3Z"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
        'sparkles' => '<path d="M12 3v4M12 13v4M3 10h4M13 10h4"/><path d="m5.6 3.6 2 2M16.4 14.4l2 2M16.4 5.6l2-2M5.6 16.4l2 2"/>',
        'coffee' => '<path d="M4 8h13v7a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8Z"/><path d="M17 9h2a2.5 2.5 0 0 1 0 5h-2"/><path d="M7 3v2M11 3v2"/>',
        ];
    }
    if (!isset($set[$name])) return '';
    $class = 'ico' . (isset($attrs['class']) && $attrs['class'] !== '' ? ' ' . $attrs['class'] : '');
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '
    . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $set[$name] . '</svg>';
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void {
    if (!hash_equals($_SESSION['_csrf'] ?? '',
    $_POST['_csrf'] ?? '')) {
        http_response_code(419);
        exit('CSRF token tidak valid.');
    }
}
function ini_bytes(string $value): int {
    $value = trim($value);
    if ($value === '') return 0;
    $last = strtolower(substr($value,
    -1));
    $num = (float)$value;
    return (int)match($last) {
        'g' => $num * 1024 * 1024 * 1024,
        'm' => $num * 1024 * 1024,
        'k' => $num * 1024,
        default => $num,
    };
}
function uploaded_image_mime(array $file): ?string {
    $tmp = $file['tmp_name'] ?? '';
    if ($tmp === '' || !is_file($tmp)) return null;
    if (function_exists('finfo_open')) {
        $f = finfo_open(FILEINFO_MIME_TYPE);
        if ($f) {
            $mime = finfo_file($f,
            $tmp) ?: null;
            finfo_close($f);
            if ($mime) return $mime;
        }
    }
    if (function_exists('getimagesize')) {
        $info = @getimagesize($tmp);
        if (is_array($info) && !empty($info['mime'])) return $info['mime'];
    }
    return null;
}
function upload_error_message(int $error): string {
    return match($error) {
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE => 'Ukuran foto melebihi batas upload server. Coba gunakan foto yang lebih kecil.',
        UPLOAD_ERR_PARTIAL => 'Upload foto tidak selesai. Silakan coba lagi.',
        UPLOAD_ERR_NO_FILE => 'Foto belum dipilih.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload PHP tidak tersedia di server.',
        UPLOAD_ERR_CANT_WRITE => 'Server tidak dapat menulis file upload. Periksa permission folder uploads.',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP di server.',
        default => 'Foto gagal diunggah. Silakan coba lagi.',
    };
}
function flash(string $key,
?string $value = null): ?string {
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}
/**
 * Cache-busting: versi aset berdasarkan waktu modifikasi file.
 * Memaksa browser mengambil CSS/JS terbaru setiap kali file berubah,
 * sehingga tidak ada lagi HTML baru yang dicampur JS/CSS lama (cache lama).
 */
function asset_version(string $relativePath): string {
    static $cache = [];
    if (!isset($cache[$relativePath])) {
        $file = BASE_PATH . '/public/' . ltrim($relativePath,
        '/');
        $cache[$relativePath] = is_file($file) ? substr(md5_file($file),
        0,
        10) : '1';
    }
    return $cache[$relativePath];
}
function rupiah(float|int|string|null $value): string {
    return 'Rp '.number_format((float)$value,
    0,
    ',',
    '.');
}
function order_status_label(string $status): string {
    return [
    'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
    'MENUNGGU_KONFIRMASI' => 'Menunggu Konfirmasi',
    'DIPROSES' => 'Diproses',
    'DIKEMAS' => 'Dikemas',
    'SEDANG_DIANTAR' => 'Dikirim',
    'SIAP_DIAMBIL' => 'Siap Diambil',
    'SELESAI' => 'Selesai',
    'DIBATALKAN' => 'Dibatalkan',
    ][$status] ?? ucwords(strtolower(str_replace('_',
    ' ',
    $status)));
}
function order_type_label(string $type): string {
    return ['DINE_IN' => 'Dine-in',
    'TAKEAWAY' => 'Takeaway',
    'DELIVERY' => 'Delivery'][$type] ?? $type;
}
/**
 * Langkah tracker pesanan. state: done | current | pending.
 * @return array<int,array{label:string,state:string}>
 */
function order_progress(string $status,
string $type): array {
    if ($status === 'DIBATALKAN') {
        return [['label' => 'Dibatalkan',
        'state' => 'current']];
    }
    if ($type === 'DELIVERY') {
        $steps = [
        ['Diterima',
        ['MENUNGGU_PEMBAYARAN',
        'MENUNGGU_KONFIRMASI']],
        ['Diproses',
        ['DIPROSES']],
        ['Dikemas',
        ['DIKEMAS']],
        ['Dikirim',
        ['SEDANG_DIANTAR']],
        ['Tiba',
        ['SELESAI']],
        ];
    }  elseif ($type === 'TAKEAWAY') {
        $steps = [
        ['Diterima',
        ['MENUNGGU_PEMBAYARAN',
        'MENUNGGU_KONFIRMASI']],
        ['Diproses',
        ['DIPROSES']],
        ['Siap Diambil',
        ['SIAP_DIAMBIL']],
        ['Selesai',
        ['SELESAI']],
        ];
    }  else {
        $steps = [
        ['Diterima',
        ['MENUNGGU_PEMBAYARAN',
        'MENUNGGU_KONFIRMASI']],
        ['Diproses',
        ['DIPROSES']],
        ['Disajikan',
        ['SEDANG_DIANTAR']],
        ['Selesai',
        ['SELESAI']],
        ];
    }
    $currentIndex = 0;
    foreach ($steps as $i => $step) {
        if (in_array($status,
        $step[1],
        true)) {
            $currentIndex = $i;
            break;
        }
    }
    $out = [];
    foreach ($steps as $i => $step) {
        $out[] = ['label' => $step[0],
        'state' => $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'current' : 'pending')];
    }
    return $out;
}
function time_ago(?string $datetime): string {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'baru saja';
    if ($diff < 3600) return floor($diff / 60).' menit lalu';
    if ($diff < 86400) return floor($diff / 3600).' jam lalu';
    if ($diff < 604800) return floor($diff / 86400).' hari lalu';
    return date('d M Y',
    $ts);
}
