<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/PerfilController.php';
require __DIR__ . '/includes/view.php';

$controller = new PerfilController();
$viewData = $controller->index();
$usuario = $viewData['usuario'];
$flash = $viewData['flash'];
$csrf = $viewData['csrf'];
$iniciais = PerfilController::iniciais((string) ($usuario['nome'] ?? ''));

$pageTitle = 'Perfil | Ardetho ERP';
$bodyPage = 'profile';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'profile';
$topbarTitle = 'Perfil';
$topbarSubtitle = 'Dados do usuário autenticado';
$scripts = [
    'assets/js/layout.js',
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
              <h1 class="page-title">Perfil</h1>
              <p class="page-description">
                Mantenha os dados do usuário atualizados no ambiente PHP principal.
              </p>
            </div>
          </div>

<?php if ($flash): ?>
          <div class="toast toast-<?= e($flash['tipo'] ?? 'info') ?>" role="status">
            <?= e($flash['mensagem'] ?? '') ?>
          </div>
<?php endif; ?>

          <div class="content-stack">
            <section class="profile-layout">
              <article class="profile-card">
                <div class="profile-avatar-large" id="profile-avatar-large"><?= e($iniciais) ?></div>

                <div class="profile-info-list">
                  <div class="profile-info-item">
                    <span class="profile-info-label">Usuário</span>
                    <span class="profile-info-value" id="profile-name"><?= e($usuario['nome'] ?? '') ?></span>
                  </div>

                  <div class="profile-info-item">
                    <span class="profile-info-label">E-mail</span>
                    <span class="profile-info-value" id="profile-email"><?= e($usuario['email'] ?? '') ?></span>
                  </div>

                  <div class="profile-info-item">
                    <span class="profile-info-label">Cargo</span>
                    <span class="profile-info-value" id="profile-role"><?= e(($usuario['cargo'] ?? '') ?: '-') ?></span>
                  </div>

                  <div class="profile-info-item">
                    <span class="profile-info-label">Departamento</span>
                    <span class="profile-info-value"><?= e(($usuario['departamento'] ?? '') ?: '-') ?></span>
                  </div>
                </div>
              </article>

              <form class="content-stack" method="post" action="perfil.php">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />

                <article class="page-card">
                  <div class="page-card-header">
                    <div>
                      <h2 class="page-card-title">Dados do usuário</h2>
                      <p class="page-card-description">Informações do usuário autenticado no ambiente.</p>
                    </div>
                  </div>

                  <div class="form-grid">
                    <div class="form-group">
                      <label for="profile-full-name">Nome completo</label>
                      <input id="profile-full-name" name="nome" class="input-default" type="text" value="<?= e($usuario['nome'] ?? '') ?>" />
                    </div>

                    <div class="form-group">
                      <label for="profile-email-input">E-mail</label>
                      <input id="profile-email-input" name="email" class="input-default" type="email" value="<?= e($usuario['email'] ?? '') ?>" />
                    </div>

                    <div class="form-group">
                      <label for="profile-role-input">Cargo</label>
                      <input id="profile-role-input" name="cargo" class="input-default" type="text" value="<?= e($usuario['cargo'] ?? '') ?>" />
                    </div>

                    <div class="form-group">
                      <label for="profile-department-input">Departamento</label>
                      <input id="profile-department-input" name="departamento" class="input-default" type="text" value="<?= e($usuario['departamento'] ?? '') ?>" />
                    </div>
                  </div>
                </article>

                <article class="page-card">
                  <div class="option-list">
                    <div class="option-item">
                      <div class="option-content">
                        <span class="option-title">Identidade visual da empresa</span>
                        <span class="option-description">Nome exibido, logo, ícone e cores foram migrados para Configurações.</span>
                      </div>
                      <a href="configuracoes.php" class="btn-secondary">Abrir</a>
                    </div>
                  </div>
                </article>

                <div class="client-form-actions">
                  <button type="submit" class="btn-primary">Salvar alterações</button>
                </div>
              </form>
            </section>
          </div>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
