<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="col-12 d-flex flex-direction-column flex-wrap">
  <!-- Formulario para agregar usuario -->
  <div class="container-fluid mb-4">
    <div class="row g-2 align-items-end flex-wrap">
      <!-- Filtro: Ubicación -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-map'></i> Ubicación
            </h6>
          </div>
          <div class="card-body">
            <select name="ubicacion" id="ubicacion" class="form-select">
              <option value="" hidden>Seleccione...</option>
              <option value="1">Acervo Acambay</option>
              <option value="2">Acervo Tenancingo</option>
              <option value="3">Acervo Toluca</option>
              <option value="4">Acervo Zinacantepec</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Filtro: Tipo de registro -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-arch'></i> Tipo de acervo
            </h6>
          </div>
          <div class="card-body">
            <select name="tipo_registro" id="tipo_registro" class="form-select">
              <option value="" hidden>Seleccione...</option>
              <option value="general">Acervo General</option>
              <option value="arqueologico">Acervo Arqueológico</option>
              <option value="numismatica">Acervo Numismático</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Filtro: Año -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-calendar'></i> Año
            </h6>
          </div>
          <div class="card-body">
            <select name="anio" id="anio" class="form-select">
              <option value="" hidden>Seleccione...</option>
             
            </select>
          </div>
        </div>
      </div>

      <!-- Filtro: Cultura -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-globe'></i> Cultura
            </h6>
          </div>
          <div class="card-body">
            <select name="cultura" id="cultura" class="form-select">
              <option value="" hidden>Seleccione...</option>
              <option value="2">Mexicas</option>
              <option value="3">Teotihuacanos</option>
              <option value="4">Toltecas</option>
              <option value="5">Chichimecas</option>
              <option value="6">Otomíes</option>
              <option value="7">Matlatzincas</option>
              <option value="8">Mazahuas</option>
              <option value="9">Purhépechas</option>
              <option value="10">Tlaxcaltecas</option>
              <option value="11">Náhuatl</option>
              <option value="12">Mazatecos</option>
              <option value="13">Mixtecos</option>
              <option value="14">Zapotecos</option>
              <option value="15">Totonacas</option>
              <option value="16">Huastecos</option>
              <option value="17">Mayas</option>
              <option value="18">Otros</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de resultados -->
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary title__card col-6 p-0">
          <i class='bx  bx-database-alt'></i> Registros
        </h6>
      </div>
      <div class="card-body p-0">
        <div class="d-flex flex-wrap p-2">
          <div class="col-6 p-1 d-flex align-items-center">
            <select class="form-select col-2" style="min-width: 3rem;" name="numero" id="numeroRegistros">
              <option value="5">5</option>
              <option value="10" selected>10</option>
              <option value="25">25</option>
            </select>
            <label class="col-10" for="numeroRegistros">Registros por página</label>
          </div>

          <div class="col-6 p-1">
            <input type="text" id="buscar-registro" class="form-control" placeholder="Buscar registro...">
          </div>
        </div>

        <div class="table-responsive">
          <!-- Loader para la carga de datos -->
          <div id="loader-tabla" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
              <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando registros...</p>
          </div>

          <table class="table table-hover" id="tabla-acervo">
            <thead class="thead-light">
              <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Código Interno</th>
                <th>Autor</th>
                <th>Descripción</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody id="tabla-piezas">
              <!-- Filas dinámicas aquí -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-body" id="paginacion-container">

      </div>
    </div>
  </div>
</div>

