<?php
$topbarTitle = $topbarTitle ?? '';
$topbarSubtitle = $topbarSubtitle ?? '';
$usuarioTopbar = function_exists('usuarioAtual') ? usuarioAtual() : null;
$nomeUsuarioTopbar = $usuarioTopbar['nome'] ?? 'Usuario Ardetho';
$emailUsuarioTopbar = $usuarioTopbar['email'] ?? '';
$partesNomeTopbar = preg_split('/\s+/', trim($nomeUsuarioTopbar)) ?: [];
$iniciaisUsuarioTopbar = 'UA';

if (count($partesNomeTopbar) === 1 && $partesNomeTopbar[0] !== '') {
    $iniciaisUsuarioTopbar = strtoupper(substr($partesNomeTopbar[0], 0, 2));
} elseif (count($partesNomeTopbar) > 1) {
    $iniciaisUsuarioTopbar = strtoupper(
        substr($partesNomeTopbar[0], 0, 1) . substr($partesNomeTopbar[1], 0, 1)
    );
}
?>
    <div class="sidebar-overlay" data-action="close-mobile-menu"></div>
    <main class="app-main">
      <header class="topbar">
        <button class="mobile-menu-toggle" data-action="toggle-mobile-menu" aria-label="Abrir menu">
          ☰
        </button>
        <div class="topbar-left">
          <div class="topbar-title-group">
            <span class="topbar-title"><?= htmlspecialchars($topbarTitle, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="topbar-subtitle"><?= htmlspecialchars($topbarSubtitle, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </div>

        <div class="topbar-right">
          <div class="user-chip" data-action="go-profile" role="button" tabindex="0">
            <div class="user-avatar" data-user="avatar"><?= htmlspecialchars($iniciaisUsuarioTopbar, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="user-meta">
              <span class="user-name" data-user="name"><?= htmlspecialchars($nomeUsuarioTopbar, ENT_QUOTES, 'UTF-8') ?></span>
              <span class="user-role" data-user="role"><?= htmlspecialchars($emailUsuarioTopbar, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <a href="logout.php" class="user-logout" data-action="logout">Sair</a>
          </div>
        </div>
      </header>
