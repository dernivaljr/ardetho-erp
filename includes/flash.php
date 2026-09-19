<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function definirFlash(string $tipo, string $mensagem): void
{
    iniciarSessao();

    $_SESSION['flash'] = [
        'tipo' => $tipo,
        'mensagem' => $mensagem,
    ];
}

function obterFlash(): ?array
{
    iniciarSessao();

    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}
