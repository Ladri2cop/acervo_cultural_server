---
title: Bitácora de Versiones y Changelog
type: reference
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - changelog
  - versiones
  - historial
---

# 📜 Bitácora de Versiones y Changelog

Registro cronológico de lanzamientos, nuevas funcionalidades, parches de seguridad y correcciones de interfaz aplicadas al **Sistema de Gestión de Acervo Cultural**.

---

## [Versión 2.0.0] - 2026-11-26
### 📥 Importador Masivo de Acervo por Excel (.xlsx) y CSV (.csv)
- **Nuevo Módulo de Importación Masiva (`admin/importar`)**:
  - Panel unificado para la carga por lotes de piezas patrimoniales pertenecientes a los tres fondos del acervo:
    1. **🏛️ Acervo General** (`acervo_general` - Módulo 5): 19 columnas de datos que abarcan artes plásticas, utilitarias, mobiliario, pintura, escultura, medidas bidimensionales y tridimensionales.
    2. **🏺 Acervo Arqueológico** (`acervo_arqueologico` - Módulo 3): 21 columnas con claves de inventario SCyT, registro INAH, sitio de procedencia, formas y técnicas de manufactura.
    3. **🪙 Acervo Numismático** (`acervo_numismatica` - Módulo 4): 16 columnas con denominación facial, tipo de obra, metales, cecas, ensayadores y descripciones de anverso y reverso.
- **Motor Especializado `AcervoImportador` (`app/classes/AcervoImportador.php`)**:
  - Generación de plantillas oficiales con encabezados estilizados institucionales en color guinda (`#6B1D2F`), fila secundaria con claves técnicas de base de datos, fila terciaria con instrucciones y ejemplos reales del patrimonio mexiquense.
  - Exportación en `.xlsx` y `.csv` con firma UTF-8 BOM (`\xEF\xBB\xBF`) para compatibilidad nativa con Microsoft Excel en Windows sin distorsión de caracteres o acentos.
  - Normalizador inteligente de columnas capaz de mapear tanto claves técnicas directas como sinónimos amigables en español.
- **Detección Avanzada de Duplicados e Integridad Relacional**:
  - Detección de colisiones contra claves de inventario existentes (`no_inventario`, `codigo_interno`, `no_inventario_scyt`, `no_registro_inah`) y verificación intra-archivo.
  - Tres directivas configurables por el usuario:
    - `Omitir duplicados` (*skip*): ignora registros preexistentes sin interrumpir la carga y los reporta como advertencia.
    - `Actualizar existentes` (*update*): actualiza columnas no vacías de la pieza existente preservando su identificador primario.
    - `Rechazar fila` (*error*): clasifica la coincidencia como inconsistencia en la tabla de resultados.
- **Integración con el Flujo de Calidad Curatorial**:
  - Por defecto, toda pieza importada nace con `estatus_revision = 'pendiente'` y `status = 0`, canalizándose a la Mesa de Revisión para dictamen curatorial previo a su visibilidad pública.
  - Opción de aprobación directa e inmediata (`estatus_revision = 'aprobado'`, `status = 1`) disponible para Super Administradores y dictaminadores.
- **Corrección en Modal de Visualización ("Ver Pieza Numismática")**:
  - Se corrigió la discrepancia de dimensiones en `modalVerPieza` para acervo numismático: ahora refleja explícitamente el badge `Tipo de Dimensiones` (*Tridimensional* o *Bidimensional*), mostrando los campos *Centímetros (Bidimensional)* y *Medidas (Tridimensional)* conforme al formulario de edición.
  - Se eliminó el desbordamiento de texto (*text overflow*) agregando auto-expansión vertical (`height: auto !important`, `min-height: 38px`, `word-break: break-word`, `overflow-wrap: anywhere`) a los contenedores `.form-control` del modal, impidiendo que textos largos (como medidas compuestas con peso y grosor) se salgan de los bordes del campo.
- **Seguridad Multimuseo y Transaccionalidad**:
  - Aislamiento de sede (tenancy): los usuarios administradores sólo pueden importar piezas asignadas a su museo custodio.
  - Ingesta atómica con transacciones PDO (`beginTransaction` / `commit` / `rollBack`), previniendo estados inconsistentes.
  - Nuevo permiso RBAC `importar_acervo` (ID 36) asignado a roles Super Administrador y Administrador.
  - Helper `user_can_import_acervo()` y enlace dinámico en el menú lateral (`sidebar.php`).
  - Bitácora forense: registro automático de eventos `IMPORT_BATCH` en la tabla `auditoria` con métricas de piezas procesadas.

---

## [Versión 1.9.6] - 2026-11-25
### 🛡️ Borrado Lógico Puro y Restauración en Catálogos Maestros (Zero Physical Delete)
- **Eliminación del campo y botón "Desactivar"**:
  - Se eliminó el interruptor switch de estatus del modal de alta/edición de registros.
  - Se removió el botón individual de "desactivar/activar" de la botonera de acciones de la tabla.
  - Al dar de alta un registro, este nace automáticamente con estatus `status = 1` (Activo).
