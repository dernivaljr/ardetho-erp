<?php
$activeNav = $activeNav ?? '';

$navItems = [
    [
        'key' => 'dashboard',
        'href' => 'dashboard.php',
        'icon' => '🏠',
        'label' => 'Dashboard',
    ],
    [
        'key' => 'clients',
        'href' => 'clientes.php',
        'icon' => '👥',
        'label' => 'Clientes',
    ],
    [
        'key' => 'products',
        'href' => 'produtos.php',
        'icon' => '📦',
        'label' => 'Produtos',
    ],
    [
        'key' => 'sales',
        'href' => 'vendas.php',
        'icon' => '🛒',
        'label' => 'Vendas',
    ],
    [
        'key' => 'financial',
        'href' => 'financeiro.php',
        'icon' => '💰',
        'label' => 'Financeiro',
    ],
    [
        'key' => 'reports',
        'href' => 'relatorios.php',
        'icon' => '📊',
        'label' => 'Relatórios',
    ],
    [
        'key' => 'hr',
        'href' => 'rh.php',
        'icon' => '🧑‍💼',
        'label' => 'RH',
    ],
    [
        'key' => 'modules',
        'href' => 'erp-modules.html',
        'icon' => '🧩',
        'label' => 'Módulos',
    ],
    [
        'key' => 'settings',
        'href' => 'configuracoes.php',
        'icon' => '⚙️',
        'label' => 'Configurações',
    ],
    [
        'key' => 'profile',
        'href' => 'perfil.php',
        'icon' => '👤',
        'label' => 'Perfil',
    ],
];
?>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <a href="dashboard.php" class="brand brand-link">
          <img src="assets/images/ardetho-logo.png" alt="Ardetho ERP" class="brand-logo brand-logo-full" />
          <img src="assets/images/ardetho-icon.png" alt="Ardetho ERP" class="brand-logo brand-logo-icon" />
        </a>
      </div>

      <nav class="sidebar-nav">
<?php foreach ($navItems as $item): ?>
        <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $activeNav === $item['key'] ? ' class="active"' : '' ?>>
          <span class="nav-icon"><?= $item['icon'] ?></span>
          <span class="nav-label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>

<?php endforeach; ?>
      </nav>
    </aside>
