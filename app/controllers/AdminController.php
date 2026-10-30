<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/models/AdminModel.php';
class AdminController {
    public function index(): void {
        requireAdmin();
        try {
            $m=new AdminModel();$usuarios=$m->usuarios();$categorias=$m->categorias();$prendas=$m->prendas();$solicitudes=$m->solicitudes();
            $stats=$m->dashboard();$configuracion=$m->configuracion();$disenosDisponibles=$m->disenosDisponibles();$flash=takeFlash();
        }catch(PDOException $e){http_response_code(503);exit('No se puede consultar MySQL. Comprueba el archivo de configuración e importa database/schema.sql.');}
        require dirname(__DIR__).'/views/admin/index.php';
    }
    public function accion(): void {
        requireAdmin();checkCsrf();
        $tabla=post('modulo',30);$operacion=post('operacion',20);$id=(int)($_POST['id']??0);$m=new AdminModel();$subido=null;
        try {
            if($operacion==='guardar') {
                if(!in_array($tabla,['categorias','prendas','usuarios'],true)) throw new InvalidArgumentException('Módulo inválido.');
                $nombre=post('nombre',$tabla==='usuarios'?100:($tabla==='categorias'?90:120));
                if(textLen($nombre)<2) throw new InvalidArgumentException('Ingresa un nombre válido.');
                $data=['nombre'=>$nombre, 'descripcion'=>post('descripcion',240),'estado'=>post('estado',20)];
                if(!in_array($data['estado'],['Activo','Inactivo','Bloqueado'],true)) throw new InvalidArgumentException('Estado inválido.');
                if($tabla==='usuarios'){
                    $correo=strtolower(post('correo',150));
                    if(!filter_var($correo,FILTER_VALIDATE_EMAIL) || !in_array($data['estado'],['Activo','Bloqueado'],true)) throw new InvalidArgumentException('Correo o estado inválido.');
                    $rol=(int)($_POST['rol_id']??0);
                    if(!in_array($rol,[1,2],true)) throw new InvalidArgumentException('Rol inválido.');
                    if($id===(int)user()['id'] && ($rol!==1 || $data['estado']!=='Activo')) throw new InvalidArgumentException('No puedes quitarte el rol de administrador ni bloquearte.');
                    $data['correo']=$correo;$data['rol_id']=$rol;$data['clave']=(string)($_POST['clave']??'');
                }else if($tabla==='prendas'){
                    $cat=(int)($_POST['categoria_id']??0);
                    $q=db()->prepare('SELECT id FROM categorias WHERE id=?');$q->execute([$cat]);
                    if(!$q->fetchColumn()) throw new InvalidArgumentException('Selecciona una categoría existente.');
                    if(!in_array($data['estado'],['Activo','Inactivo'],true)) throw new InvalidArgumentException('Estado inválido.');
                    $data['categoria_id']=$cat;
                    $existing=null;
                    if($id){$q=db()->prepare('SELECT imagen FROM prendas WHERE id=?');$q->execute([$id]);$existing=$q->fetchColumn();}
                    $data['imagen']=$existing?:null;
                    if(isset($_FILES['imagen']) && $_FILES['imagen']['error']!==UPLOAD_ERR_NO_FILE){
                        $f=$_FILES['imagen'];
                        if($f['error']!==UPLOAD_ERR_OK || $f['size']>3*1024*1024 || $f['size']<=0) throw new InvalidArgumentException('La imagen debe medir máximo 3 MB.');
                        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
                        $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
                        if(!$ext || !getimagesize($f['tmp_name'])) throw new InvalidArgumentException('Solo se admiten imágenes JPG, PNG o WebP.');
                        $filename='prenda_'.bin2hex(random_bytes(12)).'.'.$ext;
                        if(!move_uploaded_file($f['tmp_name'],dirname(__DIR__,2).'/public/uploads/'.$filename)) throw new InvalidArgumentException('No se pudo guardar la imagen.');
                        $subido='public/uploads/'.$filename;$data['imagen']=$subido;
                    }
                }elseif(!in_array($data['estado'],['Activo','Inactivo'],true)) throw new InvalidArgumentException('Estado inválido.');
                $m->guardar($tabla,$data,$id);
                flash('success','Registro guardado correctamente.');
            } elseif($operacion==='estado') {
                if($id<=0) throw new InvalidArgumentException('Identificador inválido.');
                $estado=post('estado',20);
                $allowed=$tabla==='usuarios'?['Activo','Bloqueado']:($tabla==='solicitudes'?['Pendiente','En proceso','Aprobada','Rechazada','Finalizada','Archivada']:['Activo','Inactivo']);
                if(!in_array($estado,$allowed,true)) throw new InvalidArgumentException('Estado no permitido.');
                if($tabla==='usuarios' && $id===(int)user()['id']) throw new InvalidArgumentException('No puedes cambiar el estado de tu cuenta administradora.');
                $m->estado($tabla,$id,$estado);flash('success','Estado actualizado correctamente.');
            }elseif($operacion==='nueva_solicitud'){
                $disenoId=(int)($_POST['diseno_id']??0);
                if($disenoId<1) throw new InvalidArgumentException('Debes seleccionar un diseño registrado.');
                $nuevaId=$m->nuevaSolicitud($disenoId,post('descripcion',500));
                $tabla='solicitudes';flash('success','Solicitud #'.$nuevaId.' registrada.');
            }elseif($operacion==='cambiar_clave'){
                $actual=(string)($_POST['clave_actual']??'');$nueva=(string)($_POST['clave_nueva']??'');
                if(textLen($nueva)<10) throw new InvalidArgumentException('La nueva contraseña requiere al menos 10 caracteres.');
                if(!$m->cambiarClave((int)user()['id'],$actual,$nueva)) throw new InvalidArgumentException('La contraseña actual no coincide.');
                $tabla='configuracion';flash('success','Contraseña actualizada.');
            }elseif($operacion==='configuracion'){
                $nombre=post('nombre_sistema',100);
                if(textLen($nombre)<3) throw new InvalidArgumentException('El nombre es demasiado corto.');
                $m->guardarConfiguracion($nombre);flash('success','Configuración guardada.');
            }else throw new InvalidArgumentException('Operación desconocida.');
        }catch(InvalidArgumentException $e){if($subido)@unlink(dirname(__DIR__,2).'/'.$subido);flash('error',$e->getMessage());}
         catch(PDOException $e){if($subido)@unlink(dirname(__DIR__,2).'/'.$subido);flash('error','Error al guardar. Comprueba que no exista un correo o nombre duplicado y que la base de datos esté disponible.');}
        redirect('admin',['seccion'=>in_array($tabla,['categorias','prendas','usuarios','solicitudes'],true)?$tabla:'configuracion']);
    }
    public function reporte(): void {
        requireAdmin();
        $tipo=(string)($_GET['tipo']??'solicitudes');
        $inicio=(string)($_GET['inicio']??'');$fin=(string)($_GET['fin']??'');
        foreach([$inicio,$fin] as $date) if($date && (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!strtotime($date))) {http_response_code(400);exit('Fecha inválida');}
        try { $filas=(new AdminModel())->reporte($tipo,$inicio?:null,$fin?:null); }
        catch(Throwable $e){http_response_code(400);exit('No se pudo preparar el reporte.');}
        if(($_GET['formato']??'')==='csv'){
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="disenatuprenda_'.preg_replace('/[^a-z]/','',$tipo).'.csv"');
            $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");
            if(!$filas){fputcsv($out,['Sin registros']);}
            else{
                fputcsv($out,array_keys($filas[0]));
                foreach($filas as $fila){
                    $safe=array_map(static function($value){$v=(string)$value;return preg_match('/^[\s]*[=+@\-]/u',$v)?"'".$v:$v;},array_values($fila));
                    fputcsv($out,$safe);
                }
            }
            fclose($out);return;
        }
        $fecha=date('d/m/Y H:i');$titulo='Reporte de '.ucfirst($tipo);
        ob_start();require dirname(__DIR__).'/views/admin/reporte.php';$html=ob_get_clean();
        if(($_GET['formato']??'')==='pdf'){
            $autoload=dirname(__DIR__,2).'/vendor/autoload.php';
            if(is_file($autoload)) {
                require_once $autoload;
                if(class_exists('Dompdf\\Dompdf')){
                    $pdf=new \Dompdf\Dompdf(['isRemoteEnabled'=>false]);$pdf->loadHtml($html,'UTF-8');$pdf->setPaper('a4','landscape');$pdf->render();
                    header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="reporte_'.basename($tipo).'.pdf"');echo $pdf->output();return;
                }
            }
            // No fingir que se generó PDF: el instalador explica cómo incorporar Dompdf.
            http_response_code(501); echo 'Para exportar PDF instala Dompdf con: composer require dompdf/dompdf . La vista imprimible HTML sí funciona.';return;
        }
        if(($_GET['formato']??'')==='imprimir'){
            $html=str_replace('</body>','<script>window.addEventListener("load",function(){window.print();});</script></body>',$html);
        }
        echo $html;
    }
}
