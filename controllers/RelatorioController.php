<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Relatorio.php';

class RelatorioController
{
    private const PERIODOS = ['todos', 'hoje', '7dias', '30dias', 'mes'];
    private const MODULOS_ATIVOS = 7;

    private Relatorio $model;

    public function __construct()
    {
        $this->model = new Relatorio(obterConexaoBanco());
    }

    public function index(): array
    {
        $filtros = $this->obterFiltros();
        $periodo = $this->resolverPeriodo($filtros['periodo']);
        $erros = [];

        if ($periodo === null) {
            $erros[] = 'Periodo informado e invalido.';
            $periodo = $this->resolverPeriodo('todos');
            $filtros['periodo'] = 'todos';
        }

        $financeiro = $this->model->obterResumoFinanceiro($periodo);
        $vendas = $this->model->obterResumoVendas($periodo);
        $clientes = $this->model->obterResumoClientes();
        $produtos = $this->model->obterResumoProdutos();
        $ultimosFinanceiros = $this->model->listarUltimosFinanceiros($periodo);

        return [
            'filtros' => $filtros,
            'periodo' => $periodo,
            'financeiro' => $financeiro,
            'vendas' => $vendas,
            'clientes' => $clientes,
            'produtos' => $produtos,
            'ultimosFinanceiros' => $ultimosFinanceiros,
            'relatoriosDisponiveis' => $this->relatoriosDisponiveis($filtros, $vendas, $clientes, $financeiro, $produtos),
            'analiticos' => $this->analiticos($vendas, $financeiro),
            'erros' => $erros,
        ];
    }

    public function exportarCsv(): void
    {
        $filtros = $this->obterFiltros();
        $periodo = $this->resolverPeriodo($filtros['periodo']) ?? $this->resolverPeriodo('todos');
        $financeiro = $this->model->obterResumoFinanceiro($periodo);
        $vendas = $this->model->obterResumoVendas($periodo);
        $clientes = $this->model->obterResumoClientes();
        $produtos = $this->model->obterResumoProdutos();
        $ultimosFinanceiros = $this->model->listarFinanceiroParaExportacao($periodo);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="relatorio-ardetho.csv"');

        $saida = fopen('php://output', 'w');
        if ($saida === false) {
            return;
        }

        fwrite($saida, "\xEF\xBB\xBF");
        fputcsv($saida, ['Indicador', 'Valor']);
        fputcsv($saida, ['Periodo', $this->rotuloPeriodo($filtros['periodo'])]);
        fputcsv($saida, ['Receita total', $financeiro['total_receitas']]);
        fputcsv($saida, ['Despesa total', $financeiro['total_despesas']]);
        fputcsv($saida, ['Saldo consolidado', $financeiro['saldo']]);
        fputcsv($saida, ['Pedidos concluidos', $vendas['concluidas']]);
        fputcsv($saida, ['Ticket medio', $vendas['ticket_medio']]);
        fputcsv($saida, ['Conversao comercial (%)', $this->taxaConversao($vendas)]);
        fputcsv($saida, ['Pendencias financeiras', $financeiro['pendentes']]);
        fputcsv($saida, ['Clientes cadastrados', $clientes['total']]);
        fputcsv($saida, ['Itens cadastrados', $produtos['total']]);
        fputcsv($saida, ['Pedidos registrados', $vendas['total']]);
        fputcsv($saida, ['Modulos ativos', self::MODULOS_ATIVOS]);
        fputcsv($saida, []);
        fputcsv($saida, ['Ultimos lancamentos financeiros']);
        fputcsv($saida, ['Codigo', 'Tipo', 'Categoria', 'Descricao', 'Valor', 'Status']);

        foreach ($ultimosFinanceiros as $lancamento) {
            fputcsv($saida, [
                $lancamento['codigo'] ?? '',
                $lancamento['tipo'] ?? '',
                $lancamento['categoria'] ?? '',
                $lancamento['descricao'] ?? '',
                $lancamento['valor'] ?? '0.00',
                $lancamento['status'] ?? '',
            ]);
        }

        fclose($saida);
        exit;
    }

    private function obterFiltros(): array
    {
        $periodo = $this->entradaGet('periodo');

        return [
            'periodo' => in_array($periodo, self::PERIODOS, true) ? $periodo : 'todos',
            'busca' => $this->limitar($this->entradaGet('busca'), 120),
        ];
    }

