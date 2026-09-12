<?php


$deliveryManId = (int)$_SESSION['user_id'];

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $orderId       = (int)($_POST['order_id'] ?? 0);
    $driverId      = (int)($_POST['driver_id'] ?? 0);
    $scheduledDate = trim($_POST['scheduled_date'] ?? '');
    $errors        = [];

    $orderOk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM orders WHERE id = " . $orderId . " AND status IN ('paid','shipped')"));
    if (!$orderOk) $errors[] = "Please choose a valid paid order.";

    $dateParts = explode('-', $scheduledDate);
    if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
        $errors[] = "Scheduled date must be a valid date in YYYY-MM-DD format.";
    }

    if (!empty($errors)) {
        $_SESSION['delivery_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=delivery&action=create");
        exit;
    }

    delivery_create($conn, $orderId, $driverId > 0 ? $driverId : null, $deliveryManId, $scheduledDate);
    if ($driverId > 0) {
        driver_set_status($conn, $driverId, 'busy');
    }
    order_update_status($conn, $orderId, 'shipped');

    flash_set('success', 'Delivery assigned.');
    header("Location: " . BASE_URL . "?page=delivery");
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id            = (int)($_POST['id'] ?? 0);
    $status        = $_POST['status'] ?? '';
    $scheduledDate = trim($_POST['scheduled_date'] ?? '');
    $notes         = trim($_POST['notes'] ?? '');
    $errors        = [];
    $allowedStatus = ['assigned', 'in_transit', 'delivered'];

    $delivery = delivery_find_owned($conn, $id, $deliveryManId);
    if (!$delivery) $errors[] = "Delivery not found.";
    if (!in_array($status, $allowedStatus, true)) $errors[] = "Invalid status.";
    $dateParts = explode('-', $scheduledDate);
    if (count($dateParts) !== 3 || !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])) {
        $errors[] = "Scheduled date must be a valid date in YYYY-MM-DD format.";
    }

    if (!empty($errors)) {
        $_SESSION['delivery_edit_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=delivery&action=edit&id=$id");
        exit;
    }

    delivery_update($conn, $id, $deliveryManId, $status, $scheduledDate, $notes);

    if ($status === 'delivered') {
        order_update_status($conn, (int)$delivery['order_id'], 'completed');
        if ($delivery['driver_id']) {
            driver_set_status($conn, (int)$delivery['driver_id'], 'available');
        }
    }

    flash_set('success', 'Delivery updated.');
    header("Location: " . BASE_URL . "?page=delivery");
    exit;
}

if ($action === 'delete') {
    verify_csrf();
    $id = (int)($_GET['id'] ?? 0);
    $delivery = delivery_find_owned($conn, $id, $deliveryManId);

    if (!$delivery) {
        flash_set('error', 'Delivery not found.');
    } else {
        if ($delivery['driver_id']) {
            driver_set_status($conn, (int)$delivery['driver_id'], 'available');
        }
        delivery_delete($conn, $id, $deliveryManId);
        flash_set('success', 'Delivery deleted.');
    }
    header("Location: " . BASE_URL . "?page=delivery");
    exit;
}

if ($action === 'assign_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $deliveryId = (int)($_POST['delivery_id'] ?? 0);
    $driverId   = (int)($_POST['driver_id'] ?? 0);
    $errors     = [];

    $delivery = delivery_find_owned($conn, $deliveryId, $deliveryManId);
    $driver   = driver_find_available($conn, $driverId);

    if (!$delivery) $errors[] = "Delivery not found.";
    if (!$driver) $errors[] = "Please choose an available driver.";

    if (!empty($errors)) {
        $_SESSION['assign_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=delivery&action=assign");
        exit;
    }

    if (!empty($delivery['driver_id'])) {
        driver_set_status($conn, (int)$delivery['driver_id'], 'available');
    }
    delivery_assign_driver($conn, $deliveryId, $deliveryManId, $driverId);
    driver_set_status($conn, $driverId, 'busy');

    flash_set('success', "Driver '" . $driver['name'] . "' assigned.");
    header("Location: " . BASE_URL . "?page=delivery&action=assign");
    exit;
}

if ($action === 'assign') {
    $assignableDeliveries = delivery_list_assignable($conn, $deliveryManId);
    $allDrivers = driver_list_all($conn);
    $assignErrors = $_SESSION['assign_errors'] ?? [];
    unset($_SESSION['assign_errors']);
    require __DIR__ . '/../views/delivery/assign_driver.php';
    exit;
}

if ($action === 'schedule') {
    $result = delivery_list_active_for_schedule($conn, $deliveryManId);
    $groups = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $groups[$row['scheduled_date']][] = $row;
    }
    require __DIR__ . '/../views/delivery/schedule.php';
    exit;
}

if ($action === 'history') {
    $allDrivers = driver_list_all($conn);
    $selectedDriver = (int)($_GET['driver_id'] ?? 0);
    $history = $selectedDriver > 0 ? driver_history($conn, $selectedDriver) : null;
    require __DIR__ . '/../views/delivery/driver_history.php';
    exit;
}

$editDelivery = null;
if ($action === 'edit') {
    $editDelivery = delivery_find_owned($conn, (int)($_GET['id'] ?? 0), $deliveryManId);
}
if ($action === 'create') {
    $availableOrders = order_awaiting_delivery($conn);
    $availableDrivers = driver_list_available($conn);
    $createErrors = $_SESSION['delivery_errors'] ?? [];
    unset($_SESSION['delivery_errors']);
}

$searchTerm = trim($_GET['q'] ?? '');
$deliveries = $searchTerm !== '' ? delivery_search_by_manager($conn, $deliveryManId, $searchTerm) : delivery_list_by_manager($conn, $deliveryManId);

require __DIR__ . '/../views/delivery/dashboard.php';
?>
