<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/RelatorioController.php';
require __DIR__ . '/includes/view.php';

$controller = new RelatorioController();

if (($_GET['export'] ?? '') === 'csv') {
    $controller->exportarCsv();
}

$viewData = $controller->index();
$filtros = $viewData['filtros'];
$financeiro = $viewData['financeiro'];
$vendas = $viewData['vendas'];
$relatoriosDisponiveis = $viewData['relatoriosDisponiveis'];
$analiticos = $viewData['analiticos'];
$ultimosFinanceiros = $viewData['ultimosFinanceiros'];
$erros = $viewData['erros'];
$queryExportacao = http_build_query(array_filter([
    'periodo' => $filtros['periodo'],
    'busca' => $filtros['busca'],
    'export' => 'csv',
], static fn ($valor) => $valor !== ''));

$pageTitle = 'Relatórios | Ardetho ERP';
$bodyPage = 'reports';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'reports';
$topbarTitle = 'Relatórios';
$topbarSubtitle = 'Indicadores e visão analítica da operação';
$scripts = [
    'assets/js/layout.js',
    'assets/js/reports.js',
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
              <h1 class="page-title">Relatórios</h1>
              <p class="page-description">
                Acompanhe métricas, desempenho comercial e indicadores operacionais em um só lugar.
              </p>
            </div>

            <form class="page-header-actions" method="get" action="relatorios.php" data-reports-period-form>
              <select id="reports-period-filter" name="periodo" class="select-default">
<?php foreach (RelatorioController::periodos() as $periodo): ?>
                <option value="<?= e($periodo) ?>"<?= selectedIf($filtros['periodo'], $periodo) ?>><?= e($controller->rotuloPeriodo($periodo)) ?></option>
<?php endforeach; ?>
              </select>
              <input type="hidden" name="busca" value="<?= e($filtros['busca']) ?>" />
              <a href="relatorios.php?<?= e($queryExportacao) ?>" class="btn-primary" id="reports-export-button">Exportar relatório</a>
            </form>
          </div>

<?php if ($erros): ?>
          <div class="toast toast-danger" role="alert">
            <?= e(implode(' ', $erros)) ?>
          </div>
<?php endif; ?>

          <div class="content-stack">
            <section class="report-grid">
              <article class="card report-card">
                <span class="status-card-label">Receita total</span>
                <strong class="status-card-value" id="reports-total-revenue"><?= e(RelatorioController::valorExibicao($financeiro['total_receitas'] ?? 0)) ?></strong>
                <span class="status-card-helper">Somatório das receitas registradas</span>
              </article>

              <article class="card report-card">
                <span class="status-card-label">Saldo consolidado</span>
                <strong class="status-card-value" id="reports-total-balance"><?= e(RelatorioController::valorExibicao($financeiro['saldo'] ?? 0)) ?></strong>
                <span class="status-card-helper">Receitas menos despesas não canceladas</span>
              </article>

              <article class="card report-card">
                <span class="status-card-label">Pedidos concluídos</span>
                <strong class="status-card-value" id="reports-completed-orders"><?= e($vendas['concluidas'] ?? 0) ?></strong>
                <span class="status-card-helper">Pedidos com status concluído</span>
              </article>
            </section>

            <form class="toolbar-row" method="get" action="relatorios.php">
              <input type="hidden" name="periodo" value="<?= e($filtros['periodo']) ?>" />
              <div class="toolbar-left">
                <div class="search-bar">
                  <input
                    type="text"
                    id="reports-search"
                    name="busca"
                    class="input-default"
                    placeholder="Pesquisar por relatório, categoria, período ou responsável"
                    value="<?= e($filtros['busca']) ?>"
                  />
                </div>
              </div>
            </form>

            <section class="split-grid">
              <article class="card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Relatórios disponíveis</h2>
                </div>

                <div class="option-list" id="reports-available-list">
<?php if (!$relatoriosDisponiveis): ?>
                  <div class="empty-state">
                    <h3>Nenhum relatório encontrado</h3>
                    <p>Não há blocos compatíveis com a busca atual.</p>
                  </div>
<?php endif; ?>
<?php foreach ($relatoriosDisponiveis as $relatorio): ?>
                  <div class="option-item">
                    <div class="option-content">
                      <span class="option-title"><?= e($relatorio['titulo']) ?></span>
                      <span class="option-description"><?= e($relatorio['descricao']) ?></span>
                    </div>
                    <a href="<?= e($relatorio['href']) ?>" class="btn-secondary">Abrir</a>
                  </div>
<?php endforeach; ?>
                </div>
              </article>

              <article class="card">
                <div class="section-block-header">
                  <h2 class="section-block-title">Resumo analítico</h2>
                </div>

                <div class="option-list">
<?php foreach ($analiticos as $item): ?>
                  <div class="option-item">
                    <div class="option-content">
                      <span class="option-title"><?= e($item['titulo']) ?></span>
                      <span class="option-description"><?= e($item['descricao']) ?></span>
                    </div>
                    <span class="<?= e($item['classe']) ?>"><?= e($item['badge']) ?></span>
                  </div>
<?php endforeach; ?>
                </div>
              </article>
            </section>

            <section class="section-block">
              <div class="section-block-header">
                <h2 class="section-block-title">Últimos lançamentos financeiros</h2>
              </div>

              <div class="table-wrapper">
                <table class="table-default">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Tipo</th>
                      <th>Categoria</th>
                      <th>Descrição</th>
                      <th>Valor</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="reports-table-body">
<?php if (!$ultimosFinanceiros): ?>
                    <tr>
                      <td colspan="6">
                        <div class="empty-state">
                          <h3>Nenhum lançamento encontrado</h3>
                          <p>Não há dados financeiros para exibir no relatório.</p>
                        </div>
                      </td>
                    </tr>
<?php endif; ?>
<?php foreach ($ultimosFinanceiros as $lancamento): ?>
                    <tr>
                      <td><?= e(($lancamento['codigo'] ?? '') ?: '-') ?></td>
                      <td><?= e(($lancamento['tipo'] ?? '') ?: '-') ?></td>
                      <td><?= e(($lancamento['categoria'] ?? '') ?: '-') ?></td>
                      <td><?= e(($lancamento['descricao'] ?? '') ?: '-') ?></td>
                      <td><?= e(RelatorioController::valorExibicao($lancamento['valor'] ?? 0)) ?></td>
                      <td><span class="<?= e(RelatorioController::badgeFinanceiro((string) ($lancamento['status'] ?? ''))) ?>"><?= e(($lancamento['status'] ?? '') ?: '-') ?></span></td>
                    </tr>
<?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="mobile-card-list" id="reports-mobile-list">
<?php if (!$ultimosFinanceiros): ?>
                <article class="mobile-data-card">
                  <div class="empty-state">
                    <h3>Nenhum lançamento encontrado</h3>
                    <p>Não há dados financeiros para exibir no relatório.</p>
                  </div>
                </article>
<?php endif; ?>
<?php foreach ($ultimosFinanceiros as $lancamento): ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-header">
                    <span class="mobile-data-card-title"><?= e(($lancamento['codigo'] ?? '') ?: 'Lançamento') ?></span>
                    <span class="<?= e(RelatorioController::badgeFinanceiro((string) ($lancamento['status'] ?? ''))) ?>"><?= e(($lancamento['status'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Tipo</span>
                    <span class="mobile-data-card-value"><?= e(($lancamento['tipo'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Categoria</span>
                    <span class="mobile-data-card-value"><?= e(($lancamento['categoria'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Descrição</span>
                    <span class="mobile-data-card-value"><?= e(($lancamento['descricao'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Valor</span>
                    <span class="mobile-data-card-value"><?= e(RelatorioController::valorExibicao($lancamento['valor'] ?? 0)) ?></span>
                  </div>
                </article>
<?php endforeach; ?>
              </div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
