<?php
function esc(?string $text): string {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_url(): string {
    return 'csrf_token=' . urlencode(csrf_token());
}

function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(400);
        die("Security check failed (invalid or expired form token). Please go back and try again.");
    }
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "?page=login");
        exit;
    }
}

function require_role(string $role): void {
    require_login();
    if ($_SESSION['role'] !== $role) {
        header("Location: " . BASE_URL . "?page=login&error=unauthorized");
        exit;
    }
}

function is_self(int $targetUserId): bool {
    return is_logged_in() && (int)$_SESSION['user_id'] === $targetUserId;
}

function flash_set(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array {
    if (empty($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function check_session_timeout(): void {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        $_SESSION = [];
        session_destroy();
        header("Location: " . BASE_URL . "?page=login&reason=timeout");
        exit;
    }
    $_SESSION['last_activity'] = time();

    if (!isset($_SESSION['last_regen'])) {
        $_SESSION['last_regen'] = time();
    } elseif (time() - $_SESSION['last_regen'] > SESSION_REGEN) {
        session_regenerate_id(true);
        $_SESSION['last_regen'] = time();
    }
}

function dashboard_page_for(string $role): string {
    $map = ['admin' => 'admin', 'buyer' => 'buyer', 'seller' => 'seller', 'delivery' => 'delivery'];
    return $map[$role] ?? 'login';
}
?>
