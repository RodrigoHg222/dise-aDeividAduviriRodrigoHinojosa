<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/models/DisenoModel.php';
class DisenadorController {
    public function index(): void {
        requireLogin();
        if(user()['rol']==='administrador') redirect('admin');
        try {
            // El catálogo que administra el rol administrador también alimenta el diseñador.
            $categorias=db()->query("SELECT nombre FROM categorias WHERE estado='Activo' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
            $modelos=db()->query("SELECT p.id, p.nombre, p.imagen, c.nombre AS categoria FROM prendas p JOIN categorias c ON c.id=p.categoria_id WHERE p.estado='Activo' AND c.estado='Activo' ORDER BY c.id,p.id")->fetchAll();
            $fav=db()->prepare('SELECT prenda_id FROM favoritos WHERE usuario_id=?');$fav->execute([(int)user()['id']]);$favoritosIds=array_map('intval',$fav->fetchAll(PDO::FETCH_COLUMN));
            $selectedPrendaId=(int)($_GET['prenda']??0);
        } catch (PDOException $e) {
            http_response_code(503);
            exit('No se pudo cargar el catálogo. Importa database/schema.sql y comprueba tu configuración de MySQL.');
        }
        $pageTitle='Diseñar prenda';$pageSubtitle='Personaliza cada detalle de tus prendas de vestir';$activePage='disenador';
        require dirname(__DIR__).'/views/disenador/index.php';
    }
    public function guardar(): void {
        requireLogin();if(user()['rol']!=='usuario')jsonResponse(['ok'=>false,'mensaje'=>'Acceso restringido.'],403);
        checkCsrf();
        $nombre=post('modelo',120);$talla=post('talla',12);$color=post('color',40);$tela=post('tela',60);$prendaId=(int)($_POST['prenda_id']??0);
        if($prendaId<=0 || textLen($nombre)<2 || !in_array($talla,['XS','S','M','L','XL','XXL'],true) || !$color || !$tela) jsonResponse(['ok'=>false,'mensaje'=>'Modelo, talla, color y tela son obligatorios.'],422);
        $archivo=null;
        if(isset($_FILES['referencia']) && $_FILES['referencia']['error']!==UPLOAD_ERR_NO_FILE){
            $f=$_FILES['referencia'];
            if($f['error']!==UPLOAD_ERR_OK || $f['size']>2*1024*1024) jsonResponse(['ok'=>false,'mensaje'=>'Imagen inválida o mayor a 2 MB.'],422);
            $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
            if(!$ext || !getimagesize($f['tmp_name']))jsonResponse(['ok'=>false,'mensaje'=>'Solo se aceptan imágenes JPG, PNG o WEBP.'],422);
            $nombreArchivo=bin2hex(random_bytes(16)).'.'.$ext;
            if(!move_uploaded_file($f['tmp_name'],dirname(__DIR__,2).'/public/uploads/'.$nombreArchivo))jsonResponse(['ok'=>false,'mensaje'=>'No se pudo guardar la imagen.'],500);
            $archivo='public/uploads/'.$nombreArchivo;
        }
        try{
            $db=db();$db->beginTransaction();
            $query=$db->prepare("SELECT p.id,p.nombre FROM prendas p JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.estado='Activo' AND c.estado='Activo' LIMIT 1");$query->execute([$prendaId]);
            $prenda=$query->fetch();
            if(!$prenda) throw new InvalidArgumentException('Selecciona una prenda disponible del catálogo.');
            $nombre=$prenda['nombre'];
            $datos=['usuario_id'=>user()['id'],'prenda_id'=>$prendaId,'nombre'=>$nombre,'talla'=>$talla,'color'=>$color,'tela'=>$tela,
             'escote'=>post('escote',60),'manga'=>post('manga',60),'largo'=>post('largo',60),'texto_personalizado'=>post('texto_personalizado',160),'ubicacion_texto'=>in_array(post('ubicacion_texto',8),['Pecho','Espalda'],true)?post('ubicacion_texto',8):'Pecho','imagen_referencia'=>$archivo,'descripcion'=>post('descripcion',500)];
            $m=new DisenoModel();$id=$m->guardar($datos);$solicitudId=null;
            if(post('modo',15)==='solicitud')$solicitudId=$m->solicitar($id,$datos);
            $db->commit();
            jsonResponse(['ok'=>true,'mensaje'=>$solicitudId?'Solicitud #'.$solicitudId.' enviada correctamente.':'Diseño #'.$id.' guardado correctamente.','diseno_id'=>$id,'solicitud_id'=>$solicitudId]);
        }catch(InvalidArgumentException $e){if(isset($db)&&$db->inTransaction())$db->rollBack();if($archivo)@unlink(dirname(__DIR__,2).'/'.$archivo);jsonResponse(['ok'=>false,'mensaje'=>$e->getMessage()],422);}
        catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();if($archivo)@unlink(dirname(__DIR__,2).'/'.$archivo);jsonResponse(['ok'=>false,'mensaje'=>'Error de base de datos al guardar. Revisa la conexión MySQL.'],500);}
    }
    public function misDisenos(): void {
        requireLogin();if(user()['rol']==='administrador')redirect('admin');
        try{$disenos=(new DisenoModel())->listarPorUsuario((int)user()['id']);}
        catch(PDOException $e){$disenos=[];flash('error','No se puede conectar a MySQL.');}
        $flash=takeFlash();$pageTitle='Mis diseños';$pageSubtitle='Todas tus ideas guardadas en un solo lugar';$activePage='mis_disenos';
        require dirname(__DIR__).'/views/disenador/mis_disenos.php';
    }
    public function inicio(): void {
        requireLogin(); if(user()['rol']==='administrador') redirect('admin');
        try {
            $id=(int)user()['id'];
            $q=db()->prepare('SELECT COUNT(*) FROM disenos WHERE usuario_id=?');$q->execute([$id]);$totalDisenos=(int)$q->fetchColumn();
            $q=db()->prepare('SELECT COUNT(*) FROM solicitudes WHERE usuario_id=?');$q->execute([$id]);$totalSolicitudes=(int)$q->fetchColumn();
            $q=db()->prepare("SELECT COUNT(*) FROM solicitudes WHERE usuario_id=? AND estado IN ('Pendiente','En proceso')");$q->execute([$id]);$pendientes=(int)$q->fetchColumn();
            $q=db()->prepare('SELECT COUNT(*) FROM favoritos WHERE usuario_id=?');$q->execute([$id]);$totalFavoritos=(int)$q->fetchColumn();
            $q=db()->prepare("SELECT d.*,(SELECT s.estado FROM solicitudes s WHERE s.diseno_id=d.id ORDER BY s.id DESC LIMIT 1) AS solicitud_estado FROM disenos d WHERE d.usuario_id=? ORDER BY d.id DESC LIMIT 4");$q->execute([$id]);$recientes=$q->fetchAll();
        } catch(PDOException $e) { http_response_code(503); exit('No se pudo cargar tu panel. Comprueba MySQL y ejecuta database/actualizacion_cliente_v4.sql si tu base de datos ya existía.'); }
        $flash=takeFlash();$pageTitle='Inicio';$pageSubtitle='Tu espacio creativo para diseñar prendas a tu estilo';$activePage='cliente';
        require dirname(__DIR__).'/views/disenador/inicio.php';
    }

