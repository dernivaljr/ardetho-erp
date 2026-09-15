<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Cliente.php';

class ClienteController
{
    private const STATUS_PERMITIDOS = ['Ativo', 'Em análise', 'Pendente', 'Inativo'];
    private const TIPOS_PESSOA = ['PF', 'PJ'];

    private Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente(obterConexaoBanco());
    }

    public function listar(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarAlteracaoStatus();
        }

        $filtros = [
            'busca' => $this->limitar($this->entradaGet('busca'), 120),
            'status' => $this->normalizarStatus($this->entradaGet('status')) ?? '',
            'cidade' => $this->limitar($this->entradaGet('cidade'), 100),
        ];

        return [
            'clientes' => $this->model->listar($filtros),
            'cidades' => $this->model->listarCidades(),
            'filtros' => $filtros,
            'flash' => obterFlash(),
            'csrf' => csrfToken(),
        ];
    }

    public function formulario(): array
    {
        $idCliente = $this->obterIdCliente();
        $modoEdicao = $idCliente !== null;
        $cliente = $modoEdicao ? $this->model->buscarPorId($idCliente) : null;

        if ($modoEdicao && $cliente === null) {
            definirFlash('danger', 'Cliente nao encontrado.');
            header('Location: clientes.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processarFormulario($idCliente, $cliente);
        }

        return [
            'modoEdicao' => $modoEdicao,
            'cliente' => $cliente,
            'dados' => $cliente ? $this->clienteParaFormulario($cliente) : $this->dadosVazios(),
            'erros' => [],
            'csrf' => csrfToken(),
        ];
    }

    private function processarAlteracaoStatus(): void
    {
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            definirFlash('danger', 'Acao recusada por token de seguranca invalido.');
            header('Location: clientes.php');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
        $acao = $this->entradaPost('acao');

        if (!$id || $id < 1) {
            definirFlash('danger', 'Cliente invalido.');
            header('Location: clientes.php');
            exit;
        }

        if ($acao === 'desativar') {
            $this->model->alterarStatus((int) $id, 'Inativo');
            definirFlash('success', 'Cliente desativado com sucesso.');
        } elseif ($acao === 'ativar') {
            $this->model->alterarStatus((int) $id, 'Ativo');
            definirFlash('success', 'Cliente ativado com sucesso.');
        } else {
            definirFlash('danger', 'Acao invalida.');
        }

        $destino = 'clientes.php';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        if ($query !== '') {
            $destino .= '?' . $query;
        }

        header('Location: ' . $destino);
        exit;
    }

    private function processarFormulario(?int $idCliente, ?array $clienteAtual): array
    {
        $dados = $this->obterDadosFormulario();
        $erros = [];
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            $erros[] = 'Acao recusada por token de seguranca invalido.';
        }

        $erros = array_merge($erros, $this->validarDados($dados));

        if ($idCliente !== null && $clienteAtual === null) {
            $erros[] = 'Cliente invalido para edicao.';
        }

        if ($erros) {
            return [
                'modoEdicao' => $idCliente !== null,
                'cliente' => $clienteAtual,
                'dados' => $dados,
                'erros' => $erros,
                'csrf' => csrfToken(),
            ];
        }

        if ($idCliente !== null) {
            $this->model->atualizar($idCliente, $dados);
            definirFlash('success', 'Cliente atualizado com sucesso.');
        } else {
            $idCliente = $this->model->criar($dados);
            definirFlash('success', 'Cliente cadastrado com sucesso.');
        }

        header('Location: clientes.php');
        exit;
    }

    private function obterDadosFormulario(): array
    {
        $tipoPessoa = strtoupper($this->entradaPost('tipo_pessoa'));
        $status = $this->normalizarStatus($this->entradaPost('status')) ?? 'Ativo';
        $email = $this->normalizarTexto('email', 190);
        $emailNf = $this->normalizarTexto('email_nf', 190);

        return [
            'tipo_pessoa' => in_array($tipoPessoa, self::TIPOS_PESSOA, true) ? $tipoPessoa : 'PF',
            'status' => $status,
            'nome' => $this->normalizarTexto('nome', 160),
            'cpf' => $this->normalizarTexto('cpf', 20),
            'rg' => $this->normalizarTexto('rg', 30),
            'data_nascimento' => $this->normalizarData($this->entradaPost('data_nascimento')),
            'razao_social' => $this->normalizarTexto('razao_social', 180),
            'nome_fantasia' => $this->normalizarTexto('nome_fantasia', 160),
            'cnpj' => $this->normalizarTexto('cnpj', 24),
            'inscricao_estadual' => $this->normalizarTexto('inscricao_estadual', 40),
            'contato' => $this->normalizarTexto('contato', 120),
            'email' => $email === null ? null : strtolower($email),
            'email_nf' => $emailNf === null ? null : strtolower($emailNf),
            'telefone' => $this->normalizarTexto('telefone', 30),
            'whatsapp' => $this->normalizarTexto('whatsapp', 30),
            'cep' => $this->normalizarTexto('cep', 12),
            'logradouro' => $this->normalizarTexto('logradouro', 180),
            'numero' => $this->normalizarTexto('numero', 30),
            'complemento' => $this->normalizarTexto('complemento', 120),
            'bairro' => $this->normalizarTexto('bairro', 100),
            'cidade' => $this->normalizarTexto('cidade', 100),
            'estado' => strtoupper((string) $this->normalizarTexto('estado', 2)),
            'observacoes' => $this->normalizarTexto('observacoes', 5000),
        ];
    }

    private function validarDados(array $dados): array
    {
        $erros = [];

        if (!in_array($dados['tipo_pessoa'], self::TIPOS_PESSOA, true)) {
            $erros[] = 'Tipo de pessoa invalido.';
        }

        if (!in_array($dados['status'], self::STATUS_PERMITIDOS, true)) {
            $erros[] = 'Status invalido.';
        }

        foreach (['email', 'telefone', 'cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado'] as $campo) {
            if ($dados[$campo] === null || $dados[$campo] === '') {
                $erros[] = 'Preencha todos os campos obrigatorios de contato e endereco.';
                break;
            }
        }

        if ($dados['email'] && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um e-mail principal valido.';
        }

        if ($dados['email_nf'] && !filter_var($dados['email_nf'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um e-mail de NF valido.';
        }

        if ($dados['estado'] && !preg_match('/^[A-Z]{2}$/', $dados['estado'])) {
            $erros[] = 'Informe uma UF valida com 2 letras.';
        }

        if ($dados['cep'] && strlen($this->somenteDigitos($dados['cep'])) !== 8) {
            $erros[] = 'Informe um CEP valido.';
        }

        if ($dados['telefone'] && !in_array(strlen($this->somenteDigitos($dados['telefone'])), [10, 11], true)) {
            $erros[] = 'Informe um telefone valido com DDD.';
        }

        if ($dados['whatsapp'] && !in_array(strlen($this->somenteDigitos($dados['whatsapp'])), [10, 11], true)) {
            $erros[] = 'Informe um WhatsApp valido com DDD.';
        }

        if ($dados['tipo_pessoa'] === 'PF') {
            foreach (['nome', 'cpf', 'rg', 'data_nascimento'] as $campo) {
                if ($dados[$campo] === null || $dados[$campo] === '') {
                    $erros[] = 'Preencha todos os campos obrigatorios da pessoa fisica.';
                    break;
                }
            }

            if ($dados['cpf'] && strlen($this->somenteDigitos($dados['cpf'])) !== 11) {
                $erros[] = 'Informe um CPF valido.';
            }
        }

        if ($dados['tipo_pessoa'] === 'PJ') {
            foreach (['razao_social', 'nome_fantasia', 'cnpj', 'inscricao_estadual'] as $campo) {
                if ($dados[$campo] === null || $dados[$campo] === '') {
                    $erros[] = 'Preencha todos os campos obrigatorios da pessoa juridica.';
                    break;
                }
            }

            if ($dados['cnpj'] && strlen($this->somenteDigitos($dados['cnpj'])) !== 14) {
                $erros[] = 'Informe um CNPJ valido.';
            }
        }

        return array_values(array_unique($erros));
    }

    private function clienteParaFormulario(array $cliente): array
    {
        return [
            'tipo_pessoa' => $cliente['tipo_pessoa'] ?? 'PF',
            'status' => $cliente['status'] ?? 'Ativo',
            'nome' => $cliente['nome'] ?? '',
            'cpf' => $cliente['cpf'] ?? '',
            'rg' => $cliente['rg'] ?? '',
            'data_nascimento' => $cliente['data_nascimento'] ?? '',
            'razao_social' => $cliente['razao_social'] ?? '',
            'nome_fantasia' => $cliente['nome_fantasia'] ?? '',
            'cnpj' => $cliente['cnpj'] ?? '',
            'inscricao_estadual' => $cliente['inscricao_estadual'] ?? '',
            'contato' => $cliente['contato'] ?? '',
            'email' => $cliente['email'] ?? '',
            'email_nf' => $cliente['email_nf'] ?? '',
            'telefone' => $cliente['telefone'] ?? '',
            'whatsapp' => $cliente['whatsapp'] ?? '',
            'cep' => $cliente['cep'] ?? '',
            'logradouro' => $cliente['logradouro'] ?? '',
            'numero' => $cliente['numero'] ?? '',
            'complemento' => $cliente['complemento'] ?? '',
            'bairro' => $cliente['bairro'] ?? '',
            'cidade' => $cliente['cidade'] ?? '',
            'estado' => $cliente['estado'] ?? '',
            'observacoes' => $cliente['observacoes'] ?? '',
        ];
    }

    private function dadosVazios(): array
    {
        return [
            'tipo_pessoa' => 'PF',
            'status' => 'Ativo',
            'nome' => '',
            'cpf' => '',
            'rg' => '',
            'data_nascimento' => '',
            'razao_social' => '',
            'nome_fantasia' => '',
            'cnpj' => '',
            'inscricao_estadual' => '',
            'contato' => '',
            'email' => '',
            'email_nf' => '',
            'telefone' => '',
            'whatsapp' => '',
            'cep' => '',
            'logradouro' => '',
            'numero' => '',
            'complemento' => '',
            'bairro' => '',
            'cidade' => '',
            'estado' => '',
            'observacoes' => '',
        ];
    }

    private function obterIdCliente(): ?int
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

    private function normalizarData(string $valor): ?string
    {
        if ($valor === '') {
            return null;
        }

        $data = DateTimeImmutable::createFromFormat('Y-m-d', $valor);

        return $data && $data->format('Y-m-d') === $valor ? $valor : null;
    }

    private function normalizarStatus(string $status): ?string
    {
        $statusNormalizado = strtolower(trim($status));
        $mapa = [
            'ativo' => 'Ativo',
            'em análise' => 'Em análise',
            'em analise' => 'Em análise',
            'pendente' => 'Pendente',
            'inativo' => 'Inativo',
        ];

        return $mapa[$statusNormalizado] ?? null;
    }

    private function limitar(string $valor, int $limite): string
    {
        return substr(trim($valor), 0, $limite);
    }

    private function somenteDigitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }

    public static function nomeExibicao(array $cliente): string
    {
        if (($cliente['tipo_pessoa'] ?? '') === 'PF') {
            return (string) ($cliente['nome'] ?? '');
        }

        return (string) (($cliente['razao_social'] ?? '') ?: ($cliente['nome_fantasia'] ?? ''));
    }

    public static function documentoExibicao(array $cliente): string
    {
        if (($cliente['tipo_pessoa'] ?? '') === 'PF') {
            return (string) ($cliente['cpf'] ?? '');
        }

        return (string) ($cliente['cnpj'] ?? '');
    }

    public static function contatoExibicao(array $cliente): string
    {
        return (string) (($cliente['contato'] ?? '')
            ?: ($cliente['email'] ?? '')
            ?: ($cliente['telefone'] ?? ''));
    }

    public static function badgeStatus(string $status): string
    {
        $statusNormalizado = strtolower($status);

        if (str_contains($statusNormalizado, 'inativo')) {
            return 'badge-neutral';
        }

        if (str_contains($statusNormalizado, 'analise') || str_contains($statusNormalizado, 'análise')) {
            return 'badge-info';
        }

        if (str_contains($statusNormalizado, 'pendente')) {
            return 'badge-warning';
        }

        if (str_contains($statusNormalizado, 'ativo')) {
            return 'badge-success';
        }

        return 'badge-info';
    }
}
