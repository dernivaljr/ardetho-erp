<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

class UsuarioController
{
    private Usuario $model;

    public function __construct()
    {
        $this->model = new Usuario(obterConexaoBanco());
    }

    public function listar(): array { return $this->model->listar(); }
    public function buscar(int $id): ?array { return $this->model->buscar($id); }

    private function post(string $campo, bool $aparar = true): string
    {
        $valor = is_string($_POST[$campo] ?? null) ? $_POST[$campo] : '';
        return $aparar ? trim($valor) : $valor;
    }

    private function voltar(string $destino, string $tipo, string $mensagem): never
    {
        definirFlash($tipo, $mensagem);
        header('Location: ' . $destino);
        exit;
    }

    private function csrf(string $destino): void
    {
        if (!validarCsrf($this->post('csrf_token'))) {
            $this->voltar($destino, 'danger', 'Token de segurança inválido.');
        }
    }

    public function salvar(): never
    {
        $id = filter_var($this->post('id_usuario'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $destino = $id ? 'usuario-form.php?id=' . $id : 'usuario-form.php';
        $this->csrf($destino);
        $atual = $id ? $this->model->buscar($id) : null;
        if ($id && !$atual) $this->voltar('usuarios.php', 'danger', 'Usuário não encontrado.');

        $dados = [
            'nome' => $this->post('nome'),
            'email' => strtolower($this->post('email')),
            'cargo' => $this->post('cargo') ?: null,
            'departamento' => $this->post('departamento') ?: null,
            'perfil_acesso' => $this->post('perfil_acesso'),
            'status' => $this->post('status'),
        ];
        $senha = $this->post('senha_temporaria', false);
        if ($dados['nome'] === '' || strlen($dados['nome']) > 120
            || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || strlen($dados['email']) > 190
            || strlen((string) $dados['cargo']) > 100 || strlen((string) $dados['departamento']) > 100
            || !in_array($dados['perfil_acesso'], ['Administrador', 'Usuário'], true)
            || !in_array($dados['status'], ['Ativo', 'Inativo'], true)) {
            $this->voltar($destino, 'danger', 'Confira os dados do usuário.');
        }
        if ($this->model->emailOcupado($dados['email'], $id)) {
            $this->voltar($destino, 'danger', 'E-mail já está em uso.');
        }
        if (!$id && (strlen($senha) < 8 || str_contains($senha, "\0"))) {
            $this->voltar($destino, 'danger', 'Senha temporária deve ter pelo menos 8 caracteres.');
        }
        if ($id === (int) usuarioAtual()['id_usuario'] && ($dados['status'] !== 'Ativo' || $dados['perfil_acesso'] !== 'Administrador')) {
            $this->voltar($destino, 'danger', 'Não é possível remover seu próprio acesso administrativo.');
        }
        try {
            if ($id) $this->model->editar($id, $dados);
            else $this->model->criar($dados, $senha);
        } catch (PDOException $e) {
            $this->voltar($destino, 'danger', 'Não foi possível salvar o usuário.');
        }
        $this->voltar('usuarios.php', 'success', 'Usuário salvo com sucesso.');
    }

    public function acao(): never
    {
        $this->csrf('usuarios.php');
        $id = filter_var($this->post('id_usuario'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
        $usuario = $id ? $this->model->buscar($id) : null;
        if (!$usuario) $this->voltar('usuarios.php', 'danger', 'Usuário não encontrado.');
        $acao = $this->post('acao');
        if ($acao === 'status') {
            if ($id === (int) usuarioAtual()['id_usuario']) $this->voltar('usuarios.php', 'danger', 'Não é possível desativar sua própria conta.');
            $this->model->status($id, $usuario['status'] === 'Ativo' ? 'Inativo' : 'Ativo');
        } elseif ($acao === 'reset') {
            if ($id === (int) usuarioAtual()['id_usuario']) $this->voltar('usuarios.php', 'danger', 'Use Alterar senha para sua própria conta.');
            $senha = $this->post('senha_temporaria', false);
            if (strlen($senha) < 8 || str_contains($senha, "\0")) $this->voltar('usuarios.php', 'danger', 'Senha temporária deve ter pelo menos 8 caracteres.');
            $this->model->senha($id, $senha, true);
        } else {
            $this->voltar('usuarios.php', 'danger', 'Ação inválida.');
        }
        $this->voltar('usuarios.php', 'success', 'Usuário atualizado com sucesso.');
    }

    public function alterarPropriaSenha(): never
    {
        $this->csrf('alterar-senha.php');
        $id = (int) usuarioAtual()['id_usuario'];
        $usuario = $this->model->buscar($id);
        $nova = $this->post('nova_senha', false);
        if (!$usuario || !password_verify($this->post('senha_atual', false), $usuario['senha_hash'])) {
            $this->voltar('alterar-senha.php', 'danger', 'Senha atual inválida.');
        }
        if (strlen($nova) < 8 || str_contains($nova, "\0") || $nova !== $this->post('confirmar_senha', false)) {
            $this->voltar('alterar-senha.php', 'danger', 'A nova senha deve ter 8 caracteres e coincidir com a confirmação.');
        }
        $this->model->senha($id, $nova, false);
        $usuario['trocar_senha'] = 0;
        atualizarUsuarioNaSessao($usuario);
        $this->voltar('alterar-senha.php', 'success', 'Senha alterada com sucesso.');
    }
}
