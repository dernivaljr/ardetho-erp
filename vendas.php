<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();

$pageTitle = 'Vendas | Ardetho ERP';
$bodyPage = 'sales';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'sales';
$topbarTitle = 'Vendas';
$topbarSubtitle = 'Gestão de pedidos e acompanhamento comercial';
$scripts = [
    'assets/js/data.js',
    'assets/js/storage.js',
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/sales.js',
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
              <h1 class="page-title">Pedidos e vendas</h1>
              <p class="page-description">
                Acompanhe pedidos, valores, status de aprovação e andamento comercial da operação.
              </p>
            </div>

            <div class="page-header-actions">
              <a href="venda-form.php" class="btn-primary">Novo pedido</a>
            </div>
          </div>

          <div class="content-stack">
            <section class="status-grid">
              <article class="status-card">
                <span class="status-card-label">Total de pedidos</span>
                <strong class="status-card-value" id="sales-total-orders">0</strong>
                <span class="status-card-helper">Pedidos registrados no sistema</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Pedidos em análise</span>
                <strong class="status-card-value" id="sales-analysis-orders">0</strong>
                <span class="status-card-helper">Aguardando aprovação comercial</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Faturamento total</span>
                <strong class="status-card-value" id="sales-total-revenue">R$ 0,00</strong>
                <span class="status-card-helper">Valor somado dos pedidos cadastrados</span>
              </article>
            </section>

            <section class="section-block">
              <div class="toolbar-row">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input type="text" id="sales-search" class="input-default" placeholder="Pesquisar..." />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="sales-status-filter" class="select-default">
                      <option value="todos">Status</option>
                      <option value="pendente">Pendente</option>
                      <option value="aprovado">Aprovado</option>
                      <option value="concluído">Concluído</option>
                      <option value="cancelado">Cancelado</option>
                      <option value="em análise">Em análise</option>
                    </select>

                    <select id="sales-client-filter" class="select-default">
                      <option value="todos">Cliente</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="table-wrapper">
                <table class="table-default">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Cliente</th>
                      <th>Item</th>
                      <th>Tipo</th>
                      <th>Valor total</th>
                      <th>Status</th>
                      <th>Data</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="sales-table-body"></tbody>
                </table>
              </div>
              <div class="mobile-card-list" id="sales-mobile-list"></div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
