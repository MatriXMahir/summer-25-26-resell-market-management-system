<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'resell_market');

define('SESSION_TIMEOUT', 900);   
define('SESSION_REGEN',   300);   
define('LOW_STOCK',       5);     
define('CURRENCY',        'Tk');  
define('BASE_URL',        'index.php'); 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conn, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

$rememberSession = !empty($_COOKIE['remember_session']) && $_COOKIE['remember_session'] === '1';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => $rememberSession ? (60 * 60 * 24 * 7) : 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => false,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$checkAdmin = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if ($checkAdmin && mysqli_num_rows($checkAdmin) === 0) {
    $fallbackHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password_hash, role, status)
                                    VALUES ('Admin', 'admin@resell.com', ?, 'admin', 'active')");
    mysqli_stmt_bind_param($stmt, "s", $fallbackHash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
