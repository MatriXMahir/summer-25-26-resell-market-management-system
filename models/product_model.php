<?php
function product_find($conn, int $id): ?array {
    $sql  = "SELECT * FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;

}

function product_find_owned($conn, int $id, int $sellerId): ?array {
    $sql  = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $sellerId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;

}


function product_create($conn, int $sellerId, string $name, string $desc, float $price, int $stock): int {
    $sql  = "INSERT INTO products (seller_id, name, description, price, stock_qty, status)
             VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issdi", $sellerId, $name, $desc, $price, $stock);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $newId;

}


function product_update($conn, int $id, int $sellerId, string $name, string $desc, float $price, int $stock): bool {
    // Editing sends it back to pending so the admin re-checks the change.
    $sql  = "UPDATE products SET name = ?, description = ?, price = ?, stock_qty = ?, status = 'pending'
             WHERE id = ? AND seller_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssdiii", $name, $desc, $price, $stock, $id, $sellerId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;

}

function product_delete($conn, int $id, int $sellerId): bool {
    $sql  = "DELETE FROM products WHERE id = ? AND seller_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $sellerId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;

}

function product_list_by_seller($conn, int $sellerId) {
    $sql  = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $sellerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function product_search_by_seller($conn, int $sellerId, string $term) {
    $like = "%" . $term . "%";
    $sql  = "SELECT * FROM products WHERE seller_id = ? AND name LIKE ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $sellerId, $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);

}

function product_update_stock($conn, int $id, int $sellerId, int $newQty): bool {
    $sql  = "UPDATE products SET stock_qty = ? WHERE id = ? AND seller_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $newQty, $id, $sellerId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
    
}

function product_decrement_stock($conn, int $id, int $qty): void {
    $sql  = "UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $qty, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function product_increment_stock($conn, int $id, int $qty): void {
    $sql  = "UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $qty, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function product_list_approved($conn) {
    $sql = "SELECT p.*, u.full_name AS seller_name FROM products p
            JOIN users u ON u.id = p.seller_id
            WHERE p.status = 'approved' ORDER BY p.created_at DESC";
    return mysqli_query($conn, $sql);
}

function product_search_approved($conn, string $term) {
    $like = "%" . $term . "%";
    $sql  = "SELECT p.*, u.full_name AS seller_name FROM products p
             JOIN users u ON u.id = p.seller_id
             WHERE p.status = 'approved' AND p.name LIKE ?
             ORDER BY p.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function product_autocomplete_names($conn, string $term, int $limit = 8) {
    $like = "%" . $term . "%";
    $sql  = "SELECT DISTINCT name FROM products WHERE status = 'approved' AND name LIKE ?
             ORDER BY name LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $like, $limit);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function product_list_pending($conn) {
    $sql = "SELECT p.*, u.full_name AS seller_name FROM products p
            JOIN users u ON u.id = p.seller_id
            WHERE p.status = 'pending' ORDER BY p.created_at";
    return mysqli_query($conn, $sql);
}

function product_list_recently_reviewed($conn, int $limit = 20) {
    $sql = "SELECT p.*, u.full_name AS seller_name FROM products p
            JOIN users u ON u.id = p.seller_id
            WHERE p.status <> 'pending' ORDER BY p.created_at DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function product_approve($conn, int $id): bool {
    $sql  = "UPDATE products SET status = 'approved' WHERE id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function product_reject($conn, int $id): bool {
    $sql  = "UPDATE products SET status = 'rejected' WHERE id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function notification_create($conn, int $sellerId, int $productId, string $message): void {
    $sql  = "INSERT INTO notifications (seller_id, product_id, message) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $sellerId, $productId, $message);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function notification_list($conn, int $sellerId) {
    $sql  = "SELECT * FROM notifications WHERE seller_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $sellerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function notification_count_unread($conn, int $sellerId): int {
    $sql  = "SELECT COUNT(*) AS c FROM notifications WHERE seller_id = ? AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $sellerId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return (int)$row['c'];
}

function notification_mark_read($conn, int $id, int $sellerId): bool {
    $sql  = "UPDATE notifications SET is_read = 1 WHERE id = ? AND seller_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $sellerId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function invoice_exists_for_order($conn, int $orderId): bool {
    $sql  = "SELECT id FROM invoices WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return (bool)$row;
}

function invoice_create($conn, int $orderId, int $sellerId, string $invoiceNumber, float $amount): void {
    $sql  = "INSERT INTO invoices (order_id, seller_id, invoice_number, amount) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisd", $orderId, $sellerId, $invoiceNumber, $amount);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function invoice_list_by_seller($conn, int $sellerId) {
    $sql  = "SELECT i.*, o.quantity, p.name AS product_name FROM invoices i
             JOIN orders o ON o.id = i.order_id
             JOIN products p ON p.id = o.product_id
             WHERE i.seller_id = ? ORDER BY i.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $sellerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function product_invoiceable_orders($conn, int $sellerId) {
    $sql  = "SELECT o.id, o.total_price, p.name FROM orders o
             JOIN products p ON p.id = o.product_id
             LEFT JOIN invoices i ON i.order_id = o.id
             WHERE p.seller_id = ? AND o.status IN ('paid','shipped','completed') AND i.id IS NULL";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $sellerId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function order_belongs_to_seller($conn, int $orderId, int $sellerId): ?array {
    $sql  = "SELECT o.* FROM orders o JOIN products p ON p.id = o.product_id
             WHERE o.id = ? AND p.seller_id = ? AND o.status IN ('paid','shipped','completed')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $orderId, $sellerId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}
?>
