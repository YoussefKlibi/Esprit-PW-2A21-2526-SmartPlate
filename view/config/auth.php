<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/User/UserController.php';

function startSessionIfNeeded(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function getCurrentUser(): ?array
{
    startSessionIfNeeded();
    
    // Toujours rafraîchir les données de l'utilisateur depuis la base de données
    // pour que les changements (nom, prénom) se reflètent partout instantanément
    if (isset($_SESSION['user']['email'])) {
        $uc = new UserController();
        $freshUser = $uc->getUserByEmail($_SESSION['user']['email']);
        if ($freshUser) {
            $_SESSION['user'] = $freshUser;
            return $freshUser;
        }
    }
    
    return $_SESSION['user'] ?? null;
}

function loginByCredentials(string $email, string $password): bool
{
    startSessionIfNeeded();
    $uc = new UserController();
    $user = $uc->authenticate($email, $password);
    if ($user !== null) {
        // store minimal user info in session
        $_SESSION['user'] = $user;
        // update last activity
        $uc->updateLastActivity($email);
        return true;
    }
    return false;
}

function requireAuthentication(string $redirect = 'login.php'): void
{
    startSessionIfNeeded();
    if (!isset($_SESSION['user'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireRole(string $role, string $redirect = 'login.php'): void
{
    startSessionIfNeeded();
    $user = $_SESSION['user'] ?? null;
    if ($user === null) {
        header('Location: ' . $redirect);
        exit;
    }

    // If no explicit role field exists, consider 'admin' as the specific admin email (fallback)
    if ($role === 'admin') {
        $isAdmin = isset($user['role']) && $user['role'] === 'admin';
        if (!$isAdmin && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            $isAdmin = true;
        }
        if (!$isAdmin && isset($user['email']) && $user['email'] === 'admin@smartplate.test') {
            $isAdmin = true;
        }
        if (!$isAdmin) {
            header('Location: ' . $redirect);
            exit;
        }
    }
    // for role 'user' we just require authentication (already checked)
}

function logoutCurrentUser(string $redirect = 'login.php'): void
{
    startSessionIfNeeded();
    unset($_SESSION['user']);
    session_destroy();
    header('Location: ' . $redirect);
    exit;
}
