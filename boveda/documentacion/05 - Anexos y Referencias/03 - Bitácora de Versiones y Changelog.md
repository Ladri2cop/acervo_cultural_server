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

