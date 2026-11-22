<?php
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/payments.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(url());
csrf_check();

$name  = trim($_POST['customer_name'] ?? '');
$table = trim($_POST['table_no'] ?? '');
$ttoken = trim($_POST['table_token'] ?? '');
$pay   = ($_POST['payment_method'] ?? '') === 'qris' ? 'qris' : 'tunai';
$note  = mb_substr(trim($_POST['note'] ?? ''), 0, 200);
$raw   = json_decode($_POST['items'] ?? '[]', true);

$fail = function (string $m) { flash($m); redirect(url('#menu')); };
if ($name === '' || mb_strlen($name) > 60) $fail('Nama wajib diisi (maks. 60 huruf).');
if ($ttoken !== '') {                 // dari QR meja: nomor meja diambil dari database, bukan dari browser
    $t = db()->prepare('SELECT table_no FROM cafe_tables WHERE token=? AND is_active=1'); $t->execute([$ttoken]);
    if (!($row = $t->fetch())) $fail('QR meja tidak valid. Scan ulang QR di mejamu.');
    $table = $row['table_no'];
} elseif (!preg_match('/^[A-Za-z0-9\- ]{1,10}$/', $table)) $fail('Nomor meja tidak valid.');
if ($pay === 'qris' && !gateway_ready()) $fail('Pembayaran QRIS belum aktif. Pilih tunai di kasir.');
if (!is_array($raw) || !$raw || count($raw) > 40) $fail('Pesanan kosong.');

// Harga SELALU diambil dari database, bukan dari browser, supaya tidak bisa dimanipulasi.
$pdo = db();
$lines = []; $total = 0;
$q = $pdo->prepare('SELECT id,name,price FROM menu_items WHERE id=? AND is_available=1');
foreach ($raw as $r) {
    $id = (int)($r['id'] ?? 0); $qty = (int)($r['qty'] ?? 0);
    if ($id < 1 || $qty < 1 || $qty > 20) continue;
    $q->execute([$id]);
    if (!($m = $q->fetch())) $fail('Ada menu yang sudah habis atau tidak tersedia. Muat ulang halaman.');
    $lines[] = [$m['id'], $m['name'], (int)$m['price'], $qty];
    $total += (int)$m['price'] * $qty;
}
if (!$lines) $fail('Pesanan kosong.');

$pdo->beginTransaction();
try {
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $dup = $pdo->prepare('SELECT 1 FROM orders WHERE code=?'); $dup->execute([$code]);
    } while ($dup->fetch());
    $pdo->prepare('INSERT INTO orders (code,customer_name,table_no,payment_method,note,total) VALUES (?,?,?,?,?,?)')
        ->execute([$code, $name, $table, $pay, $note, $total]);
    $oid = (int)$pdo->lastInsertId();
    $ins = $pdo->prepare('INSERT INTO order_items (order_id,menu_item_id,name,price,qty) VALUES (?,?,?,?,?)');
    foreach ($lines as [$id, $n, $p, $qty]) $ins->execute([$oid, $id, $n, $p, $qty]);
    $pdo->commit();
} catch (Throwable $ex) {
    $pdo->rollBack();
    $fail('Pesanan gagal disimpan. Coba lagi.');
}
redirect(url(($pay === 'qris' ? 'pay.php' : 'status.php') . '?code=' . $code . '&new=1'));
