<?php
// El servidor solo incluye la vista correspondiente a la opción elegida.
require_once __DIR__ . '/funciones_auxiliares.php';
$pages = [
 'inicio'=>['Inicio','Resumen general del sistema'],
 'usuarios'=>['Usuarios','Cuentas, roles y estados'],
 'categorias'=>['Categorías','Organización del catálogo'],
 'prendas'=>['Prendas','Productos y fotografías'],
 'solicitudes'=>['Solicitudes','Seguimiento de diseños y pedidos'],
 'reportes'=>['Reportes','Consultas y exportación de datos'],
 'configuracion'=>['Configuración','Ajustes generales y seguridad']
];
$section = array_key_exists($section,$pages) ? $section : 'inicio';
$inLists = in_array($section,['usuarios','categorias','prendas','solicitudes'],true);
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pages[$section][0]) ?> · <?= h($nombreSistema) ?></title>
<link href="recursos/estilos/administrador.css?v=3" rel="stylesheet"></head>
<body class="admin-body section-<?= h($section) ?>" data-section="<?= h($section) ?>">
<div class="admin-shell">
<header class="admin-topbar">
<a class="admin-brand" href="<?= h(url('admin',['seccion'=>'inicio'])) ?>"><span class="brand-hanger"><?= admIcon('hanger') ?></span><span><strong><?= $nombreMarca ?></strong><small>Crea tu estilo, hazlo realidad</small></span></a>
<div class="top-heading"><span class="top-heading-icon"><?= admIcon('users') ?></span><span><strong>Panel de administrador</strong><small>Gestiona todo el sistema</small></span></div>
<div class="top-profile"><button class="bell-icon notify-button" type="button" id="notificationButton" aria-expanded="false" aria-controls="noticeMenu" aria-label="Ver solicitudes pendientes"><?= admIcon('bell') ?><?php if($pendientes):?><span class="bell-count"><?= (int)$pendientes ?></span><?php endif; ?></button><span class="profile-avatar"><?= admIcon('user') ?></span><span class="top-profile-name"><b><?= h(user()['nombre']) ?></b><small>Administrador</small></span><span class="down-chevron">⌄</span></div>
</header>
<div class="notification-menu" id="noticeMenu" hidden><b>Solicitudes pendientes</b><p>Hay <?= (int)$pendientes ?> solicitud(es) pendiente(s) de revisión.</p><a href="<?= h(url('admin',['seccion'=>'solicitudes'])) ?>">Ver y gestionar solicitudes</a></div>
<aside class="admin-sidebar"><nav aria-label="Menú del administrador">
<?php foreach($nav as $code=>$item): ?>
<a class="admin-nav <?= $code===$section?'selected':'' ?>" href="<?= h(url('admin',['seccion'=>$code])) ?>" <?= $code===$section?'aria-current="page"':'' ?>><?= admIcon($item[0]) ?><span><?= h($item[1]) ?></span></a>
<?php endforeach; ?>
<form method="post" action="<?= h(url('logout')) ?>" class="sidebar-logout"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><button type="submit"><?= admIcon('logout') ?><span>Cerrar sesión</span></button></form>
</nav></aside>
<main class="admin-main" id="contenido-principal">
<div class="admin-toolbar"><div class="toolbar-blank"></div><div class="toolbar-date"><?= admIcon('calendar') ?><time datetime="<?= date('Y-m-d') ?>"><?= h($today) ?></time></div>
<?php if($inLists): ?><label class="toolbar-search"><?= admIcon('search') ?><input type="search" id="globalSearch" placeholder="Buscar en <?= h(strtolower($pages[$section][0])) ?>..." aria-label="Buscar en <?= h(strtolower($pages[$section][0])) ?>"></label><?php endif; ?>
</div>
<div class="section-heading"><div><span class="section-eyebrow">ADMINISTRACIÓN / <?= h(strtoupper($pages[$section][0])) ?></span><h1><?= h($pages[$section][0]) ?></h1><p><?= h($pages[$section][1]) ?></p></div><?php if($section !== 'inicio'): ?><a class="section-back" href="<?= h(url('admin',['seccion'=>'inicio'])) ?>"><?= admIcon('home') ?> Volver a inicio</a><?php endif; ?></div>
<?php if($flash): ?><div class="notice <?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div><?php endif; ?>
<?php require __DIR__ . '/secciones/'.$section.'.php'; ?>
<footer class="admin-footer">DiseñaTuPrenda · Proyecto de Programación II · Instituto Tecnológico Sacaba · <?= date('Y') ?></footer>
</main></div>
<div class="modal-overlay" id="editModal" hidden><div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTitle"><div class="modal-head"><h2 id="modalTitle">Nuevo registro</h2><button type="button" class="close-modal" data-close aria-label="Cerrar">×</button></div><form method="post" action="<?= h(url('admin_accion')) ?>" id="editForm" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="operacion" value="guardar"><input type="hidden" name="modulo" id="formModule"><input type="hidden" name="id" id="formId"><div class="modal-fields"><label>Nombre<input name="nombre" id="formName" required minlength="2" maxlength="120"></label><div id="categoryDescription"><label>Descripción<textarea name="descripcion" id="formDescription" maxlength="240" rows="2"></textarea></label></div><div id="garmentCategory" hidden><label>Imagen de la prenda (JPG, PNG, WebP · máximo 3 MB)<input type="file" name="imagen" id="formImage" accept="image/jpeg,image/png,image/webp"></label><small id="imageHelp">Opcional. La imagen se verá en el catálogo y el panel.</small><label>Categoría<select name="categoria_id" id="formCategory"><?php foreach($categorias as $cat):?><option value="<?= (int)$cat['id'] ?>"><?= h($cat['nombre']) ?></option><?php endforeach; ?></select></label></div><div id="userFields" hidden><label>Correo electrónico<input name="correo" id="formEmail" type="email" maxlength="150"></label><label>Contraseña <small>(opcional al editar)</small><input name="clave" id="formPassword" type="password" minlength="8" autocomplete="new-password"></label><label>Rol<select name="rol_id" id="formRole"><option value="2">Usuario</option><option value="1">Administrador</option></select></label></div><label>Estado<select name="estado" id="formStatus"><option>Activo</option><option>Inactivo</option></select></label></div><div class="modal-actions"><button type="button" class="secondary" data-close>Cancelar</button><button type="submit" class="primary">Guardar</button></div></form></div></div>
<div class="modal-overlay" id="requestModal" hidden><div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="requestTitle"><div class="modal-head"><h2 id="requestTitle">Revisar solicitud</h2><button data-close type="button" class="close-modal">×</button></div><div class="request-details" id="requestDetails"></div><form method="post" action="<?= h(url('admin_accion')) ?>"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="operacion" value="estado"><input type="hidden" name="modulo" value="solicitudes"><input type="hidden" name="id" id="requestId"><label>Actualizar estado<select name="estado" id="requestStatus"><?php foreach(['Pendiente','En proceso','Aprobada','Rechazada','Finalizada','Archivada'] as $estado):?><option><?= h($estado) ?></option><?php endforeach; ?></select></label><div class="modal-actions"><button type="button" class="secondary" data-close>Cancelar</button><button type="submit" class="primary">Guardar estado</button></div></form></div></div>
<div class="modal-overlay" id="newRequestModal" hidden><div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="newRequestTitle"><div class="modal-head"><h2 id="newRequestTitle">Nueva solicitud</h2><button type="button" class="close-modal" data-close aria-label="Cerrar">×</button></div><form method="post" action="<?= h(url('admin_accion')) ?>"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="operacion" value="nueva_solicitud"><label>Diseño y cliente registrados<select name="diseno_id" required><?php foreach($disenosDisponibles as $d):?><option value="<?= (int)$d['id'] ?>">#<?= (int)$d['id'] ?> · <?= h($d['nombre']) ?> · <?= h($d['usuario']) ?></option><?php endforeach; ?></select></label><label>Observaciones<textarea name="descripcion" rows="3" maxlength="500" placeholder="Indicaciones del cliente (opcional)"></textarea></label><div class="modal-actions"><button type="button" class="secondary" data-close>Cancelar</button><button type="submit" class="primary">Registrar solicitud</button></div></form></div></div>
<form method="post" action="<?= h(url('admin_accion')) ?>" id="stateForm" hidden><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="operacion" value="estado"><input type="hidden" name="modulo"><input type="hidden" name="id"><input type="hidden" name="estado"></form>
<script src="recursos/javascript/administrador.js?v=3" defer></script></body></html>
