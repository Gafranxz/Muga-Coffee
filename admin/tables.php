<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {          // CRUD meja
    csrf_check();
    $do = $_POST['do'] ?? ''; $id = (int)($_POST['id'] ?? 0); $no = trim($_POST['table_no'] ?? '');
    try {
        if ($do === 'add' && preg_match('/^[A-Za-z0-9\-]{1,10}$/', $no)) {
            db()->prepare('INSERT INTO cafe_tables (table_no, token) VALUES (?,?)')->execute([$no, bin2hex(random_bytes(5))]); flash('Meja ditambahkan.');
        } elseif ($do === 'toggle') { db()->prepare('UPDATE cafe_tables SET is_active = 1 - is_active WHERE id=?')->execute([$id]); flash('Status meja diubah.'); }
        elseif ($do === 'regen') { db()->prepare('UPDATE cafe_tables SET token=? WHERE id=?')->execute([bin2hex(random_bytes(5)), $id]); flash('QR baru dibuat. Cetak ulang QR meja ini; yang lama tidak berlaku lagi.'); }
        elseif ($do === 'delete') { db()->prepare('DELETE FROM cafe_tables WHERE id=?')->execute([$id]); flash('Meja dihapus.'); }
        else flash('Nomor meja hanya boleh huruf, angka, dan tanda minus (maks. 10).');
    } catch (PDOException $ex) { flash('Nomor meja sudah ada.'); }
    redirect(url('admin/tables.php'));
}
$rows = db()->query('SELECT * FROM cafe_tables ORDER BY LENGTH(table_no), table_no')->fetchAll();
// Alamat dasar untuk QR. Untuk dipindai dari HP, ganti localhost dengan IP laptop (mis. 192.168.1.5:8081).
$default = abs_url('');
admin_head('Meja & QR');
?>
<h1>Meja &amp; QR</h1>
<form method="post" class="inline-add no-print"><?= csrf_field() ?><input type="hidden" name="do" value="add"><input name="table_no" placeholder="Nomor meja baru, mis. 13" required maxlength="10"><button class="btn btn-primary">Tambah meja</button></form>

<div class="filter-row no-print">
  <label style="flex:1;min-width:260px">Alamat situs untuk QR (harus bisa dibuka dari HP pelanggan)
    <input id="baseUrl" value="<?= e($default) ?>"></label>
  <button type="button" class="btn btn-primary" id="makeQr">Buat QR</button>
  <button type="button" class="btn btn-cream-d" onclick="window.print()">Cetak semua</button>
</div>
<p class="foot-small no-print">Di localhost, HP tidak bisa membuka <code>localhost</code>. Pakai IP laptop di jaringan Wi-Fi yang sama (cek dengan <code>ipconfig</code>), misalnya <code>http://192.168.1.5:8081/muga-cafe/</code>.</p>

<div class="qr-grid" id="qrGrid">
<?php foreach ($rows as $r): ?>
  <div class="qr-card" data-no="<?= e($r['table_no']) ?>" data-token="<?= e($r['token']) ?>" <?= $r['is_active'] ? '' : 'style="opacity:.45"' ?>>
    <h3>Meja <?= e($r['table_no']) ?></h3>
    <div class="qr-img"></div>
    <p>Scan untuk lihat menu &amp; pesan</p>
    <div class="acts no-print" style="justify-content:center;margin-top:8px">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="toggle"><button class="btn btn-cream-d"><?= $r['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
      <form method="post" onsubmit="return confirm('Buat QR baru? QR lama di meja ini tidak berlaku lagi.')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="regen"><button class="btn btn-cream-d">QR baru</button></form>
      <form method="post" onsubmit="return confirm('Hapus meja ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="do" value="delete"><button class="btn btn-danger">Hapus</button></form>
    </div>
  </div>
<?php endforeach; ?>
</div>
<script src="<?= e(url('assets/js/qrcode.js')) ?>"></script>
<script>
function build() {
  const base = document.getElementById('baseUrl').value.trim().replace(/\/?$/, '/');
  document.querySelectorAll('.qr-card').forEach(c => {
    const link = base + '?meja=' + encodeURIComponent(c.dataset.no) + '&t=' + c.dataset.token;
    const qr = qrcode(0, 'M'); qr.addData(link); qr.make();
    c.querySelector('.qr-img').innerHTML = qr.createImgTag(6, 4);
    c.title = link;
  });
}
document.getElementById('makeQr').addEventListener('click', build); build();
</script>
<?php admin_foot();
