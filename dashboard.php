<?php
$pageTitle = 'Dashboard | Ardetho ERP';
$bodyPage = 'dashboard';
$stylesheets = ['assets/css/dashboard.css'];
$includeFavicon = true;
$activeNav = 'dashboard';
$topbarTitle = 'Dashboard';
$topbarSubtitle = 'Visão geral da operação';
$scripts = [
    'assets/js/data.js',
    'assets/js/storage.js',
    'assets/js/auth.js',
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/dashboard.js',
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
              <h1 class="page-title">Bem-vindo de volta</h1>
              <p class="page-description">
                Acompanhe indicadores importantes, atividades recentes e o status
                geral do seu ambiente de gestão.
              </p>
            </div>

            <div class="page-header-actions" id="dashboard-shortcuts">
              <a href="clientes.php" class="btn-secondary">Novo cliente</a>
              <a href="vendas.php" class="btn-primary">Novo pedido</a>
            </div>
          </div>

          <div class="content-stack">
            <section class="dashboard-notification-grid" id="dashboard-notification-cards">
              <article class="card metric-card" id="dashboard-alert-expiration-card">
                <span class="metric-label">Alertas de vencimento</span>
                <strong class="metric-value" id="dashboard-alert-expiration-value">0</strong>
                <span class="metric-change" id="dashboard-alert-expiration-text">Nenhum vencimento próximo.</span>
              </article>

              <article class="card metric-card" id="dashboard-alert-orders-card">
                <span class="metric-label">Pedidos pendentes</span>
                <strong class="metric-value" id="dashboard-alert-orders-value">0</strong>
                <span class="metric-change" id="dashboard-alert-orders-text">Nenhum pedido pendente.</span>
              </article>

              <article class="card metric-card" id="dashboard-daily-summary-card">
                <span class="metric-label">Resumo diário</span>
                <strong class="metric-value" id="dashboard-daily-summary-value">R$ 0,00</strong>
                <span class="metric-change" id="dashboard-daily-summary-text">Saldo consolidado do dia.</span>
              </article>
            </section>

            <section class="analytics-grid" id="dashboard-analytics-section">
              <article class="card chart-card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Desempenho financeiro</h2>
                  <span class="badge-info">Atualizado hoje</span>
                </div>

                <div class="chart-placeholder">
                  <div class="chart-bars" id="chart-bars">
                  </div>
                  <div class="chart-labels" id="chart-labels">
                  </div>
                </div>
              </article>

              <article class="card summary-card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Resumo rápido</h2>
                </div>

                <div class="summary-list">
                  <div class="summary-item">
                    <span class="summary-label">Módulos ativos</span>
                    <strong class="summary-value" id="summary-active-modules">0</strong>
                  </div>

                  <div class="summary-item">
                    <span class="summary-label">Pedidos em aberto</span>
                    <strong class="summary-value" id="summary-open-orders">0</strong>
                  </div>

                  <div class="summary-item">
                    <span class="summary-label">Recebimentos previstos</span>
                    <strong class="summary-value" id="summary-expected-income">R$ 0,00</strong>
                  </div>

                  <div class="summary-item">
                    <span class="summary-label">Pagamentos agendados</span>
                    <strong class="summary-value" id="summary-scheduled-payments">R$ 0,00</strong>
                  </div>
                </div>
              </article>
            </section>

            <section class="metrics-grid" id="dashboard-metrics-section">
              <article class="card metric-card">
                <span class="metric-label">Clientes ativos</span>
                <strong class="metric-value" id="metric-active-clients">0</strong>
                <span class="metric-change" id="metric-active-clients-text">0 clientes com status ativo no sistema.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Produtos cadastrados</span>
                <strong class="metric-value" id="metric-registered-products">0</strong>
                <span class="metric-change" id="metric-registered-products-text">0 itens disponíveis na base atual.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Vendas do mês</span>
                <strong class="metric-value" id="metric-monthly-sales">R$ 0,00</strong>
                <span class="metric-change" id="metric-monthly-sales-text">Receitas registradas no financeiro.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Contas pendentes</span>
                <strong class="metric-value" id="metric-pending-bills">0</strong>
                <span class="metric-change" id="metric-pending-bills-text">0 lançamentos aguardando movimentação.</span>
              </article>
            </section>

            <section class="split-grid" id="dashboard-activity-section">
              <article class="card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Atividades recentes</h2>
                  <a href="reports.html" class="btn-secondary">Ver tudo</a>
                </div>

                <div class="activity-list" id="activity-list"></div>
              </article>

              <article class="card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Status operacional</h2>
                </div>

                <div class="status-list" id="status-list"></div>
              </article>
            </section>

            <section class="section-block" id="dashboard-orders-section">
              <div class="section-block-header">
                <h2 class="section-block-title">Pedidos recentes</h2>
                <div class="section-block-actions">
                  <a href="vendas.php" class="btn-secondary">Ver pedidos</a>
                </div>
              </div>

              <div class="table-wrapper">
                <table class="table-default">
                  <thead>
                    <tr>
                      <th>Pedido</th>
                      <th>Cliente</th>
                      <th>Data</th>
                      <th>Valor</th>
                      <th>Status</th>
                      <th>Responsável</th>
                    </tr>
                  </thead>
                  <tbody id="recent-orders-body"></tbody>
                </table>
              </div>
              <div class="mobile-card-list" id="recent-orders-mobile"></div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>