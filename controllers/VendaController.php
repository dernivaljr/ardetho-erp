<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Venda.php';

class VendaController
{
    private const STATUS_PERMITIDOS = ['Em análise', 'Aprovado', 'Faturado', 'Concluído', 'Cancelado', 'Pendente'];
    private const STATUS_FORMULARIO = ['Em análise', 'Aprovado', 'Faturado', 'Concluído', 'Cancelado'];
    private const FORMAS_PAGAMENTO = ['Boleto', 'Pix', 'Transferência', 'Cartão', 'Faturado'];

    private Venda $model;

    public function __construct()
    {
        $this->model = new Venda(obterConexaoBanco());
    }

    public function listar(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarAlteracaoStatus();
        }

        $idCliente = filter_input(INPUT_GET, 'cliente', FILTER_VALIDATE_INT);
        $filtros = [
            'busca' => $this->limitar($this->entradaGet('busca'), 120),
            'status' => $this->normalizarStatus($this->entradaGet('status')) ?? '',
            'id_cliente' => $idCliente && $idCliente > 0 ? (int) $idCliente : '',
        ];

        return [
            'vendas' => $this->model->listar($filtros),
            'clientesFiltro' => $this->model->listarClientesFiltro(),
            'resumo' => $this->model->obterResumo(),
            'filtros' => $filtros,
            'flash' => obterFlash(),
            'csrf' => csrfToken(),
        ];
    }

    public function formulario(): array
    {
        $idVenda = $this->obterIdVenda();
        $modoEdicao = $idVenda !== null;
        $venda = $modoEdicao ? $this->model->buscarPorId($idVenda) : null;
        $itensAtuais = $venda ? $this->model->buscarItens($idVenda) : [];

        if ($modoEdicao && $venda === null) {
            definirFlash('danger', 'Venda nao encontrada.');
            header('Location: vendas.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processarFormulario($idVenda, $venda, $itensAtuais);
        }

        $dados = $venda ? $this->vendaParaFormulario($venda) : $this->dadosVazios();
        $itens = $itensAtuais ? $this->itensParaFormulario($itensAtuais) : [$this->itemVazio()];

        return $this->dadosViewFormulario($modoEdicao, $venda, $dados, $itens, []);
    }

    private function processarAlteracaoStatus(): void
    {
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            definirFlash('danger', 'Acao recusada por token de seguranca invalido.');
            header('Location: vendas.php');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_venda', FILTER_VALIDATE_INT);
        $acao = $this->entradaPost('acao');

        if (!$id || $id < 1) {
            definirFlash('danger', 'Venda invalida.');
            header('Location: vendas.php');
            exit;
        }

        if ($acao === 'cancelar') {
            $this->model->alterarStatus((int) $id, 'Cancelado');
            definirFlash('success', 'Venda cancelada com sucesso.');
        } else {
            definirFlash('danger', 'Acao invalida.');
        }

        $destino = 'vendas.php';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        if ($query !== '') {
            $destino .= '?' . $query;
        }

        header('Location: ' . $destino);
        exit;
    }

    private function processarFormulario(?int $idVenda, ?array $vendaAtual, array $itensAtuais): array
    {
        $modoEdicao = $idVenda !== null;
        $dados = $this->obterDadosFormulario();
        $itensFormulario = $this->obterItensFormulario();
        $erros = [];
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            $erros[] = 'Acao recusada por token de seguranca invalido.';
        }

        if ($modoEdicao && $vendaAtual === null) {
            $erros[] = 'Venda invalida para edicao.';
        }

        $idsItensAtuais = array_map(static fn ($item) => (int) $item['id_produto'], $itensAtuais);
        $resultadoValidacao = $this->validarDados($dados, $itensFormulario, $modoEdicao, $vendaAtual, $idsItensAtuais);
        $erros = array_merge($erros, $resultadoValidacao['erros']);

        if ($erros) {
            if (isset($resultadoValidacao['venda']['valor_total'])) {
                $dados['valor_total'] = self::formatarMoedaFormulario($resultadoValidacao['venda']['valor_total']);
            }

            return $this->dadosViewFormulario(
                $modoEdicao,
                $vendaAtual,
                $dados,
                $itensFormulario ?: [$this->itemVazio()],
                array_values(array_unique($erros))
            );
        }

        $dadosBanco = $resultadoValidacao['venda'];
        $itensBanco = $resultadoValidacao['itens'];

        if ($idVenda !== null) {
            $this->model->atualizar($idVenda, $dadosBanco, $itensBanco);
            definirFlash('success', 'Venda atualizada com sucesso.');
        } else {
            $this->model->criar($dadosBanco, $itensBanco);
            definirFlash('success', 'Venda cadastrada com sucesso.');
        }

        header('Location: vendas.php');
        exit;
    }

    private function validarDados(array $dados, array $itensFormulario, bool $modoEdicao, ?array $vendaAtual, array $idsItensAtuais): array
    {
        $erros = [];
        $itensBanco = [];

        if (!in_array($dados['status'], self::STATUS_PERMITIDOS, true)) {
            $erros[] = 'Status invalido.';
        }

        if (!in_array($dados['forma_pagamento'], self::FORMAS_PAGAMENTO, true)) {
            $erros[] = 'Informe uma forma de pagamento valida.';
        }

        foreach (['codigo', 'data_venda', 'id_cliente'] as $campo) {
            if ($dados[$campo] === null || $dados[$campo] === '') {
                $erros[] = 'Preencha todos os campos obrigatorios da venda.';
                break;
            }
        }

        if (!$this->dataValida($dados['data_venda'])) {
            $erros[] = 'Informe uma data de venda valida.';
        }

        $cliente = $dados['id_cliente'] ? $this->model->buscarClientePorId((int) $dados['id_cliente']) : null;

        if ($cliente === null) {
            $erros[] = 'Cliente informado nao existe.';
        } elseif ($cliente['status'] !== 'Ativo' && (!$modoEdicao || (int) $vendaAtual['id_cliente'] !== (int) $cliente['id_cliente'])) {
            $erros[] = 'Cliente inativo nao pode ser usado em nova venda.';
        }

        if (!$itensFormulario) {
            $erros[] = 'Informe pelo menos um item para a venda.';
        }

        $idsProdutos = array_map(static fn ($item) => (int) ($item['id_produto'] ?? 0), $itensFormulario);
        $produtos = $this->model->buscarProdutosPorIds($idsProdutos);
        $idsUsados = [];
        $totalCentavos = 0;

        foreach ($itensFormulario as $indice => $item) {
            $idProduto = (int) ($item['id_produto'] ?? 0);
            $quantidadeMilesimos = $this->normalizarQuantidadeMilesimos((string) ($item['quantidade'] ?? ''));
            $valorCentavos = $this->normalizarMoedaCentavos((string) ($item['valor_unitario'] ?? ''));

            if ($idProduto <= 0) {
                $erros[] = 'Selecione todos os produtos ou servicos da venda.';
                continue;
            }

            if (isset($idsUsados[$idProduto])) {
                $erros[] = 'O item ja foi adicionado a venda.';
                continue;
            }

            $idsUsados[$idProduto] = true;

            if (!isset($produtos[$idProduto])) {
                $erros[] = 'Produto ou servico informado nao existe.';
                continue;
            }

            $produto = $produtos[$idProduto];
            if ($produto['status'] !== 'Ativo' && (!$modoEdicao || !in_array($idProduto, $idsItensAtuais, true))) {
                $erros[] = 'Produto ou servico inativo nao pode ser usado em nova venda.';
            }

            if ($quantidadeMilesimos === null || $quantidadeMilesimos <= 0) {
                $erros[] = 'Informe uma quantidade valida maior que zero.';
                continue;
            }

            if ($valorCentavos === null || $valorCentavos <= 0) {
                $erros[] = 'Informe um valor unitario valido maior que zero.';
                continue;
            }

            $subtotalCentavos = (int) round(($valorCentavos * $quantidadeMilesimos) / 1000);
            $totalCentavos += $subtotalCentavos;

            $itensFormulario[$indice]['subtotal'] = self::formatarMoedaFormulario($subtotalCentavos);
            $itensBanco[] = [
                'id_produto' => $idProduto,
                'quantidade' => $this->formatarQuantidadeBanco($quantidadeMilesimos),
                'valor_unitario' => self::formatarMoedaBanco($valorCentavos),
                'subtotal' => self::formatarMoedaBanco($subtotalCentavos),
            ];
        }

        if (!$itensBanco && !$erros) {
            $erros[] = 'Informe pelo menos um item valido para a venda.';
        }

        return [
            'erros' => array_values(array_unique($erros)),
            'venda' => [
                'codigo' => $dados['codigo'],
                'id_cliente' => (int) $dados['id_cliente'],
                'data_venda' => $dados['data_venda'],
                'status' => $dados['status'],
                'forma_pagamento' => $dados['forma_pagamento'],
                'condicao_pagamento' => $dados['condicao_pagamento'],
                'valor_total' => self::formatarMoedaBanco($totalCentavos),
                'observacoes' => $dados['observacoes'],
            ],
            'itens' => $itensBanco,
            'itens_formulario' => $itensFormulario,
        ];
    }

    private function obterDadosFormulario(): array
    {
        return [
            'codigo' => $this->normalizarTexto('codigo', 40),
            'status' => $this->normalizarStatus($this->entradaPost('status')) ?? 'Em análise',
            'data_venda' => $this->limitar($this->entradaPost('data_venda'), 10),
            'id_cliente' => filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT) ?: '',
            'forma_pagamento' => $this->normalizarFormaPagamento($this->entradaPost('forma_pagamento')) ?? '',
            'condicao_pagamento' => $this->normalizarTexto('condicao_pagamento', 80),
            'observacoes' => $this->normalizarTexto('observacoes', 5000),
        ];
    }

    private function obterItensFormulario(): array
    {
        $idsProdutos = $_POST['id_produto'] ?? [];
        $quantidades = $_POST['quantidade'] ?? [];
        $valoresUnitarios = $_POST['valor_unitario'] ?? [];
        $subtotais = $_POST['subtotal'] ?? [];

        if (!is_array($idsProdutos)) {
            return [];
        }

        $itens = [];
        $totalLinhas = count($idsProdutos);

        for ($indice = 0; $indice < $totalLinhas; $indice++) {
            $item = [
                'id_produto' => (string) ($idsProdutos[$indice] ?? ''),
                'quantidade' => trim((string) ($quantidades[$indice] ?? '')),
                'valor_unitario' => trim((string) ($valoresUnitarios[$indice] ?? '')),
                'subtotal' => trim((string) ($subtotais[$indice] ?? '')),
            ];

            if (implode('', $item) === '') {
                continue;
            }

            $itens[] = $item;
        }

        return $itens;
    }

    private function dadosViewFormulario(bool $modoEdicao, ?array $venda, array $dados, array $itens, array $erros): array
    {
        $idClienteAtual = $dados['id_cliente'] !== '' ? (int) $dados['id_cliente'] : null;
        $idsProdutosAtuais = array_map(static fn ($item) => (int) ($item['id_produto'] ?? 0), $itens);

        return [
            'modoEdicao' => $modoEdicao,
            'venda' => $venda,
            'dados' => $dados,
            'itens' => $itens,
            'clientes' => $this->model->listarClientesDisponiveis($idClienteAtual),
            'produtos' => $this->model->listarProdutosDisponiveis($idsProdutosAtuais),
            'erros' => $erros,
            'csrf' => csrfToken(),
        ];
    }

    private function vendaParaFormulario(array $venda): array
    {
        return [
            'codigo' => $venda['codigo'] ?? '',
            'status' => $venda['status'] ?? 'Em análise',
            'data_venda' => $venda['data_venda'] ?? date('Y-m-d'),
            'id_cliente' => (int) ($venda['id_cliente'] ?? 0),
            'forma_pagamento' => $venda['forma_pagamento'] ?? '',
            'condicao_pagamento' => $venda['condicao_pagamento'] ?? '',
            'observacoes' => $venda['observacoes'] ?? '',
            'valor_total' => self::formatarMoedaFormulario($venda['valor_total'] ?? ''),
        ];
    }

    private function itensParaFormulario(array $itens): array
    {
        return array_map(static fn ($item) => [
            'id_produto' => (int) $item['id_produto'],
            'quantidade' => self::formatarQuantidadeFormulario($item['quantidade']),
            'valor_unitario' => self::formatarPrecoFormulario($item['valor_unitario']),
            'subtotal' => self::formatarPrecoFormulario($item['subtotal']),
        ], $itens);
    }

    private function dadosVazios(): array
    {
        return [
            'codigo' => 'PED-' . date('His'),
            'status' => 'Em análise',
            'data_venda' => date('Y-m-d'),
            'id_cliente' => '',
            'forma_pagamento' => '',
            'condicao_pagamento' => '',
            'observacoes' => '',
            'valor_total' => '',
        ];
    }

    private function itemVazio(): array
    {
        return [
            'id_produto' => '',
            'quantidade' => '1',
            'valor_unitario' => '',
            'subtotal' => '',
        ];
    }

    private function obterIdVenda(): ?int
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

    private function normalizarStatus(string $status): ?string
    {
        $statusNormalizado = strtolower(trim($status));
        $mapa = [
            'em análise' => 'Em análise',
            'em analise' => 'Em análise',
            'aprovado' => 'Aprovado',
            'faturado' => 'Faturado',
            'concluído' => 'Concluído',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'pendente' => 'Pendente',
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

    private function normalizarQuantidadeMilesimos(string $valor): ?int
    {
        $valor = trim(str_replace(',', '.', $valor));

        if ($valor === '' || !preg_match('/^\d+(?:\.\d{1,3})?$/', $valor)) {
            return null;
        }

        [$inteiro, $decimal] = array_pad(explode('.', $valor, 2), 2, '');
        $inteiro = ltrim($inteiro, '0');
        $inteiro = $inteiro === '' ? '0' : $inteiro;

        if (strlen($inteiro) > 9) {
            return null;
        }

        return ((int) $inteiro * 1000) + (int) str_pad($decimal, 3, '0');
    }

    private function formatarQuantidadeBanco(int $milesimos): string
    {
        return number_format($milesimos / 1000, 3, '.', '');
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

    public static function itemNomeExibicao(array $produto): string
    {
        $codigo = (string) ($produto['codigo'] ?? '');
        $nome = (string) ($produto['nome'] ?? '');

        return trim(($codigo !== '' ? $codigo . ' - ' : '') . ($nome !== '' ? $nome : 'Item'));
    }

    public static function resumoItens(array $venda): string
    {
        $totalItens = (int) ($venda['total_itens'] ?? 0);
        $primeiroItem = (string) ($venda['primeiro_item_nome'] ?? '');

        if ($totalItens <= 0) {
            return '—';
        }

        if ($totalItens === 1) {
            return $primeiroItem !== '' ? $primeiroItem : '1 item';
        }

        return ($primeiroItem !== '' ? $primeiroItem . ' + ' : '') . ($totalItens - 1) . ' adicionais';
    }

    public static function tipoItensResumo(array $venda): string
    {
        $totalItens = (int) ($venda['total_itens'] ?? 0);

        if ($totalItens > 1) {
            return 'Múltiplos';
        }

        return (string) (($venda['primeiro_item_tipo'] ?? '') ?: '—');
    }

    public static function valorExibicao(mixed $valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }

    public static function dataExibicao(?string $valor): string
    {
        if (!$valor) {
            return '—';
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

        if (str_contains($statusNormalizado, 'faturado') || str_contains($statusNormalizado, 'analise') || str_contains($statusNormalizado, 'análise')) {
            return 'badge-info';
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

    public static function formatarPrecoFormulario(mixed $valor): string
    {
        return self::formatarMoedaFormulario($valor);
    }

    public static function formatarMoedaBanco(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }

    public static function formatarQuantidadeFormulario(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $formatado = number_format((float) $valor, 3, '.', '');

        return rtrim(rtrim($formatado, '0'), '.');
    }

    public static function statusFormulario(): array
    {
        return self::STATUS_FORMULARIO;
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
