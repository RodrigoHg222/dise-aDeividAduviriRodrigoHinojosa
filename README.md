# DiseñaTuPrenda · Portal público y modo cliente · Versión 4

**Base:** ZIP original `disenatuprenda-mvc.zip` proporcionado por el grupo. El diseñador previo se conservó y se incorporaron inicio de sesión, persistencia de diseños y solicitudes, panel de administración y esquema MySQL. La apariencia del administrador se basó en la captura proporcionada (tema azul marino y morado). **La versión 4 incorpora una portada pública y completa el modo cliente con Inicio, Diseñar prenda, Mis diseños, Mis solicitudes, Favoritos, Mi perfil y Configuración. El panel de administrador conserva sus secciones independientes.**

## Si ya instalaste la versión anterior y el administrador funciona

**No vuelvas a importar `database/schema.sql` ni `database/datos_ejemplo.sql` si quieres conservar los registros actuales.** Esta reorganización del panel no necesita modificar la base de datos de la versión 2.

1. En `C:\xampp\htdocs\`, cambia el nombre de la carpeta antigua `disenatuprenda` a `disenatuprenda_respaldo` (para tener una copia del código).
2. Extrae la carpeta `disenatuprenda` de este ZIP en `C:\xampp\htdocs\`.
3. Conserva los datos que ya existen en MySQL; la base `disenatuprenda_db` no se elimina ni se modifica por cambiar el ZIP.
4. Si cambiaste la contraseña o el puerto de MySQL, copia **solo** tus valores de conexión a `app/config/config.php`.
5. Si subiste fotos de prendas o diseños, copia la carpeta `public/uploads` de la versión anterior a la nueva **sin sobrescribir las imágenes nuevas**.
6. Importa `database/actualizacion_cliente_v4.sql` desde phpMyAdmin para crear la tabla de favoritos sin borrar tus registros. Luego abre `http://localhost/disenatuprenda/` y actualiza el navegador con **Ctrl + F5**.

La actualización no cambia las credenciales ni elimina tus datos. La tabla nueva `favoritos` se agrega con el SQL de actualización.

## Instalación paso a paso en Windows con XAMPP

1. Extrae la carpeta **`disenatuprenda`** en `C:\xampp\htdocs\disenatuprenda`. No cambies la estructura de archivos.
2. Abre XAMPP e inicia **Apache** y **MySQL**.
3. Abre `http://localhost/phpmyadmin`, entra a **Importar** y selecciona `C:\xampp\htdocs\disenatuprenda\database\schema.sql`. El archivo creará la base `disenatuprenda_db`, las relaciones y las tablas. También puedes ejecutar el SQL en la pestaña SQL.
4. Si deseas categorías y prendas de **ejemplo** para ensayar, importa después `database/datos_ejemplo.sql`. No son datos de encuestas reales.
5. Revisa `app/config/config.php`: para XAMPP habitual, servidor `127.0.0.1`, puerto `3306`, usuario `root` y contraseña vacía. Si tu MySQL usa otros datos, actualiza la configuración. Nunca publiques contraseñas reales en Git.
6. Entra a **`http://localhost/disenatuprenda/`**. Crea una cuenta en **Regístrate**.
7. Para convertir esa cuenta en administrador **una sola vez**, abre una terminal en la carpeta del proyecto y ejecuta:

   `C:\xampp\php\php.exe scripts\promover_admin.php tu_correo@ejemplo.com`

   Alternativamente, en phpMyAdmin puedes ejecutar **solo para tu correo registrado**:

   ```sql
   USE disenatuprenda_db;
   UPDATE usuarios SET rol_id = (SELECT id FROM roles WHERE nombre='administrador')
   WHERE correo = 'tu_correo@ejemplo.com';
   ```

8. Cierra la sesión y vuelve a entrar con esa cuenta: verás el **Panel de administrador**. Las cuentas normales ingresan al nuevo **Inicio del cliente** y desde allí acceden a todas sus secciones.

## Páginas principales

| Dirección | Acceso | Uso |
|---|---|---|
| `/disenatuprenda/` | Público | Portada principal; muestra el acceso al cliente y al administrador |
| `index.php?r=registro` | Público | Registro de clientes |
| `index.php?r=login` | Público | Inicio de sesión |
| `index.php?r=cliente` | Usuario | Panel de inicio con resumen y accesos rápidos |
| `index.php?r=disenador` | Usuario | Catálogo, personalización, favoritos y envío de solicitudes |
| `index.php?r=mis_disenos` | Usuario | Historial de diseños guardados |
| `index.php?r=mis_solicitudes` | Usuario | Seguimiento de estados de las solicitudes |
| `index.php?r=favoritos` | Usuario | Modelos guardados desde el catálogo |
| `index.php?r=perfil` | Usuario | Actualización de datos y contraseña |
| `index.php?r=configuracion_usuario` | Usuario | Estado y accesos de configuración de la cuenta |
| `index.php?r=admin&seccion=inicio` | Administrador | Solo dashboard: cifras, gráfico, prendas populares y actividad |
| `index.php?r=admin&seccion=usuarios` | Administrador | Gestión individual de usuarios |
| `index.php?r=admin&seccion=categorias` | Administrador | Gestión individual de categorías |
| `index.php?r=admin&seccion=prendas` | Administrador | Gestión individual de prendas |
| `index.php?r=admin&seccion=solicitudes` | Administrador | Gestión individual de solicitudes |
| `index.php?r=admin&seccion=reportes` | Administrador | Generar, exportar e imprimir reportes |
| `index.php?r=admin&seccion=configuracion` | Administrador | Configuración, seguridad, notificaciones y respaldo |

