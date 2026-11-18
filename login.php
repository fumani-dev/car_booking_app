<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$mysqli = require __DIR__ . '/includes/database.php';
$error = '';
$mode = ($_GET['mode'] ?? $_POST['mode'] ?? 'login') === 'signup' ? 'signup' : 'login';
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($mode === 'signup') {
        if ($username === '' || strlen($username) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100 || strlen($password) < 8) {
            $error = 'Enter a name, a valid email address, and a password with at least 8 characters.';
        } else {
            $check = $mysqli->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
            $check->bind_param('ss', $email, $username);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                $error = 'An account with that email or username already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Public signup always creates a regular client account; role is never read from the request.
                $role = 'client';
                $statement = $mysqli->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
                $statement->bind_param('ssss', $username, $email, $hash, $role);
                $statement->execute();
                $userId = $statement->insert_id;
                $statement->close();

                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $userId;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header('Location: index.php');
                exit;
            }
        }
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        $statement = $mysqli->prepare('SELECT id, username, password, role FROM users WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        $user = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        }

        $error = 'Email or password is incorrect.';
    }
}

$title = $mode === 'signup' ? 'Create account' : 'Log in';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | OpenRoad SA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main class="login-page">
        <a class="login-brand" href="index.php">OpenRoad <span>SA</span></a>
        <section class="login-card" aria-labelledby="login-title">
            <p class="login-eyebrow"><?= $mode === 'signup' ? 'Get started' : 'Welcome back' ?></p>
            <h1 id="login-title"><?= $mode === 'signup' ? 'Create your account' : 'Log in to OpenRoad' ?></h1>
            <p class="login-intro">
                <?= $mode === 'signup' ? 'Create an account to get started.' : 'Sign in to continue to your account.' ?>
            </p>

            <?php if ($error !== ''): ?>
            <p class="login-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="post" action="login.php">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($mode === 'signup'): ?>
                <label for="username">Name</label>
                <input id="username" name="username" type="text" autocomplete="name" maxlength="100"
                    value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
                <?php endif; ?>
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" autocomplete="email"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>

                <label for="password">Password</label>
                <input id="password" name="password" type="password"
                    autocomplete="<?= $mode === 'signup' ? 'new-password' : 'current-password' ?>"
                    <?= $mode === 'signup' ? 'minlength="8"' : '' ?> required>

                <button class="login-submit"
                    type="submit"><?= $mode === 'signup' ? 'Create account' : 'Log in' ?></button>
            </form>

            <p class="login-switch">
                <?= $mode === 'signup' ? 'Already have an account?' : 'New to OpenRoad?' ?>
                <a
                    href="login.php<?= $mode === 'signup' ? '' : '?mode=signup' ?>"><?= $mode === 'signup' ? 'Log in' : 'Create an account' ?></a>
            </p>
            <a class="login-home" href="index.php">Back to browsing cars</a>
            
        </section>
    </main>
</body>

</html>
