<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();

$pageTitle = 'Nova Venda | Ardetho ERP';
$bodyPage = 'sale-form';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = false;
$activeNav = 'sales';
$topbarTitle = 'Vendas';
$topbarSubtitle = 'Cadastro completo de pedido';
$scripts = [
    'assets/js/data.js',
    'assets/js/storage.js',
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/sale-form.js',
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
              <h1 class="page-title">Nova venda</h1>
              <p class="page-description">
                Registre pedidos vinculando cliente, item, valores e condições de pagamento.
              </p>
            </div>

            <div class="page-header-actions">
              <a href="vendas.php" class="btn-secondary">Voltar</a>
            </div>
          </div>

          <form id="sale-form-page" class="content-stack client-form-layout">
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
                  <input id="sale-code" class="input-default" type="text" placeholder="Ex.: PED-001" />
                </div>

                <div class="form-group">
                  <label for="sale-status">Status</label>
                  <select id="sale-status" class="select-default">
                    <option value="Em análise">Em análise</option>
                    <option value="Aprovado">Aprovado</option>
                    <option value="Faturado">Faturado</option>
                    <option value="Concluído">Concluído</option>
                    <option value="Cancelado">Cancelado</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="sale-date">Data da venda</label>
                  <input id="sale-date" class="input-default" type="date" />
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
                  <select id="sale-client-id" class="select-default">
                    <option value="">Selecione um cliente</option>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Item vendido</h2>
                  <p class="page-card-description">Selecione o produto ou serviço que compõe a venda.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group client-form-col-span-2">
                  <label for="sale-product-id">Produto / Serviço</label>
                  <select id="sale-product-id" class="select-default">
                    <option value="">Selecione um item</option>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Comercial</h2>
                  <p class="page-card-description">Quantidade, valor unitário e total calculado do pedido.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="sale-quantity">Quantidade</label>
                  <input id="sale-quantity" class="input-default" type="number" min="1" placeholder="1" />
                </div>

                <div class="form-group">
                  <label for="sale-unit-price">Valor unitário</label>
                  <input id="sale-unit-price" class="input-default" type="text" placeholder="0,00" />
                </div>

                <div class="form-group">
                  <label for="sale-total-value">Valor total</label>
                  <input id="sale-total-value" class="input-default" type="text" placeholder="0,00" readonly />
                </div>
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
                  <select id="sale-payment-method" class="select-default">
                    <option value="">Selecione</option>
                    <option value="Boleto">Boleto</option>
                    <option value="Pix">Pix</option>
                    <option value="Transferência">Transferência</option>
                    <option value="Cartão">Cartão</option>
                    <option value="Faturado">Faturado</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="sale-payment-terms">Condição de pagamento</label>
                  <input id="sale-payment-terms" class="input-default" type="text" placeholder="Ex.: 28 dias" />
                </div>

                <div class="form-group client-form-col-span-2">
                  <label for="sale-notes">Observações</label>
                  <textarea id="sale-notes" class="textarea-default"
                    placeholder="Observações adicionais do pedido"></textarea>
                </div>
              </div>
            </section>

            <p id="sale-form-error" class="error-message client-form-error hidden"></p>

            <div class="client-form-actions">
              <a href="vendas.php" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">Salvar venda</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