- **Mecanismo de Borrado Lógico (Soft Delete)**:
  - La acción de eliminar (`.btn-delete-item` / `admin/post_eliminar_catalogo`) fue reconfigurada como **baja lógica pura**: conmuta de forma segura `status` de 1 a 0 en la base de datos (`UPDATE ... SET status = 0, updated_at = NOW()`).
  - **Cero borrado físico (`DELETE FROM`)**: ningún dato o registro patrimonial se elimina físicamente de la base de datos, garantizando al 100% la preservación de la memoria histórica y la consistencia relacional con el acervo (`acervo_general`, `acervo_arqueologico`, `acervo_numismatica`, `prestamos_transferencias`, `bee_users`).
  - Los registros con `status = 0` se ocultan de manera automática e inmediata de los formularios de captura de nuevas piezas en el sistema (mediante filtro nativo `WHERE status = 1`), pero se preservan en todas las piezas registradas previamente.
- **Acción y Endpoint de Restauración Inmediata**:
  - Para registros en estatus `status = 0` (etiquetados como *Eliminado*), la tabla ofrece un botón de restauración (`.btn-restore-item` con icono `bx bx-undo` y estilo verde).
  - Nuevo endpoint seguro `adminController::post_restaurar_catalogo()` y método de modelo `catalogosModel::restaurar($slug, $id)` que conmuta `status = 1`, reactivando la disponibilidad del elemento.
- **Interfaz y Filtros Reconfigurados**:
  - Insignia de estatus: `Activo` (`badge-status-active`, verde) y `Eliminado` (`badge-status-deleted`, rojo pastel).
  - Píldoras de filtrado y KPI en cabecera actualizados: *Todos*, *Activos* y *Eliminados* (reemplazando "Inactivos").
  - Diálogos de confirmación SweetAlert2 didácticos que explican que el registro pasa a baja lógica y puede ser restaurado.
- **Auditoría Forense Completa**:
  - Registro forense con eventos `ELIMINAR_CATALOGO` (baja lógica `status 0`) y `RESTAURAR_CATALOGO` (reactivación `status 1`), almacenando snapshots `datos_antes` y `datos_despues` en la tabla `auditoria`.

---

## [Versión 1.9.5] - 2026-11-25
### 📚 Gestión Administrativa de Catálogos Maestros (CRUD Centralizado)
- **Módulo Centralizado de Catálogos Maestros (`admin/catalogos`)**:
  - Arquitectura unificada para la gestión integral de los 8 catálogos oficiales del patrimonio cultural:
    1. 🏛️ **Museos y Recintos Culturales** (`museos`): 31 recintos oficiales de la Secretaría de Cultura y Turismo.
    2. 🎨 **Materiales** (`materiales`): 112 materias primas catalogadas.
    3. 🖌️ **Técnicas de Manufactura y Arte** (`tecnicas`): 167 técnicas artísticas y arqueológicas.
    4. ⏳ **Épocas y Periodos Históricos** (`epocas_periodos`): 14 periodos históricos y siglos asociados.
    5. 🖼️ **Tipos de Obra** (`tipo_obras`): 20 tipologías para conmutación de dimensiones bidimensional/tridimensional.
    6. 🛡️ **Estados de Conservación** (`estado_conservacion`): Bueno, Regular y Malo.
    7. 📦 **Colecciones** (`colecciones`): Fondos patrimoniales y agrupaciones temáticas.
    8. 👤 **Autores y Culturas** (`autor_cultura`): Creadores, artistas y civilizaciones prehispánicas.
- **Representación y Clasificación Temática en Catálogos (Categorías y Disciplinas)**:
  - Adición y persistencia de la columna `categoria` en las tablas `materiales`, `tecnicas` y `tipo_obras`.
  - Mapeo institucional conforme a la taxonomía oficial de la Secretaría de Cultura:
    - **Materiales**: *Materiales Pictóricos (65), Materiales para Culturas Populares (12), Materiales para Ciencias Naturales (6), Arqueológico (22), Numismática (7)*.
    - **Tipos de Obra**: *Bidimensional (Pintura, Dibujo, Grabado, Fotografía, Acuarela, Textil), Tridimensional (Escultura, Mobiliario, Cerámica, Objeto, Otro), Numismática (Monedas, Billetes, Medallística, etc.)*.
    - **Técnicas**: *Técnicas Pictóricas, Técnicas de Culturas Populares, Técnicas para Ciencias Naturales, Manufactura Arqueológica, Técnicas Decorativas Arqueológicas*.
  - Barra superior interactiva de píldoras de filtrado rápido por categoría (`btn-cat-pill`) con conteo de registros por disciplina en tiempo real.
  - Insignias temáticas con códigos de color diferenciados en la tabla y selector obligatorio de categoría en los modales de alta y edición.
  - Agrupación automática mediante `<optgroup>` en los selectores de captura del sistema.
- **Protección de Integridad Relacional y Prevención de Daño a Inventarios**:
  - Algoritmo defensivo `catalogosModel::contarUsoRelacional()` que inspecciona el uso de cada elemento en piezas (`acervo_general`, `acervo_arqueologico`, `acervo_numismatica`), usuarios (`bee_users`) y préstamos (`prestamos_transferencias`) antes de permitir cualquier borrado.
  - Bloqueo preventivo de eliminación si el registro se encuentra vinculado a piezas o usuarios existentes, recomendando el apagado lógico (estatus inactivo) para preservar la trazabilidad histórica sin romper registros previos.
