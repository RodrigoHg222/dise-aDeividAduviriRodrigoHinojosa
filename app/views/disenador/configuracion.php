<?php require __DIR__ . '/../layouts/customer_header.php'; ?>
<div class="portal-page narrow-page">
  <?php if($flash): ?><div class="portal-alert <?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div><?php endif; ?>
  <div class="portal-section-heading"><div><span class="eyebrow">PREFERENCIAS</span><h2>Configuración de tu cuenta</h2><p>Consulta el estado de tu cuenta y administra tus datos de acceso.</p></div></div>
  <section class="settings-card"><div class="settings-icon">♙</div><div class="settings-main"><h3>Datos personales</h3><p>Nombre, correo electrónico y contraseña vinculados a tu cuenta.</p><a href="<?= h(url('perfil')) ?>">Administrar mi perfil →</a></div><span class="settings-chevron">›</span></section>
  <section class="settings-card"><div class="settings-icon">♡</div><div class="settings-main"><h3>Modelos favoritos</h3><p>Tienes <?= (int)$totalFavoritos ?> modelo(s) guardado(s) en tu lista personal.</p><a href="<?= h(url('favoritos')) ?>">Ver mis favoritos →</a></div><span class="settings-chevron">›</span></section>
  <section class="settings-card"><div class="settings-icon">◇</div><div class="settings-main"><h3>Seguimiento de solicitudes</h3><p>Consulta las respuestas y actualizaciones de las propuestas que enviaste.</p><a href="<?= h(url('mis_solicitudes')) ?>">Consultar solicitudes →</a></div><span class="settings-chevron">›</span></section>
  <section class="account-detail-card"><div><span class="eyebrow">ESTADO DE LA CUENTA</span><h3>Cuenta <?= h(strtolower($cuenta['estado'])) ?></h3><p>Registrada el <?= h(date('d/m/Y',strtotime($cuenta['creado_en']))) ?>. Tu cuenta puede acceder al espacio de diseño mientras permanezca activa.</p></div><span class="account-status">● <?= h($cuenta['estado']) ?></span></section>
  <div class="logout-panel"><div><b>¿Deseas salir de tu cuenta?</b><small>Tu información y diseños guardados permanecerán registrados.</small></div><form method="post" action="<?= h(url('logout')) ?>"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><button type="submit" class="portal-outline-button">Cerrar sesión</button></form></div>
</div>
<?php require __DIR__ . '/../layouts/customer_footer.php'; ?>
