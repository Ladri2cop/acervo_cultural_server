<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<!-- Contenedor Principal: Préstamos y Transferencias -->
<div class="container-fluid px-3 py-2">

  <!-- Encabezado Institucional -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
        <i class='bx bx-transfer-alt text-primary'></i> Préstamos y Transferencias
      </h1>
      <p class="text-muted small mb-0">
        Control y seguimiento institucional de movimientos de bienes culturales entre museos, unidades administrativas, áreas de la Secretaría de Cultura y Turismo y dependencias de gobierno.
      </p>
    </div>
    <div class="mt-3 mt-sm-0 d-flex gap-2">
      <button type="button" id="btn-refresh-prestamos" class="btn btn-outline-secondary btn-sm shadow-sm">
        <i class='bx bx-refresh'></i> Actualizar
      </button>
      <button type="button" id="btn-nuevo-movimiento" class="btn btn-primary btn-sm shadow-sm">
        <i class='bx bx-plus-circle'></i> Registrar Movimiento
      </button>
    </div>
  </div>

  <!-- Indicadores Clave (KPIs) -->
  <div class="row g-3 mb-4">
    <!-- KPI 1: Total Movimientos -->
    <div class="col-xl-2 col-md-4 col-sm-6">
      <div class="card border-left-secondary shadow h-100 py-2 kpi-card cursor-pointer" data-filter-tipo="todos" data-filter-estatus="todos" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body py-2 px-3">
          <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Movimientos</div>
          <div class="h4 mb-0 font-weight-bold text-gray-800" id="kpi-total">0</div>
          <small class="text-muted">Historial completo</small>
        </div>
      </div>
    </div>

    <!-- KPI 2: Préstamos Activos -->
    <div class="col-xl-3 col-md-4 col-sm-6">
      <div class="card border-left-info shadow h-100 py-2 kpi-card cursor-pointer" data-filter-tipo="prestamo" data-filter-estatus="activo" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body py-2 px-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Préstamos Activos</div>
              <div class="h4 mb-0 font-weight-bold text-info" id="kpi-activos">0</div>
              <small class="text-muted">Piezas en comodato / préstamo</small>
            </div>
            <i class='bx bx-time-five text-info' style="font-size: 2.2rem; opacity: 0.8;"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- KPI 3: Préstamos Vencidos -->
    <div class="col-xl-3 col-md-4 col-sm-6">
      <div class="card border-left-danger shadow h-100 py-2 kpi-card cursor-pointer" data-filter-tipo="prestamo" data-filter-estatus="vencido" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body py-2 px-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Préstamos Vencidos</div>
              <div class="h4 mb-0 font-weight-bold text-danger" id="kpi-vencidos">0</div>
              <small class="text-muted">Fecha estimada superada</small>
            </div>
            <i class='bx bx-alarm-exclamation text-danger' style="font-size: 2.2rem; opacity: 0.8;"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- KPI 4: Préstamos Devueltos -->
    <div class="col-xl-2 col-md-6 col-sm-6">
      <div class="card border-left-success shadow h-100 py-2 kpi-card cursor-pointer" data-filter-tipo="prestamo" data-filter-estatus="devuelto" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body py-2 px-3">
          <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Préstamos Devueltos</div>
          <div class="h4 mb-0 font-weight-bold text-success" id="kpi-devueltos">0</div>
          <small class="text-muted">Reincorporadas a origen</small>
        </div>
      </div>
    </div>

    <!-- KPI 5: Transferencias -->
    <div class="col-xl-2 col-md-6 col-sm-6">
      <div class="card border-left-primary shadow h-100 py-2 kpi-card cursor-pointer" data-filter-tipo="transferencia" data-filter-estatus="todos" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="card-body py-2 px-3">
          <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Transferencias</div>
          <div class="h4 mb-0 font-weight-bold text-primary" id="kpi-transferencias">0</div>
          <small class="text-muted">Cambios definitivos</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Barra de Filtros y Búsqueda -->
  <div class="card shadow-sm mb-4 border-0">
    <div class="card-body p-3 bg-light rounded">
      <div class="row g-2 align-items-center">
        <!-- Filtro: Tipo de Movimiento -->
        <div class="col-lg-2 col-md-4 col-sm-6">
          <label class="form-label small font-weight-bold text-muted mb-1">Tipo de Movimiento</label>
          <select id="filter-tipo-movimiento" class="form-select form-select-sm">
            <option value="todos">Todos los movimientos</option>
            <option value="prestamo">Préstamos temporales</option>
            <option value="transferencia">Transferencias definitivas</option>
          </select>
        </div>

        <!-- Filtro: Estatus -->
        <div class="col-lg-2 col-md-4 col-sm-6">
          <label class="form-label small font-weight-bold text-muted mb-1">Estatus del Movimiento</label>
          <select id="filter-estatus" class="form-select form-select-sm">
            <option value="todos">Todos los estatus</option>
            <option value="activo">Activos (en curso)</option>
            <option value="vencido">Vencidos (plazo agotado)</option>
            <option value="devuelto">Devueltos</option>
            <option value="completada">Completadas (transferencias)</option>
            <option value="cancelado">Cancelados</option>
          </select>
        </div>

        <!-- Filtro: Catálogo de Acervo -->
        <div class="col-lg-2 col-md-4 col-sm-6">
          <label class="form-label small font-weight-bold text-muted mb-1">Catálogo</label>
          <select id="filter-tipo-acervo" class="form-select form-select-sm">
            <option value="todos">Todos los catálogos</option>
            <option value="general">Acervo General</option>
            <option value="arqueologico">Acervo Arqueológico</option>
            <option value="numismatica">Acervo Numismático</option>
          </select>
        </div>

        <!-- Búsqueda en tiempo real -->
        <div class="col-lg-4 col-md-8 col-sm-12">
          <label class="form-label small font-weight-bold text-muted mb-1">Búsqueda rápida</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0">
              <i class='bx bx-search text-muted'></i>
            </span>
            <input type="text" id="filter-search" class="form-control border-start-0" placeholder="Buscar por folio, código de pieza, sede, responsable, oficio...">
            <button class="btn btn-outline-secondary" type="button" id="btn-limpiar-busqueda" title="Limpiar búsqueda">
              <i class='bx bx-x'></i>
            </button>
          </div>
        </div>

        <!-- Botón Reset Filtros -->
        <div class="col-lg-2 col-md-4 col-sm-12 text-end">
          <label class="form-label small d-none d-lg-block mb-1">&nbsp;</label>
          <button type="button" id="btn-reset-filtros" class="btn btn-outline-secondary btn-sm w-100">
            <i class='bx bx-reset'></i> Limpiar Filtros
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de Movimientos -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-gray-800">
        <i class='bx bx-list-ul text-primary me-1'></i> Registro Histórico de Préstamos y Transferencias
      </h6>
      <span class="badge bg-light text-dark border px-2 py-1" id="lbl-total-registros">0 registros</span>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-gray-800" id="tabla-prestamos">
          <thead class="table-light text-uppercase text-secondary font-weight-bold" style="font-size: 0.75rem;">
            <tr>
              <th scope="col" class="ps-3" style="width: 140px;">Folio y Tipo</th>
              <th scope="col">Pieza Cultural</th>
              <th scope="col">Origen ➔ Destino</th>
              <th scope="col" style="width: 150px;">Fechas</th>
              <th scope="col" class="text-center" style="width: 110px;">Estatus</th>
              <th scope="col" class="text-center pe-3" style="width: 140px;">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbody-prestamos" style="font-size: 0.88rem;">
            <!-- Se carga dinámicamente mediante AJAX -->
          </tbody>
        </table>
      </div>

      <!-- Estado Vacío -->
      <div id="empty-state-prestamos" class="text-center py-5 d-none">
        <div class="text-muted mb-2">
          <i class='bx bx-folder-open' style="font-size: 3.5rem; opacity: 0.5;"></i>
        </div>
        <h6 class="font-weight-bold text-gray-700">No se encontraron movimientos registrados</h6>
        <p class="text-muted small mb-3">No hay préstamos o transferencias que coincidan con los criterios de búsqueda.</p>
        <button type="button" class="btn btn-primary btn-sm" id="btn-empty-nuevo">
          <i class='bx bx-plus-circle'></i> Registrar Primer Movimiento
        </button>
      </div>

      <!-- Spinner de Carga -->
      <div id="loading-state-prestamos" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="text-muted small mt-2">Cargando registros institucionales...</p>
      </div>
    </div>

    <!-- Paginador -->
    <div class="card-footer bg-white border-top d-flex flex-column flex-sm-row justify-content-between align-items-center py-2 px-3">
      <div class="small text-muted mb-2 mb-sm-0" id="info-paginacion">
        Mostrando 0 a 0 de 0 movimientos
      </div>
      <nav aria-label="Navegación de páginas">
        <ul class="pagination pagination-sm mb-0 justify-content-center" id="paginacion-prestamos">
          <!-- Botones de página dinámicos -->
        </ul>
      </nav>
    </div>
  </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTRAR NUEVO PRÉSTAMO O TRANSFERENCIA                           -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalNuevoMovimiento" tabindex="-1" aria-labelledby="modalNuevoMovimientoLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold d-flex align-items-center" id="modalNuevoMovimientoLabel">
          <i class='bx bx-transfer-alt me-2' style="font-size: 1.4rem;"></i>
          Registrar Préstamo o Transferencia de Pieza
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formNuevoMovimiento" novalidate>
        <div class="modal-body p-4">
          <!-- Alerta explicativa -->
          <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4 py-2 px-3">
            <i class='bx bx-info-circle fs-4 me-2 text-info'></i>
            <span class="small">
              Seleccione la pieza aprobada del catálogo, especifique la sede de origen y la sede de destino, y determine si se trata de un <strong>Préstamo temporal</strong> (con fecha estimada de retorno) o una <strong>Transferencia definitiva</strong>. La ubicación física de la pieza se actualizará de forma automática.
            </span>
          </div>

          <!-- SECCIÓN 1: TIPO DE MOVIMIENTO -->
          <div class="mb-4">
            <label class="form-label font-weight-bold text-gray-800 d-block">
              1. Seleccione el Tipo de Movimiento <span class="text-danger">*</span>
            </label>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="card h-100 p-3 border cursor-pointer tipo-movimiento-card" style="cursor: pointer; transition: all 0.2s;">
                  <div class="form-check d-flex align-items-start">
                    <input class="form-check-input me-3 mt-1" type="radio" name="tipo_movimiento" id="tipo_prestamo" value="prestamo" checked>
                    <div>
                      <span class="font-weight-bold text-gray-800 d-block mb-1">
                        <i class='bx bx-time-five text-info me-1'></i> Préstamo Temporal
                      </span>
                      <small class="text-muted">
                        Salida temporal para exposiciones, préstamo interinstitucional, mantenimiento o comodato. Requiere fecha estimada de devolución y permite registrar el reingreso posterior de la pieza.
                      </small>
                    </div>
                  </div>
                </label>
              </div>

              <div class="col-md-6">
                <label class="card h-100 p-3 border cursor-pointer tipo-movimiento-card" style="cursor: pointer; transition: all 0.2s;">
                  <div class="form-check d-flex align-items-start">
                    <input class="form-check-input me-3 mt-1" type="radio" name="tipo_movimiento" id="tipo_transferencia" value="transferencia">
                    <div>
                      <span class="font-weight-bold text-gray-800 d-block mb-1">
                        <i class='bx bx-right-arrow-alt text-primary me-1'></i> Transferencia Definitiva
                      </span>
                      <small class="text-muted">
                        Cambio permanente de adscripción, acervo o sede entre museos, unidades administrativas u dependencias de gobierno. La nueva ubicación pasa a ser la sede oficial de la pieza.
                      </small>
                    </div>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <!-- SECCIÓN 2: SELECCIÓN DE LA PIEZA -->
          <div class="card bg-light border mb-4">
            <div class="card-body p-3">
              <label class="form-label font-weight-bold text-gray-800 mb-2">
                2. Selección de la Pieza Cultural <span class="text-danger">*</span>
              </label>

              <!-- Buscador interactivo de piezas -->
              <div class="row g-2 mb-2">
                <div class="col-md-4">
                  <select id="modal-select-catalogo" class="form-select form-select-sm">
                    <option value="todos">Todos los catálogos aprobados</option>
                    <option value="general">Acervo General</option>
                    <option value="arqueologico">Acervo Arqueológico</option>
                    <option value="numismatica">Acervo Numismático</option>
                  </select>
                </div>
                <div class="col-md-8">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class='bx bx-search'></i></span>
                    <input type="text" id="modal-search-pieza" class="form-control" placeholder="Escriba código interno, número de inventario o título de la pieza..." autocomplete="off">
                  </div>
                </div>
              </div>

              <!-- Lista desplegable de resultados de búsqueda -->
              <div id="modal-piezas-resultados" class="list-group mb-2 shadow-sm d-none" style="max-height: 220px; overflow-y: auto;">
                <!-- Resultados renderizados por JS -->
              </div>

              <!-- Vista previa de la pieza seleccionada -->
              <div id="modal-pieza-seleccionada" class="card border-primary d-none mt-2">
                <div class="card-body p-3">
                  <div class="row align-items-center">
                    <div class="col-auto">
                      <img id="sel-pieza-foto" src="assets/img/placeholder.png" alt="Foto Pieza" class="rounded border shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">
                    </div>
                    <div class="col">
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary" id="sel-pieza-badge-catalogo">General</span>
                        <span class="font-weight-bold text-gray-800" id="sel-pieza-codigo">CI-001</span>
                      </div>
                      <h6 class="font-weight-bold text-gray-900 mb-1" id="sel-pieza-titulo">Título de la pieza</h6>
                      <div class="small text-muted">
                        <i class='bx bx-map-pin text-danger'></i> 
                        <strong>Ubicación física actual:</strong> 
                        <span id="sel-pieza-ubicacion" class="text-primary font-weight-bold">Pasillo central</span>
                      </div>
                    </div>
                    <div class="col-auto">
                      <button type="button" id="btn-cambiar-pieza" class="btn btn-outline-secondary btn-sm">
                        <i class='bx bx-refresh'></i> Cambiar Pieza
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Inputs ocultos para enviar la pieza seleccionada -->
              <input type="hidden" name="tipo_acervo" id="input_tipo_acervo" value="" required>
              <input type="hidden" name="id_pieza" id="input_id_pieza" value="" required>
            </div>
          </div>

          <!-- SECCIÓN 3: ORIGEN Y DESTINO -->
          <div class="row g-3 mb-4">
            <!-- Columna: ORIGEN -->
            <div class="col-lg-6">
              <div class="card h-100 border-start border-4 border-info shadow-sm">
                <div class="card-header bg-white py-2">
                  <h6 class="font-weight-bold text-info mb-0">
                    <i class='bx bx-log-out-circle me-1'></i> 3. Origen del Movimiento
                  </h6>
                </div>
                <div class="card-body p-3">
                  <div class="mb-3">
                    <label class="form-label small font-weight-bold text-gray-800">
                      Tipo de Dependencia / Entidad de Origen <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm" name="tipo_origen" id="select_tipo_origen" required>
                      <option value="museo">Museo Institucional</option>
                      <option value="unidad">Unidad Administrativa SCyT</option>
                      <option value="area_scyt">Área / Dirección de la SCyT</option>
                      <option value="otra_dependencia">Otra Dependencia de Gobierno</option>
                    </select>
                  </div>

                  <!-- Selector rápido si es museo -->
                  <div class="mb-3" id="wrapper-museo-origen">
                    <label class="form-label small font-weight-bold text-gray-800">
                      Seleccionar Museo Registrado
                    </label>
                    <select class="form-select form-select-sm" id="select_museo_origen_catalogo">
                      <option value="">-- Seleccionar museo existente o escribir abajo --</option>
                      <?php if (!empty($museos)): ?>
                        <?php foreach ($museos as $m): ?>
                          <option value="<?php echo htmlspecialchars($m['nombre_museo']); ?>" 
                                  data-resp="<?php echo htmlspecialchars($m['responsable_museo'] ?? ''); ?>">
                            <?php echo htmlspecialchars($m['nombre_museo']); ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label small font-weight-bold text-gray-800">
                      Nombre del Lugar / Sede de Origen <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm" name="lugar_origen" id="input_lugar_origen" placeholder="Ej. Museo de Antropología / Bóveda central" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Sede donde se encuentra actualmente la pieza.</small>
                  </div>

                  <div>
                    <label class="form-label small font-weight-bold text-gray-800">
                      Responsable / Funcionario que Entrega
                    </label>
                    <input type="text" class="form-control form-control-sm" name="responsable_origen" id="input_responsable_origen" placeholder="Nombre completo del titular o custodio de origen">
                  </div>
                </div>
              </div>
            </div>

            <!-- Columna: DESTINO -->
            <div class="col-lg-6">
              <div class="card h-100 border-start border-4 border-primary shadow-sm">
                <div class="card-header bg-white py-2">
                  <h6 class="font-weight-bold text-primary mb-0">
                    <i class='bx bx-log-in-circle me-1'></i> 4. Destino del Movimiento
                  </h6>
                </div>
                <div class="card-body p-3">
                  <div class="mb-3">
                    <label class="form-label small font-weight-bold text-gray-800">
                      Tipo de Dependencia / Entidad de Destino <span class="text-danger">*</span>
                    </label>
                    <select class="form-select form-select-sm" name="tipo_destino" id="select_tipo_destino" required>
                      <option value="museo">Museo Institucional</option>
                      <option value="unidad">Unidad Administrativa SCyT</option>
                      <option value="area_scyt">Área / Dirección de la SCyT</option>
                      <option value="otra_dependencia">Otra Dependencia de Gobierno</option>
                    </select>
                  </div>

                  <!-- Selector rápido si es museo -->
                  <div class="mb-3" id="wrapper-museo-destino">
                    <label class="form-label small font-weight-bold text-gray-800">
                      Seleccionar Museo Registrado
                    </label>
                    <select class="form-select form-select-sm" id="select_museo_destino_catalogo">
                      <option value="">-- Seleccionar museo existente o escribir abajo --</option>
                      <?php if (!empty($museos)): ?>
                        <?php foreach ($museos as $m): ?>
                          <option value="<?php echo htmlspecialchars($m['nombre_museo']); ?>"
                                  data-resp="<?php echo htmlspecialchars($m['responsable_museo'] ?? ''); ?>">
                            <?php echo htmlspecialchars($m['nombre_museo']); ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label small font-weight-bold text-gray-800">
                      Nombre del Lugar / Sede de Destino <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm" name="lugar_destino" id="input_lugar_destino" placeholder="Ej. Museo de Arte Virreinal / Sala 2" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Sede donde se exhibirá, resguardará o transferirá.</small>
                  </div>

                  <div>
                    <label class="form-label small font-weight-bold text-gray-800">
                      Responsable / Funcionario que Recibe
                    </label>
                    <input type="text" class="form-control form-control-sm" name="responsable_destino" id="input_responsable_destino" placeholder="Nombre completo del titular o receptor en destino">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- SECCIÓN 4: FECHAS, OFICIO Y CONDICIONES -->
          <div class="card border mb-3">
            <div class="card-header bg-white py-2">
              <h6 class="font-weight-bold text-gray-800 mb-0">
                <i class='bx bx-calendar-event me-1'></i> 5. Fechas, Justificación y Documentación
              </h6>
            </div>
            <div class="card-body p-3">
              <div class="row g-3">
                <!-- Fecha Salida -->
                <div class="col-md-4">
                  <label class="form-label small font-weight-bold text-gray-800">
                    Fecha de Salida <span class="text-danger">*</span>
                  </label>
                  <input type="date" class="form-control form-control-sm" name="fecha_salida" id="input_fecha_salida" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <!-- Fecha Devolución Estimada (solo si préstamo) -->
                <div class="col-md-4" id="wrapper-fecha-devolucion">
                  <label class="form-label small font-weight-bold text-gray-800">
                    Fecha Estimada de Devolución <span class="text-danger">*</span>
                  </label>
                  <input type="date" class="form-control form-control-sm" name="fecha_devolucion_estimada" id="input_fecha_devolucion_estimada">
                  <small class="text-muted" style="font-size: 0.72rem;">Plazo pactado para retornar la pieza.</small>
                </div>

                <!-- No. Oficio Autorización -->
                <div class="col-md-4">
                  <label class="form-label small font-weight-bold text-gray-800">
                    No. de Oficio / Memorándum
                  </label>
                  <input type="text" class="form-control form-control-sm" name="no_oficio_autorizacion" id="input_no_oficio" placeholder="Ej. SCyT/DPCVT/2026-0158">
                </div>

                <!-- Motivo del Movimiento -->
                <div class="col-12">
                  <label class="form-label small font-weight-bold text-gray-800">
                    Motivo o Justificación del Movimiento <span class="text-danger">*</span>
                  </label>
                  <textarea class="form-control form-control-sm" name="motivo" id="input_motivo" rows="2" placeholder="Describa el propósito: Exposición temporal conmemorativa, convenio de colaboración, comodato interinstitucional, reasignación patrimonial definitiva..." required></textarea>
                </div>

                <!-- Condiciones de Entrega -->
                <div class="col-md-6">
                  <label class="form-label small font-weight-bold text-gray-800">
                    Condiciones Físicas de Entrega
                  </label>
                  <textarea class="form-control form-control-sm" name="condiciones_entrega" id="input_condiciones_entrega" rows="2" placeholder="Estado de conservación al salir, embalaje utilizado, observaciones de seguridad..."></textarea>
                </div>

                <!-- Observaciones -->
                <div class="col-md-6">
                  <label class="form-label small font-weight-bold text-gray-800">
                    Observaciones Adicionales
                  </label>
                  <textarea class="form-control form-control-sm" name="observaciones" id="input_observaciones" rows="2" placeholder="Notas de transporte, seguro contratado o precisiones del movimiento..."></textarea>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer bg-light py-2 px-3">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-4" id="btn-guardar-movimiento">
            <i class='bx bx-check-circle me-1'></i> Confirmar y Registrar Movimiento
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTRAR DEVOLUCIÓN DE PRÉSTAMO                                   -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDevolucionPrestamo" tabindex="-1" aria-labelledby="modalDevolucionPrestamoLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-success text-white py-3">
        <h5 class="modal-title font-weight-bold d-flex align-items-center" id="modalDevolucionPrestamoLabel">
          <i class='bx bx-check-shield me-2' style="font-size: 1.4rem;"></i>
          Registrar Devolución de Préstamo
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formDevolverPrestamo" novalidate>
        <div class="modal-body p-4">
          <input type="hidden" name="id_movimiento" id="dev_id_movimiento" value="">

          <!-- Tarjeta de Resumen del Préstamo -->
          <div class="card bg-light border mb-4">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-auto">
                  <img id="dev_pieza_foto" src="assets/img/placeholder.png" alt="Foto" class="rounded border" style="width: 65px; height: 65px; object-fit: cover;">
                </div>
                <div class="col">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-secondary font-monospace" id="dev_folio">PRE-2026-0001</span>
                    <span class="badge bg-info" id="dev_pieza_codigo">CI-001</span>
                  </div>
                  <h6 class="font-weight-bold text-gray-900 mb-1" id="dev_pieza_titulo">Título de la pieza</h6>
                  <div class="small text-muted">
                    <span><strong>Prestado a:</strong> <span id="dev_lugar_destino" class="text-primary font-weight-bold">Museo</span></span>
                    <span class="ms-3"><strong>Fecha salida:</strong> <span id="dev_fecha_salida">--</span></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="alert alert-success border-0 small py-2 px-3 mb-3">
            <i class='bx bx-info-circle me-1'></i> Al confirmar la devolución, el estatus pasará a <strong>Devuelto</strong> y la ubicación física de la pieza se actualizará a la sede de reincorporación seleccionada.
          </div>

          <div class="row g-3">
            <!-- Fecha de devolución real -->
            <div class="col-md-6">
              <label class="form-label small font-weight-bold text-gray-800">
                Fecha Real de Devolución <span class="text-danger">*</span>
              </label>
              <input type="date" class="form-control form-control-sm" name="fecha_devolucion_real" id="dev_fecha_real" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <!-- Lugar de Reincorporación / Retorno -->
            <div class="col-md-6">
              <label class="form-label small font-weight-bold text-gray-800">
                Lugar / Sala de Reincorporación <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control form-control-sm" name="lugar_devolucion" id="dev_lugar_reinstalacion" placeholder="Ej. Bóveda central, Sala 1..." required>
              <small class="text-muted" style="font-size: 0.72rem;">Sede donde se resguardará de nuevo la pieza.</small>
            </div>

            <!-- Condiciones en que se recibe -->
            <div class="col-12">
              <label class="form-label small font-weight-bold text-gray-800">
                Condiciones Físicas al Recibir la Pieza <span class="text-danger">*</span>
              </label>
              <textarea class="form-control form-control-sm" name="condiciones_devolucion" id="dev_condiciones" rows="2" placeholder="Describa el estado de conservación al recibir la pieza devuelta (íntegro, sin alteraciones, empaque sellado...)" required></textarea>
            </div>

            <!-- Observaciones de Devolución -->
            <div class="col-12">
              <label class="form-label small font-weight-bold text-gray-800">
                Observaciones de Devolución
              </label>
              <textarea class="form-control form-control-sm" name="observaciones_devolucion" id="dev_observaciones" rows="2" placeholder="Notas sobre el acta de entrega-recepción de retorno o detalles del personal que entregó..."></textarea>
            </div>
          </div>

        </div>

        <div class="modal-footer bg-light py-2 px-3">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success btn-sm font-weight-bold px-4" id="btn-confirmar-devolucion">
            <i class='bx bx-check-double me-1'></i> Registrar Devolución Concluida
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETALLE / EXPEDIENTE DEL MOVIMIENTO                                -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDetalleMovimiento" tabindex="-1" aria-labelledby="modalDetalleMovimientoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-dark text-white py-3">
        <div class="d-flex align-items-center">
          <i class='bx bx-file-blank me-2' style="font-size: 1.5rem;"></i>
          <div>
            <h5 class="modal-title font-weight-bold mb-0" id="modalDetalleMovimientoLabel">
              Expediente Institucional de Movimiento
            </h5>
            <small class="text-muted text-light opacity-75" id="det-folio-sub">Folio: ---</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" id="btn-imprimir-expediente" class="btn btn-outline-light btn-sm">
            <i class='bx bx-printer'></i> Imprimir
          </button>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      </div>

      <div class="modal-body p-4" id="printable-expediente-body">
        <!-- Encabezado del Expediente -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
          <div>
            <span class="badge bg-primary px-2 py-1 fs-6 font-monospace" id="det-folio">FOLIO</span>
            <span class="badge ms-2" id="det-tipo-badge">Tipo</span>
            <span class="badge ms-2" id="det-estatus-badge">Estatus</span>
          </div>
          <div class="text-end text-muted small">
            <div><strong>Fecha de Salida:</strong> <span id="det-fecha-salida">--</span></div>
            <div id="det-wrapper-estimada"><strong>Fecha Retorno Estimada:</strong> <span id="det-fecha-estimada">--</span></div>
            <div id="det-wrapper-real" class="d-none text-success"><strong>Fecha Devolución Real:</strong> <span id="det-fecha-real">--</span></div>
          </div>
        </div>

        <!-- Ficha de la Pieza -->
        <div class="card border mb-3">
          <div class="card-header bg-light py-2 font-weight-bold text-gray-800 small">
            <i class='bx bx-cube me-1'></i> Bien Cultural Objeto del Movimiento
          </div>
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col-auto">
                <img id="det-pieza-foto" src="assets/img/placeholder.png" alt="Pieza" class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
              </div>
              <div class="col">
                <h6 class="font-weight-bold text-gray-900 mb-1" id="det-pieza-titulo">Título de la Pieza</h6>
                <div class="small text-muted mb-1">
                  <span><strong>Código interno:</strong> <span id="det-pieza-codigo">--</span></span>
                  <span class="ms-3"><strong>Catálogo:</strong> <span id="det-pieza-catalogo">--</span></span>
                </div>
                <div class="small text-muted">
                  <i class='bx bx-map-pin text-danger'></i> <strong>Ubicación actual registrada:</strong> <span id="det-pieza-ubicacion" class="text-primary font-weight-bold">--</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flujo Visual Origen -> Destino -->
        <div class="card border mb-3">
          <div class="card-header bg-light py-2 font-weight-bold text-gray-800 small">
            <i class='bx bx-trip me-1'></i> Trayecto del Movimiento
          </div>
          <div class="card-body p-3">
            <div class="row g-3 align-items-center text-center text-sm-start">
              <div class="col-sm-5">
                <div class="p-2 border rounded bg-light">
                  <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                    <i class='bx bx-log-out-circle'></i> Sede de Origen
                  </div>
                  <div class="font-weight-bold text-gray-900" id="det-lugar-origen">--</div>
                  <div class="small text-muted" id="det-tipo-origen">--</div>
                  <div class="small text-secondary mt-1"><strong>Entrega:</strong> <span id="det-responsable-origen">--</span></div>
                </div>
              </div>
              <div class="col-sm-2 text-center my-2 my-sm-0">
                <i class='bx bx-right-arrow-alt text-primary d-none d-sm-inline' style="font-size: 2.2rem;"></i>
                <i class='bx bx-down-arrow-alt text-primary d-sm-none' style="font-size: 2.2rem;"></i>
              </div>
              <div class="col-sm-5">
                <div class="p-2 border rounded bg-light">
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    <i class='bx bx-log-in-circle'></i> Sede de Destino
                  </div>
                  <div class="font-weight-bold text-gray-900" id="det-lugar-destino">--</div>
                  <div class="small text-muted" id="det-tipo-destino">--</div>
                  <div class="small text-secondary mt-1"><strong>Recibe:</strong> <span id="det-responsable-destino">--</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Justificación y Datos Administrativos -->
        <div class="card border mb-3">
          <div class="card-header bg-light py-2 font-weight-bold text-gray-800 small">
            <i class='bx bx-detail me-1'></i> Datos Administrativos y Justificación
          </div>
          <div class="card-body p-3 small">
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <strong>No. de Oficio / Autorización:</strong>
                <div id="det-no-oficio" class="text-gray-900 font-monospace">--</div>
              </div>
              <div class="col-md-6">
                <strong>Registrado por:</strong>
                <div id="det-usuario-registro" class="text-gray-900">--</div>
              </div>
            </div>

            <div class="mb-2">
              <strong>Motivo del Movimiento:</strong>
              <div id="det-motivo" class="text-gray-800 p-2 bg-light rounded mt-1">--</div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <strong>Condiciones Físicas de Entrega:</strong>
                <div id="det-condiciones-entrega" class="text-gray-800 p-2 bg-light rounded mt-1">--</div>
              </div>
              <div class="col-md-6">
                <strong>Condiciones Físicas de Devolución:</strong>
                <div id="det-condiciones-devolucion" class="text-gray-800 p-2 bg-light rounded mt-1">--</div>
              </div>
            </div>

            <div class="mb-0">
              <strong>Observaciones Registradas:</strong>
              <div id="det-observaciones" class="text-muted p-2 bg-light rounded mt-1 font-monospace" style="white-space: pre-line;">--</div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer bg-light py-2 px-3">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
