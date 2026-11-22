<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // DELETE masukan
    csrf_check();
    db()->prepare('DELETE FROM membership_feedback WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]); flash('Masukan dihapus.');
    redirect(url('admin/membership.php'));
}
$cnt = ['ya' => 0, 'mungkin' => 0, 'tidak' => 0];
foreach (db()->query('SELECT interest, COUNT(*) n FROM membership_feedback GROUP BY interest') as $r) $cnt[$r['interest']] = (int)$r['n'];
$total = array_sum($cnt);
$bn = [];
foreach (db()->query("SELECT benefits FROM membership_feedback WHERE benefits<>''") as $r)
    foreach (explode('; ', $r['benefits']) as $b) $bn[$b] = ($bn[$b] ?? 0) + 1;
arsort($bn);
$rows = db()->query('SELECT * FROM membership_feedback ORDER BY id DESC LIMIT 100')->fetchAll();
admin_head('Membership');
?>
<h1>Masukan membership</h1>
<p class="foot-small">Dari halaman <a href="<?= e(url('membership.php')) ?>" target="_blank" rel="noopener">membership.php</a>. Fitur membership belum aktif; data ini untuk menilai apakah layak dibuat.</p>
<div class="stat-row">
  <div class="stat"><small>Total masukan</small><b><?= $total ?></b></div>
  <div class="stat"><small>Ya, akan ikut</small><b><?= $cnt['ya'] ?></b> <span class="foot-small"><?= $total ? round($cnt['ya'] / $total * 100) : 0 ?>%</span></div>
  <div class="stat"><small>Mungkin</small><b><?= $cnt['mungkin'] ?></b> <span class="foot-small"><?= $total ? round($cnt['mungkin'] / $total * 100) : 0 ?>%</span></div>
  <div class="stat"><small>Tidak perlu</small><b><?= $cnt['tidak'] ?></b> <span class="foot-small"><?= $total ? round($cnt['tidak'] / $total * 100) : 0 ?>%</span></div>
</div>
<?php if ($total < 30): ?><p class="notice">Baru <?= $total ?> jawaban. Terlalu sedikit untuk menyimpulkan; target minimal 30 jawaban dari pelanggan nyata.</p><?php endif; ?>
<h2 style="font-size:1.6rem;margin:10px 0">Keuntungan yang dipilih</h2>
<div class="table-wrap"><table><thead><tr><th>Keuntungan</th><th>Dipilih</th></tr></thead><tbody>
<?php foreach ($bn as $b => $n): ?><tr><td><?= e($b) ?></td><td><?= $n ?></td></tr><?php endforeach; if (!$bn): ?><tr><td colspan="2" class="cart-empty">Belum ada data.</td></tr><?php endif; ?></tbody></table></div>
<h2 style="font-size:1.6rem;margin:22px 0 10px">Semua masukan</h2>
<div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Jawaban</th><th>Saran</th><th>Kontak</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['created_at']) ?></td><td><?= e($r['interest']) ?></td><td><?= e($r['comment']) ?></td><td><?= e($r['contact']) ?></td>
<td><form method="post" onsubmit="return confirm('Hapus masukan ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-danger">Hapus</button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5" class="cart-empty">Belum ada masukan.</td></tr><?php endif; ?></tbody></table></div>
<?php admin_foot();
