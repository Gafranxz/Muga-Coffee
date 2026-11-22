<?php
require __DIR__ . '/config.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
    }
    return $pdo;
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah(int $n): string { return 'Rp' . number_format($n, 0, ',', '.'); }

// Folder proyek di URL (mis. /muga-cafe), dihitung otomatis supaya jalan di subfolder htdocs.
function base_path(): string {
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $dir = preg_replace('#/admin$#', '', $dir);
    return rtrim($dir, '/');
}
function url(string $p = ''): string { return base_path() . '/' . ltrim($p, '/'); }
function abs_url(string $p = ''): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    return ($https ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . url($p);
}
function redirect(string $to): never { header('Location: ' . $to); exit; }

function csrf(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
    }
}
function flash(?string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m;
}
function is_admin(): bool { return !empty($_SESSION['admin_id']); }
function require_admin(): void { if (!is_admin()) redirect(url('admin/login.php')); }

function slugify(string $s): string {
    $s = strtolower(str_replace('&', 'dan', $s));
    return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-') ?: 'menu';
}
function img_src(string $file): string {
    if ($file === '') return '';
    return url((is_file(__DIR__ . '/../uploads/' . $file) ? 'uploads/' : 'assets/img/') . $file);
}
