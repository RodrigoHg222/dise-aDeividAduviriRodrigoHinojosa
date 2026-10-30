<?php require __DIR__ . '/../layouts/customer_header.php'; ?>
<div class="portal-page">
  <?php if($flash): ?><div class="portal-alert <?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div><?php endif; ?>
  <div class="portal-section-heading"><div><span class="eyebrow">TU COLECCIÓN PERSONAL</span><h2>Diseños guardados</h2><p>Aquí puedes consultar las prendas que personalizaste y guardaste.</p></div><a class="portal-primary" href="<?= h(url('disenador')) ?>">＋ Nuevo diseño</a></div>
  <?php if(!$disenos): ?><div class="portal-empty"><span>▧</span><h3>Tu colección está vacía</h3><p>Empieza seleccionando un modelo del catálogo. Podrás guardar tu propuesta y volver a verla cuando quieras.</p><a class="portal-primary" href="<?= h(url('disenador')) ?>">Explorar modelos</a></div>
  <?php else: ?><div class="design-history-grid">
    <?php foreach($disenos as $d): $estado=$d['solicitud_estado']??'Solo guardado'; $pill=in_array($estado,['Aprobada','Finalizada'],true)?'approved':($estado==='Rechazada'?'rejected':($estado==='Pendiente'||$estado==='En proceso'?'pending':'neutral')); ?>
      <article class="design-history-card"><div class="design-history-art"><span>👗</span><small>DISEÑO #<?= (int)$d['id'] ?></small></div><div class="design-history-content"><div class="history-title-row"><h3><?= h($d['nombre']) ?></h3><span class="status-pill <?= $pill ?>"><?= h($estado) ?></span></div><p><?= h($d['talla']) ?> <i>·</i> <?= h($d['color']) ?> <i>·</i> <?= h($d['tela']) ?></p><?php if($d['escote']||$d['manga']||$d['largo']): ?><small><?= h(implode(' · ',array_filter([$d['escote'],$d['manga'],$d['largo']]))) ?></small><?php endif; ?><?php if(!empty($d['texto_personalizado'])): ?><small>Texto: “<?= h($d['texto_personalizado']) ?>” (<?= h($d['ubicacion_texto']) ?>)</small><?php endif; ?><div class="history-card-footer"><small>Guardado el <?= h(date('d/m/Y',strtotime($d['creado_en']))) ?></small><?php if($d['imagen_referencia']): ?><a href="<?= h($d['imagen_referencia']) ?>" target="_blank" rel="noopener">Ver referencia ↗</a><?php endif; ?></div></div></article>
    <?php endforeach; ?>
  </div><?php endif; ?>
</div>
<?php require __DIR__ . '/../layouts/customer_footer.php'; ?>
