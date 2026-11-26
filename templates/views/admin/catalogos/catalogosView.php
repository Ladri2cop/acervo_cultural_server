<?php require_once INCLUDES . 'admin/dashboardTop.php'; 

// Normalización a arrays para acceso uniforme y robustez
$catalogos       = json_decode(json_encode($d->catalogos), true);
$config_activa   = json_decode(json_encode($d->config_activa), true);
$estadisticas    = json_decode(json_encode($d->estadisticas), true);
$items           = json_decode(json_encode($d->items), true);
$categorias      = json_decode(json_encode($d->categorias ?? []), true);
$catalogo_activo = (string)$d->catalogo_activo;
$hasCategoria    = isset($config_activa['fields']['categoria']);

function getCategoriaBadgeClass($cat) {
  $c = mb_strtolower(trim($cat ?? ''));
  if (strpos($c, 'pict') !== false) return 'badge-cat-pictorico';
  if (strpos($c, 'popular') !== false) return 'badge-cat-popular';
  if (strpos($c, 'natural') !== false) return 'badge-cat-natural';
  if (strpos($c, 'arqueo') !== false || strpos($c, 'decorativa') !== false || strpos($c, 'manufactura') !== false) return 'badge-cat-arqueo';
  if (strpos($c, 'numis') !== false) return 'badge-cat-numis';
  if (strpos($c, 'bidi') !== false) return 'badge-cat-bidi';
  if (strpos($c, 'tridi') !== false) return 'badge-cat-tridi';
  return 'badge-cat-default';
}
?>

<!-- Estilos específicos para la gestión de catálogos y categorías -->
<style>
  .catalogo-nav-item {
    transition: all 0.2s ease-in-out;
    border-left: 4px solid transparent !important;
    cursor: pointer;
  }
  .catalogo-nav-item:hover {
    background-color: #f8f9fc !important;
    transform: translateX(3px);
  }
  .catalogo-nav-item.active {
    background-color: #f0f4ff !important;
    color: #4e73df !important;
    font-weight: 600;
    border-left-color: #4e73df !important;
  }
  .badge-count {
    background-color: #f1f5f9 !important;
    color: #000000 !important;
    font-weight: 700 !important;
    border: 1px solid #cbd5e1 !important;
    font-size: 0.82rem;
    padding: 0.3em 0.65em;
  }
  .catalogo-nav-item:hover .badge-count {
    background-color: #e2e8f0 !important;
    color: #000000 !important;
  }
  .catalogo-nav-item.active .badge-count {
    background-color: #4e73df !important;
    color: #ffffff !important;
    border-color: #4e73df !important;
  }
  .btn-cat-pill .badge {
    color: #000000 !important;
    background-color: #e2e8f0;
  }
  .btn-cat-pill.active .badge {
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.3);
  }
  .table-catalogo th {
    background-color: #f8f9fc;
    color: #4e73df;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-top: none;
  }
  .badge-status-active {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
    font-weight: 600;
    padding: 0.35em 0.7em;
  }
  .badge-status-deleted {
    background-color: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-weight: 600;
    padding: 0.35em 0.7em;
  }
  .badge-cat-pictorico {
    background-color: #e3f2fd;
    color: #1565c0;
    border: 1px solid #bbdefb;
  }
  .badge-cat-popular {
    background-color: #fce4ec;
    color: #c2185b;
    border: 1px solid #f8bbd0;
  }
  .badge-cat-natural {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
  }
  .badge-cat-arqueo {
    background-color: #fff3e0;
    color: #e65100;
    border: 1px solid #ffe0b2;
  }
  .badge-cat-numis {
    background-color: #ede7f6;
    color: #512da8;
    border: 1px solid #d1c4e9;
  }
  .badge-cat-bidi {
    background-color: #e0f2f1;
    color: #00695c;
    border: 1px solid #b2dfdb;
  }
  .badge-cat-tridi {
    background-color: #fbe9e7;
    color: #d84315;
    border: 1px solid #ffccbc;
  }
  .badge-cat-default {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
  }
  .btn-action-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 0.95rem;
  }
  .card-header-catalogo {
    background: linear-gradient(135deg, #f8f9fc 0%, #edf2f7 100%);
    border-bottom: 1px solid #e3e6f0;
  }
  .search-catalogo-box {
    position: relative;
  }
  .search-catalogo-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
  }
  .search-catalogo-box input {
    padding-left: 36px;
  }
  .btn-cat-pill {
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.15s ease;
  }
  .btn-cat-pill.active {
    background-color: #4e73df !important;
    color: #fff !important;
    border-color: #4e73df !important;
    box-shadow: 0 2px 4px rgba(78, 115, 223, 0.25);
  }
