<?php
function admIcon(string $name): string {
    $paths=[
        'home'=>'<path d="m3 10 9-7 9 7"/><path d="M5 9v12h14V9M9 21v-7h6v7"/>',
        'users'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3a4 4 0 0 1 0 8"/>',
        'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'shirt'=>'<path d="m8 4-2 1-4 4 4 4 2-2v10h8V11l2 2 4-4-4-4-2-1c0 3-8 3-8 0Z"/>',
        'file'=>'<path d="M6 2h9l5 5v15H6z"/><path d="M15 2v6h5M9 13h8M9 17h8"/>',
        'chart'=>'<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m6 16 4-4 3 2 5-6"/>',
        'settings'=>'<circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M19 5l-2 2M7 17l-2 2"/>',
        'logout'=>'<path d="M10 3H5v18h5M14 7l5 5-5 5m5-5H8"/>',
        'bell'=>'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'search'=>'<circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/>',
        'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6m10-6v6M3 10h18"/>',
        'edit'=>'<path d="m4 20 5-.8L20 8a3 3 0 0 0-4-4L5 15Z"/><path d="m14 6 4 4"/>',
        'trash'=>'<path d="M4 7h16M10 3h4m-8 4 1 14h10l1-14M10 11v6m4-6v6"/>',
        'plus'=>'<path d="M12 5v14M5 12h14"/>',
        'check'=>'<path d="m4 12 5 5L20 6"/>',
        'download'=>'<path d="M12 3v13m-5-5 5 5 5-5M4 17v4h16v-4"/>',
        'lock'=>'<rect x="4" y="10" width="16" height="12" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'database'=>'<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 4 18 4 18 0V5M3 12c0 4 18 4 18 0"/>',
        'hanger'=>'<path d="M9 6a3 3 0 0 1 6 0c0 2-3 3-3 5L2 18a2 2 0 0 0 2 3h16a2 2 0 0 0 2-3l-10-7"/>',
    ];
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['grid']).'</svg>';
}
function stateBadge(string $estado): string {
    $c=match($estado){'Activo','Aprobada','Finalizada'=>'green','Pendiente'=>'yellow','En proceso'=>'blue',default=>'red'};
    return '<span class="state-pill '.$c.'">'.h($estado).'</span>';
}
function makeEdit(string $modulo,array $r): string {
    $json=h(json_encode($r,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT));
    return '<button class="icon-action" data-edit="'.h($modulo).'" data-record="'.$json.'" aria-label="Editar">'.admIcon('edit').'</button>';
}
function stateButton(string $modulo,array $r): string {
    $estado=$r['estado'];$next= $modulo==='usuarios' ? ($estado==='Activo'?'Bloqueado':'Activo') : ($estado==='Activo'?'Inactivo':'Activo');
    return '<button class="icon-action danger" data-state="'.h($modulo).'" data-id="'.(int)$r['id'].'" data-next="'.h($next).'" aria-label="Cambiar estado" title="Cambiar a '.h($next).'">'.admIcon($next==='Activo'?'check':'trash').'</button>';
}
$nav=['inicio'=>['home','Inicio'],'usuarios'=>['users','Usuarios'],'categorias'=>['grid','Categorías'],'prendas'=>['shirt','Prendas'],'solicitudes'=>['file','Solicitudes'],'reportes'=>['chart','Reportes'],'configuracion'=>['settings','Configuración']];
$counts=$stats['counts'];$today=date('d/m/Y');
$nombreSistema=(string)($configuracion['nombre_sistema']??'DiseñaTuPrenda');
$nombreMarca=$nombreSistema==='DiseñaTuPrenda'?'Diseña<span>TuPrenda</span>':h($nombreSistema);
$pendientes=count(array_filter($solicitudes,static fn($r)=>$r['estado']==='Pendiente'));
$days=$stats['days'];$max=max(1,...array_map(static fn($d)=>max($d['usuarios'],$d['solicitudes']),$days));
$chartPoints=function(string $key) use($days,$max): string { $points=[];foreach($days as $i=>$d){$x=35+$i*116;$y=164-($d[$key]/$max)*132;$points[]=$x.','.round($y,1);}return implode(' ',$points); };
$section=(string)($_GET['seccion']??'inicio');
$images=['Vestido largo'=>'vestido-largo.png','Falda midi'=>'falda-midi.png','Top moderno'=>'top-moderno.png','Vestido corto'=>'vestido-corto.png'];
function garmentImage(array $prenda,array $images): ?string {
    if(!empty($prenda['imagen']) && preg_match('~^public/uploads/prenda_[0-9a-f]{24}\.(?:jpg|png|webp)$~',$prenda['imagen'])) return $prenda['imagen'];
    return isset($images[$prenda['nombre']])?'public/assets/img/'.$images[$prenda['nombre']]:null;
}

?>
