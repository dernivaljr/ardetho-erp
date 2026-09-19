<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/VendaController.php';
require __DIR__ . '/includes/view.php';

$controller = new VendaController();
$viewData = $controller->listar();
$vendas = $viewData['vendas'];
$clientesFiltro = $viewData['clientesFiltro'];
$resumo = $viewData['resumo'];
$filtros = $viewData['filtros'];
$flash = $viewData['flash'];
$csrf = $viewData['csrf'];
$queryAtual = http_build_query(array_filter([
    'busca' => $filtros['busca'],
    'status' => $filtros['status'],
    'cliente' => $filtros['id_cliente'],
], static fn ($valor) => $valor !== ''));
$acaoAtual = 'vendas.php' . ($queryAtual !== '' ? '?' . $queryAtual : '');

$pageTitle = 'Vendas | Ardetho ERP';
$bodyPage = 'sales';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'sales';
$topbarTitle = 'Vendas';
$topbarSubtitle = 'Gestão de pedidos e acompanhamento comercial';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/sales.js',
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

<?php if ($flash): ?>
          <div class="toast toast-<?= e($flash['tipo'] ?? 'info') ?>" role="status">
            <?= e($flash['mensagem'] ?? '') ?>
          </div>
<?php endif; ?>

          <div class="content-stack">
            <section class="status-grid">
              <article class="status-card">
                <span class="status-card-label">Total de pedidos</span>
                <strong class="status-card-value" id="sales-total-orders"><?= e($resumo['total'] ?? 0) ?></strong>
                <span class="status-card-helper">Pedidos registrados no sistema</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Pedidos em análise</span>
                <strong class="status-card-value" id="sales-analysis-orders"><?= e($resumo['em_analise'] ?? 0) ?></strong>
                <span class="status-card-helper">Aguardando aprovação comercial</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Faturamento total</span>
                <strong class="status-card-value" id="sales-total-revenue"><?= e(VendaController::valorExibicao($resumo['faturamento'] ?? 0)) ?></strong>
                <span class="status-card-helper">Valor somado dos pedidos cadastrados</span>
              </article>
            </section>

            <section class="section-block">
              <form class="toolbar-row" method="get" action="vendas.php">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input
                      type="text"
                      id="sales-search"
                      name="busca"
                      class="input-default"
                      placeholder="Pesquisar..."
                      value="<?= e($filtros['busca']) ?>"
                    />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="sales-status-filter" name="status" class="select-default">
                      <option value="">Status</option>
<?php foreach (VendaController::statusFiltro() as $status): ?>
                      <option value="<?= e(strtolower($status)) ?>"<?= selectedIf($filtros['status'], $status) ?>><?= e($status) ?></option>
<?php endforeach; ?>
                    </select>

                    <select id="sales-client-filter" name="cliente" class="select-default">
                      <option value="">Cliente</option>
<?php foreach ($clientesFiltro as $cliente): ?>
                      <option value="<?= e($cliente['id_cliente']) ?>"<?= selectedIf($filtros['id_cliente'], $cliente['id_cliente']) ?>><?= e(VendaController::clienteNomeExibicao($cliente)) ?></option>
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
                      <th>Cliente</th>
                      <th>Item</th>
                      <th>Tipo</th>
                      <th>Valor total</th>
                      <th>Status</th>
                      <th>Data</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="sales-table-body">
<?php if (!$vendas): ?>
                    <tr>
                      <td colspan="8">
                        <div class="empty-state">
                          <h3>Nenhum pedido encontrado</h3>
                          <p>Não há vendas compatíveis com os filtros atuais.</p>
                        </div>
                      </td>
                    </tr>
<?php endif; ?>
<?php foreach ($vendas as $venda): ?>
                    <tr>
                      <td><?= e(($venda['codigo'] ?? '') ?: '—') ?></td>
                      <td><?= e(VendaController::clienteNomeExibicao($venda)) ?></td>
                      <td><?= e(VendaController::resumoItens($venda)) ?></td>
                      <td><?= e(VendaController::tipoItensResumo($venda)) ?></td>
                      <td><?= e(VendaController::valorExibicao($venda['valor_total'] ?? 0)) ?></td>
                      <td><span class="<?= e(VendaController::badgeStatus((string) ($venda['status'] ?? ''))) ?>"><?= e(($venda['status'] ?? '') ?: '—') ?></span></td>
                      <td><?= e(VendaController::dataExibicao($venda['data_venda'] ?? null)) ?></td>
                      <td>
                        <div class="action-group">
                          <a href="venda-form.php?id=<?= e($venda['id_venda']) ?>" class="btn-secondary">Editar</a>
<?php if (($venda['status'] ?? '') !== 'Cancelado'): ?>
                          <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-sale-status="cancelar">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                            <input type="hidden" name="id_venda" value="<?= e($venda['id_venda']) ?>" />
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
              <div class="mobile-card-list" id="sales-mobile-list">
<?php if (!$vendas): ?>
                <article class="mobile-data-card">
                  <div class="empty-state">
                    <h3>Nenhum pedido encontrado</h3>
                    <p>Não há vendas compatíveis com os filtros atuais.</p>
                  </div>
                </article>
<?php endif; ?>
<?php foreach ($vendas as $venda): ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-header">
                    <span class="mobile-data-card-title"><?= e(($venda['codigo'] ?? '') ?: 'Pedido') ?></span>
                    <span class="<?= e(VendaController::badgeStatus((string) ($venda['status'] ?? ''))) ?>"><?= e(($venda['status'] ?? '') ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Cliente</span>
                    <span class="mobile-data-card-value"><?= e(VendaController::clienteNomeExibicao($venda)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Item</span>
                    <span class="mobile-data-card-value"><?= e(VendaController::resumoItens($venda)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Tipo</span>
                    <span class="mobile-data-card-value"><?= e(VendaController::tipoItensResumo($venda)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Valor total</span>
                    <span class="mobile-data-card-value"><?= e(VendaController::valorExibicao($venda['valor_total'] ?? 0)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Data</span>
                    <span class="mobile-data-card-value"><?= e(VendaController::dataExibicao($venda['data_venda'] ?? null)) ?></span>
                  </div>

                  <div class="mobile-data-card-actions">
                    <a href="venda-form.php?id=<?= e($venda['id_venda']) ?>" class="btn-secondary">Editar</a>
<?php if (($venda['status'] ?? '') !== 'Cancelado'): ?>
                    <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-sale-status="cancelar">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                      <input type="hidden" name="id_venda" value="<?= e($venda['id_venda']) ?>" />
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
