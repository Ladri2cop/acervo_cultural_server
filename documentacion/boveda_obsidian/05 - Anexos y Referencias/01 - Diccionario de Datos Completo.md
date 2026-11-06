---
title: Diccionario de Datos Completo
type: reference
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - diccionario-datos
  - base-de-datos
  - sql
  - referencia
---

# 📖 Diccionario de Datos Completo

Este documento compila el catálogo técnico exhaustivo de tablas, columnas, tipos de datos, llaves primarias y restricciones que componen la base de datos `db_framework` del Sistema de Gestión de Acervo Cultural.

---

## 1. Tabla `acervo_general`
Almacena el inventario de artes plásticas, visuales y bienes artísticos muebles.

| Nombre de Campo | Tipo de Dato | Nulo | Default | Clave | Descripción |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id_acervo_general` | `int` | NO | *Auto Inc* | **PRI** | Identificador primario de la pieza. |
| `id_modulo` | `int` | NO | `1` | MUL | Código de módulo (`1` = Acervo General). |
| `codigo_interno` | `varchar(255)` | SÍ | `NULL` | | Clave única institucional de la pieza. |
| `no_inventario` | `varchar(255)` | SÍ | `NULL` | | Número oficial de inventario de bienes muebles. |
| `nombre_titulo_pieza`| `text` | SÍ | `NULL` | | Título oficial de la obra o nombre descriptivo. |
| `cm` | `varchar(255)` | SÍ | `NULL` | | Dimensiones bidimensionales (Alto $\times$ Ancho cm). |
| `medidas` | `varchar(1000)`| SÍ | `NULL` | | Medidas tridimensionales (Alto, Ancho, Profundidad, Peso). |
| `materia` | `varchar(255)` | SÍ | `NULL` | | *(En desuso)* Reemplazado por campo `material`. |
| `fotografia` | `varchar(255)` | SÍ | `NULL` | | Ruta de almacenamiento de la imagen en servidor. |
| `autor` | `varchar(255)` | SÍ | `NULL` | | Autor, pintor, escultor o taller asignado. |
| `anio` | `varchar(50)` | SÍ | `NULL` | | Año de manufactura o siglo estimado. |
| `epoca` | `varchar(255)` | SÍ | `NULL` | | Periodo histórico o corriente estilística. |
| `tecnica` | `varchar(255)` | SÍ | `NULL` | | Técnica empleada (Óleo, temple, grabado, fundición). |
| `material` | `varchar(255)` | SÍ | `NULL` | | Soporte y materias primas constitutivas. |
| `origen` | `varchar(255)` | SÍ | `NULL` | | País, estado o región de origen. |
| `lote` | `varchar(255)` | SÍ | `NULL` | | Identificador de lote de adquisición masiva. |
| `peso` | `varchar(255)` | SÍ | `NULL` | | Peso físico en gramos o kilogramos. |
| `coleccion` | `varchar(255)` | SÍ | `NULL` | | *(En desuso)* Colección específica. |
| `tipo` | `varchar(255)` | SÍ | `NULL` | | Tipología formal de la obra. |
| `ubicacion_fisica` | `varchar(255)` | SÍ | `NULL` | | Museo institucional o sede externa donde reside. |
| `ubicacion_topografica`| `varchar(255)`| SÍ | `NULL` | | Ubicación micro (Sala, Vitrina, Bóveda, Estante). |
| `estado_conservacion` | `varchar(255)` | SÍ | `NULL` | | Estado: Excelente, Bueno, Regular, Malo, En Restauración. |
| `observaciones` | `text` | SÍ | `NULL` | | Notas curatoriales, marcas, sellos y particularidades. |
| `descripcion` | `text` | SÍ | `NULL` | | Análisis formal e iconográfico de la pieza. |
| `status` | `tinyint(1)` | SÍ | `1` | | Estatus lógico: `1` Activa, `0` Eliminada/Baja. |
| `estatus_revision` | `enum(...)` | NO | `'aprobado'` | | `'aprobado'`, `'pendiente'`, `'cambios_solicitados'`, `'rechazado'`. |
| `observacion_revision` | `text` | SÍ | `NULL` | | Retroalimentación del revisor si solicitó cambios o rechazó. |
| `id_usuario_revisor` | `int` | SÍ | `NULL` | | ID del usuario administrador que evaluó la pieza. |
| `fecha_revision` | `datetime` | SÍ | `NULL` | | Fecha y hora en que se dictaminó la revisión. |
| `id_usuario_creador` | `int` | SÍ | `NULL` | | ID del usuario capturista que dio de alta la pieza. |
| `created_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | | Fecha y hora de creación del registro. |
| `updated_at` | `timestamp` | SÍ | `NULL` | | Marca de última actualización (`ON UPDATE CURRENT_TIMESTAMP`). |
| `datos_anteriores_edicion`| `longtext` | SÍ | `NULL` | | Snapshot JSON previo a la solicitud de cambios. |

