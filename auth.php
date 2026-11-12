<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('e')) {
    function e(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}

function destinoSegunRol(string $rol): string
{
    return $rol === 'admin' ? 'admin_users.php' : 'panel.php';
}

function requerirLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php');
        exit;
    }
}

function requerirAdmin(): void
{
    if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfValido($token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}