    private function resolverPeriodo(string $periodo): ?array
    {
        if (!in_array($periodo, self::PERIODOS, true)) {
            return null;
        }

        $hoje = new DateTimeImmutable('today', new DateTimeZone('America/Sao_Paulo'));

        return match ($periodo) {
            'hoje' => [
                'inicio' => $hoje->format('Y-m-d'),
                'fim' => $hoje->format('Y-m-d'),
            ],
            '7dias' => [
                'inicio' => $hoje->modify('-7 days')->format('Y-m-d'),
                'fim' => $hoje->format('Y-m-d'),
            ],
            '30dias' => [
                'inicio' => $hoje->modify('-30 days')->format('Y-m-d'),
                'fim' => $hoje->format('Y-m-d'),
            ],
            'mes' => [
                'inicio' => $hoje->modify('first day of this month')->format('Y-m-d'),
                'fim' => $hoje->format('Y-m-d'),
            ],
            default => [
                'inicio' => '',
                'fim' => '',
            ],
        };
    }

    private function relatoriosDisponiveis(array $filtros, array $vendas, array $clientes, array $financeiro, array $produtos): array
    {
        $itens = [
            [
                'titulo' => 'Desempenho comercial',
                'descricao' => "{$vendas['total']} pedidos registrados, {$vendas['concluidas']} concluídos.",
                'href' => 'vendas.php',
            ],
            [
                'titulo' => 'Clientes ativos e inativos',
                'descricao' => "{$clientes['total']} clientes cadastrados, {$clientes['ativos']} ativos e {$clientes['inativos']} inativos.",
                'href' => 'clientes.php',
            ],
            [
                'titulo' => 'Financeiro consolidado',
                'descricao' => self::valorExibicao($financeiro['total_receitas']) . ' em receitas e ' . self::valorExibicao($financeiro['total_despesas']) . ' em despesas.',
                'href' => 'financeiro.php',
            ],
            [
                'titulo' => 'Produtos e estoque',
                'descricao' => "{$produtos['total']} itens cadastrados, {$produtos['estoque_atencao']} com atenção de estoque.",
                'href' => 'produtos.php',
            ],
        ];

        if ($filtros['busca'] === '') {
            return $itens;
        }

        $busca = self::textoMinusculo($filtros['busca']);

        return array_values(array_filter($itens, static function (array $item) use ($busca): bool {
            $texto = self::textoMinusculo($item['titulo'] . ' ' . $item['descricao']);

            return str_contains($texto, $busca);
        }));
    }

    private function analiticos(array $vendas, array $financeiro): array
    {
        return [
            [
                'titulo' => 'Ticket médio',
                'descricao' => self::valorExibicao($vendas['ticket_medio']) . ' por pedido registrado.',
                'classe' => 'badge-info',
                'badge' => 'Atual',
            ],
            [
                'titulo' => 'Conversão comercial',
                'descricao' => $this->taxaConversao($vendas) . '% dos pedidos avançaram para aprovação.',
                'classe' => 'badge-success',
                'badge' => 'Positivo',
            ],
            [
                'titulo' => 'Pendências financeiras',
                'descricao' => "{$financeiro['pendentes']} lançamentos com status pendente.",
                'classe' => 'badge-warning',
                'badge' => 'Atenção',
            ],
            [
                'titulo' => 'Módulos em uso',
                'descricao' => self::MODULOS_ATIVOS . ' módulos ativos no ambiente atual.',
                'classe' => 'badge-neutral',
                'badge' => 'Base',
            ],
        ];
    }

    private function taxaConversao(array $vendas): int
    {
        if (($vendas['total'] ?? 0) <= 0) {
            return 0;
        }

        return (int) round(((int) $vendas['convertidas'] / (int) $vendas['total']) * 100);
    }

    private function entradaGet(string $campo): string
    {
        $valor = filter_input(INPUT_GET, $campo, FILTER_UNSAFE_RAW);

        return is_string($valor) ? trim($valor) : '';
    }

    private function limitar(string $valor, int $limite): string
    {
        return substr(trim($valor), 0, $limite);
    }

    public function rotuloPeriodo(string $periodo): string
    {
        return [
            'todos' => 'Todos os períodos',
            'hoje' => 'Hoje',
            '7dias' => 'Últimos 7 dias',
            '30dias' => 'Últimos 30 dias',
            'mes' => 'Este mês',
        ][$periodo] ?? 'Todos os períodos';
    }

    public static function valorExibicao(mixed $valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }

    private static function textoMinusculo(string $valor): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($valor, 'UTF-8');
        }

        return strtolower($valor);
    }

    public static function badgeFinanceiro(string $status): string
    {
        $statusNormalizado = strtolower($status);

        if (str_contains($statusNormalizado, 'cancelado')) {
            return 'badge-danger';
        }

        if (str_contains($statusNormalizado, 'recebido') || str_contains($statusNormalizado, 'pago')) {
            return 'badge-success';
        }

        if (str_contains($statusNormalizado, 'pendente')) {
            return 'badge-warning';
        }

        return 'badge-info';
    }

    public static function periodos(): array
    {
        return self::PERIODOS;
    }
}
