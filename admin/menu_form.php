<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$m = ['name' => '', 'category_id' => 0, 'price' => '', 'description' => '', 'image' => '', 'is_spicy' => 0, 'is_available' => 1, 'sort_order' => 99];
if ($id) { $s = db()->prepare('SELECT * FROM menu_items WHERE id=?'); $s->execute([$id]); $m = $s->fetch() ?: redirect(url('admin/menu.php')); }
$cats = db()->query('SELECT id,name FROM categories ORDER BY sort_order')->fetchAll();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // CREATE / UPDATE
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $cat = (int)($_POST['category_id'] ?? 0);
    $price = (int)($_POST['price'] ?? 0);
    $desc = mb_substr(trim($_POST['description'] ?? ''), 0, 255);
    $image = $m['image'];
    if ($name === '' || mb_strlen($name) > 100) $err = 'Nama wajib diisi.';
    elseif ($price < 1000 || $price > 1000000) $err = 'Harga tidak masuk akal (isi dalam rupiah, mis. 25000).';
    elseif (!$cat) $err = 'Pilih kategori.';

    if (!$err && !empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        $info = ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= 2 * 1024 * 1024) ? @getimagesize($f['tmp_name']) : false;
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
        if (!$ext) $err = 'Gambar harus JPG/PNG/WebP, maks. 2 MB.';
        else {
            $image = 'u' . bin2hex(random_bytes(6)) . '.' . $ext;
            move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $image);
        }
    }
    if (!$err) {
        $slug = slugify($name); $n = 2;
        $chk = db()->prepare('SELECT 1 FROM menu_items WHERE slug=? AND id<>?');
        for ($base = $slug; ; $slug = $base . '-' . $n++) { $chk->execute([$slug, $id]); if (!$chk->fetch()) break; }
        $p = [$cat, $name, $slug, $desc ?: null, $price, $image, isset($_POST['is_spicy']) ? 1 : 0, isset($_POST['is_available']) ? 1 : 0, (int)($_POST['sort_order'] ?? 99)];
        if ($id) { db()->prepare('UPDATE menu_items SET category_id=?,name=?,slug=?,description=?,price=?,image=?,is_spicy=?,is_available=?,sort_order=? WHERE id=?')->execute([...$p, $id]); flash('Menu diperbarui.'); }
        else { db()->prepare('INSERT INTO menu_items (category_id,name,slug,description,price,image,is_spicy,is_available,sort_order) VALUES (?,?,?,?,?,?,?,?,?)')->execute($p); flash('Menu ditambahkan.'); }
        redirect(url('admin/menu.php'));
    }
    $m = array_merge($m, $_POST, ['image' => $image]);
}
admin_head($id ? 'Ubah menu' : 'Tambah menu');
?>
<h1><?= $id ? 'Ubah menu' : 'Tambah menu' ?></h1>
<?php if ($err): ?><p class="notice err"><?= e($err) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-card">
  <?= csrf_field() ?>
  <label>Nama menu<input name="name" required maxlength="100" value="<?= e($m['name']) ?>"></label>
  <label>Kategori<select name="category_id" required><option value="">Pilih...</option>
    <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$m['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
  <label>Harga (rupiah)<input type="number" name="price" min="1000" step="500" required value="<?= e($m['price']) ?>"></label>
  <label>Deskripsi (opsional)<input name="description" maxlength="255" value="<?= e($m['description']) ?>"></label>
  <label>Foto (opsional, JPG/PNG/WebP maks. 2 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
  <?php if ($m['image']): ?><p class="foot-small">Foto saat ini: <?= e($m['image']) ?></p><?php endif; ?>
  <label>Urutan<input type="number" name="sort_order" value="<?= (int)$m['sort_order'] ?>"></label>
  <label class="radio"><input type="checkbox" name="is_spicy" <?= $m['is_spicy'] ? 'checked' : '' ?>> Pedas</label>
  <label class="radio"><input type="checkbox" name="is_available" <?= $m['is_available'] ? 'checked' : '' ?>> Tersedia</label>
  <div class="acts"><button class="btn btn-primary">Simpan</button> <a class="btn btn-cream-d" href="<?= e(url('admin/menu.php')) ?>">Batal</a></div>
</form>
<?php admin_foot();
