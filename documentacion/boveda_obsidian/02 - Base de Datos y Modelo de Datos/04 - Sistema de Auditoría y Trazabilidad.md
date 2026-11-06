---
title: Sistema de Auditoría y Trazabilidad
type: database
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - auditoria
  - seguridad
  - trazabilidad
  - forense
---

# 🕵️ Sistema de Auditoría y Trazabilidad

El sistema incorpora un motor de auditoría transaccional para cumplir con los estándares de control interno gubernamental y transparencia patrimonial. Cada mutación de datos genera una entrada inmutable en la bitácora central.

---

## 1. Esquema de la Tabla `auditoria`

```sql
CREATE TABLE `auditoria` (
  `id_auditoria` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `id_modulo` int DEFAULT NULL,
  `nombre_tabla` varchar(100) NOT NULL,
  `id_pieza` int DEFAULT NULL,
  `tipo_accion` varchar(50) NOT NULL,
  `fecha_accion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text,
  `datos_antes` longtext,
  `datos_despues` longtext,
  `ip_address` varchar(50) DEFAULT NULL,
  `mac_address` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_tabla_pieza` (`nombre_tabla`, `id_pieza`),
  KEY `idx_fecha` (`fecha_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 2. Tipología de Acciones Auditadas

| Tipo de Acción (`tipo_accion`) | Módulo Desencadenante | Datos Almacenados |
| :--- | :--- | :--- |
| `REGISTRO_PIEZA` | Catálogo / Registro | `datos_despues` (Objeto JSON completo de la nueva pieza). |
| `EDICION_PIEZA` | Catálogo / Edición | `datos_antes` vs `datos_despues` (Comparativa diferencial de campos). |
| `ELIMINACION_PIEZA` | Catálogo / Eliminar | `datos_antes` (Snapshot completo antes de la baja lógica). |
| `REVISION_APROBADA` | Revisión Curatorial | Dictamen de autorización y usuario revisor. |
| `SOLICITUD_CAMBIOS` | Revisión Curatorial | Observaciones del revisor y respaldo en `datos_anteriores_edicion`. |
| `REVISION_RECHAZADA`| Revisión Curatorial | Causa del rechazo definitivo de la pieza. |
| `MOVIMIENTO_REGISTRO`| Préstamos / Traslados | Folio generado, fechas pactadas y recintos involucrados. |
| `MOVIMIENTO_DEVOLUCION`| Préstamos | Condiciones físicas registradas al retorno de la obra. |
| `MOVIMIENTO_CANCELACION`| Préstamos | Justificación de la anulación del traslado. |
| `CAMBIO_ROL_USUARIO`| Administración | Alteración de privilegios de acceso a personal. |

---

## 3. Estructura de los Snapshots JSON

Los campos `datos_antes` y `datos_despues` almacenan el estado serializado del registro en formato JSON estandarizado:

```json
{
  "id_acervo_general": 104,
  "codigo_interno": "SCYT-P-2024-089",
  "nombre_titulo_pieza": "Paisaje de los Volcanes al Atardecer",
  "cm": "120 x 85 cm",
  "medidas": null,
  "autor": "José María Velasco (Atribuido)",
  "anio": "1892",
  "material": "Óleo sobre tela",
  "ubicacion_fisica": "Museo de Bellas Artes de Toluca",
  "ubicacion_topografica": "Bóveda Principal, Rack A-12",
  "estado_conservacion": "Bueno",
  "estatus_revision": "aprobado"
}
```

> [!TIP]
> **Valor Pericial del Snapshot**:
> En caso de litigio, extravío o alteración maliciosa de información patrimonial, el perito informático puede reconstruir exactamente qué usuario, desde qué IP y en qué segundo modificó una ficha técnica, así como los valores originales antes de la edición.

---

## 4. Captura de Dirección IP y Red

El controlador extrae la dirección de conexión del cliente inspeccionando las cabeceras HTTP de red en el siguiente orden de precedencia:
1. `HTTP_CLIENT_IP`
2. `HTTP_X_FORWARDED_FOR` (para servidores tras balanceador o proxy reverso)
3. `REMOTE_ADDR` (dirección IP de conexión directa del socket TCP)

---

## 5. Referencias Cruzadas
- Vista de auditoría en manual de usuario: [[06 - Fichas Técnicas, Exportaciones y Auditoría#3. Consulta del Historial de Auditoría]]
- Métodos del controlador: [[01 - Controladores y Flujo de Peticiones]]
- Modelo relacional general: [[01 - Diagrama Entidad-Relación (ERD)]]

