---
title: Mapa de Navegación del Sistema
type: guide
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - navegacion
  - arquitectura
  - diagramas
  - flujos
---

# 🗺️ Mapa de Navegación del Sistema

Esta nota describe la topología de navegación de la aplicación web, ilustrando las rutas accesibles según los roles de usuario y los flujos de interacción entre módulos.

---

## 1. Diagrama de Flujo de Navegación Global

El acceso a los diferentes módulos está gobernado por el estado de autenticación y los permisos RBAC (Role-Based Access Control) del usuario.

```mermaid
flowchart TD
    Inicio["/ (Home / Login)"] --> AuthCheck{"¿Sesión Activa?"}
    
    AuthCheck -- No --> LoginForm["/login (Formulario de Ingreso)"]
    LoginForm --> ValidaCred{"Validar Credenciales"}
    ValidaCred -- Inválidas --> LoginForm
    ValidaCred -- Válidas --> Dashboard["/admin (Dashboard Principal)"]
    
    AuthCheck -- Sí --> Dashboard
    
    Dashboard --> NavMenu["Barra Lateral de Navegación"]
    
    NavMenu --> ModAcervo["/admin/acervo (Catálogo Unificado)"]
    NavMenu --> ModRegistrar["/admin/registrar (Alta de Bienes Culturales)"]
    NavMenu --> ModRevision["/admin/revision (Revisión y Semáforo)"]
    NavMenu --> ModPrestamos["/admin/prestamos_transferencias (Movimientos)"]
    NavMenu --> ModAuditoria["/admin/auditoria (Bitácora de Eventos)"]
    NavMenu --> ModUsuarios["/admin/usuarios (Gestión de Personal)"]
    NavMenu --> ModRoles["/admin/roles (Control de Privilegios)"]
    NavMenu --> ModCatalogos["/admin/catalogos (Tablas Maestras)"]
    NavMenu --> ModPerfil["/admin/perfil (Mi Cuenta)"]
    NavMenu --> Logout["/logout (Cerrar Sesión)"]
    
    ModAcervo --> SubGen["Acervo General"]
    ModAcervo --> SubArq["Acervo Arqueológico"]
    ModAcervo --> SubNum["Acervo Numismático"]
    ModAcervo --> FichaTec["Ficha Técnica Modal / Vista"]
    
    ModRevision --> RevDetalle["Modal de Evaluación Curatorial"]
    RevDetalle --> RevAccion["Aprobar / Solicitar Cambios / Rechazar"]
    
    ModPrestamos --> MovNuevo["Registrar Movimiento (Préstamo / Traslado)"]
    ModPrestamos --> MovDevolver["Registrar Devolución"]
    ModPrestamos --> MovCancelar["Cancelar Movimiento"]
    ModPrestamos --> MovDetalle["Expediente Completo del Movimiento"]
```

---

## 2. Mapa de Rutas y Controladores

A continuación se detalla la correspondencia entre las URIs del sistema, los métodos en los controladores y las vistas asociadas:

| URI del Sistema | Controlador / Método | Archivo de Vista | Descripción Funcional |
| :--- | :--- | :--- | :--- |
| `/login` | `loginController->index()` | `login/loginView.php` | Formulario de autenticación por usuario y contraseña. |
| `/logout` | `logoutController->index()` | *(Redirección a login)* | Cierra sesión segura y destruye variables globales. |
| `/admin` | `adminController->index()` | `admin/indexView.php` | Métricas generales, conteo de piezas, accesos rápidos. |
| `/admin/acervo` | `adminController->acervo()` | `admin/acervo/acervoView.php` | Listado tabular de piezas con filtrado AJAX, edición y eliminación. |
| `/admin/registrar` | `adminController->registrar()` | `admin/registrar/registrarView.php` | Formulario dinámico de alta de piezas con carga fotográfica. |
| `/admin/revision` | `adminController->revision()` | `admin/revision/revisionView.php` | Bandeja curatorial de control de calidad y semáforo. |
| `/admin/prestamos_transferencias` | `adminController->prestamos_transferencias()` | `admin/prestamos/prestamosView.php` | Control de traslados, préstamos entre recintos y devoluciones. |
| `/admin/auditoria` | `adminController->auditoria()` | `admin/auditoria/auditoriaView.php` | Monitoreo forense de modificaciones a registros patrimoniales. |
| `/admin/usuarios` | `adminController->usuarios()` | `admin/usuarios/usuariosView.php` | Catálogo de usuarios, altas, ediciones y asignación de rol. |
| `/admin/roles` | `adminController->roles()` | `admin/roles/rolesView.php` | Configuración de perfiles y asignación de permisos específicos. |
| `/admin/catalogos` | `adminController->catalogos()` | `admin/catalogos/catalogosView.php` | Gestión de vocabularios controlados (épocas, materiales, museos). |
| `/admin/perfil` | `adminController->perfil()` | `admin/perfilView.php` | Actualización de datos de cuenta del usuario conectado. |

---

## 3. Matriz de Permisos por Rol

El sistema implementa 4 roles predefinidos con diferentes alcances de navegación:

```mermaid
graph LR
    subgraph Roles
        SA["Super Administrador"]
        AD["Administrador"]
        CP["Capturista"]
        LG["Lector General"]
    end

    subgraph Modulos
        M_Admin["Gestión del Sistema (Roles, Usuarios, Auditoría)"]
        M_Rev["Bandeja de Revisión Curatorial"]
        M_Mov["Préstamos y Transferencias"]
        M_Reg["Registro y Edición de Piezas"]
        M_Con["Consulta y Fichas Técnicas"]
    end

    SA --> M_Admin
    SA --> M_Rev
    SA --> M_Mov
    SA --> M_Reg
    SA --> M_Con

    AD --> M_Rev
    AD --> M_Mov
    AD --> M_Reg
    AD --> M_Con

    CP --> M_Reg
    CP --> M_Con
    
    LG --> M_Con
```

Para más detalles sobre la implementación técnica de este mecanismo, consulta:
- [[03 - Sistema de Roles y Permisos (RBAC)]]
- [[01 - Inicio de Sesión y Perfiles de Usuario]]

