<?php

function startSessionIfNeeded(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function getFakeAccounts(): array
{
    return [
        [
            'email' => 'client@smartplate.test',
            'password' => 'client123',
            'role' => 'user',
            'name' => 'Client Demo',
        ],
        [
            'email' => 'admin@smartplate.test',
            'password' => 'admin123',
            'role' => 'admin',
            'name' => 'Admin Demo',
        ],
    ];
}

function normalizeEmail(string $email): string
{
    $email = trim($email);

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($email, 'UTF-8');
    }

    return strtolower($email);
}

function loginByCredentials(string $email, string $password): ?array
{
    $email = normalizeEmail($email);
    $password = trim($password);

    foreach (getFakeAccounts() as $account) {
        if (normalizeEmail($account['email']) === $email && $account['password'] === $password) {
            $user = [
                'email' => $account['email'],
                'role' => $account['role'],
                'name' => $account['name'],
            ];

            startSessionIfNeeded();
            $_SESSION['auth_user'] = $user;
            return $user;
        }
    }

    return null;
}

function getCurrentUser(): ?array
{
    startSessionIfNeeded();

    if (!isset($_SESSION['auth_user']) || !is_array($_SESSION['auth_user'])) {
        return null;
    }

    return $_SESSION['auth_user'];
}

function isAuthenticated(): bool
{
    return getCurrentUser() !== null;
}

function requireAuthentication(string $redirectTo): void
{
    if (!isAuthenticated()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function requireRole(string $role, string $redirectTo): void
{
    $user = getCurrentUser();
    if ($user === null || ($user['role'] ?? '') !== $role) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function logoutCurrentUser(): void
{
    startSessionIfNeeded();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
 