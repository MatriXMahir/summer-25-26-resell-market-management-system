<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/helpers.php';

require_once __DIR__ . '/models/user_model.php';
require_once __DIR__ . '/models/product_model.php';
require_once __DIR__ . '/models/order_model.php';
require_once __DIR__ . '/models/delivery_model.php';

check_session_timeout();

$page   = $_GET['page']   ?? 'login';
$action = $_GET['action'] ?? 'index';

switch ($page) {

    case 'login':
    case 'register':
    case 'logout':
        require __DIR__ . '/controllers/auth_controller.php';
        break;

    case 'admin':
        require_role('admin');
        require __DIR__ . '/controllers/admin_controller.php';
        break;

    case 'seller':
        require_role('seller');
        require __DIR__ . '/controllers/seller_controller.php';
        break;

    case 'buyer':
        require_role('buyer');
        require __DIR__ . '/controllers/buyer_controller.php';
        break;

    case 'delivery':
        require_role('delivery');
        require __DIR__ . '/controllers/delivery_controller.php';
        break;

    case 'ajax':
        require_login(); // each action inside re-checks role as needed
        require __DIR__ . '/controllers/ajax_controller.php';
        break;

    default:
        http_response_code(404);
        echo "Page not found.";
}
?>
