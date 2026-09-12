<?php
$buyerId = (int)$_SESSION['user_id'];

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $productId = (int)($_POST['product_id'] ?? 0);
    $qtyRaw    = $_POST['quantity'] ?? '';
    $errors    = [];

    $product = product_find($conn, $productId);
    if (!$product || $product['status'] !== 'approved') {
        $errors[] = "Product not found.";
    } elseif ($qtyRaw === '' || !ctype_digit((string)$qtyRaw)) {
        $errors[] = "Quantity must be a whole number.";
    } else {
        $qty = (int)$qtyRaw;
        if ($qty < 1) {
            $errors[] = "Quantity must be at least 1.";
        } elseif ($qty > (int)$product['stock_qty']) {
            $errors[] = "Only " . (int)$product['stock_qty'] . " unit(s) left in stock.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['order_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=buyer&action=browse&product_id=$productId");
        exit;
    }

    $total = $qty * (float)$product['price'];
    order_create($conn, $buyerId, $productId, $qty, $total);
    product_decrement_stock($conn, $productId, $qty);

    $newStock = (int)$product['stock_qty'] - $qty;
    if ($newStock < LOW_STOCK) {
        $label = $newStock === 0 ? "is now OUT OF STOCK" : "is running low ($newStock left)";
        notification_create($conn, (int)$product['seller_id'], $productId, "'" . $product['name'] . "' $label.");
    }

    flash_set('success', 'Order placed successfully.');
    header("Location: " . BASE_URL . "?page=buyer");
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = (int)($_POST['id'] ?? 0);
    $qtyRaw = $_POST['quantity'] ?? '';
    $errors = [];

    $order = order_find_owned($conn, $id, $buyerId);
    if (!$order || $order['status'] !== 'pending') {
        $errors[] = "Order not found or no longer editable.";
    } else {
        $availableStock = (int)$order['stock_qty'] + (int)$order['quantity'];
        if ($qtyRaw === '' || !ctype_digit((string)$qtyRaw)) {
            $errors[] = "Quantity must be a whole number.";
        } else {
            $qty = (int)$qtyRaw;
            if ($qty < 1) {
                $errors[] = "Quantity must be at least 1.";
            } elseif ($qty > $availableStock) {
                $errors[] = "Only $availableStock unit(s) available.";
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['order_edit_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=buyer&action=edit&id=$id");
        exit;
    }

    $diff = $qty - (int)$order['quantity'];
    $newTotal = $qty * (float)$order['price'];
    order_update_quantity($conn, $id, $buyerId, $qty, $newTotal);
    if ($diff > 0) {
        product_decrement_stock($conn, $order['product_id'], $diff);
    } elseif ($diff < 0) {
        product_increment_stock($conn, $order['product_id'], -$diff);
    }

    flash_set('success', 'Order updated.');
    header("Location: " . BASE_URL . "?page=buyer");
    exit;
}

if ($action === 'delete') {
    verify_csrf();
    $id = (int)($_GET['id'] ?? 0);
    $order = order_find_owned($conn, $id, $buyerId);

    if (!$order || $order['status'] !== 'pending') {
        flash_set('error', 'Order not found or no longer cancellable.');
    } else {
        product_increment_stock($conn, $order['product_id'], (int)$order['quantity']);
        order_delete($conn, $id, $buyerId);
        flash_set('success', 'Order cancelled.');
    }
    header("Location: " . BASE_URL . "?page=buyer");
    exit;
}

if ($action === 'payment_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int)($_POST['order_id'] ?? 0);
    $method  = $_POST['method'] ?? '';
    $errors  = [];
    $allowedMethods = ['bkash', 'nagad', 'card', 'cod'];

    $order = order_find_owned($conn, $orderId, $buyerId);
    if (!$order || $order['status'] !== 'pending') {
        $errors[] = "Order not found or already paid.";
    }
    if (empty($method) || !in_array($method, $allowedMethods, true)) {
        $errors[] = "Please choose a valid payment method.";
    }

    if (!empty($errors)) {
        $_SESSION['payment_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=buyer&action=payment");
        exit;
    }

    payment_create($conn, $orderId, $buyerId, (float)$order['total_price'], $method);
    order_update_status($conn, $orderId, 'paid');
    flash_set('success', 'Payment successful! Your order is now marked as paid.');
    header("Location: " . BASE_URL . "?page=buyer&action=payment");
    exit;
}

if ($action === 'review_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int)($_POST['order_id'] ?? 0);
    $rating  = $_POST['rating'] ?? '';
    $comment = trim($_POST['comment'] ?? '');
    $errors  = [];

    $order = order_find_owned($conn, $orderId, $buyerId);
    if (!$order || !in_array($order['status'], ['paid', 'shipped', 'completed'], true)) {
        $errors[] = "That order is not eligible for review.";
    } elseif (review_exists_for_order($conn, $orderId)) {
        $errors[] = "You already reviewed this order.";
    }
    if ($rating === '' || !ctype_digit((string)$rating) || (int)$rating < 1 || (int)$rating > 5) {
        $errors[] = "Rating must be a whole number between 1 and 5.";
    }
    if (empty($comment)) {
        $errors[] = "Please write a short comment.";
    } elseif (strlen($comment) > 500) {
        $errors[] = "Comment must be under 500 characters.";
    }

    if (!empty($errors)) {
        $_SESSION['review_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=buyer&action=review");
        exit;
    }

    review_create($conn, $buyerId, (int)$order['product_id'], $orderId, (int)$rating, $comment);
    flash_set('success', 'Thanks for your review!');
    header("Location: " . BASE_URL . "?page=buyer&action=review");
    exit;
}

if ($action === 'browse') {
    $selectedProductId = (int)($_GET['product_id'] ?? 0);
    $selectedProduct = $selectedProductId > 0 ? product_find($conn, $selectedProductId) : null;
    $term = trim($_GET['q'] ?? '');
    $products = $term !== '' ? product_search_approved($conn, $term) : product_list_approved($conn);
    $orderErrors = $_SESSION['order_errors'] ?? [];
    unset($_SESSION['order_errors']);
    require __DIR__ . '/../views/buyer/browse.php';
    exit;
}

if ($action === 'payment') {
    $pendingOrders = order_list_pending_for_buyer($conn, $buyerId);
    $paymentHistory = payment_list_by_buyer($conn, $buyerId);
    $paymentErrors = $_SESSION['payment_errors'] ?? [];
    unset($_SESSION['payment_errors']);
    require __DIR__ . '/../views/buyer/payment.php';
    exit;
}

if ($action === 'review') {
    $eligibleOrders = review_eligible_orders($conn, $buyerId);
    $myReviews = review_list_by_buyer($conn, $buyerId);
    $reviewErrors = $_SESSION['review_errors'] ?? [];
    unset($_SESSION['review_errors']);
    require __DIR__ . '/../views/buyer/review.php';
    exit;
}

$editOrder = null;
if ($action === 'edit') {
    $editOrder = order_find_owned($conn, (int)($_GET['id'] ?? 0), $buyerId);
}

$searchTerm = trim($_GET['q'] ?? '');
$orders = $searchTerm !== '' ? order_search_by_buyer($conn, $buyerId, $searchTerm) : order_list_by_buyer($conn, $buyerId);

$orderEditErrors = $_SESSION['order_edit_errors'] ?? [];
unset($_SESSION['order_edit_errors']);

require __DIR__ . '/../views/buyer/dashboard.php';
?>
