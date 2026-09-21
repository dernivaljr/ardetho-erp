<?php
declare(strict_types=1);
require __DIR__ . '/includes/auth.php';
exigirAdministrador();
require __DIR__ . '/controllers/UsuarioController.php';
require __DIR__ . '/includes/view.php';
$controller = new UsuarioController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') $controller->salvar();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$usuario = $id ? $controller->buscar($id) : null;
if ($id && !$usuario) { http_response_code(404); exit('Usuário não encontrado.'); }
$usuario ??= ['nome' => '', 'email' => '', 'cargo' => '', 'departamento' => '', 'perfil_acesso' => 'Usuário', 'status' => 'Ativo'];
$flash = obterFlash();
$csrf = csrfToken();
$pageTitle = 'Usuário | Ardetho ERP';
$bodyPage = 'settings';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'settings';
$topbarTitle = $id ? 'Editar usuário' : 'Novo usuário';
$topbarSubtitle = 'Gestão de acesso';
$scripts = ['assets/js/layout.js'];
$isInternal = true;
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>
<section class="app-content"><div class="content-container">
  <div class="page-header"><div class="page-header-content"><h1 class="page-title"><?= e($topbarTitle) ?></h1></div><a class="btn-secondary" href="usuarios.php">Voltar</a></div>
<?php if ($flash): ?><div class="toast toast-<?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>
  <form class="page-card" method="post" action="usuario-form.php">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="id_usuario" value="<?= $id ?>">
    <div class="form-grid">
<?php foreach (['nome' => 'Nome', 'email' => 'E-mail', 'cargo' => 'Cargo', 'departamento' => 'Departamento'] as $campo => $label): ?>
      <div class="form-group"><label for="<?= e($campo) ?>"><?= e($label) ?></label><input class="input-default" id="<?= e($campo) ?>" name="<?= e($campo) ?>" type="<?= $campo === 'email' ? 'email' : 'text' ?>" value="<?= e($usuario[$campo] ?? '') ?>" maxlength="<?= $campo === 'email' ? 190 : ($campo === 'nome' ? 120 : 100) ?>"<?= in_array($campo, ['nome', 'email'], true) ? ' required' : '' ?>></div>
<?php endforeach; ?>
      <div class="form-group"><label for="perfil_acesso">Perfil</label><select class="select-default" id="perfil_acesso" name="perfil_acesso"><option value="Usuário"<?= selectedIf($usuario['perfil_acesso'], 'Usuário') ?>>Usuário</option><option value="Administrador"<?= selectedIf($usuario['perfil_acesso'], 'Administrador') ?>>Administrador</option></select></div>
      <div class="form-group"><label for="status">Status</label><select class="select-default" id="status" name="status"><option value="Ativo"<?= selectedIf($usuario['status'], 'Ativo') ?>>Ativo</option><option value="Inativo"<?= selectedIf($usuario['status'], 'Inativo') ?>>Inativo</option></select></div>
<?php if (!$id): ?>
      <div class="form-group"><label for="senha_temporaria">Senha temporária</label><input class="input-default" id="senha_temporaria" name="senha_temporaria" type="password" minlength="8" autocomplete="new-password" required></div>
<?php endif; ?>
    </div>
    <div class="client-form-actions"><button class="btn-primary" type="submit">Salvar usuário</button></div>
  </form>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