- **Operaciones CRUD en Tiempo Real**:
  - Alta y edición modal con validación estricta de duplicados y campos obligatorios.
  - Conmutador rápido de estatus (Activo / Inactivo) con badges visuales (`badge-status-active` / `badge-status-inactive`).
  - Filtrado en vivo instantáneo por palabra clave y selector de estatus (*Todos, Activos, Inactivos*).
- **Trazabilidad y Auditoría Forense**:
  - Registro inmutable en la tabla `auditoria` con acciones `CREAR_CATALOGO`, `ACTUALIZAR_CATALOGO`, `CAMBIO_ESTATUS_CATALOGO` y `ELIMINAR_CATALOGO`, capturando snapshots completos de `datos_antes` y `datos_despues`.
- **Integración RBAC y Navegación**:
  - Nuevo permiso oficial `gestion_catalogos` (ID 35) en `bee_permisos` y asignado a Super Administrador y Administrador.
  - Helper de autorización `user_can_manage_catalogos()` en `bee_custom_functions.php`.
  - Acceso directo en el menú lateral institucional (`sidebar.php`) bajo la sección de **Gestión**.
- **Alimentación Dinámica de Selectores en Formularios (`acervo_catalogos.php`)**:
  - Actualización de los helpers `obtenerOpcionesEpocas()`, `obtenerOpcionesEstadoConservacion()` y `obtenerOpcionesTiposGeneral()` para consultar directamente los registros activos (`status = 1`) de la base de datos, con tolerancia a fallos mediante respaldo estático.

---

## [Versión 1.9.4] - 2026-11-25
### 🛡️ Ampliación Integral del Esquema RBAC (26 Permisos) y Nueva Interfaz de Roles y Permisos
- **Ampliación de la Matriz Oficial de Permisos (`bee_permisos`)**:
  - Incorporación a base de datos de los 11 permisos faltantes para completar la cobertura total de operaciones del sistema (26 permisos en total):
    - **Revisión y Dictamen**: `ver_revision_acervo`, `dictaminar_acervo`, `ver_mis_registros`.
    - **Préstamos y Transferencias**: `ver_prestamos_transferencias`, `registrar_prestamo_transferencia`, `dictaminar_prestamo_transferencia`, `devolver_prestamo`, `cancelar_prestamo_transferencia`.
    - **Gestión Institucional y Auditoría**: `gestion_usuarios`, `gestion_roles`, `ver_auditoria`.
  - Mapeo institucional en `bee_roles_permisos`: **Super Administrador** asignado con los 26 permisos; **Administrador** asignado con 24 permisos (protección de facultades raíz `admin-access` y `gestion_roles`).
  - Aplicación de restricción de unicidad `uq_role_permiso (id_role, id_permiso)` para garantizar consistencia referencial y prevenir duplicidades.
  - **Eliminación del Bypass Heredado de `is_admin_user()` en RBAC**:
    - Se identificó y resolvió que `is_admin_user()` (que abarca tanto a Super Admin como a Administrador) estaba colocado como bypass previo en `user_can()` y en los helpers de autorización (`user_can_view_auditoria()`, `user_can_access_prestamos()`, etc.).
    - Se refactorizó la lógica para que **únicamente `is_super_admin()` (Rol ID 1)** mantenga bypass global; para todos los demás roles (incluyendo **Administrador ID 2**), la verificación se ejecuta de manera 100% dinámica contra `bee_roles_permisos`.
    - Al revocar permisos (p. ej. `ver_auditoria` o `ver_prestamos_transferencias`) a un rol desde la interfaz de Roles y Permisos, los módulos correspondientes desaparecen de inmediato del menú lateral y bloquean las rutas con acceso denegado.
- **Rediseño Estructurado de la Vista de Roles (`rolesView.php`)**:
  - Eliminación de la columna técnica de ID en la tabla de roles registrados, optimizando el espacio y focalizando la visualización en el nombre del rol, conteo de permisos asignados y fecha de creación.
  - Categorización visual y lógica de los permisos en 7 grupos temáticos (*Acervo General, Acervo Arqueológico, Acervo Numismático, Revisión y Dictamen, Préstamos y Transferencias, Reportes y Exportación, Gestión y Auditoría*).
  - Encabezados con iconos, badges de conteo y botones de selección rápida por categoría (`btn-toggle-cat`).
  - Botones globales de acción masiva **"Todos"** y **"Ninguno"** tanto en el formulario de alta de nuevo rol como en el modal de edición.
- **Protección y Restricción del Rol Administrador General (`#13` / `administrador-general`)**:
  - El rol de Administrador General queda reservado exclusivamente para visualización y asignación por parte del **Super Administrador** al dar de alta o editar usuarios en `admin/usuarios`.
  - Para administradores regulares o funcionarios de museos, el selector de roles excluye automáticamente tanto al Super Administrador como al Administrador General, con validación de rechazo estricta en el controlador backend (`post_usuarios` y `post_editar_usuario`).
