---
title: Visión General y Objetivos del Proyecto
type: overview
project: Sistema de Gestión de Acervo Cultural
date: 2026-11-05
tags:
  - introduccion
  - objetivos
  - marco-institucional
---

# 🏛️ Visión General y Objetivos del Proyecto

## 1. Contexto Institucional
El **Sistema de Gestión de Acervo Cultural** es una plataforma tecnológica institucional desarrollada para la **Secretaría de Cultura y Turismo**. Su propósito primordial es salvaguardar, registrar, clasificar, auditar y controlar el patrimonio material custodiado en los diversos museos, galerías, casas de cultura y recintos históricos bajo su jurisdicción.

El acervo patrimonial del estado abarca miles de bienes clasificados en tres grandes categorías especializadas:
1. **Acervo General**: Obras de arte plástico y visual (pintura, escultura, grabado, textiles, mobiliario histórico, piezas mixtas).
2. **Acervo Arqueológico**: Vestigios prehispánicos, piezas cerámicas, líticas y entierros con registro y cotejo de número oficial INAH (Instituto Nacional de Antropología e Historia).
3. **Acervo Numismático**: Monedas, medallas conmemorativas, fichas de hacienda y papel moneda de trascendencia histórica, catalogadas con precisión por ensayador, ceca, anverso y reverso.

---

## 2. Objetivos Principales

### 2.1. Custodia e Inventario Fidedigno
- Centralizar en una única base de datos estructurada el expediente técnico, fotográfico y legal de cada bien cultural.
- Eliminar la dispersión de información en hojas de cálculo no estandarizadas o registros físicos vulnerables a pérdida.

### 2.2. Flujo Curatorial y Control de Calidad
- Implementar un flujo de revisión por **Semáforo** (Aprobado, Cambios Solicitados, Rechazado, Pendiente) que garantice que ninguna pieza quede registrada como parte del inventario definitivo sin la previa validación de curadores y administradores autorizados.
- Permitir la retroalimentación continua entre el personal capturista y los revisores institucionales mediante observaciones específicas.

### 2.3. Control de Movilidad y Trazabilidad (Préstamos y Traslados)
- Registrar y fiscalizar cualquier salida de obra de su recinto habitual, ya sea para exposiciones temporales, restauración especializada o traslado definitivo entre recintos.
- Generar folios únicos de movimiento con registro de responsables de entrega, recepción, fechas límite y estado de conservación al momento del retorno.

### 2.4. Seguridad, Integridad y Auditoría Forense
- Registrar de manera inmutable cada inserción, modificación o eliminación de datos, capturando el estado previo (`datos_antes`), el estado resultante (`datos_despues`), usuario responsable, marca de tiempo, dirección IP y dirección MAC del equipo emisor.
- Establecer un control de acceso basado en roles (RBAC) para segregar funciones críticas.

---

## 3. Alcance de los Módulos Funcionales

```mermaid
mindmap
  root((Sistema Acervo Cultural))
    Registro y Catalogación
      Acervo General
        Bidimensional (cm)
        Tridimensional (medidas)
        Ubicación física en museos
        Ubicación topográfica
      Acervo Arqueológico
        No. Registro INAH
        Técnicas de manufactura y decoración
      Acervo Numismático
        Ensayador y denominación
        Anverso (Cara A) y Reverso (Cara B)
    Revisión Curatorial
      Semáforo de Validación
      Observaciones de corrección
      Historial de versiones
    Movilidad de Acervo
      Préstamos temporales
      Transferencias definitivas
      Devolución e inspección de estado
    Auditoría Forense
      Snapshots JSON
      Trazabilidad de IPs
    Reportes y Salidas
      Fichas técnicas museográficas
      Exportación a Excel
      Exportación a PDF con logotipos oficiales
```

---

## 4. Notas Relacionadas
- Para consultar la arquitectura técnica: [[02 - Arquitectura Bee Framework MVC]]
- Para explorar el modelo de base de datos: [[01 - Diagrama Entidad-Relación (ERD)]]
- Para la guía del usuario final: [[01 - Inicio de Sesión y Perfiles de Usuario]]

