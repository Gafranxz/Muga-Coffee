<?php
// Webhook Midtrans. Hanya bekerja jika server Anda dapat diakses internet (bukan localhost).
// Tanpa webhook, status tetap diperbarui lewat polling status.php, jadi fitur ini opsional.
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/payments.php';

$n = json_decode(file_get_contents('php://input'), true) ?: [];
$sig = hash('sha512', ($n['order_id'] ?? '') . ($n['status_code'] ?? '') . ($n['gross_amount'] ?? '') . MIDTRANS_SERVER_KEY);
if (!hash_equals($sig, $n['signature_key'] ?? '')) { http_response_code(403); exit('bad signature'); }

$st = db()->prepare('SELECT * FROM orders WHERE gateway_order_id=?'); $st->execute([$n['order_id']]);
if ($o = $st->fetch()) sync_payment($o);      // status dikonfirmasi ulang langsung ke Midtrans
http_response_code(200); echo 'ok';
