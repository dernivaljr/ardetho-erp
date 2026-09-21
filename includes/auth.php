<?php
declare(strict_types=1);

function conexaoSeguraAtual(): bool
{
    $https = $_SERVER['HTTPS'] ?? '';
    $porta = $_SERVER['SERVER_PORT'] ?? null;

    return ($https !== '' && strtolower((string) $https) !== 'off')
        || (string) $porta === '443';
}

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    configurarDiretorioSessao();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => conexaoSeguraAtual(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function configurarDiretorioSessao(): void
{
    $caminhoAtual = session_save_path();

    if ($caminhoAtual !== '' && is_dir($caminhoAtual) && is_writable($caminhoAtual)) {
        return;
    }

    $caminhoLocal = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'sessions';

    if (!is_dir($caminhoLocal)) {
        mkdir($caminhoLocal, 0700, true);
    }

    if (is_dir($caminhoLocal) && is_writable($caminhoLocal)) {
        session_save_path($caminhoLocal);
    }
}

function usuarioAutenticado(): bool
{
    iniciarSessao();

    return isset($_SESSION['usuario'])
        && is_array($_SESSION['usuario'])
        && !empty($_SESSION['usuario']['id_usuario'])
        && !empty($_SESSION['usuario']['email']);
}

function usuarioAtual(): ?array
{
    if (!usuarioAutenticado()) {
        return null;
    }

    return $_SESSION['usuario'];
}

function exigirAutenticacao(): void
{
    if (!usuarioAutenticado()) {
        header('Location: login.php');
        exit;
    }

    require_once __DIR__ . '/../config/database.php';
    $consulta = obterConexaoBanco()->prepare(
        'SELECT id_usuario, nome, email, cargo, departamento, ativo, perfil_acesso, status, trocar_senha
           FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1'
    );
    $consulta->execute(['id_usuario' => (int) $_SESSION['usuario']['id_usuario']]);
    $usuario = $consulta->fetch();

    if (!$usuario || (int) $usuario['ativo'] !== 1 || $usuario['status'] !== 'Ativo') {
        realizarLogout();
        header('Location: login.php');
        exit;
    }

    atualizarUsuarioNaSessao($usuario);
    if ((int) $usuario['trocar_senha'] === 1 && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'alterar-senha.php') {
        header('Location: alterar-senha.php');
        exit;
    }
}

function exigirAdministrador(): void
{
    exigirAutenticacao();
    if ((usuarioAtual()['perfil_acesso'] ?? '') !== 'Administrador') {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

function autenticarUsuario(PDO $pdo, string $email, string $senha): ?array
{
    $consulta = $pdo->prepare(
        'SELECT id_usuario, nome, email, cargo, departamento, senha_hash, ativo, perfil_acesso, status, trocar_senha
           FROM usuarios
          WHERE email = :email
          LIMIT 1'
    );

    $consulta->execute(['email' => $email]);
    $usuario = $consulta->fetch();

    if (!$usuario || (int) $usuario['ativo'] !== 1 || $usuario['status'] !== 'Ativo') {
        return null;
    }

    if (!password_verify($senha, (string) $usuario['senha_hash'])) {
        return null;
    }

    $atualizacao = $pdo->prepare(
        'UPDATE usuarios
            SET ultimo_login = CURRENT_TIMESTAMP
          WHERE id_usuario = :id_usuario'
    );
    $atualizacao->execute(['id_usuario' => (int) $usuario['id_usuario']]);

    return [
        'id_usuario' => (int) $usuario['id_usuario'],
        'nome' => (string) $usuario['nome'],
        'email' => (string) $usuario['email'],
        'cargo' => (string) ($usuario['cargo'] ?? ''),
        'departamento' => (string) ($usuario['departamento'] ?? ''),
        'perfil_acesso' => (string) $usuario['perfil_acesso'],
        'status' => (string) $usuario['status'],
        'trocar_senha' => (int) $usuario['trocar_senha'],
    ];
}

function registrarUsuarioNaSessao(array $usuario): void
{
    iniciarSessao();
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id_usuario' => (int) $usuario['id_usuario'],
        'nome' => (string) $usuario['nome'],
        'email' => (string) $usuario['email'],
        'cargo' => (string) ($usuario['cargo'] ?? ''),
        'departamento' => (string) ($usuario['departamento'] ?? ''),
        'perfil_acesso' => (string) ($usuario['perfil_acesso'] ?? 'Usuário'),
        'status' => (string) ($usuario['status'] ?? 'Ativo'),
        'trocar_senha' => (int) ($usuario['trocar_senha'] ?? 0),
    ];
}

function atualizarUsuarioNaSessao(array $usuario): void
{
    iniciarSessao();

    $_SESSION['usuario'] = [
        'id_usuario' => (int) $usuario['id_usuario'],
        'nome' => (string) $usuario['nome'],
        'email' => (string) $usuario['email'],
        'cargo' => (string) ($usuario['cargo'] ?? ''),
        'departamento' => (string) ($usuario['departamento'] ?? ''),
        'perfil_acesso' => (string) ($usuario['perfil_acesso'] ?? 'Usuário'),
        'status' => (string) ($usuario['status'] ?? 'Ativo'),
        'trocar_senha' => (int) ($usuario['trocar_senha'] ?? 0),
    ];
}

function realizarLogout(): void
{
    iniciarSessao();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}
