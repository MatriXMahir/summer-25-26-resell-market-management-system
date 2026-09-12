<?php
header('Content-Type: application/json');

$role = $_SESSION['role'] ?? '';
$term = trim($_GET['q'] ?? '');

if ($action === 'autocomplete_products') {
    if ($role !== 'buyer') { http_response_code(403); echo json_encode([]); exit; }

    if ($term === '') { echo json_encode([]); exit; }

    $result = product_autocomplete_names($conn, $term);
    $names = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $names[] = $row['name'];
    }
    echo json_encode($names);
    exit;
}

if ($action === 'search_users') {
    if ($role !== 'admin') { http_response_code(403); echo json_encode([]); exit; }

    $result = $term !== '' ? user_search($conn, $term) : user_list_all($conn);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'id'        => (int)$row['id'],
            'full_name' => $row['full_name'],
            'email'     => $row['email'],
            'role'      => $row['role'],
            'status'    => $row['status'],
        ];
    }
    echo json_encode($rows);
    exit;
}

if ($action === 'search_products') {
    if ($role !== 'seller') { http_response_code(403); echo json_encode([]); exit; }

    $sellerId = (int)$_SESSION['user_id'];
    $result = $term !== '' ? product_search_by_seller($conn, $sellerId, $term) : product_list_by_seller($conn, $sellerId);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'id'        => (int)$row['id'],
            'name'      => $row['name'],
            'price'     => (float)$row['price'],
            'stock_qty' => (int)$row['stock_qty'],
            'status'    => $row['status'],
        ];
    }
    echo json_encode($rows);
    exit;
}

if ($action === 'search_orders') {
    if ($role !== 'buyer') { http_response_code(403); echo json_encode([]); exit; }

    $buyerId = (int)$_SESSION['user_id'];
    $result = $term !== '' ? order_search_by_buyer($conn, $buyerId, $term) : order_list_by_buyer($conn, $buyerId);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'id'           => (int)$row['id'],
            'product_name' => $row['product_name'],
            'quantity'     => (int)$row['quantity'],
            'total_price'  => (float)$row['total_price'],
            'status'       => $row['status'],
        ];
    }
    echo json_encode($rows);
    exit;
}

if ($action === 'search_deliveries') {
    if ($role !== 'delivery') { http_response_code(403); echo json_encode([]); exit; }

    $deliveryManId = (int)$_SESSION['user_id'];
    $result = $term !== '' ? delivery_search_by_manager($conn, $deliveryManId, $term) : delivery_list_by_manager($conn, $deliveryManId);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = [
            'id'            => (int)$row['id'],
            'order_num'     => (int)$row['order_num'],
            'product_name'  => $row['product_name'],
            'driver_name'   => $row['driver_name'],
            'status'        => $row['status'],
            'scheduled_date'=> $row['scheduled_date'],
        ];
    }
    echo json_encode($rows);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Unknown AJAX action']);
?>
