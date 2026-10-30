# Pruebas de la navegación del administrador — V3

Accede con cuenta **administrador** y verifica lo siguiente. Marca solo cuando se haya probado en XAMPP.

- [ ] Inicio: tarjetas, gráfico, prendas populares y actividad. NO aparecen las tablas CRUD.
- [ ] Usuarios: tabla de usuarios, buscador y filtro de rol; Nuevo y Editar guardan en MySQL; bloquear/desbloquear funciona.
- [ ] Categorías: lista exclusiva; crear, editar, activar/desactivar; comprueba que se mantiene al recargar.
- [ ] Prendas: lista exclusiva; categoría, foto, crear, editar, activar/desactivar; prueba que el catálogo del diseñador se actualiza.
- [ ] Solicitudes: lista exclusiva; buscar, filtrar; revisar y modificar estado, verificar persistencia tras recargar.
- [ ] Reportes: seleccionar tipo y fechas; generar HTML, exportar CSV e imprimir o guardar como PDF desde navegador.
- [ ] Configuración: General, Seguridad, Base de datos, Notificaciones y Respaldo. Prueba guardar nombre y modificar contraseña.
- [ ] Cerrar sesión: vuelve al login; al abrir una dirección `?r=admin&seccion=...` sin sesión, se exige autenticación.
- [ ] La campana abre las solicitudes pendientes y el enlace muestra SOLO Solicitudes.
- [ ] En móvil, menú lateral pasa a navegación horizontal y las tablas se pueden desplazar horizontalmente.
- [ ] No aparece contenido de otros módulos al entrar en una opción del menú.
- [ ] Al actualizar una pestaña (`Ctrl+F5`), mantiene el módulo elegido.

**Nota:** El SQL solo se importa la primera vez que se instala; para actualizar desde V2 no lo importes de nuevo. Las pruebas con MySQL se harán en la computadora que usa XAMPP.
