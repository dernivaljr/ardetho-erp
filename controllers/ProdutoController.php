<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Produto.php';

class ProdutoController
{
    private const TIPOS_ITEM = ['Produto', 'Serviço'];
    private const STATUS_CADASTRAIS = ['Ativo', 'Em análise', 'Inativo'];
    private const STATUS_FILTRO = [
        'Disponível',
        'Baixo estoque',
        'Indisponível',
        'Ativo',
        'Em análise',
        'Inativo',
    ];

    private Produto $model;

    public function __construct()
    {
        $this->model = new Produto(obterConexaoBanco());
    }

    public function listar(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarAlteracaoStatus();
        }

        $filtros = [
            'busca' => $this->limitar($this->entradaGet('busca'), 120),
            'categoria' => $this->limitar($this->entradaGet('categoria'), 100),
            'status' => $this->normalizarStatusFiltro($this->entradaGet('status')) ?? '',
        ];

        return [
            'produtos' => $this->model->listar($filtros),
            'categorias' => $this->model->listarCategorias(),
            'resumo' => $this->model->obterResumo(),
            'filtros' => $filtros,
            'flash' => obterFlash(),
            'csrf' => csrfToken(),
        ];
    }

    public function formulario(): array
    {
        $idProduto = $this->obterIdProduto();
        $modoEdicao = $idProduto !== null;
        $produto = $modoEdicao ? $this->model->buscarPorId($idProduto) : null;

        if ($modoEdicao && $produto === null) {
            definirFlash('danger', 'Item nao encontrado.');
            header('Location: produtos.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processarFormulario($idProduto, $produto);
        }

        return [
            'modoEdicao' => $modoEdicao,
            'produto' => $produto,
            'dados' => $produto ? $this->produtoParaFormulario($produto) : $this->dadosVazios(),
            'erros' => [],
            'csrf' => csrfToken(),
        ];
    }

    private function processarAlteracaoStatus(): void
    {
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            definirFlash('danger', 'Acao recusada por token de seguranca invalido.');
            header('Location: produtos.php');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
        $acao = $this->entradaPost('acao');

        if (!$id || $id < 1) {
            definirFlash('danger', 'Item invalido.');
            header('Location: produtos.php');
            exit;
        }

        if ($acao === 'desativar') {
            $this->model->alterarStatus((int) $id, 'Inativo');
            definirFlash('success', 'Item desativado com sucesso.');
        } elseif ($acao === 'ativar') {
            $this->model->alterarStatus((int) $id, 'Ativo');
            definirFlash('success', 'Item ativado com sucesso.');
        } else {
            definirFlash('danger', 'Acao invalida.');
        }

        $destino = 'produtos.php';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        if ($query !== '') {
            $destino .= '?' . $query;
        }

        header('Location: ' . $destino);
        exit;
    }

    private function processarFormulario(?int $idProduto, ?array $produtoAtual): array
    {
        $dados = $this->obterDadosFormulario();
        $erros = [];
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            $erros[] = 'Acao recusada por token de seguranca invalido.';
        }

        $erros = array_merge($erros, $this->validarDados($dados));

        if ($idProduto !== null && $produtoAtual === null) {
            $erros[] = 'Item invalido para edicao.';
        }

        if ($dados['codigo'] !== null && $dados['codigo'] !== '' && $this->model->codigoExiste($dados['codigo'], $idProduto)) {
            $erros[] = 'Ja existe um item cadastrado com este codigo.';
        }

        if ($erros) {
            return [
                'modoEdicao' => $idProduto !== null,
                'produto' => $produtoAtual,
                'dados' => $dados,
                'erros' => array_values(array_unique($erros)),
                'csrf' => csrfToken(),
            ];
        }

        $dadosBanco = $this->dadosParaBanco($dados);

        try {
            if ($idProduto !== null) {
                $this->model->atualizar($idProduto, $dadosBanco);
                definirFlash('success', 'Item atualizado com sucesso.');
            } else {
                $this->model->criar($dadosBanco);
                definirFlash('success', 'Item cadastrado com sucesso.');
            }
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return [
                    'modoEdicao' => $idProduto !== null,
                    'produto' => $produtoAtual,
                    'dados' => $dados,
                    'erros' => ['Ja existe um item cadastrado com este codigo.'],
                    'csrf' => csrfToken(),
                ];
            }

            throw $exception;
        }

        header('Location: produtos.php');
        exit;
    }

    private function obterDadosFormulario(): array
    {
        $tipoItem = $this->normalizarTipoItem($this->entradaPost('tipo_item')) ?? 'Produto';
        $status = $this->normalizarStatusCadastral($this->entradaPost('status')) ?? 'Ativo';

        return [
            'tipo_item' => $tipoItem,
            'codigo' => $this->normalizarTexto('codigo', 40),
            'nome' => $this->normalizarTexto('nome', 160),
            'categoria' => $this->normalizarTexto('categoria', 100),
            'descricao' => $this->normalizarTexto('descricao', 5000),
            'preco' => $this->limitar($this->entradaPost('preco'), 30),
            'unidade' => $this->normalizarTexto('unidade', 30),
            'status' => $status,
            'estoque' => $this->limitar($this->entradaPost('estoque'), 30),
            'estoque_minimo' => $this->limitar($this->entradaPost('estoque_minimo'), 30),
            'marca' => $this->normalizarTexto('marca', 100),
            'fornecedor' => $this->normalizarTexto('fornecedor', 120),
            'ncm' => $this->normalizarTexto('ncm', 20),
            'prazo_estimado' => $this->normalizarTexto('prazo_estimado', 60),
            'departamento' => $this->normalizarTexto('departamento', 100),
        ];
    }

    private function validarDados(array $dados): array
    {
        $erros = [];
        $preco = $this->normalizarDecimal($dados['preco'], 2, 10);

        if (!in_array($dados['tipo_item'], self::TIPOS_ITEM, true)) {
            $erros[] = 'Tipo de item invalido.';
        }

        if (!in_array($dados['status'], self::STATUS_CADASTRAIS, true)) {
            $erros[] = 'Status invalido.';
        }

        if ($dados['tipo_item'] === 'Produto' && !in_array($dados['status'], ['Ativo', 'Inativo'], true)) {
            $erros[] = 'Produtos podem usar somente status cadastral Ativo ou Inativo.';
        }

        foreach (['codigo', 'nome', 'categoria', 'unidade'] as $campo) {
            if ($dados[$campo] === null || $dados[$campo] === '') {
                $erros[] = 'Preencha todos os campos obrigatorios de identificacao e comercial.';
                break;
            }
        }

        if ($preco === null || (float) $preco <= 0) {
            $erros[] = 'Informe um preco valido maior que zero.';
        }

        if ($dados['tipo_item'] === 'Produto') {
            foreach (['marca', 'fornecedor', 'ncm'] as $campo) {
                if ($dados[$campo] === null || $dados[$campo] === '') {
                    $erros[] = 'Preencha todos os campos obrigatorios do produto.';
                    break;
                }
            }

            $estoque = $this->normalizarDecimal($dados['estoque'], 3, 9);
            $estoqueMinimo = $this->normalizarDecimal($dados['estoque_minimo'], 3, 9);

            if ($estoque === null || (float) $estoque < 0) {
                $erros[] = 'Informe um estoque atual valido.';
            }

            if ($estoqueMinimo === null || (float) $estoqueMinimo < 0) {
                $erros[] = 'Informe um estoque minimo valido.';
            }

            if ($dados['ncm'] && strlen($this->somenteDigitos($dados['ncm'])) !== 8) {
                $erros[] = 'Informe um NCM valido com 8 digitos.';
            }
        }

        if ($dados['tipo_item'] === 'Serviço') {
            foreach (['prazo_estimado', 'departamento'] as $campo) {
                if ($dados[$campo] === null || $dados[$campo] === '') {
                    $erros[] = 'Preencha todos os campos obrigatorios do servico.';
                    break;
                }
            }
        }

        return array_values(array_unique($erros));
    }

    private function dadosParaBanco(array $dados): array
    {
        $dadosBanco = [
            'tipo_item' => $dados['tipo_item'],
            'codigo' => $dados['codigo'],
            'nome' => $dados['nome'],
            'categoria' => $dados['categoria'],
            'descricao' => $dados['descricao'],
            'preco' => $this->normalizarDecimal($dados['preco'], 2, 10) ?? '0.00',
            'unidade' => $dados['unidade'],
            'status' => $dados['status'],
            'estoque' => null,
            'estoque_minimo' => null,
            'marca' => null,
            'fornecedor' => null,
            'ncm' => null,
            'prazo_estimado' => null,
            'departamento' => null,
        ];

        if ($dados['tipo_item'] === 'Produto') {
            $dadosBanco['estoque'] = $this->normalizarDecimal($dados['estoque'], 3, 9);
            $dadosBanco['estoque_minimo'] = $this->normalizarDecimal($dados['estoque_minimo'], 3, 9);
            $dadosBanco['marca'] = $dados['marca'];
            $dadosBanco['fornecedor'] = $dados['fornecedor'];
            $dadosBanco['ncm'] = $dados['ncm'];
        } else {
            $dadosBanco['prazo_estimado'] = $dados['prazo_estimado'];
            $dadosBanco['departamento'] = $dados['departamento'];
        }

        return $dadosBanco;
    }

    private function produtoParaFormulario(array $produto): array
    {
        return [
            'tipo_item' => $produto['tipo_item'] ?? 'Produto',
            'codigo' => $produto['codigo'] ?? '',
            'nome' => $produto['nome'] ?? '',
            'categoria' => $produto['categoria'] ?? '',
            'descricao' => $produto['descricao'] ?? '',
            'preco' => self::formatarPrecoFormulario($produto['preco'] ?? '0.00'),
            'unidade' => $produto['unidade'] ?? '',
            'status' => $produto['status'] ?? 'Ativo',
            'estoque' => self::formatarQuantidadeFormulario($produto['estoque'] ?? ''),
            'estoque_minimo' => self::formatarQuantidadeFormulario($produto['estoque_minimo'] ?? ''),
            'marca' => $produto['marca'] ?? '',
            'fornecedor' => $produto['fornecedor'] ?? '',
            'ncm' => $produto['ncm'] ?? '',
            'prazo_estimado' => $produto['prazo_estimado'] ?? '',
            'departamento' => $produto['departamento'] ?? '',
        ];
    }

    private function dadosVazios(): array
    {
        return [
            'tipo_item' => 'Produto',
            'codigo' => '',
            'nome' => '',
            'categoria' => '',
            'descricao' => '',
            'preco' => '',
            'unidade' => '',
            'status' => 'Ativo',
            'estoque' => '',
            'estoque_minimo' => '',
            'marca' => '',
            'fornecedor' => '',
            'ncm' => '',
            'prazo_estimado' => '',
            'departamento' => '',
        ];
    }

    private function obterIdProduto(): ?int
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

    private function normalizarTipoItem(string $tipoItem): ?string
    {
        $tipoNormalizado = strtolower(trim($tipoItem));
        $mapa = [
            'produto' => 'Produto',
            'serviço' => 'Serviço',
            'servico' => 'Serviço',
        ];

        return $mapa[$tipoNormalizado] ?? null;
    }

    private function normalizarStatusCadastral(string $status): ?string
    {
        $statusNormalizado = strtolower(trim($status));
        $mapa = [
            'ativo' => 'Ativo',
            'em análise' => 'Em análise',
            'em analise' => 'Em análise',
            'inativo' => 'Inativo',
        ];

        return $mapa[$statusNormalizado] ?? null;
    }

    private function normalizarStatusFiltro(string $status): ?string
    {
        $statusNormalizado = strtolower(trim($status));
        $mapa = [
            'disponível' => 'Disponível',
            'disponivel' => 'Disponível',
            'baixo estoque' => 'Baixo estoque',
            'indisponível' => 'Indisponível',
            'indisponivel' => 'Indisponível',
            'ativo' => 'Ativo',
            'em análise' => 'Em análise',
            'em analise' => 'Em análise',
            'inativo' => 'Inativo',
        ];

        return $mapa[$statusNormalizado] ?? null;
    }

    private function normalizarDecimal(string $valor, int $escala, int $digitosInteiros): ?string
    {
        $valor = trim(str_replace(['R$', ' '], '', $valor));

        if ($valor === '') {
            return null;
        }

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        if (!preg_match('/^\d+(?:\.\d+)?$/', $valor)) {
            return null;
        }

        [$inteiro, $decimal] = array_pad(explode('.', $valor, 2), 2, '');
        $inteiro = ltrim($inteiro, '0');
        $inteiro = $inteiro === '' ? '0' : $inteiro;

        if (strlen($inteiro) > $digitosInteiros || strlen($decimal) > $escala) {
            return null;
        }

        return $inteiro . '.' . str_pad($decimal, $escala, '0');
    }

    private function limitar(string $valor, int $limite): string
    {
        return substr(trim($valor), 0, $limite);
    }

    private function somenteDigitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }

    public static function precoExibicao(mixed $preco): string
    {
        return 'R$ ' . number_format((float) $preco, 2, ',', '.');
    }

    public static function estoqueExibicao(array $produto): string
    {
        if (($produto['tipo_item'] ?? '') === 'Serviço') {
            return '—';
        }

        if (($produto['estoque'] ?? null) === null || $produto['estoque'] === '') {
            return '—';
        }

        return self::formatarQuantidadeFormulario($produto['estoque']);
    }

    public static function statusExibicao(array $produto): string
    {
        if (($produto['status'] ?? '') === 'Inativo') {
            return 'Inativo';
        }

        if (($produto['tipo_item'] ?? '') !== 'Produto') {
            return (string) (($produto['status'] ?? '') ?: 'Ativo');
        }

        $estoque = (float) ($produto['estoque'] ?? 0);
        $estoqueMinimo = (float) ($produto['estoque_minimo'] ?? 0);

        if ($estoque <= 0) {
            return 'Indisponível';
        }

        if ($estoque <= $estoqueMinimo) {
            return 'Baixo estoque';
        }

        return 'Disponível';
    }

    public static function badgeStatus(string $status): string
    {
        $statusNormalizado = strtolower($status);

        if (str_contains($statusNormalizado, 'inativo') || str_contains($statusNormalizado, 'indispon')) {
            return 'badge-danger';
        }

        if (str_contains($statusNormalizado, 'baixo')) {
            return 'badge-warning';
        }

        if (str_contains($statusNormalizado, 'analise') || str_contains($statusNormalizado, 'análise')) {
            return 'badge-info';
        }

        if (str_contains($statusNormalizado, 'dispon') || str_contains($statusNormalizado, 'ativo')) {
            return 'badge-success';
        }

        return 'badge-info';
    }

    public static function formatarPrecoFormulario(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        return number_format((float) $valor, 2, ',', '.');
    }

    public static function formatarQuantidadeFormulario(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $formatado = number_format((float) $valor, 3, '.', '');

        return rtrim(rtrim($formatado, '0'), '.');
    }

    public static function opcoesStatusFormulario(string $tipoItem): array
    {
        if ($tipoItem === 'Produto') {
            return ['Ativo', 'Inativo'];
        }

        return self::STATUS_CADASTRAIS;
    }

    public static function opcoesStatusFiltro(): array
    {
        return self::STATUS_FILTRO;
    }
}
