<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/FinanceiroController.php';
require __DIR__ . '/includes/view.php';

$controller = new FinanceiroController();
$viewData = $controller->formulario();
$modoEdicao = $viewData['modoEdicao'];
$dados = $viewData['dados'];
$clientes = $viewData['clientes'];
$vendas = $viewData['vendas'];
$erros = $viewData['erros'];
$csrf = $viewData['csrf'];
$idFinanceiro = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formAction = $modoEdicao && $idFinanceiro ? 'financeiro-form.php?id=' . (int) $idFinanceiro : 'financeiro-form.php';
$tituloPagina = $modoEdicao ? 'Editar lançamento' : 'Novo lançamento';
$descricaoPagina = $modoEdicao
    ? 'Atualize os dados do lançamento financeiro selecionado.'
    : 'Registre receitas e despesas com dados financeiros, origem e forma de pagamento.';

$pageTitle = ($modoEdicao ? 'Editar Lançamento' : 'Novo Lançamento') . ' | Ardetho ERP';
$bodyPage = 'financial-form';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = false;
$activeNav = 'financial';
$topbarTitle = 'Financeiro';
$topbarSubtitle = 'Cadastro completo de lançamento';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/financial-form.js',
    'assets/js/pwa.js'
];
$isInternal = true;

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>
<section class="app-content">
        <div class="content-container">
          <div class="page-header">
            <div class="page-header-content">
              <h1 class="page-title"><?= e($tituloPagina) ?></h1>
              <p class="page-description">
                <?= e($descricaoPagina) ?>
              </p>
            </div>

            <div class="page-header-actions">
              <a href="financeiro.php" class="btn-secondary">Voltar</a>
            </div>
          </div>

          <form id="financial-form-page" class="content-stack client-form-layout" method="post" action="<?= e($formAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados do lançamento</h2>
                  <p class="page-card-description">Informações principais de identificação, tipo e situação.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="financial-code">Código</label>
                  <input id="financial-code" name="codigo" class="input-default" type="text" placeholder="Ex.: LAN-001" value="<?= e($dados['codigo'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="financial-entry-type">Tipo</label>
                  <select id="financial-entry-type" name="tipo" class="select-default">
<?php foreach (FinanceiroController::tipos() as $tipo): ?>
                    <option value="<?= e($tipo) ?>"<?= selectedIf($dados['tipo'] ?? '', $tipo) ?>><?= e($tipo) ?></option>
<?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="financial-status">Status</label>
                  <select id="financial-status" name="status" class="select-default">
<?php foreach (FinanceiroController::statusFormulario() as $status): ?>
                    <option value="<?= e($status) ?>"<?= selectedIf($dados['status'] ?? '', $status) ?>><?= e($status) ?></option>
<?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="financial-entry-date">Data do lançamento</label>
                  <input id="financial-entry-date" name="data_lancamento" class="input-default" type="date" value="<?= e($dados['data_lancamento'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="financial-due-date">Vencimento</label>
                  <input id="financial-due-date" name="data_vencimento" class="input-default" type="date" value="<?= e($dados['data_vencimento'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Origem</h2>
                  <p class="page-card-description">Vínculo opcional com cliente e pedido relacionado.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group" id="financial-client-group">
                  <label for="financial-client-id">Cliente</label>
                  <select id="financial-client-id" name="id_cliente" class="select-default">
                    <option value="">Selecione um cliente</option>
<?php foreach ($clientes as $cliente): ?>
                    <option value="<?= e($cliente['id_cliente']) ?>"<?= selectedIf($dados['id_cliente'] ?? '', $cliente['id_cliente']) ?>><?= e(FinanceiroController::clienteNomeExibicao($cliente)) ?><?= ($cliente['status'] ?? '') === 'Inativo' ? ' (Inativo)' : '' ?></option>
<?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group" id="financial-sale-group">
                  <label for="financial-sale-id">Pedido relacionado</label>
                  <select id="financial-sale-id" name="id_venda" class="select-default">
                    <option value="">Selecione um pedido</option>
<?php foreach ($vendas as $venda): ?>
                    <option
                      value="<?= e($venda['id_venda']) ?>"
                      data-client-id="<?= e($venda['id_cliente']) ?>"
                      data-category="Venda"
                      data-description="<?= e('Recebimento referente ao pedido ' . (($venda['codigo'] ?? '') ?: 'PED') . '.') ?>"
                      data-amount="<?= e($venda['valor_total']) ?>"
                      data-payment-method="<?= e($venda['forma_pagamento'] ?? '') ?>"
                      <?= selectedIf($dados['id_venda'] ?? '', $venda['id_venda']) ?>
                    >
                      <?= e(FinanceiroController::vendaNomeExibicao($venda)) ?><?= ($venda['status'] ?? '') === 'Cancelado' ? ' (Cancelado)' : '' ?>
                    </option>
<?php endforeach; ?>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Financeiro</h2>
                  <p class="page-card-description">Categoria, descrição, valor e forma de pagamento.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group" id="financial-category-group">
                  <label for="financial-category">Categoria</label>
                  <input id="financial-category" name="categoria" class="input-default" type="text" placeholder="Ex.: Venda, Compra, Serviço" value="<?= e($dados['categoria'] ?? '') ?>" />
                </div>

                <div class="form-group" id="financial-payment-method-group">
                  <label for="financial-payment-method">Forma de pagamento</label>
                  <select id="financial-payment-method" name="forma_pagamento" class="select-default">
                    <option value="">Selecione</option>
<?php foreach (FinanceiroController::formasPagamento() as $formaPagamento): ?>
                    <option value="<?= e($formaPagamento) ?>"<?= selectedIf($dados['forma_pagamento'] ?? '', $formaPagamento) ?>><?= e($formaPagamento) ?></option>
<?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group client-form-col-span-2" id="financial-description-group">
                  <label for="financial-description">Descrição</label>
                  <input id="financial-description" name="descricao" class="input-default" type="text" placeholder="Descrição do lançamento" value="<?= e($dados['descricao'] ?? '') ?>" />
                </div>

                <div class="form-group" id="financial-amount-group">
                  <label for="financial-amount">Valor</label>
                  <input id="financial-amount" name="valor" class="input-default" type="text" placeholder="0,00" value="<?= e($dados['valor'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Observações</h2>
                  <p class="page-card-description">Informações adicionais do lançamento.</p>
                </div>
              </div>

              <div class="form-group">
                <label for="financial-notes">Observações</label>
                <textarea id="financial-notes" name="observacoes" class="textarea-default" placeholder="Observações adicionais"><?= e($dados['observacoes'] ?? '') ?></textarea>
              </div>
            </section>

<?php if ($erros): ?>
            <div id="financial-form-error" class="error-message client-form-error" role="alert">
<?php foreach ($erros as $erro): ?>
              <p><?= e($erro) ?></p>
<?php endforeach; ?>
            </div>
<?php else: ?>
            <p id="financial-form-error" class="error-message client-form-error hidden"></p>
<?php endif; ?>

            <div class="client-form-actions">
              <a href="financeiro.php" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">Salvar lançamento</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
