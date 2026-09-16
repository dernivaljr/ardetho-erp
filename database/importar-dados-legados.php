<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Este utilitario deve ser executado somente via CLI.');
}

require __DIR__ . '/../config/database.php';

function extrairArrayLegado(string $fonte, string $secao): array
{
    if (!preg_match('/\b' . preg_quote($secao, '/') . '\s*:\s*\[/', $fonte, $match, PREG_OFFSET_CAPTURE)) {
        throw new RuntimeException("Secao {$secao} nao encontrada em assets/js/data.js.");
    }

    $inicioArray = strpos($fonte, '[', $match[0][1]);

    if ($inicioArray === false) {
        throw new RuntimeException("Inicio da secao {$secao} nao encontrado.");
    }

    $profundidade = 0;
    $emString = false;
    $escape = false;
    $tamanho = strlen($fonte);

    for ($indice = $inicioArray; $indice < $tamanho; $indice++) {
        $caractere = $fonte[$indice];

        if ($emString) {
            if ($escape) {
                $escape = false;
                continue;
            }

            if ($caractere === '\\') {
                $escape = true;
                continue;
            }

            if ($caractere === '"') {
                $emString = false;
            }

            continue;
        }

        if ($caractere === '"') {
            $emString = true;
            continue;
        }

        if ($caractere === '[') {
            $profundidade++;
            continue;
        }

        if ($caractere === ']') {
            $profundidade--;

            if ($profundidade === 0) {
                $literal = substr($fonte, $inicioArray, $indice - $inicioArray + 1);
                return decodificarLiteralJs($literal, $secao);
            }
        }
    }

    throw new RuntimeException("Fim da secao {$secao} nao encontrado.");
}

function decodificarLiteralJs(string $literal, string $secao): array
{
    $json = preg_replace('/([{,]\s*)([A-Za-z_][A-Za-z0-9_]*)(\s*:)/', '$1"$2"$3', $literal);
    $json = preg_replace('/,\s*([}\]])/', '$1', (string) $json);
    $dados = json_decode($json, true);

    if (!is_array($dados)) {
        throw new RuntimeException("Nao foi possivel decodificar a secao {$secao}: " . json_last_error_msg());
    }

    return $dados;
}

function textoOuNull(mixed $valor, int $limite): ?string
{
    $texto = trim((string) ($valor ?? ''));

    if ($texto === '') {
        return null;
    }

    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, $limite, 'UTF-8');
    }

    return substr($texto, 0, $limite);
}

function decimalBanco(mixed $valor, int $casas): ?string
{
    if ($valor === null || $valor === '') {
        return null;
    }

    return number_format((float) $valor, $casas, '.', '');
}

function dataCadastroLegada(mixed $valor): string
{
    $texto = trim((string) ($valor ?? ''));

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $texto)) {
        return $texto . ' 00:00:00';
    }

    return date('Y-m-d H:i:s');
}

function statusProdutoBanco(array $produto): string
{
    $tipoItem = (string) ($produto['itemType'] ?? 'Produto');
    $status = (string) ($produto['status'] ?? 'Ativo');

    if ($tipoItem === 'Produto') {
        return $status === 'Inativo' ? 'Inativo' : 'Ativo';
    }

    if (in_array($status, ['Ativo', 'Em análise', 'Inativo'], true)) {
        return $status;
    }

    return $status === 'Inativo' ? 'Inativo' : 'Ativo';
}

function clienteNomeLegado(array $cliente): string
{
    if (($cliente['personType'] ?? '') === 'PF') {
        return (string) (($cliente['fullName'] ?? '') ?: 'Cliente');
    }

    return (string) (($cliente['tradeName'] ?? '') ?: ($cliente['companyName'] ?? '') ?: 'Cliente');
}