- **Funciones Helper RBAC y Protección Perimetral**:
  - Nuevas funciones de verificación granular en `app/functions/bee_custom_functions.php`: `user_can_access_revision()`, `user_can_dictate_acervo()`, `user_can_access_prestamos()`, `user_can_register_prestamos()`, `user_can_dictate_prestamos()`, `user_can_return_prestamos()`, `user_can_cancel_prestamos()`, `user_can_manage_users()`, `user_can_manage_roles()`, `user_can_view_auditoria()`.
  - Protección de acceso a nivel de controlador (`adminController.php`) en `roles()`, `post_roles()`, `post_editar_rol()`, `borrar_rol()`, `usuarios()`, `post_usuarios()`, `post_editar_usuario()`, `borrar_usuario()`, `auditoria()`, `revision()`, `get_revision_data()`, `autorizar_pieza()`, `solicitar_cambios_pieza()`, `rechazar_pieza()`, `prestamos_transferencias()`, `post_crear_movimiento()`, `post_aceptar_movimiento()`, `post_declinar_movimiento()`, `post_devolver_prestamo()`, `post_cancelar_movimiento()`.
  - Filtrado condicional en `templates/includes/admin/sidebar.php` según los permisos específicos del rol en sesión.

---

## [Versión 1.9.3] - 2026-11-25
### ⏱️ Semáforo Institucional de Plazos y Alertas de Préstamos en Riesgo
- **Lógica Centralizada de Semáforo Temporal (`PrestamosModel::enriquecerSemaforo`)**:
  - Evaluación cronológica automática de préstamos temporales activos contra la fecha actual (`CURDATE()`):
    - 🔴 **Vencido**: `fecha_devolucion_estimada < hoy`. Calcula días de retraso (`dias_vencido`) y activa animación `pulse-danger`.
    - 🟡 **Por Vencer**: `fecha_devolucion_estimada <= hoy + 7 días`. Calcula cuenta regresiva de días restantes (`dias_restantes`) y activa animación `pulse-warning`.
    - 🟢 **En Tiempo**: `fecha_devolucion_estimada > hoy + 7 días`. Despliega días de vigencia con estatus activo regular.
    - 🔵 **Devuelto**: Conserva trazabilidad de fecha real de restitución de la obra.
- **Nuevos Indicadores Clave (KPIs) y Filtrado Avanzado**:
  - Indicador numérico dedicado para **Próximos a Vencer (≤7d)** (`#kpi-por-vencer`).
  - Soporte de filtros específicos en backend y frontend para `por_vencer`, `vencido`, `en_riesgo` (vencidos o por vencer combinados) y `en_tiempo`.
- **Banner Dinámico de Alerta Preventiva**:
  - Componente de alerta inteligente (`#banner-alerta-vencimientos`) que se activa automáticamente al existir obras con retraso o próximas a vencer en la jurisdicción del usuario (sede asignada o red global para Super Administrador).
  - Botón de acción inmediata **"Filtrar en Riesgo"** para aislar en 1 clic los préstamos que requieren gestión o requerimiento formal.
- **Píldoras de Acceso Rápido y Sincronización Bidireccional**:
  - Barra de botones rápidos (`.btn-filtro-rapido`) para alternar instantáneamente entre *Todos, Pendientes, Activos, Por Vencer, Vencidos, En Riesgo, Devueltos y Transferencias*.
  - Sincronización automática entre clics en tarjetas KPI, botones de píldoras y selectores desplegables.
- **Detalle y Expediente del Movimiento**:
  - Reflejo del estado del semáforo con los días exactos de vencimiento o tiempo restante en el modal de expediente (`mostrarDetalleMovimiento`).
- **Visor Modal Integrado de Oficios Adjuntos (`modalVisorOficio`)**:
  - Eliminación de la descarga forzada o automática al hacer clic en *"Ver Oficio Adjunto"*.
  - Despliegue en modal interactivo con visualización nativa de archivos PDF e imágenes en alta resolución, y opción de descarga controlada mediante el botón *"Descargar Oficio"*.

---

## [Versión 1.9.2] - 2026-11-25
### 📊 Panel de Control (Dashboard) en Tiempo Real con Indicadores Patrimoniales y Gráficas Dinámicas
- **Conexión Directa a la Base de Datos (`adminController::index`)**:
  - Sustitución de datos ficticios y estáticos de la plantilla SB Admin 2 por consultas SQL optimizadas de alta velocidad (<270 ms para más de 39,540 piezas patrimoniales).
  - Cálculo en tiempo real de piezas totales, inventario oficial aprobado (semáforo verde), piezas en dictamen (pendientes y cambios solicitados) y movimientos activos de préstamos/transferencias.
- **Gráficas Analíticas con Chart.js**:
  - **Tendencia de Registro Mensual**: Gráfica de área dinámica con el volumen mensual real de piezas catalogadas e incorporadas al padrón durante los últimos meses.
  - **Distribución Patrimonial**: Gráfica de dona con proporciones y conteos exactos por colección (*Acervo General*, *Arqueológico* y *Numismático*) utilizando la paleta institucional (Granate `#8A2035`, Oro `#B99056` y Azul `#2e59d9`).
- **Eliminación de Contenido Simulado y Nuevos Componentes Operativos**:
  - Eliminación definitiva de bloques de plantilla heredados (*"Lorem ipsum"*, ilustraciones de prueba y enlaces ciegos).
  - **Actividad Patrimonial Reciente**: Tabla interactiva con las últimas piezas registradas o dictaminadas, mostrando miniatura, código interno, título, colección, museo asignado y badge de semáforo.
  - **Top 5 Museos con Mayor Acervo**: Ranking con barras de progreso dinámicas que reflejan las sedes y recintos con mayor concentración de bienes culturales.
  - **Semáforo Global del Inventario**: Desglose porcentual y numérico del estado del inventario (*Aprobado, Pendiente, Cambios Solicitados, Rechazado*) con acceso directo a la estación de revisión.

