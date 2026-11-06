---
title: Preguntas Frecuentes y Solución de Problemas (FAQ)
type: reference
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - faq
  - soporte
  - resolucion-problemas
  - ayuda
---

# ❓ Preguntas Frecuentes y Solución de Problemas (FAQ)

Esta sección reúne las dudas operativas más comunes de los usuarios y una guía de resolución de incidencias técnicas para administradores de sistemas.

---

## 1. Preguntas Frecuentes de Usuarios

### P1. ¿Por qué una pieza que acabo de registrar no aparece en el Catálogo General de consulta?
> **Respuesta**: Al registrar una pieza, esta ingresa automáticamente con estatus **"Pendiente"** (marcador azul). Para evitar que información incompleta se publique en el inventario oficial, las obras solo aparecen en el catálogo general una vez que un curador o administrador las haya evaluado y autorizado mediante el botón verde del **Semáforo Curatorial**.

### P2. ¿Dónde quedaron los campos "Materia" y "Colección" en Acervo General?
> **Respuesta**: Por disposición de la normativa técnica institucional, se simplificó el formulario para eliminar duplicidad. La composición de la obra se registra de manera unificada en el campo **Material** (ej. *Óleo sobre lienzo*, *Bronce*, *Madera estofada*).

### P3. ¿Cómo registro una obra que se encuentra en una dependencia fuera de los museos de la Secretaría?
> **Respuesta**: En el campo **Ubicación Física**, despliega la lista y selecciona la última opción: **"Otro (Especificar sede externa o dependencia)"**. Se habilitará de inmediato un campo de texto adicional para que escribas el nombre exacto de la sede (ejemplo: *Secretaría de Finanzas - Despacho Principal* o *Galería de Arte Bicentenario*).

### P4. Mi pieza fue devuelta con "Cambios Solicitados", ¿dónde veo qué debo corregir?
> **Respuesta**: Ingresa a tu módulo de registro o catálogo, busca la pieza marcada en amarillo y pulsa en **"Editar Pieza"**. En la parte superior del formulario encontrarás una alerta amarilla destacada con las **Observaciones del Revisor**, detallando los puntos exactos que debes subsanar antes de reenviarla.

### P5. ¿Por qué no puedo registrar un préstamo para una pieza específica?
> **Respuesta**: El sistema implementa dos candados de seguridad patrimonial:
> 1. Solo se pueden prestar piezas con dictamen **Aprobado**.
> 2. Una pieza no puede prestarse si actualmente cuenta con otro préstamo con estatus **Activo** sin registrar retorno.

---

## 2. Resolución de Problemas Técnicos (Troubleshooting)

### Incidencia 1: Error al subir fotografías de piezas ("Fallo de carga o pantalla en blanco")
- **Causa probable**: El tamaño de la imagen excede los límites configurados en `php.ini` o el directorio no cuenta con permisos de escritura para el usuario de Apache.
- **Solución**:
  1. Verifica en `/etc/php/8.x/apache2/php.ini` que:
     ```ini
     upload_max_filesize = 32M
     post_max_size = 64M
     ```
  2. Comprueba los permisos del directorio de uploads:
     ```bash
     sudo chown -R www-data:www-data /var/www/html/ACERVO_CULTURAL/assets/uploads/
     sudo chmod -R 775 /var/www/html/ACERVO_CULTURAL/assets/uploads/
     ```

---

### Incidencia 2: Error 404 al navegar entre módulos ("Not Found /admin/...")
- **Causa probable**: El módulo de reescritura de Apache `mod_rewrite` no está activo o la directiva `AllowOverride` está en `None`.
- **Solución**:
  ```bash
  sudo a2enmod rewrite
  ```
  En la configuración del VirtualHost (`/etc/apache2/sites-available/000-default.conf`):
  ```apache
  <Directory /var/www/html/ACERVO_CULTURAL>
      AllowOverride All
      Require all granted
  </Directory>
  ```
  Reinicia Apache: `sudo systemctl restart apache2`.

---

### Incidencia 3: Desplazamiento y recorte del menú "Acciones" en tablas
- **Diagnóstico anterior**: Al abrir el menú desplegable en pantallas pequeñas o tablas anchas, la lista de opciones se cortaba con el borde inferior de la tabla o provocaba un scroll vertical interno molesto.
- **Solución aplicada**: Se configuró Popper con `strategy: "fixed"`, `data-bs-boundary="viewport"` y la regla CSS dinámica `.table-responsive:has(.dropdown-menu.show) { overflow: visible !important; }`. Si se presenta en navegadores muy antiguos, actualice el navegador o verifique que `assets/css/admin/custom-admin.css` esté cargando la versión más reciente (Ctrl + F5 para limpiar caché).

---

### Incidencia 4: Discrepancia de medidas al editar piezas devueltas
- **Diagnóstico anterior**: Al cambiar una pieza de tridimensional a bidimensional durante la atención de cambios solicitados, el valor bidimensional no se reflejaba en la revisión.
- **Causa**: Existía un identificador duplicado `#editar-cm` en el DOM de la vista.
- **Estado**: **Resuelto y parchado** en la versión actual del código.

---

### Incidencia 5: Olvido de contraseña de Super Administrador
- **Procedimiento de recuperación de emergencia vía consola**:
  1. Conéctate a la terminal del servidor.
  2. Genera un hash seguro mediante PHP CLI:
     ```bash
     php -r "echo password_hash('TuNuevaClave2026', PASSWORD_BCRYPT) . PHP_EOL;"
     ```
  3. Actualiza el usuario en MariaDB/MySQL:
     ```bash
     mysql -u srvccm -p'720./Srv.xi8' db_framework -e "UPDATE bee_users SET password = 'HASH_GENERADO' WHERE id = 1;"
     ```

---

## 3. Referencias Cruzadas
- Conoce los requisitos del servidor: [[03 - Stack Tecnológico y Dependencias]]
- Procedimiento de despliegue: [[06 - Despliegue, Servidor y Mantenimiento]]
- Diccionario de tablas: [[01 - Diccionario de Datos Completo]]