## Qué funciona y qué queda pendiente

**Implementado a nivel de código:** registro y login con `password_hash` y `password_verify`; protección por sesión y roles; CSRF en operaciones POST; consultas preparadas con PDO/MySQL; tablas relacionadas; CRUD de usuarios, categorías y prendas (crear, consultar, editar y activar/desactivar); gestión de estados de solicitudes y creación administrativa de solicitudes sobre diseños existentes; carga segura de fotografías de prendas (hasta 3 MB); notificaciones de solicitudes pendientes; actualización de contraseña del administrador; búsquedas, filtros y paginación en cliente; resumen y gráfico de actividad consultados de MySQL; reportes HTML imprimibles y exportación CSV, con opción de abrir directamente el diálogo para guardar como PDF; guardado de diseño y envío de solicitudes; referencia de imagen JPG, PNG o WebP hasta 2 MB; portada pública; panel de inicio para el cliente; historial de diseños; seguimiento de solicitudes; favoritos persistentes en MySQL; actualización de perfil y contraseña; configuración de cuenta; diseño responsivo. En la interfaz del diseñador se preservan algunas funciones visuales de la maqueta original (por ejemplo la vista “3D” no es modelado tridimensional real).

**Requiere completar/configurar:** importar SQL y comprobar el acceso MySQL en tu equipo; realizar pruebas funcionales integrales con datos reales; decidir si se agregan vistas de imágenes específicas por prenda y más controles del personalizador; incluir resultados reales de entrevistas de Sacaba. El botón **Guardar PDF** abre el diálogo de impresión del navegador: selecciona **Guardar como PDF**. La generación de un PDF binario desde PHP requiere opcionalmente Dompdf. El CSV funciona sin librerías externas.

## Verificación básica en XAMPP (sin alterar datos)

Desde `C:\xampp\htdocs\disenatuprenda`, ejecuta:

`C:\xampp\php\php.exe scripts\verificar_instalacion.php`

El script revisa conexión, tablas y número de relaciones foráneas. **No sustituye las pruebas de botones**. Sigue `docs/checklist_pruebas.md` y registra los resultados reales en semana 4.

## Exportación PDF con Dompdf (opcional)

Con Composer instalado, ejecuta en la carpeta del proyecto: `composer require dompdf/dompdf`.

La ruta `index.php?r=reporte&tipo=solicitudes&formato=pdf` devolverá un PDF cuando Dompdf esté disponible. Esta dependencia **no está incluida** dentro del ZIP, para evitar simular una librería instalada.

## Seguridad y buenas prácticas

- Acceso a `admin`, administración y reportes restringido en el servidor al rol **administrador**.
- Operaciones que cambian datos solo con POST y token CSRF.
- Contraseñas con hash; validación en PHP además de campos HTML.
- SQL con parámetros; nombres de tabla y campos permitidos por lista cerrada.
- En lugar de eliminar registros vinculados, se cambia su **estado** para preservar integridad e historial.
- Los archivos de referencia se validan por tamaño y MIME y reciben nombre aleatorio; se bloquea la ejecución de PHP en `public/uploads/` (Apache con `.htaccess`).
- El reporte no toma datos inventados de la imagen: consulta los registros guardados.

## Estructura MVC extendida

```text
disenatuprenda/
├── index.php                  # Entrada y rutas
├── app/
│   ├── config/config.php      # MySQL
│   ├── core/bootstrap.php     # Sesión, DB, seguridad
│   ├── controllers/           # Auth, Disenador, Admin
│   ├── models/                # Usuario, Diseño, Admin
│   └── views/                 # auth, admin, disenador, layouts
├── database/schema.sql       # Base relacional
├── database/datos_ejemplo.sql # Datos simulados opcionales
├── docs/                      # Diagrama MVC y ER
├── scripts/promover_admin.php # Solo ejecución en terminal
└── public/assets/            # CSS, JS, imágenes
```

## Validación

Este paquete pasó pruebas automáticas de **sintaxis PHP** y **sintaxis JavaScript**. No fue posible ejecutar pruebas reales contra un servidor MySQL desde el entorno de generación; deben realizarse en XAMPP con Apache y MySQL encendidos. Un diseño visual o un código sin errores de sintaxis no equivale a una validación funcional completa.

## Correspondencia con el documento académico

