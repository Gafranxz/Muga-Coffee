<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/payments.php';

// QR meja: ?meja=12&t=TOKEN -> disimpan di sesi supaya nomor meja tetap terkunci selama menu dibuka.
if (isset($_GET['meja'], $_GET['t'])) {
    $t = db()->prepare('SELECT table_no, token FROM cafe_tables WHERE table_no=? AND token=? AND is_active=1');
    $t->execute([(string)$_GET['meja'], (string)$_GET['t']]);
    if ($row = $t->fetch()) $_SESSION['table'] = $row;
}
$table = $_SESSION['table'] ?? null;
$flashMsg = flash();

$cats = db()->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();
$items = [];
foreach (db()->query('SELECT * FROM menu_items ORDER BY category_id, sort_order, id') as $r) $items[$r['category_id']][] = $r;

// Data terstruktur untuk mesin pencari (Google dapat menampilkan kafe + menu).
$sections = [];
foreach ($cats as $c) {
    $mi = [];
    foreach ($items[$c['id']] ?? [] as $it) {
        $mi[] = ['@type' => 'MenuItem', 'name' => $it['name'],
                 'offers' => ['@type' => 'Offer', 'price' => (int)$it['price'], 'priceCurrency' => 'IDR']];
    }
    $sections[] = ['@type' => 'MenuSection', 'name' => $c['name'], 'hasMenuItem' => $mi];
}
$jsonld = [
    '@context' => 'https://schema.org', '@type' => 'CafeOrCoffeeShop',
    'name' => SITE_NAME, 'url' => abs_url(), 'image' => abs_url('assets/img/signature.webp'),
    'servesCuisine' => ['Kopi', 'Makanan ringan', 'Indonesia'], 'priceRange' => 'Rp5.000 - Rp35.000',
    'hasMap' => MAPS_URL,
    'geo' => ['@type' => 'GeoCoordinates', 'latitude' => MAPS_LAT, 'longitude' => MAPS_LNG],
    'hasMenu' => ['@type' => 'Menu', 'name' => 'Menu Muga Kopi', 'hasMenuSection' => $sections],
];

page_head('Muga Kopi Makassar | Menu Kopi, Matcha & Makanan, Pesan dari Meja',
  'Menu lengkap Muga Kopi: kopi susu, matcha, mont blanc, rice bowl, snack, dan cheesecake mulai Rp25.000. Pesan langsung dari mejamu, pesanan diantar ke tempat duduk.',
  ['jsonld' => $jsonld]);
site_header();
?>
<main>
<?php if ($table): ?><p class="table-banner">Kamu duduk di <b>Meja <?= e($table['table_no']) ?></b>. Pesananmu akan diantar ke sini.</p><?php endif; ?>
<?php if ($flashMsg): ?><p class="table-banner warn" role="alert"><?= e($flashMsg) ?></p><?php endif; ?>
<section class="hero" aria-labelledby="h1">
  <div class="hero-copy">
    <p class="kicker">Muga Kopi &middot; Makassar</p>
    <h1 id="h1">Kopi lebih enak kalau dinikmati <em>bareng</em>.</h1>
    <p class="lead">Sudah dapat kursi? Pilih menu dari mejamu, kami yang antar. Tidak perlu bolak-balik ke kasir.</p>
    <div class="hero-cta">
      <a class="btn btn-cream" href="#menu">Lihat menu</a>
      <button type="button" class="btn btn-ghost" data-cart-open>Pesan dari meja</button>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true">
    <img class="cup c1" src="<?= e(url('assets/img/signature.webp')) ?>" alt="" width="248" height="313">
    <img class="cup c2" src="<?= e(url('assets/img/montblanc.webp')) ?>" alt="" width="262" height="359">
    <img class="cup c3" src="<?= e(url('assets/img/matcha.webp')) ?>" alt="" width="180" height="245">
  </div>
</section>

<section class="steps" id="cara-pesan" aria-labelledby="h-cara">
  <h2 id="h-cara" class="sec-title">Tiga langkah, tanpa antre</h2>
  <ol>
    <li><b>01</b><span>Duduk dan catat nomor mejamu.</span></li>
    <li><b>02</b><span>Pilih menu di sini, isi nama dan nomor meja.</span></li>
    <li><b>03</b><span>Kasir menerima pesananmu, lalu minuman dan makanan diantar ke meja.</span></li>
  </ol>
</section>

