# Seguimiento de Acuerdos - Minuta de Trabajo 03 (SIPAC)
**Fecha de reunión original:** 16 de octubre de 2026  
**Fecha límite institucional:** 26 de noviembre de 2026  
**Estatus general:** **COMPLETADO AL 100%**  
**Documento base de catálogos:** `boveda/ÉPOCA, TÉCNICAS Y MATERIALES.docx`  
**Minuta de acuerdos:** `boveda/Minuta_de_Trabajo_SIPAC_2026.docx`

---

## 📋 Resumen de Cumplimiento de Acuerdos

1. **Dimensiones en Acervo General (Bidimensional vs Tridimensional):**
   - **Completado:** Se habilitaron los campos `cm` (bidimensional) y `medidas` (tridimensional) mediante botones de radio y se implementó la selección automática inteligente al cambiar el campo **Tipo de Obra** (Acuerdo 1).
   - *Tipos bidimensionales:* Pintura, Dibujo, Grabado, Fotografía, Acuarela, Textil.
   - *Tipos tridimensionales:* Escultura, Mobiliario, Cerámica, Objeto, Otro.

2. **Campo Materia vs Material:**
   - **Completado:** Se eliminó el campo redundante `materia` en Acervo General, conservando exclusivamente `material` vinculado a los catálogos oficiales normalizados (Acuerdo 5).

3. **Reubicación del Campo Colección:**
   - **Completado:** Se retiró el campo `coleccion` de los formularios de captura y edición de Acervo General y Numismático, reubicándolo e integrándolo plenamente en el módulo de [[Préstamos y Transferencias]] (tabla `prestamos_transferencias`, formulario de nuevo movimiento y ficha de detalle/expediente) (Acuerdo 7).

4. **Ubicación Física vs Ubicación Topográfica:**
   - **Completado:** Se convirtió Ubicación Topográfica en campo de texto libre y Ubicación Física en selector desplegable poblado con la red de 31 museos oficiales de la Secretaría, integrando la opción *"Otro"* con campo dinámico desplegable para sedes o dependencias externas (Acuerdo 8).

5. **Catálogo Oficial de Museos:**
   - **Completado:** Se actualizaron los 31 recintos y museos oficiales en la base de datos y se integraron en todos los formularios y selects de origen/destino (Acuerdos 4 y 9).

6. **Módulo de Préstamos y Transferencias:**
   - **Completado:** Módulo completamente funcional con folios institucionales, semáforos de vigencia, cambio dinámico de ubicación física, generación de actas/expedientes imprimibles y registro de devoluciones (Acuerdo 10).

7. **Estados de Conservación:**
   - **Completado:** Se normalizó a los 3 estados oficiales validados por Acervo: `Bueno`, `Regular` y `Malo` (Acuerdo 11).

8. **Catálogos Arqueológicos Especializados:**
   - **Completado:** Se integraron con soporte de `<optgroup>` los catálogos de **Formas** (7 grupos / 180 formas), **Técnicas de Manufactura** (4 grupos / 28 técnicas), **Técnicas Decorativas** (4 grupos / 70 técnicas) y **Materiales Arqueológicos** (6 grupos / 30 materiales) (Acuerdos 12 y 13).

9. **Catálogo de Tipos Numismáticos y Dimensiones:**
   - **Completado:** Se integraron los 10 tipos oficiales de numismática (Monedas, Billetes, Medallística, etc.) y se habilitó la alternancia de dimensiones bidimensionales (`cm`) y tridimensionales (`medidas`) con guardado persistente en base de datos (Acuerdos 14 y 15).
   - **Mejora adicional:** Se resolvió la visualización correcta de dimensiones en el modal "Ver Pieza" para numismática y se evitó el desbordamiento visual de texto con auto-expansión vertical.

10. **Módulo de Importación Masiva de Acervo (Excel / CSV):**
    - **Completado:** Implementación del módulo de carga por lotes con plantillas dinámicas `.xlsx` y `.csv` para Acervo General, Acervo Arqueológico y Acervo Numismático, detección y resolución de duplicados, transacciones atómicas seguras y registro de auditoría (`IMPORT_BATCH`).

11. **Listas Desplegables en Celdas y Catálogos Automatizados:**
    - **Completado:** Integración en las plantillas Excel (`.xlsx`) de menús desplegables nativos (`DataValidation`) en filas 4 a 500 y hoja complementaria `Catalogos` con los museos, épocas, tipos, técnicas y materiales oficiales.

12. **Blindaje de Esquema y Detección de Discrepancia:**
    - **Completado:** Validación preventiva que alerta si se sube una plantilla perteneciente a otro acervo, y blindaje de esquema físico (`DESCRIBE {tabla}`) que previene cualquier error de SQL por columnas inexistentes.

---

## 🔗 Enlaces a Registros de Trabajo
* [[pendientes los puntos de DTI]] - Todos los puntos completados (18/18).
* [[pendientes los puntos de Dirección de Patrimonio y Subdirección de Acervo Cultural]] - Información entregada e integrada.
* [[08 - Importador Masivo de Acervo por Excel y CSV]] - Manual operativo del módulo de importación masiva.
* [[Préstamos y Transferencias]] - Módulo documentado.
