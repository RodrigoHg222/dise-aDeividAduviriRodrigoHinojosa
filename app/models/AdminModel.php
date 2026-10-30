<?php
declare(strict_types=1);
class AdminModel {
    public function usuarios(): array { return db()->query('SELECT u.id,u.nombre,u.correo,u.estado,u.creado_en,r.nombre AS rol FROM usuarios u JOIN roles r ON r.id=u.rol_id ORDER BY u.id DESC')->fetchAll(); }
    public function categorias(): array { return db()->query('SELECT * FROM categorias ORDER BY id DESC')->fetchAll(); }
    public function prendas(): array { return db()->query('SELECT p.*,c.nombre AS categoria FROM prendas p JOIN categorias c ON c.id=p.categoria_id ORDER BY p.id DESC')->fetchAll(); }
    public function solicitudes(): array { return db()->query('SELECT s.*,u.nombre AS usuario,d.nombre AS diseno FROM solicitudes s JOIN usuarios u ON u.id=s.usuario_id JOIN disenos d ON d.id=s.diseno_id ORDER BY s.id DESC')->fetchAll(); }
    public function disenosDisponibles(): array {
        return db()->query("SELECT d.id, d.nombre, d.usuario_id, u.nombre AS usuario FROM disenos d JOIN usuarios u ON u.id=d.usuario_id WHERE u.estado='Activo' ORDER BY d.id DESC LIMIT 300")->fetchAll();
    }
    public function nuevaSolicitud(int $disenoId, string $descripcion): int {
        $pdo=db();$pdo->beginTransaction();
        try {
            $q=$pdo->prepare('SELECT usuario_id,prenda_id,talla,color,tela FROM disenos WHERE id=?');$q->execute([$disenoId]);
            $d=$q->fetch();if(!$d) throw new InvalidArgumentException('El diseño seleccionado ya no existe.');
            $q=$pdo->prepare('INSERT INTO solicitudes(usuario_id,diseno_id,descripcion) VALUES(?,?,?)');
            $q->execute([$d['usuario_id'],$disenoId,$descripcion]);$id=(int)$pdo->lastInsertId();
            $q=$pdo->prepare('INSERT INTO detalles_solicitud(solicitud_id,prenda_id,cantidad,especificaciones) VALUES(?,?,1,?)');
            $q->execute([$id,$d['prenda_id'],json_encode(['talla'=>$d['talla'],'color'=>$d['color'],'tela'=>$d['tela']],JSON_UNESCAPED_UNICODE)]);
            $pdo->commit(); return $id;
        }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
    }
    public function cambiarClave(int $id,string $actual,string $nueva): bool {
        $q=db()->prepare('SELECT password_hash FROM usuarios WHERE id=?');$q->execute([$id]);
        $hash=$q->fetchColumn();if(!$hash || !password_verify($actual,$hash))return false;
        $q=db()->prepare('UPDATE usuarios SET password_hash=? WHERE id=?');
        $q->execute([password_hash($nueva,PASSWORD_DEFAULT),$id]);return true;
    }
    public function configuracion(): array { return db()->query('SELECT clave,valor FROM configuracion')->fetchAll(PDO::FETCH_KEY_PAIR); }
    public function dashboard(): array {
        $counts=[];
        foreach (['usuarios','categorias','prendas','solicitudes'] as $table) {
            $counts[$table]=(int)db()->query('SELECT COUNT(*) FROM '.$table.($table==='prendas'?" WHERE estado='Activo'":''))->fetchColumn();
        }
        $recent=db()->query("SELECT 'usuario' AS tipo, nombre AS titulo,creado_en FROM usuarios UNION ALL SELECT 'prenda',nombre,creado_en FROM prendas UNION ALL SELECT 'solicitud',CONCAT('Solicitud #',id),creado_en FROM solicitudes ORDER BY creado_en DESC LIMIT 6")->fetchAll();
        $most=db()->query("SELECT p.nombre, p.imagen, COUNT(ds.id) AS cantidad,p.id FROM prendas p JOIN detalles_solicitud ds ON ds.prenda_id=p.id GROUP BY p.id,p.nombre,p.imagen ORDER BY cantidad DESC LIMIT 4")->fetchAll();
        $days=[];
        for($i=6;$i>=0;$i--){ $day=date('Y-m-d',strtotime('-'.$i.' days')); $days[$day]=['fecha'=>$day,'usuarios'=>0,'solicitudes'=>0]; }
        foreach (['usuarios','solicitudes'] as $table) {
            $q=db()->prepare('SELECT DATE(creado_en) AS dia,COUNT(*) AS n FROM '.$table.' WHERE creado_en>=? GROUP BY DATE(creado_en)');
            $q->execute([array_key_first($days)]);
            foreach($q as $r) if(isset($days[$r['dia']])) $days[$r['dia']][$table]=(int)$r['n'];
        }
        return ['counts'=>$counts,'recent'=>$recent,'most'=>$most,'days'=>array_values($days)];
    }
    public function guardar(string $tabla, array $data, int $id=0): void {
        // Lista cerrada: nunca interpolar nombres de tabla o campos recibidos sin validar.
        $allowed=[
          'categorias'=>['nombre','descripcion','estado'],
          'prendas'=>['categoria_id','nombre','descripcion','imagen','estado'],
          'usuarios'=>['nombre','correo','rol_id','estado'],
        ];
        if(!isset($allowed[$tabla])) throw new InvalidArgumentException('Módulo inválido.');
        $columns=$allowed[$tabla]; $values=[];
        foreach($columns as $c) $values[$c]=$data[$c]??'';
        if($tabla==='usuarios' && $id===0) {
            if(empty($data['clave']) || textLen($data['clave'])<8) throw new InvalidArgumentException('La contraseña debe tener 8 caracteres o más.');
            $values['password_hash']=password_hash($data['clave'],PASSWORD_DEFAULT);
        } elseif ($tabla==='usuarios' && !empty($data['clave'])) {
            if(textLen($data['clave'])<8) throw new InvalidArgumentException('La contraseña debe tener 8 caracteres o más.');
            $values['password_hash']=password_hash($data['clave'],PASSWORD_DEFAULT);
        }
        if ($id) {
            $sets=implode(', ',array_map(static fn($key)=>"`$key`=?", array_keys($values)));
            $stmt=db()->prepare("UPDATE `$tabla` SET $sets WHERE id=?");
            $stmt->execute([...array_values($values),$id]);
        } else {
            $keys=array_keys($values);
            $cols=implode(',',array_map(static fn($key)=>"`$key`", $keys));
            $marks=implode(',',array_fill(0,count($keys),'?'));
            $stmt=db()->prepare("INSERT INTO `$tabla` ($cols) VALUES ($marks)");
            $stmt->execute(array_values($values));
        }
    }
    public function estado(string $tabla, int $id, string $estado): void {
        if(!in_array($tabla,['categorias','prendas','usuarios','solicitudes'],true)) throw new InvalidArgumentException('Módulo inválido.');
        $stmt=db()->prepare("UPDATE `$tabla` SET estado=? WHERE id=?"); $stmt->execute([$estado,$id]);
    }
    public function cambiarEstadoSolicitud(int $id, string $estado): void { $this->estado('solicitudes',$id,$estado); }
    public function guardarConfiguracion(string $nombre): void {
        $q=db()->prepare("INSERT INTO configuracion(clave,valor) VALUES('nombre_sistema',?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)"); $q->execute([$nombre]);
    }
    public function reporte(string $tipo, ?string $inicio, ?string $fin): array {
        $definitions=[
         'usuarios'=>['sql'=>'SELECT u.id,u.nombre,u.correo,r.nombre AS rol,u.estado,u.creado_en AS fecha FROM usuarios u JOIN roles r ON r.id=u.rol_id','date'=>'u.creado_en'],
         'categorias'=>['sql'=>'SELECT id,nombre,descripcion,estado,creado_en AS fecha FROM categorias','date'=>'creado_en'],
         'prendas'=>['sql'=>'SELECT p.id,p.nombre,c.nombre AS categoria,p.estado,p.creado_en AS fecha FROM prendas p JOIN categorias c ON c.id=p.categoria_id','date'=>'p.creado_en'],
         'solicitudes'=>['sql'=>'SELECT s.id,u.nombre AS usuario,d.nombre AS diseno,s.descripcion,s.estado,s.creado_en AS fecha FROM solicitudes s JOIN usuarios u ON u.id=s.usuario_id JOIN disenos d ON d.id=s.diseno_id','date'=>'s.creado_en'],
         'actividad'=>['sql'=>"SELECT id,'Usuario registrado' AS tipo,nombre AS detalle,creado_en AS fecha FROM usuarios UNION ALL SELECT id,'Prenda registrada',nombre,creado_en FROM prendas UNION ALL SELECT id,'Solicitud recibida',CONCAT('Solicitud #',id),creado_en FROM solicitudes",'date'=>'fecha'],
        ];
        if(!isset($definitions[$tipo])) throw new InvalidArgumentException('Reporte desconocido.');
        $def=$definitions[$tipo]; $where=[]; $args=[];
        if($tipo==='actividad'){ $def['sql']='SELECT * FROM ('.$def['sql'].') AS actividad_registrada'; }
        if($inicio){$where[]=$def['date'].' >= ?'; $args[]=$inicio.' 00:00:00';}
        if($fin){$where[]=$def['date'].' <= ?'; $args[]=$fin.' 23:59:59';}
        $sql=$def['sql'].($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY fecha DESC LIMIT 1000';
        $q=db()->prepare($sql);$q->execute($args);return $q->fetchAll();
    }
}
