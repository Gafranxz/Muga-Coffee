<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/payments.php';

$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_GET['code'] ?? ''));
$st = db()->prepare('SELECT * FROM orders WHERE code=?'); $st->execute([$code]);
$o = $st->fetch();
if (!$o || $o['payment_method'] !== 'qris') redirect(url());
if ($o['payment_status'] === 'lunas' || $o['status'] === 'batal') redirect(url('status.php?code=' . $code));

$token = gateway_ready() ? create_snap_token($o) : null;
page_head('Bayar QRIS | Muga Kopi', 'Selesaikan pembayaran QRIS pesananmu.', ['robots' => 'noindex,nofollow', 'path' => 'pay.php', 'body' => 'plain']);
site_header();
?>
<main class="status-page">
  <p class="kicker">Pembayaran QRIS</p>
  <h1 class="code"><?= e($o['code']) ?></h1>
  <p>Total <b><?= rupiah((int)$o['total']) ?></b> &middot; Meja <b><?= e($o['table_no']) ?></b></p>
<?php if (!$token): ?>
  <p class="notice err">Pembayaran online sedang tidak bisa dimulai. Kamu bisa bayar tunai ke kasir, atau coba lagi.</p>
  <a class="btn btn-cream" href="<?= e(url('pay.php?code=' . $code)) ?>">Coba lagi</a>
  <a class="btn btn-ghost" href="<?= e(url('status.php?code=' . $code)) ?>">Lihat status pesanan</a>
<?php else: ?>
  <p>Jendela pembayaran akan terbuka. Pindai QRIS dengan aplikasi e-wallet atau mobile banking.</p>
  <button type="button" class="btn btn-cream" id="payNow">Buka pembayaran</button>
  <a class="btn btn-ghost" href="<?= e(url('status.php?code=' . $code)) ?>">Lihat status pesanan</a>
  <script src="<?= e(MIDTRANS_SNAP_JS) ?>" data-client-key="<?= e(MIDTRANS_CLIENT_KEY) ?>"></script>
  <script>
    const go = () => snap.pay(<?= json_encode($token) ?>, {
      onSuccess: () => location.href = <?= json_encode(url('status.php?code=' . $code)) ?>,
      onPending: () => location.href = <?= json_encode(url('status.php?code=' . $code)) ?>,
      onError: () => alert('Pembayaran gagal. Coba lagi atau bayar tunai di kasir.'),
    });
    document.getElementById('payNow').addEventListener('click', go);
    window.addEventListener('load', () => setTimeout(go, 300));
  </script>
<?php endif; ?>
</main>
<?php site_footer(); ?>
</body></html>
