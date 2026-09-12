<?php
if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    setcookie('remember_email', '', time() - 3600, '/');
    header("Location: " . BASE_URL . "?page=login");
    exit;
}

if (is_logged_in()) {
    header("Location: " . BASE_URL . "?page=" . dashboard_page_for($_SESSION['role']));
    exit;
}

if ($page === 'login') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
        verify_csrf();

        $errors   = [];
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        if (empty($password)) {
            $errors[] = "Password is required.";
        }

        $user = null;
        if (empty($errors)) {
            $user = user_find_by_email($conn, $email);
            if ($user) {
                $user = user_lift_expired_ban($conn, $user);
            }
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errors[] = "Invalid email or password.";
                $user = null;
            } elseif ($user['status'] === 'banned') {
                $until = $user['ban_until'] ? date('d M Y, h:i A', strtotime($user['ban_until'])) : 'further notice';
                $errors[] = "Your account is temporarily banned until $until.";
            } elseif ($user['status'] === 'pending') {
                $errors[] = "Your account is awaiting admin approval.";
            }
        }

        if (!empty($errors) || !$user) {
            $_SESSION['login_errors'] = $errors;
            header("Location: " . BASE_URL . "?page=login");
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']       = (int)$user['id'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['email']         = $user['email'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['logged_in_at']  = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regen']    = time();

        if (!empty($_POST['remember'])) {
            setcookie('remember_email', $email, time() + (86400 * 7), '/');
        } else {
            setcookie('remember_email', '', time() - 3600, '/');
        }

        header("Location: " . BASE_URL . "?page=" . dashboard_page_for($user['role']));
        exit;
    }

    $loginErrors = $_SESSION['login_errors'] ?? [];
    unset($_SESSION['login_errors']);
    require __DIR__ . '/../views/auth/login.php';
    exit;
}


if ($page === 'register') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
        verify_csrf();

        $errors       = [];
        $allowedRoles = ['buyer', 'seller', 'delivery'];

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? '';

        if (empty($fullName) || !preg_match("/^[a-zA-Z .]{2,60}$/", $fullName)) {
            $errors[] = "Please enter a valid full name (letters, spaces, dots only).";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }
        if (!in_array($role, $allowedRoles, true)) {
            $errors[] = "Please choose a valid role.";
        }
        if (empty($errors) && user_email_exists($conn, $email)) {
            $errors[] = "An account with this email already exists.";
        }

        if (!empty($errors)) {
            $_SESSION['register_errors'] = $errors;
            $_SESSION['register_old']    = ['full_name' => $fullName, 'email' => $email, 'role' => $role];
            header("Location: " . BASE_URL . "?page=register");
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        user_create($conn, $fullName, $email, $hash, $role, 'pending');

        flash_set('success', 'Account created! Please wait for admin approval before logging in.');
        header("Location: " . BASE_URL . "?page=login");
        exit;
    }

    $registerErrors = $_SESSION['register_errors'] ?? [];
    $old = $_SESSION['register_old'] ?? ['full_name' => '', 'email' => '', 'role' => ''];
    unset($_SESSION['register_errors'], $_SESSION['register_old']);
    require __DIR__ . '/../views/auth/register.php';
    exit;
}
?>