    public function misSolicitudes(): void {
        requireLogin(); if(user()['rol']==='administrador') redirect('admin');
        try {
            $q=db()->prepare('SELECT s.*,d.nombre AS prenda,d.talla,d.color,d.tela,d.texto_personalizado FROM solicitudes s JOIN disenos d ON d.id=s.diseno_id WHERE s.usuario_id=? ORDER BY s.id DESC');
            $q->execute([(int)user()['id']]);$solicitudes=$q->fetchAll();
        } catch(PDOException $e) { http_response_code(503); exit('No se pudieron cargar tus solicitudes. Comprueba la conexión MySQL.'); }
        $flash=takeFlash();$pageTitle='Mis solicitudes';$pageSubtitle='Consulta el estado de las propuestas que enviaste';$activePage='mis_solicitudes';
        require dirname(__DIR__).'/views/disenador/mis_solicitudes.php';
    }

    public function favoritos(): void {
        requireLogin(); if(user()['rol']==='administrador') redirect('admin');
        try {
            $q=db()->prepare('SELECT p.id,p.nombre,p.imagen,c.nombre AS categoria,f.creado_en FROM favoritos f JOIN prendas p ON p.id=f.prenda_id JOIN categorias c ON c.id=p.categoria_id WHERE f.usuario_id=? AND p.estado=\'Activo\' AND c.estado=\'Activo\' ORDER BY f.id DESC');
            $q->execute([(int)user()['id']]);$favoritos=$q->fetchAll();
        } catch(PDOException $e) { http_response_code(503); exit('No se pudieron cargar tus favoritos. Ejecuta database/actualizacion_cliente_v4.sql y vuelve a intentarlo.'); }
        $flash=takeFlash();$pageTitle='Mis favoritos';$pageSubtitle='Guarda aquí los modelos que te gustaría personalizar';$activePage='favoritos';
        require dirname(__DIR__).'/views/disenador/favoritos.php';
    }

