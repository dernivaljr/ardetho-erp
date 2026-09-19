<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/VendaController.php';
require __DIR__ . '/includes/view.php';

$controller = new VendaController();
$viewData = $controller->formulario();
$modoEdicao = $viewData['modoEdicao'];
$dados = $viewData['dados'];
$itens = $viewData['itens'];
$clientes = $viewData['clientes'];
$produtos = $viewData['produtos'];
$erros = $viewData['erros'];
$csrf = $viewData['csrf'];
$idVenda = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formAction = $modoEdicao && $idVenda ? 'venda-form.php?id=' . (int) $idVenda : 'venda-form.php';
$tituloPagina = $modoEdicao ? 'Editar venda' : 'Nova venda';
$descricaoPagina = $modoEdicao
    ? 'Atualize os dados do pedido selecionado.'
    : 'Registre pedidos vinculando cliente, item, valores e condições de pagamento.';

$pageTitle = ($modoEdicao ? 'Editar Venda' : 'Nova Venda') . ' | Ardetho ERP';
$bodyPage = 'sale-form';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = false;
$activeNav = 'sales';
$topbarTitle = 'Vendas';
$topbarSubtitle = 'Cadastro completo de pedido';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/sale-form.js',
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
              <a href="vendas.php" class="btn-secondary">Voltar</a>
            </div>
          </div>

          <form id="sale-form-page" class="content-stack client-form-layout" method="post" action="<?= e($formAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados do pedido</h2>
                  <p class="page-card-description">Informações principais de identificação e status da venda.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="sale-code">Código do pedido</label>
                  <input id="sale-code" name="codigo" class="input-default" type="text" placeholder="Ex.: PED-001" value="<?= e($dados['codigo'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="sale-status">Status</label>
                  <select id="sale-status" name="status" class="select-default">
<?php foreach (VendaController::statusFormulario() as $status): ?>
                    <option value="<?= e($status) ?>"<?= selectedIf($dados['status'] ?? '', $status) ?>><?= e($status) ?></option>
<?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="sale-date">Data da venda</label>
                  <input id="sale-date" name="data_venda" class="input-default" type="date" value="<?= e($dados['data_venda'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Cliente</h2>
                  <p class="page-card-description">Selecione o cliente vinculado a este pedido.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group client-form-col-span-2">
                  <label for="sale-client-id">Cliente</label>
                  <select id="sale-client-id" name="id_cliente" class="select-default">
                    <option value="">Selecione um cliente</option>
<?php foreach ($clientes as $cliente): ?>
                    <option value="<?= e($cliente['id_cliente']) ?>"<?= selectedIf($dados['id_cliente'] ?? '', $cliente['id_cliente']) ?>><?= e(VendaController::clienteNomeExibicao($cliente)) ?><?= ($cliente['status'] ?? '') === 'Inativo' ? ' (Inativo)' : '' ?></option>
<?php endforeach; ?>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Itens da venda</h2>
                  <p class="page-card-description">Adicione produtos ou serviços, quantidades e valores negociados.</p>
                </div>
                <button type="button" class="btn-secondary" id="sale-add-item">Adicionar item</button>
              </div>

              <div class="sale-items-list" id="sale-items-list">
<?php foreach ($itens as $indice => $item): ?>
                <div class="sale-item-row" data-sale-item-row>
                  <div class="form-group sale-item-product">
                    <label>Produto / Serviço</label>
                    <select name="id_produto[]" class="select-default" data-sale-product>
                      <option value="">Selecione um item</option>
<?php foreach ($produtos as $produto): ?>
                      <option
                        value="<?= e($produto['id_produto']) ?>"
                        data-price="<?= e($produto['preco']) ?>"
                        data-type="<?= e($produto['tipo_item']) ?>"
                        <?= selectedIf($item['id_produto'] ?? '', $produto['id_produto']) ?>
                      >
                        <?= e(VendaController::itemNomeExibicao($produto)) ?><?= ($produto['status'] ?? '') === 'Inativo' ? ' (Inativo)' : '' ?>
                      </option>
<?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Quantidade</label>
                    <input name="quantidade[]" class="input-default" type="number" min="0.001" step="0.001" placeholder="1" value="<?= e($item['quantidade'] ?? '') ?>" data-sale-quantity />
                  </div>

                  <div class="form-group">
                    <label>Valor unitário</label>
                    <input name="valor_unitario[]" class="input-default" type="text" placeholder="0,00" value="<?= e($item['valor_unitario'] ?? '') ?>" data-sale-unit-price />
                  </div>

                  <div class="form-group">
                    <label>Subtotal</label>
                    <input name="subtotal[]" class="input-default" type="text" placeholder="0,00" value="<?= e($item['subtotal'] ?? '') ?>" readonly data-sale-subtotal />
                  </div>

                  <button type="button" class="btn-danger sale-item-remove" data-sale-remove-item>Remover</button>
                </div>
<?php endforeach; ?>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Pagamento</h2>
                  <p class="page-card-description">Informações de pagamento e observações do pedido.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="sale-payment-method">Forma de pagamento</label>
                  <select id="sale-payment-method" name="forma_pagamento" class="select-default">
                    <option value="">Selecione</option>
<?php foreach (VendaController::formasPagamento() as $formaPagamento): ?>
                    <option value="<?= e($formaPagamento) ?>"<?= selectedIf($dados['forma_pagamento'] ?? '', $formaPagamento) ?>><?= e($formaPagamento) ?></option>
<?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="sale-payment-terms">Condição de pagamento</label>
                  <input id="sale-payment-terms" name="condicao_pagamento" class="input-default" type="text" placeholder="Ex.: 28 dias" value="<?= e($dados['condicao_pagamento'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="sale-total-value">Valor total</label>
                  <input id="sale-total-value" name="valor_total" class="input-default" type="text" placeholder="0,00" readonly data-sale-total value="<?= e($dados['valor_total'] ?? '') ?>" />
                </div>

                <div class="form-group client-form-col-span-2">
                  <label for="sale-notes">Observações</label>
                  <textarea id="sale-notes" name="observacoes" class="textarea-default" placeholder="Observações adicionais do pedido"><?= e($dados['observacoes'] ?? '') ?></textarea>
                </div>
              </div>
            </section>

<?php if ($erros): ?>
            <div id="sale-form-error" class="error-message client-form-error" role="alert">
<?php foreach ($erros as $erro): ?>
              <p><?= e($erro) ?></p>
<?php endforeach; ?>
            </div>
<?php else: ?>
            <p id="sale-form-error" class="error-message client-form-error hidden"></p>
<?php endif; ?>

            <div class="client-form-actions">
              <a href="vendas.php" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">Salvar venda</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
