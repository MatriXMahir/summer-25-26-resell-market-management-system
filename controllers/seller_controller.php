<?php
$sellerId = (int)$_SESSION['user_id'];

if ( $action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    verify_csrf();

    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock_qty'] ?? '';
    $errors = [];

    if (empty($name)) {
        $errors[] = "Product name is required.";
    } elseif (strlen($name) > 150) {
        $errors[] = "Product name is too long.";
    }
    if (!is_numeric($price) || (float)$price <= 0) {
        $errors[] = "Price must be a positive number.";
    }
    if ($stock === '' || !ctype_digit((string)$stock) || (int)$stock < 0) {
        $errors[] = "Stock quantity must be a non-negative whole number.";
    }

    if (!empty($errors)) {
        $_SESSION['product_errors'] = $errors;
        $_SESSION['product_old'] = ['id' => $id, 'name' => $name, 'description' => $desc, 'price' => $price, 'stock_qty' => $stock];
        header("Location: " . BASE_URL . "?page=seller" . ($id > 0 ? "&action=edit&id=$id" : ""));
        exit;
    }

    if ($id > 0) {
        product_update($conn, $id, $sellerId, $name, $desc, (float)$price, (int)$stock);
        flash_set('success', 'Product updated and resent for approval.');
    } else {
        product_create($conn, $sellerId, $name, $desc, (float)$price, (int)$stock);
        flash_set('success', 'Product submitted for admin approval.');
    }
    header("Location: " . BASE_URL . "?page=seller");
    exit;
}

if ($action === 'delete') {
    verify_csrf();
    $id = (int)($_GET['id'] ?? 0);
    if (product_delete($conn, $id, $sellerId)) {
        flash_set('success', 'Product deleted.');
    } else {
        flash_set('error', 'Product not found.');
    }
    header("Location: " . BASE_URL . "?page=seller");
    exit;
}

if ($action === 'stock_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['product_id'] ?? 0);
    $newQty = $_POST['new_qty'] ?? '';
    $errors = [];

    $product = product_find_owned($conn, $id, $sellerId);
    if (!$product) $errors[] = "Product not found.";
    if ($newQty === '' || !ctype_digit((string)$newQty) || (int)$newQty < 0) {
        $errors[] = "New quantity must be a non-negative whole number.";
    }

    if (!empty($errors)) {
        $_SESSION['stock_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=seller&action=stock_form");
        exit;
    }

    $newQty = (int)$newQty;
    product_update_stock($conn, $id, $sellerId, $newQty);

    if ($newQty < LOW_STOCK) {
        $label = $newQty === 0 ? "is now OUT OF STOCK" : "is running low ($newQty left)";
        notification_create($conn, $sellerId, $id, "'" . $product['name'] . "' $label.");
    }

    flash_set('success', "Stock status for '" . $product['name'] . "' updated to $newQty.");
    header("Location: " . BASE_URL . "?page=seller&action=stock_form");
    exit;
}

if ($action === 'invoice_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int)($_POST['order_id'] ?? 0);
    $errors = [];

    $order = order_belongs_to_seller($conn, $orderId, $sellerId);
    if (!$order) {
        $errors[] = "Order not found or not yet paid.";
    } elseif (invoice_exists_for_order($conn, $orderId)) {
        $errors[] = "An invoice for this order already exists.";
    }

    if (!empty($errors)) {
        $_SESSION['invoice_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=seller&action=invoice_form");
        exit;
    }

    $invoiceNumber = "INV-" . date('Ymd') . "-" . $orderId;
    invoice_create($conn, $orderId, $sellerId, $invoiceNumber, (float)$order['total_price']);
    flash_set('success', "Invoice $invoiceNumber generated.");
    header("Location: " . BASE_URL . "?page=seller&action=invoice_form");
    exit;
}

if ($action === 'notif_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    notification_mark_read($conn, (int)($_POST['notif_id'] ?? 0), $sellerId);
    header("Location: " . BASE_URL . "?page=seller&action=notifications");
    exit;
}

if ($action === 'stock_form') {
    $products = product_list_by_seller($conn, $sellerId);
    $stockErrors = $_SESSION['stock_errors'] ?? [];
    unset($_SESSION['stock_errors']);
    require __DIR__ . '/../views/seller/stock.php';
    exit;
}

if ($action === 'invoice_form') {
    $invoiceableOrders = product_invoiceable_orders($conn, $sellerId);
    $invoices = invoice_list_by_seller($conn, $sellerId);
    $invoiceErrors = $_SESSION['invoice_errors'] ?? [];
    unset($_SESSION['invoice_errors']);
    require __DIR__ . '/../views/seller/invoice.php';
    exit;
}

if ($action === 'notifications') {
    $notifications = notification_list($conn, $sellerId);
    require __DIR__ . '/../views/seller/notifications.php';
    exit;
}

$editProduct = null;
if ($action === 'edit') {
    $editProduct = product_find_owned($conn, (int)($_GET['id'] ?? 0), $sellerId);
}

$searchTerm = trim($_GET['q'] ?? '');
$products = $searchTerm !== '' ? product_search_by_seller($conn, $sellerId, $searchTerm) : product_list_by_seller($conn, $sellerId);

$productErrors = $_SESSION['product_errors'] ?? [];
$oldValues = $_SESSION['product_old'] ?? $editProduct ?? ['id' => 0, 'name' => '', 'description' => '', 'price' => '', 'stock_qty' => ''];
unset($_SESSION['product_errors'], $_SESSION['product_old']);

$unreadCount = notification_count_unread($conn, $sellerId);

require __DIR__ . '/../views/seller/dashboard.php';
?>