<!-- Modal para editar pieza -->
<div class="modal fade" id="modalEditarPieza" tabindex="-1" aria-labelledby="modalEditarPiezaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalEditarPiezaLabel">
          <i class="bx bx-edit me-2"></i>Editar Pieza de Acervo General
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formEditarPieza">
        <div class="modal-body p-4">
          <input type="hidden" id="editar-id" name="id">
          <input type="hidden" id="editar-fotografia_actual" name="fotografia_actual">
          <input type="hidden" id="editar-cm" name="cm">

          <!-- Fotografía a todo lo ancho arriba -->
          <div class="row mb-4">
            <div class="col-12 container-preview-image">
              <label for="editar-fotografia" class="form-label fw-semibold">Fotografía de la Pieza</label>
              <input type="file" id="editar-fotografia" accept="image/*" class="input-file form-control mb-2" name="fotografia">
              <div class="card shadow-sm text-center p-3 border rounded preview-clickable preview-container-edit d-flex flex-column justify-content-center align-items-center bg-light" id="editar-previewContainer" style="min-height: 220px; border-style: dashed !important;">
                <i class="bx bx-image fs-1 text-muted" id="editar-previewIcon"></i>
                <p id="editar-previewText" class="mt-2 text-muted mb-0">No hay imagen seleccionada</p>
              </div>
            </div>
          </div>

          <!-- Información General e Identificación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-info-circle me-1"></i>Información General e Identificación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar-codigo_interno" class="form-label fw-semibold">Código Interno</label>
              <input type="text" class="form-control" id="editar-codigo_interno" name="codigo_interno">
            </div>
            <div class="col-md-4">
              <label for="editar-no_inventario" class="form-label fw-semibold">No. Inventario</label>
              <input type="text" class="form-control" id="editar-no_inventario" name="no_inventario">
            </div>
            <div class="col-md-4">
              <label for="editar-nombre" class="form-label fw-semibold">Nombre / Título de la Pieza</label>
              <input type="text" class="form-control" id="editar-nombre" name="nombre_titulo_pieza" required>
            </div>
          </div>

          <!-- Autoría y Cronología -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-user me-1"></i>Autoría y Cronología
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar-autor" class="form-label fw-semibold">Autor</label>
              <input type="text" class="form-control" id="editar-autor" name="autor">
            </div>
            <div class="col-md-4">
              <label for="editar-fecha" class="form-label fw-semibold">Año</label>
              <input type="text" class="form-control" id="editar-fecha" name="anio">
            </div>
            <div class="col-md-4">
              <label for="editar-epoca" class="form-label fw-semibold">Época</label>
              <input type="text" class="form-control" id="editar-epoca" name="epoca">
            </div>
          </div>

          <!-- Características Físicas y Clasificación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-cube me-1"></i>Características Físicas y Clasificación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar-tecnica" class="form-label fw-semibold">Técnica</label>
              <input type="text" class="form-control" id="editar-tecnica" name="tecnica">
            </div>
            <div class="col-md-4">
              <label for="editar-material" class="form-label fw-semibold">Material</label>
              <input type="text" class="form-control" id="editar-material" name="material">
            </div>
            <div class="col-md-4">
              <label for="editar-medidas" class="form-label fw-semibold">Medidas</label>
              <input type="text" class="form-control" id="editar-medidas" name="medidas">
            </div>
            <div class="col-md-3">
              <label for="editar-lote" class="form-label fw-semibold">Lote</label>
              <input type="text" class="form-control" id="editar-lote" name="lote">
            </div>
            <div class="col-md-3">
              <label for="editar-peso" class="form-label fw-semibold">Peso</label>
              <input type="text" class="form-control" id="editar-peso" name="peso">
            </div>
            <div class="col-md-3">
              <label for="editar-coleccion" class="form-label fw-semibold">Colección</label>
              <input type="text" class="form-control" id="editar-coleccion" name="coleccion">
            </div>
            <div class="col-md-3">
              <label for="editar-tipo" class="form-label fw-semibold">Tipo</label>
              <input type="text" class="form-control" id="editar-tipo" name="tipo">
            </div>
          </div>

          <!-- Ubicación y Estado de Conservación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-map-pin me-1"></i>Ubicación y Estado de Conservación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="editar-ubicacion" class="form-label fw-semibold">Ubicación Física</label>
              <input type="text" class="form-control" id="editar-ubicacion" name="ubicacion_fisica">
            </div>
            <div class="col-md-6">
              <label for="editar-estado_conservacion" class="form-label fw-semibold">Estado de Conservación</label>
              <input type="text" class="form-control" id="editar-estado_conservacion" name="estado_conservacion">
            </div>
          </div>

          <!-- Detalle y Observaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-detail me-1"></i>Detalles y Observaciones
          </h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="editar-descripcion" class="form-label fw-semibold">Descripción</label>
              <textarea class="form-control" id="editar-descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="col-md-6">
              <label for="editar-observaciones" class="form-label fw-semibold">Observaciones</label>
              <textarea class="form-control" id="editar-observaciones" name="observaciones" rows="3"></textarea>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para editar pieza de acervo arqueológico -->