function buscarClienteExistente(PDO $pdo, array $cliente): ?int
{
    $cpf = textoOuNull($cliente['cpf'] ?? null, 20);
    $cnpj = textoOuNull($cliente['cnpj'] ?? null, 24);

    if ($cpf !== null || $cnpj !== null) {
        $consulta = $pdo->prepare(
            'SELECT id_cliente
               FROM clientes
              WHERE (:cpf_filtro IS NOT NULL AND cpf = :cpf_valor)
                 OR (:cnpj_filtro IS NOT NULL AND cnpj = :cnpj_valor)
              LIMIT 1'
        );
        $consulta->execute([
            'cpf_filtro' => $cpf,
            'cpf_valor' => $cpf,
            'cnpj_filtro' => $cnpj,
            'cnpj_valor' => $cnpj,
        ]);
        $id = $consulta->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }
    }

    $email = textoOuNull($cliente['mainEmail'] ?? null, 190);
    $nome = textoOuNull(($cliente['personType'] ?? '') === 'PF' ? ($cliente['fullName'] ?? '') : ($cliente['companyName'] ?? ''), 180);

    if ($email === null || $nome === null) {
        return null;
    }

    $consulta = $pdo->prepare(
        'SELECT id_cliente
           FROM clientes
          WHERE email = :email
            AND (nome = :nome_pf OR razao_social = :nome_razao OR nome_fantasia = :nome_fantasia)
          LIMIT 1'
    );
    $consulta->execute([
        'email' => $email,
        'nome_pf' => $nome,
        'nome_razao' => $nome,
        'nome_fantasia' => $nome,
    ]);
    $id = $consulta->fetchColumn();

    return $id === false ? null : (int) $id;
}

function buscarProdutoExistente(PDO $pdo, string $codigo): ?int
{
    $consulta = $pdo->prepare(
        'SELECT id_produto
           FROM produtos
          WHERE codigo = :codigo
          LIMIT 1'
    );
    $consulta->execute(['codigo' => $codigo]);
    $id = $consulta->fetchColumn();

    return $id === false ? null : (int) $id;
}

function buscarVendaExistente(PDO $pdo, string $codigo): ?int
{
    $consulta = $pdo->prepare(
        'SELECT id_venda
           FROM vendas
          WHERE codigo = :codigo
          LIMIT 1'
    );
    $consulta->execute(['codigo' => $codigo]);
    $id = $consulta->fetchColumn();

    return $id === false ? null : (int) $id;
}

function buscarFinanceiroExistente(PDO $pdo, string $codigo): ?int
{
    $consulta = $pdo->prepare(
        'SELECT id_financeiro
           FROM financeiro
          WHERE codigo = :codigo
          LIMIT 1'
    );
    $consulta->execute(['codigo' => $codigo]);
    $id = $consulta->fetchColumn();

    return $id === false ? null : (int) $id;
}

function buscarFuncionarioExistente(PDO $pdo, string $email): ?int
{
    $consulta = $pdo->prepare(
        'SELECT id_funcionario
           FROM funcionarios
          WHERE email = :email
          LIMIT 1'
    );
    $consulta->execute(['email' => $email]);
    $id = $consulta->fetchColumn();

    return $id === false ? null : (int) $id;
}

$caminhoDataJs = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'data.js';
$fonte = file_get_contents($caminhoDataJs);

if ($fonte === false) {
    fwrite(STDERR, "Nao foi possivel ler assets/js/data.js.\n");
    exit(1);
}

