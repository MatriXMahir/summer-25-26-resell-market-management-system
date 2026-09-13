<?php
$adminId = (int)$_SESSION['user_id'];

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id       = (int)($_POST['id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $errors   = [];
    $allowedRoles = ['buyer', 'seller', 'delivery', 'admin'];

    if (empty($fullName)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (!in_array($role, $allowedRoles, true)) $errors[] = "Invalid role.";
    if ($id === 0 && strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if (empty($errors) && user_email_exists($conn, $email, $id)) $errors[] = "Email already registered to another user.";
    if ($id > 0 && is_self($id) && $role !== 'admin') $errors[] = "You cannot change your own account away from admin.";

    if (!empty($errors)) {
        $_SESSION['admin_errors'] = $errors;
        $_SESSION['admin_old'] = ['id' => $id, 'full_name' => $fullName, 'email' => $email, 'role' => $role];
        header("Location: " . BASE_URL . "?page=admin" . ($id > 0 ? "&action=edit&id=$id" : ""));
        exit;
    }

    if ($id > 0) {
        user_update($conn, $id, $fullName, $email, $role);
        flash_set('success', 'User updated.');
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        user_create($conn, $fullName, $email, $hash, $role, 'active');
        flash_set('success', 'User created.');
    }
    header("Location: " . BASE_URL . "?page=admin");
    exit;
}

if ($action === 'delete') {
    verify_csrf();
    $id = (int)($_GET['id'] ?? 0);

    if (is_self($id)) {
        flash_set('error', 'You cannot delete your own account.');
    } else {
        $target = user_find_by_id($conn, $id);
        if (!$target) {
            flash_set('error', 'User not found.');
        } elseif ($target['role'] === 'admin') {
            flash_set('error', 'Cannot delete an admin account.');
        } else {
            user_delete($conn, $id);
            flash_set('success', 'User deleted.');
        }
    }
    header("Location: " . BASE_URL . "?page=admin");
    exit;
}

if ($action === 'approve') {
    verify_csrf();
    user_approve($conn, (int)($_GET['id'] ?? 0));
    flash_set('success', 'User approved.');
    header("Location: " . BASE_URL . "?page=admin");
    exit;
}

if ($action === 'ban_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['user_id'] ?? 0);
    $days = $_POST['days'] ?? '';
    $errors = [];

    $target = user_find_by_id($conn, $id);
    if (is_self($id)) {
        $errors[] = "You cannot ban your own account.";
    } elseif (!$target || $target['role'] === 'admin') {
        $errors[] = "User not found or cannot be banned.";
    }
    if ($days === '' || !ctype_digit((string)$days) || (int)$days < 1 || (int)$days > 365) {
        $errors[] = "Ban duration must be a whole number of days between 1 and 365.";
    }

    if (!empty($errors)) {
        $_SESSION['ban_errors'] = $errors;
        header("Location: " . BASE_URL . "?page=admin&action=ban_form");
        exit;
    }

    $banUntil = date('Y-m-d H:i:s', strtotime('+' . (int)$days . ' days'));
    user_ban($conn, $id, $banUntil);
    flash_set('success', "'" . $target['full_name'] . "' has been banned until " . date('d M Y', strtotime($banUntil)) . ".");
    header("Location: " . BASE_URL . "?page=admin&action=ban_form");
    exit;
}

if ($action === 'unban') {
    verify_csrf();
    user_unban($conn, (int)($_GET['id'] ?? 0));
    flash_set('success', 'User unbanned.');
    header("Location: " . BASE_URL . "?page=admin&action=ban_form");
    exit;
}

if ($action === 'product_approve') {
    verify_csrf();
    product_approve($conn, (int)($_GET['id'] ?? 0));
    flash_set('success', 'Product approved.');
    header("Location: " . BASE_URL . "?page=admin&action=products");
    exit;
}

if ($action === 'product_reject') {
    verify_csrf();
    product_reject($conn, (int)($_GET['id'] ?? 0));
    flash_set('success', 'Product rejected.');
    header("Location: " . BASE_URL . "?page=admin&action=products");
    exit;
}

if ($action === 'ban_form') {
    $activeUsers = user_list_active_non_admin($conn);
    $bannedUsers = user_list_banned($conn);
    $banErrors = $_SESSION['ban_errors'] ?? [];
    unset($_SESSION['ban_errors']);
    require __DIR__ . '/../views/admin/ban.php';
    exit;
}

if ($action === 'products') {
    $pending  = product_list_pending($conn);
    $reviewed = product_list_recently_reviewed($conn);
    require __DIR__ . '/../views/admin/product_approval.php';
    exit;
}

if ($action === 'revenue') {
    $monthlyResult = revenue_monthly($conn);
    $monthlyData = [];
    $maxTotal = 0;
    while ($r = mysqli_fetch_assoc($monthlyResult)) {
        $monthlyData[] = $r;
        if ((float)$r['total'] > $maxTotal) $maxTotal = (float)$r['total'];
    }
    $grandTotal = revenue_total($conn);
    $thisMonth = date('Y-m');
    $thisMonthTotal = 0;
    foreach ($monthlyData as $d) {
        if ($d['ym'] === $thisMonth) { $thisMonthTotal = (float)$d['total']; break; }
    }
    require __DIR__ . '/../views/admin/revenue.php';
    exit;
}


$editUser = null;
if ($action === 'edit') {
    $editUser = user_find_by_id($conn, (int)($_GET['id'] ?? 0));
}

$searchTerm = trim($_GET['q'] ?? '');
$users = $searchTerm !== '' ? user_search($conn, $searchTerm) : user_list_all($conn);

$adminErrors = $_SESSION['admin_errors'] ?? [];
$oldValues = $_SESSION['admin_old'] ?? $editUser ?? ['id' => 0, 'full_name' => '', 'email' => '', 'role' => 'buyer'];
unset($_SESSION['admin_errors'], $_SESSION['admin_old']);

require __DIR__ . '/../views/admin/dashboard.php';
?>
