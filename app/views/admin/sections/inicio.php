<?php // Inicio: indicadores y actividad, sin tablas de mantenimiento. ?>
<section class="summary-grid" aria-label="Resumen de la administración">
<?php foreach([['usuarios','user','Usuarios registrados','+ Nuevos registros','violet'],['categorias','grid','Categorías','Catálogo de prendas','indigo'],['prendas','shirt','Prendas disponibles','Catálogo administrable','teal'],['solicitudes','file','Solicitudes de diseño','Seguimiento de pedidos','purple']] as $card): ?>
<div class="stat-card"><span class="stat-icon <?= $card[4] ?>"><?= admIcon($card[1]) ?></span><div><small><?= h($card[2]) ?></small><strong><?= number_format($counts[$card[0]],0,',','.') ?></strong><span class="stat-note"><?= h($card[3]) ?></span></div></div>
<?php endforeach; ?></section>
<section class="dashboard-middle"><article class="dash-panel chart-panel"><div class="panel-title">Actividad del sistema <span class="muted small">(últimos 7 días)</span><div class="chart-legend"><i class="purple-dot"></i> Usuarios <i class="blue-dot"></i> Solicitudes</div></div>
<svg viewBox="0 0 770 215" class="activity-chart" role="img" aria-label="Nuevos usuarios y solicitudes de los últimos siete días">
<?php for($y=32;$y<=164;$y+=33):?><line x1="35" y1="<?= $y ?>" x2="737" y2="<?= $y ?>" stroke="#1d2c4c" stroke-width="1"/><?php endfor; ?>
<?php foreach($days as $i=>$day):?><line x1="<?= 35+$i*116 ?>" y1="31" x2="<?= 35+$i*116 ?>" y2="164" stroke="#152645"/><text x="<?= 35+$i*116 ?>" y="194" text-anchor="middle" fill="#b8c4e1" font-size="13"><?= date('d/m',strtotime($day['fecha'])) ?></text><?php endforeach; ?>
<polyline points="<?= h($chartPoints('usuarios')) ?>" fill="none" stroke="#943dff" stroke-width="3"/>
<polyline points="<?= h($chartPoints('solicitudes')) ?>" fill="none" stroke="#288dff" stroke-width="3"/>
<?php foreach($days as $i=>$day):$x=35+$i*116;foreach(['usuarios'=>'#943dff','solicitudes'=>'#288dff'] as $key=>$color):$y=164-($day[$key]/$max)*132;?><circle cx="<?= $x ?>" cy="<?= $y ?>" r="4.3" fill="<?= $color ?>"><title><?= h($day['fecha'].': '.$key.' '.$day[$key]) ?></title></circle><?php endforeach;endforeach; ?>
</svg></article>
<article class="dash-panel popular-panel"><div class="panel-title">Prendas más usadas</div><div class="popular-grid">
<?php if(!$stats['most']):?><div class="empty-popular"><div class="dummy-dress">♧</div>Las prendas aparecerán aquí cuando reciban solicitudes.</div><?php else:foreach($stats['most'] as $i=>$p):?><div class="popular-item"><div class="popular-image"><?php if(garmentImage($p,$images)):?><img src="<?= h(garmentImage($p,$images)) ?>" alt="<?= h($p['nombre']) ?>"><?php else:?><?= admIcon('shirt') ?><?php endif; ?></div><b><?= h($p['nombre']) ?></b><small><?= (int)$p['cantidad'] ?> solicitudes</small></div><?php endforeach;endif; ?>
</div></article>
<article class="dash-panel activity-panel"><div class="panel-title">Actividad reciente</div><div class="activity-list">
<?php if(!$stats['recent']):?><p class="muted">Todavía no hay actividad registrada.</p><?php else:foreach($stats['recent'] as $act):?><div class="activity-item"><span class="activity-icon"><?= admIcon($act['tipo']==='prenda'?'shirt':($act['tipo']==='usuario'?'user':'file')) ?></span><div><b><?= h($act['tipo']==='usuario'?'Usuario registrado':($act['tipo']==='prenda'?'Prenda registrada':'Solicitud recibida')) ?></b><span><?= h($act['titulo']) ?></span><small><?= h(date('d/m/Y · H:i',strtotime($act['creado_en']))) ?></small></div></div><?php endforeach;endif; ?></div></article></section>

<section class="dashboard-cta dash-panel"><div><strong>Gestiona tu catálogo de prendas y solicitudes</strong><p>Accede a cada módulo desde el menú lateral. Las estadísticas corresponden a los registros de MySQL.</p></div><div class="cta-links"><a href="<?= h(url('admin',['seccion'=>'prendas'])) ?>">Ver prendas</a><a href="<?= h(url('admin',['seccion'=>'solicitudes'])) ?>">Ver solicitudes</a></div></section>
