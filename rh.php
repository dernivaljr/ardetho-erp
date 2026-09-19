<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/FuncionarioController.php';
require __DIR__ . '/includes/view.php';

$controller = new FuncionarioController();
$viewData = $controller->listar();
$funcionarios = $viewData['funcionarios'];
$departamentos = $viewData['departamentos'];
$resumo = $viewData['resumo'];
$filtros = $viewData['filtros'];
$flash = $viewData['flash'];
$csrf = $viewData['csrf'];
$queryAtual = http_build_query(array_filter([
    'busca' => $filtros['busca'],
    'status' => $filtros['status'],
    'departamento' => $filtros['departamento'],
], static fn ($valor) => $valor !== ''));
$acaoAtual = 'rh.php' . ($queryAtual !== '' ? '?' . $queryAtual : '');

$pageTitle = 'RH | Ardetho ERP';
$bodyPage = 'hr';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'hr';
$topbarTitle = 'RH';
$topbarSubtitle = 'Gestão de colaboradores e estrutura interna';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
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
              <h1 class="page-title">Colaboradores</h1>
              <p class="page-description">
                Cadastre, acompanhe e organize os colaboradores da empresa por cargo, departamento e status.
              </p>
            </div>

            <div class="page-header-actions">
              <a href="funcionario-form.php" class="btn-primary">Novo colaborador</a>
            </div>
          </div>

<?php if ($flash): ?>
          <div class="toast toast-<?= e($flash['tipo'] ?? 'info') ?>" role="status">
            <?= e($flash['mensagem'] ?? '') ?>
          </div>
<?php endif; ?>

          <div class="content-stack">
            <section class="metrics-grid" id="hr-metrics-section">
              <article class="card metric-card">
                <span class="metric-label">Colaboradores ativos</span>
                <strong class="metric-value" id="hr-metric-active"><?= e($resumo['ativos'] ?? 0) ?></strong>
                <span class="metric-change" id="hr-metric-active-text"><?= e($resumo['ativos'] ?? 0) ?> colaborador(es) em atividade.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Em férias</span>
                <strong class="metric-value" id="hr-metric-vacation"><?= e($resumo['ferias'] ?? 0) ?></strong>
                <span class="metric-change" id="hr-metric-vacation-text"><?= e($resumo['ferias'] ?? 0) ?> colaborador(es) em período de férias.</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Afastados</span>
                <strong class="metric-value" id="hr-metric-away"><?= e($resumo['afastados'] ?? 0) ?></strong>
                <span class="metric-change" id="hr-metric-away-text"><?= e($resumo['afastados'] ?? 0) ?> colaborador(es) afastado(s).</span>
              </article>

              <article class="card metric-card">
                <span class="metric-label">Folha estimada</span>
                <strong class="metric-value" id="hr-metric-payroll"><?= e(FuncionarioController::valorExibicao($resumo['folha_estimada'] ?? 0)) ?></strong>
                <span class="metric-change" id="hr-metric-payroll-text">Soma salarial dos colaboradores ativos.</span>
              </article>
            </section>

            <section class="section-block">
              <form class="toolbar-row" method="get" action="rh.php">
                <div class="toolbar-left">
                  <div class="search-bar">
                    <input
                      id="hr-search"
                      name="busca"
                      type="text"
                      class="input-default"
                      placeholder="Buscar colaborador..."
                      value="<?= e($filtros['busca']) ?>"
                    />
                  </div>
                </div>

                <div class="toolbar-right">
                  <div class="filter-group">
                    <select id="hr-status-filter" name="status" class="select-default">
                      <option value="">Todos os status</option>
<?php foreach (FuncionarioController::status() as $status): ?>
                      <option value="<?= e(strtolower($status)) ?>"<?= selectedIf($filtros['status'], $status) ?>><?= e($status) ?></option>
<?php endforeach; ?>
                    </select>

                    <select id="hr-department-filter" name="departamento" class="select-default">
                      <option value="">Todos os departamentos</option>
<?php foreach ($departamentos as $departamento): ?>
                      <option value="<?= e($departamento) ?>"<?= selectedIf($filtros['departamento'], $departamento) ?>><?= e($departamento) ?></option>
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
                      <th>Nome</th>
                      <th>Cargo</th>
                      <th>Departamento</th>
                      <th>Salário</th>
                      <th>Admissão</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="hr-table-body">
