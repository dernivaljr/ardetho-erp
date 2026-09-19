<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../models/Funcionario.php';

class FuncionarioController
{
    private const STATUS = ['Ativo', 'Férias', 'Afastado', 'Desligado'];

    private Funcionario $model;

    public function __construct()
    {
        $this->model = new Funcionario(obterConexaoBanco());
    }

    public function listar(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarAlteracaoStatus();
        }

        $filtros = [
            'busca' => $this->limitar($this->entradaGet('busca'), 120),
            'status' => $this->normalizarStatus($this->entradaGet('status')) ?? '',
            'departamento' => $this->limitar($this->entradaGet('departamento'), 100),
        ];

        return [
            'funcionarios' => $this->model->listar($filtros),
            'departamentos' => $this->model->listarDepartamentos(),
            'resumo' => $this->model->obterResumo(),
            'filtros' => $filtros,
            'flash' => obterFlash(),
            'csrf' => csrfToken(),
        ];
    }

    public function formulario(): array
    {
        $idFuncionario = $this->obterIdFuncionario();
        $modoEdicao = $idFuncionario !== null;
        $funcionario = $modoEdicao ? $this->model->buscarPorId($idFuncionario) : null;

        if ($modoEdicao && $funcionario === null) {
            definirFlash('danger', 'Colaborador nao encontrado.');
            header('Location: rh.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processarFormulario($idFuncionario, $funcionario);
        }

        return [
            'modoEdicao' => $modoEdicao,
            'dados' => $funcionario ? $this->funcionarioParaFormulario($funcionario) : $this->dadosVazios(),
            'erros' => [],
            'csrf' => csrfToken(),
        ];
    }

    private function processarAlteracaoStatus(): void
    {
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            definirFlash('danger', 'Acao recusada por token de seguranca invalido.');
            header('Location: rh.php');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id_funcionario', FILTER_VALIDATE_INT);
        $acao = $this->entradaPost('acao');

        if (!$id || $id < 1) {
            definirFlash('danger', 'Colaborador invalido.');
            header('Location: rh.php');
            exit;
        }

        if ($acao === 'desligar') {
            $this->model->alterarStatus((int) $id, 'Desligado');
            definirFlash('success', 'Colaborador desligado com sucesso.');
        } elseif ($acao === 'ativar') {
            $this->model->alterarStatus((int) $id, 'Ativo');
            definirFlash('success', 'Colaborador ativado com sucesso.');
        } else {
            definirFlash('danger', 'Acao invalida.');
        }

        $destino = 'rh.php';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        if ($query !== '') {
            $destino .= '?' . $query;
        }

        header('Location: ' . $destino);
        exit;
    }

    private function processarFormulario(?int $idFuncionario, ?array $funcionarioAtual): array
    {
        $dados = $this->obterDadosFormulario();
        $erros = [];
        $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);

        if (!validarCsrf(is_string($token) ? $token : null)) {
            $erros[] = 'Acao recusada por token de seguranca invalido.';
        }

        $erros = array_merge($erros, $this->validarDados($dados));

        if ($idFuncionario !== null && $funcionarioAtual === null) {
            $erros[] = 'Colaborador invalido para edicao.';
        }

        if ($erros) {
            return [
                'modoEdicao' => $idFuncionario !== null,
                'dados' => $dados,
                'erros' => array_values(array_unique($erros)),
                'csrf' => csrfToken(),
            ];
        }

        $dadosBanco = $this->dadosParaBanco($dados);

        if ($idFuncionario !== null) {
            $this->model->atualizar($idFuncionario, $dadosBanco);
            definirFlash('success', 'Colaborador atualizado com sucesso.');
        } else {
            $this->model->criar($dadosBanco);
            definirFlash('success', 'Colaborador cadastrado com sucesso.');
        }

        header('Location: rh.php');
        exit;
    }

    private function obterDadosFormulario(): array
    {
        return [
            'nome_completo' => $this->normalizarTexto('nome_completo', 160),
            'email' => $this->normalizarTexto('email', 190),
            'telefone' => $this->normalizarTexto('telefone', 30),
            'cargo' => $this->normalizarTexto('cargo', 120),
            'departamento' => $this->normalizarTexto('departamento', 100),
            'salario' => $this->limitar($this->entradaPost('salario'), 30),
            'data_admissao' => $this->limitar($this->entradaPost('data_admissao'), 10),
            'status' => $this->normalizarStatus($this->entradaPost('status')) ?? 'Ativo',
            'observacoes' => $this->normalizarTexto('observacoes', 5000),
        ];
    }

    private function validarDados(array $dados): array
    {
        $erros = [];

        foreach (['nome_completo', 'email', 'cargo', 'departamento', 'data_admissao'] as $campo) {
            if (($dados[$campo] ?? '') === '') {
                $erros[] = 'Preencha todos os campos obrigatorios do colaborador.';
                break;
            }
        }

        if (($dados['email'] ?? '') !== '' && filter_var($dados['email'], FILTER_VALIDATE_EMAIL) === false) {
            $erros[] = 'Informe um e-mail valido.';
        }

        if (!in_array($dados['status'], self::STATUS, true)) {
            $erros[] = 'Status invalido.';
        }

        $salario = $this->normalizarDecimal($dados['salario'], 2, 10);

        if ($salario === null || (float) $salario < 0) {
            $erros[] = 'Informe um salario valido.';
        }

        if (!$this->dataValida($dados['data_admissao'] ?? '')) {
            $erros[] = 'Informe uma data de admissao valida.';
        }

        return array_values(array_unique($erros));
    }

    private function dadosParaBanco(array $dados): array
    {
        return [
            'nome_completo' => $dados['nome_completo'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'],
            'cargo' => $dados['cargo'],
            'departamento' => $dados['departamento'],
            'salario' => $this->normalizarDecimal($dados['salario'], 2, 10) ?? '0.00',
            'data_admissao' => $dados['data_admissao'],
            'status' => $dados['status'],
            'observacoes' => $dados['observacoes'],
        ];
    }

    private function funcionarioParaFormulario(array $funcionario): array
    {
        return [
            'nome_completo' => $funcionario['nome_completo'] ?? '',
            'email' => $funcionario['email'] ?? '',
            'telefone' => $funcionario['telefone'] ?? '',
            'cargo' => $funcionario['cargo'] ?? '',
            'departamento' => $funcionario['departamento'] ?? '',
            'salario' => self::formatarSalarioFormulario($funcionario['salario'] ?? ''),
            'data_admissao' => $funcionario['data_admissao'] ?? '',
            'status' => $funcionario['status'] ?? 'Ativo',
            'observacoes' => $funcionario['observacoes'] ?? '',
        ];
    }

    private function dadosVazios(): array
    {
        return [
            'nome_completo' => '',
            'email' => '',
            'telefone' => '',
            'cargo' => '',
            'departamento' => '',
            'salario' => '',
            'data_admissao' => '',
            'status' => 'Ativo',
            'observacoes' => '',
        ];
    }

    private function obterIdFuncionario(): ?int
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
            'ativo' => 'Ativo',
            'ferias' => 'Férias',
            'férias' => 'Férias',
            'afastado' => 'Afastado',
            'desligado' => 'Desligado',
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

    private function dataValida(string $valor): bool
    {
        $data = DateTimeImmutable::createFromFormat('Y-m-d', $valor);

        return $data !== false && $data->format('Y-m-d') === $valor;
    }

    private function limitar(string $valor, int $limite): string
    {
        return substr(trim($valor), 0, $limite);
    }

    public static function status(): array
    {
        return self::STATUS;
    }

    public static function badgeStatus(string $status): string
    {
        $statusNormalizado = strtolower($status);

        if (str_contains($statusNormalizado, 'ativo')) {
            return 'badge-success';
        }

        if (str_contains($statusNormalizado, 'ferias') || str_contains($statusNormalizado, 'férias')) {
            return 'badge-info';
        }

        if (str_contains($statusNormalizado, 'afastado')) {
            return 'badge-warning';
        }

        if (str_contains($statusNormalizado, 'desligado')) {
            return 'badge-danger';
        }

        return 'badge-neutral';
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

    public static function formatarSalarioFormulario(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        return number_format((float) $valor, 2, ',', '.');
    }
}
