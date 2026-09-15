<?php
declare(strict_types=1);

class Dashboard
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function obterMetricas(): array
    {
        $clientesAtivos = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM clientes WHERE status = 'Ativo'")
            ->fetchColumn();

        $produtosServicosAtivos = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM produtos WHERE status = 'Ativo'")
            ->fetchColumn();

        $produtosAtivos = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM produtos WHERE status = 'Ativo' AND tipo_item = 'Produto'")
            ->fetchColumn();

        $servicosAtivos = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM produtos WHERE status = 'Ativo' AND tipo_item <> 'Produto'")
            ->fetchColumn();

        $vendasValidas = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM vendas WHERE status <> 'Cancelado'")
            ->fetchColumn();

        $pedidosAbertos = (int) $this->pdo
            ->query(
                "SELECT COUNT(*)
                   FROM vendas
                  WHERE status IN ('Em análise', 'Aprovado', 'Faturado', 'Pendente')"
            )
            ->fetchColumn();

        $faturamentoTotal = (string) $this->pdo
            ->query("SELECT COALESCE(SUM(valor_total), 0) FROM vendas WHERE status <> 'Cancelado'")
            ->fetchColumn();

        $faturamentoHoje = (string) $this->pdo
            ->query(
                "SELECT COALESCE(SUM(valor_total), 0)
                   FROM vendas
                  WHERE status <> 'Cancelado'
                    AND data_venda = CURRENT_DATE"
            )
            ->fetchColumn();

        $financeiro = $this->pdo
            ->query(
                "SELECT
                    COALESCE(SUM(CASE WHEN tipo = 'Receita' AND status <> 'Cancelado' THEN valor ELSE 0 END), 0) AS receitas,
                    COALESCE(SUM(CASE WHEN tipo = 'Despesa' AND status <> 'Cancelado' THEN valor ELSE 0 END), 0) AS despesas,
                    COALESCE(SUM(CASE WHEN tipo = 'Receita' AND status = 'Pendente' THEN valor ELSE 0 END), 0) AS receitas_pendentes,
                    COALESCE(SUM(CASE WHEN tipo = 'Despesa' AND status = 'Pendente' THEN valor ELSE 0 END), 0) AS despesas_pendentes,
                    COUNT(CASE
                        WHEN status = 'Pendente'
                         AND data_vencimento BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
                        THEN 1 END) AS vencimentos_proximos
                   FROM financeiro"
            )
            ->fetch();

        $receitasFinanceiras = (float) ($financeiro['receitas'] ?? 0);
        $despesasFinanceiras = (float) ($financeiro['despesas'] ?? 0);

        return [
            'clientes_ativos' => $clientesAtivos,
            'produtos_servicos_ativos' => $produtosServicosAtivos,
            'produtos_ativos' => $produtosAtivos,
            'servicos_ativos' => $servicosAtivos,
            'vendas_validas' => $vendasValidas,
            'pedidos_abertos' => $pedidosAbertos,
            'faturamento_total' => $faturamentoTotal,
            'faturamento_hoje' => $faturamentoHoje,
            'financeiro_saldo' => number_format($receitasFinanceiras - $despesasFinanceiras, 2, '.', ''),
            'financeiro_receitas_pendentes' => (string) ($financeiro['receitas_pendentes'] ?? '0.00'),
            'financeiro_despesas_pendentes' => (string) ($financeiro['despesas_pendentes'] ?? '0.00'),
            'financeiro_vencimentos_proximos' => (int) ($financeiro['vencimentos_proximos'] ?? 0),
        ];
    }

    public function listarVendasRecentes(int $limite = 5): array
    {
        $consulta = $this->pdo->prepare(
            'SELECT v.id_venda,
                    v.codigo,
                    v.data_venda,
                    v.status,
                    v.valor_total,
                    c.tipo_pessoa AS cliente_tipo_pessoa,
                    c.nome AS cliente_nome,
                    c.razao_social AS cliente_razao_social,
                    c.nome_fantasia AS cliente_nome_fantasia
               FROM vendas v
               JOIN clientes c ON c.id_cliente = v.id_cliente
              ORDER BY v.data_venda DESC, v.id_venda DESC
              LIMIT :limite'
        );
        $consulta->bindValue('limite', $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll();
    }

    public function listarResumoStatus(): array
    {
        $consulta = $this->pdo->query(
            "SELECT status, COUNT(*) AS total
               FROM vendas
              GROUP BY status
              ORDER BY FIELD(status, 'Em análise', 'Pendente', 'Aprovado', 'Faturado', 'Concluído', 'Cancelado'), status"
        );

        return $consulta->fetchAll();
    }

    public function listarFaturamentoMensal(): array
    {
        $consulta = $this->pdo->query(
            "SELECT DATE_FORMAT(data_venda, '%Y-%m') AS mes,
                    COALESCE(SUM(valor_total), 0) AS total
               FROM vendas
              WHERE status <> 'Cancelado'
                AND data_venda >= DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 5 MONTH), '%Y-%m-01')
              GROUP BY DATE_FORMAT(data_venda, '%Y-%m')
              ORDER BY mes"
        );

        return $consulta->fetchAll();
    }

    public function buscarClienteMaisRecente(): ?array
    {
        $consulta = $this->pdo->query(
            'SELECT tipo_pessoa, nome, razao_social, nome_fantasia, data_cadastro
               FROM clientes
              ORDER BY data_cadastro DESC, id_cliente DESC
              LIMIT 1'
        );
        $cliente = $consulta->fetch();

        return $cliente ?: null;
    }

    public function buscarProdutoMaisRecente(): ?array
    {
        $consulta = $this->pdo->query(
            'SELECT codigo, nome, tipo_item, data_cadastro
               FROM produtos
              ORDER BY data_cadastro DESC, id_produto DESC
              LIMIT 1'
        );
        $produto = $consulta->fetch();

        return $produto ?: null;
    }
}