<div class="modal fade" id="modalEditarPiezaArq" tabindex="-1" aria-labelledby="modalEditarPiezaArqLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalEditarPiezaArqLabel">
          <i class="bx bx-edit me-2"></i>Editar Pieza Arqueológica
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formEditarPiezaArq">
        <div class="modal-body p-4">
          <input type="hidden" id="editar-arq-id" name="id">
          <input type="hidden" id="editar-arq-fotografia_actual" name="fotografia_actual">

          <!-- Fotografía a todo lo ancho arriba -->
          <div class="row mb-4">
            <div class="col-12 container-preview-image">
              <label for="editar-arq-fotografia" class="form-label fw-semibold">Fotografía de la Pieza Arqueológica</label>
              <input type="file" id="editar-arq-fotografia" accept="image/*" class="input-file form-control mb-2" name="fotografia">
              <div class="card shadow-sm text-center p-3 border rounded preview-clickable preview-container-edit d-flex flex-column justify-content-center align-items-center bg-light" id="editar-arq-previewContainer" style="min-height: 220px; border-style: dashed !important;">
                <i class="bx bx-image fs-1 text-muted" id="editar-arq-previewIcon"></i>
                <p id="editar-arq-previewText" class="mt-2 text-muted mb-0">No hay imagen seleccionada</p>
              </div>
            </div>
          </div>

          <!-- Información General y Registros -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-info-circle me-1"></i>Información General y Registros
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label for="editar-arq-codigo_interno" class="form-label fw-semibold">Código Interno</label>
              <input type="text" class="form-control" id="editar-arq-codigo_interno" name="codigo_interno">
            </div>
            <div class="col-md-3">
              <label for="editar-arq-no_inventario_scyt" class="form-label fw-semibold">No. Inventario SCYT</label>
              <input type="text" class="form-control" id="editar-arq-no_inventario_scyt" name="no_inventario_scyt">
            </div>
            <div class="col-md-3">
              <label for="editar-arq-no_registro_inah" class="form-label fw-semibold">No. Registro INAH</label>
              <input type="text" class="form-control" id="editar-arq-no_registro_inah" name="no_registro_inah">
            </div>
            <div class="col-md-3">
              <label for="editar-arq-otros" class="form-label fw-semibold">Otros Registros</label>
              <input type="text" class="form-control" id="editar-arq-otros" name="otros">
            </div>
            <div class="col-md-8">
              <label for="editar-arq-nombre_titulo_pieza" class="form-label fw-semibold">Nombre / Título de la Pieza</label>
              <input type="text" class="form-control" id="editar-arq-nombre_titulo_pieza" name="nombre_titulo_pieza">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-numero_pieza_por_lote" class="form-label fw-semibold">Pieza por Lote</label>
              <input type="text" class="form-control" id="editar-arq-numero_pieza_por_lote" name="numero_pieza_por_lote">
            </div>
          </div>

          <!-- Origen y Cronología -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-world me-1"></i>Origen y Cronología
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar-arq-epoca" class="form-label fw-semibold">Época</label>
              <input type="text" class="form-control" id="editar-arq-epoca" name="epoca">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-procedencia" class="form-label fw-semibold">Procedencia</label>
              <input type="text" class="form-control" id="editar-arq-procedencia" name="procedencia">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-obtencion" class="form-label fw-semibold">Forma de Obtención</label>
              <input type="text" class="form-control" id="editar-arq-obtencion" name="obtencion">
            </div>
          </div>

          <!-- Características Físicas y Técnicas -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-cube me-1"></i>Características Físicas y Técnicas
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar-arq-material" class="form-label fw-semibold">Material</label>
              <input type="text" class="form-control" id="editar-arq-material" name="material">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-medidas" class="form-label fw-semibold">Medidas</label>
              <input type="text" class="form-control" id="editar-arq-medidas" name="medidas">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-forma" class="form-label fw-semibold">Forma</label>
              <input type="text" class="form-control" id="editar-arq-forma" name="forma">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-tecnica_manufactura" class="form-label fw-semibold">Técnica de Manufactura</label>
              <input type="text" class="form-control" id="editar-arq-tecnica_manufactura" name="tecnica_manufactura">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-tecnica_decorativa" class="form-label fw-semibold">Técnica Decorativa</label>
              <input type="text" class="form-control" id="editar-arq-tecnica_decorativa" name="tecnica_decorativa">
            </div>
            <div class="col-md-4">
              <label for="editar-arq-coleccion" class="form-label fw-semibold">Colección</label>
              <input type="text" class="form-control" id="editar-arq-coleccion" name="coleccion">
            </div>
          </div>

          <!-- Ubicación y Estado de Conservación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-map-pin me-1"></i>Ubicación y Estado de Conservación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="editar-arq-ubicacion_fisica" class="form-label fw-semibold">Ubicación Física</label>
              <input type="text" class="form-control" id="editar-arq-ubicacion_fisica" name="ubicacion_fisica">
            </div>
            <div class="col-md-6">
              <label for="editar-arq-estado_conservacion" class="form-label fw-semibold">Estado de Conservación</label>
              <input type="text" class="form-control" id="editar-arq-estado_conservacion" name="estado_conservacion">
            </div>
          </div>

          <!-- Detalles, Representación y Observaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-detail me-1"></i>Descripción y Observaciones
          </h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="editar-arq-descripcion" class="form-label fw-semibold">Descripción</label>
              <textarea class="form-control" id="editar-arq-descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="col-md-4">
              <label for="editar-arq-representacion" class="form-label fw-semibold">Representación</label>
              <textarea class="form-control" id="editar-arq-representacion" name="representacion" rows="3"></textarea>
            </div>
            <div class="col-md-4">
              <label for="editar-arq-observaciones" class="form-label fw-semibold">Observaciones</label>
              <textarea class="form-control" id="editar-arq-observaciones" name="observaciones" rows="3"></textarea>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para editar pieza de acervo numismático -->
