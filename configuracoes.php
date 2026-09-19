<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/ConfiguracaoController.php';
require __DIR__ . '/includes/view.php';

$controller = new ConfiguracaoController();
$viewData = $controller->index();
$configuracoes = $viewData['configuracoes'];
$flash = $viewData['flash'];
$csrf = $viewData['csrf'];

$pageTitle = 'Configurações | Ardetho ERP';
$bodyPage = 'settings';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'settings';
$topbarTitle = 'Configurações';
$topbarSubtitle = 'Preferências gerais e personalização do ambiente';
$scripts = [
    'assets/js/layout.js',
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
              <h1 class="page-title">Configurações do sistema</h1>
              <p class="page-description">
                Defina preferências gerais, comportamento visual e parâmetros do ambiente.
              </p>
            </div>
          </div>

<?php if ($flash): ?>
          <div class="toast toast-<?= e($flash['tipo'] ?? 'info') ?>" role="status">
            <?= e($flash['mensagem'] ?? '') ?>
          </div>
<?php endif; ?>

          <form class="content-stack" method="post" action="configuracoes.php">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />

            <section class="split-grid">
              <article class="page-card">
                <div class="page-card-header">
                  <div>
                    <h2 class="page-card-title">Preferências visuais</h2>
                    <p class="page-card-description">Defina a experiência de navegação e exibição.</p>
                  </div>
                </div>

                <div class="option-list">
                  <div class="form-group">
                    <label for="theme-mode">Tema do sistema</label>
                    <select id="theme-mode" name="theme_mode" class="select-default">
<?php foreach (ConfiguracaoController::opcoes('theme_mode') as $opcao): ?>
                      <option value="<?= e($opcao) ?>"<?= selectedIf($configuracoes['theme_mode'] ?? '', $opcao) ?>><?= e($opcao === 'dark' ? 'Escuro' : 'Claro') ?></option>
<?php endforeach; ?>
                    </select>
                  </div>

<?php foreach ([
    'sidebar_compact' => ['Sidebar compacta', 'Reduzir largura da navegação lateral para mais espaço útil.'],
    'dashboard_shortcuts' => ['Exibir atalhos no dashboard', 'Mostrar ações rápidas na visão inicial do sistema.'],
] as $campo => [$titulo, $descricao]): ?>
                  <label class="option-item">
                    <div class="option-content">
                      <span class="option-title"><?= e($titulo) ?></span>
                      <span class="option-description"><?= e($descricao) ?></span>
                    </div>
                    <input type="checkbox" name="<?= e($campo) ?>" value="1"<?= checkedIf(ConfiguracaoController::ativo($configuracoes, $campo)) ?> />
                  </label>
<?php endforeach; ?>
                </div>
              </article>

              <article class="page-card">
                <div class="page-card-header">
                  <div>
                    <h2 class="page-card-title">Notificações</h2>
                    <p class="page-card-description">Controle alertas e sinais de acompanhamento.</p>
                  </div>
                </div>

                <div class="option-list">
<?php foreach ([
    'alerts_expiration' => ['Alertas de vencimento', 'Notificar contas com prazo próximo de vencimento.'],
    'alerts_orders' => ['Pedidos pendentes', 'Exibir avisos para pedidos aguardando andamento.'],
    'daily_summary' => ['Resumo diário', 'Apresentar visão resumida no início do expediente.'],
] as $campo => [$titulo, $descricao]): ?>
                  <label class="option-item">
                    <div class="option-content">
                      <span class="option-title"><?= e($titulo) ?></span>
                      <span class="option-description"><?= e($descricao) ?></span>
                    </div>
                    <input type="checkbox" name="<?= e($campo) ?>" value="1"<?= checkedIf(ConfiguracaoController::ativo($configuracoes, $campo)) ?> />
                  </label>
<?php endforeach; ?>
                </div>
              </article>
            </section>

            <section class="split-grid">
              <article class="page-card">
                <div class="page-card-header">
                  <div>
                    <h2 class="page-card-title">Parâmetros operacionais</h2>
                    <p class="page-card-description">Ajustes gerais de funcionamento do sistema.</p>
                  </div>
                </div>

                <div class="form-layout">
<?php foreach ([
    'language' => 'Idioma do sistema',
    'date_format' => 'Formato de data',
    'timezone' => 'Fuso horário',
] as $campo => $label): ?>
                  <div class="form-group">
                    <label for="<?= e($campo) ?>"><?= e($label) ?></label>
                    <select id="<?= e($campo) ?>" name="<?= e($campo) ?>" class="select-default">
<?php foreach (ConfiguracaoController::opcoes($campo) as $opcao): ?>
                      <option value="<?= e($opcao) ?>"<?= selectedIf($configuracoes[$campo] ?? '', $opcao) ?>><?= e($opcao) ?></option>
<?php endforeach; ?>
                    </select>
                  </div>
<?php endforeach; ?>
                </div>
              </article>

              <article class="page-card">
                <div class="page-card-header">
                  <div>
                    <h2 class="page-card-title">Módulos prioritários</h2>
                    <p class="page-card-description">Defina quais áreas terão mais destaque no ambiente.</p>
                  </div>
                </div>

                <div class="form-layout">
<?php foreach ([
    'main_module' => 'Módulo inicial',
    'priority_module' => 'Módulo prioritário',
    'startup_view' => 'Visão ao iniciar',
] as $campo => $label): ?>
                  <div class="form-group">
                    <label for="<?= e($campo) ?>"><?= e($label) ?></label>
                    <select id="<?= e($campo) ?>" name="<?= e($campo) ?>" class="select-default">
<?php foreach (ConfiguracaoController::opcoes($campo) as $opcao): ?>
                      <option value="<?= e($opcao) ?>"<?= selectedIf($configuracoes[$campo] ?? '', $opcao) ?>><?= e($opcao) ?></option>
<?php endforeach; ?>
                    </select>
                  </div>
<?php endforeach; ?>
                </div>
              </article>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Identidade da empresa</h2>
                  <p class="page-card-description">Dados visuais e institucionais exibidos no sistema.</p>
                </div>
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label for="company-name">Nome da empresa</label>
                  <input id="company-name" name="company_name" class="input-default" type="text" value="<?= e($configuracoes['company_name'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="company-display-name">Nome exibido no sistema</label>
                  <input id="company-display-name" name="company_display_name" class="input-default" type="text" value="<?= e($configuracoes['company_display_name'] ?? '') ?>" />
                </div>

                <div class="form-group form-group-full">
                  <label for="company-logo-url">Logo da empresa (URL ou caminho local)</label>
                  <input id="company-logo-url" name="company_logo_url" class="input-default" type="text" value="<?= e($configuracoes['company_logo_url'] ?? '') ?>" />
                </div>

                <div class="form-group form-group-full">
                  <label for="company-icon-url">Favicon / ícone da empresa</label>
                  <input id="company-icon-url" name="company_icon_url" class="input-default" type="text" value="<?= e($configuracoes['company_icon_url'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="brand-primary-color">Cor primária</label>
                  <input id="brand-primary-color" name="brand_primary_color" class="input-default" type="text" value="<?= e($configuracoes['brand_primary_color'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="brand-accent-color">Cor de destaque</label>
                  <input id="brand-accent-color" name="brand_accent_color" class="input-default" type="text" value="<?= e($configuracoes['brand_accent_color'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Resumo da configuração atual</h2>
                  <p class="page-card-description">Visão rápida do ambiente configurado.</p>
                </div>
              </div>

              <div class="option-list">
                <div class="option-item">
                  <div class="option-content">
                    <span class="option-title">Tema visual</span>
                    <span class="option-description"><?= e(($configuracoes['theme_mode'] ?? 'light') === 'dark' ? 'Tema escuro configurado para o ambiente.' : 'Interface clara com foco em produtividade e leitura.') ?></span>
                  </div>
                  <span class="<?= ($configuracoes['theme_mode'] ?? 'light') === 'dark' ? 'badge-warning' : 'badge-success' ?>"><?= e(($configuracoes['theme_mode'] ?? 'light') === 'dark' ? 'Escuro' : 'Claro') ?></span>
                </div>

                <div class="option-item">
                  <div class="option-content">
                    <span class="option-title">Idioma principal</span>
                    <span class="option-description"><?= e($configuracoes['language'] ?? '') ?> definido como idioma principal do sistema.</span>
                  </div>
                  <span class="badge-info">Atual</span>
                </div>

                <div class="option-item">
                  <div class="option-content">
                    <span class="option-title">Módulo prioritário</span>
                    <span class="option-description"><?= e($configuracoes['priority_module'] ?? '') ?> configurado como foco principal do ambiente.</span>
                  </div>
                  <span class="badge-warning">Config.</span>
                </div>
              </div>
            </section>

            <div class="client-form-actions">
              <button type="submit" name="acao" value="resetar" class="btn-secondary">Restaurar padrão</button>
              <button type="submit" name="acao" value="salvar" class="btn-primary">Salvar alterações</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