---

## 2. Tabla `acervo_arqueologico`
Inventario de bienes culturales de carácter mesoamericano e histórico registrado ante INAH.

| Nombre de Campo | Tipo de Dato | Nulo | Default | Clave | Descripción |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id_acervo_arq` | `int` | NO | *Auto Inc* | **PRI** | Identificador primario de la pieza arqueológica. |
| `id_modulo` | `int` | NO | `2` | MUL | Código de módulo (`2` = Arqueológico). |
| `codigo_interno` | `varchar(255)` | SÍ | `NULL` | | Clave de control institucional. |
| `no_inventario_scyt`| `varchar(255)` | SÍ | `NULL` | | Folio de inventario Secretaría de Cultura y Turismo. |
| `no_registro_inah` | `varchar(255)` | SÍ | `NULL` | | Número de registro oficial ante el INAH. |
| `otros` | `varchar(255)` | SÍ | `NULL` | | Registros o claves accesorias. |
| `nombre_titulo_pieza`| `varchar(255)`| SÍ | `NULL` | | Nombre o tipología (ej. Cajete trípode policromado). |
| `fotografia` | `varchar(255)` | SÍ | `NULL` | | Ruta de imagen en `/assets/uploads/acervo/`. |
| `numero_pieza_por_lote`| `varchar(255)`| SÍ | `NULL` | | Consecutivo de pieza si pertenece a ofrenda. |
| `epoca` | `varchar(255)` | SÍ | `NULL` | | Horizonte cultural (Preclásico, Clásico, Posclásico). |
| `procedencia` | `varchar(255)` | SÍ | `NULL` | | Sitio arqueológico, municipio o yacimiento de origen. |
| `material` | `varchar(255)` | SÍ | `NULL` | | Arcilla, obsidiana, basalto, jadeíta, concha, hueso. |
| `medidas` | `varchar(255)` | SÍ | `NULL` | | Dimensiones físicas y peso. |
| `forma` | `varchar(255)` | SÍ | `NULL` | | Forma estructural o morfológica de la vasija/objeto. |
| `tecnica_manufactura`| `varchar(255)` | SÍ | `NULL` | | Modelado, vaciado, molde, lasqueado, pulido. |
| `tecnica_decorativa`| `varchar(255)` | SÍ | `NULL` | | Incisión, pastillaje, pintura al fresco, esgrafiado. |
| `coleccion` | `varchar(255)` | SÍ | `NULL` | | Colección o proyecto arqueológico de procedencia. |
| `obtencion` | `varchar(255)` | SÍ | `NULL` | | Excavación arqueológica, donación, aseguramiento. |
| `ubicacion_fisica` | `varchar(255)` | SÍ | `NULL` | | Museo donde se resguarda físicamente. |
| `ubicacion_topografica`| `varchar(255)`| SÍ | `NULL` | | Vitrina, estante, sala o bóveda. |
| `estado_conservacion` | `varchar(255)` | SÍ | `NULL` | | Estado: Excelente, Bueno, Regular, Malo, En Restauración. |
| `observaciones` | `varchar(255)` | SÍ | `NULL` | | Notas adicionales del arqueólogo/curador. |
| `descripcion` | `varchar(500)` | SÍ | `NULL` | | Descripción contextual e iconográfica. |
| `representacion` | `varchar(255)` | SÍ | `NULL` | | Figura zoomorfa, antropomorfa, fitomorfa, geométrica. |
| `status` | `tinyint(1)` | SÍ | `1` | | `1` Activa, `0` Baja lógica. |
| `estatus_revision` | `enum(...)` | NO | `'aprobado'` | | `'aprobado'`, `'pendiente'`, `'cambios_solicitados'`, `'rechazado'`. |
| `observacion_revision` | `text` | SÍ | `NULL` | | Observaciones del revisor. |
| `id_usuario_revisor` | `int` | SÍ | `NULL` | | ID del usuario que dictaminó la revisión. |
| `fecha_revision` | `datetime` | SÍ | `NULL` | | Fecha y hora de revisión. |
| `id_usuario_creador` | `int` | SÍ | `NULL` | | ID del capturista. |
| `created_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | | Fecha de alta. |
| `updated_at` | `timestamp` | SÍ | `NULL` | | Fecha de actualización. |
| `datos_anteriores_edicion`| `longtext` | SÍ | `NULL` | | Snapshot JSON previo a edición solicitada. |