<?php if (!$funcionarios): ?>
                    <tr>
                      <td colspan="7">
                        <div class="empty-state">
                          <h3>Nenhum colaborador encontrado</h3>
                          <p>Não há colaboradores compatíveis com os filtros atuais.</p>
                        </div>
                      </td>
                    </tr>
<?php endif; ?>
<?php foreach ($funcionarios as $funcionario): ?>
                    <tr>
                      <td>
                        <strong><?= e(($funcionario['nome_completo'] ?? '') ?: '-') ?></strong><br />
                        <span style="color: var(--color-text-secondary); font-size: 13px;"><?= e(($funcionario['email'] ?? '') ?: '-') ?></span>
                      </td>
                      <td><?= e(($funcionario['cargo'] ?? '') ?: '-') ?></td>
                      <td><?= e(($funcionario['departamento'] ?? '') ?: '-') ?></td>
                      <td><?= e(FuncionarioController::valorExibicao($funcionario['salario'] ?? 0)) ?></td>
                      <td><?= e(FuncionarioController::dataExibicao($funcionario['data_admissao'] ?? null)) ?></td>
                      <td><span class="<?= e(FuncionarioController::badgeStatus((string) ($funcionario['status'] ?? ''))) ?>"><?= e(($funcionario['status'] ?? '') ?: '-') ?></span></td>
                      <td>
                        <div class="action-group">
                          <a href="funcionario-form.php?id=<?= e($funcionario['id_funcionario']) ?>" class="btn-secondary">Editar</a>
                          <form method="post" action="<?= e($acaoAtual) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                            <input type="hidden" name="id_funcionario" value="<?= e($funcionario['id_funcionario']) ?>" />
<?php if (($funcionario['status'] ?? '') === 'Desligado'): ?>
                            <input type="hidden" name="acao" value="ativar" />
                            <button type="submit" class="btn-secondary">Ativar</button>
<?php else: ?>
                            <input type="hidden" name="acao" value="desligar" />
                            <button type="submit" class="btn-danger">Desligar</button>
<?php endif; ?>
                          </form>
                        </div>
                      </td>
                    </tr>
<?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="mobile-card-list" id="hr-mobile-list">
<?php if (!$funcionarios): ?>
                <article class="mobile-data-card">
                  <div class="empty-state">
                    <h3>Nenhum colaborador encontrado</h3>
                    <p>Não há colaboradores compatíveis com os filtros atuais.</p>
                  </div>
                </article>
<?php endif; ?>
<?php foreach ($funcionarios as $funcionario): ?>
                <article class="mobile-data-card">
                  <div class="mobile-data-card-header">
                    <span class="mobile-data-card-title"><?= e(($funcionario['nome_completo'] ?? '') ?: 'Colaborador') ?></span>
                    <span class="<?= e(FuncionarioController::badgeStatus((string) ($funcionario['status'] ?? ''))) ?>"><?= e(($funcionario['status'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">E-mail</span>
                    <span class="mobile-data-card-value"><?= e(($funcionario['email'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Cargo</span>
                    <span class="mobile-data-card-value"><?= e(($funcionario['cargo'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Departamento</span>
                    <span class="mobile-data-card-value"><?= e(($funcionario['departamento'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="mobile-data-card-row">
                    <span class="mobile-data-card-label">Salário</span>
                    <span class="mobile-data-card-value"><?= e(FuncionarioController::valorExibicao($funcionario['salario'] ?? 0)) ?></span>
                  </div>

                  <div class="mobile-data-card-actions">
                    <a href="funcionario-form.php?id=<?= e($funcionario['id_funcionario']) ?>" class="btn-secondary">Editar</a>
                    <form method="post" action="<?= e($acaoAtual) ?>">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
                      <input type="hidden" name="id_funcionario" value="<?= e($funcionario['id_funcionario']) ?>" />
<?php if (($funcionario['status'] ?? '') === 'Desligado'): ?>
                      <input type="hidden" name="acao" value="ativar" />
                      <button type="submit" class="btn-secondary">Ativar</button>
<?php else: ?>
                      <input type="hidden" name="acao" value="desligar" />
                      <button type="submit" class="btn-danger">Desligar</button>
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