---

## [Versión 1.9.1] - 2026-11-25
### 🔑 Módulo de Autoservicio de Perfil, Cambio de Contraseña y Saneamiento del Topbar
- **Gestión Autónoma de Seguridad y Contraseña (`/admin/perfil`)**:
  - Implementación del formulario interactivo de **Cambio de Contraseña** con validación en dos capas (JavaScript en cliente y PHP en backend).
  - Verificación estricta de la contraseña actual del usuario mediante `password_verify()` combinado con la constante criptográfica `AUTH_SALT`.
  - Requisito de longitud mínima de 8 caracteres, validación en tiempo real de coincidencia entre nueva contraseña y confirmación, y restricción que impide reutilizar la misma clave actual.
  - Generación de nuevo hash con `PASSWORD_BCRYPT` y registro formal en la bitácora de auditoría forense (`registrar_auditoria`).
  - Botones conmutadores de visibilidad (mostrar/ocultar mediante ícono de ojo) para los tres campos de contraseña.
- **Actualización de Datos de Contacto de Perfil**:
  - Endpoint `post_actualizar_perfil()` para modificación del correo institucional con validación de sintaxis RFC y verificación de no duplicidad en la tabla `bee_users`.
  - Sincronización inmediata de la variable de sesión `$_SESSION['user_session']['email']`.
- **Saneamiento y Optimización del Topbar (`topbar.php`)**:
  - Eliminación de enlaces muertos de la plantilla SB Admin 2 (`href=""` en "Configuración" y "Actividad").
  - Menú de usuario contextualizado: acceso directo a *Mi Perfil*, salto directo a *Seguridad y Contraseña* (`admin/perfil#seguridad`), acceso a *Bitácora de Auditoría* para administradores o *Mis Registros* para capturistas, y modal de cierre de sesión con rutas absolutas sanitizadas.

---

## [Versión 1.9.0] - 2026-11-25
### 🛡️ Identidad Institucional SIPAC, Herencia Territorial RBAC, Dictamen de Piezas y Transaccionalidad
- **Identidad Oficial SIPAC y Presencia Institucional**:
  - **Renombramiento oficial**: Cambio del nombre del sitio a **SIPAC** (*Sistema de Inventario Para el Acervo Cultural*) en `app/core/settings.php`. Los títulos de las pestañas en el navegador ahora se despliegan formalmente como `<title>[Sección] - SIPAC</title>`.
  - **Avatares Institucionales de Usuario**:
    - Creación y despliegue del avatar oficial institucional en alta resolución (`512x512` px) con los colores de identidad del Gobierno del Estado de México: fondo circular en **Granate Institucional** (`#8A2035`), marco metálico en **Dorado** (`#B99056`) y el emblema oficial del **Colibrí** en blanco nítido.
    - Sustitución definitiva del avatar genérico previo (`bee.png`) por la función helper `get_user_avatar()`.
    - Integración del avatar en la barra superior (`topbar.php`), en el catálogo de usuarios (`usuariosView.php`) y en la tarjeta de presentación de perfil de usuario (`perfilView.php`).
  - **Paquete de Favicons Multirresolución Versionados**:
    - Generación de `favicon.ico` multirresolución (16x16, 32x32, 48x48, 64x64, 128x128, 256x256), `favicon-16x16.png`, `favicon-32x32.png`, `apple-touch-icon.png` (180x180) y `favicon.png` (192x192).
    - Inyección optimizada en encabezados HTML mediante `get_favicon()` con etiqueta de versión `?v=2.1` para forzar la actualización instantánea en la memoria caché del navegador.
- **Herencia Territorial Automática en Usuarios Dependientes**:
  - Actualización retroactiva en base de datos: Todo usuario dependiente creado por un administrador con museo asignado adquiere automáticamente el `id_museo` de su administrador creador (ej. usuario `capturista` `id=6` asignado al Museo de Numismática `id=20`).
  - Lógica en backend: En `post_usuarios()` y `post_editar_usuario()` de `adminController.php`, si el usuario que registra o edita es un administrador de museo (no Super Admin), el sistema hereda de manera forzosa su `id_museo`, impidiendo la creación o existencia de capturistas huérfanos sin recinto asignado.
  - Interfaz de administración de usuarios (`usuariosView.php`): Para administradores de museo, el selector manual se bloquea y se sustituye por una insignia informativa que muestra el recinto institucional adscrito, transmitiendo el valor mediante un campo oculto seguro.
- **Validación y Dictamen Curatorial por Administradores de Museo**:
  - Ampliación del modelo de tenencia en `check_museo_tenancy()`: Un administrador ahora puede consultar, inspeccionar, autorizar (`autorizar_pieza`), solicitar correcciones (`solicitar_cambios_pieza`) y rechazar (`rechazar_pieza`) piezas creadas por él mismo, piezas capturadas o editadas por usuarios creados bajo su tutela (`created_by`), o pertenecientes a usuarios de su mismo recinto (`id_museo`).
  - Protección estricta contra ataques de referencia indirecta a objetos (IDOR): Se bloquea con código HTTP `403 Forbidden` cualquier intento de validación o dictamen cruzado entre administradores de recintos diferentes.
  - Alcance Global de Super Administrador: El rol de Super Administrador (`id_role = 1` o slug `super-administrador`) goza de facultades irrestrictas sobre todos los recintos, usuarios y piezas de Acervo General, Arqueológico y Numismático a nivel estatal.
  - Integración en "Mis Registros en Revisión": Los administradores pueden consultar de forma unificada en la pestaña correspondiente tanto sus piezas como las capturadas por su equipo de capturistas.
