<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

$flow = ['baru' => 'diproses', 'diproses' => 'diantar', 'diantar' => 'selesai'];
$label = ['baru' => 'Terima & buat', 'diproses' => 'Antar', 'diantar' => 'Selesai'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // UPDATE status
    csrf_check();
    $id = (int)($_POST['id'] ?? 0); $to = $_POST['to'] ?? '';
    $cur = db()->prepare('SELECT * FROM orders WHERE id=?'); $cur->execute([$id]); $o = $cur->fetch();
    if ($o && $to === 'paid' && $o['payment_method'] === 'tunai') {            // kasir menerima uang tunai
        db()->prepare("UPDATE orders SET payment_status='lunas', paid_at=NOW() WHERE id=?")->execute([$id]);
    } elseif ($o && in_array($to, ['diproses', 'diantar', 'selesai', 'batal'], true)) {
        // pesanan QRIS baru boleh diproses setelah lunas
        if ($o['payment_method'] === 'qris' && $o['payment_status'] !== 'lunas' && $to !== 'batal') { flash('Pesanan QRIS ini belum dibayar.'); redirect(url('admin/')); }
        db()->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$to, $id]);
        if ($to === 'selesai' && $o['payment_method'] === 'tunai')              // tunai: otomatis lunas saat selesai
            db()->prepare("UPDATE orders SET payment_status='lunas', paid_at=COALESCE(paid_at,NOW()) WHERE id=?")->execute([$id]);
    }
    redirect(url('admin/'));
}

function render_board(): void {
    global $flow, $label;
    $rows = db()->query("SELECT * FROM orders WHERE status NOT IN ('selesai','batal') ORDER BY created_at")->fetchAll();
    if (!$rows) { echo '<p class="cart-empty">Belum ada pesanan aktif.</p>'; return; }
    $it = db()->prepare('SELECT name,qty FROM order_items WHERE order_id=?');
    foreach ($rows as $o) {
        $it->execute([$o['id']]); ?>
<article class="ord s-<?= e($o['status']) ?>">
  <header><b class="tbl">Meja <?= e($o['table_no']) ?></b><span class="pill"><?= e($o['status']) ?></span></header>
  <p class="pay pay-<?= e($o['payment_status']) ?>"><?= $o['payment_method'] === 'qris' ? 'QRIS' : 'Tunai' ?> &middot; <?= ['belum' => 'belum dibayar', 'menunggu' => 'menunggu pembayaran', 'lunas' => 'LUNAS', 'gagal' => 'gagal/kedaluwarsa'][$o['payment_status']] ?></p>
  <p class="who"><?= e($o['customer_name']) ?> &middot; <?= e($o['code']) ?> &middot; <?= date('H:i', strtotime($o['created_at'])) ?></p>
  <ul><?php foreach ($it as $r): ?><li><?= (int)$r['qty'] ?>&times; <?= e($r['name']) ?></li><?php endforeach; ?></ul>
  <?php if ($o['note']): ?><p class="note">Catatan: <?= e($o['note']) ?></p><?php endif; ?>
  <p class="tot"><?= rupiah((int)$o['total']) ?></p>
  <form method="post" class="ord-actions"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
    <?php $locked = $o['payment_method'] === 'qris' && $o['payment_status'] !== 'lunas'; ?>
    <button name="to" value="<?= e($flow[$o['status']]) ?>" class="btn btn-primary" <?= $locked ? 'disabled title="Menunggu pembayaran QRIS"' : '' ?>><?= e($label[$o['status']]) ?></button>
    <?php if ($o['payment_method'] === 'tunai' && $o['payment_status'] !== 'lunas'): ?><button name="to" value="paid" class="btn btn-cream-d">Terima tunai</button><?php endif; ?>
    <button name="to" value="batal" class="btn btn-ghost-d" onclick="return confirm('Batalkan pesanan ini?')">Batalkan</button>
  </form>
</article>
<?php }
}
if (isset($_GET['partial'])) { render_board(); exit; }

admin_head('Pesanan');
?>
<h1>Pesanan aktif</h1>
<div class="board" data-board data-src="<?= e(url('admin/?partial=1')) ?>"><?php render_board(); ?></div>
<script>
// Segarkan daftar tiap 6 detik tanpa muat ulang halaman.
const b=document.querySelector('[data-board]');
setInterval(()=>{ if(document.hidden) return; fetch(b.dataset.src).then(r=>r.text()).then(h=>{b.innerHTML=h}).catch(()=>{}) },6000);
</script>
<?php admin_foot();