El PDF indica: semana 1, análisis; **semana 2, diseño de MVC, base de datos, pantallas y permisos**; semana 3, implementación; semana 4, verificación y publicación; semana 5, presentación y feedback. El Word original propone siete entidades (`Rol`, `Usuario`, `Categoría`, `Prenda`, `Diseño`, `Solicitud` y `DetalleSolicitud`) y los módulos de autenticación, catálogo, personalización, solicitudes, administración y reportes. En la implementación se agregó además una pequeña tabla de configuración del sistema. Ver los diagramas de `/docs/` y el documento Word de diseño entregado por separado.

**Importante sobre el catálogo:** el diseñador ahora consulta las categorías y prendas activas de la tabla MySQL. Si recién instalas el esquema y no creas prendas, verás el mensaje de catálogo vacío. Ingresa como administrador y crea al menos una categoría y una prenda, o carga los datos simulados de `datos_ejemplo.sql`.

**PHP recomendado:** 8.1 o superior (se utilizan funciones como `match` y tipos modernos de retorno). Extensiones requeridas en XAMPP: PDO, `pdo_mysql`, `fileinfo` y manejo de imágenes. Para archivos de referencia, la carpeta `public/uploads` debe tener permisos de escritura para Apache.

## Funcionalidades del administrador integradas en esta revisión

- **Inicio:** tarjetas, actividad de 7 días, prendas solicitadas y registro reciente; los números dependen de MySQL y el icono de campana abre las solicitudes pendientes.
- **Usuarios:** nuevo, editar, activar o bloquear (el administrador no puede bloquear su propia cuenta desde el panel).
- **Categorías:** alta, descripción, edición, filtro y activación/desactivación.
- **Prendas:** alta, edición, fotografía opcional, categoría, estado, búsqueda y filtro; solo prendas y categorías activas aparecen en el diseñador.
- **Solicitudes:** revisar datos, actualizar estado y registrar una solicitud asociada a un diseño de cliente ya guardado.
- **Reportes:** filtros por fecha, HTML imprimible, descarga CSV y botón que abre el diálogo del navegador para guardar como PDF.
- **Configuración:** nombre del sistema; sección de Seguridad para cambiar la contraseña; Notificaciones para ver pendientes; Respaldo con exportaciones CSV.

Los CSV son respaldos administrativos de datos seleccionados, **no copias completas SQL**. Para respaldar toda la base usa Exportar en phpMyAdmin.


## Versión 4: portada y modo cliente completo

- **Portada pública:** nueva página inicial con explicación del sistema y accesos a registro e inicio de sesión.
- **Inicio del cliente:** resumen de diseños, solicitudes pendientes y favoritos.
- **Diseñar prenda:** conserva el personalizador y permite guardar un modelo en Favoritos desde el catálogo.
- **Mis diseños:** historial separado de propuestas guardadas.
- **Mis solicitudes:** seguimiento independiente del estado de cada solicitud de confección.
- **Favoritos:** agrega o quita modelos y los conserva en la tabla `favoritos`.
- **Mi perfil:** permite actualizar nombre, correo y contraseña con validación de contraseña actual.
- **Configuración:** concentra accesos a perfil, favoritos, seguimiento y cierre de sesión.

**Actualización de una base de datos existente:** importa `database/actualizacion_cliente_v4.sql` desde phpMyAdmin. No vuelvas a importar `schema.sql` sobre datos que quieras conservar. En una instalación nueva, el `schema.sql` actualizado ya crea la tabla `favoritos`.

## Versión 3: navegación reorganizada por separado

El menú de la izquierda ya **no** utiliza anclas `#usuarios`, `#categorias`, etc. Cada opción navega mediante `?r=admin&seccion=...`. En el servidor se selecciona una vista bajo `app/views/admin/sections/`, por lo que las otras secciones no se incluyen ni aparecen en pantalla:

```text
app/views/admin/
├── index.php                 # Cabecera, menú, ruta seleccionada, diálogos y pie
├── helpers.php               # Iconos, estados, datos preparados para la vista
└── sections/
    ├── inicio.php            # Resumen: indicadores, gráficos y actividad
    ├── usuarios.php          # Tabla, filtro y mantenimiento de usuarios
    ├── categorias.php        # Tabla, filtro y mantenimiento de categorías
    ├── prendas.php           # Tabla, fotos, filtro y mantenimiento de prendas
    ├── solicitudes.php       # Bandeja, detalle y cambio de estado
    ├── reportes.php          # Reportes, fechas, exportación e impresión
    └── configuracion.php     # General, clave, MySQL, notificaciones, respaldo
```

Los botones Nuevo/Editar/Desactivar/Estado siguen utilizando `AdminController.php` + `AdminModel.php` + JavaScript `admin.js`, con validación de token CSRF y control del rol administrador en PHP. **Cerrar sesión** ejecuta POST `logout` y regresa al acceso. Los usuarios normales no tienen acceso al panel.

Se verificó que el HTML generado para las siete vistas incluye únicamente la sección pedida (con datos simulados SOLO en pruebas internas de presentación; no se distribuyen como registros reales). Quedan pendientes pruebas completas de CRUD en Apache/MySQL de tu XAMPP, y no se afirma que ya se hayan ejecutado.
