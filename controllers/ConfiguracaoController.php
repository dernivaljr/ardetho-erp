<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Configuracao.php';

class ConfiguracaoController
{
    private const OPCOES = [
        'theme_mode' => ['light', 'dark'],
        'language' => ['Português (Brasil)', 'English', 'Español'],
        'date_format' => ['DD/MM/AAAA', 'MM/DD/AAAA', 'AAAA-MM-DD'],
        'timezone' => ['America/Sao_Paulo', 'UTC'],
        'main_module' => ['Dashboard', 'Clientes', 'Vendas', 'Financeiro'],
        'priority_module' => ['Financeiro', 'Vendas', 'Relatórios', 'Produtos'],
        'startup_view' => ['Resumo executivo', 'Lista de atividades', 'Métricas principais'],
    ];

    private const BOOLEANOS = [
        'sidebar_compact',
        'dashboard_shortcuts',
        'alerts_expiration',
        'alerts_orders',
        'daily_summary',
    ];

    private Configuracao $model;

    public function __construct()
    {
        $this->model = new Configuracao(obterConexaoBanco());
    }

    public function index(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarFormulario();
        }

        return [
            'configuracoes' => $this->model->obterTodas(self::padroes()),
            'flash' => obterFlash(),
            'csrf' => csrfToken(),
        ];
    }

    private function processarFormulario(): void
    {
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            definirFlash('danger', 'Acao recusada por token de seguranca invalido.');
            header('Location: configuracoes.php');
            exit;
        }

        $acao = $this->entradaPost('acao');

        if ($acao === 'resetar') {
            $this->model->salvar(self::padroes());
            definirFlash('success', 'Configurações restauradas para o padrão.');
            header('Location: configuracoes.php');
            exit;
        }

        $dados = $this->obterDadosFormulario();
        $erros = $this->validarDados($dados);

        if ($erros) {
            definirFlash('danger', implode(' ', array_unique($erros)));
            header('Location: configuracoes.php');
            exit;
        }

        $this->model->salvar($dados);
        definirFlash('success', 'Configurações salvas com sucesso.');
        header('Location: configuracoes.php');
        exit;
    }

    private function obterDadosFormulario(): array
    {
        $dados = [];

        foreach (self::OPCOES as $campo => $opcoes) {
            $valor = $this->entradaPost($campo);
            $dados[$campo] = in_array($valor, $opcoes, true) ? $valor : self::padroes()[$campo];
        }

        foreach (self::BOOLEANOS as $campo) {
            $dados[$campo] = $this->entradaPost($campo) === '1' ? '1' : '0';
        }

        foreach ([
            'company_name' => 160,
            'company_display_name' => 160,
            'company_logo_url' => 255,
            'company_icon_url' => 255,
            'brand_primary_color' => 20,
            'brand_accent_color' => 20,
        ] as $campo => $limite) {
            $dados[$campo] = $this->limitar($this->entradaPost($campo), $limite);
        }

        return $dados;
    }

    private function validarDados(array $dados): array
    {
        $erros = [];

        if ($dados['company_name'] === '' || $dados['company_display_name'] === '') {
            $erros[] = 'Informe o nome da empresa e o nome exibido no sistema.';
        }

        foreach (['brand_primary_color', 'brand_accent_color'] as $campo) {
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $dados[$campo])) {
                $erros[] = 'Informe cores em formato hexadecimal, como #2563EB.';
                break;
            }
        }

        return $erros;
    }

    private function entradaPost(string $campo): string
    {
        $valor = filter_input(INPUT_POST, $campo, FILTER_UNSAFE_RAW);

        return is_string($valor) ? trim($valor) : '';
    }

    private function limitar(string $valor, int $limite): string
    {
        return substr(trim($valor), 0, $limite);
    }

    public static function padroes(): array
    {
        return [
            'theme_mode' => 'light',
            'sidebar_compact' => '0',
            'dashboard_shortcuts' => '1',
            'alerts_expiration' => '1',
            'alerts_orders' => '1',
            'daily_summary' => '0',
            'language' => 'Português (Brasil)',
            'date_format' => 'DD/MM/AAAA',
            'timezone' => 'America/Sao_Paulo',
            'main_module' => 'Dashboard',
            'priority_module' => 'Financeiro',
            'startup_view' => 'Resumo executivo',
            'company_name' => 'Ardetho ERP',
            'company_display_name' => 'Ardetho ERP',
            'company_logo_url' => 'assets/images/ardetho-logo.png',
            'company_icon_url' => 'assets/images/ardetho-icon.png',
            'brand_primary_color' => '#2563EB',
            'brand_accent_color' => '#60A5FA',
        ];
    }

    public static function opcoes(string $campo): array
    {
        return self::OPCOES[$campo] ?? [];
    }

    public static function ativo(array $configuracoes, string $campo): bool
    {
        return ($configuracoes[$campo] ?? '0') === '1';
    }
}