---

## 3. Tabla `acervo_numismatica`
Inventario de monedas coloniales, conmemorativas, fichas y medallas.

| Nombre de Campo | Tipo de Dato | Nulo | Default | Clave | Descripción |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id_acervo_numismatica`| `int` | NO | *Auto Inc* | **PRI** | Identificador primario de la pieza numismática. |
| `id_modulo` | `int` | NO | `3` | MUL | Código de módulo (`3` = Numismática). |
| `codigo_interno` | `varchar(255)` | SÍ | `NULL` | | Clave de control institucional. |
| `no_inventario` | `varchar(255)` | SÍ | `NULL` | | Folio oficial de inventario de bienes muebles. |
| `fotografia` | `varchar(255)` | SÍ | `NULL` | | Fotografía general de la pieza. |
| `tipo_obra` | `varchar(255)` | SÍ | `NULL` | | Moneda, medalla conmemorativa, ficha, billete. |
| `ensayador` | `varchar(50)` | SÍ | `NULL` | | Siglas o nombre del ensayador oficial de la ceca. |
| `denominacion` | `varchar(255)` | SÍ | `NULL` | | Valor nominal histórico (ej. 8 Reales, 1 Escudo, 1 Peso). |
| `material` | `varchar(255)` | SÍ | `NULL` | | Oro, plata, cobre, vellón, bronce, papel moneda. |
| `fecha_epoca` | `varchar(255)` | SÍ | `NULL` | | Año de acuñación o periodo virreinal/republicano. |
| `dimensiones` | `varchar(255)` | SÍ | `NULL` | | Diámetro en mm y grosor / espesor. |
| `ubicacion_fisica` | `varchar(255)` | SÍ | `NULL` | | Museo o sede custodio. |
| `ubicacion_topografica`| `varchar(255)`| SÍ | `NULL` | | Vitrina, caja numismática, gaveta, nivel. |
| `estado_conservacion` | `varchar(255)` | SÍ | `NULL` | | Estado: Excelente, Bueno, Regular, Malo, En Restauración. |
| `observaciones` | `varchar(255)` | SÍ | `NULL` | | Pátina, desgaste, marcas de ceca, variantes. |
| `descripcion_cara_a` | `varchar(255)` | SÍ | `NULL` | | **Anverso**: Escudo real, busto, leyenda circundante. |
| `descripcion_cara_b` | `varchar(255)` | SÍ | `NULL` | | **Reverso**: Columnas, cruz, águila sobre nopal, valor. |
| `status` | `tinyint(1)` | SÍ | `1` | | `1` Activa, `0` Baja lógica. |
| `estatus_revision` | `enum(...)` | NO | `'aprobado'` | | `'aprobado'`, `'pendiente'`, `'cambios_solicitados'`, `'rechazado'`. |
| `observacion_revision` | `text` | SÍ | `NULL` | | Observaciones del revisor. |
| `id_usuario_revisor` | `int` | SÍ | `NULL` | | ID del curador revisor. |
| `fecha_revision` | `datetime` | SÍ | `NULL` | | Fecha de dictamen curatorial. |
| `id_usuario_creador` | `int` | SÍ | `NULL` | | ID del usuario capturista. |
| `created_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | | Fecha de registro. |
| `updated_at` | `timestamp` | SÍ | `NULL` | | Fecha de modificación. |
| `datos_anteriores_edicion`| `longtext` | SÍ | `NULL` | | Snapshot JSON anterior a cambios. |

---

## 4. Tabla `prestamos_transferencias`
Registro jurídico y de movilidad física de obras del patrimonio.

