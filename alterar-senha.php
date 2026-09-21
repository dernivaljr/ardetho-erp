<?php
declare(strict_types=1);
require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/UsuarioController.php';
require __DIR__ . '/includes/view.php';
$controller = new UsuarioController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') $controller->alterarPropriaSenha();
$flash = obterFlash();
$csrf = csrfToken();
$pageTitle = 'Alterar senha | Ardetho ERP';
$bodyPage = 'profile';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'profile';
$topbarTitle = 'Alterar senha';
$topbarSubtitle = 'Segurança da conta';
$scripts = ['assets/js/layout.js'];
$isInternal = true;
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>
<section class="app-content"><div class="content-container">
  <div class="page-header"><div class="page-header-content"><h1 class="page-title">Alterar senha</h1></div><?php if ((int) (usuarioAtual()['trocar_senha'] ?? 0) === 0): ?><a class="btn-secondary" href="dashboard.php">Dashboard</a><?php endif; ?></div>
<?php if ((int) (usuarioAtual()['trocar_senha'] ?? 0) === 1): ?><div class="toast toast-warning" role="status">Troque a senha temporária para continuar.</div><?php endif; ?>
<?php if ($flash): ?><div class="toast toast-<?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>
  <form class="page-card" method="post" action="alterar-senha.php">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <div class="form-grid">
      <div class="form-group"><label for="senha_atual">Senha atual</label><input class="input-default" id="senha_atual" name="senha_atual" type="password" autocomplete="current-password" required></div>
      <div class="form-group"><label for="nova_senha">Nova senha</label><input class="input-default" id="nova_senha" name="nova_senha" type="password" minlength="8" autocomplete="new-password" required></div>
      <div class="form-group"><label for="confirmar_senha">Confirmar nova senha</label><input class="input-default" id="confirmar_senha" name="confirmar_senha" type="password" minlength="8" autocomplete="new-password" required></div>
    </div>
    <div class="client-form-actions"><button class="btn-primary" type="submit">Salvar senha</button></div>
  </form>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
