# DiseñaTuPrenda

Sistema web para el diseño y personalización de prendas de vestir.

## Estructura
- `controladores/`: recibe las acciones y coordina el sistema.
- `modelos/`: contiene las consultas y operaciones con MySQL.
- `vistas/`: contiene las pantallas del sistema.
- `recursos/`: contiene CSS, JavaScript, imágenes y archivos subidos.
- `base_datos/`: contiene el esquema y actualizaciones SQL.
- `index.php`: único punto de entrada y enrutador.

## Instalación
1. Copia la carpeta del proyecto en `htdocs`.
2. Importa `base_datos/esquema.sql` desde phpMyAdmin.
3. Si ya tienes una base anterior, revisa `base_datos/actualizacion_favoritos_v4.sql` antes de ejecutarlo.
4. Configura MySQL mediante las variables `DTP_DB_HOST`, `DTP_DB_PORT`, `DTP_DB_NAME`, `DTP_DB_USER` y `DTP_DB_PASS`; por defecto usa `127.0.0.1`, `3306`, `disenatuprenda_db`, `root` y contraseña vacía.
5. Abre `http://localhost/DisenaTuPrenda/`.