| Nombre de Campo | Tipo de Dato | Nulo | Default | Clave | Descripción |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id_movimiento` | `int` | NO | *Auto Inc* | **PRI** | Identificador primario del movimiento. |
| `folio_movimiento` | `varchar(50)` | NO | *(Ninguno)* | **UNI** | Folio único institucional (ej. `MOV-2026-0001`). |
| `tipo_movimiento` | `enum(...)` | NO | *(Ninguno)* | | `'prestamo_temporal'`, `'transferencia_definitiva'`. |
| `tipo_acervo` | `varchar(50)` | NO | *(Ninguno)* | MUL | Discriminador: `'general'`, `'arqueologico'`, `'numismatica'`. |
| `id_pieza` | `int` | NO | *(Ninguno)* | MUL | ID de la pieza en su respectiva tabla de acervo. |
| `codigo_interno_pieza`| `varchar(100)`| SÍ | `NULL` | | Código patrimonial estático al momento de mover. |
| `titulo_pieza` | `varchar(255)` | SÍ | `NULL` | | Título estático de la pieza al momento de mover. |
| `tipo_origen` | `varchar(100)` | NO | *(Ninguno)* | | `'institucional'`, `'externo'`. |
| `lugar_origen` | `varchar(255)` | NO | *(Ninguno)* | | Recinto o museo emisor. |
| `responsable_origen`| `varchar(255)` | SÍ | `NULL` | | Funcionario que entrega la custodia. |
| `tipo_destino` | `varchar(100)` | NO | *(Ninguno)* | | `'institucional'`, `'externo'`. |
| `lugar_destino` | `varchar(255)` | NO | *(Ninguno)* | | Recinto, museo o sede receptora. |
| `responsable_destino`| `varchar(255)` | SÍ | `NULL` | | Funcionario que asume la custodia receptora. |
| `fecha_salida` | `date` | NO | *(Ninguno)* | MUL | Fecha oficial en que la pieza abandona el recinto. |
| `fecha_devolucion_estimada`| `date` | SÍ | `NULL` | | Fecha programada de retorno (en préstamos temporales). |
| `fecha_devolucion_real`| `datetime` | SÍ | `NULL` | | Fecha y hora en que se consuma el retorno físico. |
| `motivo` | `text` | NO | *(Ninguno)* | | Causa de la movilidad (Exposición, restauración, etc.). |
| `no_oficio_autorizacion`| `varchar(150)`| SÍ | `NULL` | | Número de oficio de autorización de la Secretaría. |
| `condiciones_entrega`| `text` | SÍ | `NULL` | | Dictamen de estado físico de la obra al salir. |
| `condiciones_devolucion`| `text` | SÍ | `NULL` | | Dictamen de estado físico de la obra al reingresar. |
| `lugar_devolucion` | `varchar(255)` | SÍ | `NULL` | | Recinto o bóveda donde se recibe la obra de regreso. |
| `observaciones` | `text` | SÍ | `NULL` | | Notas adicionales o justificación de cancelación. |
| `estatus` | `enum(...)` | NO | `'activo'` | MUL | `'activo'`, `'devuelto'`, `'completada'`, `'cancelado'`. |
| `id_usuario_registro`| `int` | NO | *(Ninguno)* | | ID del usuario que dio de alta el movimiento. |
| `id_usuario_devolucion`| `int` | SÍ | `NULL` | | ID del usuario que registró la devolución física. |
| `created_at` | `timestamp` | SÍ | `CURRENT_TIMESTAMP` | | Fecha de creación del registro. |
| `updated_at` | `timestamp` | SÍ | `NULL` | | Marca de última actualización. |

---

## 5. Tabla `auditoria`
Bitácora forense e inmutable de eventos en la plataforma.

| Nombre de Campo | Tipo de Dato | Nulo | Default | Clave | Descripción |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id_auditoria` | `int` | NO | *Auto Inc* | **PRI** | Identificador primario de la entrada forense. |
| `id_usuario` | `int` | SÍ | `NULL` | MUL | ID del usuario que realizó la acción. |
| `username` | `varchar(255)` | SÍ | `NULL` | | Nombre de usuario al momento del evento. |
| `id_modulo` | `int` | SÍ | `NULL` | MUL | Identificador de módulo. |
| `nombre_tabla` | `varchar(100)` | NO | *(Ninguno)* | | Nombre de la tabla modificada. |
| `id_pieza` | `int` | SÍ | `NULL` | | ID del registro afectado. |
| `tipo_accion` | `varchar(50)` | NO | *(Ninguno)* | | Acción ejecutada (INSERT, UPDATE, DELETE, REVISION, etc.). |
| `fecha_accion` | `timestamp` | NO | `CURRENT_TIMESTAMP` | | Marca temporal con precisión de segundos. |
| `observaciones` | `text` | SÍ | `NULL` | | Notas adicionales del evento o motivo de cambio. |
| `datos_antes` | `longtext` | SÍ | `NULL` | | Snapshot JSON de los valores antes de la operación. |
| `datos_despues` | `longtext` | SÍ | `NULL` | | Snapshot JSON de los valores resultantes. |
| `ip_address` | `varchar(50)` | SÍ | `NULL` | | Dirección IP del cliente emisor. |
| `mac_address` | `varchar(50)` | SÍ | `NULL` | | Identificador de red / hardware si está disponible. |

