---
title: Estructura de Directorios del Proyecto
type: technical
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - estructura
  - directorios
  - organizacion
---

# 📂 Estructura de Directorios del Proyecto

El proyecto sigue una estructura limpia y desacoplada, separando la lógica del framework (`/app`), los recursos públicos y assets del navegador (`/assets`), las plantillas de presentación visual (`/templates`) y la base de conocimiento (`/documentacion`).

---

## 1. Árbol General del Directorio

```text
/var/www/html/ACERVO_CULTURAL/
├── .htaccess                           # Reglas de reescritura de URL para Apache
├── index.php                           # Punto de entrada Front Controller (Bee::fly())
├── db_beeframework.sql                 # Dump inicial de la estructura de base de datos
├── app/                                # Núcleo del aplicativo (Backend MVC)
│   ├── classes/                        # Clases del motor (Bee, Db, View, Model, Csrf, etc.)
│   ├── config/                         # Parámetros globales y credenciales (bee_config.php)
│   ├── controllers/                    # Controladores de negocio y endpoints AJAX
│   ├── functions/                      # Funciones de utilidad global (bee_custom_functions.php)
│   └── models/                         # Clases de acceso a datos y sentencias SQL
├── assets/                             # Archivos estáticos consumidos por el cliente
│   ├── css/                            # Hojas de estilo (Bootstrap, SB Admin 2, custom-admin.css)
│   ├── js/                             # Scripts cliente (acervo.js, revision.js, prestamos.js)
│   ├── images/                         # Recursos gráficos, logotipos de gobierno, íconos
│   ├── plugins/                        # Librerías de terceros (DataTables, SweetAlert2, Select2)
│   └── uploads/                        # Almacén de fotografías de acervo y comprobantes
├── templates/                          # Capa de presentación (Vistas HTML/PHP)
│   ├── includes/                       # Componentes compartidos (sidebar, navbar, footer)
│   └── views/                          # Vistas organizadas por módulo funcional
│       ├── admin/                      # Vistas del panel de administración
│       │   ├── acervo/                 # Catálogo interactivo de piezas
│       │   ├── auditoria/              # Visor forense de bitácora
│       │   ├── catalogos/              # Mantenimiento de tablas auxiliares
│       │   ├── prestamos/              # Control de préstamos y transferencias
│       │   ├── registrar/              # Formularios de alta con toggle dimensional
│       │   ├── revision/               # Bandeja curatorial con semáforo
│       │   ├── roles/                  # Asignación de perfiles y privilegios
│       │   └── usuarios/               # Gestión de cuentas de usuario
│       ├── login/                      # Interfaz de acceso al sistema
│       └── error/                      # Páginas de error HTTP (403, 404, 500)
└── documentacion/                      # Bóveda Obsidian de documentación oficial
    └── boveda_obsidian/                # Notas interconectadas con Wikilinks y Diagramas
```

---

## 2. Descripción de Subdirectorios Clave

### 2.1. Directorio `/app` (Lógica del Servidor)
- **`app/config/bee_config.php`**: Define constantes del sistema:
  - `IS_LOCAL`: Bandera booleana para alternar automáticamente entre credenciales locales de desarrollo y producción.
  - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`: Configuración de acceso MySQL.
  - `URL`: Raíz absoluta de las URLs públicas (`http://servidor/ACERVO_CULTURAL/`).
  - `UPLOADS`: Ruta absoluta del sistema de archivos para guardar imágenes subidas.
- **`app/controllers/adminController.php`**: Es el controlador central. Maneja:
  - Procesamiento de filtros avanzados para las tablas de acervo.
  - Validaciones de inserción y actualización de piezas.
  - Reglas de negocio para cambios de estatus en la revisión curatorial.
  - Creación, devolución y cancelación de movimientos de préstamo.
  - Exportación de listados en Excel y fichas técnicas en PDF.
- **`app/models/`**: Clases PDO encargadas de realizar consultas preparadas (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) hacia las tablas de la base de datos.

### 2.2. Directorio `/assets` (Recursos Estáticos)
- **`assets/css/admin/custom-admin.css`**: Contiene reglas CSS personalizadas críticas, entre ellas:
  - Menú de acciones flotante (`.btn__actions`).
  - Corrección de desbordamiento en tablas (`.table-responsive:has(.dropdown-menu.show)`).
  - Estilos de insignias para el semáforo curatorial (`.badge-aprobado`, `.badge-pendiente`, `.badge-cambios`, `.badge-rechazado`).
  - Animaciones de carga y transiciones de modales.
- **`assets/js/admin/`**:
  - `acervo.js`: Control de eventos de la tabla de catálogo, búsqueda dinámica y apertura de modales de edición.
  - `registrar.js`: Control del conmutador de dimensiones (bidimensional vs tridimensional) y carga de campos condicionales de ubicación física (museo vs "Otro").
  - `revision.js`: Lógica de aprobación, rechazo y solicitud de modificaciones con diálogos interactivos de SweetAlert2.
  - `prestamos_transferencias.js`: Formulario de préstamos con selector de piezas, cálculo de fechas y registro de devoluciones.

### 2.3. Directorio `/templates` (Plantillas de Vista)
- Utiliza la técnica de composición mediante inclusión:
  1. `templates/includes/headerView.php`: Carga de metadatos, fuentes, Boxicons y estilos CSS.
  2. `templates/includes/sidebar.php`: Menú lateral adaptativo que evalúa permisos en tiempo real (`user_can()`).
  3. `templates/views/[modulo]/[vista]View.php`: Contenido principal de la página.
  4. `templates/includes/footerView.php`: Scripts JS, inicialización de plugins y cierre de etiquetas HTML.

---

## 3. Permisos en el Sistema de Archivos

Para garantizar el funcionamiento sin interrupciones del aplicativo en un servidor Linux:
- El directorio `assets/uploads/` y sus subcarpetas deben pertenecer al usuario del servidor web (`www-data:www-data` en Debian/Ubuntu) con permisos de escritura `775` o `755`.
- Los archivos PHP deben mantener permisos de solo lectura para el usuario web (`644` o `664`) para evitar inyección o modificación no autorizada de código.

---

## 4. Referencias Cruzadas
- Conoce los controladores en: [[01 - Controladores y Flujo de Peticiones]]
- Conoce los scripts JS en: [[05 - Componentes Frontend, Estilos CSS y JavaScript]]
- Revisa las tareas de servidor en: [[06 - Despliegue, Servidor y Mantenimiento]]