try {
    $clientes = extrairArrayLegado($fonte, 'clients');
    $produtos = extrairArrayLegado($fonte, 'products');
    $vendas = extrairArrayLegado($fonte, 'sales');
    $financeiro = extrairArrayLegado($fonte, 'financial');
    $funcionarios = extrairArrayLegado($fonte, 'hr');

    $pdo = obterConexaoBanco();
    $pdo->beginTransaction();

    $resultado = [
        'clientes_encontrados' => count($clientes),
        'produtos_encontrados' => count(array_filter($produtos, static fn ($produto) => ($produto['itemType'] ?? '') === 'Produto')),
        'servicos_encontrados' => count(array_filter($produtos, static fn ($produto) => ($produto['itemType'] ?? '') === 'Serviço')),
        'vendas_encontradas' => count($vendas),
        'financeiro_encontrado' => count($financeiro),
        'funcionarios_encontrados' => count($funcionarios),
        'clientes_inseridos' => 0,
        'clientes_ignorados' => 0,
        'produtos_inseridos' => 0,
        'produtos_ignorados' => 0,
        'vendas_inseridas' => 0,
        'vendas_ignoradas' => 0,
        'venda_itens_inseridos' => 0,
        'financeiro_inserido' => 0,
        'financeiro_ignorado' => 0,
        'funcionarios_inseridos' => 0,
        'funcionarios_ignorados' => 0,
    ];

    $mapaClientes = [];
    $inserirCliente = $pdo->prepare(
        'INSERT INTO clientes (
            tipo_pessoa, status, nome, cpf, rg, data_nascimento,
            razao_social, nome_fantasia, cnpj, inscricao_estadual,
            contato, email, email_nf, telefone, whatsapp, cep,
            logradouro, numero, complemento, bairro, cidade, estado,
            observacoes, data_cadastro
        ) VALUES (
            :tipo_pessoa, :status, :nome, :cpf, :rg, :data_nascimento,
            :razao_social, :nome_fantasia, :cnpj, :inscricao_estadual,
            :contato, :email, :email_nf, :telefone, :whatsapp, :cep,
            :logradouro, :numero, :complemento, :bairro, :cidade, :estado,
            :observacoes, :data_cadastro
        )'
    );

    foreach ($clientes as $cliente) {
        $idLegado = (string) ($cliente['id'] ?? '');
        $idExistente = buscarClienteExistente($pdo, $cliente);

        if ($idExistente !== null) {
            $mapaClientes[$idLegado] = $idExistente;
            $resultado['clientes_ignorados']++;
            continue;
        }

        $inserirCliente->execute([
            'tipo_pessoa' => textoOuNull($cliente['personType'] ?? 'PF', 2) ?? 'PF',
            'status' => textoOuNull($cliente['status'] ?? 'Ativo', 30) ?? 'Ativo',
            'nome' => textoOuNull($cliente['fullName'] ?? null, 160),
            'cpf' => textoOuNull($cliente['cpf'] ?? null, 20),
            'rg' => textoOuNull($cliente['rg'] ?? null, 30),
            'data_nascimento' => textoOuNull($cliente['birthDate'] ?? null, 10),
            'razao_social' => textoOuNull($cliente['companyName'] ?? null, 180),
            'nome_fantasia' => textoOuNull($cliente['tradeName'] ?? null, 160),
            'cnpj' => textoOuNull($cliente['cnpj'] ?? null, 24),
            'inscricao_estadual' => textoOuNull($cliente['stateRegistration'] ?? null, 40),
            'contato' => textoOuNull($cliente['contactName'] ?? null, 120),
            'email' => textoOuNull($cliente['mainEmail'] ?? null, 190),
            'email_nf' => textoOuNull($cliente['invoiceEmail'] ?? null, 190),
            'telefone' => textoOuNull($cliente['phone'] ?? null, 30),
            'whatsapp' => textoOuNull($cliente['whatsapp'] ?? null, 30),
            'cep' => textoOuNull($cliente['zipCode'] ?? null, 12),
            'logradouro' => textoOuNull($cliente['street'] ?? null, 180),
            'numero' => textoOuNull($cliente['number'] ?? null, 30),
            'complemento' => textoOuNull($cliente['complement'] ?? null, 120),
            'bairro' => textoOuNull($cliente['district'] ?? null, 100),
            'cidade' => textoOuNull($cliente['city'] ?? null, 100),
            'estado' => textoOuNull($cliente['state'] ?? null, 2),
            'observacoes' => textoOuNull($cliente['notes'] ?? null, 5000),
            'data_cadastro' => dataCadastroLegada($cliente['createdAt'] ?? null),
        ]);

        $mapaClientes[$idLegado] = (int) $pdo->lastInsertId();
        $resultado['clientes_inseridos']++;
    }

    $mapaProdutos = [];
    $inserirProduto = $pdo->prepare(
        'INSERT INTO produtos (
            tipo_item, codigo, nome, categoria, descricao, preco, unidade,
            status, estoque, estoque_minimo, marca, fornecedor, ncm,
            prazo_estimado, departamento, data_cadastro
        ) VALUES (
            :tipo_item, :codigo, :nome, :categoria, :descricao, :preco, :unidade,
            :status, :estoque, :estoque_minimo, :marca, :fornecedor, :ncm,
            :prazo_estimado, :departamento, :data_cadastro
        )'
    );

    foreach ($produtos as $produto) {
        $idLegado = (string) ($produto['id'] ?? '');
        $codigo = textoOuNull($produto['code'] ?? null, 40);

        if ($codigo === null) {
            throw new RuntimeException("Produto legado {$idLegado} nao possui codigo.");
        }

        $idExistente = buscarProdutoExistente($pdo, $codigo);

        if ($idExistente !== null) {
            $mapaProdutos[$idLegado] = $idExistente;
            $resultado['produtos_ignorados']++;
            continue;
        }

        $tipoItem = textoOuNull($produto['itemType'] ?? 'Produto', 20) ?? 'Produto';

        $inserirProduto->execute([
            'tipo_item' => $tipoItem,
            'codigo' => $codigo,
            'nome' => textoOuNull($produto['name'] ?? null, 160) ?? $codigo,
            'categoria' => textoOuNull($produto['category'] ?? null, 100),
            'descricao' => textoOuNull($produto['description'] ?? null, 5000),
            'preco' => decimalBanco($produto['price'] ?? 0, 2) ?? '0.00',
            'unidade' => textoOuNull($produto['unit'] ?? null, 30),
            'status' => statusProdutoBanco($produto),
            'estoque' => $tipoItem === 'Produto' ? decimalBanco($produto['stock'] ?? 0, 3) : null,
            'estoque_minimo' => $tipoItem === 'Produto' ? decimalBanco($produto['minimumStock'] ?? 0, 3) : null,
            'marca' => $tipoItem === 'Produto' ? textoOuNull($produto['brand'] ?? null, 100) : null,
            'fornecedor' => $tipoItem === 'Produto' ? textoOuNull($produto['supplier'] ?? null, 120) : null,
            'ncm' => $tipoItem === 'Produto' ? textoOuNull($produto['ncm'] ?? null, 20) : null,
            'prazo_estimado' => $tipoItem === 'Serviço' ? textoOuNull($produto['estimatedDeadline'] ?? null, 60) : null,
            'departamento' => $tipoItem === 'Serviço' ? textoOuNull($produto['department'] ?? null, 100) : null,
            'data_cadastro' => dataCadastroLegada($produto['createdAt'] ?? null),
        ]);

        $mapaProdutos[$idLegado] = (int) $pdo->lastInsertId();
        $resultado['produtos_inseridos']++;
    }

    $inserirVenda = $pdo->prepare(
        'INSERT INTO vendas (
            codigo, id_cliente, data_venda, status, forma_pagamento,
            condicao_pagamento, valor_total, observacoes, data_cadastro
        ) VALUES (
            :codigo, :id_cliente, :data_venda, :status, :forma_pagamento,
            :condicao_pagamento, :valor_total, :observacoes, :data_cadastro
        )'
    );
    $inserirItem = $pdo->prepare(
        'INSERT INTO venda_itens (
            id_venda, id_produto, quantidade, valor_unitario, subtotal
        ) VALUES (
            :id_venda, :id_produto, :quantidade, :valor_unitario, :subtotal
        )'
    );

    $mapaVendas = [];

    foreach ($vendas as $venda) {
        $idLegado = (string) ($venda['id'] ?? '');
        $codigo = textoOuNull($venda['code'] ?? null, 40);
        $idClienteLegado = (string) ($venda['clientId'] ?? '');
        $idProdutoLegado = (string) ($venda['productId'] ?? '');

        if ($codigo === null) {
            throw new RuntimeException('Venda legada sem codigo encontrada.');
        }

        $idVendaExistente = buscarVendaExistente($pdo, $codigo);

        if ($idVendaExistente !== null) {
            $mapaVendas[$idLegado] = $idVendaExistente;
            $resultado['vendas_ignoradas']++;
            continue;
        }

        if (!isset($mapaClientes[$idClienteLegado])) {
            throw new RuntimeException("Cliente legado {$idClienteLegado} nao encontrado para a venda {$codigo}.");
        }

        if (!isset($mapaProdutos[$idProdutoLegado])) {
            throw new RuntimeException("Produto legado {$idProdutoLegado} nao encontrado para a venda {$codigo}.");
        }

        $quantidade = (float) ($venda['quantity'] ?? 0);
        $valorUnitario = (float) ($venda['unitPrice'] ?? 0);
        $subtotalCalculado = round($quantidade * $valorUnitario, 2);
        $totalLegado = (float) ($venda['totalValue'] ?? $subtotalCalculado);
        $valorTotal = abs($totalLegado - $subtotalCalculado) < 0.01 ? $totalLegado : $subtotalCalculado;

        $inserirVenda->execute([
            'codigo' => $codigo,
            'id_cliente' => $mapaClientes[$idClienteLegado],
            'data_venda' => textoOuNull($venda['saleDate'] ?? null, 10) ?? date('Y-m-d'),
            'status' => textoOuNull($venda['status'] ?? 'Em análise', 30) ?? 'Em análise',
            'forma_pagamento' => textoOuNull($venda['paymentMethod'] ?? null, 40),
            'condicao_pagamento' => textoOuNull($venda['paymentTerms'] ?? null, 80),
            'valor_total' => decimalBanco($valorTotal, 2) ?? '0.00',
            'observacoes' => textoOuNull($venda['notes'] ?? null, 5000),
            'data_cadastro' => dataCadastroLegada($venda['createdAt'] ?? null),
        ]);
        $idVenda = (int) $pdo->lastInsertId();
        $mapaVendas[$idLegado] = $idVenda;

        $inserirItem->execute([
            'id_venda' => $idVenda,
            'id_produto' => $mapaProdutos[$idProdutoLegado],
            'quantidade' => decimalBanco($quantidade, 3) ?? '0.000',
            'valor_unitario' => decimalBanco($valorUnitario, 2) ?? '0.00',
            'subtotal' => decimalBanco($subtotalCalculado, 2) ?? '0.00',
        ]);

        $resultado['vendas_inseridas']++;
        $resultado['venda_itens_inseridos']++;
    }

    $inserirFinanceiro = $pdo->prepare(
        'INSERT INTO financeiro (
            codigo, tipo, descricao, categoria, valor, data_lancamento,
            data_vencimento, status, forma_pagamento, id_cliente,
            id_venda, observacoes, data_cadastro
        ) VALUES (
            :codigo, :tipo, :descricao, :categoria, :valor, :data_lancamento,
            :data_vencimento, :status, :forma_pagamento, :id_cliente,
            :id_venda, :observacoes, :data_cadastro
        )'
    );

    foreach ($financeiro as $lancamento) {
        $codigo = textoOuNull($lancamento['code'] ?? null, 40);
        $idClienteLegado = (string) ($lancamento['clientId'] ?? '');
        $idVendaLegada = (string) ($lancamento['saleId'] ?? '');

        if ($codigo === null) {
            throw new RuntimeException('Lancamento financeiro legado sem codigo encontrado.');
        }

        if (buscarFinanceiroExistente($pdo, $codigo) !== null) {
            $resultado['financeiro_ignorado']++;
            continue;
        }

        $idCliente = $idClienteLegado !== '' && isset($mapaClientes[$idClienteLegado])
            ? $mapaClientes[$idClienteLegado]
            : null;
        $idVenda = $idVendaLegada !== '' && isset($mapaVendas[$idVendaLegada])
            ? $mapaVendas[$idVendaLegada]
            : null;

        if ($idVenda !== null) {
            $consultaVenda = $pdo->prepare('SELECT id_cliente FROM vendas WHERE id_venda = :id_venda LIMIT 1');
            $consultaVenda->execute(['id_venda' => $idVenda]);
            $idClienteVenda = $consultaVenda->fetchColumn();
            $idCliente = $idClienteVenda === false ? $idCliente : (int) $idClienteVenda;
        }

        $inserirFinanceiro->execute([
            'codigo' => $codigo,
            'tipo' => textoOuNull($lancamento['entryType'] ?? 'Receita', 20) ?? 'Receita',
            'descricao' => textoOuNull($lancamento['description'] ?? null, 180) ?? $codigo,
            'categoria' => textoOuNull($lancamento['category'] ?? null, 100),
            'valor' => decimalBanco($lancamento['amount'] ?? 0, 2) ?? '0.00',
            'data_lancamento' => textoOuNull($lancamento['entryDate'] ?? null, 10) ?? date('Y-m-d'),
            'data_vencimento' => textoOuNull($lancamento['dueDate'] ?? null, 10) ?? date('Y-m-d'),
            'status' => textoOuNull($lancamento['status'] ?? 'Pendente', 30) ?? 'Pendente',
            'forma_pagamento' => textoOuNull($lancamento['paymentMethod'] ?? null, 40) ?? 'Faturado',
            'id_cliente' => $idCliente,
            'id_venda' => $idVenda,
            'observacoes' => textoOuNull($lancamento['notes'] ?? null, 5000),
            'data_cadastro' => dataCadastroLegada($lancamento['createdAt'] ?? null),
        ]);

        $resultado['financeiro_inserido']++;
    }

    $inserirFuncionario = $pdo->prepare(
        'INSERT INTO funcionarios (
            nome_completo, email, telefone, cargo, departamento,
            salario, data_admissao, status, observacoes
        ) VALUES (
            :nome_completo, :email, :telefone, :cargo, :departamento,
            :salario, :data_admissao, :status, :observacoes
        )'
    );

    foreach ($funcionarios as $funcionario) {
        $email = textoOuNull($funcionario['email'] ?? null, 190);

        if ($email === null) {
            throw new RuntimeException('Colaborador legado sem e-mail encontrado.');
        }

        if (buscarFuncionarioExistente($pdo, $email) !== null) {
            $resultado['funcionarios_ignorados']++;
            continue;
        }

        $statusFuncionario = textoOuNull($funcionario['status'] ?? 'Ativo', 30) ?? 'Ativo';

        if (!in_array($statusFuncionario, ['Ativo', 'Férias', 'Afastado', 'Desligado'], true)) {
            $statusFuncionario = 'Ativo';
        }

        $inserirFuncionario->execute([
            'nome_completo' => textoOuNull($funcionario['fullName'] ?? null, 160) ?? $email,
            'email' => $email,
            'telefone' => textoOuNull($funcionario['phone'] ?? null, 30),
            'cargo' => textoOuNull($funcionario['role'] ?? null, 120) ?? 'Colaborador',
            'departamento' => textoOuNull($funcionario['department'] ?? null, 100) ?? 'Geral',
            'salario' => decimalBanco($funcionario['salary'] ?? 0, 2) ?? '0.00',
            'data_admissao' => textoOuNull($funcionario['admissionDate'] ?? null, 10) ?? date('Y-m-d'),
            'status' => $statusFuncionario,
            'observacoes' => textoOuNull($funcionario['notes'] ?? null, 5000),
        ]);

        $resultado['funcionarios_inseridos']++;
    }

    $pdo->commit();

    echo "Fonte: assets/js/data.js\n";
    echo "Clientes encontrados: {$resultado['clientes_encontrados']}\n";
    echo "Produtos encontrados: {$resultado['produtos_encontrados']}\n";
    echo "Servicos encontrados: {$resultado['servicos_encontrados']}\n";
    echo "Vendas encontradas: {$resultado['vendas_encontradas']}\n";
    echo "Lancamentos financeiros encontrados: {$resultado['financeiro_encontrado']}\n";
    echo "Funcionarios encontrados: {$resultado['funcionarios_encontrados']}\n";
    echo "Clientes inseridos: {$resultado['clientes_inseridos']}\n";
    echo "Clientes ignorados: {$resultado['clientes_ignorados']}\n";
    echo "Produtos/servicos inseridos: {$resultado['produtos_inseridos']}\n";
    echo "Produtos/servicos ignorados: {$resultado['produtos_ignorados']}\n";
    echo "Vendas inseridas: {$resultado['vendas_inseridas']}\n";
    echo "Vendas ignoradas: {$resultado['vendas_ignoradas']}\n";
    echo "Itens de venda inseridos: {$resultado['venda_itens_inseridos']}\n";
    echo "Lancamentos financeiros inseridos: {$resultado['financeiro_inserido']}\n";
    echo "Lancamentos financeiros ignorados: {$resultado['financeiro_ignorado']}\n";
    echo "Funcionarios inseridos: {$resultado['funcionarios_inseridos']}\n";
    echo "Funcionarios ignorados: {$resultado['funcionarios_ignorados']}\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Importacao cancelada: {$exception->getMessage()}\n");
    exit(1);
}
