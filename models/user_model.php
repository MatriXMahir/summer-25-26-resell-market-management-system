<?php
function user_find_by_email($conn, string $email): ?array {
    $sql  = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $user ?: null;
}

function user_find_by_id($conn, int $id): ?array {
    $sql  = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $user ?: null;
}


function user_email_exists($conn, string $email, int $excludeId = 0): bool {
    $sql  = "SELECT id FROM users WHERE email = ? AND id <> ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $email, $excludeId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return (bool)$row;
}

function user_create($conn, string $fullName, string $email, string $hash, string $role, string $status = 'pending'): int {
    $sql  = "INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $fullName, $email, $hash, $role, $status);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $newId;
}

function user_update($conn, int $id, string $fullName, string $email, string $role): bool {
    $sql  = "UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $fullName, $email, $role, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function user_delete($conn, int $id): bool {
    $sql  = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function user_list_all($conn) {
    $sql = "SELECT * FROM users ORDER BY status = 'pending' DESC, created_at DESC";
    return mysqli_query($conn, $sql);
}

function user_search($conn, string $term) {
    $like = "%" . $term . "%";
    $sql  = "SELECT * FROM users WHERE full_name LIKE ? OR email LIKE ?
             ORDER BY status = 'pending' DESC, created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function user_approve($conn, int $id): bool {
    $sql  = "UPDATE users SET status = 'active' WHERE id = ? AND status = 'pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function user_ban($conn, int $id, string $banUntil): bool {
    $sql  = "UPDATE users SET status = 'banned', ban_until = ? WHERE id = ? AND role <> 'admin'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $banUntil, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function user_unban($conn, int $id): bool {
    $sql  = "UPDATE users SET status = 'active', ban_until = NULL WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function user_lift_expired_ban($conn, array $user): array {
    if ($user['status'] === 'banned' && $user['ban_until'] !== null && strtotime($user['ban_until']) < time()) {
        $sql  = "UPDATE users SET status = 'active', ban_until = NULL WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user['id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $user['status'] = 'active';
    }
    return $user;
}

function user_list_active_non_admin($conn) {
    return mysqli_query($conn, "SELECT * FROM users WHERE role <> 'admin' AND status = 'active' ORDER BY full_name");
}

function user_list_banned($conn) {
    return mysqli_query($conn, "SELECT * FROM users WHERE status = 'banned' ORDER BY ban_until");
}
?>
