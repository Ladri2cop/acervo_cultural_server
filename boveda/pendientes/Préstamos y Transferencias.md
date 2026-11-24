# 🚚 Préstamos y Transferencias - Estado del Módulo

## Indicadores y KPIs Implementados:
- Total de movimientos
- **Pendientes de aprobación** (requieren dictamen de sede receptora)
- Préstamos activos
- Préstamos vencidos
- Préstamos devueltos
- Transferencias completadas

---

## ✅ Funcionalidades Implementadas (Noviembre 2026):

1. **Asignación Obligatoria de Museo para Administradores**:
   - Cada usuario con rol *Administrador* (`id_role = 2`) debe estar asignado obligatoriamente a algún museo de la red oficial de la Secretaría de Cultura y Turismo.
   - El *Super Administrador* (`id_role = 1`) no aplica restricción geográfica, teniendo jurisdicción y alcance global sobre todos los museos.
   - La gestión de usuarios valida dinámicamente este requisito tanto al crear como al editar usuarios.

2. **Carga Obligatoria de Oficio de Autorización**:
   - Todo registro de préstamo o transferencia requiere obligatoriamente adjuntar el archivo digital escaneado del oficio o memorándum de autorización (.pdf, .doc, .docx, .jpg, .png).
   - Los archivos se almacenan de forma segura en `assets/uploads/oficios_movimientos/` y se pueden descargar o visualizar desde la tabla y el expediente institucional.

3. **Flujo de Dictamen por la Sede Receptora (Aceptar / Declinar)**:
   - Todo nuevo movimiento nace en estatus `pendiente_aprobacion`.
   - **La pieza física no cambia de ubicación** en el catálogo oficial hasta que el movimiento sea formalmente aceptado.
   - El usuario Administrador asignado al museo de destino (o el Super Administrador) cuenta con las acciones para:
     - **Aceptar Movimiento**: Aprueba el traslado, cambia el estatus a `activo` (préstamo) o `completada` (transferencia), y reubica físicamente la pieza a la sede receptora.
     - **Declinar Movimiento**: Requiere captura obligatoria del motivo o justificación institucional del rechazo. El estatus cambia a `declinado` y la pieza permanece resguardada en su sede de origen.
   - Acciones disponibles tanto desde el dropdown de la tabla como en el footer del modal de expediente completo.


4. **Recuperación Automática de Origen y Alternancia Dinámica (Museo vs Otras Dependencias)**:
   - **Recuperación Automática de Origen**: Al seleccionar cualquier pieza del catálogo en el modal de nuevo movimiento, el sistema resuelve y autocompleta de inmediato su origen (cotejando con los 31 museos institucionales, sus acrónimos históricos como MAHEM, CCMB, etc., o movimientos previos), precargando el museo, el responsable y activando la insignia visual de origen recuperado.
   - **Alternancia Dinámica de Controles de Sede**:
     - Si se selecciona *"Museo Institucional"*, se muestra el combobox de museos registrados y se oculta el campo de texto libre.
     - Si se selecciona cualquier otra dependencia (*Unidad Administrativa SCyT*, *Área / Dirección*, *Otra Dependencia de Gobierno*), se oculta el combobox y se muestra el campo de texto libre para captura manual.
     - Esta alternancia opera tanto en la sección de Origen como en la de Destino con validaciones en tiempo real que impiden registrar un movimiento con origen y destino idénticos.

5. **Alcance Territorial y Filtrado de Visualización por Museo Asignado**:
   - **Administradores (`id_role = 2`)**: Únicamente pueden visualizar, consultar y gestionar los préstamos y transferencias donde el museo que tienen asignado participe como **origen** o como **destino**. Tanto la tabla de movimientos como los indicadores KPIs se filtran en tiempo real para reflejar exclusivamente la estadística de su recinto. Intentos de acceso directo a expedientes de otros recintos son bloqueados con código de seguridad 403.
   - **Super Administradores (`id_role = 1`)**: Mantienen jurisdicción global, pudiendo auditar y gestionar el 100% de los movimientos y KPIs de toda la red estatal de museos.

6. **Combo Box de Colecciones con Recuperación Automática en Modal de Movimiento**:
   - El campo de colección en el modal de registro de movimiento se sustituyó por un **combo box dinámico** que agrupa y normaliza las 317 colecciones registradas en la plataforma (`PrestamosModel::getColeccionesDisponibles()`).
   - Al seleccionar la pieza del catálogo a prestar o transferir, el sistema recupera y selecciona automáticamente la colección que ya tenía la obra en su ficha técnica, visualizándose con el badge *Colección recuperada*.
   - Si la pieza cuenta con una colección histórica no listada, se agrega como opción al combo box de forma automática.
   - Permite al usuario cambiarla libremente o seleccionar `-- Otra colección (especificar) --` para capturar una denominación personalizada.
