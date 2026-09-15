<?php
declare(strict_types=1);

class Cliente
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (($filtros['busca'] ?? '') !== '') {
            $camposBusca = [
                'nome',
                'razao_social',
                'nome_fantasia',
                'cpf',
                'cnpj',
                'email',
                'telefone',
                'contato',
            ];
            $partesBusca = [];
            $termoBusca = '%' . $filtros['busca'] . '%';

            foreach ($camposBusca as $indice => $campo) {
                $placeholder = 'busca_' . $indice;
                $partesBusca[] = "{$campo} LIKE :{$placeholder}";
                $params[$placeholder] = $termoBusca;
            }

            $where[] = '(' . implode(' OR ', $partesBusca) . ')';
        }

        if (($filtros['status'] ?? '') !== '') {
            $where[] = 'status = :status';
            $params['status'] = $filtros['status'];
        }

        if (($filtros['cidade'] ?? '') !== '') {
            $where[] = 'cidade = :cidade';
            $params['cidade'] = $filtros['cidade'];
        }

        $sql = 'SELECT *
                  FROM clientes';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY data_cadastro DESC, id_cliente DESC';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function listarCidades(): array
    {
        $consulta = $this->pdo->query(
            "SELECT DISTINCT cidade
               FROM clientes
              WHERE cidade IS NOT NULL
                AND cidade <> ''
              ORDER BY cidade"
        );

        return array_column($consulta->fetchAll(), 'cidade');
    }

    public function buscarPorId(int $idCliente): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT *
               FROM clientes
              WHERE id_cliente = :id_cliente
              LIMIT 1'
        );
        $consulta->execute(['id_cliente' => $idCliente]);
        $cliente = $consulta->fetch();

        return $cliente ?: null;
    }

    public function criar(array $dados): int
    {
        $consulta = $this->pdo->prepare(
            'INSERT INTO clientes (
                tipo_pessoa, status, nome, cpf, rg, data_nascimento,
                razao_social, nome_fantasia, cnpj, inscricao_estadual,
                contato, email, email_nf, telefone, whatsapp, cep,
                logradouro, numero, complemento, bairro, cidade, estado,
                observacoes
            ) VALUES (
                :tipo_pessoa, :status, :nome, :cpf, :rg, :data_nascimento,
                :razao_social, :nome_fantasia, :cnpj, :inscricao_estadual,
                :contato, :email, :email_nf, :telefone, :whatsapp, :cep,
                :logradouro, :numero, :complemento, :bairro, :cidade, :estado,
                :observacoes
            )'
        );
        $consulta->execute($dados);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $idCliente, array $dados): void
    {
        $dados['id_cliente'] = $idCliente;

        $consulta = $this->pdo->prepare(
            'UPDATE clientes
                SET tipo_pessoa = :tipo_pessoa,
                    status = :status,
                    nome = :nome,
                    cpf = :cpf,
                    rg = :rg,
                    data_nascimento = :data_nascimento,
                    razao_social = :razao_social,
                    nome_fantasia = :nome_fantasia,
                    cnpj = :cnpj,
                    inscricao_estadual = :inscricao_estadual,
                    contato = :contato,
                    email = :email,
                    email_nf = :email_nf,
                    telefone = :telefone,
                    whatsapp = :whatsapp,
                    cep = :cep,
                    logradouro = :logradouro,
                    numero = :numero,
                    complemento = :complemento,
                    bairro = :bairro,
                    cidade = :cidade,
                    estado = :estado,
                    observacoes = :observacoes
              WHERE id_cliente = :id_cliente'
        );
        $consulta->execute($dados);
    }

    public function alterarStatus(int $idCliente, string $status): void
    {
        $consulta = $this->pdo->prepare(
            'UPDATE clientes
                SET status = :status
              WHERE id_cliente = :id_cliente'
        );
        $consulta->execute([
            'id_cliente' => $idCliente,
            'status' => $status,
        ]);
    }
}
