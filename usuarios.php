<?php
declare(strict_types=1);
require __DIR__ . '/includes/auth.php';
exigirAdministrador();
require __DIR__ . '/controllers/UsuarioController.php';
require __DIR__ . '/includes/view.php';
$controller = new UsuarioController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') $controller->acao();
$usuarios = $controller->listar();
$flash = obterFlash();
$csrf = csrfToken();
$pageTitle = 'Usuários | Ardetho ERP';
$bodyPage = 'settings';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'settings';
$topbarTitle = 'Usuários';
$topbarSubtitle = 'Gestão de acesso';
$scripts = ['assets/js/layout.js'];
$isInternal = true;
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>
<section class="app-content"><div class="content-container">
  <div class="page-header"><div class="page-header-content"><h1 class="page-title">Usuários</h1></div>
    <a class="btn-primary" href="usuario-form.php">Novo usuário</a></div>
<?php if ($flash): ?><div class="toast toast-<?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>
  <section class="page-card"><div class="option-list">
<?php foreach ($usuarios as $usuario): ?>
    <div class="option-item user-management-row">
      <div class="option-content">
        <span class="option-title"><?= e($usuario['nome']) ?></span>
        <span class="option-description"><?= e($usuario['email']) ?> · <?= e($usuario['cargo'] ?: '-') ?> · <?= e($usuario['departamento'] ?: '-') ?> · <?= e($usuario['perfil_acesso']) ?> · <?= e($usuario['status']) ?></span>
      </div>
      <a class="btn-secondary" href="usuario-form.php?id=<?= (int) $usuario['id_usuario'] ?>">Editar</a>
      <form method="post" action="usuarios.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
        <button class="btn-secondary" type="submit" name="acao" value="status"><?= $usuario['status'] === 'Ativo' ? 'Desativar' : 'Ativar' ?></button>
      </form>
      <form method="post" action="usuarios.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
        <label>Nova senha temporária <input class="input-default" type="password" name="senha_temporaria" minlength="8" autocomplete="new-password" required></label>
        <button class="btn-secondary" type="submit" name="acao" value="reset">Redefinir senha</button>
      </form>
    </div>
<?php endforeach; ?>
  </div></section>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
