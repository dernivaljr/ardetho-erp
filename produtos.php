<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/ProdutoController.php';
require __DIR__ . '/includes/view.php';

$controller = new ProdutoController();
$viewData = $controller->listar();
$produtos = $viewData['produtos'];
$categorias = $viewData['categorias'];
$resumo = $viewData['resumo'];
$filtros = $viewData['filtros'];
$flash = $viewData['flash'];
$csrf = $viewData['csrf'];
$queryAtual = http_build_query(array_filter([
    'busca' => $filtros['busca'],
    'categoria' => $filtros['categoria'],
    'status' => $filtros['status'],
], static fn ($valor) => $valor !== ''));
$acaoAtual = 'produtos.php' . ($queryAtual !== '' ? '?' . $queryAtual : '');

$pageTitle = 'Produtos | Ardetho ERP';
$bodyPage = 'products';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'products';
$topbarTitle = 'Produtos e serviços';
$topbarSubtitle = 'Gestão de itens, serviços e estoque';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/products.js',
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
              <h1 class="page-title">Produtos e serviços</h1>
              <p class="page-description">
                Organize itens cadastrados, categorias, valores e controle de disponibilidade.
              </p>
            </div>

            <div class="page-header-actions">
              <a href="produto-form.php" class="btn-primary">Novo item</a>
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
                <span class="status-card-label">Total de itens</span>
                <strong class="status-card-value" id="products-total-items"><?= e($resumo['total'] ?? 0) ?></strong>
                <span class="status-card-helper">Entre produtos e serviços</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Estoque crítico</span>
                <strong class="status-card-value" id="products-critical-stock"><?= e($resumo['estoque_critico'] ?? 0) ?></strong>
                <span class="status-card-helper">Itens com necessidade de reposição</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Categorias ativas</span>
                <strong class="status-card-value" id="products-active-categories"><?= e($resumo['categorias_ativas'] ?? 0) ?></strong>
                <span class="status-card-helper">Distribuição organizada por tipo</span>
              </article>
            </section>

            <section class="section-block">
              <form class="toolbar-row" method="get" action="produtos.php">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input
                      type="text"
                      id="products-search"
                      name="busca"
                      class="input-default"
                      placeholder="Pesquisar..."
                      value="<?= e($filtros['busca']) ?>"
                    />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="products-category-filter" name="categoria" class="select-default">
                      <option value="">Categoria</option>
<?php foreach ($categorias as $categoria): ?>
                      <option value="<?= e($categoria) ?>"<?= selectedIf($filtros['categoria'], $categoria) ?>><?= e($categoria) ?></option>
<?php endforeach; ?>
                    </select>

                    <select id="products-status-filter" name="status" class="select-default">
                      <option value="">Status</option>
<?php foreach (ProdutoController::opcoesStatusFiltro() as $statusFiltro): ?>
                      <option value="<?= e(strtolower($statusFiltro)) ?>"<?= selectedIf($filtros['status'], $statusFiltro) ?>><?= e($statusFiltro) ?></option>
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
                      <th>Tipo</th>
                      <th>Código</th>
                      <th>Nome</th>
                      <th>Categoria</th>
                      <th>Preço</th>
                      <th>Estoque</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="products-table-body">
<?php if (!$produtos): ?>
                    <tr>
                      <td colspan="8">
                        <div class="empty-state">
                          <h3>Nenhum item encontrado</h3>
                          <p>Não há produtos ou serviços compatíveis com os filtros atuais.</p>
                        </div>
                      </td>
                    </tr>
<?php endif; ?>
<?php foreach ($produtos as $produto): ?>
<?php $statusExibicao = ProdutoController::statusExibicao($produto); ?>
                    <tr>
                      <td><?= e($produto['tipo_item'] ?? '') ?></td>
                      <td><?= e(($produto['codigo'] ?? '') ?: '—') ?></td>
                      <td><?= e(($produto['nome'] ?? '') ?: '—') ?></td>
                      <td><?= e(($produto['categoria'] ?? '') ?: '—') ?></td>
                      <td><?= e(ProdutoController::precoExibicao($produto['preco'] ?? 0)) ?></td>
                      <td><?= e(ProdutoController::estoqueExibicao($produto)) ?></td>
                      <td><span class="<?= e(ProdutoController::badgeStatus($statusExibicao)) ?>"><?= e($statusExibicao) ?></span></td>
                      <td>
                        <div class="action-group">
                          <a href="produto-form.php?id=<?= e($produto['id_produto']) ?>" class="btn-secondary">Editar</a>
                          <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-product-status="<?= ($produto['status'] ?? '') === 'Inativo' ? 'ativar' : 'desativar' ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                            <input type="hidden" name="id_produto" value="<?= e($produto['id_produto']) ?>" />
<?php if (($produto['status'] ?? '') === 'Inativo'): ?>
                            <input type="hidden" name="acao" value="ativar" />
                            <button type="submit" class="btn-secondary">Ativar</button>
<?php else: ?>
                            <input type="hidden" name="acao" value="desativar" />
                            <button type="submit" class="btn-danger">Desativar</button>
<?php endif; ?>
                          </form>
                        </div>
                      </td>
                    </tr>
<?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="mobile-card-list" id="products-mobile-list">
<?php if (!$produtos): ?>
                <article class="mobile-data-card">
                  <div class="empty-state">
                    <h3>Nenhum item encontrado</h3>
                    <p>Não há produtos ou serviços compatíveis com os filtros atuais.</p>
                  </div>
                </article>
<?php endif; ?>
<?php foreach ($produtos as $produto): ?>
<?php $statusExibicao = ProdutoController::statusExibicao($produto); ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-header">
                    <span class="mobile-data-card-title"><?= e(($produto['nome'] ?? '') ?: 'Item') ?></span>
                    <span class="<?= e(ProdutoController::badgeStatus($statusExibicao)) ?>"><?= e($statusExibicao) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Tipo</span>
                    <span class="mobile-data-card-value"><?= e(($produto['tipo_item'] ?? '') ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Código</span>
                    <span class="mobile-data-card-value"><?= e(($produto['codigo'] ?? '') ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Categoria</span>
                    <span class="mobile-data-card-value"><?= e(($produto['categoria'] ?? '') ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Preço</span>
                    <span class="mobile-data-card-value"><?= e(ProdutoController::precoExibicao($produto['preco'] ?? 0)) ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Estoque</span>
                    <span class="mobile-data-card-value"><?= e(ProdutoController::estoqueExibicao($produto)) ?></span>
                  </div>

                  <div class="mobile-data-card-actions">
                    <a href="produto-form.php?id=<?= e($produto['id_produto']) ?>" class="btn-secondary">Editar</a>
                    <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-product-status="<?= ($produto['status'] ?? '') === 'Inativo' ? 'ativar' : 'desativar' ?>">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                      <input type="hidden" name="id_produto" value="<?= e($produto['id_produto']) ?>" />
<?php if (($produto['status'] ?? '') === 'Inativo'): ?>
                      <input type="hidden" name="acao" value="ativar" />
                      <button type="submit" class="btn-secondary">Ativar</button>
<?php else: ?>
                      <input type="hidden" name="acao" value="desativar" />
                      <button type="submit" class="btn-danger">Desativar</button>
<?php endif; ?>
                    </form>
                  </div>
                </article>
<?php endforeach; ?>
              </div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