- **Optimización Extrema de la Estación de Revisión (`/admin/revision`)**:
  - Reducción drástica del tiempo de carga de la bandeja y cálculo de KPIs de **más de 26 segundos a menos de 100 milisegundos**.
  - Puesta en marcha de *WHERE pushdown* en las subconsultas de la unión SQL (`acervo_general`, `acervo_arqueologico`, `acervo_numismatica`), evitando escaneos completos no indexados de más de 39,540 registros patrimoniales.
- **Robustez y Atomización de Transacciones PDO (`app/classes/Db.php`)**:
  - **Eliminación de transacciones colgantes**: Corrección del comportamiento de `Db::query()` que abría transacciones automáticas en consultas de tipo `SELECT` y las dejaba sin confirmar, provocando la excepción `PDOException: There is already an active transaction` en solicitudes posteriores.
  - **Soporte de transacciones anidadas seguras**: Introducción de la bandera `$weStartedTransaction` para asegurar que consultas internas (como el registro de auditoría `registrar_auditoria`) no confirmen ni reviertan de forma prematura transacciones abiertas en controladores (`autorizar_pieza`, `PrestamosModel`).
  - Verificación defensiva `if (!$db->inTransaction())` e `if ($db->inTransaction())` en métodos de dictamen y persistencia.
- **Optimización de Exportación a PDF de Acervos**:
  - Aceleración radical del tiempo de respuesta en la exportación de catálogos mediante streaming directo con SQL puro y procesamiento en lotes de 500 registros, preservando íntegramente el diseño, tipografía, cabeceras institucionales y formato oficial del documento.

---

## [Versión 1.8.0] - 2026-11-24
### 🔒 Seguridad de Sesión y Combo Box Dinámico de Colecciones en Préstamos
- **Política Estricta de Expiración por Inactividad (30 Minutos)**:
  - Implementación de `SESSION_TIMEOUT = 1800` (1,800 segundos / 30 minutos) en `settings.php`.
  - **Desactivación definitiva de sesiones persistentes semanales**: Configuración de `BEE_COOKIES = false` y remoción forzosa de cookies `bee__cookie_id` y `bee__cookie_tkn` en el navegador del cliente mediante encabezados de caducidad en el pasado.
  - El usuario que permanezca inactivo por más de 30 minutos es expulsado de inmediato hacia `/login?timeout=1` al intentar cualquier interacción o recargar el navegador (F5), requiriendo forzosamente reingresar credenciales.
  - Registro auditable del evento `LOGOUT` por inactividad en la tabla `auditoria`.
- **Monitor Global de Actividad en Frontend (`admin.js`)**:
  - Detección de eventos de interacción (`mousemove`, `mousedown`, `keydown`, `scroll`, `touchstart`).
  - Sincronización multi-pestaña mediante `localStorage` (`sipac_last_active_time`).
  - Heartbeat periódico cada 5 minutos mediante endpoint `GET /admin/ping_session` únicamente si el usuario registró actividad.
  - Alerta modal interactiva a los 28 minutos de inactividad con SweetAlert2 y temporizador regresivo de 120 segundos para renovar sesión o salir voluntariamente.
  - Cierre automático y redirección forzada a los 30 minutos sin actividad.
  - Interceptor global de errores HTTP `401 Unauthorized` para redirección automática en llamadas AJAX.
  - Implementación de *cache-buster* `admin.js?v=...` en `footer.php` para asegurar la descarga del script más reciente en el navegador.
- **Combo Box Dinámico de Colecciones con Recuperación Automática**:
  - Reemplazo del campo de texto libre de colección en el modal de nuevo movimiento (`#modalNuevoMovimiento`) por un combo box `<select name="coleccion" id="select_coleccion">`.
  - Creación del método `PrestamosModel::getColeccionesDisponibles()` que consolida, deduplica y clasifica alfabéticamente las 317 colecciones registradas en el sistema (tabla `colecciones`, `acervo_general`, `acervo_arqueologico` y `prestamos_transferencias`).
  - **Recuperación Automática de la Colección**: Al seleccionar cualquier pieza del catálogo cultural, el sistema recupera la colección que ya tenía la obra y la selecciona de inmediato en el combo box, activando la insignia visual *Colección recuperada*.
  - Si la pieza contiene una colección histórica no listada, el sistema la inyecta dinámicamente como opción del combo box y la selecciona.
  - Opción de selección libre `-- Otra colección (especificar) --` con campo de texto emergente si se requiere capturar una denominación inédita.
- **Armonización de Iconografía Institucional**:
  - Actualización de iconos en el sidebar y vistas operativas:
    - *Revisión de Acervo*: Icono `bx-clipboard-check`.
    - *Préstamos y Transferencias*: Icono `bx-swap-horizontal`.

---

