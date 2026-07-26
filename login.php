<?php
// login.php
session_start();
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role_name']) && strtolower($_SESSION['role_name']) === 'admin') {
        header("Location: /CRM%20P/admin/dashboard.php");
    } else {
        header("Location: /CRM%20P/user/dashboard.php");
    }
    exit();
}

$errorMsg = '';
if (isset($_GET['error'])) {
    $errorMsg = htmlspecialchars($_GET['error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    if (empty($usernameInput) || empty($passwordInput)) {
        $errorMsg = "Please provide both username/email and password.";
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = :uname OR u.email = :email
            LIMIT 1
        ");
        $stmt->execute([':uname' => $usernameInput, ':email' => $usernameInput]);
        $user = $stmt->fetch();

        if ($user && password_verify($passwordInput, $user['password'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = strtolower($user['role_name']);

            if ($_SESSION['role_name'] === 'admin') {
                header("Location: /CRM%20P/admin/dashboard.php");
            } else {
                header("Location: /CRM%20P/user/dashboard.php");
            }
            exit();
        } else {
            $errorMsg = "Invalid credentials. Default logins: admin / admin123 OR john_user / user123";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lead Management System</title>
    <link rel="stylesheet" href="/CRM%20P/assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">Lead CRM System</div>
        <div class="auth-subtitle">Sign in to your account dashboard</div>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger">
            <?php echo $errorMsg; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label" for="username">Username or Email</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="admin or admin@crm.com" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
            Sign In
        </button>
    </form>

    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border-color); font-size: 12px; color: var(--text-muted); text-align: center;">
        <strong>Default Test Credentials:</strong><br>
        Admin: <code>admin</code> / <code>password</code><br>
        User: <code>john_user</code> / <code>password</code>
    </div>
</div>

</body>
</html>
