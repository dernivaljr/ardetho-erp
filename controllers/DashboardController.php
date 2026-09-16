<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController
{
    private Dashboard $model;

    public function __construct()
    {
        $this->model = new Dashboard(obterConexaoBanco());
    }

    public function index(): array
    {
        $metricas = $this->model->obterMetricas();
        $vendasRecentes = $this->model->listarVendasRecentes();
        $resumoStatus = $this->model->listarResumoStatus();
        $faturamentoMensal = $this->prepararFaturamentoMensal($this->model->listarFaturamentoMensal());

        return [
            'metricas' => $metricas,
            'vendasRecentes' => $vendasRecentes,
            'resumoStatus' => $resumoStatus,
            'faturamentoMensal' => $faturamentoMensal,
            'atividades' => $this->prepararAtividades(
                $vendasRecentes,
                $this->model->buscarClienteMaisRecente(),
                $this->model->buscarProdutoMaisRecente()
            ),
            'modulos' => $this->modulosOperacionais(),
        ];
    }

    private function prepararFaturamentoMensal(array $linhas): array
    {
        $valoresPorMes = [];

        foreach ($linhas as $linha) {
            $valoresPorMes[(string) $linha['mes']] = (float) $linha['total'];
        }

        $mesAtual = new DateTimeImmutable('first day of this month');
        $meses = [];

        for ($indice = 5; $indice >= 0; $indice--) {
            $data = $mesAtual->modify("-{$indice} months");
            $chave = $data->format('Y-m');
            $meses[] = [
                'mes' => $chave,
                'rotulo' => $this->rotuloMes($data),
                'total' => $valoresPorMes[$chave] ?? 0.0,
            ];
        }

        return $meses;
    }

    private function prepararAtividades(array $vendasRecentes, ?array $clienteRecente, ?array $produtoRecente): array
    {
        $atividades = [];

        if ($vendasRecentes) {
            $venda = $vendasRecentes[0];
            $atividades[] = [
                'tipo' => 'success',
                'titulo' => 'Venda registrada',
                'descricao' => (($venda['codigo'] ?? '') ?: 'Pedido') . ' está com status ' . (($venda['status'] ?? '') ?: 'indefinido') . '.',
                'tempo' => self::dataExibicao($venda['data_venda'] ?? null),
            ];
        }

        if ($clienteRecente) {
            $atividades[] = [
                'tipo' => 'info',
                'titulo' => 'Cliente na base',
                'descricao' => self::clienteNomeExibicao($clienteRecente) . ' é o cadastro mais recente.',
                'tempo' => 'Recente',
            ];
        }

        if ($produtoRecente) {
            $atividades[] = [
                'tipo' => 'info',
                'titulo' => 'Item cadastrado',
                'descricao' => self::itemNomeExibicao($produtoRecente) . ' é o cadastro mais recente no catálogo.',
                'tempo' => 'Recente',
            ];
        }

        return array_slice($atividades, 0, 4);
    }

    private function modulosOperacionais(): array
    {
        return [
            ['nome' => 'Login', 'status' => 'Ativo', 'classe' => 'badge-success'],
            ['nome' => 'Dashboard', 'status' => 'Ativo', 'classe' => 'badge-success'],
            ['nome' => 'Clientes', 'status' => 'Ativo', 'classe' => 'badge-success'],
            ['nome' => 'Produtos/Serviços', 'status' => 'Ativo', 'classe' => 'badge-success'],
            ['nome' => 'Vendas', 'status' => 'Ativo', 'classe' => 'badge-success'],
            ['nome' => 'Financeiro', 'status' => 'Ativo', 'classe' => 'badge-success'],
            ['nome' => 'RH', 'status' => 'Ativo', 'classe' => 'badge-success'],
        ];
    }

    private function rotuloMes(DateTimeImmutable $data): string
    {
        $meses = [
            '01' => 'Jan',
            '02' => 'Fev',
            '03' => 'Mar',
            '04' => 'Abr',
            '05' => 'Mai',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Ago',
            '09' => 'Set',
            '10' => 'Out',
            '11' => 'Nov',
            '12' => 'Dez',
        ];

        return $meses[$data->format('m')] ?? $data->format('m/Y');
    }

    public static function clienteNomeExibicao(array $cliente): string
    {
        if (($cliente['tipo_pessoa'] ?? $cliente['cliente_tipo_pessoa'] ?? '') === 'PF') {
            return (string) (($cliente['nome'] ?? $cliente['cliente_nome'] ?? '') ?: 'Cliente');
        }

        return (string) (($cliente['nome_fantasia'] ?? $cliente['cliente_nome_fantasia'] ?? '')
            ?: ($cliente['razao_social'] ?? $cliente['cliente_razao_social'] ?? '')
            ?: 'Cliente');
    }

    public static function itemNomeExibicao(array $produto): string
    {
        $codigo = (string) ($produto['codigo'] ?? '');
        $nome = (string) ($produto['nome'] ?? '');

        return trim(($codigo !== '' ? $codigo . ' - ' : '') . ($nome !== '' ? $nome : 'Item'));
    }

    public static function valorExibicao(mixed $valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }

    public static function dataExibicao(?string $valor): string
    {
        if (!$valor) {
            return '-';
        }

        $data = DateTimeImmutable::createFromFormat('Y-m-d', $valor);

        return $data ? $data->format('d/m/Y') : $valor;
    }

    public static function badgeStatus(string $status): string
    {
        $statusNormalizado = strtolower($status);

        if (str_contains($statusNormalizado, 'cancelado')) {
            return 'badge-danger';
        }

        if (str_contains($statusNormalizado, 'aprovado') || str_contains($statusNormalizado, 'conclu')) {
            return 'badge-success';
        }

        if (str_contains($statusNormalizado, 'pendente')) {
            return 'badge-warning';
        }

        return 'badge-info';
    }
}
