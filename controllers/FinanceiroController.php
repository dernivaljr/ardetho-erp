<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Financeiro.php';

class FinanceiroController
{
    private const TIPOS = ['Receita', 'Despesa'];
    private const STATUS_PERMITIDOS = ['Pendente', 'Recebido', 'Pago', 'Cancelado'];
    private const FORMAS_PAGAMENTO = ['Boleto', 'Pix', 'Transferência', 'Cartão', 'Dinheiro', 'Faturado'];

    private Financeiro $model;

    public function __construct()
    {
        $this->model = new Financeiro(obterConexaoBanco());
    }

    public function listar(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarAlteracaoStatus();
        }

        $idCliente = filter_input(INPUT_GET, 'cliente', FILTER_VALIDATE_INT);
        $filtros = [
            'busca' => $this->limitar($this->entradaGet('busca'), 120),
            'tipo' => $this->normalizarTipo($this->entradaGet('tipo')) ?? '',
            'status' => $this->normalizarStatus($this->entradaGet('status')) ?? '',
            'id_cliente' => $idCliente && $idCliente > 0 ? (int) $idCliente : '',
        ];

        return [
            'lancamentos' => $this->model->listar($filtros),
            'clientesFiltro' => $this->model->listarClientesFiltro(),
            'resumo' => $this->model->obterResumo(),
            'filtros' => $filtros,
            'flash' => obterFlash(),
            'csrf' => csrfToken(),
        ];
    }

    public function formulario(): array
    {
        $idFinanceiro = $this->obterIdFinanceiro();
        $modoEdicao = $idFinanceiro !== null;
        $lancamento = $modoEdicao ? $this->model->buscarPorId($idFinanceiro) : null;

        if ($modoEdicao && $lancamento === null) {
            definirFlash('danger', 'Lancamento financeiro nao encontrado.');
            header('Location: financeiro.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processarFormulario($idFinanceiro, $lancamento);
        }

        $dados = $lancamento ? $this->lancamentoParaFormulario($lancamento) : $this->dadosVazios();

        return $this->dadosViewFormulario($modoEdicao, $lancamento, $dados, []);
    }

    private function processarAlteracaoStatus(): void
    {
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            definirFlash('danger', 'Acao recusada por token de seguranca invalido.');
            header('Location: financeiro.php');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_financeiro', FILTER_VALIDATE_INT);
        $acao = $this->entradaPost('acao');

        if (!$id || $id < 1) {
            definirFlash('danger', 'Lancamento financeiro invalido.');
            header('Location: financeiro.php');
            exit;
        }

        if ($acao === 'cancelar') {
            $this->model->alterarStatus((int) $id, 'Cancelado');
            definirFlash('success', 'Lancamento financeiro cancelado com sucesso.');
        } else {
            definirFlash('danger', 'Acao invalida.');
        }

        $destino = 'financeiro.php';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        if ($query !== '') {
            $destino .= '?' . $query;
        }

        header('Location: ' . $destino);
        exit;
    }

    private function processarFormulario(?int $idFinanceiro, ?array $lancamentoAtual): array
    {
        $modoEdicao = $idFinanceiro !== null;
        $dados = $this->obterDadosFormulario();
        $erros = [];
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            $erros[] = 'Acao recusada por token de seguranca invalido.';
        }

        if ($modoEdicao && $lancamentoAtual === null) {
            $erros[] = 'Lancamento financeiro invalido para edicao.';
        }

        $resultadoValidacao = $this->validarDados($dados, $modoEdicao, $lancamentoAtual);
        $erros = array_merge($erros, $resultadoValidacao['erros']);

        if ($erros) {
            if (isset($resultadoValidacao['dados']['valor'])) {
                $dados['valor'] = self::formatarMoedaFormulario($resultadoValidacao['dados']['valor']);
            }

            return $this->dadosViewFormulario(
                $modoEdicao,
                $lancamentoAtual,
                $dados,
                array_values(array_unique($erros))
            );
        }

        if ($idFinanceiro !== null) {
            $this->model->atualizar($idFinanceiro, $resultadoValidacao['dados']);
            definirFlash('success', 'Lancamento financeiro atualizado com sucesso.');
        } else {
            $this->model->criar($resultadoValidacao['dados']);
            definirFlash('success', 'Lancamento financeiro cadastrado com sucesso.');
        }

        header('Location: financeiro.php');
        exit;
    }

    private function validarDados(array $dados, bool $modoEdicao, ?array $lancamentoAtual): array
    {
        $erros = [];
        $valorCentavos = $this->normalizarMoedaCentavos((string) ($dados['valor'] ?? ''));

        if (!in_array($dados['tipo'], self::TIPOS, true)) {
            $erros[] = 'Tipo invalido.';
        }

        if (!in_array($dados['status'], self::STATUS_PERMITIDOS, true)) {
            $erros[] = 'Status invalido.';
        }

        if (!in_array($dados['forma_pagamento'], self::FORMAS_PAGAMENTO, true)) {
            $erros[] = 'Informe uma forma de pagamento valida.';
        }

        foreach (['codigo', 'tipo', 'status', 'data_lancamento', 'data_vencimento', 'forma_pagamento', 'categoria', 'descricao'] as $campo) {
            if ($dados[$campo] === null || $dados[$campo] === '') {
                $erros[] = 'Preencha todos os campos obrigatorios do lancamento.';
                break;
            }
        }

        if (!$this->dataValida($dados['data_lancamento'])) {
            $erros[] = 'Informe uma data de lancamento valida.';
        }

        if (!$this->dataValida($dados['data_vencimento'])) {
            $erros[] = 'Informe uma data de vencimento valida.';
        }

        if ($valorCentavos === null || $valorCentavos <= 0) {
            $erros[] = 'Informe um valor valido maior que zero.';
        }

        if ($dados['codigo'] !== '' && $this->model->codigoExiste($dados['codigo'], $modoEdicao ? (int) ($lancamentoAtual['id_financeiro'] ?? 0) : null)) {
            $erros[] = 'Ja existe um lancamento financeiro com este codigo.';
        }

        $idCliente = $dados['id_cliente'] !== '' ? (int) $dados['id_cliente'] : null;
        $idVenda = $dados['id_venda'] !== '' ? (int) $dados['id_venda'] : null;

        if ($idCliente !== null) {
            $cliente = $this->model->buscarClientePorId($idCliente);
            if ($cliente === null) {
                $erros[] = 'Cliente informado nao existe.';
            } elseif ($cliente['status'] !== 'Ativo' && (!$modoEdicao || (int) ($lancamentoAtual['id_cliente'] ?? 0) !== $idCliente)) {
                $erros[] = 'Cliente inativo nao pode ser usado em novo lancamento.';
            }
        }

        if ($idVenda !== null) {
            $venda = $this->model->buscarVendaPorId($idVenda);

            if ($venda === null) {
                $erros[] = 'Pedido relacionado nao existe.';
            } elseif (($venda['status'] ?? '') === 'Cancelado' && (!$modoEdicao || (int) ($lancamentoAtual['id_venda'] ?? 0) !== $idVenda)) {
                $erros[] = 'Pedido cancelado nao pode ser usado em novo lancamento.';
            } else {
                $idCliente = (int) ($venda['id_cliente'] ?? 0);
            }
        }

        if ($dados['tipo'] === 'Receita' && $idVenda === null) {
            $erros[] = 'Selecione um pedido relacionado para a receita.';
        }

        return [
            'erros' => array_values(array_unique($erros)),
            'dados' => [
                'codigo' => $dados['codigo'],
                'tipo' => $dados['tipo'],
                'descricao' => $dados['descricao'],
                'categoria' => $dados['categoria'],
                'valor' => $valorCentavos !== null ? self::formatarMoedaBanco($valorCentavos) : '0.00',
                'data_lancamento' => $dados['data_lancamento'],
                'data_vencimento' => $dados['data_vencimento'],
                'status' => $dados['status'],
                'forma_pagamento' => $dados['forma_pagamento'],
                'id_cliente' => $idCliente,
                'id_venda' => $idVenda,
                'observacoes' => $dados['observacoes'],
            ],
        ];
    }

    private function obterDadosFormulario(): array
    {
        return [
            'codigo' => $this->normalizarTexto('codigo', 40) ?? '',
            'tipo' => $this->normalizarTipo($this->entradaPost('tipo')) ?? 'Receita',
            'status' => $this->normalizarStatus($this->entradaPost('status')) ?? 'Pendente',
            'data_lancamento' => $this->limitar($this->entradaPost('data_lancamento'), 10),
            'data_vencimento' => $this->limitar($this->entradaPost('data_vencimento'), 10),
            'id_cliente' => filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT) ?: '',
            'id_venda' => filter_input(INPUT_POST, 'id_venda', FILTER_VALIDATE_INT) ?: '',
            'categoria' => $this->normalizarTexto('categoria', 100) ?? '',
            'descricao' => $this->normalizarTexto('descricao', 180) ?? '',
            'valor' => $this->limitar($this->entradaPost('valor'), 40),
            'forma_pagamento' => $this->normalizarFormaPagamento($this->entradaPost('forma_pagamento')) ?? '',
            'observacoes' => $this->normalizarTexto('observacoes', 5000),
        ];
    }

    private function dadosViewFormulario(bool $modoEdicao, ?array $lancamento, array $dados, array $erros): array
    {
        $idClienteAtual = $dados['id_cliente'] !== '' ? (int) $dados['id_cliente'] : null;
        $idVendaAtual = $dados['id_venda'] !== '' ? (int) $dados['id_venda'] : null;

        return [
            'modoEdicao' => $modoEdicao,
            'lancamento' => $lancamento,
            'dados' => $dados,
            'clientes' => $this->model->listarClientesDisponiveis($idClienteAtual),
            'vendas' => $this->model->listarVendasDisponiveis($idVendaAtual),
            'erros' => $erros,
            'csrf' => csrfToken(),
        ];
    }

    private function lancamentoParaFormulario(array $lancamento): array
    {
        return [
            'codigo' => $lancamento['codigo'] ?? '',
            'tipo' => $lancamento['tipo'] ?? 'Receita',
            'status' => $lancamento['status'] ?? 'Pendente',
            'data_lancamento' => $lancamento['data_lancamento'] ?? date('Y-m-d'),
            'data_vencimento' => $lancamento['data_vencimento'] ?? date('Y-m-d'),
            'id_cliente' => $lancamento['id_cliente'] ? (int) $lancamento['id_cliente'] : '',
            'id_venda' => $lancamento['id_venda'] ? (int) $lancamento['id_venda'] : '',
            'categoria' => $lancamento['categoria'] ?? '',
            'descricao' => $lancamento['descricao'] ?? '',
            'valor' => self::formatarMoedaFormulario($lancamento['valor'] ?? ''),
            'forma_pagamento' => $lancamento['forma_pagamento'] ?? '',
            'observacoes' => $lancamento['observacoes'] ?? '',
        ];
    }

    private function dadosVazios(): array
    {
        return [
            'codigo' => 'LAN-' . date('His'),
            'tipo' => 'Receita',
            'status' => 'Pendente',
            'data_lancamento' => date('Y-m-d'),
            'data_vencimento' => date('Y-m-d'),
            'id_cliente' => '',
            'id_venda' => '',
            'categoria' => '',
            'descricao' => '',
            'valor' => '',
            'forma_pagamento' => '',
            'observacoes' => '',
        ];
    }

    private function obterIdFinanceiro(): ?int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        return $id && $id > 0 ? (int) $id : null;
    }

    private function entradaGet(string $campo): string
    {
        $valor = filter_input(INPUT_GET, $campo, FILTER_UNSAFE_RAW);

        return is_string($valor) ? trim($valor) : '';
    }

    private function entradaPost(string $campo): string
    {
        $valor = filter_input(INPUT_POST, $campo, FILTER_UNSAFE_RAW);

        return is_string($valor) ? trim($valor) : '';
    }

    private function normalizarTexto(string $campo, int $limite): ?string
    {
        $valor = $this->limitar($this->entradaPost($campo), $limite);

        return $valor === '' ? null : $valor;
    }

    private function normalizarTipo(string $tipo): ?string
    {
        $tipoNormalizado = strtolower(trim($tipo));
        $mapa = [
            'receita' => 'Receita',
            'despesa' => 'Despesa',
        ];

        return $mapa[$tipoNormalizado] ?? null;
    }

    private function normalizarStatus(string $status): ?string
    {
        $statusNormalizado = strtolower(trim($status));
        $mapa = [
            'pendente' => 'Pendente',
            'recebido' => 'Recebido',
            'pago' => 'Pago',
            'cancelado' => 'Cancelado',
        ];

        return $mapa[$statusNormalizado] ?? null;
    }

    private function normalizarFormaPagamento(string $formaPagamento): ?string
    {
        $valorNormalizado = strtolower(trim($formaPagamento));
        $mapa = [
            'boleto' => 'Boleto',
            'pix' => 'Pix',
            'transferência' => 'Transferência',
            'transferencia' => 'Transferência',
            'cartão' => 'Cartão',
            'cartao' => 'Cartão',
            'dinheiro' => 'Dinheiro',
            'faturado' => 'Faturado',
        ];

        return $mapa[$valorNormalizado] ?? null;
    }

    private function dataValida(?string $valor): bool
    {
        if (!$valor) {
            return false;
        }

        $data = DateTimeImmutable::createFromFormat('Y-m-d', $valor);

        return $data && $data->format('Y-m-d') === $valor;
    }

    private function normalizarMoedaCentavos(string $valor): ?int
    {
        $valor = trim(str_replace(['R$', ' '], '', $valor));

        if ($valor === '') {
            return null;
        }

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valor)) {
            return null;
        }

        [$inteiro, $decimal] = array_pad(explode('.', $valor, 2), 2, '');
        $inteiro = ltrim($inteiro, '0');
        $inteiro = $inteiro === '' ? '0' : $inteiro;

        if (strlen($inteiro) > 10) {
            return null;
        }

        return ((int) $inteiro * 100) + (int) str_pad($decimal, 2, '0');
    }

    private function limitar(string $valor, int $limite): string
    {
        return substr(trim($valor), 0, $limite);
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

    public static function vendaNomeExibicao(array $venda): string
    {
        $codigo = (string) (($venda['codigo'] ?? $venda['venda_codigo'] ?? '') ?: 'Pedido');
        $cliente = self::clienteNomeExibicao($venda);

        return $codigo . ' - ' . $cliente;
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

        if (str_contains($statusNormalizado, 'recebido') || str_contains($statusNormalizado, 'pago')) {
            return 'badge-success';
        }

        if (str_contains($statusNormalizado, 'pendente')) {
            return 'badge-warning';
        }

        return 'badge-info';
    }

    public static function formatarMoedaFormulario(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        if (is_int($valor)) {
            return number_format($valor / 100, 2, ',', '.');
        }

        return number_format((float) $valor, 2, ',', '.');
    }

    public static function formatarMoedaBanco(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }

    public static function tipos(): array
    {
        return self::TIPOS;
    }

    public static function statusFormulario(): array
    {
        return self::STATUS_PERMITIDOS;
    }

    public static function statusFiltro(): array
    {
        return self::STATUS_PERMITIDOS;
    }

    public static function formasPagamento(): array
    {
        return self::FORMAS_PAGAMENTO;
    }
}
