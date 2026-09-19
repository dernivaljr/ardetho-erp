<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/FinanceiroController.php';
require __DIR__ . '/includes/view.php';

$controller = new FinanceiroController();
$viewData = $controller->listar();
$lancamentos = $viewData['lancamentos'];
$clientesFiltro = $viewData['clientesFiltro'];
$resumo = $viewData['resumo'];
$filtros = $viewData['filtros'];
$flash = $viewData['flash'];
$csrf = $viewData['csrf'];
$queryAtual = http_build_query(array_filter([
    'busca' => $filtros['busca'],
    'tipo' => $filtros['tipo'],
    'status' => $filtros['status'],
    'cliente' => $filtros['id_cliente'],
], static fn ($valor) => $valor !== ''));
$acaoAtual = 'financeiro.php' . ($queryAtual !== '' ? '?' . $queryAtual : '');

$pageTitle = 'Financeiro | Ardetho ERP';
$bodyPage = 'financial';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'financial';
$topbarTitle = 'Financeiro';
$topbarSubtitle = 'Contas, vencimentos e visão financeira da operação';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/financial.js',
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
              <h1 class="page-title">Financeiro</h1>
              <p class="page-description">
                Monitore entradas, saídas, vencimentos e indicadores financeiros do período.
              </p>
            </div>

            <div class="page-header-actions">
              <a href="financeiro-form.php" class="btn-primary">Novo lançamento</a>
            </div>
          </div>

<?php if ($flash): ?>
          <div class="toast toast-<?= e($flash['tipo'] ?? 'info') ?>" role="status">
            <?= e($flash['mensagem'] ?? '') ?>
          </div>
<?php endif; ?>

          <div class="content-stack">
            <section class="finance-summary">
              <article class="card finance-item">
                <span class="finance-label">Saldo atual</span>
                <strong class="finance-value" id="financial-current-balance"><?= e(FinanceiroController::valorExibicao($resumo['saldo_atual'] ?? 0)) ?></strong>
              </article>

              <article class="card finance-item">
                <span class="finance-label">Contas a receber</span>
                <strong class="finance-value" id="financial-accounts-receivable"><?= e(FinanceiroController::valorExibicao($resumo['contas_receber'] ?? 0)) ?></strong>
              </article>

              <article class="card finance-item">
                <span class="finance-label">Contas a pagar</span>
                <strong class="finance-value" id="financial-accounts-payable"><?= e(FinanceiroController::valorExibicao($resumo['contas_pagar'] ?? 0)) ?></strong>
              </article>

              <article class="card finance-item">
                <span class="finance-label">Vencimentos próximos</span>
                <strong class="finance-value" id="financial-upcoming-due"><?= e(FinanceiroController::valorExibicao($resumo['vencimentos_proximos'] ?? 0)) ?></strong>
              </article>
            </section>

            <section class="section-block">
              <form class="toolbar-row" method="get" action="financeiro.php">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input
                      type="text"
                      id="financial-search"
                      name="busca"
                      class="input-default"
                      placeholder="Pesquisar..."
                      value="<?= e($filtros['busca']) ?>"
                    />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="financial-type-filter" name="tipo" class="select-default">
                      <option value="">Tipo</option>
<?php foreach (FinanceiroController::tipos() as $tipo): ?>
                      <option value="<?= e(strtolower($tipo)) ?>"<?= selectedIf($filtros['tipo'], $tipo) ?>><?= e($tipo) ?></option>
<?php endforeach; ?>
                    </select>

                    <select id="financial-status-filter" name="status" class="select-default">
                      <option value="">Status</option>
<?php foreach (FinanceiroController::statusFiltro() as $status): ?>
                      <option value="<?= e(strtolower($status)) ?>"<?= selectedIf($filtros['status'], $status) ?>><?= e($status) ?></option>
<?php endforeach; ?>
                    </select>

                    <select id="financial-client-filter" name="cliente" class="select-default">
                      <option value="">Cliente</option>
<?php foreach ($clientesFiltro as $cliente): ?>
                      <option value="<?= e($cliente['id_cliente']) ?>"<?= selectedIf($filtros['id_cliente'], $cliente['id_cliente']) ?>><?= e(FinanceiroController::clienteNomeExibicao($cliente)) ?></option>
