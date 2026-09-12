<?php
function delivery_find_owned($conn, int $id, int $deliveryManId): ?array {
    $sql  = "SELECT d.*, p.name AS product_name FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN products p ON p.id = o.product_id
             WHERE d.id = ? AND d.delivery_man_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $deliveryManId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function delivery_create($conn, int $orderId, ?int $driverId, int $deliveryManId, string $scheduledDate): int {
    $sql  = "INSERT INTO deliveries (order_id, driver_id, delivery_man_id, scheduled_date, status)
             VALUES (?, ?, ?, ?, 'assigned')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiis", $orderId, $driverId, $deliveryManId, $scheduledDate);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $newId;
}

function delivery_update($conn, int $id, int $deliveryManId, string $status, string $scheduledDate, string $notes): bool {
    $sql  = "UPDATE deliveries SET status = ?, scheduled_date = ?, notes = ? WHERE id = ? AND delivery_man_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssii", $status, $scheduledDate, $notes, $id, $deliveryManId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delivery_delete($conn, int $id, int $deliveryManId): bool {
    $sql  = "DELETE FROM deliveries WHERE id = ? AND delivery_man_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $deliveryManId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function delivery_list_by_manager($conn, int $deliveryManId) {
    $sql  = "SELECT d.*, o.id AS order_num, p.name AS product_name, dr.name AS driver_name
             FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN products p ON p.id = o.product_id
             LEFT JOIN drivers dr ON dr.id = d.driver_id
             WHERE d.delivery_man_id = ? ORDER BY d.scheduled_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $deliveryManId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function delivery_search_by_manager($conn, int $deliveryManId, string $term) {
    $like = "%" . $term . "%";
    $sql  = "SELECT d.*, o.id AS order_num, p.name AS product_name, dr.name AS driver_name
             FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN products p ON p.id = o.product_id
             LEFT JOIN drivers dr ON dr.id = d.driver_id
             WHERE d.delivery_man_id = ? AND p.name LIKE ?
             ORDER BY d.scheduled_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $deliveryManId, $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function delivery_list_active_for_schedule($conn, int $deliveryManId) {
    $sql  = "SELECT d.*, p.name AS product_name, dr.name AS driver_name FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN products p ON p.id = o.product_id
             LEFT JOIN drivers dr ON dr.id = d.driver_id
             WHERE d.delivery_man_id = ? AND d.status <> 'cancelled'
             ORDER BY d.scheduled_date";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $deliveryManId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function delivery_list_assignable($conn, int $deliveryManId) {
    $sql  = "SELECT d.*, p.name AS product_name FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN products p ON p.id = o.product_id
             WHERE d.delivery_man_id = ? AND d.status IN ('assigned','in_transit')
             ORDER BY d.scheduled_date";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $deliveryManId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function delivery_assign_driver($conn, int $deliveryId, int $deliveryManId, int $driverId): bool {
    $sql  = "UPDATE deliveries SET driver_id = ? WHERE id = ? AND delivery_man_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $driverId, $deliveryId, $deliveryManId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function driver_list_all($conn) {
    return mysqli_query($conn, "SELECT * FROM drivers ORDER BY name");
}

function driver_list_available($conn) {
    return mysqli_query($conn, "SELECT * FROM drivers WHERE status = 'available' ORDER BY name");
}

function driver_find_available($conn, int $id): ?array {
    $sql  = "SELECT * FROM drivers WHERE id = ? AND status = 'available'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function driver_set_status($conn, int $id, string $status): void {
    $sql  = "UPDATE drivers SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function driver_history($conn, int $driverId) {
    $sql  = "SELECT d.*, p.name AS product_name FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN products p ON p.id = o.product_id
             WHERE d.driver_id = ? AND d.status = 'delivered'
             ORDER BY d.scheduled_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $driverId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
?>
