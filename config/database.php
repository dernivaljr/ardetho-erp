<?php
declare(strict_types=1);

function obterVariavelAmbiente(string $nome, string $padrao): string
{
    $valor = getenv($nome);

    if ($valor === false || $valor === '') {
        return $padrao;
    }

    return $valor;
}

function obterConfiguracaoBanco(): array
{
    return [
        'host' => obterVariavelAmbiente('DB_HOST', '127.0.0.1'),
        'port' => obterVariavelAmbiente('DB_PORT', '3306'),
        'dbname' => obterVariavelAmbiente('DB_NAME', 'ardetho_erp'),
        'user' => obterVariavelAmbiente('DB_USER', 'root'),
        'password' => obterVariavelAmbiente('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ];
}

function obterConexaoBanco(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = obterConfiguracaoBanco();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset']
    );

    try {
        $pdo = new PDO(
            $dsn,
            $config['user'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $exception) {
        error_log('Falha ao conectar ao banco Ardetho ERP: ' . $exception->getMessage());
        throw new RuntimeException('Nao foi possivel conectar ao banco de dados.');
    }

    return $pdo;
}
