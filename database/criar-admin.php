<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Este utilitario deve ser executado somente via CLI.');
}

require __DIR__ . '/../config/database.php';

function lerEntrada(string $mensagem): string
{
    if (function_exists('readline')) {
        $valor = readline($mensagem);
        return $valor === false ? '' : trim($valor);
    }

    echo $mensagem;
    $valor = fgets(STDIN);

    return $valor === false ? '' : trim($valor);
}

function obterOpcaoCli(array $opcoes, string $nome): string
{
    $valor = $opcoes[$nome] ?? '';

    if (is_array($valor)) {
        $valor = end($valor);
    }

    return trim((string) $valor);
}

$opcoes = getopt('', ['nome:', 'email:', 'password-env:']) ?: [];

$nome = obterOpcaoCli($opcoes, 'nome');
$email = strtolower(obterOpcaoCli($opcoes, 'email'));
$senhaEnv = obterOpcaoCli($opcoes, 'password-env');
$senha = $senhaEnv !== '' ? (string) getenv($senhaEnv) : '';

if ($nome === '') {
    $nome = lerEntrada('Nome do administrador: ');
}

if ($email === '') {
    $email = strtolower(lerEntrada('E-mail do administrador: '));
}

if ($senha === '') {
    $senha = lerEntrada('Senha do administrador: ');
}

if ($nome === '') {
    fwrite(STDERR, "Nome obrigatorio.\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "E-mail invalido.\n");
    exit(1);
}

if (strlen($senha) < 8) {
    fwrite(STDERR, "A senha deve ter pelo menos 8 caracteres.\n");
    exit(1);
}

try {
    $pdo = obterConexaoBanco();

    $consulta = $pdo->prepare(
        'SELECT id_usuario
           FROM usuarios
          WHERE email = :email
          LIMIT 1'
    );
    $consulta->execute(['email' => $email]);

    if ($consulta->fetch()) {
        echo "Administrador ja existe para o e-mail informado. Nenhum registro duplicado foi criado.\n";
        exit(0);
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $insercao = $pdo->prepare(
        'INSERT INTO usuarios (nome, email, senha_hash, ativo)
         VALUES (:nome, :email, :senha_hash, 1)'
    );

    $insercao->execute([
        'nome' => $nome,
        'email' => $email,
        'senha_hash' => $hash,
    ]);

    echo "Administrador criado com sucesso para {$email}.\n";
} catch (Throwable $exception) {
    error_log('Falha ao criar administrador Ardetho ERP: ' . $exception->getMessage());
    fwrite(STDERR, "Nao foi possivel criar o administrador. Verifique a conexao e o schema do banco.\n");
    exit(1);
}
