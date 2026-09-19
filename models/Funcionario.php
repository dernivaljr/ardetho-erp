<?php
declare(strict_types=1);

class Funcionario
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (($filtros['busca'] ?? '') !== '') {
            $termo = '%' . $filtros['busca'] . '%';
            $where[] = '(nome_completo LIKE :busca_nome OR email LIKE :busca_email OR cargo LIKE :busca_cargo OR departamento LIKE :busca_departamento)';
            $params['busca_nome'] = $termo;
            $params['busca_email'] = $termo;
            $params['busca_cargo'] = $termo;
            $params['busca_departamento'] = $termo;
        }

        if (($filtros['status'] ?? '') !== '') {
            $where[] = 'status = :status';
            $params['status'] = $filtros['status'];
        }

        if (($filtros['departamento'] ?? '') !== '') {
            $where[] = 'departamento = :departamento';
            $params['departamento'] = $filtros['departamento'];
        }

        $sql = 'SELECT *
                  FROM funcionarios';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY FIELD(status, 'Ativo', 'Férias', 'Afastado', 'Desligado'), nome_completo";

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function obterResumo(): array
    {
        $consulta = $this->pdo->query(
            "SELECT
                COUNT(CASE WHEN status = 'Ativo' THEN 1 END) AS ativos,
                COUNT(CASE WHEN status = 'Férias' THEN 1 END) AS ferias,
                COUNT(CASE WHEN status = 'Afastado' THEN 1 END) AS afastados,
                COALESCE(SUM(CASE WHEN status = 'Ativo' THEN salario ELSE 0 END), 0) AS folha_estimada
               FROM funcionarios"
        );
        $resumo = $consulta->fetch() ?: [];

        return [
            'ativos' => (int) ($resumo['ativos'] ?? 0),
            'ferias' => (int) ($resumo['ferias'] ?? 0),
            'afastados' => (int) ($resumo['afastados'] ?? 0),
            'folha_estimada' => (string) ($resumo['folha_estimada'] ?? '0.00'),
        ];
    }

    public function listarDepartamentos(): array
    {
        $consulta = $this->pdo->query(
            "SELECT DISTINCT departamento
               FROM funcionarios
              WHERE departamento <> ''
              ORDER BY departamento"
        );

        return array_column($consulta->fetchAll(), 'departamento');
    }

    public function buscarPorId(int $idFuncionario): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT *
               FROM funcionarios
              WHERE id_funcionario = :id_funcionario
              LIMIT 1'
        );
        $consulta->execute(['id_funcionario' => $idFuncionario]);
        $funcionario = $consulta->fetch();

        return $funcionario ?: null;
    }

    public function criar(array $dados): int
    {
        $consulta = $this->pdo->prepare(
            'INSERT INTO funcionarios (
                nome_completo, email, telefone, cargo, departamento,
                salario, data_admissao, status, observacoes
            ) VALUES (
                :nome_completo, :email, :telefone, :cargo, :departamento,
                :salario, :data_admissao, :status, :observacoes
            )'
        );
        $consulta->execute($dados);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $idFuncionario, array $dados): void
    {
        $dados['id_funcionario'] = $idFuncionario;

        $consulta = $this->pdo->prepare(
            'UPDATE funcionarios
                SET nome_completo = :nome_completo,
                    email = :email,
                    telefone = :telefone,
                    cargo = :cargo,
                    departamento = :departamento,
                    salario = :salario,
                    data_admissao = :data_admissao,
                    status = :status,
                    observacoes = :observacoes
              WHERE id_funcionario = :id_funcionario'
        );
        $consulta->execute($dados);
    }

    public function alterarStatus(int $idFuncionario, string $status): void
    {
        $consulta = $this->pdo->prepare(
            'UPDATE funcionarios
                SET status = :status
              WHERE id_funcionario = :id_funcionario'
        );
        $consulta->execute([
            'id_funcionario' => $idFuncionario,
            'status' => $status,
        ]);
    }
}
