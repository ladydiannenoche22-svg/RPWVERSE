<?php
require_once __DIR__ . '/config/config.php';
if (is_logged_in()) redirect('index.php');
$page_title = 'Create account';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = strtolower(trim($_POST['username'] ?? ''));
    $display = trim($_POST['display_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!preg_match('/^[a-z0-9_]{3,40}$/', $username)) $error = 'Username must be 3–40 characters (letters, numbers, underscore).';
    elseif ($display === '' || mb_strlen($display) > 80) $error = 'Enter a display name (up to 80 characters).';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email address.';
    elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
    else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, display_name, email, password_hash) VALUES (?,?,?,?)');
            $stmt->bind_param('ssss', $username, $display, $email, $hash);
            $stmt->execute();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['display_name'] = $display;
            $_SESSION['role'] = 'member';
            flash('Welcome to RPWVERSE! Your account is ready.');
            redirect('index.php');
        } catch (mysqli_sql_exception $e) { $error = 'That username or email is already registered.'; }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card card"><div class="auth-icon">♡</div><p class="eyebrow">JOIN THE COMMUNITY</p><h1>Create your account</h1><p class="muted">Your RPW world starts here.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="stack-form">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Username<input name="username" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_]+" value="<?= e($_POST['username'] ?? '') ?>"></label>
<label>Display name<input name="display_name" required maxlength="80" value="<?= e($_POST['display_name'] ?? '') ?>"></label>
<label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
<label>Password<input type="password" name="password" required minlength="8"></label>
<button class="btn full" type="submit">Create account ♡</button>
</form><p class="auth-bottom">Already a member? <a href="login.php">Log in</a></p></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
