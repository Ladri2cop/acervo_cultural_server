<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!-- Contenedor Principal de Revisión y Semáforo -->
<div class="container-fluid px-3 py-2">

  <!-- Encabezado de Sección -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
        <i class='bx bx-check-shield text-primary'></i> Revisión y Autorización de Acervo
      </h1>
      <p class="text-muted small mb-0">
        Supervisión de registros capturados. Evalúe y dictamine mediante el semáforo institucional antes de su publicación en el catálogo oficial.
      </p>
    </div>
    <div class="mt-2 mt-sm-0">
      <button type="button" id="btn-refresh-revision" class="btn btn-outline-primary btn-sm shadow-sm">
        <i class='bx bx-refresh'></i> Actualizar Lista
      </button>
      <a href="admin/acervo" class="btn btn-primary btn-sm shadow-sm ms-2">
        <i class='bx bx-library'></i> Ir al Catálogo Oficial
      </a>
    </div>
  </div>

  <!-- Tarjetas KPI del Semáforo -->
  <div class="row g-3 mb-4">
    <!-- KPI 1: Pendientes de Revisión (Azul) -->
    <div class="col-xl-3 col-md-6">
      <div class="card border-left-primary shadow h-100 py-2 kpi-card cursor-pointer" data-semaforo="pendiente" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                Pendientes de Revisión
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800" id="kpi-pendientes">0</div>
              <small class="text-muted">En espera de dictamen</small>
            </div>
            <div class="col-auto">
              <i class='bx bx-time-five fa-2x text-primary' style="font-size: 2.2rem;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KPI 2: Cambios Solicitados (Amarillo) -->
    <div class="col-xl-3 col-md-6">
      <div class="card border-left-warning shadow h-100 py-2 kpi-card cursor-pointer" data-semaforo="cambios_solicitados" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                Cambios Solicitados (Amarillo)
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800" id="kpi-cambios">0</div>
              <small class="text-muted">Con observaciones enviadas</small>
            </div>
            <div class="col-auto">
              <i class='bx bx-edit-alt fa-2x text-warning' style="font-size: 2.2rem;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KPI 3: Rechazados (Rojo) -->
    <div class="col-xl-3 col-md-6">
      <div class="card border-left-danger shadow h-100 py-2 kpi-card cursor-pointer" data-semaforo="rechazado" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                Rechazados (Rojo)
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800" id="kpi-rechazados">0</div>
              <small class="text-muted">No aprobados para catálogo</small>
            </div>
            <div class="col-auto">
              <i class='bx bx-x-circle fa-2x text-danger' style="font-size: 2.2rem;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KPI 4: Aprobados (Verde) -->
    <div class="col-xl-3 col-md-6">
      <div class="card border-left-success shadow h-100 py-2 kpi-card cursor-pointer" data-semaforo="aprobado" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                Aprobados (Verde)
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800" id="kpi-aprobados">0</div>
              <small class="text-muted">Publicados oficialmente</small>
            </div>
            <div class="col-auto">
              <i class='bx bx-check-double fa-2x text-success' style="font-size: 2.2rem;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Barra de Filtros y Búsqueda -->
  <div class="card shadow mb-4">
    <div class="card-body py-3">
      <div class="row g-2 align-items-center">
        <!-- Filtro: Tipo de Acervo -->
        <div class="col-12 col-md-3">
          <label for="filtro_tipo_acervo" class="form-label small text-muted font-weight-bold mb-1">
            <i class='bx bx-collection'></i> Tipo de Acervo
          </label>
          <select id="filtro_tipo_acervo" class="form-select form-select-sm">
            <option value="todos" selected>Todos los acervos</option>
            <option value="general">Acervo General</option>
            <option value="arqueologico">Acervo Arqueológico</option>
            <option value="numismatica">Acervo Numismático</option>
          </select>
        </div>

        <!-- Filtro: Estado del Semáforo -->
        <div class="col-12 col-md-3">
          <label for="filtro_semaforo" class="form-label small text-muted font-weight-bold mb-1">
            <i class='bx bx-traffic-cone'></i> Estado del Semáforo
          </label>
          <select id="filtro_semaforo" class="form-select form-select-sm">
            <option value="todos">Todos los estados</option>
            <option value="pendiente" selected>Pendientes de revisión</option>
            <option value="cambios_solicitados">Amarillo - Cambios Solicitados</option>
            <option value="rechazado">Rojo - Rechazados</option>
            <option value="aprobado">Verde - Aprobados</option>
          </select>
        </div>

        <!-- Filtro: Búsqueda rápida -->
        <div class="col-12 col-md-4">
          <label for="busqueda_revision" class="form-label small text-muted font-weight-bold mb-1">
            <i class='bx bx-search'></i> Buscar registro
          </label>
          <div class="input-group input-group-sm">
            <input type="text" id="busqueda_revision" class="form-control" placeholder="Buscar por código, título, creador u observaciones...">
            <button class="btn btn-outline-secondary" type="button" id="btn-limpiar-busqueda" title="Limpiar búsqueda">
              <i class='bx bx-x'></i>
            </button>
          </div>
        </div>

        <!-- Paginador por página -->
        <div class="col-12 col-md-2">
          <label for="per_page_revision" class="form-label small text-muted font-weight-bold mb-1">
            Mostrar
          </label>
          <select id="per_page_revision" class="form-select form-select-sm">
            <option value="10" selected>10 por página</option>
            <option value="25">25 por página</option>
            <option value="50">50 por página</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de Registros en Revisión -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom">
      <h6 class="m-0 font-weight-bold text-primary">
        <i class='bx bx-list-ul'></i> Piezas para Dictamen y Revisión
        <span class="badge bg-light text-dark ms-2 font-weight-normal" id="badge-total-filas">0 registros</span>
      </h6>
      <div id="indicador-filtro-activo" class="small text-muted"></div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabla-revision">
          <thead class="table-light">
            <tr>
              <th scope="col" style="width: 70px;">Pieza</th>
              <th scope="col" style="width: 140px;">Código</th>
              <th scope="col">Título / Denominación</th>
              <th scope="col" style="width: 130px;">Acervo</th>
              <th scope="col" style="width: 160px;">Capturista / Fecha</th>
              <th scope="col" style="width: 170px;">Semáforo</th>
              <th scope="col" style="width: 210px;">Observaciones</th>
              <th scope="col" class="text-center" style="width: 170px;">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbody-revision">
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                Cargando registros en revisión...
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light flex-wrap">
        <div class="small text-muted" id="info-paginacion">
          Mostrando página 1
        </div>
        <nav aria-label="Navegación de páginas">
          <ul class="pagination pagination-sm mb-0" id="paginacion-revision"></ul>
        </nav>
      </div>
    </div>
  </div>

