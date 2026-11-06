---
title: Despliegue, Servidor y Mantenimiento
type: technical
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - despliegue
  - apache
  - linux
  - mysql
  - devops
---

# 🚀 Despliegue, Servidor y Mantenimiento

Esta guía técnica describe el procedimiento para desplegar, configurar y mantener en óptimas condiciones de disponibilidad y seguridad el sistema en un entorno Linux en producción.

---

## 1. Configuración del Servidor Web Apache

### 1.1. Habilitación de Módulos Requeridos
Asegúrate de que Apache cuente con los módulos `rewrite` y `headers` activos:
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

### 1.2. VirtualHost de Producción
Configuración recomendada para `/etc/apache2/sites-available/acervo_cultural.conf`:
```apache
<VirtualHost *:80>
    ServerName acervo.cultura.gob.mx
    DocumentRoot /var/www/html/ACERVO_CULTURAL

    <Directory /var/www/html/ACERVO_CULTURAL>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/acervo_cultural_error.log
    CustomLog ${APACHE_LOG_DIR}/acervo_cultural_access.log combined
</VirtualHost>
```

### 1.3. Directivas de `.htaccess`
El archivo `.htaccess` en la raíz del proyecto asegura la canalización hacia el Front Controller y bloquea el acceso a archivos sensibles:
```apache
Options -Indexes

<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /ACERVO_CULTURAL/

  # Bloquear acceso a archivos sensibles
  RewriteRule ^(\.git|\.env|composer\.(json|lock)|.*\.sql|.*\.txt)$ - [F,L]

  # Canalizar peticiones hacia index.php
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php?uri=$1 [QSA,L]
</IfModule>
```

---

## 2. Permisos y Estructura en Sistema de Archivos

Para permitir que los usuarios carguen fotografías sin comprometer la seguridad del sistema operativo:

```bash
# Asignar propiedad al usuario de Apache
sudo chown -R www-data:www-data /var/www/html/ACERVO_CULTURAL

# Permisos para carpetas (755) y archivos (644)
find /var/www/html/ACERVO_CULTURAL/ -type d -exec chmod 755 {} \;
find /var/www/html/ACERVO_CULTURAL/ -type f -exec chmod 644 {} \;

# Directorio de subida de imágenes con permisos de escritura
chmod -R 775 /var/www/html/ACERVO_CULTURAL/assets/uploads/
```

---

## 3. Parámetros de Entorno PHP (`php.ini`)

Valores recomendados para el entorno de producción en `/etc/php/8.x/apache2/php.ini`:

```ini
; Rendimiento y Capacidad de Carga
upload_max_filesize = 32M
post_max_size = 64M
memory_limit = 256M
max_execution_time = 120

; Zona Horaria Institucional
date.timezone = "America/Mexico_City"

; Seguridad en Producción
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/acervo_cultural_errors.log
expose_php = Off
session.cookie_httponly = 1
session.use_only_cookies = 1
```

---

## 4. Estrategia de Respaldos de Base de Datos

Se recomienda programar un respaldo nocturno automatizado mediante un script en el `crontab` del sistema:

### Script de Backup (`/usr/local/bin/backup_acervo.sh`):
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/acervo_cultural"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="db_framework"
DB_USER="srvccm"
DB_PASS="720./Srv.xi8"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS --single-transaction --routines --triggers $DB_NAME | gzip > "$BACKUP_DIR/backup_${DB_NAME}_$TIMESTAMP.sql.gz"

# Retener únicamente los últimos 30 días de respaldos
find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +30 -exec rm {} \;
```

### Configuración en Cron (`crontab -e`):
```cron
# Ejecutar respaldo diariamente a las 02:00 AM
0 2 * * * /usr/local/bin/backup_acervo.sh > /dev/null 2>&1
```

---

## 5. Referencias Cruzadas
- Arquitectura del framework: [[02 - Arquitectura Bee Framework MVC]]
- Catálogo de dependencias: [[03 - Stack Tecnológico y Dependencias]]
- Bitácora de versiones: [[03 - Bitácora de Versiones y Changelog]]