<section class="menu" id="menu" aria-labelledby="h-menu">
  <h2 id="h-menu" class="sec-title">Menu Muga</h2>
  <nav class="cat-nav" aria-label="Kategori menu">
    <?php foreach ($cats as $c): ?><a href="#<?= e($c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
  </nav>

  <?php foreach ($cats as $c): $list = $items[$c['id']] ?? []; if (!$list) continue; ?>
  <section class="cat <?= $c['slug'] === 'cheesecake' ? 'cat-photos' : '' ?>" id="<?= e($c['slug']) ?>" aria-labelledby="h-<?= e($c['slug']) ?>">
    <div class="cat-head">
      <h3 id="h-<?= e($c['slug']) ?>"><?= e($c['name']) ?></h3>
      <?php if ($c['image'] && $c['slug'] !== 'cheesecake'): ?>
        <img src="<?= e(img_src($c['image'])) ?>" alt="<?= e($c['name']) ?> Muga Kopi" loading="lazy" width="200" height="240">
      <?php endif; ?>
    </div>
    <?php if ($c['slug'] === 'cheesecake'): ?>
    <ul class="photo-grid">
      <?php foreach ($list as $it): ?>
      <li class="photo-card <?= $it['is_available'] ? '' : 'sold-out' ?>" id="<?= e($it['slug']) ?>">
        <img src="<?= e(img_src($it['image'])) ?>" alt="<?= e($it['name']) ?>" loading="lazy" width="280" height="280">
        <span class="mi-name"><?= e($it['name']) ?></span>
        <span class="mi-price"><?= (int)($it['price'] / 1000) ?>K</span>
        <?php if ($it['is_available']): ?>
          <button type="button" class="add" data-add data-id="<?= (int)$it['id'] ?>" data-name="<?= e($it['name']) ?>" data-price="<?= (int)$it['price'] ?>" aria-label="Tambah <?= e($it['name']) ?> ke pesanan">+</button>
        <?php else: ?><span class="habis">Habis</span><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <ul class="menu-list">
      <?php foreach ($list as $it): ?>
      <li class="mi <?= $it['is_available'] ? '' : 'sold-out' ?>" id="<?= e($it['slug']) ?>">
        <span class="mi-name"><?= e($it['name']) ?><?php if ($it['is_spicy']): ?> <i class="chili">Pedas</i><?php endif; ?></span>
        <span class="mi-dots" aria-hidden="true"></span>
        <span class="mi-price"><?= (int)($it['price'] / 1000) ?>K</span>
        <?php if ($it['is_available']): ?>
          <button type="button" class="add" data-add data-id="<?= (int)$it['id'] ?>" data-name="<?= e($it['name']) ?>" data-price="<?= (int)$it['price'] ?>" aria-label="Tambah <?= e($it['name']) ?> ke pesanan">+</button>
        <?php else: ?><span class="habis">Habis</span><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>
  <?php endforeach; ?>
</section>

<section class="mood" id="suasana" aria-labelledby="h-suasana">
  <div class="mood-copy">
    <h2 id="h-suasana" class="sec-title">Tempat duduk lama, ngobrol panjang</h2>
    <p>Rak kayu dengan lampu hangat, meja kayu, dan kursi rotan. Muga dibuat untuk berlama-lama bersama teman, keluarga, atau laptop yang belum selesai.</p>
  </div>
  <div class="shelf" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
</section>

<section class="where" id="lokasi" aria-labelledby="h-lokasi">
  <h2 id="h-lokasi" class="sec-title">Temukan Muga</h2>
  <div class="map-wrap">
    <iframe title="Peta lokasi Muga Kopi" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
      src="https://www.google.com/maps?q=<?= e(MAPS_LAT) ?>,<?= e(MAPS_LNG) ?>&amp;z=17&amp;output=embed"></iframe>
  </div>
  <a class="btn btn-cream" href="<?= e(MAPS_URL) ?>" target="_blank" rel="noopener">Buka di Google Maps</a>
</section>
</main>

<div class="cart-bar" data-cart-bar hidden>
  <button type="button" class="btn btn-cream" data-cart-open><span data-cart-count2>0</span> item &middot; <b data-cart-total2>Rp0</b> &nbsp; Lihat pesanan</button>
</div>

<div class="overlay" data-overlay hidden></div>
<aside class="cart" data-cart role="dialog" aria-modal="true" aria-labelledby="cart-title" hidden>
  <div class="cart-head">
    <h2 id="cart-title">Pesananmu</h2>
    <button type="button" class="icon-btn" data-cart-close aria-label="Tutup">&times;</button>
  </div>
  <ul class="cart-items" data-cart-items></ul>
  <p class="cart-empty" data-cart-empty>Belum ada pesanan. Tekan tombol + pada menu.</p>
  <form class="cart-form" method="post" action="<?= e(url('order.php')) ?>" data-order-form>
    <?= csrf_field() ?>
    <input type="hidden" name="items" data-items-json>
    <label>Nama<input name="customer_name" maxlength="60" required autocomplete="name" placeholder="Nama kamu"></label>
    <?php if ($table): ?>
      <input type="hidden" name="table_token" value="<?= e($table['token']) ?>">
      <p class="table-chip">Meja <b><?= e($table['table_no']) ?></b> (dari QR)</p>
    <?php else: ?>
      <label>Nomor meja<input name="table_no" maxlength="10" required inputmode="numeric" placeholder="mis. 12"></label>
      <p class="foot-small">Tips: scan QR di mejamu agar nomor meja terisi otomatis.</p>
    <?php endif; ?>
    <fieldset>
      <legend>Pembayaran</legend>
      <label class="radio"><input type="radio" name="payment_method" value="qris" <?= gateway_ready() ? 'checked' : 'disabled' ?>> QRIS, bayar sekarang di HP<?= gateway_ready() ? '' : ' (belum aktif)' ?></label>
      <label class="radio"><input type="radio" name="payment_method" value="tunai" <?= gateway_ready() ? '' : 'checked' ?>> Tunai, bayar ke kasir</label>
    </fieldset>
    <label>Catatan<input name="note" maxlength="200" placeholder="mis. less sugar"></label>
    <div class="cart-total"><span>Total</span><b data-cart-total>Rp0</b></div>
    <button type="submit" class="btn btn-primary" data-submit>Kirim pesanan</button>
  </form>
</aside>
<?php cs_widget(); site_footer(); ?>
<script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
</body></html>
