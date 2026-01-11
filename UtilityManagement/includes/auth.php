<?php
require_once __DIR__ . '/functions.php';

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function normalize_username(string $username): string
{
    $username = trim($username);
    $parts = explode('\\', $username);
    return strtolower(end($parts));
}

function windows_identity(): ?string
{
    foreach (['AUTH_USER', 'LOGON_USER', 'REMOTE_USER'] as $key) {
        if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
        }
    }
    return null;
}

function login_user_record(array $user): void
{
    $_SESSION['user'] = [
        'staff_id' => $user['staff_id'],
        'name' => $user['first_name'] . ' ' . $user['last_name'],
        'role' => $user['role'],
        'username' => $user['username'],
    ];
    db_query('UPDATE Staff SET last_login = GETDATE() WHERE staff_id = ?', [$user['staff_id']]);
}

function login(string $username, string $password): bool
{
    $username = normalize_username($username);
    $stmt = db_query('SELECT * FROM Staff WHERE username = ? AND is_active = 1', [$username]);
    $user = db_fetch_one($stmt);

    if ($user) {
        $hashed = strtoupper(hash('sha256', $password));
        if (strtoupper($user['password_hash']) === $hashed) {
            login_user_record($user);
            return true;
        }
    }

    return false;
}

function attempt_windows_login(): bool
{
    $identity = windows_identity();
    if (!$identity) {
        return false;
    }

    $username = normalize_username($identity);
    $stmt = db_query('SELECT * FROM Staff WHERE username = ? AND is_active = 1', [$username]);
    $user = db_fetch_one($stmt);

    if ($user) {
        login_user_record($user);
        return true;
    }

    set_flash("Windows user {$username} is not registered in Utility Management System.", 'danger');
    return false;
}

function require_login(): void
{
    if (!empty($_SESSION['user'])) {
        return;
    }

    if (attempt_windows_login()) {
        return;
    }

    header('Location: login.php');
    exit;
}

function require_role(array $roles): void
{
    $user = ums_current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        set_flash('You are not authorized to access that module.', 'danger');
        header('Location: index.php');
        exit;
    }
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

