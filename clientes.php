<?php
$pageTitle = 'Clientes | Ardetho ERP';
$bodyPage = 'clients';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'clients';
$topbarTitle = 'Clientes';
$topbarSubtitle = 'Gestão de cadastros e relacionamento';
$scripts = [
    'assets/js/data.js',
    'assets/js/storage.js',
    'assets/js/auth.js',
    'assets/js/layout.js',
    'assets/js/clients.js',
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
              <h1 class="page-title">Clientes</h1>
              <p class="page-description">
                Visualize, filtre e acompanhe os clientes cadastrados no sistema.
              </p>
            </div>

            <div class="page-header-actions">
              <a href="cliente-form.php" class="btn-primary">Novo cliente</a>
            </div>
          </div>

          <div class="content-stack">
            <section class="section-block">
              <div class="toolbar-row">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input id="clients-search" type="text" class="input-default" placeholder="Pesquisar..." />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="clients-status-filter" class="select-default">
                      <option value="todos">Status</option>
                      <option value="ativo">Ativo</option>
                      <option value="em análise">Em análise</option>
                      <option value="inativo">Inativo</option>
                      <option value="pendente">Pendente</option>
                    </select>

                    <select id="clients-city-filter" class="select-default">
                      <option value="todas">Cidade</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="table-wrapper">
                <table class="table-default">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Nome / Razão social</th>
                      <th>Documento</th>
                      <th>Contato</th>
                      <th>Cidade</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="clients-table-body"></tbody>
                </table>
              </div>
              <div class="mobile-card-list" id="clients-mobile-list"></div>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>