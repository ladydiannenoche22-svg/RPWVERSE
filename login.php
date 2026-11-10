<?php
require_once __DIR__ . '/config/config.php';
if (is_logged_in()) redirect('index.php');
$page_title = 'Log in';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare('SELECT id, username, display_name, password_hash, role FROM users WHERE email=? OR username=? LIMIT 1');
    $stmt->bind_param('ss', $identity, $identity);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['role'] = $user['role'];
        redirect('index.php');
    } else $error = 'Incorrect username/email or password.';
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card card"><div class="auth-icon">♡</div><p class="eyebrow">WELCOME BACK</p><h1>Log in to RPWVERSE</h1><p class="muted">Your community is waiting for you.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?><div class="alert">Your RPWVERSE account has been permanently deleted.</div><?php endif; ?>
<form method="post" class="stack-form">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Username or email<input name="identity" required value="<?= e($_POST['identity'] ?? '') ?>"></label>
<label>Password<input type="password" name="password" required></label>
<button class="btn full" type="submit">Log in ♡</button>
</form><p class="auth-bottom">New here? <a href="register.php">Create an account</a></p></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
