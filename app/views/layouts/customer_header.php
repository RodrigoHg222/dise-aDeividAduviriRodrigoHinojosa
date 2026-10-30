<?php
$pageTitle = $pageTitle ?? 'Inicio';
$pageSubtitle = $pageSubtitle ?? 'Tu espacio para crear y personalizar prendas';
$activePage = $activePage ?? 'cliente';
$menuItems = [
    'cliente' => ['⌂', 'Inicio', 'cliente'],
    'disenador' => ['✧', 'Diseñar prenda', 'disenador'],
    'mis_disenos' => ['▧', 'Mis diseños', 'mis_disenos'],
    'mis_solicitudes' => ['◇', 'Mis solicitudes', 'mis_solicitudes'],
    'favoritos' => ['♡', 'Favoritos', 'favoritos'],
    'perfil' => ['♙', 'Mi perfil', 'perfil'],
    'configuracion_usuario' => ['⚙', 'Configuración', 'configuracion_usuario'],
];
$initial = function_exists('mb_substr') ? mb_strtoupper(mb_substr((string)user()['nombre'], 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr((string)user()['nombre'], 0, 1));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($pageTitle) ?> · DiseñaTuPrenda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assets/css/app.css?v=4">
  <link rel="stylesheet" href="public/assets/css/portal.css?v=4">
</head>
<body class="customer-body">
<div class="app-shell customer-shell">
  <header class="topbar">
    <a class="brand" href="<?= h(url('cliente')) ?>"><span class="brand-icon">♧</span><span><b>Diseña<span>TuPrenda</span></b><small>Crea tu estilo, hazlo realidad</small></span></a>
    <div class="page-heading"><span class="heading-dress">✧</span><div><h1><?= h($pageTitle) ?></h1><p><?= h($pageSubtitle) ?></p></div></div>
    <div class="top-actions"><a class="icon-button notification" href="<?= h(url('mis_solicitudes')) ?>" aria-label="Consultar mis solicitudes">◇</a><a class="avatar" href="<?= h(url('perfil')) ?>" aria-label="Mi perfil"><?= h($initial) ?></a><a class="user-label" href="<?= h(url('perfil')) ?>"><b><?= h(user()['nombre']) ?></b><small>Cliente</small></a><a class="chevron" href="<?= h(url('configuracion_usuario')) ?>" aria-label="Configuración">⌄</a></div>
  </header>
  <aside class="sidebar customer-sidebar">
    <div class="sidebar-caption">MENÚ PRINCIPAL</div>
    <nav class="side-nav" aria-label="Menú del cliente">
      <?php foreach ($menuItems as $code => $item): ?>
        <a class="<?= $activePage === $code ? 'active' : '' ?>" href="<?= h(url($item[2])) ?>" <?= $activePage === $code ? 'aria-current="page"' : '' ?>><span><?= h($item[0]) ?></span><?= h($item[1]) ?></a>
      <?php endforeach; ?>
      <form action="<?= h(url('logout')) ?>" method="post" class="logout-side"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><button type="submit"><span>⇥</span>Cerrar sesión</button></form>
    </nav>
    <div class="promo"><div class="promo-photo"></div><div class="promo-copy">Moda hecha<br>a tu medida <span>♡</span></div><div class="promo-location"><span>♧</span><div><b>Sacaba</b><small>Cuna de emprendedores</small></div></div></div>
  </aside>
  <main class="workspace customer-workspace">
