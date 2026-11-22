<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // CRUD kategori
    csrf_check();
    $do = $_POST['do'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? ''); $sort = (int)($_POST['sort_order'] ?? 0);
    try {
        if ($do === 'add' && $name !== '') {
            db()->prepare('INSERT INTO categories (slug,name,sort_order) VALUES (?,?,?)')->execute([slugify($name), $name, $sort]); flash('Kategori ditambahkan.');
        } elseif ($do === 'save' && $name !== '') {
            db()->prepare('UPDATE categories SET name=?, sort_order=? WHERE id=?')->execute([$name, $sort, $id]); flash('Kategori disimpan.');
        } elseif ($do === 'delete') {
            db()->prepare('DELETE FROM categories WHERE id=?')->execute([$id]); flash('Kategori dihapus.');
        }
    } catch (PDOException $ex) {
        flash($do === 'delete' ? 'Kategori masih berisi menu. Pindahkan atau hapus menunya dulu.' : 'Nama kategori sudah dipakai.');
    }
    redirect(url('admin/categories.php'));
}
$rows = db()->query('SELECT c.*, (SELECT COUNT(*) FROM menu_items m WHERE m.category_id=c.id) n FROM categories c ORDER BY sort_order')->fetchAll();
admin_head('Kategori');
?>
<h1>Kategori</h1>
<form method="post" class="inline-add"><?= csrf_field() ?><input type="hidden" name="do" value="add">
  <input name="name" placeholder="Nama kategori baru" required maxlength="80"><input type="number" name="sort_order" value="<?= count($rows) + 1 ?>" aria-label="Urutan"><button class="btn btn-primary">Tambah</button></form>
<div class="table-wrap"><table><thead><tr><th>Nama</th><th>Urutan</th><th>Menu</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): $f = 'f' . (int)$r['id']; ?><tr>
  <td><input form="<?= $f ?>" name="name" value="<?= e($r['name']) ?>" required maxlength="80" aria-label="Nama"></td>
  <td><input form="<?= $f ?>" type="number" name="sort_order" value="<?= (int)$r['sort_order'] ?>" aria-label="Urutan"></td>
  <td><?= (int)$r['n'] ?></td>
  <td class="acts"><button form="<?= $f ?>" name="do" value="save" class="btn btn-cream-d">Simpan</button>
    <button form="<?= $f ?>" name="do" value="delete" class="btn btn-danger" onclick="return confirm('Hapus kategori ini?')">Hapus</button></td>
</tr><?php endforeach; ?></tbody></table></div>
<?php foreach ($rows as $r): ?><form id="f<?= (int)$r['id'] ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"></form><?php endforeach; ?>
<?php admin_foot();
