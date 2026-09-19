<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/DashboardController.php';
require __DIR__ . '/includes/view.php';

$controller = new DashboardController();
$viewData = $controller->index();
$metricas = $viewData['metricas'];
$vendasRecentes = $viewData['vendasRecentes'];
$resumoStatus = $viewData['resumoStatus'];
$faturamentoMensal = $viewData['faturamentoMensal'];
$atividades = $viewData['atividades'];
$modulos = $viewData['modulos'];
$usuarioDashboard = usuarioAtual();
$nomeResponsavel = $usuarioDashboard['nome'] ?? 'Usuario Ardetho';
$modulosAtivos = count(array_filter($modulos, static fn ($modulo) => ($modulo['status'] ?? '') === 'Ativo'));
$valoresMensais = array_map(static fn ($mes) => (float) $mes['total'], $faturamentoMensal);
$maiorFaturamentoMensal = $valoresMensais ? max($valoresMensais) : 0.0;
$temFaturamentoMensal = $maiorFaturamentoMensal > 0;

$pageTitle = 'Dashboard | Ardetho ERP';
$bodyPage = 'dashboard';
$stylesheets = ['assets/css/dashboard.css'];
$includeFavicon = true;
$activeNav = 'dashboard';
$topbarTitle = 'Dashboard';
$topbarSubtitle = 'Visão geral da operação';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
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
              <a href="cliente-form.php" class="btn-secondary">Novo cliente</a>
              <a href="venda-form.php" class="btn-primary">Novo pedido</a>
            </div>
          </div>

          <div class="content-stack">
            <section class="dashboard-notification-grid" id="dashboard-notification-cards">
              <article class="card metric-card" id="dashboard-alert-expiration-card">
                <span class="metric-label">Alertas de vencimento</span>
                <strong class="metric-value" id="dashboard-alert-expiration-value"><?= e($metricas['financeiro_vencimentos_proximos']) ?></strong>
                <span class="metric-change" id="dashboard-alert-expiration-text"><?= e($metricas['financeiro_vencimentos_proximos']) ?> lançamento(s) pendente(s) vencendo em até 7 dias.</span>
              </article>

              <article class="card metric-card" id="dashboard-alert-orders-card">
                <span class="metric-label">Pedidos pendentes</span>
                <strong class="metric-value" id="dashboard-alert-orders-value"><?= e($metricas['pedidos_abertos']) ?></strong>
                <span class="metric-change" id="dashboard-alert-orders-text"><?= e($metricas['pedidos_abertos']) ?> pedido(s) em análise, aprovado(s), faturado(s) ou pendente(s).</span>
              </article>

              <article class="card metric-card" id="dashboard-daily-summary-card">
                <span class="metric-label">Resumo diário</span>
                <strong class="metric-value" id="dashboard-daily-summary-value"><?= e(DashboardController::valorExibicao($metricas['faturamento_hoje'])) ?></strong>
                <span class="metric-change" id="dashboard-daily-summary-text">Vendas de hoje, exceto canceladas.</span>
              </article>
            </section>

            <section class="analytics-grid" id="dashboard-analytics-section">
              <article class="card chart-card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Faturamento por mês</h2>
                  <span class="badge-info">Atualizado hoje</span>
                </div>

                <div class="chart-placeholder">
                  <div class="chart-bars" id="chart-bars">
<?php if (!$temFaturamentoMensal): ?>
                    <div class="empty-state">Sem vendas faturadas no período.</div>
<?php else: ?>
<?php foreach ($faturamentoMensal as $mes): ?>
<?php $altura = max(((float) $mes['total'] / $maiorFaturamentoMensal) * 100, 12); ?>
                    <div class="chart-bar" title="<?= e($mes['rotulo'] . ': ' . DashboardController::valorExibicao($mes['total'])) ?>" style="height: <?= e(number_format($altura, 2, '.', '')) ?>%"></div>
<?php endforeach; ?>
<?php endif; ?>
                  </div>
                  <div class="chart-labels" id="chart-labels">
<?php foreach ($faturamentoMensal as $mes): ?>
                    <span><?= e($mes['rotulo']) ?></span>
