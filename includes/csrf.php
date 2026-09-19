<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function csrfToken(): string
{
    iniciarSessao();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarCsrf(?string $token): bool
{
    iniciarSessao();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
