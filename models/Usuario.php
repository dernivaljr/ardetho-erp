<?php
declare(strict_types=1);

class Usuario
{
    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        return $this->pdo->query('SELECT id_usuario, nome, email, cargo, departamento, perfil_acesso, status FROM usuarios ORDER BY nome')->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id_usuario, nome, email, cargo, departamento, perfil_acesso, status, trocar_senha, senha_hash FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function emailOcupado(string $email, int $ignorar = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = :email AND id_usuario <> :id');
        $stmt->execute(['email' => $email, 'id' => $ignorar]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function criar(array $dados, string $senha): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO usuarios (nome, email, cargo, departamento, senha_hash, ativo, perfil_acesso, status, trocar_senha) VALUES (:nome, :email, :cargo, :departamento, :senha_hash, :ativo, :perfil_acesso, :status, 1)');
        $stmt->execute($dados + ['senha_hash' => password_hash($senha, PASSWORD_DEFAULT), 'ativo' => $dados['status'] === 'Ativo' ? 1 : 0]);
    }

    public function editar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET nome = :nome, email = :email, cargo = :cargo, departamento = :departamento, perfil_acesso = :perfil_acesso, status = :status, ativo = :ativo WHERE id_usuario = :id');
        $stmt->execute($dados + ['id' => $id, 'ativo' => $dados['status'] === 'Ativo' ? 1 : 0]);
    }

    public function status(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET status = :status, ativo = :ativo WHERE id_usuario = :id');
        $stmt->execute(['status' => $status, 'ativo' => $status === 'Ativo' ? 1 : 0, 'id' => $id]);
    }

    public function senha(int $id, string $senha, bool $obrigarTroca): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET senha_hash = :hash, trocar_senha = :trocar WHERE id_usuario = :id');
        $stmt->execute(['hash' => password_hash($senha, PASSWORD_DEFAULT), 'trocar' => $obrigarTroca ? 1 : 0, 'id' => $id]);
    }
}