<?php endforeach; ?>
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
                    <strong class="summary-value" id="summary-active-modules"><?= e($modulosAtivos) ?></strong>
                  </div>

                  <div class="summary-item">
                    <span class="summary-label">Pedidos em aberto</span>
                    <strong class="summary-value" id="summary-open-orders"><?= e($metricas['pedidos_abertos']) ?></strong>
                  </div>

                  <div class="summary-item">
                    <span class="summary-label">Faturamento válido</span>
                    <strong class="summary-value" id="summary-expected-income"><?= e(DashboardController::valorExibicao($metricas['faturamento_total'])) ?></strong>
                  </div>

                  <div class="summary-item">
                    <span class="summary-label">Financeiro</span>
                    <strong class="summary-value" id="summary-scheduled-payments"><?= e(DashboardController::valorExibicao($metricas['financeiro_saldo'])) ?></strong>
                  </div>
                </div>
              </article>
            </section>

            <section class="metrics-grid" id="dashboard-metrics-section">
              <article class="card metric-card">
                <span class="metric-label">Clientes ativos</span>
                <strong class="metric-value" id="metric-active-clients"><?= e($metricas['clientes_ativos']) ?></strong>
                <span class="metric-change" id="metric-active-clients-text"><?= e($metricas['clientes_ativos']) ?> cliente(s) com status ativo no sistema.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Produtos/Serviços ativos</span>
                <strong class="metric-value" id="metric-registered-products"><?= e($metricas['produtos_servicos_ativos']) ?></strong>
                <span class="metric-change" id="metric-registered-products-text"><?= e($metricas['produtos_ativos']) ?> produto(s) e <?= e($metricas['servicos_ativos']) ?> serviço(s) ativos.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Vendas válidas</span>
                <strong class="metric-value" id="metric-monthly-sales"><?= e($metricas['vendas_validas']) ?></strong>
                <span class="metric-change" id="metric-monthly-sales-text">Pedidos registrados, exceto cancelados.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Faturamento total</span>
                <strong class="metric-value" id="metric-pending-bills"><?= e(DashboardController::valorExibicao($metricas['faturamento_total'])) ?></strong>
                <span class="metric-change" id="metric-pending-bills-text">Soma de vendas, exceto canceladas.</span>
              </article>
            </section>

            <section class="split-grid" id="dashboard-activity-section">
              <article class="card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Atividades recentes</h2>
                  <a href="vendas.php" class="btn-secondary">Ver tudo</a>
                </div>

                <div class="activity-list" id="activity-list">
<?php if (!$atividades): ?>
                  <div class="activity-item">
                    <div class="activity-dot neutral"></div>
                    <div class="activity-content">
                      <strong>Nenhuma atividade recente</strong>
                      <p>Não há clientes, produtos ou vendas para exibir no momento.</p>
                    </div>
                    <span class="activity-time">Agora</span>
                  </div>
<?php endif; ?>
<?php foreach ($atividades as $atividade): ?>
                  <div class="activity-item">
                    <div class="activity-dot <?= e($atividade['tipo']) ?>"></div>
                    <div class="activity-content">
                      <strong><?= e($atividade['titulo']) ?></strong>
                      <p><?= e($atividade['descricao']) ?></p>
                    </div>
                    <span class="activity-time"><?= e($atividade['tempo']) ?></span>
                  </div>
<?php endforeach; ?>
                </div>
              </article>

              <article class="card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Status operacional</h2>
                </div>

                <div class="status-list" id="status-list">
<?php if (!$resumoStatus): ?>
                  <div class="status-item">
                    <span class="status-label">Vendas</span>
                    <span class="badge-neutral">Sem registros</span>
                  </div>
<?php endif; ?>
<?php foreach ($resumoStatus as $status): ?>
                  <div class="status-item">
                    <span class="status-label"><?= e($status['status'] ?: 'Sem status') ?></span>
                    <span class="<?= e(DashboardController::badgeStatus((string) ($status['status'] ?? ''))) ?>"><?= e($status['total']) ?></span>
                  </div>
<?php endforeach; ?>
<?php foreach ($modulos as $modulo): ?>
                  <div class="status-item">
                    <span class="status-label"><?= e($modulo['nome']) ?></span>
                    <span class="<?= e($modulo['classe']) ?>"><?= e($modulo['status']) ?></span>
                  </div>
<?php endforeach; ?>
                </div>
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
                  <tbody id="recent-orders-body">
<?php if (!$vendasRecentes): ?>
                    <tr>
                      <td colspan="6">Nenhum pedido recente encontrado.</td>
                    </tr>
<?php endif; ?>
<?php foreach ($vendasRecentes as $venda): ?>
                    <tr>
                      <td><?= e(($venda['codigo'] ?? '') ?: 'Pedido') ?></td>
                      <td><?= e(DashboardController::clienteNomeExibicao($venda)) ?></td>
                      <td><?= e(DashboardController::dataExibicao($venda['data_venda'] ?? null)) ?></td>
                      <td><?= e(DashboardController::valorExibicao($venda['valor_total'] ?? 0)) ?></td>
                      <td><span class="<?= e(DashboardController::badgeStatus((string) ($venda['status'] ?? ''))) ?>"><?= e(($venda['status'] ?? '') ?: '-') ?></span></td>
                      <td><?= e($nomeResponsavel) ?></td>
                    </tr>
<?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="mobile-card-list" id="recent-orders-mobile">
<?php if (!$vendasRecentes): ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Nenhum pedido recente encontrado.</span>
                  </div>
                </article>
<?php endif; ?>
<?php foreach ($vendasRecentes as $venda): ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-header">
                    <span class="mobile-data-card-title"><?= e(($venda['codigo'] ?? '') ?: 'Pedido') ?></span>
                    <span class="<?= e(DashboardController::badgeStatus((string) ($venda['status'] ?? ''))) ?>"><?= e(($venda['status'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Cliente</span>
                    <span class="mobile-data-card-value"><?= e(DashboardController::clienteNomeExibicao($venda)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Data</span>
                    <span class="mobile-data-card-value"><?= e(DashboardController::dataExibicao($venda['data_venda'] ?? null)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Valor</span>
                    <span class="mobile-data-card-value"><?= e(DashboardController::valorExibicao($venda['valor_total'] ?? 0)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Responsável</span>
                    <span class="mobile-data-card-value"><?= e($nomeResponsavel) ?></span>
                  </div>
                </article>
<?php endforeach; ?>
              </div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
