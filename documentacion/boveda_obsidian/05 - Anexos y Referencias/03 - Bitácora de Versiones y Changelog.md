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
