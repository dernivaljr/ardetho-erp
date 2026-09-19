<?php
declare(strict_types=1);

class Relatorio
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function obterResumoFinanceiro(array $periodo): array
    {
        [$where, $params] = $this->periodoWhere('data_lancamento', $periodo);
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN tipo = 'Receita' AND status <> 'Cancelado' THEN valor ELSE 0 END), 0) AS total_receitas,
                    COALESCE(SUM(CASE WHEN tipo = 'Despesa' AND status <> 'Cancelado' THEN valor ELSE 0 END), 0) AS total_despesas,
                    COUNT(CASE WHEN status = 'Pendente' THEN 1 END) AS pendentes
                  FROM financeiro";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);
        $resumo = $consulta->fetch() ?: [];

        return [
            'total_receitas' => (string) ($resumo['total_receitas'] ?? '0.00'),
            'total_despesas' => (string) ($resumo['total_despesas'] ?? '0.00'),
            'saldo' => number_format((float) ($resumo['total_receitas'] ?? 0) - (float) ($resumo['total_despesas'] ?? 0), 2, '.', ''),
            'pendentes' => (int) ($resumo['pendentes'] ?? 0),
        ];
    }

    public function obterResumoVendas(array $periodo): array
    {
        [$where, $params] = $this->periodoWhere('v.data_venda', $periodo);
        $sql = "SELECT
                    COUNT(DISTINCT v.id_venda) AS total,
                    COUNT(DISTINCT CASE WHEN v.status = 'Concluído' THEN v.id_venda END) AS concluidas,
                    COUNT(DISTINCT CASE WHEN v.status IN ('Aprovado', 'Faturado', 'Concluído') THEN v.id_venda END) AS convertidas,
                    COALESCE(SUM(CASE WHEN v.status <> 'Cancelado' THEN v.valor_total ELSE 0 END), 0) AS faturamento_liquido,
                    COALESCE(AVG(CASE WHEN v.status <> 'Cancelado' THEN v.valor_total END), 0) AS ticket_medio
                  FROM vendas v";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);
        $resumo = $consulta->fetch() ?: [];

        return [
            'total' => (int) ($resumo['total'] ?? 0),
            'concluidas' => (int) ($resumo['concluidas'] ?? 0),
            'convertidas' => (int) ($resumo['convertidas'] ?? 0),
            'faturamento_liquido' => (string) ($resumo['faturamento_liquido'] ?? '0.00'),
            'ticket_medio' => (string) ($resumo['ticket_medio'] ?? '0.00'),
        ];
    }

    public function obterResumoClientes(): array
    {
        $consulta = $this->pdo->query(
            "SELECT
                COUNT(*) AS total,
                COUNT(CASE WHEN status = 'Ativo' THEN 1 END) AS ativos,
                COUNT(CASE WHEN status = 'Inativo' THEN 1 END) AS inativos
               FROM clientes"
        );
        $resumo = $consulta->fetch() ?: [];

        return [
            'total' => (int) ($resumo['total'] ?? 0),
            'ativos' => (int) ($resumo['ativos'] ?? 0),
            'inativos' => (int) ($resumo['inativos'] ?? 0),
        ];
    }

    public function obterResumoProdutos(): array
    {
        $consulta = $this->pdo->query(
            "SELECT
                COUNT(*) AS total,
                COUNT(CASE
                    WHEN tipo_item = 'Produto'
                     AND status <> 'Inativo'
                     AND estoque > 0
                     AND estoque <= estoque_minimo
                    THEN 1 END) AS estoque_atencao
               FROM produtos"
        );
        $resumo = $consulta->fetch() ?: [];

        return [
            'total' => (int) ($resumo['total'] ?? 0),
            'estoque_atencao' => (int) ($resumo['estoque_atencao'] ?? 0),
        ];
    }

    public function listarUltimosFinanceiros(array $periodo, int $limite = 10): array
    {
        [$where, $params] = $this->periodoWhere('f.data_lancamento', $periodo);
        $sql = 'SELECT f.codigo,
                       f.tipo,
                       f.categoria,
                       f.descricao,
                       f.valor,
                       f.status,
                       f.data_lancamento
                  FROM financeiro f';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY f.data_cadastro DESC, f.data_lancamento DESC, f.id_financeiro DESC
                  LIMIT :limite';

        $consulta = $this->pdo->prepare($sql);
        foreach ($params as $chave => $valor) {
            $consulta->bindValue($chave, $valor);
        }
        $consulta->bindValue('limite', $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll();
    }

    public function listarFinanceiroParaExportacao(array $periodo, int $limite = 10): array
    {
        return $this->listarUltimosFinanceiros($periodo, $limite);
    }

    private function periodoWhere(string $campo, array $periodo): array
    {
        $where = [];
        $params = [];

        if (($periodo['inicio'] ?? '') !== '') {
            $where[] = "{$campo} >= :data_inicio";
            $params['data_inicio'] = $periodo['inicio'];
        }

        if (($periodo['fim'] ?? '') !== '') {
            $where[] = "{$campo} <= :data_fim";
            $params['data_fim'] = $periodo['fim'];
        }

        return [$where, $params];
    }
}
