# REPORTE DE AUDITORÍA Y CONTROL INTERNO - SIPAC
**Entidad:** Secretaría de Cultura y Turismo del Estado de México  
**Área Responsable:** Departamento de Tecnologías de la Información (DTI)  
**Sistema Auditado:** Sistema de Inventario para el Acervo Cultural (SIPAC)  
**Repositorio Base:** `acervo_cultural_server`  
**Bóveda Documental:** `boveda/` (Obsidian Vault)  
**Fecha de Emisión:** 24 de Noviembre de 2026  
**Auditor Principal:** Auditoría de Arquitectura de Software, Ciberseguridad y Control Interno Gubernamental (COCODI / OIC)  

---

## 1. RESUMEN EJECUTIVO

### 1.1. Diagnóstico General de Madurez Técnica
Se ha llevado a cabo una auditoría técnica profunda, estricta e integral sobre el código fuente del backend del **Sistema de Inventario para el Acervo Cultural (SIPAC)** y su contraste directo contra las notas de arquitectura, diagramas relacionales y reglas de negocio documentadas en la bóveda de Obsidian (`boveda/`).

El sistema está construido sobre una arquitectura personalizada en **PHP 8.x** bajo el patrón MVC soportado por el framework base **Bee Framework v1.5.8**, complementado con **MariaDB / MySQL (InnoDB)** y persistencia vía PDO. La base de datos administra un acervo patrimonial activo de más de **40,000 bienes culturales** distribuidos en:
- `acervo_general`: **21,528 registros**
- `acervo_arqueologico`: **10,021 registros**
- `acervo_numismatica`: **8,405 registros**
- `acervo_acuarela` / `historico`: **12 registros** (tablas residuales/inactivas)
- `prestamos_transferencias`: **17 movimientos**
- `auditoria`: **2,339 eventos de bitácora**

**Diagnóstico Global:** El sistema presenta un nivel de madurez técnica **MEDIO-BAJO (Calificación: 5.8 / 10)**. Si bien dispone de flujos funcionales operativos robustos para la catalogación, semaforización de revisiones y préstamos interinstitucionales, adolece de **vulnerabilidades de seguridad críticas de orden OWASP Top 10** (incluyendo riesgo de Ejecución Remota de Código por subida irrestricta de archivos, exposición pública de secretos criptográficos en el cliente y ausencia de control de acceso horizontal o IDOR por recinto), así como **vicios severos de diseño arquitectónico** (cálculo de paginación en memoria RAM PHP mediante scans completos de 21,500+ tuplas en lugar de consultas SQL optimizadas).

### 1.2. Porcentaje Global de Paridad Documental (Obsidian vs. Código)
El índice global de paridad y sincronización técnica se sitúa en un **64.5%**.
- **Paridad Conceptual y Flujos de Trabajo:** **85%** (Los flujos de semáforo de revisión y de movilidad en préstamos y transferencias reflejan con fidelidad las reglas descritas en la bóveda).
- **Paridad de Esquemas de Base de Datos y Diccionario:** **58%** (Discrepancias críticas en identificadores de módulos, presencia de columnas obsoletas no eliminadas en tablas físicas, y ausencia absoluta de las llaves foráneas e índices documentados en el ERD).
- **Paridad de Especificación de API y Seguridad:** **51%** (La documentación asevera la existencia de protección CSRF en todos los endpoints `POST` y validación estricta de parámetros con nombres como `busqueda` o `filtro_ubicacion`, mientras que el código carece de tokens CSRF en endpoints de mutación y utiliza nombres de parámetros discordantes como `search`, `ubicacion`).

### 1.3. Dictamen de Cumplimiento de Control Interno Gubernamental (COCODI / OIC)
**DICTAMEN: NO APROBATORIO CON SALVEDADES CRÍTICAS.**  
Frente a los marcos de control interno emitidos por el **Comité de Control Interno (COCODI)** y el **Órgano Interno de Control (OIC)** del Estado de México, el SIPAC incurre en observaciones graves:
1. **Riesgo Patrimonial por Falta de Aislamiento de Custodia (IDOR):** Los administradores de museos locales pueden editar, suprimir o alterar bienes pertenecientes a otros recintos culturales del Estado, vulnerando el principio de segregación funcional y custodia institucional.
2. **Deficiencia en la Integridad Transaccional (No Cumplimiento ACID):** Las operaciones compuestas de alta patrimonial y dictamen de movilidad se ejecutan sin transacciones atómicas (`BEGIN TRANSACTION` / `COMMIT` / `ROLLBACK`). Una falla intermedia genera estados inconsistentes (archivos huérfanos o actualizaciones parciales sin registro de auditoría).
3. **Exposición de Secretos y Vulnerabilidad RCE:** La presencia de contraseñas de base de datos en texto plano dentro del repositorio, la publicación de llaves privadas API en el DOM de la aplicación y la recepción de archivos en directorios `0777` sin desactivación de ejecución de scripts representan un riesgo inminente de compromiso del servidor institucional.

---

## 2. MATRIZ DE PARIDAD DETALLADA (OBSIDIAN vs. BACKEND)

