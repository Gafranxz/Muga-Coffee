<?php
// Pengaturan database. Port MySQL XAMPP Anda sebelumnya 3309; ubah ke 3306 jika memakai port bawaan.
define('DB_HOST', getenv('MUGA_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('MUGA_DB_PORT') ?: '3309');
define('DB_NAME', 'muga_kopi');
define('DB_USER', getenv('MUGA_DB_USER') ?: 'root');
define('DB_PASS', getenv('MUGA_DB_PASS') ?: '');

define('SITE_NAME', 'Muga Kopi');
define('MAPS_LAT', '-5.1630994');   // dari tautan Google Maps kafe
define('MAPS_LNG', '119.4523907');
define('MAPS_URL', 'https://maps.app.goo.gl/FYqxj51eawt9Dypi6');

// ---- Pengaturan rahasia (kunci API) dibaca dari config.local.php, yang TIDAK ikut ke GitHub ----
if (is_file(__DIR__ . '/config.local.php')) require __DIR__ . '/config.local.php';
$def = function (string $k, $v) { if (!defined($k)) define($k, getenv('MUGA_' . $k) !== false ? getenv('MUGA_' . $k) : $v); };
// Midtrans Sandbox (QRIS uji coba). Isi kunci di config.local.php.
$def('MIDTRANS_SERVER_KEY', '');
$def('MIDTRANS_CLIENT_KEY', '');
$def('MIDTRANS_SNAP_JS', 'https://app.sandbox.midtrans.com/snap/snap.js');
$def('MIDTRANS_SNAP_API', 'https://app.sandbox.midtrans.com/snap/v1/transactions');
$def('MIDTRANS_STATUS_API', 'https://api.sandbox.midtrans.com/v2');
// CS berbasis AI (Gemini gratis atau Anthropic). Isi AI_API_KEY di config.local.php.
$def('AI_API_KEY', '');
$def('AI_PROVIDER', 'gemini');   // 'gemini' (gratis) atau 'anthropic' (berbayar)
$def('AI_API_URL', AI_PROVIDER === 'gemini' ? 'https://generativelanguage.googleapis.com/v1beta/models' : 'https://api.anthropic.com/v1/messages');
$def('AI_MODEL', AI_PROVIDER === 'gemini' ? 'gemini-2.5-flash' : 'claude-haiku-5-5');
