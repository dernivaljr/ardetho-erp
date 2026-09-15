<?php
declare(strict_types=1);

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function selectedIf(mixed $valorAtual, mixed $valorEsperado): string
{
    return (string) $valorAtual === (string) $valorEsperado ? ' selected' : '';
}

function checkedIf(bool $condicao): string
{
    return $condicao ? ' checked' : '';
}
