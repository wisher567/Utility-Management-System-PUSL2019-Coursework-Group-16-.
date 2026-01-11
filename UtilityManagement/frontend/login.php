<?php
require_once __DIR__ . '/../includes/auth.php';

if (ums_current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials or inactive account.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <section class="auth-card">
        <h1><?= APP_NAME ?></h1>
        <p class="subtitle">Secure utility oversight for every role.</p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="form">
            <label>Username
                <input type="text" name="username" required autofocus>
            </label>
            <label>Password
                <input type="password" name="password" required>
            </label>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
    </section>
</body>
</html>

