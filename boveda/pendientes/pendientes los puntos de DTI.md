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
