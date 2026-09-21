<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

class PerfilController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = obterConexaoBanco();
    }

    public function index(): array
    {
        $usuarioSessao = usuarioAtual();
        $idUsuario = (int) ($usuarioSessao['id_usuario'] ?? 0);
        $usuario = $this->buscarUsuario($idUsuario);

        if ($usuario === null) {
            realizarLogout();
            header('Location: login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarFormulario($usuario);
        }

        return [
            'usuario' => $usuario,
            'flash' => obterFlash(),
            'csrf' => csrfToken(),
        ];
    }

    private function processarFormulario(array $usuarioAtual): void
    {
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            definirFlash('danger', 'Acao recusada por token de seguranca invalido.');
            header('Location: perfil.php');
            exit;
        }

        $idUsuario = (int) $usuarioAtual['id_usuario'];
        $nome = $this->limitar($this->entradaPost('nome'), 120);
        $email = strtolower($this->limitar($this->entradaPost('email'), 190));
        $cargo = $this->textoOuNull('cargo', 100);
        $departamento = $this->textoOuNull('departamento', 100);
        $erros = [];

        if ($nome === '') {
            $erros[] = 'Informe o nome completo.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um e-mail valido.';
        } elseif ($this->emailExiste($email, $idUsuario)) {
            $erros[] = 'Este e-mail ja esta em uso por outro usuario.';
        }

        if ($erros) {
            definirFlash('danger', implode(' ', array_unique($erros)));
            header('Location: perfil.php');
            exit;
        }

        $consulta = $this->pdo->prepare(
            'UPDATE usuarios
                SET nome = :nome,
                    email = :email,
                    cargo = :cargo,
                    departamento = :departamento
              WHERE id_usuario = :id_usuario'
        );
        $consulta->execute([
            'id_usuario' => $idUsuario,
            'nome' => $nome,
            'email' => $email,
            'cargo' => $cargo,
            'departamento' => $departamento,
        ]);

        atualizarUsuarioNaSessao([
            'id_usuario' => $idUsuario,
            'nome' => $nome,
            'email' => $email,
            'cargo' => $cargo ?? '',
            'departamento' => $departamento ?? '',
            'perfil_acesso' => $usuarioAtual['perfil_acesso'],
            'status' => $usuarioAtual['status'],
            'trocar_senha' => $usuarioAtual['trocar_senha'],
        ]);

        definirFlash('success', 'Perfil atualizado com sucesso.');
        header('Location: perfil.php');
        exit;
    }

    private function buscarUsuario(int $idUsuario): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT id_usuario, nome, email, cargo, departamento, perfil_acesso, status, trocar_senha
               FROM usuarios
              WHERE id_usuario = :id_usuario
              LIMIT 1'
        );
        $consulta->execute(['id_usuario' => $idUsuario]);
        $usuario = $consulta->fetch();

        return $usuario ?: null;
    }

    private function emailExiste(string $email, int $ignorarId): bool
    {
        $consulta = $this->pdo->prepare(
            'SELECT COUNT(*)
               FROM usuarios
              WHERE email = :email
                AND id_usuario <> :id_usuario'
        );
        $consulta->execute([
            'email' => $email,
            'id_usuario' => $ignorarId,
        ]);

        return (int) $consulta->fetchColumn() > 0;
    }

    private function entradaPost(string $campo): string
    {
        $valor = filter_input(INPUT_POST, $campo, FILTER_UNSAFE_RAW);

        return is_string($valor) ? trim($valor) : '';
    }

    private function textoOuNull(string $campo, int $limite): ?string
    {
        $valor = $this->limitar($this->entradaPost($campo), $limite);

        return $valor === '' ? null : $valor;
    }

    private function limitar(string $valor, int $limite): string
    {
        return substr(trim($valor), 0, $limite);
    }

    public static function iniciais(string $nome): string
    {
        $partes = preg_split('/\s+/', trim($nome)) ?: [];

        if (count($partes) === 1 && $partes[0] !== '') {
            return strtoupper(substr($partes[0], 0, 2));
        }

        if (count($partes) > 1) {
            return strtoupper(substr($partes[0], 0, 1) . substr($partes[1], 0, 1));
        }

        return 'UA';
    }
}
