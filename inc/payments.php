<?php
require_once __DIR__ . '/http.php';

function gateway_ready(): bool { return MIDTRANS_SERVER_KEY !== '' && MIDTRANS_CLIENT_KEY !== ''; }
function gateway_auth(): array { return ['Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')]; }

/** Membuat transaksi QRIS di Midtrans (Snap). Mengembalikan token Snap atau null. */
function create_snap_token(array $o): ?string {
    $gid = $o['code'] . '-' . time();     // order_id unik di Midtrans (boleh bayar ulang)
    [$http, $d] = http_json('POST', MIDTRANS_SNAP_API, gateway_auth(), [
        'transaction_details' => ['order_id' => $gid, 'gross_amount' => (int)$o['total']],
        'enabled_payments' => ['qris', 'gopay', 'bca_va', 'bni_va'],
        'customer_details' => ['first_name' => mb_substr($o['customer_name'], 0, 40)],
    ]);
    if ($http < 200 || $http >= 300 || empty($d['token'])) return null;
    db()->prepare("UPDATE orders SET gateway_order_id=?, payment_status='menunggu' WHERE id=?")->execute([$gid, $o['id']]);
    return $d['token'];
}

/**
 * Menanyakan status transaksi langsung ke Midtrans (sumber kebenaran), lalu memperbarui pesanan.
 * Dipakai oleh polling halaman status DAN webhook, jadi data dari browser tidak pernah dipercaya.
 */
function sync_payment(array $o): array {
    if (empty($o['gateway_order_id']) || $o['payment_status'] === 'lunas') return $o;
    [$http, $d] = http_json('GET', rtrim(MIDTRANS_STATUS_API, '/') . '/' . rawurlencode($o['gateway_order_id']) . '/status', gateway_auth());
    if ($http !== 200 || !is_array($d)) return $o;
    // pastikan transaksi ini memang milik pesanan ini dan nominalnya sama
    if (($d['order_id'] ?? '') !== $o['gateway_order_id'] || (int)round((float)($d['gross_amount'] ?? 0)) !== (int)$o['total']) return $o;
    $ts = $d['transaction_status'] ?? '';
    if (in_array($ts, ['settlement', 'capture'], true) && ($d['fraud_status'] ?? 'accept') === 'accept') {
        db()->prepare("UPDATE orders SET payment_status='lunas', paid_at=NOW() WHERE id=? AND payment_status<>'lunas'")->execute([$o['id']]);
    } elseif (in_array($ts, ['deny', 'cancel', 'expire', 'failure'], true)) {
        db()->prepare("UPDATE orders SET payment_status='gagal' WHERE id=? AND payment_status<>'lunas'")->execute([$o['id']]);
    }
    $st = db()->prepare('SELECT * FROM orders WHERE id=?'); $st->execute([$o['id']]);
    return $st->fetch();
}
