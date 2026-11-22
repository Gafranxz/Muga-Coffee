<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/payments.php';

$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_GET['code'] ?? ''));
$st = db()->prepare('SELECT * FROM orders WHERE code=?'); $st->execute([$code]);
$o = $st->fetch();
if ($o && $o['payment_method'] === 'qris' && $o['payment_status'] !== 'lunas') $o = sync_payment($o);

if (isset($_GET['json'])) {          // dipakai polling otomatis
    header('Content-Type: application/json');
    echo json_encode(['status' => $o['status'] ?? null, 'pay' => $o['payment_status'] ?? null]); exit;
}
if (!$o) { http_response_code(404); }
$steps = ['baru' => 'Pesanan masuk', 'diproses' => 'Sedang dibuat', 'diantar' => 'Diantar ke mejamu', 'selesai' => 'Selesai'];
$items = [];
if ($o) { $i = db()->prepare('SELECT * FROM order_items WHERE order_id=?'); $i->execute([$o['id']]); $items = $i->fetchAll(); }

page_head('Status Pesanan | Muga Kopi', 'Pantau status pesanan Muga Kopi kamu.', ['robots' => 'noindex,nofollow', 'path' => 'status.php', 'body' => 'plain']);
site_header();
?>
<main class="status-page">
<?php if (!$o): ?>
  <h1>Pesanan tidak ditemukan</h1>
  <p>Cek kembali kodenya, atau <a href="<?= e(url()) ?>">kembali ke menu</a>.</p>
<?php else: ?>
  <p class="kicker">Kode pesanan</p>
  <h1 class="code"><?= e($o['code']) ?></h1>
  <p>Atas nama <b><?= e($o['customer_name']) ?></b> &middot; Meja <b><?= e($o['table_no']) ?></b> &middot; <?= $o['payment_method'] === 'qris' ? 'QRIS' : 'Tunai di kasir' ?></p>
  <?php if ($o['payment_status'] === 'lunas'): ?>
    <p class="notice ok">Pembayaran diterima. Terima kasih!</p>
  <?php elseif ($o['status'] !== 'batal' && $o['payment_method'] === 'qris'): ?>
    <p class="notice"><?= $o['payment_status'] === 'gagal' ? 'Pembayaran gagal atau kedaluwarsa.' : 'Pesananmu baru dibuat setelah pembayaran QRIS berhasil.' ?></p>
    <a class="btn btn-primary" href="<?= e(url('pay.php?code=' . $o['code'])) ?>">Bayar sekarang</a>
  <?php elseif ($o['status'] !== 'batal'): ?>
    <p class="notice">Bayar tunai ke kasir saat pesanan diantar.</p>
  <?php endif; ?>

  <?php if ($o['status'] === 'batal'): ?>
    <p class="notice">Pesanan ini dibatalkan. Silakan hubungi kasir.</p>
  <?php else: $keys = array_keys($steps); $cur = array_search($o['status'], $keys); ?>
    <ol class="track" data-track data-pay="<?= e($o['payment_status']) ?>" data-code="<?= e($o['code']) ?>" data-poll="<?= e(url('status.php?code=' . $o['code'] . '&json=1')) ?>">
      <?php foreach ($keys as $i => $k): ?>
        <li class="<?= $i < $cur ? 'done' : ($i === $cur ? 'now' : '') ?>" data-step="<?= e($k) ?>"><i></i><?= e($steps[$k]) ?></li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>

  <ul class="receipt">
    <?php foreach ($items as $it): ?>
      <li><span><?= (int)$it['qty'] ?>&times; <?= e($it['name']) ?></span><span><?= rupiah($it['price'] * $it['qty']) ?></span></li>
    <?php endforeach; ?>
    <li class="sum"><span>Total</span><b><?= rupiah((int)$o['total']) ?></b></li>
  </ul>
  <p class="foot-small">Halaman ini memperbarui status otomatis.</p>
  <a class="btn btn-cream" href="<?= e(url()) ?>">Pesan lagi</a>
  <script>try{localStorage.removeItem('muga_cart')}catch(e){}</script>
<?php endif; ?>
</main>
<?php cs_widget(); site_footer(); ?>
<script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
</body></html>
