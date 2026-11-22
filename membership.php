<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$done = false; $err = '';
$options = ['Diskon untuk pelanggan tetap', 'Kumpul poin, tukar minuman gratis', 'Menu atau promo khusus member', 'Prioritas antrean pesanan', 'Gratis upgrade ukuran atau extra shot'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $interest = $_POST['interest'] ?? '';
    $picked = array_values(array_intersect($options, (array)($_POST['benefits'] ?? [])));
    $comment = mb_substr(trim($_POST['comment'] ?? ''), 0, 300);
    $contact = mb_substr(trim($_POST['contact'] ?? ''), 0, 80);
    if (!in_array($interest, ['ya', 'mungkin', 'tidak'], true)) $err = 'Pilih salah satu jawaban dulu ya.';
    elseif (($_SESSION['fb_last'] ?? 0) > time() - 20) $err = 'Tunggu sebentar sebelum mengirim lagi.';
    else {
        db()->prepare('INSERT INTO membership_feedback (interest,benefits,comment,contact) VALUES (?,?,?,?)')
            ->execute([$interest, mb_substr(implode('; ', $picked), 0, 120), $comment, $contact]);
        $_SESSION['fb_last'] = time(); $done = true;
    }
}
page_head('Membership Muga Kopi | Segera Hadir', 'Program membership Muga Kopi segera hadir. Bantu kami menentukan keuntungan member yang paling kamu mau.',
  ['path' => 'membership.php', 'body' => 'plain']);
site_header();
?>
<main class="soon">
  <span class="tag">Segera hadir</span>
  <h1>Membership Muga</h1>
  <p class="lead">Kami sedang menyiapkan program untuk pelanggan setia. Fiturnya belum aktif, jadi sebelum dibuat, kami ingin tahu pendapatmu dulu.</p>
  <ul class="perks">
    <?php foreach ($options as $o): ?><li><?= e($o) ?></li><?php endforeach; ?>
  </ul>
  <p class="foot-small">Daftar di atas adalah ide, belum janji. Hasil masukan kalian yang menentukan mana yang dibuat.</p>

<?php if ($done): ?>
  <p class="notice ok">Terima kasih! Masukanmu sudah tersimpan.</p>
  <a class="btn btn-cream" href="<?= e(url()) ?>">Kembali ke menu</a>
<?php else: ?>
  <?php if ($err): ?><p class="notice err"><?= e($err) ?></p><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <fieldset><legend>Menurutmu, fitur membership di Muga Kopi menarik?</legend>
      <label class="radio"><input type="radio" name="interest" value="ya" required> Ya, saya akan ikut</label>
      <label class="radio"><input type="radio" name="interest" value="mungkin"> Mungkin, tergantung keuntungannya</label>
      <label class="radio"><input type="radio" name="interest" value="tidak"> Tidak terlalu perlu</label>
    </fieldset>
    <fieldset><legend>Keuntungan apa yang paling kamu mau? (boleh lebih dari satu)</legend>
      <?php foreach ($options as $o): ?><label class="radio"><input type="checkbox" name="benefits[]" value="<?= e($o) ?>"> <?= e($o) ?></label><?php endforeach; ?>
    </fieldset>
    <label>Saran atau komentar (opsional)<textarea name="comment" rows="3" maxlength="300"></textarea></label>
    <label>WhatsApp atau email, jika mau dikabari saat sudah buka (opsional)<input name="contact" maxlength="80" autocomplete="off"></label>
    <button class="btn btn-primary">Kirim masukan</button>
  </form>
<?php endif; ?>
</main>
<?php site_footer(); ?>
</body></html>
