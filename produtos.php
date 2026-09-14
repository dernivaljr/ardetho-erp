<?php
$pageTitle = 'Produtos | Ardetho ERP';
$bodyPage = 'products';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'products';
$topbarTitle = 'Produtos e serviços';
$topbarSubtitle = 'Gestão de itens, serviços e estoque';
$scripts = [
    'assets/js/data.js',
    'assets/js/storage.js',
    'assets/js/auth.js',
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

          <div class="content-stack">
            <section class="status-grid">
              <article class="status-card">
                <span class="status-card-label">Total de itens</span>
                <strong class="status-card-value" id="products-total-items">0</strong>
                <span class="status-card-helper">Entre produtos e serviços</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Estoque crítico</span>
                <strong class="status-card-value" id="products-critical-stock">0</strong>
                <span class="status-card-helper">Itens com necessidade de reposição</span>
              </article>

              <article class="status-card">
                <span class="status-card-label">Categorias ativas</span>
                <strong class="status-card-value" id="products-active-categories">0</strong>
                <span class="status-card-helper">Distribuição organizada por tipo</span>
              </article>
            </section>

            <section class="section-block">
              <div class="toolbar-row">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input type="text" id="products-search" class="input-default" placeholder="Pesquisar..." />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="products-category-filter" class="select-default">
                      <option value="todas">Categoria</option>
                    </select>

                    <select id="products-status-filter" class="select-default">
                      <option value="todos">Status</option>
                      <option value="disponível">Disponível</option>
                      <option value="baixo estoque">Baixo estoque</option>
                      <option value="indisponível">Indisponível</option>
                      <option value="ativo">Ativo</option>
                      <option value="em análise">Em análise</option>
                      <option value="inativo">Inativo</option>
                    </select>
                  </div>
                </div>
              </div>

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
                  <tbody id="products-table-body"></tbody>
                </table>
              </div>
              <div class="mobile-card-list" id="products-mobile-list"></div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>