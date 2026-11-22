<?php
function admin_head(string $title): void { ?>
<!doctype html><html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> | Kasir Muga</title>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Lilita+One&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head><body class="admin">
<?php if (is_admin()): ?>
<header class="adm-bar">
  <a class="brand" href="<?= e(url('admin/')) ?>">MUGA<span>KASIR</span></a>
  <nav>
    <a href="<?= e(url('admin/')) ?>">Pesanan</a>
    <a href="<?= e(url('admin/menu.php')) ?>">Menu</a>
    <a href="<?= e(url('admin/categories.php')) ?>">Kategori</a>
    <a href="<?= e(url('admin/tables.php')) ?>">Meja &amp; QR</a>
    <a href="<?= e(url('admin/report.php')) ?>">Laporan</a>
    <a href="<?= e(url('admin/membership.php')) ?>">Membership</a>
    <a href="<?= e(url()) ?>" target="_blank" rel="noopener">Lihat situs</a>
    <a href="<?= e(url('admin/logout.php')) ?>">Keluar</a>
  </nav>
</header>
<?php endif; ?>
<main class="adm-main">
<?php if ($m = flash()): ?><p class="notice" role="status"><?= e($m) ?></p><?php endif;
}
function admin_foot(): void { echo '</main></body></html>'; }