</style>

<div class="container-fluid py-3">

  <!-- Encabezado y Acciones -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <p class="text-muted small mb-0">
        Administración centralizada, alta, modificación y control de estatus de los catálogos del patrimonio cultural.
      </p>
    </div>
    <div class="mt-2 mt-md-0 d-flex gap-2">
      <button type="button" class="btn btn-primary shadow-sm" id="btnNuevoItem">
        <i class='bx bx-plus-circle mr-1'></i> <span id="btnNuevoTexto">Nuevo Elemento</span>
      </button>
    </div>
  </div>

  <div class="row">
    <!-- Navegación de Catálogos (Columna Izquierda) -->
    <div class="col-xl-3 col-lg-4 mb-4">
      <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-header py-3 bg-white border-bottom">
          <h6 class="m-0 font-weight-bold text-gray-800 d-flex align-items-center justify-content-between">
            <span><i class='bx bx-list-ul mr-1 text-primary'></i> Catálogos Oficiales</span>
            <span class="badge badge-primary badge-pill"><?php echo count($catalogos); ?></span>
          </h6>
        </div>
        <div class="list-group list-group-flush" id="listadoCatalogos">
          <?php foreach ($catalogos as $slug => $cat): 
            $isActive = ($slug === $catalogo_activo);
            $statsCat = $estadisticas[$slug] ?? ['total' => 0, 'activos' => 0];
          ?>
            <a href="<?php echo get_base_url(); ?>admin/catalogos/<?php echo $slug; ?>" 
               class="list-group-item list-group-item-action catalogo-nav-item d-flex align-items-center justify-content-between py-3 <?php echo $isActive ? 'active' : ''; ?>"
               data-slug="<?php echo $slug; ?>">
              <div class="d-flex align-items-center">
                <i class="<?php echo $cat['icon']; ?> mr-2 fa-lg <?php echo $isActive ? 'text-primary' : 'text-muted'; ?>"></i>
                <div>
                  <div class="font-weight-bold <?php echo $isActive ? 'text-primary' : 'text-gray-800'; ?>" style="font-size: 0.95rem;">
                    <?php echo $cat['title']; ?>
                  </div>
                  <small class="text-muted"><?php echo $statsCat['activos']; ?> activos / <?php echo $statsCat['total']; ?> total</small>
                </div>
              </div>
              <span class="badge badge-pill badge-count"><?php echo $statsCat['activos']; ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Tarjeta informativa de integridad -->
      <div class="card shadow-sm border-0 rounded-lg mt-3 bg-light">
        <div class="card-body p-3">
          <div class="d-flex align-items-start">
            <i class='bx bx-shield-quarter text-primary bx-sm mr-2 mt-1'></i>
            <div>
              <h6 class="font-weight-bold text-gray-800 mb-1" style="font-size: 0.85rem;">Borrado Lógico y Preservación</h6>
              <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                La eliminación opera mediante baja lógica (estatus 0): ningún registro se elimina físicamente de la base de datos, garantizando la preservación histórica y la integridad relacional de todas las piezas.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel Principal del Catálogo (Columna Derecha) -->
    <div class="col-xl-9 col-lg-8">
      <div class="card shadow-sm border-0 rounded-lg">
        
        <!-- Encabezado del catálogo seleccionado -->
        <div class="card-header card-header-catalogo py-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center">
              <div class="bg-white p-2 rounded shadow-sm mr-3 text-primary">
                <i class="<?php echo $config_activa['icon']; ?> fa-2x"></i>
              </div>
              <div>
                <h5 class="m-0 font-weight-bold text-gray-800" id="currentCatalogoTitle">
                  <?php echo $config_activa['title']; ?>
                </h5>
                <small class="text-muted" id="currentCatalogoDesc">
                  <?php echo $config_activa['description']; ?>
                </small>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
              <span class="badge badge-success px-2 py-1 font-weight-bold" id="kpiActivos">
                <?php echo $estadisticas[$catalogo_activo]['activos'] ?? 0; ?> Activos
              </span>
              <span class="badge badge-danger px-2 py-1 font-weight-bold" id="kpiEliminados">
                <?php echo $estadisticas[$catalogo_activo]['inactivos'] ?? 0; ?> Eliminados
              </span>
            </div>
          </div>
        </div>

        <!-- Filtros y Herramientas -->
        <div class="card-body border-bottom py-3 bg-white">
          <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
              <div class="search-catalogo-box">
                <i class='bx bx-search'></i>
                <input type="text" class="form-control form-control-sm rounded-pill" id="inputBuscarItem" placeholder="Buscar en <?php echo strtolower($config_activa['title']); ?>...">
              </div>
            </div>
            <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2">
              <label class="small text-muted mb-0 mr-2">Estatus:</label>
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary active btn-filtro-st" data-status="todos">Todos</button>
                <button type="button" class="btn btn-outline-success btn-filtro-st" data-status="1">Activos</button>
                <button type="button" class="btn btn-outline-danger btn-filtro-st" data-status="0">Eliminados</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Barra de Filtro de Categorías / Disciplinas (para Materiales, Técnicas, Obras) -->
        <?php if ($hasCategoria && !empty($categorias)): ?>
          <div class="px-3 pt-3 pb-2 bg-light border-bottom">
            <div class="d-flex flex-wrap align-items-center" id="contenedorPillsCategorias">
              <span class="small font-weight-bold text-gray-700 mr-2 mb-2">
                <i class='bx bx-category mr-1 text-primary'></i> Clasificación:
              </span>
              <button type="button" class="btn btn-sm btn-outline-primary active btn-cat-pill mr-2 mb-2 rounded-pill" data-cat="todas">
                Todas <span class="badge badge-light border ml-1"><?php echo count($items); ?></span>
              </button>
              <?php foreach ($categorias as $catRow): 
                $catName = htmlspecialchars($catRow['categoria']);
                $catTotal = (int)$catRow['total'];
              ?>
                <button type="button" class="btn btn-sm btn-outline-secondary btn-cat-pill mr-2 mb-2 rounded-pill" data-cat="<?php echo strtolower($catName); ?>">
                  <?php echo $catName; ?> <span class="badge badge-light border ml-1"><?php echo $catTotal; ?></span>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Tabla de Registros -->
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-catalogo align-middle mb-0" id="tablaCatalogo">
              <thead>
                <tr>
                  <th style="width: 60px;" class="text-center">#</th>
                  <th><?php echo $config_activa['fields'][$config_activa['name_field']]['label']; ?></th>
                  
                  <?php if ($hasCategoria): ?>
                    <th style="min-width: 180px;">Categoría / Clasificación</th>
                  <?php endif; ?>

                  <?php if ($catalogo_activo === 'museos'): ?>
                    <th>Ubicación</th>
                    <th>Responsable</th>
                    <th>Teléfono</th>
                  <?php elseif ($catalogo_activo === 'epocas_periodos'): ?>
                    <th>Siglo</th>
                  <?php endif; ?>
                  
                  <th style="width: 120px;" class="text-center">Estatus</th>
                  <th style="width: 140px;" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody id="tbodyCatalogo">
                <?php if (empty($items)): ?>
                  <tr id="rowEmpty">
                    <td colspan="<?php echo ($catalogo_activo === 'museos') ? '6' : (($hasCategoria) ? '4' : '3'); ?>" class="text-center py-5 text-muted">
                      <i class='bx bx-folder-open fa-3x mb-2 text-gray-300 d-block'></i>
                      No hay registros capturados en este catálogo.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php $idx = 1; foreach ($items as $item): 
                    $itemId = (int)$item[$config_activa['pk']];
                    $nombre = htmlspecialchars($item[$config_activa['name_field']] ?? '', ENT_QUOTES, 'UTF-8');
                    $isActivo = ((int)($item['status'] ?? 1) === 1);
                    $catVal = htmlspecialchars($item['categoria'] ?? '', ENT_QUOTES, 'UTF-8');
                  ?>
                    <tr class="catalogo-row" 
                        data-id="<?php echo $itemId; ?>" 
                        data-nombre="<?php echo strtolower($nombre); ?>"
                        data-categoria="<?php echo strtolower($catVal); ?>"
                        data-status="<?php echo $isActivo ? '1' : '0'; ?>">
                      <td class="text-center text-muted font-weight-bold"><?php echo $idx++; ?></td>
                      <td>
                        <span class="font-weight-bold text-gray-800"><?php echo $nombre; ?></span>
                      </td>

                      <?php if ($hasCategoria): ?>
                        <td>
                          <?php if (!empty($catVal)): ?>
                            <span class="badge badge-pill <?php echo getCategoriaBadgeClass($catVal); ?> font-weight-bold px-2 py-1">
                              <i class='bx bx-tag-alt mr-1'></i> <?php echo $catVal; ?>
                            </span>
                          <?php else: ?>
                            <span class="text-muted small">—</span>
                          <?php endif; ?>
                        </td>
                      <?php endif; ?>

                      <?php if ($catalogo_activo === 'museos'): ?>
                        <td class="small text-muted"><?php echo htmlspecialchars($item['ubicacion'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="small text-muted"><?php echo htmlspecialchars($item['responsable_museo'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="small text-muted"><?php echo htmlspecialchars($item['telefono'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                      <?php elseif ($catalogo_activo === 'epocas_periodos'): ?>
                        <td class="small text-muted"><?php echo htmlspecialchars($item['siglo'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                      <?php endif; ?>

                      <td class="text-center">
                        <?php if ($isActivo): ?>
                          <span class="badge badge-status-active rounded-pill">
                            <i class='bx bx-check-circle mr-1'></i> Activo
                          </span>
                        <?php else: ?>
                          <span class="badge badge-status-deleted rounded-pill">
                            <i class='bx bx-trash mr-1'></i> Eliminado
                          </span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                          <!-- Botón Editar -->
                          <button type="button" class="btn btn-outline-primary btn-action-icon btn-edit-item" 
                                  data-id="<?php echo $itemId; ?>" title="Editar">
                            <i class='bx bx-edit-alt'></i>
                          </button>
                          
                          <?php if ($isActivo): ?>
                            <!-- Botón Eliminar (Baja Lógica: status 1 -> 0) -->
                            <button type="button" class="btn btn-outline-danger btn-action-icon btn-delete-item" 
                                    data-id="<?php echo $itemId; ?>" 
                                    data-nombre="<?php echo $nombre; ?>" title="Eliminar (Baja Lógica)">
                              <i class='bx bx-trash'></i>
                            </button>
                          <?php else: ?>
                            <!-- Botón Restaurar (status 0 -> 1) -->
                            <button type="button" class="btn btn-outline-success btn-action-icon btn-restore-item" 
                                    data-id="<?php echo $itemId; ?>" 
                                    data-nombre="<?php echo $nombre; ?>" title="Restaurar registro">
                              <i class='bx bx-undo'></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer con estadísticas del catálogo -->
        <div class="card-footer bg-white border-top py-3 text-muted small d-flex justify-content-between align-items-center">
          <div>
            Mostrando <span class="font-weight-bold" id="totalFiltrados"><?php echo count($items); ?></span> registros
          </div>
          <div>
            SIPAC · Gestión Institucional de Bienes Culturales
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- Modal para Crear / Editar Registro de Catálogo -->
<div class="modal fade" id="modalCatalogoItem" tabindex="-1" role="dialog" aria-labelledby="modalItemTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold d-flex align-items-center" id="modalItemTitle">
          <i class='bx bx-edit-alt mr-2'></i> <span id="modalItemHeader">Nuevo Registro</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formCatalogoItem">
        <?php echo insert_inputs(); ?>
        <input type="hidden" name="catalogo" id="formCatalogoSlug" value="<?php echo $catalogo_activo; ?>">
        <input type="hidden" name="id" id="formItemId" value="">

        <div class="modal-body p-4">
          <!-- Campo Principal: Nombre -->
          <div class="form-group mb-3">
            <label for="inputItemNombre" class="font-weight-bold text-gray-800" id="labelItemNombre">
              <?php echo $config_activa['fields'][$config_activa['name_field']]['label']; ?> <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="inputItemNombre" 
                   name="<?php echo $config_activa['name_field']; ?>" 
                   placeholder="<?php echo $config_activa['fields'][$config_activa['name_field']]['placeholder']; ?>" 
                   required autocomplete="off">
          </div>

          <!-- Campo de Categoría (Para Materiales, Técnicas, Tipos de Obra) -->
          <?php if ($hasCategoria): ?>
            <div id="fieldCategoria" class="form-group mb-3">
              <label for="inputItemCategoria" class="font-weight-bold text-gray-800">
                <?php echo $config_activa['fields']['categoria']['label']; ?> <span class="text-danger">*</span>
              </label>
              <select class="form-control" id="inputItemCategoria" name="categoria" required>
                <option value="">-- Seleccione una opción --</option>
                <?php foreach ($config_activa['fields']['categoria']['options'] as $optVal => $optLabel): ?>
                  <option value="<?php echo htmlspecialchars($optVal); ?>"><?php echo htmlspecialchars($optLabel); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <!-- Campos adicionales para Museos -->
          <div id="fieldsMuseo" style="<?php echo ($catalogo_activo === 'museos') ? '' : 'display: none;'; ?>">
            <div class="form-group mb-3">
              <label for="inputUbicacion" class="font-weight-bold text-gray-800">Ubicación / Domicilio</label>
              <textarea class="form-control" id="inputUbicacion" name="ubicacion" rows="2" placeholder="Ej: Jesús Reyes Heroles 302, San Buenaventura, Toluca"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 form-group mb-3">
                <label for="inputResponsable" class="font-weight-bold text-gray-800">Responsable / Titular</label>
                <input type="text" class="form-control" id="inputResponsable" name="responsable_museo" placeholder="Ej: Mtro. Director del Recinto">
              </div>
              <div class="col-md-6 form-group mb-3">
                <label for="inputTelefono" class="font-weight-bold text-gray-800">Teléfono</label>
                <input type="text" class="form-control" id="inputTelefono" name="telefono" placeholder="Ej: 7221234567" maxlength="15">
              </div>
            </div>
          </div>

          <!-- Campos adicionales para Épocas -->
          <div id="fieldsEpoca" style="<?php echo ($catalogo_activo === 'epocas_periodos') ? '' : 'display: none;'; ?>">
            <div class="form-group mb-0">
              <label for="inputSiglo" class="font-weight-bold text-gray-800">Siglo (Opcional)</label>
              <input type="text" class="form-control" id="inputSiglo" name="siglo" placeholder="Ej: XIX, XX, Posclásico Tardío">
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" id="btnCancelarModal">Cancelar</button>
          <button type="submit" class="btn btn-primary font-weight-bold" id="btnGuardarItem">
            <i class='bx bx-save mr-1'></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const CSRF_TOKEN = '<?php echo CSRF_TOKEN; ?>';
  const catalogoActivo = '<?php echo $catalogo_activo; ?>';
  const configActiva = <?php echo json_encode($config_activa); ?>;
  const hasCategoria = <?php echo $hasCategoria ? 'true' : 'false'; ?>;

  const btnNuevoItem = document.getElementById('btnNuevoItem');
  const btnNuevoTexto = document.getElementById('btnNuevoTexto');
  const modalElement = document.getElementById('modalCatalogoItem');
  const modalItemTitle = document.getElementById('modalItemHeader');
  const formCatalogoItem = document.getElementById('formCatalogoItem');
  const formItemId = document.getElementById('formItemId');
  const inputItemNombre = document.getElementById('inputItemNombre');
  const inputItemCategoria = document.getElementById('inputItemCategoria');
  const inputBuscarItem = document.getElementById('inputBuscarItem');
  const totalFiltrados = document.getElementById('totalFiltrados');

  if (btnNuevoTexto) {
    btnNuevoTexto.textContent = 'Nuevo ' + configActiva.singular;
  }

  // Funciones robustas para abrir y cerrar modal (Bootstrap 5 y jQuery)
  function abrirModal() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(modalElement).show();
    } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
      $(modalElement).modal('show');
    }
  }

  function cerrarModal() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      const inst = bootstrap.Modal.getInstance(modalElement) || bootstrap.Modal.getOrCreateInstance(modalElement);
      if (inst) inst.hide();
    }
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
      $(modalElement).modal('hide');
    }
  }

  // Escuchadores explícitos de cierre en los botones cancelar y cruz
  document.querySelectorAll('#modalCatalogoItem [data-bs-dismiss="modal"], #modalCatalogoItem [data-dismiss="modal"], #btnCancelarModal').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      cerrarModal();
    });
  });

  // Abrir modal para NUEVO registro
  btnNuevoItem.addEventListener('click', function () {
    formCatalogoItem.reset();
    formItemId.value = '';
    if (inputItemCategoria) inputItemCategoria.value = '';
    modalItemTitle.textContent = 'Nuevo ' + configActiva.singular;
    abrirModal();
  });

  // Abrir modal para EDITAR registro
  document.querySelectorAll('.btn-edit-item').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.getAttribute('data-id');
      cargarItemParaEditar(id);
    });
  });

  function cargarItemParaEditar(id) {
    fetch('admin/get_catalogo_item?catalogo=' + encodeURIComponent(catalogoActivo) + '&id=' + id)
      .then(res => res.json())
      .then(data => {
        if (!data.status) {
          Swal.fire('Error', data.message || 'No fue posible consultar el registro.', 'error');
          return;
        }

        formCatalogoItem.reset();
        formItemId.value = id;
        modalItemTitle.textContent = 'Editar ' + configActiva.singular;

        const item = data.item;
        inputItemNombre.value = item[configActiva.name_field] || '';

        if (hasCategoria && inputItemCategoria) {
          inputItemCategoria.value = item.categoria || '';
        }

        if (catalogoActivo === 'museos') {
          document.getElementById('inputUbicacion').value = item.ubicacion || '';
          document.getElementById('inputResponsable').value = item.responsable_museo || '';
          document.getElementById('inputTelefono').value = item.telefono || '';
        } else if (catalogoActivo === 'epocas_periodos') {
          document.getElementById('inputSiglo').value = item.siglo || '';
        }

        abrirModal();
      })
      .catch(err => {
        Swal.fire('Error', 'Fallo de conexión al cargar datos.', 'error');
      });
  }

  // Enviar formulario (Crear o Actualizar)
  formCatalogoItem.addEventListener('submit', function (e) {
    e.preventDefault();

    const nombre = inputItemNombre.value.trim();
    if (!nombre) {
      Swal.fire('Atención', 'El campo de nombre es obligatorio.', 'warning');
      return;
    }

    if (hasCategoria && inputItemCategoria && !inputItemCategoria.value.trim()) {
      Swal.fire('Atención', 'Por favor selecciona la categoría correspondiente.', 'warning');
      return;
    }

    const formData = new FormData(formCatalogoItem);

    const btnSubmit = document.getElementById('btnGuardarItem');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = "<i class='bx bx-loader-alt bx-spin mr-1'></i> Guardando...";

    fetch('admin/post_guardar_catalogo', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = "<i class='bx bx-save mr-1'></i> Guardar";

        if (data.status) {
          cerrarModal();
          Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire('Error', data.message || 'No fue posible guardar el registro.', 'error');
        }
      })
      .catch(err => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = "<i class='bx bx-save mr-1'></i> Guardar";
        Swal.fire('Error', 'Ocurrió un error en la solicitud.', 'error');
      });
  });

  // Eliminar Registro (Baja Lógica: status 1 -> 0)
  document.querySelectorAll('.btn-delete-item').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.getAttribute('data-id');
      const nombre = this.getAttribute('data-nombre');

      Swal.fire({
        title: '¿Eliminar del catálogo?',
        html: `¿Estás seguro de dar de baja a <b>"${nombre}"</b>?<br><small class='text-muted'>Esta acción realiza un <b>borrado lógico</b> (estatus 0). El registro se preservará en la base de datos para no afectar las piezas vinculadas y podrá restaurarse en cualquier momento.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          const fd = new FormData();
          fd.append('csrf', CSRF_TOKEN);
          fd.append('catalogo', catalogoActivo);
          fd.append('id', id);

          fetch('admin/post_eliminar_catalogo', {
            method: 'POST',
            body: fd
          })
            .then(res => res.json())
            .then(data => {
              if (data.status) {
                Swal.fire({
                  icon: 'success',
                  title: 'Baja realizada',
                  text: data.message,
                  timer: 1400,
                  showConfirmButton: false
                }).then(() => {
                  window.location.reload();
                });
              } else {
                Swal.fire({
                  icon: 'warning',
                  title: 'No se pudo eliminar',
                  text: data.message,
                  confirmButtonColor: '#4e73df',
                  confirmButtonText: 'Entendido'
                });
              }
            })
            .catch(() => {
              Swal.fire('Error', 'Fallo de conexión.', 'error');
            });
        }
      });
    });
  });

  // Restaurar Registro (status 0 -> 1)
  document.querySelectorAll('.btn-restore-item').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.getAttribute('data-id');
      const nombre = this.getAttribute('data-nombre');

      Swal.fire({
        title: '¿Restaurar registro?',
        html: `¿Deseas reactivar <b>"${nombre}"</b> en el catálogo oficial?<br><small class='text-muted'>El registro volverá a estar <b>Activo</b> (estatus 1) y disponible para nuevas capturas en el sistema.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1cc88a',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          const fd = new FormData();
          fd.append('csrf', CSRF_TOKEN);
          fd.append('catalogo', catalogoActivo);
          fd.append('id', id);

          fetch('admin/post_restaurar_catalogo', {
            method: 'POST',
            body: fd
          })
            .then(res => res.json())
            .then(data => {
              if (data.status) {
                Swal.fire({
                  icon: 'success',
                  title: '¡Restaurado!',
                  text: data.message,
                  timer: 1400,
                  showConfirmButton: false
                }).then(() => {
                  window.location.reload();
                });
              } else {
                Swal.fire('Error', data.message || 'No se pudo restaurar el registro.', 'error');
              }
            })
            .catch(() => {
              Swal.fire('Error', 'Fallo de conexión al restaurar registro.', 'error');
            });
        }
      });
    });
  });

  // Filtros combinados: Búsqueda en vivo + Estatus + Categoría
  let filtroEstatusActual = 'todos';
  let filtroCategoriaActual = 'todas';

  // Filtro por estatus
  document.querySelectorAll('.btn-filtro-st').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.btn-filtro-st').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      filtroEstatusActual = this.getAttribute('data-status');
      filtrarTabla();
    });
  });

  // Filtro por píldoras de categoría
  document.querySelectorAll('.btn-cat-pill').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.btn-cat-pill').forEach(b => {
        b.classList.remove('active', 'btn-outline-primary');
        b.classList.add('btn-outline-secondary');
      });
      this.classList.remove('btn-outline-secondary');
      this.classList.add('active', 'btn-outline-primary');
      filtroCategoriaActual = this.getAttribute('data-cat');
      filtrarTabla();
    });
  });

  inputBuscarItem.addEventListener('input', filtrarTabla);

  function filtrarTabla() {
    const query = inputBuscarItem.value.trim().toLowerCase();
    const rows = document.querySelectorAll('.catalogo-row');
    let visibles = 0;

    rows.forEach(row => {
      const nombre = row.getAttribute('data-nombre') || '';
      const status = row.getAttribute('data-status') || '';
      const categoria = row.getAttribute('data-categoria') || '';

      const coincideTexto = (query === '' || nombre.includes(query) || categoria.includes(query));
      const coincideStatus = (filtroEstatusActual === 'todos' || status === filtroEstatusActual);
      const coincideCategoria = (filtroCategoriaActual === 'todas' || categoria === filtroCategoriaActual);

      if (coincideTexto && coincideStatus && coincideCategoria) {
        row.style.display = '';
        visibles++;
      } else {
        row.style.display = 'none';
      }
    });

    if (totalFiltrados) {
      totalFiltrados.textContent = visibles;
    }
  }
});
</script>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
