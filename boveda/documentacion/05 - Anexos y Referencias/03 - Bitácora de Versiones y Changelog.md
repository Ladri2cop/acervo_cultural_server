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
  - **Renombramiento oficial**: Cambio del nombre del sitio a **SIPAC** (*Sistema de Información del Patrimonio Cultural*) en `app/core/settings.php`. Los títulos de las pestañas en el navegador ahora se despliegan formalmente como `<title>[Sección] - SIPAC</title>`.
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