<?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn-secondary">Filtrar</button>
                  </div>
                </div>
              </form>

              <div class="table-wrapper">
                <table class="table-default">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Tipo</th>
                      <th>Cliente</th>
                      <th>Categoria</th>
                      <th>Descrição</th>
                      <th>Vencimento</th>
                      <th>Valor</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="financial-table-body">
<?php if (!$lancamentos): ?>
                    <tr>
                      <td colspan="9">
                        <div class="empty-state">
                          <h3>Nenhum lançamento encontrado</h3>
                          <p>Não há registros compatíveis com os filtros atuais.</p>
                        </div>
                      </td>
                    </tr>
<?php endif; ?>
<?php foreach ($lancamentos as $lancamento): ?>
                    <tr>
                      <td><?= e(($lancamento['codigo'] ?? '') ?: '-') ?></td>
                      <td><?= e(($lancamento['tipo'] ?? '') ?: '-') ?></td>
                      <td><?= e(FinanceiroController::clienteNomeExibicao($lancamento)) ?></td>
                      <td><?= e(($lancamento['categoria'] ?? '') ?: '-') ?></td>
                      <td><?= e(($lancamento['descricao'] ?? '') ?: '-') ?></td>
                      <td><?= e(FinanceiroController::dataExibicao($lancamento['data_vencimento'] ?? null)) ?></td>
                      <td><?= e(FinanceiroController::valorExibicao($lancamento['valor'] ?? 0)) ?></td>
                      <td><span class="<?= e(FinanceiroController::badgeStatus((string) ($lancamento['status'] ?? ''))) ?>"><?= e(($lancamento['status'] ?? '') ?: '-') ?></span></td>
                      <td>
                        <div class="action-group">
                          <a href="financeiro-form.php?id=<?= e($lancamento['id_financeiro']) ?>" class="btn-secondary">Editar</a>
<?php if (($lancamento['status'] ?? '') !== 'Cancelado'): ?>
                          <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-financial-status="cancelar">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                            <input type="hidden" name="id_financeiro" value="<?= e($lancamento['id_financeiro']) ?>" />
                            <input type="hidden" name="acao" value="cancelar" />
                            <button type="submit" class="btn-danger">Cancelar</button>
                          </form>
<?php endif; ?>
                        </div>
                      </td>
                    </tr>
<?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="mobile-card-list" id="financial-mobile-list">
<?php if (!$lancamentos): ?>
                <article class="mobile-data-card">
                  <div class="empty-state">
                    <h3>Nenhum lançamento encontrado</h3>
                    <p>Não há registros compatíveis com os filtros atuais.</p>
                  </div>
                </article>
<?php endif; ?>
<?php foreach ($lancamentos as $lancamento): ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-header">
                    <span class="mobile-data-card-title"><?= e(($lancamento['codigo'] ?? '') ?: 'Lançamento') ?></span>
                    <span class="<?= e(FinanceiroController::badgeStatus((string) ($lancamento['status'] ?? ''))) ?>"><?= e(($lancamento['status'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Tipo</span>
                    <span class="mobile-data-card-value"><?= e(($lancamento['tipo'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Cliente</span>
                    <span class="mobile-data-card-value"><?= e(FinanceiroController::clienteNomeExibicao($lancamento)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Descrição</span>
                    <span class="mobile-data-card-value"><?= e(($lancamento['descricao'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Vencimento</span>
                    <span class="mobile-data-card-value"><?= e(FinanceiroController::dataExibicao($lancamento['data_vencimento'] ?? null)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Valor</span>
                    <span class="mobile-data-card-value"><?= e(FinanceiroController::valorExibicao($lancamento['valor'] ?? 0)) ?></span>
                  </div>

                  <div class="mobile-data-card-actions">
                    <a href="financeiro-form.php?id=<?= e($lancamento['id_financeiro']) ?>" class="btn-secondary">Editar</a>
<?php if (($lancamento['status'] ?? '') !== 'Cancelado'): ?>
                    <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-financial-status="cancelar">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                      <input type="hidden" name="id_financeiro" value="<?= e($lancamento['id_financeiro']) ?>" />
                      <input type="hidden" name="acao" value="cancelar" />
                      <button type="submit" class="btn-danger">Cancelar</button>
                    </form>
<?php endif; ?>
                  </div>
                </article>
<?php endforeach; ?>
              </div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
