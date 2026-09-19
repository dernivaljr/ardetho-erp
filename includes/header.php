<?php
$pageTitle = $pageTitle ?? 'Ardetho ERP';
$bodyPage = $bodyPage ?? '';
$bodyClass = $bodyClass ?? '';
$stylesheets = $stylesheets ?? [];
$includeFavicon = $includeFavicon ?? true;

$bodyAttributes = [];

if ($bodyPage !== '') {
    $bodyAttributes[] = 'data-page="' . htmlspecialchars($bodyPage, ENT_QUOTES, 'UTF-8') . '"';
}

if ($bodyClass !== '') {
    $bodyAttributes[] = 'class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="manifest" href="manifest.json" />
  <meta name="theme-color" content="#0F172A" />
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/variables.css" />
  <link rel="stylesheet" href="assets/css/global.css" />
  <link rel="stylesheet" href="assets/css/layout.css" />
  <link rel="stylesheet" href="assets/css/components.css" />
<?php foreach ($stylesheets as $stylesheet): ?>
  <link rel="stylesheet" href="<?= htmlspecialchars($stylesheet, ENT_QUOTES, 'UTF-8') ?>" />
<?php endforeach; ?>
  <link rel="stylesheet" href="assets/css/responsive.css" />
<?php if ($includeFavicon): ?>
  <link rel="icon" type="image/png" href="assets/images/ardetho-icon.png" />
<?php endif; ?>
</head>
<body<?= $bodyAttributes ? ' ' . implode(' ', $bodyAttributes) : '' ?>>