<div class="modal fade" id="modalEditarPiezaNum" tabindex="-1" aria-labelledby="modalEditarPiezaNumLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalEditarPiezaNumLabel">
          <i class="bx bx-edit me-2"></i>Editar Pieza Numismática
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formEditarPiezaNum">
        <div class="modal-body p-4">
          <input type="hidden" id="editar-num-id" name="id">
          <input type="hidden" id="editar-num-fotografia_actual" name="fotografia_actual">

          <!-- Fotografía a todo lo ancho arriba -->
          <div class="row mb-4">
            <div class="col-12 container-preview-image">
              <label for="editar-num-fotografia" class="form-label fw-semibold">Fotografía de la Pieza Numismática</label>
              <input type="file" id="editar-num-fotografia" accept="image/*" class="input-file form-control mb-2" name="fotografia">
              <div class="card shadow-sm text-center p-3 border rounded preview-clickable preview-container-edit d-flex flex-column justify-content-center align-items-center bg-light" id="editar-num-previewContainer" style="min-height: 220px; border-style: dashed !important;">
                <i class="bx bx-image fs-1 text-muted" id="editar-num-previewIcon"></i>
                <p id="editar-num-previewText" class="mt-2 text-muted mb-0">No hay imagen seleccionada</p>
              </div>
            </div>
          </div>

          <!-- Información General y Clasificación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-info-circle me-1"></i>Información General y Clasificación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar-num-codigo_interno" class="form-label fw-semibold">Código Interno</label>
              <input type="text" class="form-control" id="editar-num-codigo_interno" name="codigo_interno">
            </div>
            <div class="col-md-4">
              <label for="editar-num-no_inventario" class="form-label fw-semibold">Número de Inventario</label>
              <input type="text" class="form-control" id="editar-num-no_inventario" name="no_inventario">
            </div>
            <div class="col-md-4">
              <label for="editar-num-tipo_obra" class="form-label fw-semibold">Tipo de Obra</label>
              <select class="form-select" id="editar-num-tipo_obra" name="tipo_obra">
                <option value="Pintura">Pintura</option>
                <option value="Escultura">Escultura</option>
                <option value="Fotografía">Fotografía</option>
                <option value="Objeto">Objeto</option>
              </select>
            </div>
          </div>

          <!-- Origen y Especificaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-coin me-1"></i>Origen y Especificaciones
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label for="editar-num-ensayador" class="form-label fw-semibold">Ensayador</label>
              <input type="text" class="form-control" id="editar-num-ensayador" name="ensayador">
            </div>
            <div class="col-md-3">
              <label for="editar-num-denominacion" class="form-label fw-semibold">Denominación</label>
              <input type="text" class="form-control" id="editar-num-denominacion" name="denominacion">
            </div>
            <div class="col-md-3">
              <label for="editar-num-material" class="form-label fw-semibold">Material</label>
              <select class="form-select" id="editar-num-material" name="material">
                <option value="Madera">Madera</option>
                <option value="Metal">Metal</option>
                <option value="Cerámica">Cerámica</option>
                <option value="Textil">Textil</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="editar-num-fecha_epoca" class="form-label fw-semibold">Época</label>
              <select class="form-select" id="editar-num-fecha_epoca" name="fecha_epoca">
                <option value="Prehispánica">Prehispánica</option>
                <option value="Colonial">Colonial</option>
                <option value="Moderna">Moderna</option>
                <option value="Contemporánea">Contemporánea</option>
              </select>
            </div>
          </div>

          <!-- Dimensiones, Ubicación y Estado -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-map-pin me-1"></i>Dimensiones, Ubicación y Conservación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="editar-num-dimensiones" class="form-label fw-semibold">Dimensiones (cm)</label>
              <input type="text" class="form-control" id="editar-num-dimensiones" name="dimensiones">
            </div>
            <div class="col-md-4">
              <label for="editar-num-ubicacion_fisica" class="form-label fw-semibold">Ubicación Física</label>
              <input type="text" class="form-control" id="editar-num-ubicacion_fisica" name="ubicacion_fisica">
            </div>
            <div class="col-md-4">
              <label for="editar-num-estado_conservacion" class="form-label fw-semibold">Estado de Conservación</label>
              <select class="form-select" id="editar-num-estado_conservacion" name="estado_conservacion">
                <option value="Excelente">Excelente</option>
                <option value="Bueno">Bueno</option>
                <option value="Regular">Regular</option>
                <option value="Dañado">Dañado</option>
              </select>
            </div>
          </div>

          <!-- Descripciones y Observaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-detail me-1"></i>Descripciones y Observaciones
          </h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="editar-num-descripcion_cara_a" class="form-label fw-semibold">Descripción Cara A</label>
              <textarea class="form-control" id="editar-num-descripcion_cara_a" name="descripcion_cara_a" rows="3"></textarea>
            </div>
            <div class="col-md-4">
              <label for="editar-num-descripcion_cara_b" class="form-label fw-semibold">Descripción Cara B</label>
              <textarea class="form-control" id="editar-num-descripcion_cara_b" name="descripcion_cara_b" rows="3"></textarea>
            </div>
            <div class="col-md-4">
              <label for="editar-num-observaciones" class="form-label fw-semibold">Observaciones</label>
              <textarea class="form-control" id="editar-num-observaciones" name="observaciones" rows="3"></textarea>
            </div>
          </div>

        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para ver detalles de la pieza -->