## [Versión 1.7.0] - 2026-11-23
### 🚚 Dictamen de Préstamos y Transferencias por Museo Receptora y Carga de Oficios
- **Asignación de Usuarios Administradores a Museos de la Red**:
  - Incorporación del campo `id_museo` en `bee_users` con llave foránea a `museos(id_museo)`.
  - Exigencia estricta de asignación de museo para el rol *Administrador* (`id_role = 2`) en formularios de creación y edición de usuarios.
  - El *Super Administrador* (`id_role = 1`) queda exento de asignación fija con alcance global sobre toda la red.
- **Flujo de Dictamen Institucional (Aceptar / Declinar)**:
  - Todo movimiento de préstamo o transferencia se registra inicialmente con estatus `pendiente_aprobacion`.
  - **Inmovilidad preventiva**: La pieza no se traslada físicamente en el catálogo hasta que la sede de destino autorice el movimiento.
  - Validación de permisos RBAC territorial: Solo el administrador del museo destino (`id_museo_destino`) o el Super Administrador pueden emitir dictamen.
  - Acción de **Aceptar Movimiento**: Concreta el préstamo (`activo`) o transferencia (`completada`), actualizando la ubicación física de la obra al nuevo recinto.
  - Acción de **Declinar Movimiento**: Requiere motivo institucional obligatorio (`motivo_declinacion`), pasa a estatus `declinado` y la obra permanece en su sede original.
- **Carga Obligatoria de Oficios Digitales**:
  - Requisito obligatorio de adjuntar el archivo digital del oficio de autorización (.pdf, .doc, .docx, .jpg, .png) al momento de registrar el movimiento.
  - Almacenamiento seguro en `assets/uploads/oficios_movimientos/` y visualización/descarga directa desde la tabla y el expediente.
- **Expediente Institucional Enriquecido y KPIs**:
  - Nuevo indicador KPI para movimientos **Pendientes de Aprobación**.
  - Visualización del dictamen oficial con fecha, funcionario responsable y justificación de rechazo en caso de declinación.
- **Recuperación Automática de Origen y Alternancia Dinámica de Sede**:
  - Detección y resolución automática del origen (museo o sede externa) al seleccionar una pieza en el modal de movimiento, precargando el museo, responsable e indicando confirmación visual.
  - Alternancia dinámica en Origen y Destino: Si se elige *"Museo Institucional"*, se despliega el combobox de museos registrados y se oculta el texto libre; si se selecciona otra dependencia, se oculta el combobox y se muestra el campo de texto libre para captura manual.
  - Validación preventiva que impide registrar movimientos con origen y destino en el mismo museo.
- **Filtro de Alcance Territorial por Museo Asignado**:
  - Los usuarios *Administradores* (`id_role = 2`) solo pueden ver y gestionar movimientos (préstamos y transferencias) donde su museo asignado sea origen o destino.
  - Los indicadores KPIs se recalculan dinámicamente reflejando las métricas territoriales de su recinto.
  - Protección de seguridad en endpoints (`get_detalle_movimiento`, devoluciones y cancelaciones) impidiendo la consulta o manipulación de movimientos de recintos ajenos.

---

## [Versión 1.6.0] - 2026-11-17
### 🏛️ Implementación de Catálogos Oficiales y Acuerdos de Minuta 03 (SIPAC)
- **Catálogos Oficiales de Curaduría y Patrimonio (`ÉPOCA, TÉCNICAS Y MATERIALES.docx`)**:
  - Población y normalización completa de las tablas `museos` (31 recintos oficiales), `epocas_periodos` (14 épocas históricas), `estado_conservacion` (Bueno, Regular, Malo), `tipo_obras` (20 clasificaciones), `materiales` (112 materiales) y `tecnicas` (167 técnicas clasificadas).
  - Creación del módulo central de opciones `app/functions/acervo_catalogos.php` con funciones seguras y generador de opciones `renderizarOpcionesSelect()`.
- **Soporte de Agrupación `<optgroup>` en BeeFormBuilder**:
  - Actualización del motor `BeeFormBuilder.php` para soportar de manera nativa arreglos multidimensionales con `<optgroup label="...">`, habilitando la visualización jerárquica de 180 formas arqueológicas (7 grupos) y técnicas especializadas.
- **Conmutación Inteligente de Dimensiones por Tipo de Obra**:
  - Acervo General: Al elegir el tipo de obra (Pintura, Escultura, etc.), el sistema preselecciona automáticamente entre *Bidimensional* (`cm`) y *Tridimensional* (`medidas`) tanto en registro nuevo como en edición.
  - Acervo Numismático: Incorporación de campos `cm` y `medidas` en tabla `acervo_numismatica`, con alternancia automática según el tipo de bien (Monedas/Medallas vs Billetes/Documentos/Filatelia).
- **Módulo de Préstamos y Transferencias**:
  - Incorporación del campo `coleccion` en la tabla `prestamos_transferencias`, con autocompletado en el buscador de piezas, formulario de nuevo movimiento y expediente de detalle.

---

