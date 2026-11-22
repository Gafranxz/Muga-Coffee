<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/_layout.php';
if (is_admin()) redirect(url('admin/'));
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $_SESSION['tries'] = ($_SESSION['tries'] ?? 0);
    if ($_SESSION['tries'] >= 8) { $err = 'Terlalu banyak percobaan. Tutup browser lalu coba lagi.'; }
    else {
        $st = db()->prepare('SELECT * FROM admins WHERE username=?'); $st->execute([trim($_POST['username'] ?? '')]);
        $a = $st->fetch();
        // hash tiruan agar waktu respons sama baik user ada maupun tidak
        $hash = $a['password_hash'] ?? '$2y$10$9QiyVFPPpwk3AxWCF78P4.hqt.ANDcxH3xox1Y7OI2pLgBP0F7IAG';
        if (password_verify($_POST['password'] ?? '', $hash) && $a) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$a['id']; $_SESSION['tries'] = 0;
            redirect(url('admin/'));
        }
        $_SESSION['tries']++; $err = 'Username atau password salah.';
    }
}
admin_head('Masuk');
?>
<form method="post" class="login-card">
  <h1>Masuk kasir</h1>
  <?php if ($err): ?><p class="notice err"><?= e($err) ?></p><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username<input name="username" required autocomplete="username"></label>
  <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
  <button class="btn btn-primary">Masuk</button>
  <p class="foot-small"><a href="<?= e(url()) ?>">&larr; Kembali ke situs</a></p>
</form>
<?php admin_foot();