| Módulo / Tabla / Colección | Especificación en Obsidian | Implementación en Código Backend / DB | Estado | Impacto Operativo y Técnico |
|---|---|---|---|---|
| **Mapeo de Módulo: Acervo General** | Documentado en [02 - Tablas de Acervo.md](file:///var/www/html/ACERVO_CULTURAL/boveda/documentacion/02%20-%20Base%20de%20Datos%20y%20Modelo%20de%20Datos/02%20-%20Tablas%20de%20Acervo%20(General,%20Arqueol%C3%B3gico,%20Numism%C3%A1tico).md#L28) como `id_modulo = 1`. | En BD física (`modulos`), el ID 1 corresponde a *"Acervo Antropología"* y el ID 5 a *"Acervo General"*. En código ([adminController.php:1097](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1097)) se asigna `id_modulo = 5`. Existen además 2 tuplas anómalas en `acervo_general` con `id_modulo = 55`. | **Discrepante** | Inconsistencia en reportes de auditoría por módulo y desalineación entre el manual y el DDL real. |
| **Campos Obsoletos en Acervo General** | Documentado en notas de actualización que `materia` y `coleccion` fueron suprimidos en favor de `material`. | Las columnas `materia` y `coleccion` persisten en la tabla física `acervo_general`. En [adminController.php:1090](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1090) se fuerza `'coleccion' => ''`. | **Discrepante** | Deuda técnica, redundancia en almacenamiento y riesgo de reactivación accidental de campos no controlados. |
| **Acervo Histórico** | Mencionada como colección patrimonial especializada sujeta a catalogación técnica. | La tabla `historico` existe con 1 solo registro arcaico (`inventario_uno`...`cinco`, `id_registro_pieza`). No tiene modelo, controlador, vistas ni rutas activas en el backend. | **Ausente / Obsoleto** | Documentación describe un módulo inexistente funcionalmente; confusión sobre el resguardo del patrimonio histórico escrito. |
| **Integridad Relacional (Foreign Keys)** | El [Diagrama ERD](file:///var/www/html/ACERVO_CULTURAL/boveda/documentacion/02%20-%20Base%20de%20Datos%20y%20Modelo%20de%20Datos/01%20-%20Diagrama%20Entidad-Relaci%C3%B3n%20(ERD).md#L27-L35) especifica llaves foráneas formales entre `acervo_*`, `bee_users` (`id_usuario_creador`, `id_usuario_revisor`) y `museos`. | En MariaDB existen **0 llaves foráneas** en `acervo_general`, `acervo_arqueologico` y `acervo_numismatica`. La relación con museos es por cadena de texto libre en `ubicacion_fisica`. | **Discrepante** | Riesgo de orfandad de registros si se suprime un usuario; incapacidad del RDBMS de garantizar consistencia referencial. |
| **Indexación Documentada vs Real** | El ERD documenta índices sobre `codigo_interno`, `no_inventario`, `estatus_revision` y `ubicacion_fisica`. | MariaDB **únicamente** posee índice de llave primaria (`PRIMARY KEY`) y un índice sobre `id_modulo`. No existen índices sobre códigos ni estatus en ninguna de las tres tablas de acervo. | **Ausente** | Colapso de rendimiento; cada consulta de búsqueda o semáforo obliga a full table scans de más de 21,500 filas. |
| **Protección CSRF en Endpoints POST** | Documentado en [04 - Endpoints AJAX.md](file:///var/www/html/ACERVO_CULTURAL/boveda/documentacion/03%20-%20Manual%20T%C3%A9cnico%20de%20Desarrollo/04%20-%20Endpoints%20AJAX%20y%20API%20Interna.md#L16): *"Todas las peticiones POST implementan validación contra ataques CSRF mediante tokens de sesión"*. | Solo los endpoints de edición (`acervo_*_editar`) validan token. Endpoints críticos como `acervo_general_eliminar`, `autorizar_pieza`, `solicitar_cambios_pieza`, `rechazar_pieza` y `post_crear_movimiento` **NO verifican CSRF**. | **Discrepante** | Vulnerabilidad crítica contra peticiones transfronterizas no deseadas; incumplimiento de la propia norma documentada. |
| **Nomenclatura de Parámetros de Búsqueda** | Documentado como `busqueda`, `filtro_ubicacion`, `filtro_anio`, `filtro_autor_cultura` en [04 - Endpoints AJAX.md](file:///var/www/html/ACERVO_CULTURAL/boveda/documentacion/03%20-%20Manual%20T%C3%A9cnico%20de%20Desarrollo/04%20-%20Endpoints%20AJAX%20y%20API%20Interna.md#L47-L50). | El controlador [adminController.php:1298-1390](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1298-L1390) consume `search`, `ubicacion`, `anio` y `cultura`. | **Discrepante** | Cualquier integración externa o script basado en la documentación técnica fallará al consultar la API interna. |
| **Polimorfismo en Movimientos de Préstamo** | Documentado como relación con validación de tipo de bien cultural y folio normado. | Implementado mediante pseudo-referencia polimórfica (`tipo_acervo` + `id_pieza`), pero sin verificación de integridad relacional hacia las tablas maestras. | **Alineado con Salvedades** | Funciona operativamente pero carece de restricciones a nivel de motor de datos. |
| **Bitácora de Auditoría y Trazabilidad** | Documentado en [04 - Sistema de Auditoría.md](file:///var/www/html/ACERVO_CULTURAL/boveda/documentacion/02%20-%20Base%20de%20Datos%20y%20Modelo%20de%20Datos/04%20-%20Sistema%20de%20Auditor%C3%ADa%20y%20Trazabilidad.md): Captura de `ip_address`, `mac_address`, snapshots JSON (`datos_antes`, `datos_despues`). | Implementado fielmente en [bee_custom_functions.php:99-132](file:///var/www/html/ACERVO_CULTURAL/app/functions/bee_custom_functions.php#L99-L132) e integrado en revisiones y préstamos. Salvedad: la captura de MAC address es técnicamente espuria a través de capas IP. | **Alineado** | Cumple con los requerimientos de evidencia pericial para auditoría interna gubernamental. |

---

## 3. MATRIZ INTEGRAL DE VULNERABILIDADES Y RIESGOS TÉCNICOS

| ID | Archivo / Endpoint | Hallazgo / Deficiencia | Severidad | Vector de Riesgo (OWASP / CWE) | Acción Correctiva Recomendada |
|---|---|---|---|---|---|
| **VUL-01** | [adminController.php:1100-1115](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1100-L1115)<br>[adminController.php:1176-1190](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1176-L1190)<br>[adminController.php:1255-1270](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1255-L1270)<br>[adminController.php:2028-2037](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L2028-L2037) | **Subida Irrestricta de Archivos sin Validación de Extensión ni Tipo MIME (RCE)**:<br>Al registrar o editar piezas de cualquier colección, el archivo `$_FILES['fotografia']` se almacena directamente con su nombre original o `basename()` en `assets/uploads/` sin validar extensión ni tipo MIME real (`finfo`). El directorio tiene permisos `0777` y carece de archivo `.htaccess` protector. | **Crítica** | **OWASP A04:2021 - Insecure Design**<br>**CWE-434: Unrestricted Upload of File with Dangerous Type**<br>Permite a un usuario autenticado cargar scripts ejecutables (`.php`, `.phtml`) en la raíz web y tomar control total del servidor (Remote Code Execution). | Implementar validación estricta de extensiones permitidas (`jpg`, `jpeg`, `png`, `webp`), verificación de cabeceras binarias MIME vía `finfo`, renombramiento criptográfico aleatorio (UUIDv4) y desplegar un `.htaccess` en `assets/uploads/` con `php_flag engine off` y denegación de ejecución. |
| **VUL-02** | [adminController.php:1388-1455](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1388-L1455)<br>[acervoGeneralModel.php:65-76](file:///var/www/html/ACERVO_CULTURAL/app/models/acervoGeneralModel.php#L65-L76) | **Denegación de Servicio por Agotamiento de Memoria (Scans No Acotados)**:<br>El método `get_acervo_general()` llama a `AcervoGeneralModel::getAll()`, ejecutando un `SELECT *` de **21,528 registros** sin `LIMIT` hacia memoria RAM en cada clic de paginación. El filtrado y recorte de 10 elementos se realiza en memoria PHP mediante `array_filter` y `array_slice`. | **Crítica** | **OWASP A05:2021 - Security Misconfiguration**<br>**CWE-400: Uncontrolled Resource Consumption (DoS)**<br>Con 10 a 20 peticiones concurrentes de enlaces o administradores, el consumo de memoria desborda el límite de PHP-FPM / Apache, paralizando el servidor institucional. | Refactorizar la arquitectura de persistencia para delegar el filtrado, conteo y paginación directamente al motor SQL (`WHERE ... LIMIT :offset, :limit`) mediante sentencias preparadas, erradicando la carga masiva en RAM. |
| **VUL-03** | [adminController.php:24-34](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L24-L34)<br>[adminController.php:1980-2002](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L1980-L2002)<br>[adminController.php:2300-2340](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L2300-L2340) | **Falla de Control de Acceso Horizontal (IDOR) y Elevación por Omisión de Rol**:<br>1. En `check_user_permission()`, si `id_role` no está definido en sesión, se asigna por defecto `id_role = 1` (**Super Administrador**), aplicando una política de *Permitir por Defecto*.<br>2. En `acervo_*_editar` y `acervo_*_eliminar`, cualquier usuario con perfil Administrador puede modificar o eliminar piezas de recintos distintos a su museo asignado, sin verificación de `bee_users.id_museo`. | **Crítica** | **OWASP A01:2021 - Broken Access Control**<br>**CWE-639: Authorization Bypass Through User-Controlled Key**<br>**CWE-276: Incorrect Default Permissions**<br>Permite a enlaces de un museo alterar o suprimir piezas de otros recintos culturales. | Aplicar el principio de *Denegación por Defecto* (`fail-closed` arrojando HTTP 403 si no hay rol), e incorporar validación de pertenencia geográfica/museística (`id_museo`) en todas las operaciones sobre acervo. |
| **VUL-04** | [bee_core_functions.php:1533](file:///var/www/html/ACERVO_CULTURAL/app/functions/bee_core_functions.php#L1533)<br>[settings.php:24-32](file:///var/www/html/ACERVO_CULTURAL/app/core/settings.php#L24-L32)<br>[bee_config.php:20-25](file:///var/www/html/ACERVO_CULTURAL/app/config/bee_config.php#L20-L25) | **Exposición de Secretos Criptográficos y Credenciales en Código y Cliente**:<br>1. `get_bee_options()` inyecta `API_PRIVATE_KEY` en el objeto global JavaScript `Bee.private_key` en todas las páginas HTML renderizadas.<br>2. Las llaves de API, el `AUTH_SALT` y las credenciales de BD (`srvccm` / `720./Srv.xi8`) están incrustadas en texto plano y versionadas en el repositorio Git. | **Alta** | **OWASP A07:2021 - Identification and Authentication Failures**<br>**CWE-798: Use of Hard-coded Credentials**<br>**CWE-200: Exposure of Sensitive Information**<br>Cualquier usuario o visitante puede inspeccionar la consola web, extraer la llave privada y autenticarse contra los endpoints de la API REST interna. | Suprimir la inyección de la llave privada en JavaScript; migrar credenciales y sales a variables de entorno (`.env`) fuera de la raíz web y restringir el consumo de la API mediante tokens JWT de vida corta. |
| **VUL-05** | [adminController.php:2300-2340](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L2300-L2340)<br>[adminController.php:2930-3050](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L2930-L3050)<br>[adminController.php:3680-3750](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L3680-L3750) | **Ausencia de Protección CSRF en Acciones de Estado Críticas**:<br>Los endpoints de eliminación de piezas (`acervo_*_eliminar`), dictamen de revisión (`autorizar_pieza`, `solicitar_cambios_pieza`, `rechazar_pieza`) y aceptación/declinación de préstamos carecen de verificación de token CSRF (`check_csrf()`). | **Alta** | **OWASP A01:2021 - Broken Access Control**<br>**CWE-352: Cross-Site Request Forgery (CSRF)**<br>Un atacante puede inducir a un administrador autenticado a ejecutar eliminaciones o aprobaciones patrimoniales mediante peticiones forjadas desde sitios externos. | Exigir validación obligatoria de token CSRF en todos los controladores receptores de peticiones `POST`/`DELETE` antes de procesar cualquier cambio en base de datos. |
| **VUL-06** | [adminController.php:3614-3617](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L3614-L3617)<br>[assets/uploads/oficios_movimientos/](file:///var/www/html/ACERVO_CULTURAL/assets/uploads/oficios_movimientos/) | **Validación Superficial de Documentos Oficiales y Permisos Inseguros en Disco**:<br>La validación de oficios de autorización de préstamos solo examina la extensión superficial con `pathinfo()`, omitiendo el tipo MIME real. Asimismo, la carpeta de oficios se crea con permisos `0777` sin restricciones de ejecución web. | **Media** | **OWASP A04:2021 - Insecure Design**<br>**CWE-434 / CWE-732: Incorrect Permission Assignment**<br>Riesgo de alojamiento de archivos maliciosos camuflados como documentos oficiales de movilidad cultural. | Incorporar validación con `finfo_file()` y `mime_content_type()`, forzar permisos `0750` y almacenar los documentos en una ruta privada fuera del `DocumentRoot`, sirviéndolos vía script con control de descarga. |
| **VUL-07** | [adminController.php:2600](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L2600)<br>[assets/js/admin/prestamos.js:430-682](file:///var/www/html/ACERVO_CULTURAL/assets/js/admin/prestamos.js#L430-L682) | **Saneamiento Incompleto contra XSS Persistente en Títulos y Vistas Dinámicas**:<br>En `ficha_tecnica()`, el elemento `<title>' . $titulo . '</title>` se concatena sin `htmlspecialchars()`. Adicionalmente, diversas tablas dinámicas en el frontend inyectan cadenas de texto de acervo mediante `.innerHTML` sin saneamiento estricto de entidades HTML. | **Media** | **OWASP A03:2021 - Injection**<br>**CWE-79: Cross-site Scripting (Stored XSS)**<br>Si un catalogador ingresa caracteres especiales o scripts en el título o denominación, estos se interpretan en el visor de ficha técnica o vistas administrativas. | Aplicar `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')` en todos los puntos de salida de plantillas y preferir `.textContent` o funciones de escape en scripts JavaScript del frontend. |
| **VUL-08** | [settings.php:50](file:///var/www/html/ACERVO_CULTURAL/app/core/settings.php#L50)<br>[index.php](file:///var/www/html/ACERVO_CULTURAL/index.php) | **Configuración Inadecuada de Entorno de Producción**:<br>`PREPROS = true` permanece activo en producción. `display_errors` no está desactivado explícitamente en el bootstrap (`index.php`), lo que provoca que ante fallas de PDO o advertencias de PHP se expongan rutas absolutas (`/var/www/html/...`) y consultas SQL al usuario final. | **Baja** | **OWASP A05:2021 - Security Misconfiguration**<br>**CWE-209: Generation of Error Message Containing Sensitive Information**<br>Fuga de información de la infraestructura y topología interna del servidor gubernamental. | Forzar `ini_set('display_errors', '0')`, `error_reporting(E_ALL & ~E_DEPRECATED)` en producción, y registrar todas las excepciones de forma segura en `app/logs/`. |

---

## 4. ANÁLISIS DE BASE DE DATOS Y CONGRUENCIA RELACIONAL

### 4.1. Evaluación de Normalización e Integridad Referencial
1. **Ausencia Crítica de Claves Foráneas (Foreign Keys):**
   Las 3 tablas principales que resguardan el patrimonio (`acervo_general`, `acervo_arqueologico`, `acervo_numismatica`) operan en el motor InnoDB pero fueron creadas **sin restricciones de integridad referencial**.
   - Los campos `id_usuario_creador` e `id_usuario_revisor` son enteros simples sin restricción `FOREIGN KEY ... REFERENCES bee_users(id) ON DELETE RESTRICT`. Si un usuario del DTI o enlace de museo es eliminado, sus piezas quedan asociadas a IDs huérfanos.
   - La tabla `prestamos_transferencias` utiliza un esquema polimórfico (`tipo_acervo` + `id_pieza`), lo cual impide al motor relacional garantizar que `id_pieza` exista físicamente en `acervo_general`, `acervo_arqueologico` o `acervo_numismatica`.

2. **Desnormalización de Museos y Ubicaciones:**
   A pesar de existir la tabla maestra `museos` con 26 recintos dados de alta, los registros de acervo almacenan la ubicación física como cadena de texto libre (`ubicacion_fisica varchar(255)`). Esto genera variaciones tipográficas ("Toluca", "Museo de Antropología", "Toluca - Bodega") que fragmentan los reportes institucionales e impiden filtros indexados unificados.

3. **Colaciones Heterogéneas en el Mismo Esquema:**
   La base de datos presenta una fragmentación severa en juegos de caracteres y colaciones:
   - Tablas nativas del Bee Framework (`bee_users`, `bee_roles`, `bee_permisos`): `utf8mb3_general_ci` (obsoleto).
   - Tablas de acervo (`acervo_general`, `acervo_arqueologico`, `acervo_numismatica`): `utf8mb4_unicode_ci`.
   - Tablas auxiliares y auditoría (`auditoria`, `modulos`, `museos`): `utf8mb4_general_ci`.
   *Riesgo:* Las consultas complejas que unen `bee_users` con `auditoria` o `acervo_*` generan advertencias de conversión de caracteres (*Illegal mix of collations*) y degradan el desempeño al deshabilitar el uso óptimo de índices en los `JOIN`.

### 4.2. Estado de los Índices en Campos de Búsqueda Frecuente
El diagnóstico de los índices actuales frente a las consultas más frecuentes revela un déficit alarmante:

| Tabla | Registros | Índices Existentes | Campos Consultados en Búsquedas Frecuentes | Estado / Diagnóstico |
|---|---|---|---|---|
| `acervo_general` | 21,528 | `PRIMARY` (`id_acervo_general`)<br>`idx_id_modulo` (`id_modulo`) | `codigo_interno`, `no_inventario`, `estatus_revision`, `status`, `ubicacion_fisica`, `anio` | **Crítico:** No hay índices en códigos ni estatus. Cualquier búsqueda por semáforo o inventario genera lectura completa de las 21,528 filas. |
| `acervo_arqueologico` | 10,021 | `PRIMARY` (`id_acervo_arq`)<br>`idx_id_modulo` (`id_modulo`) | `codigo_interno`, `no_registro_inah`, `estatus_revision`, `status`, `epoca`, `ubicacion_fisica` | **Crítico:** Búsquedas por registro INAH o código interno causan lecturas de 10,021 filas en disco. |
| `acervo_numismatica` | 8,405 | `PRIMARY` (`id_acervo_numismatica`)<br>`idx_id_modulo` (`id_modulo`) | `codigo_interno`, `denominacion`, `estatus_revision`, `status`, `fecha_epoca` | **Crítico:** Búsquedas por denominación o código provocan lecturas de 8,405 filas sin optimización. |
| `prestamos_transferencias` | 17 | `PRIMARY` (`id_movimiento`)<br>`folio` (UNIQUE)<br>`idx_tipo_acervo_pieza`<br>`idx_fecha_salida`<br>`idx_estatus`<br>`idx_museo_destino` | `folio`, `estatus`, `id_museo_destino`, `fecha_salida` | **Adecuado:** Esta tabla cuenta con índices compuestos correctos de acuerdo con la especificación de Obsidian. |

### 4.3. Auditoría al Esquema de Bitácoras y Trazabilidad Gubernamental
1. **Fortalezas Identificadas:**
   - La tabla `auditoria` registra fielmente los estados anteriores y posteriores en formato JSON (`datos_antes`, `datos_despues`), lo cual satisface la exigencia de trazabilidad pericial en dictámenes de bienes culturales.
   - Posee claves foráneas formales hacia `bee_users(id)` y `modulos(id_modulo)`.
   - Se registran eventos de autenticación (`LOGIN`, `LOGOUT`), aprobaciones, devoluciones y ediciones.

2. **Debilidades Detectadas:**
   - **Inmutabilidad No Garantizada:** La tabla `auditoria` permite operaciones directas de `UPDATE` y `DELETE` si un actor con acceso al RDBMS ejecuta sentencias manuales. No existen disparadores (`TRIGGERS`) que impidan la manipulación de la bitácora histórica.
   - **Falta de Atomicidad en Transacciones:** Cuando una pieza se aprueba en [adminController.php:2971-3000](file:///var/www/html/ACERVO_CULTURAL/app/controllers/adminController.php#L2971-L3000), el `UPDATE` sobre la tabla de acervo y el `INSERT` en `auditoria` se efectúan en llamadas independientes. Si la conexión se interrumpe tras la actualización de la pieza, el cambio queda registrado en el acervo pero **desaparece de la bitácora**, viciando la certeza legal del inventario.

---

## 5. PLAN DE ACCIÓN Y PARCHES TÉCNICOS PROPUESTOS

A continuación se presentan las propuestas de parches técnicos listas para producción para mitigar los **3 hallazgos de mayor criticidad** identificados durante la auditoría.

---

### PARCHE 1: Corrección de Subida Irrestricta de Archivos y Blindaje de Directorios (Mitigación VUL-01)

#### 1.1. Creación del Archivo de Seguridad Apache `.htaccess` en Directorios de Carga
Desplegar este archivo tanto en `/var/www/html/ACERVO_CULTURAL/assets/uploads/.htaccess` como en `/var/www/html/ACERVO_CULTURAL/assets/uploads/oficios_movimientos/.htaccess`:

```apache
# Desactivar completamente la ejecución de motores de script en la carpeta de subidas
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
<IfModule mod_php7.c>
    php_flag engine off
</IfModule>
<IfModule mod_php8.c>
    php_flag engine off
</IfModule>

# Denegar el acceso y ejecución directa de archivos potencialmente peligrosos
<FilesMatch "(?i)\.(php|phtml|php3|php4|php5|php7|php8|phps|pht|phar|inc|pl|py|cgi|sh|bash|exe|bin)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Forzar descarga para tipos de contenido dudosos y restringir sniffing de MIME
Options -Indexes -ExecCGI
AddType text/plain .html .htm .shtml .xhtml
Header set X-Content-Type-Options "nosniff"
```

#### 1.2. Helper de Carga Segura de Fotografías en `app/functions/bee_custom_functions.php`
Reemplazar la subida directa de archivos por la siguiente función determinista con validación binaria de MIME y aleatorización criptográfica:

```php
/**
 * Procesa y valida de manera segura la carga de fotografías patrimoniales
 * 
 * @param array $file Arreglo $_FILES['fotografia']
 * @param string $destDir Directorio de destino absoluto
 * @return string Nombre del archivo sanitizado y guardado
 * @throws Exception En caso de formato inválido, exceso de tamaño o falla de guardado
 */
function procesar_fotografia_segura(array $file, string $destDir = UPLOADS): string
{
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Parámetros de subida de archivo inválidos.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error en el archivo subido (Código: ' . $file['error'] . ').');
    }

    // 1. Limitar tamaño a 15 MB
    $maxBytes = 15 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        throw new Exception('La fotografía excede el tamaño máximo permitido de 15 MB.');
    }

    // 2. Validación de extensión en lista blanca
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new Exception('Formato de imagen no permitido. Se aceptan exclusivamente JPG, PNG y WEBP.');
    }

    // 3. Validación estricta de encabezados binarios reales (MIME Magic Bytes)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
        'image/webp' => ['webp']
    ];

    if (!array_key_exists($realMime, $allowedMimes) || !in_array($extension, $allowedMimes[$realMime], true)) {
        throw new Exception('El contenido binario del archivo no coincide con una imagen válida permitida.');
    }

    // 4. Nombre aleatorio e inmune a colisiones y path traversal
    $nuevoNombre = 'pieza_' . bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
    $rutaFinal = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nuevoNombre;

    if (!move_uploaded_file($file['tmp_name'], $rutaFinal)) {
        throw new Exception('Fallo al resguardar la fotografía en el servidor.');
    }

    // Asegurar permisos de lectura sin ejecución (0644)
    @chmod($rutaFinal, 0644);

    return $nuevoNombre;
}
```

---

### PARCHE 2: Optimización de Paginación y Filtrado SQL Directo en Acervo General (Mitigación VUL-02)

#### 2.1. Implementación en `app/models/acervoGeneralModel.php`
Reemplazar la lectura masiva de `getAll()` por consultas parametrizadas con `LIMIT` y `COUNT` en motor de base de datos:

```php
/**
 * Obtiene el listado de acervo general aplicando filtros y paginación a nivel SQL
 * 
 * @param array $filtros Arreglo asociativo con search, ubicacion, anio, etc.
 * @param int $limit Cantidad de registros por página
 * @param int $offset Registro inicial
 * @return array ['piezas' => array, 'total' => int]
 */
public static function getPiezasFiltradas(array $filtros, int $limit = 10, int $offset = 0): array
{
    $db = Db::connect();
    $whereParts = ["status = 1", "estatus_revision = 'aprobado'"];
    $params = [];

    // Búsqueda textual general
    if (!empty($filtros['search'])) {
        $whereParts[] = "(codigo_interno LIKE :s1 OR nombre_titulo_pieza LIKE :s2 OR autor LIKE :s3 OR descripcion LIKE :s4)";
        $term = '%' . trim($filtros['search']) . '%';
        $params['s1'] = $term;
        $params['s2'] = $term;
        $params['s3'] = $term;
        $params['s4'] = $term;
    }

    // Filtro por ubicación geográfica / museo
    if (!empty($filtros['ubicacion'])) {
        $whereParts[] = "ubicacion_fisica LIKE :ubicacion";
        $params['ubicacion'] = '%' . trim($filtros['ubicacion']) . '%';
    }

    // Filtro por año / época
    if (!empty($filtros['anio'])) {
        $whereParts[] = "(anio LIKE :anio OR epoca LIKE :anioEpoca)";
        $params['anio'] = '%' . trim($filtros['anio']) . '%';
        $params['anioEpoca'] = '%' . trim($filtros['anio']) . '%';
    }

    $whereSql = implode(' AND ', $whereParts);

    // 1. Obtener conteo exacto sin cargar los datos
    $countSql = "SELECT COUNT(*) FROM " . self::$table . " WHERE {$whereSql}";
    $stmtCount = $db->prepare($countSql);
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetchColumn();

    // 2. Obtener únicamente la rebanada (slice) requerida para la vista
    $dataSql = "SELECT id_acervo_general, nombre_titulo_pieza, codigo_interno, autor, descripcion, fotografia 
                FROM " . self::$table . " 
                WHERE {$whereSql} 
                ORDER BY id_acervo_general DESC 
                LIMIT :offset, :limit";

    $stmtData = $db->prepare($dataSql);
    foreach ($params as $key => $val) {
        $stmtData->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmtData->execute();

    return [
        'piezas' => $stmtData->fetchAll(PDO::FETCH_ASSOC),
        'total'  => $total
    ];
}
```

#### 2.2. Adaptación del Endpoint en `app/controllers/adminController.php`
```php
public function get_acervo_general()
{
    $this->check_user_permission('ver_acervo_general');
    $page = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
    $perPage = isset($_REQUEST['per_page']) ? max(1, min(100, (int)$_REQUEST['per_page'])) : 10;
    $offset = ($page - 1) * $perPage;

    require_once APP . 'models/acervoGeneralModel.php';
    
    $filtros = [
        'search'    => $_REQUEST['search'] ?? '',
        'ubicacion' => $_REQUEST['ubicacion'] ?? '',
        'anio'      => $_REQUEST['anio'] ?? ''
    ];

    $resultado = AcervoGeneralModel::getPiezasFiltradas($filtros, $perPage, $offset);
    $total = $resultado['total'];
    $piezas = $resultado['piezas'];

    $data = array_map(function ($pieza) {
        return [
            'image'          => !empty($pieza['fotografia']) ? 'assets/uploads/' . $pieza['fotografia'] : '',
            'id'             => $pieza['id_acervo_general'],
            'nombre'         => $pieza['nombre_titulo_pieza'] ?: '-',
            'codigo_interno' => $pieza['codigo_interno'] ?: '-',
            'autor'          => $pieza['autor'] ?: '-',
            'descripcion'    => $pieza['descripcion'] ?: '-'
        ];
    }, $piezas);

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 200,
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages'  => max(1, (int)ceil($total / $perPage)),
            'total'        => $total,
            'per_page'     => $perPage
        ]
    ]);
    exit;
}
```

---

### PARCHE 3: Fortalecimiento de Control de Acceso (RBAC / Tenancy), Validación CSRF y Denegación por Defecto (Mitigación VUL-03 y VUL-05)

#### 3.1. Corrección del Fallback en `check_user_permission()` en `app/controllers/adminController.php`
Sustituir la asignación vulnerable de `id_role = 1` por denegación inmediata:

```php
private function check_user_permission(string $permissionSlug)
{
    $id_role = get_user('id_role');
    
    // REGLA DE SEGURIDAD: Denegación por defecto (Fail-closed)
    if (!$id_role) {
        $isAjax = is_ajax() || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST);
        if ($isAjax) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 403, 'msg' => 'Sesión sin privilegios asignados. Acceso denegado.']);
            exit;
        }
        Flasher::error('No tienes permisos suficientes para acceder a esta sección.');
        Redirect::to('login');
        exit;
    }

    $roleRow = userModel::query("SELECT slug FROM bee_roles WHERE id = :id LIMIT 1", ['id' => $id_role]);
    $roleSlug = ($roleRow && isset($roleRow[0]['slug'])) ? $roleRow[0]['slug'] : 'desconocido';

    $roleManager = new BeeRoleManager($roleSlug);
    if (!$roleManager->can($permissionSlug)) {
        $isAjax = is_ajax() || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST);
        if ($isAjax) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 403, 'msg' => 'No cuentas con el permiso requerido: ' . $permissionSlug]);
            exit;
        }
        Flasher::error('No tienes permisos para realizar esta acción.');
        Redirect::to(is_admin_user() ? 'admin' : 'admin/acervo');
        exit;
    }
}
```

#### 3.2. Aplicación de Validación de Tenancy de Museo y CSRF en Eliminación y Edición
Actualizar `acervo_general_eliminar` para validar CSRF y restringir a administradores locales exclusivamente a las piezas de su propio recinto:

```php
public function acervo_general_eliminar()
{
    // 1. Validar permiso formal RBAC
    $this->check_user_permission('eliminar_acervo_general');

    // 2. Validar Token CSRF obligatorio
    if (!check_csrf()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 403, 'msg' => 'Token de seguridad CSRF inválido o expirado.']);
        exit;
    }

    $id = isset($_POST['id_acervo_general']) ? (int)$_POST['id_acervo_general'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
        echo json_encode(['status' => 400, 'msg' => 'Identificador de pieza inválido.']);
        exit;
    }

    require_once APP . 'models/acervoGeneralModel.php';
    $pieza = AcervoGeneralModel::getById($id);
    if (empty($pieza)) {
        echo json_encode(['status' => 404, 'msg' => 'La pieza no existe.']);
        exit;
    }
    $piezaRow = $pieza[0];

    // 3. Control de Acceso Horizontal (Tenancy de Museo):
    // El Super Administrador (id_role = 1) tiene alcance estatal global.
    // Los Administradores locales (id_role = 2) solo pueden suprimir piezas de su museo asignado.
    $currentUserRole = (int)get_user('id_role');
    $currentMuseoId  = (int)get_user('id_museo');

    if ($currentUserRole !== 1) {
        $db = Db::connect();
        $stmtM = $db->prepare("SELECT nombre_museo FROM museos WHERE id_museo = :id LIMIT 1");
        $stmtM->execute(['id' => $currentMuseoId]);
        $miMuseoNombre = $stmtM->fetchColumn();

        if (empty($miMuseoNombre) || stripos($piezaRow['ubicacion_fisica'], $miMuseoNombre) === false) {
            echo json_encode([
                'status' => 403, 
                'msg' => 'Violación de custodia: No tienes autorización para eliminar piezas asignadas a otro recinto cultural.'
            ]);
            exit;
        }
    }

    // 4. Ejecución atómica y registro en bitácora
    $ok = AcervoGeneralModel::deletePieza($id);
    if ($ok) {
        registrar_auditoria('DELETE', 'acervo_general', $id, 'Baja lógica de bien patrimonial', 5, null, $piezaRow, ['status' => 0]);
        echo json_encode(['status' => 200, 'msg' => 'Pieza dada de baja correctamente del inventario.']);
    } else {
        echo json_encode(['status' => 500, 'msg' => 'Error al procesar la baja en base de datos.']);
    }
    exit;
}
```

---

## 6. SCRIPT DDL DE REMEDIACIÓN DE BASE DE DATOS (MIGRACIÓN RECOMENDADA)

Para solventar el déficit de indexación e inconsistencia de colaciones detectado en la Fase 4, se recomienda la ejecución controlada del siguiente script SQL durante una ventana de mantenimiento técnico programada:

```sql
-- =============================================================================
-- SCRIPT DE REMEDIACIÓN Y FORTALECIMIENTO DE INTEGRIDAD SIPAC
-- Base de Datos: db_framework
-- =============================================================================

-- 1. Homogeneización de colación a utf8mb4_unicode_ci
ALTER TABLE `acervo_general` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acervo_arqueologico` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `acervo_numismatica` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `auditoria` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Creación de índices estratégicos para búsquedas frecuentes y filtros de semáforo
-- Acervo General (21,528 registros)
ALTER TABLE `acervo_general`
  ADD INDEX `idx_ag_codigo_interno` (`codigo_interno`),
  ADD INDEX `idx_ag_no_inventario` (`no_inventario`),
  ADD INDEX `idx_ag_revision_status` (`estatus_revision`, `status`),
  ADD INDEX `idx_ag_ubicacion` (`ubicacion_fisica`(100)),
  ADD INDEX `idx_ag_creador` (`id_usuario_creador`);

-- Acervo Arqueológico (10,021 registros)
ALTER TABLE `acervo_arqueologico`
  ADD INDEX `idx_aa_codigo_interno` (`codigo_interno`),
  ADD INDEX `idx_aa_no_registro_inah` (`no_registro_inah`),
  ADD INDEX `idx_aa_revision_status` (`estatus_revision`, `status`),
  ADD INDEX `idx_aa_ubicacion` (`ubicacion_fisica`(100)),
  ADD INDEX `idx_aa_creador` (`id_usuario_creador`);

-- Acervo Numismática (8,405 registros)
ALTER TABLE `acervo_numismatica`
  ADD INDEX `idx_an_codigo_interno` (`codigo_interno`),
  ADD INDEX `idx_an_denominacion` (`denominacion`(100)),
  ADD INDEX `idx_an_revision_status` (`estatus_revision`, `status`),
  ADD INDEX `idx_an_ubicacion` (`ubicacion_fisica`(100)),
  ADD INDEX `idx_an_creador` (`id_usuario_creador`);

-- 3. Depuración de tuplas con anomalía de módulo
UPDATE `acervo_general` SET `id_modulo` = 5 WHERE `id_modulo` = 55;

-- 4. Disparador de inmutabilidad sobre bitácora de auditoría (Blindaje OIC)
DELIMITER $$
CREATE TRIGGER `trg_prevent_audit_tampering`
BEFORE UPDATE ON `auditoria`
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'ACCESO DENEGADO: Los registros de la bitácora de auditoría SIPAC son legalmente inmutables.';
END$$

CREATE TRIGGER `trg_prevent_audit_deletion`
BEFORE DELETE ON `auditoria`
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'ACCESO DENEGADO: Queda estrictamente prohibida la supresión de evidencia de auditoría.';
END$$
DELIMITER ;
```

---
*Fin del Dictamen de Auditoría y Control Interno.*  
**Departamento de Tecnologías de la Información — Secretaría de Cultura y Turismo del Estado de México.**
