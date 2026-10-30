# DiseñaTuPrenda — lista para comprobar en XAMPP

La fase actual es **semana 2 (diseño)**. Este plan de verificación se completará en **semana 4**. Marca los resultados únicamente después de ejecutar cada prueba. **No se declara que MySQL haya sido probado en el entorno de generación.**

| Código | Prueba | Resultado esperado | Estado real |
|---|---|---|---|
| T01 | Importar `schema.sql` en phpMyAdmin | 9 tablas con claves foráneas, incluida `favoritos` | Pendiente |
| T02 | Abrir la portada, registrar cliente e iniciar/cerrar sesión | Acceso al inicio del cliente y cierre seguro | Pendiente |
| T03 | Acceder a `?r=admin` como cliente | Denegado (403) | Pendiente |
| T04 | Crear administrador por terminal y volver a entrar | Acceso al panel de administrador | Pendiente |
| T05 | Crear categoría, prenda y foto opcional | Se guarda en MySQL y aparece en el catálogo | Pendiente |
| T06 | Editar y desactivar una prenda | Cambia la tabla y desaparece del catálogo del cliente | Pendiente |
| T07 | Cliente guarda diseño y adjunta imagen válida | Se crea diseño; archivo JPG/PNG/WebP <= 2 MB | Pendiente |
| T08 | Cliente envía solicitud y revisa su historial | La solicitud se registra con estado Pendiente | Pendiente |
| T09 | Administrador cambia el estado de solicitud | Cliente consulta el nuevo estado | Pendiente |
| T10 | Filtrar usuarios, categorías, prendas y solicitudes | Búsqueda, filtros y paginación | Pendiente |
| T11 | Abrir reportes y exportar CSV o imprimir | Documento con registros reales del sistema | Pendiente |
| T12 | Cambiar clave de administrador | Clave antigua deja de permitir entrar | Pendiente |
| T13 | Cargar archivo no permitido o >3 MB en prendas | Se rechaza y muestra error | Pendiente |
| T14 | Navegar desde celular | Panel sin desbordamientos graves y botones utilizables | Pendiente |
| T15 | Probar CSRF inválido en un formulario | Solicitud rechazada (419) | Pendiente |
| T16 | Bloquear cuenta mientras tiene sesión iniciada | Pierde acceso a páginas protegidas | Pendiente |
| T17 | Navegar por Inicio, Mis diseños y Mis solicitudes | Cada sección muestra solo los registros del cliente | Pendiente |
| T18 | Agregar y quitar un modelo de Favoritos | El cambio se guarda en MySQL y se refleja al volver a entrar | Pendiente |
| T19 | Actualizar nombre y correo desde Mi perfil | Los datos nuevos se muestran en la sesión y en MySQL | Pendiente |
| T20 | Cambiar contraseña desde Mi perfil | La clave anterior deja de funcionar y la nueva permite entrar | Pendiente |
| T21 | Abrir Configuración y utilizar sus accesos | Muestra el estado de cuenta y dirige a las secciones correctas | Pendiente |

La captura enviada es una **referencia de apariencia**, no evidencia de 243 usuarios ni 586 prendas. Las cifras de la interfaz se calculan desde la base instalada.
