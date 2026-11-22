<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

$today = date('Y-m-d');
$from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'] ?? '') ? $_GET['from'] : date('Y-m-01');
$to   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'] ?? '') ? $_GET['to'] : $today;
$p = [$from . ' 00:00:00', $to . ' 23:59:59'];
// Laporan hanya menghitung pesanan LUNAS dan tidak dibatalkan.
$where = "payment_status='lunas' AND status<>'batal' AND created_at BETWEEN ? AND ?";

$q = fn(string $sql) => (function () use ($sql, $p) { $s = db()->prepare($sql); $s->execute($p); return $s->fetchAll(); })();
$sum = $q("SELECT COUNT(*) n, COALESCE(SUM(total),0) omzet FROM orders WHERE $where")[0];
$byPay = $q("SELECT payment_method m, COUNT(*) n, SUM(total) t FROM orders WHERE $where GROUP BY payment_method");
$byDay = $q("SELECT DATE(created_at) d, COUNT(*) n, SUM(total) t FROM orders WHERE $where GROUP BY DATE(created_at) ORDER BY d");
$byItem = $q("SELECT oi.name, SUM(oi.qty) qty, SUM(oi.qty*oi.price) t FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.payment_status='lunas' AND o.status<>'batal' AND o.created_at BETWEEN ? AND ? GROUP BY oi.name ORDER BY qty DESC, t DESC LIMIT 10");
$byCat = $q("SELECT COALESCE(c.name,'(menu dihapus)') cat, SUM(oi.qty*oi.price) t FROM order_items oi JOIN orders o ON o.id=oi.order_id LEFT JOIN menu_items m ON m.id=oi.menu_item_id LEFT JOIN categories c ON c.id=m.category_id WHERE o.payment_status='lunas' AND o.status<>'batal' AND o.created_at BETWEEN ? AND ? GROUP BY cat ORDER BY t DESC");
$pending = $q("SELECT COUNT(*) n, COALESCE(SUM(total),0) t FROM orders WHERE payment_status<>'lunas' AND status<>'batal' AND created_at BETWEEN ? AND ?")[0];
$cancel = $q("SELECT COUNT(*) n FROM orders WHERE status='batal' AND created_at BETWEEN ? AND ?")[0]['n'];

if (($_GET['csv'] ?? '') === '1') {                    // ekspor CSV (dibuka di Excel)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan-muga-' . $from . '_' . $to . '.csv"');
    $f = fopen('php://output', 'w'); fwrite($f, "\xEF\xBB\xBF");
    fputcsv($f, ['Tanggal', 'Kode', 'Nama', 'Meja', 'Metode', 'Total', 'Dibayar']);
    $s = db()->prepare("SELECT * FROM orders WHERE $where ORDER BY created_at"); $s->execute($p);
    foreach ($s as $o) {
        // cegah injeksi rumus spreadsheet dari nama pelanggan
        $nm = preg_match('/^[=+\-@\t\r]/', $o['customer_name']) ? "'" . $o['customer_name'] : $o['customer_name'];
        fputcsv($f, [$o['created_at'], $o['code'], $nm, $o['table_no'], $o['payment_method'], $o['total'], $o['paid_at']]);
    }
    exit;
}
$max = max([1, ...array_map(fn($r) => (int)$r['t'], $byDay)]);
admin_head('Laporan');
?>
<h1>Laporan keuangan</h1>
<form method="get" class="filter-row no-print">
  <label>Dari<input type="date" name="from" value="<?= e($from) ?>"></label>
  <label>Sampai<input type="date" name="to" value="<?= e($to) ?>"></label>
  <button class="btn btn-primary">Tampilkan</button>
  <a class="btn btn-cream-d" href="?from=<?= e($from) ?>&to=<?= e($to) ?>&csv=1">Unduh CSV</a>
  <button type="button" class="btn btn-cream-d" onclick="window.print()">Cetak</button>
</form>
<p class="foot-small">Periode <?= e($from) ?> s/d <?= e($to) ?>. Hanya pesanan <b>lunas</b> dan tidak dibatalkan yang dihitung. Pesanan tunai menjadi lunas saat kasir menekan "Terima tunai" atau menyelesaikan pesanan.</p>

<div class="stat-row">
  <div class="stat"><small>Omzet</small><b><?= rupiah((int)$sum['omzet']) ?></b></div>
  <div class="stat"><small>Jumlah pesanan</small><b><?= (int)$sum['n'] ?></b></div>
  <div class="stat"><small>Rata-rata per pesanan</small><b><?= $sum['n'] ? rupiah((int)round($sum['omzet'] / $sum['n'])) : 'Rp0' ?></b></div>
  <div class="stat"><small>Belum dibayar</small><b><?= (int)$pending['n'] ?></b> <span class="foot-small"><?= rupiah((int)$pending['t']) ?></span></div>
  <div class="stat"><small>Dibatalkan</small><b><?= (int)$cancel ?></b></div>
</div>

<h2 style="font-size:1.6rem;margin:10px 0">Per metode pembayaran</h2>
<div class="table-wrap"><table><thead><tr><th>Metode</th><th>Pesanan</th><th>Total</th></tr></thead><tbody>
<?php foreach ($byPay as $r): ?><tr><td><?= $r['m'] === 'qris' ? 'QRIS' : 'Tunai' ?></td><td><?= (int)$r['n'] ?></td><td><?= rupiah((int)$r['t']) ?></td></tr>
<?php endforeach; if (!$byPay): ?><tr><td colspan="3" class="cart-empty">Belum ada data pada periode ini.</td></tr><?php endif; ?></tbody></table></div>

<h2 style="font-size:1.6rem;margin:22px 0 10px">Omzet harian</h2>
<div class="bars">
<?php foreach ($byDay as $r): ?><div class="bar"><span><?= e($r['d']) ?></span><i style="width:<?= max(1, round($r['t'] / $max * 100)) ?>%"></i><b><?= rupiah((int)$r['t']) ?> (<?= (int)$r['n'] ?>)</b></div>
<?php endforeach; if (!$byDay) echo '<p class="cart-empty">Belum ada data.</p>'; ?>
</div>

<h2 style="font-size:1.6rem;margin:22px 0 10px">10 menu terlaris</h2>
<div class="table-wrap"><table><thead><tr><th>Menu</th><th>Terjual</th><th>Pendapatan</th></tr></thead><tbody>
<?php foreach ($byItem as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= (int)$r['qty'] ?></td><td><?= rupiah((int)$r['t']) ?></td></tr>
<?php endforeach; if (!$byItem): ?><tr><td colspan="3" class="cart-empty">Belum ada data.</td></tr><?php endif; ?></tbody></table></div>

<h2 style="font-size:1.6rem;margin:22px 0 10px">Per kategori</h2>
<div class="table-wrap"><table><thead><tr><th>Kategori</th><th>Pendapatan</th></tr></thead><tbody>
<?php foreach ($byCat as $r): ?><tr><td><?= e($r['cat']) ?></td><td><?= rupiah((int)$r['t']) ?></td></tr>
<?php endforeach; if (!$byCat): ?><tr><td colspan="2" class="cart-empty">Belum ada data.</td></tr><?php endif; ?></tbody></table></div>
<?php admin_foot();