---

## 6. Tablas de Seguridad y Administración (`bee_*`)

### 6.1. `bee_roles`
- `id` (`int`, PK, Auto Inc)
- `nombre` (`varchar(100)`): Nombre legible (Super Administrador, Administrador, Capturista, Lector General).
- `slug` (`varchar(100)`): Identificador clave (`super-administrador`, `administrador`, `capturista`, `lector-general`).
- `creado` (`datetime`): Fecha de creación del rol.

### 6.2. `bee_permisos`
- `id` (`int`, PK, Auto Inc)
- `nombre` (`varchar(100)`): Nombre descriptivo de la acción.
- `slug` (`varchar(100)`): Identificador programmatico (`ver_acervo_general`, `registrar_acervo_general`, etc.).
- `descripcion` (`varchar(255)`): Explicación del privilegio.
- `creado` (`datetime`): Fecha de alta del permiso.

### 6.3. `bee_roles_permisos`
- `id` (`int`, PK, Auto Inc)
- `id_role` (`int`, FK a `bee_roles.id`)
- `id_permiso` (`int`, FK a `bee_permisos.id`)

### 6.4. `bee_users`
- `id` (`int`, PK, Auto Inc)
- `nombre` (`varchar(255)`): Nombre completo del funcionario.
- `usuario` (`varchar(255)`): Nombre de usuario único para inicio de sesión.
- `password` (`varchar(255)`): Hash seguro de contraseña (`password_hash` con algoritmo bcrypt).
- `email` (`varchar(255)`): Correo institucional.
- `id_role` (`int`, FK a `bee_roles.id`): Rol asignado.
- `status` (`tinyint(1)`): `1` Activo, `0` Suspendido.
- `creado` (`datetime`): Fecha de alta.

---

## 7. Tabla `museos`
Catálogo de recintos museográficos oficiales del estado.

| Nombre de Campo | Tipo de Dato | Nulo | Default | Clave | Descripción |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id_museo` | `int` | NO | *Auto Inc* | **PRI** | Identificador del museo. |
| `nombre_museo` | `varchar(255)` | SÍ | `NULL` | | Nombre oficial del recinto museográfico. |
| `ubicacion` | `text` | SÍ | `NULL` | | Domicilio físico, municipio y código postal. |
| `telefono` | `varchar(10)` | SÍ | `NULL` | | Teléfono de contacto institucional. |
| `responsable_museo` | `varchar(255)` | SÍ | `NULL` | | Nombre del director o custodio general. |
| `status` | `tinyint(1)` | NO | `1` | | `1` Activo para selección, `0` Inactivo. |
| `created_at` | `timestamp` | NO | `CURRENT_TIMESTAMP` | | Fecha de alta en el sistema. |
| `updated_at` | `timestamp` | SÍ | `NULL` | | Fecha de última edición. |

---

## 8. Referencias Cruzadas
- Consulta el diagrama visual en: [[01 - Diagrama Entidad-Relación (ERD)]]
- Tablas de acervo: [[02 - Tablas de Acervo (General, Arqueológico, Numismático)]]
- Trazabilidad y movimientos: [[03 - Módulo de Préstamos y Transferencias]]
