<?php
/** Menu terlaris dari pesanan yang sudah dibayar (lunas) dan tidak dibatalkan. */
function top_sellers(int $days = 30, int $limit = 5): array {
    $st = db()->prepare("SELECT oi.name, SUM(oi.qty) qty FROM order_items oi JOIN orders o ON o.id=oi.order_id
        WHERE o.payment_status='lunas' AND o.status<>'batal' AND o.created_at >= (NOW() - INTERVAL ? DAY)
        GROUP BY oi.name ORDER BY qty DESC, oi.name LIMIT " . (int)$limit);
    $st->execute([$days]);
    return $st->fetchAll();
}
