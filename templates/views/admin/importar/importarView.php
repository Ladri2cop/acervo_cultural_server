<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="container-fluid px-2 px-md-4">
  <!-- Encabezado de Sección -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
      <h1 class="h3 mb-1 text-gray-800 fw-bold d-flex align-items-center gap-2">
        <i class='bx bx-cloud-upload text-primary'></i> Importador Masivo de Acervo Cultural
      </h1>
      <p class="text-muted mb-0 small">
        Carga por lotes mediante plantillas estructuradas Excel (.xlsx) o CSV (.csv) con validación automática y trazabilidad institucional.
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="admin/acervo" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
        <i class='bx bx-database-alt'></i> Ver Catálogo
      </a>
      <a href="admin/revision" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
        <i class='bx bx-clipboard-check'></i> Mesa de Revisión
      </a>
    </div>
  </div>

  <!-- Paso 1: Selección de Tipo de Acervo -->
  <div class="card shadow-sm border-0 mb-4 rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
      <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
        <span class="badge bg-primary rounded-circle px-2 py-1">1</span>
        Seleccione el Tipo de Acervo a Importar
      </h6>
      <?php if (!empty($d->user_museum_name)): ?>
        <span class="badge bg-light text-dark border px-3 py-2">
          <i class='bx bxs-institution text-primary me-1'></i> Sede: <strong><?php echo htmlspecialchars($d->user_museum_name); ?></strong>
        </span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <!-- Acervo General -->
        <div class="col-12 col-md-4">
          <div class="tipo-acervo-card p-3 rounded-3 border active cursor-pointer h-100 transition-all" data-tipo="general" id="card-tipo-general">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="icon-shape bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(107,29,47,0.1); color: #6B1D2F;">
                <i class='bx bx-palette fs-4'></i>
              </div>
              <input type="radio" name="radio_tipo_acervo" id="radio_general" value="general" checked class="form-check-input mt-0">
            </div>
            <h5 class="fw-bold mb-1 fs-6 text-dark">Acervo General</h5>
            <p class="text-muted small mb-0">Pintura, escultura, artes utilitarias, mobiliario, obra gráfica, textil y fotografía.</p>
          </div>
        </div>

        <!-- Acervo Arqueológico -->
        <div class="col-12 col-md-4">
          <div class="tipo-acervo-card p-3 rounded-3 border cursor-pointer h-100 transition-all" data-tipo="arqueologico" id="card-tipo-arqueologico">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="icon-shape bg-warning-subtle text-warning-emphasis rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(179,142,46,0.15); color: #B38E2E;">
                <i class='bx bx-arch fs-4'></i>
              </div>
              <input type="radio" name="radio_tipo_acervo" id="radio_arqueologico" value="arqueologico" class="form-check-input mt-0">
            </div>
            <h5 class="fw-bold mb-1 fs-6 text-dark">Acervo Arqueológico</h5>
            <p class="text-muted small mb-0">Artefactos prehispánicos, lítica, cerámica, figurillas, vasos trípodes y registro INAH.</p>
          </div>
        </div>

        <!-- Acervo Numismático -->
        <div class="col-12 col-md-4">
          <div class="tipo-acervo-card p-3 rounded-3 border cursor-pointer h-100 transition-all" data-tipo="numismatica" id="card-tipo-numismatica">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="icon-shape bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(13,110,253,0.1); color: #0d6efd;">
                <i class='bx bx-coin-stack fs-4'></i>
              </div>
              <input type="radio" name="radio_tipo_acervo" id="radio_numismatica" value="numismatica" class="form-check-input mt-0">
            </div>
            <h5 class="fw-bold mb-1 fs-6 text-dark">Acervo Numismático</h5>
            <p class="text-muted small mb-0">Monedas novohispanas, billetes, medallas, fichas de hacienda, cecas y ensayadores.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Columna Izquierda: Descarga de Plantillas y Guía -->
    <div class="col-12 col-lg-5 mb-4">
      <div class="card shadow-sm border-0 rounded-3 h-100">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-circle px-2 py-1">2</span>
            Descargar Plantillas Oficiales
          </h6>
        </div>
        <div class="card-body">
          <p class="small text-muted mb-3">
            Descargue la plantilla prediseñada para el tipo de acervo seleccionado. Cada archivo incluye encabezados normalizados, claves técnicas de ingesta y ejemplos reales de captura.
          </p>

          <div class="alert alert-success bg-success-subtle border-success border-opacity-25 rounded-3 p-3 mb-3 small">
            <div class="d-flex align-items-start gap-2">
              <i class='bx bx-list-check text-success fs-4 mt-1'></i>
              <div>
                <h6 class="fw-bold text-success mb-1">¡Listas Desplegables Automatizadas en Excel!</h6>
                <p class="mb-0 text-dark">
                  La plantilla <strong>Excel (.xlsx)</strong> incorpora listas desplegables nativas en las celdas y una hoja auxiliar <strong>«Catálogos»</strong> con los museos, épocas, técnicas, materiales y estados oficiales para que no tenga que escribirlos a mano.
                </p>
              </div>
            </div>
          </div>

          <div class="d-grid gap-2 mb-4">
            <a href="admin/descargar_plantilla_importacion?tipo=general&formato=xlsx" id="btn-download-xlsx" class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
              <i class='bx bxs-file-export fs-5'></i>
              <span>Descargar Plantilla Excel (.xlsx) con Catálogos</span>
            </a>
            <a href="admin/descargar_plantilla_importacion?tipo=general&formato=csv" id="btn-download-csv" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2 py-2">
              <i class='bx bx-file fs-5'></i>
              <span>Descargar Plantilla CSV (.csv con UTF-8 BOM)</span>
            </a>
          </div>

          <div class="alert alert-light border rounded-3 p-3 mb-0 small">
            <h6 class="fw-bold text-dark d-flex align-items-center gap-1 mb-2">
              <i class='bx bx-info-circle text-primary'></i> Instrucciones y Buenas Prácticas
            </h6>
            <ul class="mb-0 ps-3 text-muted">
              <li class="mb-1"><strong>Listas desplegables:</strong> En Excel (.xlsx), haga clic en la flecha de la celda en columnas como Ubicación, Época, Tipo, Material o Técnica para elegir directamente la opción oficial.</li>
              <li class="mb-1"><strong>No modifique</strong> ni elimine las filas 1 y 2 (títulos descriptivos y claves técnicas).</li>
              <li class="mb-1">Los campos marcados con asterisco <strong>(*) son obligatorios</strong> (ej. Nombre/Título de la pieza y Ubicación Física).</li>
              <li class="mb-1">Para la <strong>Ubicación Física</strong>, debe seleccionarse o coincidir exactamente con una sede activa del catálogo.</li>
              <li class="mb-1">Puede borrar las filas de ejemplo antes de subir su archivo, o dejarlas ya que el sistema las identificará automáticamente.</li>
              <li>Tamaño máximo del archivo: <strong>20 MB</strong>.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Columna Derecha: Subida de Archivo y Parámetros -->
    <div class="col-12 col-lg-7 mb-4">
      <div class="card shadow-sm border-0 rounded-3 h-100">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-circle px-2 py-1">3</span>
            Carga de Archivo y Parámetros de Ingesta
          </h6>
        </div>
        <div class="card-body">
          <form id="form-importar-acervo" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?php echo CSRF_TOKEN; ?>">
            <input type="hidden" name="tipo_acervo" id="input_tipo_acervo" value="general">

            <!-- Zona de Carga / Drag & Drop -->
            <div class="upload-dropzone p-4 text-center rounded-3 border border-2 border-dashed mb-4 bg-light cursor-pointer" id="dropzone">
              <input type="file" name="archivo_acervo" id="input_archivo_acervo" accept=".xlsx,.xls,.csv" class="d-none">
              <div id="dropzone-idle">
                <i class='bx bx-cloud-upload display-4 text-primary mb-2'></i>
                <h6 class="fw-bold text-dark mb-1">Arrastre su archivo Excel o CSV aquí</h6>
                <p class="text-muted small mb-2">o haga clic para examinar en su equipo</p>
                <span class="badge bg-white text-secondary border">Formatos admitidos: .XLSX, .XLS, .CSV</span>
              </div>
              <div id="dropzone-file-info" class="d-none">
                <div class="d-inline-flex align-items-center gap-2 p-2 px-3 bg-white border rounded-pill shadow-sm">
                  <i class='bx bxs-file-doc text-success fs-4'></i>
                  <div class="text-start">
                    <div class="fw-bold text-dark small" id="selected-file-name">archivo.xlsx</div>
                    <div class="text-muted small" style="font-size: 0.75rem;" id="selected-file-size">0 KB</div>
                  </div>
                  <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" id="btn-remove-file" title="Quitar archivo">
                    <i class='bx bx-x fs-5'></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Parámetros de Ingesta -->
            <div class="row g-3 mb-4">
              <!-- Manejo de Duplicados -->
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold text-dark small mb-1">
                  <i class='bx bx-copy-alt text-secondary me-1'></i> Manejo de claves existentes
                </label>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="radio" name="manejo_duplicados" id="dupl_skip" value="skip" checked>
                  <label class="form-check-label small" for="dupl_skip">
                    <strong>Omitir duplicados</strong> (Recomendado)
                  </label>
                </div>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="radio" name="manejo_duplicados" id="dupl_update" value="update">
                  <label class="form-check-label small" for="dupl_update">
                    <strong>Actualizar datos</strong> de pieza existente
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="manejo_duplicados" id="dupl_error" value="error">
                  <label class="form-check-label small" for="dupl_error">
                    <strong>Rechazar fila</strong> y reportar error
                  </label>
                </div>
              </div>

              <!-- Destino de las Piezas -->
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold text-dark small mb-1">
                  <i class='bx bx-shield-quarter text-secondary me-1'></i> Destino en flujo de revisión
                </label>
                <div class="form-check mb-1">
                  <input class="form-check-input" type="radio" name="auto_aprobar" id="aprobar_pendiente" value="0" checked>
                  <label class="form-check-label small" for="aprobar_pendiente">
                    <strong>Mesa de Revisión</strong> (Pendiente)
                  </label>
                </div>
                <?php if ($d->es_super_admin || user_can_dictate_acervo()): ?>
                <div class="form-check mt-2">
                  <input class="form-check-input" type="radio" name="auto_aprobar" id="aprobar_directo" value="1">
                  <label class="form-check-label small text-success fw-bold" for="aprobar_directo">
                    Aprobar e incorporar de inmediato
                  </label>
                  <div class="text-muted" style="font-size: 0.75rem;">Se publica directamente en el catálogo activo.</div>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
              <button type="reset" class="btn btn-light border" id="btn-reset-form">
                <i class='bx bx-reset me-1'></i> Limpiar
              </button>
              <button type="submit" class="btn btn-primary px-4 fw-semibold d-flex align-items-center gap-2" id="btn-submit-import">
                <i class='bx bx-upload fs-5'></i>
                <span>Comenzar Importación</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Sección de Resultados y Métricas (Inicialmente oculta) -->
  <div class="card shadow-sm border-0 rounded-3 mb-4 d-none" id="card-resultados">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
      <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
        <i class='bx bx-bar-chart-alt-2 text-primary'></i>
        Resumen de la Importación Masiva
      </h6>
      <span class="badge bg-success px-3 py-2" id="badge-status-proceso">Finalizado</span>
    </div>
    <div class="card-body">
      <!-- Tarjetas KPI -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 border bg-light text-center">
            <span class="text-muted small fw-semibold text-uppercase">Filas Leídas</span>
            <h3 class="fw-bold mb-0 text-dark mt-1" id="kpi-total-leidas">0</h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 border text-center" style="background: rgba(25,135,84,0.08); border-color: #198754 !important;">
            <span class="text-success small fw-semibold text-uppercase">Importadas</span>
            <h3 class="fw-bold mb-0 text-success mt-1" id="kpi-insertadas">0</h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 border text-center" style="background: rgba(13,110,253,0.08); border-color: #0d6efd !important;">
            <span class="text-primary small fw-semibold text-uppercase">Actualizadas</span>
            <h3 class="fw-bold mb-0 text-primary mt-1" id="kpi-actualizadas">0</h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="p-3 rounded-3 border text-center" style="background: rgba(255,193,7,0.12); border-color: #ffc107 !important;">
            <span class="text-warning-emphasis small fw-semibold text-uppercase">Omitidas / Claves</span>
            <h3 class="fw-bold mb-0 text-dark mt-1" id="kpi-omitidas">0</h3>
          </div>
        </div>
      </div>

      <!-- Mensaje descriptivo de resultado -->
      <div class="alert alert-info border rounded-3 p-3 mb-4 d-flex align-items-center gap-2" id="alert-resumen-msg">
        <i class='bx bx-info-circle fs-4'></i>
        <span id="txt-resumen-msg">El proceso finalizó con éxito.</span>
      </div>

      <!-- Tabla de Inconsistencias / Errores si existen -->
      <div id="seccion-errores-tabla" class="d-none mb-4">
        <h6 class="fw-bold text-danger d-flex align-items-center gap-2 mb-2">
          <i class='bx bx-error-circle'></i> Filas con Observaciones o Inconsistencias (<span id="count-errores">0</span>)
        </h6>
        <div class="table-responsive rounded-3 border">
          <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light small text-uppercase">
              <tr>
                <th style="width: 90px;" class="text-center">Fila</th>
                <th style="width: 180px;">Identificador</th>
                <th style="width: 120px;">Tipo</th>
                <th>Descripción del Problema Detectado</th>
              </tr>
            </thead>
            <tbody id="tbody-errores" class="small">
              <!-- Se puebla dinámicamente -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Botones de Navegación Rápida -->
      <div class="d-flex flex-wrap justify-content-end gap-2 pt-3 border-top">
        <button type="button" class="btn btn-outline-secondary" id="btn-importar-otro">
          <i class='bx bx-plus me-1'></i> Importar Otro Lote
        </button>
        <a href="admin/revision" class="btn btn-outline-primary" id="btn-ir-revision">
          <i class='bx bx-clipboard-check me-1'></i> Ir a Mesa de Revisión
        </a>
        <a href="admin/acervo" class="btn btn-success" id="btn-ir-acervo">
          <i class='bx bx-database-alt me-1'></i> Consultar Acervo
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Modal / Overlay de Procesamiento con Barra de Progreso -->
<div class="modal fade" id="modalProcesando" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center p-4">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
          <span class="visually-hidden">Procesando...</span>
        </div>
        <h5 class="fw-bold text-dark mb-1">Procesando Importación Masiva</h5>
        <p class="text-muted small mb-3">Analizando celdas, validando inventarios y registrando en la base de datos...</p>
        <div class="progress" style="height: 10px;">
          <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.tipo-acervo-card {
  transition: all 0.2s ease-in-out;
  background: #ffffff;
  border-color: #dee2e6 !important;
}
.tipo-acervo-card:hover {
  border-color: #6B1D2F !important;
  box-shadow: 0 4px 12px rgba(107, 29, 47, 0.08);
}
.tipo-acervo-card.active {
  border-color: #6B1D2F !important;
  border-width: 2px !important;
  background: rgba(107, 29, 47, 0.02);
}
.upload-dropzone {
  transition: all 0.2s ease;
  border-color: #ced4da !important;
}
.upload-dropzone:hover, .upload-dropzone.dragover {
  border-color: #6B1D2F !important;
  background-color: rgba(107, 29, 47, 0.04) !important;
}
.cursor-pointer {
  cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const CSRF_TOKEN = '<?php echo CSRF_TOKEN; ?>';

  // Elementos DOM
  const tipoCards = document.querySelectorAll('.tipo-acervo-card');
  const inputTipoAcervo = document.getElementById('input_tipo_acervo');
  const btnDownloadXlsx = document.getElementById('btn-download-xlsx');
  const btnDownloadCsv = document.getElementById('btn-download-csv');

  const dropzone = document.getElementById('dropzone');
  const inputArchivo = document.getElementById('input_archivo_acervo');
  const dropzoneIdle = document.getElementById('dropzone-idle');
  const dropzoneFileInfo = document.getElementById('dropzone-file-info');
  const selectedFileName = document.getElementById('selected-file-name');
  const selectedFileSize = document.getElementById('selected-file-size');
  const btnRemoveFile = document.getElementById('btn-remove-file');

  const formImportar = document.getElementById('form-importar-acervo');
  const btnSubmit = document.getElementById('btn-submit-import');
  const btnReset = document.getElementById('btn-reset-form');

  const cardResultados = document.getElementById('card-resultados');
  const kpiTotalLeidas = document.getElementById('kpi-total-leidas');
  const kpiInsertadas = document.getElementById('kpi-insertadas');
  const kpiActualizadas = document.getElementById('kpi-actualizadas');
  const kpiOmitidas = document.getElementById('kpi-omitidas');
  const txtResumenMsg = document.getElementById('txt-resumen-msg');
  const seccionErroresTabla = document.getElementById('seccion-errores-tabla');
  const countErrores = document.getElementById('count-errores');
  const tbodyErrores = document.getElementById('tbody-errores');
  const btnImportarOtro = document.getElementById('btn-importar-otro');

  const modalProcesandoEl = document.getElementById('modalProcesando');
  const modalProcesando = new bootstrap.Modal(modalProcesandoEl, { backdrop: 'static', keyboard: false });

  // 1. Selector de Tipo de Acervo
  tipoCards.forEach(card => {
    card.addEventListener('click', function () {
      tipoCards.forEach(c => {
        c.classList.remove('active');
        const r = c.querySelector('input[type="radio"]');
        if (r) r.checked = false;
      });

      this.classList.add('active');
      const radio = this.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;

      const tipo = this.getAttribute('data-tipo');
      inputTipoAcervo.value = tipo;

      // Actualizar URLs de descarga
      btnDownloadXlsx.href = `admin/descargar_plantilla_importacion?tipo=${tipo}&formato=xlsx`;
      btnDownloadCsv.href = `admin/descargar_plantilla_importacion?tipo=${tipo}&formato=csv`;
    });
  });

  // 2. Manejo de archivo y dropzone
  dropzone.addEventListener('click', function (e) {
    if (e.target === btnRemoveFile || btnRemoveFile.contains(e.target)) return;
    inputArchivo.click();
  });

  dropzone.addEventListener('dragover', function (e) {
    e.preventDefault();
    dropzone.classList.add('dragover');
  });

  dropzone.addEventListener('dragleave', function () {
    dropzone.classList.remove('dragover');
  });

  dropzone.addEventListener('drop', function (e) {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      inputArchivo.files = e.dataTransfer.files;
      mostrarArchivoSeleccionado(e.dataTransfer.files[0]);
    }
  });

  inputArchivo.addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
      mostrarArchivoSeleccionado(this.files[0]);
    }
  });

  btnRemoveFile.addEventListener('click', function (e) {
    e.stopPropagation();
    inputArchivo.value = '';
    dropzoneFileInfo.classList.add('d-none');
    dropzoneIdle.classList.remove('d-none');
  });

  function mostrarArchivoSeleccionado(file) {
    const validExts = ['xlsx', 'xls', 'csv'];
    const ext = file.name.split('.').pop().toLowerCase();
    if (!validExts.includes(ext)) {
      Swal.fire('Formato Inválido', 'Únicamente se permiten archivos de hoja de cálculo con extensión .xlsx, .xls o .csv', 'warning');
      inputArchivo.value = '';
      return;
    }

    if (file.size > 20 * 1024 * 1024) {
      Swal.fire('Archivo Muy Pesado', 'El archivo supera el límite de 20 MB.', 'warning');
      inputArchivo.value = '';
      return;
    }

    selectedFileName.textContent = file.name;
    selectedFileSize.textContent = formatBytes(file.size);
    dropzoneIdle.classList.add('d-none');
    dropzoneFileInfo.classList.remove('d-none');
  }

  function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  // 3. Reset del formulario
  btnReset.addEventListener('click', function () {
    inputArchivo.value = '';
    dropzoneFileInfo.classList.add('d-none');
    dropzoneIdle.classList.remove('d-none');
    cardResultados.classList.add('d-none');
  });

  btnImportarOtro.addEventListener('click', function () {
    btnReset.click();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // 4. Envío AJAX del formulario de importación
  formImportar.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!inputArchivo.files || inputArchivo.files.length === 0) {
      Swal.fire('Archivo Requerido', 'Por favor seleccione o arrastre un archivo Excel (.xlsx) o CSV (.csv) antes de continuar.', 'warning');
      return;
    }

    const fd = new FormData(formImportar);
    if (!fd.has('csrf')) {
      fd.append('csrf', CSRF_TOKEN);
    }

    modalProcesando.show();
    btnSubmit.disabled = true;

    fetch('admin/post_importar_acervo', {
      method: 'POST',
      body: fd,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(res => res.json())
    .then(data => {
      setTimeout(() => {
        modalProcesando.hide();
        btnSubmit.disabled = false;

        if (!data.status) {
          Swal.fire('Atención en la Importación', data.msg || 'No fue posible completar la importación.', 'error');
          return;
        }

        // Mostrar sección de resultados
        cardResultados.classList.remove('d-none');
        kpiTotalLeidas.textContent = data.resumen.total_leidas || 0;
        kpiInsertadas.textContent = data.resumen.insertadas || 0;
        kpiActualizadas.textContent = data.resumen.actualizadas || 0;
        kpiOmitidas.textContent = data.resumen.omitidas || 0;
        txtResumenMsg.textContent = data.msg || 'Proceso finalizado.';

        // Renderizar tabla de errores/advertencias si existen
        tbodyErrores.innerHTML = '';
        const filasInconsistencias = [];

        if (Array.isArray(data.detalle_errores)) {
          data.detalle_errores.forEach(err => {
            filasInconsistencias.push({
              fila: err.fila,
              codigo: err.codigo,
              tipo: 'Error',
              clase: 'badge bg-danger',
              desc: err.error
            });
          });
        }

        if (Array.isArray(data.detalle_advertencias)) {
          data.detalle_advertencias.forEach(adv => {
            filasInconsistencias.push({
              fila: adv.fila,
              codigo: adv.codigo,
              tipo: 'Aviso',
              clase: 'badge bg-warning text-dark',
              desc: adv.msg
            });
          });
        }

        if (filasInconsistencias.length > 0) {
          seccionErroresTabla.classList.remove('d-none');
          countErrores.textContent = filasInconsistencias.length;

          filasInconsistencias.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td class="text-center fw-bold">${item.fila}</td>
              <td class="fw-semibold text-dark">${escapeHtml(item.codigo || '—')}</td>
              <td><span class="${item.clase}">${item.tipo}</span></td>
              <td class="text-secondary">${escapeHtml(item.desc)}</td>
            `;
            tbodyErrores.appendChild(tr);
          });
        } else {
          seccionErroresTabla.classList.add('d-none');
        }

        // Notificación de éxito
        Swal.fire({
          icon: 'success',
          title: 'Importación Finalizada',
          text: data.msg,
          confirmButtonColor: '#6B1D2F'
        });

        // Scroll suave a resultados
        cardResultados.scrollIntoView({ behavior: 'smooth' });

      }, 500);
    })
    .catch(err => {
      modalProcesando.hide();
      btnSubmit.disabled = false;
      Swal.fire('Error de Conexión', 'Ocurrió un fallo en la comunicación con el servidor: ' + err.message, 'error');
    });
  });

  function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});
</script>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
