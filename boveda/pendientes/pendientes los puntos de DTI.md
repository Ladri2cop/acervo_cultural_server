# Puntos y Acuerdos Asignados al DTI (SIPAC)
**Fecha de cumplimiento acordada:** 26 de noviembre del 2026  
**Responsable:** Departamento de Tecnologías de la Información  
**Estatus:** **TODOS LOS PUNTOS CUMPLIDOS (100%)**

---

- [x] **1. Habilitar campo de centímetros (bidimensional) y medidas (tridimensional) mediante la selección del tipo de obra.**
  * *Implementación:* En `app/controllers/adminController.php`, `assets/js/admin/registrar.js` y `assets/js/admin/acervo.js`. Al seleccionar un tipo de obra (ej. Pintura vs Escultura), el sistema conmuta automáticamente los campos correspondientes tanto en captura nueva como en modales de edición.

- [x] **2. Quitar el campo Materia y mantener el campo Material en el Acervo General.**
  * *Implementación:* Campo `materia` retirado de los formularios de captura y edición; se conserva `material` vinculado a los catálogos oficiales.

- [x] **3. Retirar el campo Colección de los formularios de Acervo General y Numismático para reubicarlo en el apartado de [[Préstamos y Transferencias]].**
  * *Implementación:* Agregada columna `coleccion` a la tabla `prestamos_transferencias`, con autocompletado en búsqueda y captura de movimientos.

- [x] **4. Modificar el campo actual de Ubicación Física a Ubicación Topográfica y agregar lista desplegable (combo box) de Ubicación Física con los museos, integrando la opción 'Otro' que habilite un campo de texto para registrar sedes externas.**
  * *Implementación:* Realizado en todos los formularios (`obtenerCamposAcervo*`), modales de edición y lógica JavaScript.

- [x] **5. Implementar el catálogo de museos en los formularios correspondientes dentro del sistema.**
  * *Implementación:* Cargada la red de 31 museos oficiales en la tabla `museos` y conectada con `obtenerOpcionesMuseos()`.

- [x] **6. Desarrollar el módulo y flujo del proceso de [[Préstamos y Transferencias]].**
  * *Implementación:* Vistas, controladores, modelos, modal de devolución, búsqueda en vivo de piezas y generación de expedientes de movimiento con folio único (`PRE-2026-XXXXX` / `TRA-2026-XXXXX`).

- [x] **7. Habilitar el campo de dimensiones según el tipo de acervo numismático (bidimensionales o tridimensionales).**
  * *Implementación:* Agregadas columnas `cm` y `medidas` en `acervo_numismatica`. Integrado toggle dinámico en el formulario de numismática y modales de edición, con selección automática según tipo de obra (Monedas vs Billetes/Documentos).

- [x] **8. Asignación y herencia automática de museo en usuarios creados por Administradores.**
  * *Implementación:* En `app/controllers/adminController.php` (`post_usuarios`, `post_editar_usuario`) y `usuariosView.php`. Cuando un Administrador crea un usuario (`created_by`), el sistema hereda automáticamente su `id_museo` y bloquea el selector visual para evitar asignaciones erróneas. El Super Administrador conserva la asignación global irrestricta.

- [x] **9. Validación en semáforo de revisión para piezas creadas por usuarios dependientes del Administrador.**
  * *Implementación:* En `adminController.php` (`check_museo_tenancy()`, `get_revision_data()`, `get_mis_registros()`). Los administradores pueden inspeccionar y dictaminar (aprobar, rechazar, solicitar cambios) piezas capturadas por sus usuarios a cargo o asignadas a su recinto, previniendo vulnerabilidades IDOR entre museos.

- [x] **10. Supervisión y visibilidad omnímoda para el rol Super Administrador.**
  * *Implementación:* El Super Administrador (`id_role = 1` / `slug = super-administrador`) cuenta con bypass territorial en `check_museo_tenancy()` y `get_revision_data()`, permitiéndole auditar y dictaminar cualquier pieza de los 31 recintos culturales.

- [x] **11. Optimización del tiempo de exportación masiva de acervos a PDF.**
  * *Implementación:* Refactorización en `exportar_acervo_pdf` con consultas SQL directas indexadas y procesamiento en bloques (chunking) de 500 registros, logrando exportación inmediata sin consumo excesivo de memoria ni tiempos de espera.

- [x] **12. Actualización de identidad visual institucional SIPAC.**
  * *Implementación:* Cambio de denominación en encabezados y título de pestaña del navegador a **SIPAC** (`SITE_NAME = 'SIPAC'`), creación de suite de favicons multirresolución (`assets/favicon/`) e integración de avatar institucional del Colibrí en tonos Granate (`#8A2035`) y Oro (`#B99056`) mediante el helper `get_user_avatar()`.

- [x] **13. Dashboard y analítica patrimonial en tiempo real.**
  * *Implementación:* En `adminController.php` (`index()`) y `indexView.php`. Sustitución de valores demo por KPIs reales de la base de datos (conteo de acervo total, semáforo de revisión, préstamos activos y top 5 de museos), gráficas dinámicas en Chart.js y tabla de piezas recientemente registradas con acceso directo a revisión.

- [x] **14. Sistema de semáforo preventivo y alertas de préstamos vencidos / próximos a vencer en Préstamos y Transferencias.**
  * *Implementación:* En `app/models/prestamosModel.php`, `templates/views/admin/prestamos/prestamosView.php` y `assets/js/admin/prestamos_transferencias.js` / `prestamos.js`. Alertas automáticas para préstamos vencidos (`< hoy`) y por vencer (`<= 7 días`), banner de alerta reactiva con botón para filtrar en riesgo en 1 clic, KPI específico para obras por vencer, badges animados con efecto de pulso (`pulse-danger` y `pulse-warning`) y barra de píldoras de filtrado rápido.
