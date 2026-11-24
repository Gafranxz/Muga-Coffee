<?php
// Kerangka halaman publik: <head> lengkap dengan SEO (title, description, canonical, Open Graph).
function page_head(string $title, string $desc, array $o = []): void {
    $canon = abs_url($o['path'] ?? '');
    $robots = $o['robots'] ?? 'index,follow';
    $og = abs_url('assets/img/signature.webp');
    ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="robots" content="<?= e($robots) ?>">
<meta name="theme-color" content="#5a2314">
<link rel="canonical" href="<?= e($canon) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta property="og:image" content="<?= e($og) ?>">
<meta property="og:locale" content="id_ID">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Lilita+One&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
<?php if (!empty($o['jsonld'])): ?>
<script type="application/ld+json"><?= json_encode($o['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>
</head>
<body class="<?= e($o['body'] ?? '') ?>">
<?php }

function site_header(int $cart = 0): void { ?>
<header class="site-header">
  <a class="brand" href="<?= e(url()) ?>" aria-label="Muga Kopi, beranda">MUGA<span>KOPI</span></a>
  <nav aria-label="Navigasi utama">
    <a href="<?= e(url('#menu')) ?>">Menu</a>
    <a href="<?= e(url('#cara-pesan')) ?>">Cara pesan</a>
    <a href="<?= e(url('#suasana')) ?>">Suasana</a>
    <a href="<?= e(url('#lokasi')) ?>">Lokasi</a>
    <a href="<?= e(url('membership.php')) ?>">Membership</a>
  </nav>
  <button type="button" class="btn btn-cream cart-open" data-cart-open aria-label="Buka pesanan">
    Pesanan <b class="cart-count" data-cart-count>0</b>
  </button>
</header>
<?php }

function site_footer(): void { ?>
<footer class="site-footer">
  <p class="foot-brand">MUGA<span>KOPI</span></p>
  <p>Coffee is always better when shared with the people you love.</p>
  <p class="foot-small">&copy; <?= date('Y') ?> Muga Kopi &middot; <a href="<?= e(url('admin/login.php')) ?>">Masuk kasir</a></p>
</footer>
<?php }

function cs_widget(): void { ?>
<button type="button" class="cs-fab" data-cs-open aria-label="Tanya asisten Muga">Tanya Muga</button>
<section class="cs" data-cs hidden aria-label="Asisten Muga (AI)" data-endpoint="<?= e(url('cs.php')) ?>" data-csrf="<?= e(csrf()) ?>">
  <header><div><b>Asisten Muga</b><small>Dijawab oleh AI, bukan manusia</small></div><button type="button" class="icon-btn" data-cs-close aria-label="Tutup">&times;</button></header>
  <div class="cs-log" data-cs-log aria-live="polite">
    <p class="cs-bot">Halo! Saya asisten AI Muga Kopi. Tanya apa saja soal menu, misalnya "menu apa yang paling laris?" atau "ada yang tidak pedas dan manis?".</p>
  </div>
  <div class="cs-quick"><button type="button">Menu paling laris?</button><button type="button">Rekomendasi minuman manis</button><button type="button">Ada yang pedas?</button></div>
  <form class="cs-form" data-cs-form><input name="q" maxlength="300" required autocomplete="off" placeholder="Tulis pertanyaanmu..." aria-label="Pertanyaan"><button class="btn btn-primary">Kirim</button></form>
</section>
<?php }