<div class="modal fade" id="modalVerPieza" tabindex="-1" aria-labelledby="modalVerPiezaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold text-primary" id="modalVerPiezaLabel">
          <i class="bx bx-show me-2"></i>Ver Detalles de la Pieza
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4">

        <!-- VISTA: ACERVO GENERAL -->
        <div id="ver-seccion-general" style="display: none;">
          <!-- Fotografía a todo lo ancho arriba -->
          <div class="row mb-4">
            <div class="col-12">
              <label class="form-label fw-semibold">Fotografía de la Pieza</label>
              <div class="card shadow-sm text-center p-3 border rounded d-flex flex-column justify-content-center align-items-center bg-light" style="min-height: 220px;">
                <img id="ver-gen-imagen" src="" alt="Fotografía" class="img-fluid rounded" style="max-height: 260px; object-fit: contain; display: none;">
                <div id="ver-gen-no-imagen" class="text-muted p-4">
                  <i class="bx bx-image fs-1 d-block mb-1"></i>
                  <span>No hay imagen seleccionada</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Información General e Identificación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-info-circle me-1"></i>Información General e Identificación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Código Interno</label>
              <div class="form-control bg-light" id="ver-gen-codigo_interno">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">No. Inventario</label>
              <div class="form-control bg-light" id="ver-gen-no_inventario">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Nombre / Título de la Pieza</label>
              <div class="form-control bg-light" id="ver-gen-nombre">-</div>
            </div>
          </div>

          <!-- Autoría y Cronología -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-user me-1"></i>Autoría y Cronología
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Autor</label>
              <div class="form-control bg-light" id="ver-gen-autor">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Año</label>
              <div class="form-control bg-light" id="ver-gen-anio">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Época</label>
              <div class="form-control bg-light" id="ver-gen-epoca">-</div>
            </div>
          </div>

          <!-- Características Físicas y Clasificación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-cube me-1"></i>Características Físicas y Clasificación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Técnica</label>
              <div class="form-control bg-light" id="ver-gen-tecnica">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Material</label>
              <div class="form-control bg-light" id="ver-gen-material">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Medidas</label>
              <div class="form-control bg-light" id="ver-gen-medidas">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Lote</label>
              <div class="form-control bg-light" id="ver-gen-lote">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Peso</label>
              <div class="form-control bg-light" id="ver-gen-peso">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Colección</label>
              <div class="form-control bg-light" id="ver-gen-coleccion">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tipo</label>
              <div class="form-control bg-light" id="ver-gen-tipo">-</div>
            </div>
          </div>

          <!-- Ubicación y Estado de Conservación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-map-pin me-1"></i>Ubicación y Estado de Conservación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Ubicación Física</label>
              <div class="form-control bg-light" id="ver-gen-ubicacion_fisica">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Estado de Conservación</label>
              <div class="form-control bg-light" id="ver-gen-estado_conservacion">-</div>
            </div>
          </div>

          <!-- Detalles y Observaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-detail me-1"></i>Detalles y Observaciones
          </h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Descripción</label>
              <div class="form-control bg-light" id="ver-gen-descripcion" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Observaciones</label>
              <div class="form-control bg-light" id="ver-gen-observaciones" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
          </div>
        </div>


        <!-- VISTA: ACERVO ARQUEOLÓGICO -->
        <div id="ver-seccion-arq" style="display: none;">
          <!-- Fotografía a todo lo ancho arriba -->
          <div class="row mb-4">
            <div class="col-12">
              <label class="form-label fw-semibold">Fotografía de la Pieza Arqueológica</label>
              <div class="card shadow-sm text-center p-3 border rounded d-flex flex-column justify-content-center align-items-center bg-light" style="min-height: 220px;">
                <img id="ver-arq-imagen" src="" alt="Fotografía Arqueológica" class="img-fluid rounded" style="max-height: 260px; object-fit: contain; display: none;">
                <div id="ver-arq-no-imagen" class="text-muted p-4">
                  <i class="bx bx-image fs-1 d-block mb-1"></i>
                  <span>No hay imagen seleccionada</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Información General y Registros -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-info-circle me-1"></i>Información General y Registros
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Código Interno</label>
              <div class="form-control bg-light" id="ver-arq-codigo_interno">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">No. Inventario SCYT</label>
              <div class="form-control bg-light" id="ver-arq-no_inventario_scyt">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">No. Registro INAH</label>
              <div class="form-control bg-light" id="ver-arq-no_registro_inah">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Otros Registros</label>
              <div class="form-control bg-light" id="ver-arq-otros">-</div>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nombre / Título de la Pieza</label>
              <div class="form-control bg-light" id="ver-arq-nombre_titulo_pieza">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Pieza por Lote</label>
              <div class="form-control bg-light" id="ver-arq-numero_pieza_por_lote">-</div>
            </div>
          </div>

          <!-- Origen y Cronología -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-world me-1"></i>Origen y Cronología
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Época</label>
              <div class="form-control bg-light" id="ver-arq-epoca">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Procedencia</label>
              <div class="form-control bg-light" id="ver-arq-procedencia">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Forma de Obtención</label>
              <div class="form-control bg-light" id="ver-arq-obtencion">-</div>
            </div>
          </div>

          <!-- Características Físicas y Técnicas -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-cube me-1"></i>Características Físicas y Técnicas
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Material</label>
              <div class="form-control bg-light" id="ver-arq-material">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Medidas</label>
              <div class="form-control bg-light" id="ver-arq-medidas">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Forma</label>
              <div class="form-control bg-light" id="ver-arq-forma">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Técnica de Manufactura</label>
              <div class="form-control bg-light" id="ver-arq-tecnica_manufactura">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Técnica Decorativa</label>
              <div class="form-control bg-light" id="ver-arq-tecnica_decorativa">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Colección</label>
              <div class="form-control bg-light" id="ver-arq-coleccion">-</div>
            </div>
          </div>

          <!-- Ubicación y Estado de Conservación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-map-pin me-1"></i>Ubicación y Estado de Conservación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Ubicación Física</label>
              <div class="form-control bg-light" id="ver-arq-ubicacion_fisica">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Estado de Conservación</label>
              <div class="form-control bg-light" id="ver-arq-estado_conservacion">-</div>
            </div>
          </div>

          <!-- Detalles, Representación y Observaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-detail me-1"></i>Descripción y Observaciones
          </h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Descripción</label>
              <div class="form-control bg-light" id="ver-arq-descripcion" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Representación</label>
              <div class="form-control bg-light" id="ver-arq-representacion" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Observaciones</label>
              <div class="form-control bg-light" id="ver-arq-observaciones" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
          </div>
        </div>


        <!-- VISTA: ACERVO NUMISMÁTICO -->
        <div id="ver-seccion-num" style="display: none;">
          <!-- Fotografía a todo lo ancho arriba -->
          <div class="row mb-4">
            <div class="col-12">
              <label class="form-label fw-semibold">Fotografía de la Pieza Numismática</label>
              <div class="card shadow-sm text-center p-3 border rounded d-flex flex-column justify-content-center align-items-center bg-light" style="min-height: 220px;">
                <img id="ver-num-imagen" src="" alt="Fotografía Numismática" class="img-fluid rounded" style="max-height: 260px; object-fit: contain; display: none;">
                <div id="ver-num-no-imagen" class="text-muted p-4">
                  <i class="bx bx-image fs-1 d-block mb-1"></i>
                  <span>No hay imagen seleccionada</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Información General y Clasificación -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-info-circle me-1"></i>Información General y Clasificación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Código Interno</label>
              <div class="form-control bg-light" id="ver-num-codigo_interno">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Número de Inventario</label>
              <div class="form-control bg-light" id="ver-num-no_inventario">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Tipo de Obra</label>
              <div class="form-control bg-light" id="ver-num-tipo_obra">-</div>
            </div>
          </div>

          <!-- Origen y Especificaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-coin me-1"></i>Origen y Especificaciones
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Ensayador</label>
              <div class="form-control bg-light" id="ver-num-ensayador">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Denominación</label>
              <div class="form-control bg-light" id="ver-num-denominacion">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Material</label>
              <div class="form-control bg-light" id="ver-num-material">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Época</label>
              <div class="form-control bg-light" id="ver-num-fecha_epoca">-</div>
            </div>
          </div>

          <!-- Dimensiones, Ubicación y Estado -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-map-pin me-1"></i>Dimensiones, Ubicación y Conservación
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Dimensiones (cm)</label>
              <div class="form-control bg-light" id="ver-num-dimensiones">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Ubicación Física</label>
              <div class="form-control bg-light" id="ver-num-ubicacion_fisica">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Estado de Conservación</label>
              <div class="form-control bg-light" id="ver-num-estado_conservacion">-</div>
            </div>
          </div>

          <!-- Descripciones y Observaciones -->
          <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
            <i class="bx bx-detail me-1"></i>Descripciones y Observaciones
          </h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Descripción Cara A</label>
              <div class="form-control bg-light" id="ver-num-descripcion_cara_a" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Descripción Cara B</label>
              <div class="form-control bg-light" id="ver-num-descripcion_cara_b" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Observaciones</label>
              <div class="form-control bg-light" id="ver-num-observaciones" style="min-height: 80px; white-space: pre-wrap;">-</div>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>