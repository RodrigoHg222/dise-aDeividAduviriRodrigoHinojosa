<?php require __DIR__ . '/../layouts/customer_header.php'; ?>
<div class="portal-page">
  <?php if($flash): ?><div class="portal-alert <?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div><?php endif; ?>
  <section class="welcome-banner">
    <div><span class="eyebrow">TU ESPACIO CREATIVO</span><h2>¡Hola, <?= h(user()['nombre']) ?>!</h2><p>Hoy es un buen día para convertir una idea en una prenda única. Explora el catálogo y empieza a personalizar.</p><a class="portal-primary" href="<?= h(url('disenador')) ?>">✧ Diseñar una prenda <span>→</span></a></div>
    <div class="welcome-art"><img src="public/assets/img/vestido-corto.png" alt="Vestido para personalizar"><span class="welcome-spark spark-one">✦</span><span class="welcome-spark spark-two">♡</span><span class="welcome-art-note">Crea algo<br><b>único</b></span></div>
  </section>
  <section class="portal-stats" aria-label="Resumen de tu actividad">
    <article class="portal-stat"><span class="stat-icon purple">✧</span><div><small>Mis diseños</small><strong><?= (int)$totalDisenos ?></strong><a href="<?= h(url('mis_disenos')) ?>">Ver diseños →</a></div></article>
    <article class="portal-stat"><span class="stat-icon pink">◇</span><div><small>Solicitudes enviadas</small><strong><?= (int)$totalSolicitudes ?></strong><a href="<?= h(url('mis_solicitudes')) ?>">Consultar →</a></div></article>
    <article class="portal-stat"><span class="stat-icon amber">◷</span><div><small>En seguimiento</small><strong><?= (int)$pendientes ?></strong><a href="<?= h(url('mis_solicitudes')) ?>">Ver estados →</a></div></article>
    <article class="portal-stat"><span class="stat-icon green">♡</span><div><small>Favoritos</small><strong><?= (int)$totalFavoritos ?></strong><a href="<?= h(url('favoritos')) ?>">Ver modelos →</a></div></article>
  </section>
  <div class="portal-section-heading"><div><span class="eyebrow">CONTINÚA CREANDO</span><h2>Accesos rápidos</h2></div></div>
  <section class="quick-grid">
    <a class="quick-card" href="<?= h(url('disenador')) ?>"><span class="quick-icon">✧</span><div><b>Diseñar prenda</b><small>Elige un modelo y personaliza cada detalle.</small></div><span class="quick-arrow">→</span></a>
    <a class="quick-card" href="<?= h(url('mis_disenos')) ?>"><span class="quick-icon">▧</span><div><b>Mis diseños</b><small>Revisa las ideas que guardaste.</small></div><span class="quick-arrow">→</span></a>
    <a class="quick-card" href="<?= h(url('mis_solicitudes')) ?>"><span class="quick-icon">◇</span><div><b>Mis solicitudes</b><small>Consulta las actualizaciones de confección.</small></div><span class="quick-arrow">→</span></a>
  </section>
  <section class="portal-section recent-section">
    <div class="portal-section-heading"><div><span class="eyebrow">TU ACTIVIDAD</span><h2>Diseños recientes</h2></div><a class="text-link" href="<?= h(url('mis_disenos')) ?>">Ver todos →</a></div>
    <?php if(!$recientes): ?><div class="portal-empty"><span>✧</span><h3>Aún no tienes diseños guardados</h3><p>Cuando personalices una prenda y la guardes, aparecerá aquí.</p><a class="portal-primary" href="<?= h(url('disenador')) ?>">Crear mi primer diseño</a></div>
    <?php else: ?><div class="recent-grid"><?php foreach($recientes as $d): ?><article class="recent-card"><div class="recent-card-icon">👗</div><div class="recent-card-info"><b><?= h($d['nombre']) ?></b><small><?= h($d['talla']) ?> · <?= h($d['color']) ?> · <?= h($d['tela']) ?></small><span class="status-pill <?= ($d['solicitud_estado']??'')==='Aprobada'?'approved':((($d['solicitud_estado']??'')==='Rechazada')?'rejected':'neutral') ?>"><?= h($d['solicitud_estado']??'Diseño guardado') ?></span></div><small class="recent-date"><?= h(date('d/m/Y',strtotime($d['creado_en']))) ?></small></article><?php endforeach; ?></div><?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/../layouts/customer_footer.php'; ?>
