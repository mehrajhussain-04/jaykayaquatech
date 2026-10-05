<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!login_attempt_allowed()) {
        $error = 'Too many failed attempts. Please wait a few minutes and try again.';
    } else {
        $email = post('email');
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Please enter both email and password.';
        } elseif (attempt_login($email, $password)) {
            unset($_SESSION['redirect_after_login']);
            redirect('dashboard.php');
        } else {
            login_attempt_record_failure();
            $error = 'Incorrect email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login · Jay Kay Aqua Tech</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="../favicon.png">
<link rel="stylesheet" href="assets/admin-style.css">
</head>
<body>
<div class="login-shell">
  <div class="login-card">
    <div class="brand">
      <img src="../assets/logo/Jay Kay Aqua Tech.png" alt="Jay Kay Aqua Tech">
      <strong>Jay Kay Aqua Tech</strong>
    </div>
    <h1>Admin Login</h1>
    <p class="sub">Sign in to manage products, enquiries and site content.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; padding:13px;">Sign In</button>
    </form>
  </div>
</div>
</body>
</html>
