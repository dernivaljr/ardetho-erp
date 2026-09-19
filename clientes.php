<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/ClienteController.php';
require __DIR__ . '/includes/view.php';

$controller = new ClienteController();
$viewData = $controller->listar();
$clientes = $viewData['clientes'];
$cidades = $viewData['cidades'];
$filtros = $viewData['filtros'];
$flash = $viewData['flash'];
$csrf = $viewData['csrf'];
$queryAtual = http_build_query(array_filter([
    'busca' => $filtros['busca'],
    'status' => $filtros['status'],
    'cidade' => $filtros['cidade'],
], static fn ($valor) => $valor !== ''));
$acaoAtual = 'clientes.php' . ($queryAtual !== '' ? '?' . $queryAtual : '');

$pageTitle = 'Clientes | Ardetho ERP';
$bodyPage = 'clients';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'clients';
$topbarTitle = 'Clientes';
$topbarSubtitle = 'Gestão de cadastros e relacionamento';
$scripts = [
    'assets/js/layout.js',
    'assets/js/clients.js',
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

<?php if ($flash): ?>
          <div class="toast toast-<?= e($flash['tipo'] ?? 'info') ?>" role="status">
            <?= e($flash['mensagem'] ?? '') ?>
          </div>
<?php endif; ?>

          <div class="content-stack">
            <section class="section-block">
              <form class="toolbar-row" method="get" action="clientes.php">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input
                      id="clients-search"
                      name="busca"
                      type="text"
                      class="input-default"
                      placeholder="Pesquisar..."
                      value="<?= e($filtros['busca']) ?>"
                    />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="clients-status-filter" name="status" class="select-default">
                      <option value="">Status</option>
                      <option value="ativo"<?= selectedIf($filtros['status'], 'Ativo') ?>>Ativo</option>
                      <option value="em análise"<?= selectedIf($filtros['status'], 'Em análise') ?>>Em análise</option>
                      <option value="inativo"<?= selectedIf($filtros['status'], 'Inativo') ?>>Inativo</option>
                      <option value="pendente"<?= selectedIf($filtros['status'], 'Pendente') ?>>Pendente</option>
                    </select>

                    <select id="clients-city-filter" name="cidade" class="select-default">
                      <option value="">Cidade</option>
<?php foreach ($cidades as $cidade): ?>
                      <option value="<?= e($cidade) ?>"<?= selectedIf($filtros['cidade'], $cidade) ?>><?= e($cidade) ?></option>
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
                      <th>Nome / Razão social</th>
                      <th>Documento</th>
                      <th>Contato</th>
                      <th>Cidade</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="clients-table-body">
<?php if (!$clientes): ?>
                    <tr>
                      <td colspan="7">
                        <div class="empty-state">
                          <h3>Nenhum cliente encontrado</h3>
                          <p>Não há clientes compatíveis com os filtros atuais.</p>
                        </div>
                      </td>
                    </tr>
<?php endif; ?>
<?php foreach ($clientes as $cliente): ?>
                    <tr>
                      <td><?= e($cliente['tipo_pessoa'] ?? '') ?></td>
                      <td><?= e(ClienteController::nomeExibicao($cliente) ?: '—') ?></td>
                      <td><?= e(ClienteController::documentoExibicao($cliente) ?: '—') ?></td>
                      <td><?= e(ClienteController::contatoExibicao($cliente) ?: '—') ?></td>
                      <td><?= e(($cliente['cidade'] ?? '') ?: '—') ?></td>
                      <td><span class="<?= e(ClienteController::badgeStatus((string) ($cliente['status'] ?? ''))) ?>"><?= e(($cliente['status'] ?? '') ?: '—') ?></span></td>
                      <td>
                        <div class="action-group">
                          <a href="cliente-form.php?id=<?= e($cliente['id_cliente']) ?>" class="btn-secondary">Editar</a>
                          <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-client-status="<?= ($cliente['status'] ?? '') === 'Inativo' ? 'ativar' : 'desativar' ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                            <input type="hidden" name="id_cliente" value="<?= e($cliente['id_cliente']) ?>" />
<?php if (($cliente['status'] ?? '') === 'Inativo'): ?>
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
              <div class="mobile-card-list" id="clients-mobile-list">
<?php if (!$clientes): ?>
                <article class="mobile-data-card">
                  <div class="empty-state">
                    <h3>Nenhum cliente encontrado</h3>
                    <p>Não há clientes compatíveis com os filtros atuais.</p>
                  </div>
                </article>
<?php endif; ?>
<?php foreach ($clientes as $cliente): ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-header">
                    <span class="mobile-data-card-title"><?= e(ClienteController::nomeExibicao($cliente) ?: 'Cliente') ?></span>
                    <span class="<?= e(ClienteController::badgeStatus((string) ($cliente['status'] ?? ''))) ?>"><?= e(($cliente['status'] ?? '') ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Tipo</span>
                    <span class="mobile-data-card-value"><?= e($cliente['tipo_pessoa'] ?? '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Documento</span>
                    <span class="mobile-data-card-value"><?= e(ClienteController::documentoExibicao($cliente) ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Contato</span>
                    <span class="mobile-data-card-value"><?= e(ClienteController::contatoExibicao($cliente) ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Cidade</span>
                    <span class="mobile-data-card-value"><?= e(($cliente['cidade'] ?? '') ?: '—') ?></span>
                  </div>

                  <div class="mobile-data-card-actions">
                    <a href="cliente-form.php?id=<?= e($cliente['id_cliente']) ?>" class="btn-secondary">Editar</a>
                    <form method="post" action="<?= e($acaoAtual) ?>" data-confirm-client-status="<?= ($cliente['status'] ?? '') === 'Inativo' ? 'ativar' : 'desativar' ?>">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                      <input type="hidden" name="id_cliente" value="<?= e($cliente['id_cliente']) ?>" />
<?php if (($cliente['status'] ?? '') === 'Inativo'): ?>
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
