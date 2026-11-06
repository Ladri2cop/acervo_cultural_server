---
title: Diagrama Entidad-Relación (ERD)
type: database
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - base-de-datos
  - erd
  - sql
  - esquema
---

# 🗄️ Diagrama Entidad-Relación (ERD)

Este documento presenta el modelo conceptual y relacional de la base de datos `db_framework`, detallando las entidades principales, llaves primarias, foráneas, índices y la cardinalidad de sus interacciones.

---

## 1. Diagrama Global de Entidades

```mermaid
erDiagram
    BEE_ROLES ||--o{ BEE_USERS : "posee"
    BEE_ROLES ||--o{ BEE_ROLES_PERMISOS : "agrupa"
    BEE_PERMISOS ||--o{ BEE_ROLES_PERMISOS : "concede"
    
    BEE_USERS ||--o{ ACERVO_GENERAL : "registra / revisa"
    BEE_USERS ||--o{ ACERVO_ARQUEOLOGICO : "registra / revisa"
    BEE_USERS ||--o{ ACERVO_NUMISMATICA : "registra / revisa"
    BEE_USERS ||--o{ PRESTAMOS_TRANSFERENCIAS : "crea / recibe"
    BEE_USERS ||--o{ AUDITORIA : "dispara evento"

    MUSEOS ||--o{ ACERVO_GENERAL : "alberga fisicamente"
    MUSEOS ||--o{ ACERVO_ARQUEOLOGICO : "alberga fisicamente"
    MUSEOS ||--o{ ACERVO_NUMISMATICA : "alberga fisicamente"

    BEE_ROLES {
        int id PK
        varchar nombre
        varchar slug
        datetime creado
    }

    BEE_PERMISOS {
        int id PK
        varchar nombre
        varchar slug
        varchar descripcion
        datetime creado
    }

    BEE_ROLES_PERMISOS {
        int id PK
        int id_role FK
        int id_permiso FK
    }

    BEE_USERS {
        int id PK
        varchar nombre
        varchar usuario
        varchar password
        varchar email
        int id_role FK
        tinyint status
    }

    MUSEOS {
        int id_museo PK
        varchar nombre_museo
        text ubicacion
        varchar telefono
        varchar responsable_museo
        tinyint status
    }

    ACERVO_GENERAL {
        int id_acervo_general PK
        varchar codigo_interno
        varchar no_inventario
        text nombre_titulo_pieza
        varchar cm "Bidimensional (cm)"
        varchar medidas "Tridimensional (medidas)"
        varchar autor
        varchar anio
        varchar epoca
        varchar tecnica
        varchar material
        varchar ubicacion_fisica
        varchar ubicacion_topografica
        varchar estado_conservacion
        enum estatus_revision "aprobado, pendiente, cambios_solicitados, rechazado"
        text observacion_revision
        int id_usuario_revisor FK
        int id_usuario_creador FK
    }

    ACERVO_ARQUEOLOGICO {
        int id_acervo_arq PK
        varchar codigo_interno
        varchar no_inventario_scyt
        varchar no_registro_inah
        varchar nombre_titulo_pieza
        varchar epoca
        varchar procedencia
        varchar material
        varchar medidas
        varchar tecnica_manufactura
        varchar ubicacion_fisica
        varchar ubicacion_topografica
        enum estatus_revision
        int id_usuario_creador FK
        int id_usuario_revisor FK
    }

    ACERVO_NUMISMATICA {
        int id_acervo_numismatica PK
        varchar codigo_interno
        varchar no_inventario
        varchar ensayador
        varchar denominacion
        varchar material
        varchar dimensiones
        varchar ubicacion_fisica
        varchar ubicacion_topografica
        text descripcion_cara_a
        text descripcion_cara_b
        enum estatus_revision
        int id_usuario_creador FK
        int id_usuario_revisor FK
    }

    PRESTAMOS_TRANSFERENCIAS {
        int id_movimiento PK
        varchar folio_movimiento UK
        enum tipo_movimiento "prestamo_temporal, transferencia_definitiva"
        varchar tipo_acervo
        int id_pieza
        varchar codigo_interno_pieza
        varchar titulo_pieza
        varchar tipo_origen
        varchar lugar_origen
        varchar tipo_destino
        varchar lugar_destino
        date fecha_salida
        date fecha_devolucion_estimada
        datetime fecha_devolucion_real
        enum estatus "activo, devuelto, completada, cancelado"
        int id_usuario_registro FK
        int id_usuario_devolucion FK
    }

    AUDITORIA {
        int id_auditoria PK
        int id_usuario FK
        varchar username
        varchar nombre_tabla
        int id_pieza
        varchar tipo_accion
        timestamp fecha_accion
        longtext datos_antes
        longtext datos_despues
        varchar ip_address
        varchar mac_address
    }
```

---

## 2. Relaciones Fundamentales y Restricciones de Integridad

### 2.1. Seguridad y Acceso (RBAC)
- **`bee_roles` $\rightarrow$ `bee_users`**: Relación 1 a N. Cada usuario tiene asignado exactamente un rol funcional (`id_role`), el cual determina su conjunto de permisos.
- **`bee_roles` $\leftrightarrow$ `bee_permisos`**: Relación N a M resuelta a través de la tabla intermedia `bee_roles_permisos`. Permite granularidad total en las operaciones autorizadas.

### 2.2. Custodia del Acervo y Trazabilidad Curatorial
- Cada tabla de acervo (`acervo_general`, `acervo_arqueologico`, `acervo_numismatica`) contiene dos llaves foráneas lógicas hacia `bee_users`:
  - `id_usuario_creador`: Identifica al capturista responsable del registro inicial.
  - `id_usuario_revisor`: Identifica al curador o administrador que autorizó, solicitó cambios o rechazó la pieza.

### 2.3. Trazabilidad de Movilidad (Préstamos y Traslados)
- `prestamos_transferencias` utiliza un polimorfismo estructurado mediante las columnas `tipo_acervo` (`general`, `arqueologico`, `numismatica`) e `id_pieza`. Esto permite enlazar un único módulo de movimientos con cualquiera de las tres colecciones sin redundancia de tablas.
- Genera un `folio_movimiento` único e indexado (ej. `MOV-2026-0042`) para control administrativo.

### 2.4. Integridad Forense (Auditoría)
- La tabla `auditoria` no tiene llaves foráneas restrictivas de tipo `CASCADE` o `RESTRICT` para evitar bloqueos transaccionales o fallos si un usuario fuera suspendido; captura instantáneas de datos en campos de texto enriquecido (`longtext`) serializados en formato JSON.

---

## 3. Notas Relacionadas
- Estructura detallada de piezas: [[02 - Tablas de Acervo (General, Arqueológico, Numismático)]]
- Detalle del ciclo de préstamos: [[03 - Módulo de Préstamos y Transferencias]]
- Mecanismo de auditoría: [[04 - Sistema de Auditoría y Trazabilidad]]
- Tablas maestras y catálogos: [[05 - Catálogos y Tablas Auxiliares]]

