<?php
require_once __DIR__ . '/config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}
verify_csrf();

$password = $_POST['password'] ?? '';
$confirmation = trim($_POST['confirmation'] ?? '');
$userId = current_user_id();

if ($confirmation !== 'DELETE') {
    flash('Type DELETE exactly to confirm account deletion.', 'error');
    redirect('profile.php#delete-account');
}

$stmt = $conn->prepare('SELECT password_hash, avatar_url, cover_url FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    flash('The password you entered is incorrect. Your account was not deleted.', 'error');
    redirect('profile.php#delete-account');
}

// Collect this account's uploaded images so they can be removed after the database deletion succeeds.
$filesToRemove = [];
foreach ([$user['avatar_url'], $user['cover_url']] as $path) {
    if (!empty($path)) $filesToRemove[] = $path;
}
$postStmt = $conn->prepare('SELECT image_path FROM posts WHERE user_id = ? AND image_path IS NOT NULL');
$postStmt->bind_param('i', $userId);
$postStmt->execute();
$images = $postStmt->get_result();
while ($row = $images->fetch_assoc()) {
    if (!empty($row['image_path'])) $filesToRemove[] = $row['image_path'];
}

try {
    $conn->begin_transaction();
    // Related posts, comments, reactions, follows, notifications, reports and messages
    // are removed automatically by the database's ON DELETE CASCADE relationships.
    $delete = $conn->prepare('DELETE FROM users WHERE id = ?');
    $delete->bind_param('i', $userId);
    $delete->execute();
    if ($delete->affected_rows !== 1) throw new RuntimeException('Account could not be found.');
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    flash('Account deletion failed. Please try again or contact the site administrator.', 'error');
    redirect('profile.php#delete-account');
}

// Only remove files located inside this project's uploads directory.
$uploadsRoot = realpath(__DIR__ . '/uploads');
if ($uploadsRoot !== false) {
    foreach (array_unique($filesToRemove) as $relativePath) {
        $candidate = realpath(__DIR__ . '/' . ltrim($relativePath, '/\\'));
        if ($candidate && strpos($candidate, $uploadsRoot . DIRECTORY_SEPARATOR) === 0 && is_file($candidate)) {
            @unlink($candidate);
        }
    }
}

// End this browser session and send the user to the login page.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: ' . BASE_URL . '/login.php?deleted=1');
exit;