</div>

<!-- ========================================================== -->
<!-- MODALES DEL SISTEMA DE REVISIÓN Y SEMÁFORO                -->
<!-- ========================================================== -->

<!-- Modal 1: Ver Detalle Completo de la Pieza en Revisión -->
<div class="modal fade" id="modalDetalleRevision" tabindex="-1" aria-labelledby="modalDetalleRevisionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white py-3">
        <h5 class="modal-title font-weight-bold" id="modalDetalleRevisionLabel">
          <i class='bx bx-search-alt-2'></i> Expediente de la Pieza para Revisión
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-4" id="modalDetalleRevisionBody">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-muted">Cargando detalles de la pieza...</p>
        </div>
      </div>
      <div class="modal-footer bg-light d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
            <i class='bx bx-x'></i> Cerrar
          </button>
        </div>
        <div id="modalDetalleRevisionAcciones">
          <!-- Botones inyectados dinámicamente según el estado -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal 2: Solicitar Cambios (Amarillo) -->
<div class="modal fade" id="modalSolicitarCambios" tabindex="-1" aria-labelledby="modalSolicitarCambiosLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark py-3">
        <h5 class="modal-title font-weight-bold" id="modalSolicitarCambiosLabel">
          <i class='bx bx-edit-alt'></i> Solicitar Cambios al Registro (Amarillo)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formSolicitarCambios">
        <div class="modal-body p-4">
          <input type="hidden" id="cambios_pieza_id" name="id" value="">
          <input type="hidden" id="cambios_pieza_tipo" name="tipo" value="">

          <div class="alert alert-warning border-0 small mb-3">
            <i class='bx bx-info-circle me-1'></i>
            <strong>Instrucciones:</strong> Describa de manera clara y precisa los cambios que el capturista debe realizar (por ejemplo: faltas de ortografía, medidas imprecisas, datos faltantes). La pieza cambiará a estatus <strong>Amarillo (Cambios Solicitados)</strong> y el capturista podrá corregirla y volver a enviarla a revisión.
          </div>

          <div class="mb-3">
            <label class="form-label font-weight-bold text-gray-800">Pieza seleccionada:</label>
            <div id="cambios_pieza_info" class="p-2 bg-light rounded text-muted small mb-2 border"></div>
          </div>

          <div class="mb-3">
            <label for="cambios_observacion" class="form-label font-weight-bold text-gray-800">
              Observaciones y correcciones requeridas <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="cambios_observacion" name="observacion" rows="4" placeholder="Especifique con detalle las correcciones ortográficas o de contenido necesarias..." required></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning btn-sm font-weight-bold" id="btn-confirmar-cambios">
            <i class='bx bx-send'></i> Enviar a Corrección (Amarillo)
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 3: Rechazar Pieza (Rojo) -->
<div class="modal fade" id="modalRechazarPieza" tabindex="-1" aria-labelledby="modalRechazarPiezaLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white py-3">
        <h5 class="modal-title font-weight-bold" id="modalRechazarPiezaLabel">
          <i class='bx bx-x-circle'></i> Rechazar Registro de Pieza (Rojo)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formRechazarPieza">
        <div class="modal-body p-4">
          <input type="hidden" id="rechazar_pieza_id" name="id" value="">
          <input type="hidden" id="rechazar_pieza_tipo" name="tipo" value="">

          <div class="alert alert-danger border-0 small mb-3">
            <i class='bx bx-error-circle me-1'></i>
            <strong>Atención:</strong> Indique el motivo oficial del rechazo del registro. La pieza pasará a estatus <strong>Rojo (Rechazado)</strong> y permanecerá inactiva fuera del catálogo oficial.
          </div>

          <div class="mb-3">
            <label class="form-label font-weight-bold text-gray-800">Pieza a rechazar:</label>
            <div id="rechazar_pieza_info" class="p-2 bg-light rounded text-muted small mb-2 border"></div>
          </div>

          <div class="mb-3">
            <label for="rechazar_observacion" class="form-label font-weight-bold text-gray-800">
              Motivo o justificación del rechazo <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="rechazar_observacion" name="observacion" rows="4" placeholder="Indique la causa por la cual se rechaza el registro de esta pieza..." required></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger btn-sm font-weight-bold" id="btn-confirmar-rechazo">
            <i class='bx bx-x-circle'></i> Confirmar Rechazo (Rojo)
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