    public function favoritoAccion(): void {
        requireLogin(); if(user()['rol']==='administrador') redirect('admin'); checkCsrf();
        $prendaId=(int)($_POST['prenda_id']??0);$operacion=post('operacion',20);
        if($prendaId<1){flash('error','Selecciona una prenda válida.');redirect('favoritos');}
        try {
            if($operacion==='agregar') {
                $q=db()->prepare("INSERT IGNORE INTO favoritos(usuario_id,prenda_id) SELECT ?,p.id FROM prendas p JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.estado='Activo' AND c.estado='Activo'");
                $q->execute([(int)user()['id'],$prendaId]);flash('success','Modelo agregado a tus favoritos.');
            } elseif($operacion==='quitar') {
                $q=db()->prepare('DELETE FROM favoritos WHERE usuario_id=? AND prenda_id=?');$q->execute([(int)user()['id'],$prendaId]);flash('success','Modelo quitado de tus favoritos.');
            } else { flash('error','Operación no válida.'); }
        } catch(PDOException $e) { flash('error','No se pudo actualizar favoritos. Ejecuta la actualización de base de datos v4.'); }
        $volver=post('volver',20);redirect($volver==='disenador'?'disenador':'favoritos');
    }

    public function perfil(): void {
        requireLogin(); if(user()['rol']==='administrador') redirect('admin');
        $perfil=(new UsuarioModel())->porId((int)user()['id']);
        if(!$perfil){$_SESSION=[];redirect('login');}
        $flash=takeFlash();$pageTitle='Mi perfil';$pageSubtitle='Administra tu información personal y tus datos de acceso';$activePage='perfil';
        require dirname(__DIR__).'/views/disenador/perfil.php';
    }

    public function guardarPerfil(): void {
        requireLogin(); if(user()['rol']==='administrador') redirect('admin'); checkCsrf();
        $modelo=new UsuarioModel();$operacion=post('operacion',30);$id=(int)user()['id'];
        try {
            if($operacion==='datos') {
                $nombre=post('nombre',100);$correo=strtolower(post('correo',150));
                if(textLen($nombre)<3 || !filter_var($correo,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Escribe un nombre de al menos 3 caracteres y un correo válido.');
                if($modelo->correoEnUso($correo,$id)) throw new InvalidArgumentException('Ese correo ya está registrado por otra cuenta.');
                $modelo->actualizarDatos($id,$nombre,$correo);
                $_SESSION['usuario']['nombre']=$nombre;$_SESSION['usuario']['correo']=$correo;
                flash('success','Tus datos personales se actualizaron correctamente.');
            } elseif($operacion==='clave') {
                $actual=(string)($_POST['clave_actual']??'');$nueva=(string)($_POST['clave_nueva']??'');$confirmar=(string)($_POST['clave_confirmar']??'');
                if(textLen($nueva)<8) throw new InvalidArgumentException('La nueva contraseña debe tener al menos 8 caracteres.');
                if($nueva!==$confirmar) throw new InvalidArgumentException('La confirmación de contraseña no coincide.');
                if(!$modelo->cambiarClave($id,$actual,$nueva)) throw new InvalidArgumentException('La contraseña actual no es correcta.');
                flash('success','Tu contraseña se cambió correctamente.');
            } else throw new InvalidArgumentException('Operación no válida.');
        } catch(InvalidArgumentException $e) { flash('error',$e->getMessage()); }
          catch(PDOException $e) { flash('error','No se pudieron guardar los cambios. Comprueba si el correo ya está registrado y revisa MySQL.'); }
        redirect('perfil');
    }

    public function configuracion(): void {
        requireLogin(); if(user()['rol']==='administrador') redirect('admin');
        try {
            $q=db()->prepare('SELECT creado_en,estado FROM usuarios WHERE id=?');$q->execute([(int)user()['id']]);$cuenta=$q->fetch();
            $q=db()->prepare('SELECT COUNT(*) FROM favoritos WHERE usuario_id=?');$q->execute([(int)user()['id']]);$totalFavoritos=(int)$q->fetchColumn();
        } catch(PDOException $e) { http_response_code(503); exit('No se pudo consultar la configuración de tu cuenta.'); }
        $flash=takeFlash();$pageTitle='Configuración';$pageSubtitle='Preferencias y seguridad de tu cuenta';$activePage='configuracion_usuario';
        require dirname(__DIR__).'/views/disenador/configuracion.php';
    }

}
