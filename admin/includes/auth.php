<?php
/**
 * Authentication helpers.
 */

declare(strict_types=1);

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, email, created_at FROM admins WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

/**
 * Call at the top of every protected admin page.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'dashboard.php';
        redirect('login.php');
    }
}

/**
 * Very small brute-force throttle: after 5 failed attempts within 10
 * minutes from the same session, force a short cooldown.
 */
function login_attempt_allowed(): bool
{
    $attempts = $_SESSION['login_attempts'] ?? [];
    $recent = array_filter($attempts, fn($t) => $t > (time() - 600));
    $_SESSION['login_attempts'] = array_values($recent);
    return count($recent) < 5;
}

function login_attempt_record_failure(): void
{
    $_SESSION['login_attempts'][] = time();
}

function attempt_login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = $row['id'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['login_attempts']);
    return true;
}

function do_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(SESSION_NAME, '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
