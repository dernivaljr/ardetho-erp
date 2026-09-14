<?php
$topbarTitle = $topbarTitle ?? '';
$topbarSubtitle = $topbarSubtitle ?? '';
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
            <div class="user-avatar" data-user="avatar">AD</div>
            <div class="user-meta">
              <span class="user-name" data-user="name">Admin User</span>
              <span class="user-role" data-user="role">Administrador</span>
            </div>
            <a href="#" class="user-logout" data-action="logout">Sair</a>
          </div>
        </div>
      </header>
