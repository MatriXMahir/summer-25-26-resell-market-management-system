<?php
function order_find_owned($conn, int $id, int $buyerId): ?array {
    $sql  = "SELECT o.*, p.price, p.stock_qty, p.name AS product_name, p.id AS product_id
             FROM orders o JOIN products p ON p.id = o.product_id
             WHERE o.id = ? AND o.buyer_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $buyerId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function order_create($conn, int $buyerId, int $productId, int $qty, float $total): int {
    $sql  = "INSERT INTO orders (buyer_id, product_id, quantity, total_price, status)
             VALUES (?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiid", $buyerId, $productId, $qty, $total);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $newId;
}

function order_update_quantity($conn, int $id, int $buyerId, int $qty, float $total): bool {
    $sql  = "UPDATE orders SET quantity = ?, total_price = ? WHERE id = ? AND buyer_id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "idii", $qty, $total, $id, $buyerId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function order_delete($conn, int $id, int $buyerId): bool {
    $sql  = "DELETE FROM orders WHERE id = ? AND buyer_id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $buyerId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function order_list_by_buyer($conn, int $buyerId) {
    $sql  = "SELECT o.*, p.name AS product_name FROM orders o
             JOIN products p ON p.id = o.product_id
             WHERE o.buyer_id = ? ORDER BY o.order_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $buyerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function order_search_by_buyer($conn, int $buyerId, string $term) {
    $like = "%" . $term . "%";
    $sql  = "SELECT o.*, p.name AS product_name FROM orders o
             JOIN products p ON p.id = o.product_id
             WHERE o.buyer_id = ? AND p.name LIKE ? ORDER BY o.order_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $buyerId, $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function order_update_status($conn, int $id, string $status): void {
    $sql  = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function order_list_pending_for_buyer($conn, int $buyerId) {
    $sql  = "SELECT o.*, p.name AS product_name FROM orders o
             JOIN products p ON p.id = o.product_id
             WHERE o.buyer_id = ? AND o.status = 'pending' ORDER BY o.order_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $buyerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Paid orders awaiting delivery assignment (used by the delivery model too,
// but the query starts from orders so it belongs here).
function order_awaiting_delivery($conn) {
    $sql = "SELECT o.id, p.name FROM orders o
            JOIN products p ON p.id = o.product_id
            LEFT JOIN deliveries d ON d.order_id = o.id
            WHERE o.status IN ('paid','shipped') AND d.id IS NULL
            ORDER BY o.order_date";
    return mysqli_query($conn, $sql);
}

function payment_create($conn, int $orderId, int $buyerId, float $amount, string $method): void {
    $sql  = "INSERT INTO payments (order_id, buyer_id, amount, method, payment_status)
             VALUES (?, ?, ?, ?, 'success')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iids", $orderId, $buyerId, $amount, $method);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function payment_list_by_buyer($conn, int $buyerId) {
    $sql  = "SELECT pay.*, p.name AS product_name FROM payments pay
             JOIN orders o ON o.id = pay.order_id
             JOIN products p ON p.id = o.product_id
             WHERE pay.buyer_id = ? ORDER BY pay.paid_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $buyerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function review_exists_for_order($conn, int $orderId): bool {
    $sql  = "SELECT id FROM reviews WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return (bool)$row;
}

function review_create($conn, int $buyerId, int $productId, int $orderId, int $rating, string $comment): void {
    $sql  = "INSERT INTO reviews (buyer_id, product_id, order_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiiis", $buyerId, $productId, $orderId, $rating, $comment);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function review_list_by_buyer($conn, int $buyerId) {
    $sql  = "SELECT r.*, p.name AS product_name FROM reviews r
             JOIN products p ON p.id = r.product_id
             WHERE r.buyer_id = ? ORDER BY r.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $buyerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function review_eligible_orders($conn, int $buyerId) {
    $sql  = "SELECT o.id, p.name FROM orders o
             JOIN products p ON p.id = o.product_id
             LEFT JOIN reviews r ON r.order_id = o.id
             WHERE o.buyer_id = ? AND o.status IN ('paid','shipped','completed') AND r.id IS NULL";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $buyerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function revenue_monthly($conn, int $months = 12) {
    $sql = "SELECT DATE_FORMAT(paid_at, '%Y-%m') AS ym, SUM(amount) AS total
            FROM payments WHERE payment_status = 'success'
            GROUP BY ym ORDER BY ym DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $months);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function revenue_total($conn): float {
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM payments WHERE payment_status = 'success'"));
    return (float)($row['total'] ?? 0);
}
?>
