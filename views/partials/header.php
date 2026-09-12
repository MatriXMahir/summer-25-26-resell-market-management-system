<?php
$activePage = $activePage ?? '';
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Resell Market</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/app.js"></script>
</head>
<body>
<div class="navbar">
    <div class="brand">Resell Market <span><?= esc(ucfirst($role)) ?></span></div>
    <div class="nav-links">
        <?php if ($role === 'admin'): ?>
            <a href="index.php?page=admin" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Users</a>
            <a href="index.php?page=admin&action=ban_form" class="<?= $activePage === 'ban' ? 'active' : '' ?>">Ban User</a>
            <a href="index.php?page=admin&action=products" class="<?= $activePage === 'products' ? 'active' : '' ?>">Product Approval</a>
            <a href="index.php?page=admin&action=revenue" class="<?= $activePage === 'revenue' ? 'active' : '' ?>">Revenue</a>
        <?php elseif ($role === 'seller'): ?>
            <a href="index.php?page=seller" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Products</a>
            <a href="index.php?page=seller&action=stock_form" class="<?= $activePage === 'stock' ? 'active' : '' ?>">Stock</a>
            <a href="index.php?page=seller&action=invoice_form" class="<?= $activePage === 'invoice' ? 'active' : '' ?>">Invoices</a>
            <a href="index.php?page=seller&action=notifications" class="<?= $activePage === 'notifications' ? 'active' : '' ?>">
                Notifications
                <?php if (!empty($unreadCount)): ?><span class="pill"><?= (int)$unreadCount ?></span><?php endif; ?>
            </a>
        <?php elseif ($role === 'buyer'): ?>
            <a href="index.php?page=buyer" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">My Orders</a>
            <a href="index.php?page=buyer&action=browse" class="<?= $activePage === 'browse' ? 'active' : '' ?>">Browse Products</a>
            <a href="index.php?page=buyer&action=payment" class="<?= $activePage === 'payment' ? 'active' : '' ?>">Payments</a>
            <a href="index.php?page=buyer&action=review" class="<?= $activePage === 'review' ? 'active' : '' ?>">Reviews</a>
        <?php elseif ($role === 'delivery'): ?>
            <a href="index.php?page=delivery" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">Deliveries</a>
            <a href="index.php?page=delivery&action=assign" class="<?= $activePage === 'assign' ? 'active' : '' ?>">Assign Driver</a>
            <a href="index.php?page=delivery&action=schedule" class="<?= $activePage === 'schedule' ? 'active' : '' ?>">Schedule</a>
            <a href="index.php?page=delivery&action=history" class="<?= $activePage === 'history' ? 'active' : '' ?>">Driver History</a>
        <?php endif; ?>
    </div>
    <div class="user-box">
        <span>Hi, <?= esc($_SESSION['full_name'] ?? '') ?></span>
        <a href="index.php?page=logout" class="logout-btn">Logout</a>
    </div>
</div>
<div class="container">
<?php $flash = flash_get(); if ($flash): ?>
    <div class="alert alert-<?= esc($flash['type']) ?>"><?= esc($flash['message']) ?></div>
<?php endif; ?>
