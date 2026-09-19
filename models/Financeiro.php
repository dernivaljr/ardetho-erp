<?php
declare(strict_types=1);

class Financeiro
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (($filtros['busca'] ?? '') !== '') {
            $termoBusca = '%' . $filtros['busca'] . '%';
            $where[] = '(f.codigo LIKE :busca_codigo
                      OR f.descricao LIKE :busca_descricao
                      OR f.categoria LIKE :busca_categoria
                      OR c.nome LIKE :busca_cliente_nome
                      OR c.razao_social LIKE :busca_cliente_razao
                      OR c.nome_fantasia LIKE :busca_cliente_fantasia
                      OR v.codigo LIKE :busca_venda)';
            $params['busca_codigo'] = $termoBusca;
            $params['busca_descricao'] = $termoBusca;
            $params['busca_categoria'] = $termoBusca;
            $params['busca_cliente_nome'] = $termoBusca;
            $params['busca_cliente_razao'] = $termoBusca;
            $params['busca_cliente_fantasia'] = $termoBusca;
            $params['busca_venda'] = $termoBusca;
        }

        if (($filtros['tipo'] ?? '') !== '') {
            $where[] = 'f.tipo = :tipo';
            $params['tipo'] = $filtros['tipo'];
        }

        if (($filtros['status'] ?? '') !== '') {
            $where[] = 'f.status = :status';
            $params['status'] = $filtros['status'];
        }

        if (($filtros['id_cliente'] ?? '') !== '') {
            $where[] = 'f.id_cliente = :id_cliente';
            $params['id_cliente'] = (int) $filtros['id_cliente'];
        }

        $sql = 'SELECT f.*,
                       c.tipo_pessoa AS cliente_tipo_pessoa,
                       c.nome AS cliente_nome,
                       c.razao_social AS cliente_razao_social,
                       c.nome_fantasia AS cliente_nome_fantasia,
                       v.codigo AS venda_codigo,
                       v.status AS venda_status
                  FROM financeiro f
             LEFT JOIN clientes c ON c.id_cliente = f.id_cliente
             LEFT JOIN vendas v ON v.id_venda = f.id_venda';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY f.data_lancamento DESC, f.id_financeiro DESC';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function obterResumo(): array
    {
        $consulta = $this->pdo->query(
            "SELECT
                COALESCE(SUM(CASE WHEN tipo = 'Receita' AND status <> 'Cancelado' THEN valor ELSE 0 END), 0) AS total_receitas,
                COALESCE(SUM(CASE WHEN tipo = 'Despesa' AND status <> 'Cancelado' THEN valor ELSE 0 END), 0) AS total_despesas,
                COALESCE(SUM(CASE WHEN tipo = 'Receita' AND status = 'Pendente' THEN valor ELSE 0 END), 0) AS contas_receber,
                COALESCE(SUM(CASE WHEN tipo = 'Despesa' AND status = 'Pendente' THEN valor ELSE 0 END), 0) AS contas_pagar,
                COALESCE(SUM(CASE
                    WHEN status = 'Pendente'
                     AND data_vencimento BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
                    THEN valor ELSE 0 END), 0) AS vencimentos_proximos,
                COUNT(CASE
                    WHEN status = 'Pendente'
                     AND data_vencimento BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
                    THEN 1 END) AS total_vencimentos_proximos
               FROM financeiro"
        );

        $resumo = $consulta->fetch() ?: [];
        $totalReceitas = (float) ($resumo['total_receitas'] ?? 0);
        $totalDespesas = (float) ($resumo['total_despesas'] ?? 0);

        return [
            'saldo_atual' => number_format($totalReceitas - $totalDespesas, 2, '.', ''),
            'contas_receber' => (string) ($resumo['contas_receber'] ?? '0.00'),
            'contas_pagar' => (string) ($resumo['contas_pagar'] ?? '0.00'),
            'vencimentos_proximos' => (string) ($resumo['vencimentos_proximos'] ?? '0.00'),
            'total_vencimentos_proximos' => (int) ($resumo['total_vencimentos_proximos'] ?? 0),
        ];
    }

    public function listarClientesFiltro(): array
    {
        $consulta = $this->pdo->query(
            'SELECT DISTINCT c.id_cliente,
                    c.tipo_pessoa,
                    c.nome,
                    c.razao_social,
                    c.nome_fantasia
               FROM financeiro f
               JOIN clientes c ON c.id_cliente = f.id_cliente
              ORDER BY c.nome, c.nome_fantasia, c.razao_social'
        );

        return $consulta->fetchAll();
    }

    public function listarClientesDisponiveis(?int $idClienteAtual = null): array
    {
        $sql = "SELECT id_cliente, tipo_pessoa, nome, razao_social, nome_fantasia, status
                  FROM clientes
                 WHERE status = 'Ativo'";
        $params = [];

        if ($idClienteAtual !== null) {
            $sql .= ' OR id_cliente = :id_cliente_atual';
            $params['id_cliente_atual'] = $idClienteAtual;
        }

        $sql .= ' ORDER BY nome, nome_fantasia, razao_social';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function listarVendasDisponiveis(?int $idVendaAtual = null): array
    {
        $sql = "SELECT v.id_venda,
                       v.codigo,
                       v.valor_total,
                       v.forma_pagamento,
                       v.status,
                       v.id_cliente,
                       c.tipo_pessoa AS cliente_tipo_pessoa,
                       c.nome AS cliente_nome,
                       c.razao_social AS cliente_razao_social,
                       c.nome_fantasia AS cliente_nome_fantasia
                  FROM vendas v
                  JOIN clientes c ON c.id_cliente = v.id_cliente
                 WHERE v.status <> 'Cancelado'";
        $params = [];

        if ($idVendaAtual !== null) {
            $sql .= ' OR v.id_venda = :id_venda_atual';
            $params['id_venda_atual'] = $idVendaAtual;
        }

        $sql .= ' ORDER BY v.data_venda DESC, v.id_venda DESC';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function buscarPorId(int $idFinanceiro): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT *
               FROM financeiro
              WHERE id_financeiro = :id_financeiro
              LIMIT 1'
        );
        $consulta->execute(['id_financeiro' => $idFinanceiro]);
        $financeiro = $consulta->fetch();

        return $financeiro ?: null;
    }

    public function buscarClientePorId(int $idCliente): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT id_cliente, tipo_pessoa, nome, razao_social, nome_fantasia, status
               FROM clientes
              WHERE id_cliente = :id_cliente
              LIMIT 1'
        );
        $consulta->execute(['id_cliente' => $idCliente]);
        $cliente = $consulta->fetch();

        return $cliente ?: null;
    }

    public function buscarVendaPorId(int $idVenda): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT v.id_venda,
                    v.codigo,
                    v.id_cliente,
                    v.valor_total,
                    v.forma_pagamento,
                    v.status
               FROM vendas v
              WHERE v.id_venda = :id_venda
              LIMIT 1'
        );
        $consulta->execute(['id_venda' => $idVenda]);
        $venda = $consulta->fetch();

        return $venda ?: null;
    }

    public function codigoExiste(string $codigo, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM financeiro WHERE codigo = :codigo';
        $params = ['codigo' => $codigo];

        if ($ignorarId !== null) {
            $sql .= ' AND id_financeiro <> :id_financeiro';
            $params['id_financeiro'] = $ignorarId;
        }

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return (int) $consulta->fetchColumn() > 0;
    }

    public function criar(array $dados): int
    {
        $consulta = $this->pdo->prepare(
            'INSERT INTO financeiro (
                codigo, tipo, descricao, categoria, valor, data_lancamento,
                data_vencimento, status, forma_pagamento, id_cliente,
                id_venda, observacoes
            ) VALUES (
                :codigo, :tipo, :descricao, :categoria, :valor, :data_lancamento,
                :data_vencimento, :status, :forma_pagamento, :id_cliente,
                :id_venda, :observacoes
            )'
        );
        $consulta->execute($dados);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $idFinanceiro, array $dados): void
    {
        $dados['id_financeiro'] = $idFinanceiro;

        $consulta = $this->pdo->prepare(
            'UPDATE financeiro
                SET codigo = :codigo,
                    tipo = :tipo,
                    descricao = :descricao,
                    categoria = :categoria,
                    valor = :valor,
                    data_lancamento = :data_lancamento,
                    data_vencimento = :data_vencimento,
                    status = :status,
                    forma_pagamento = :forma_pagamento,
                    id_cliente = :id_cliente,
                    id_venda = :id_venda,
                    observacoes = :observacoes
              WHERE id_financeiro = :id_financeiro'
        );
        $consulta->execute($dados);
    }

    public function alterarStatus(int $idFinanceiro, string $status): void
    {
        $consulta = $this->pdo->prepare(
            'UPDATE financeiro
                SET status = :status
              WHERE id_financeiro = :id_financeiro'
        );
        $consulta->execute([
            'id_financeiro' => $idFinanceiro,
            'status' => $status,
        ]);
    }
}
