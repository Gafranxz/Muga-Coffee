<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // DELETE & toggle stok
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'delete') {
        db()->prepare('DELETE FROM menu_items WHERE id=?')->execute([$id]); flash('Menu dihapus.');
    } elseif (($_POST['do'] ?? '') === 'toggle') {
        db()->prepare('UPDATE menu_items SET is_available = 1 - is_available WHERE id=?')->execute([$id]); flash('Ketersediaan diubah.');
    }
    redirect(url('admin/menu.php'));
}
$q = trim($_GET['q'] ?? '');
$st = db()->prepare('SELECT m.*, c.name cat FROM menu_items m JOIN categories c ON c.id=m.category_id
    WHERE m.name LIKE ? OR c.name LIKE ? ORDER BY c.sort_order, m.sort_order, m.id');
$st->execute(["%$q%", "%$q%"]);
$rows = $st->fetchAll();
admin_head('Menu');
?>
<div class="adm-title"><h1>Menu <small><?= count($rows) ?> item</small></h1>
  <a class="btn btn-primary" href="<?= e(url('admin/menu_form.php')) ?>">+ Tambah menu</a></div>
<form method="get" class="search"><input name="q" value="<?= e($q) ?>" placeholder="Cari menu atau kategori" aria-label="Cari"><button class="btn btn-cream-d">Cari</button></form>
<div class="table-wrap"><table>
<thead><tr><th>Menu</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr class="<?= $r['is_available'] ? '' : 'off' ?>">
  <td><?= e($r['name']) ?><?= $r['is_spicy'] ? ' <i class="chili">Pedas</i>' : '' ?></td>
  <td><?= e($r['cat']) ?></td><td><?= rupiah((int)$r['price']) ?></td>
  <td><?= $r['is_available'] ? 'Tersedia' : 'Habis' ?></td>
  <td class="acts">
    <a class="btn btn-cream-d" href="<?= e(url('admin/menu_form.php?id=' . $r['id'])) ?>">Ubah</a>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="toggle"><button class="btn btn-cream-d"><?= $r['is_available'] ? 'Tandai habis' : 'Tersedia' ?></button></form>
    <form method="post" onsubmit="return confirm('Hapus <?= e(addslashes($r['name'])) ?>?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="delete"><button class="btn btn-danger">Hapus</button></form>
  </td>
</tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5" class="cart-empty">Tidak ada data.</td></tr><?php endif; ?>
</tbody></table></div>
<?php admin_foot();