## [Versión 1.5.0] - 2026-11-05
### 📚 Documentación y Base de Conocimiento Centralizada
- **Bóveda Oficial en Obsidian**: Creación de la estructura integral de documentación en formato Markdown con enlaces bidireccionales (`wikilinks`), diagramas Mermaid, alertas GitHub Flavored Markdown y configuración nativa `.obsidian`.
- **Manual Técnico de Desarrollo**: Documentación completa de arquitectura Bee Framework MVC, modelos PDO Singleton, RBAC, endpoints API/AJAX y despliegue en servidor Apache/Linux.
- **Manual de Operación de Usuario**: Guías paso a paso para perfiles Super Admin, Administrador, Capturista y Lector General.
- **Diccionario de Datos**: Catálogo pormenorizado de todas las tablas y columnas de la base de datos `db_framework`.

---

## [Versión 1.4.2] - 2026-11-04
### 🎨 Optimizaciones de Interfaz y Corrección de Desbordamientos (UI/UX)
- **Menú de Acciones Flotante (`.btn__actions`)**:
  - Estandarización de botones de acción en formato dropdown compacto en las tablas de *Acervo*, *Revisión* y *Préstamos y Transferencias*.
  - Implementación de Popper.js con configuración `strategy: "fixed"` y `data-bs-boundary="viewport"`, evitando que el menú quede oculto bajo los bordes de la tabla.
  - Corrección de desbordamiento CSS con `.table-responsive:has(.dropdown-menu.show)` y eventos jQuery en `admin.js`, eliminando la molesta barra de scroll vertical interna que aparecía al abrir el menú.
- **Iconografía Unificada (Boxicons)**:
  - Corrección del botón "Ver expediente completo" asignando la clase nativa `bx-eye` y añadiendo alias de compatibilidad `.bx-show:before` en `custom-admin.css`.
  - Proporciones simétricas para botones de acción rápida e íconos en tablas.

---

## [Versión 1.4.0] - 2026-11-03
### 🏺 Mejoras en Catalogación, Dimensiones y Ubicación
- **Conmutador Dimensional en Acervo General**:
  - Implementación de botones de radio para seleccionar entre obra **Bidimensional** (`cm`) y **Tridimensional** (`medidas`).
  - Dinamismo de validación en formulario: los campos requeridos se alternan automáticamente sin enviar datos residuales.
- **Resolución de Conflicto en el DOM**:
  - Eliminación de la duplicidad del identificador `#editar-cm` en `acervoView.php`, permitiendo que una pieza devuelta con cambios solicitados actualice y persista adecuadamente las medidas bidimensionales al reenviarse a revisión.
- **Depuración de Campos Redundantes**:
  - Eliminación definitiva de los campos `Materia` y `Colección` en Acervo General, concentrando la descripción compositiva en el campo normativo `Material`.
- **Estandarización de Ubicaciones en los Tres Acervos**:
  - Renombramiento de la anterior "Ubicación Física" a **Ubicación Topográfica** (vitrinas, salas, estantes).
  - Creación del nuevo combo **Ubicación Física** poblado desde la tabla `museos`, incluyendo la opción dinámica `"Otro"` con campo libre para sedes externas y dependencias gubernamentales.
- **Scroll Integrado en Ventanas Modales**:
  - Adición de `modal-dialog-scrollable` y control de altura en el modal de *Registrar Movimiento*, permitiendo visualizar todos los campos mediante scroll de ratón sin forzar navegación por teclado.
- **Contraste de Texto en Selector de Acervo**:
  - Corrección de color de texto en el combobox de selección de acervo previo a la elección.

---

## [Versión 1.3.0] - 2026-10-18
### 🚚 Módulo de Préstamos, Transferencias y Movilidad
- Creación de la tabla `prestamos_transferencias` para registrar salidas temporales y reasignaciones definitivas de obras.
- Generador algorítmico de folios correlativos institucionales `MOV-YYYY-XXXX`.
- Flujo de retorno con inspección física de conservación y registro de `fecha_devolucion_real`.
- Mecanismo de cancelación justificada de traslados no ejecutados.
- Validación para impedir préstamos duplicados en piezas ya activas en salida.

---

## [Versión 1.2.0] - 2026-10-10
### 🚦 Sistema Curatorial de Revisión (Semáforo de Validación)
- Implementación de la bandeja `/admin/revision` con estados:
  - 🟢 **Aprobado** (obra oficial certificada).
  - 🟡 **Cambios Solicitados** (retroalimentación con observaciones al capturista).
  - 🔴 **Rechazado** (baja del proceso curatorial).
  - 🔵 **Pendiente** (en espera de dictamen).
- Respaldo de versiones previas en `datos_anteriores_edicion` al solicitar modificaciones.

---

## [Versión 1.1.0] - 2026-10-08
### 🕵️ Auditoría Forense y Control de Acceso RBAC
- Creación de la tabla `auditoria` con almacenamiento de snapshots en formato JSON (`datos_antes` y `datos_despues`), dirección IP y fecha/hora exacta.
- Implementación de roles: *Super Administrador*, *Administrador*, *Capturista* y *Lector General*.
- Matriz de permisos granulares por colección (`ver_*`, `registrar_*`, `editar_*`, `eliminar_*`, `exportar_*`).

---

## [Versión 1.0.0] - 2025-10-01
### 🏛️ Arquitectura Base del Proyecto
- Implementación del núcleo MVC sobre **Bee Framework 1.5.8**.
- Conexión PDO Singleton a MariaDB/MySQL.
- Estructuración inicial de colecciones: Acervo General, Acervo Arqueológico y Acervo Numismático.
- Plantilla responsiva con Bootstrap 5, SB Admin 2 y DataTables.

