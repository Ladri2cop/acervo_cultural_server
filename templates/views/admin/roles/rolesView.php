<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<?php
// Mapeo y categorización estructurada de permisos SIPAC
$categorias_definicion = [
  'general' => [
    'titulo' => 'Acervo General',
    'icono'  => 'fas fa-landmark text-primary',
    'badge'  => 'bg-primary text-white',
    'slugs'  => ['ver_acervo_general', 'registrar_acervo_general', 'editar_acervo_general', 'eliminar_acervo_general']
  ],
  'arqueologico' => [
    'titulo' => 'Acervo Arqueológico',
    'icono'  => 'fas fa-monument text-warning',
    'badge'  => 'bg-warning text-dark',
    'slugs'  => ['ver_acervo_arqueologico', 'registrar_acervo_arqueologico', 'editar_acervo_arqueologico', 'eliminar_acervo_arqueologico']
  ],
  'numismatica' => [
    'titulo' => 'Acervo Numismático',
    'icono'  => 'fas fa-coins text-success',
    'badge'  => 'bg-success text-white',
    'slugs'  => ['ver_acervo_numismatica', 'registrar_acervo_numismatica', 'editar_acervo_numismatica', 'eliminar_acervo_numismatica']
  ],
  'revision' => [
    'titulo' => 'Revisión y Dictamen',
    'icono'  => 'fas fa-clipboard-check text-info',
    'badge'  => 'bg-info text-white',
    'slugs'  => ['ver_revision_acervo', 'dictaminar_acervo', 'ver_mis_registros']
  ],
  'prestamos' => [
    'titulo' => 'Préstamos y Transferencias',
    'icono'  => 'fas fa-exchange-alt text-danger',
    'badge'  => 'bg-danger text-white',
    'slugs'  => ['ver_prestamos_transferencias', 'registrar_prestamo_transferencia', 'dictaminar_prestamo_transferencia', 'devolver_prestamo', 'cancelar_prestamo_transferencia']
  ],
  'exportaciones' => [
    'titulo' => 'Reportes y Exportación',
    'icono'  => 'fas fa-file-export text-secondary',
    'badge'  => 'bg-secondary text-white',
    'slugs'  => ['exportar_excel', 'exportar_pdf']
  ],
  'administracion' => [
    'titulo' => 'Gestión y Auditoría',
    'icono'  => 'fas fa-shield-alt text-dark',
    'badge'  => 'bg-dark text-white',
    'slugs'  => ['admin-access', 'gestion_usuarios', 'gestion_roles', 'gestion_catalogos', 'ver_auditoria']
  ]
];

$permisos_indexados = [];
if (!empty($d->permisos)) {
  foreach ($d->permisos as $p) {
    $pSlug = is_object($p) ? $p->slug : $p['slug'];
    $permisos_indexados[$pSlug] = $p;
  }
}

$grupos_render = [];
$slugs_utilizados = [];

foreach ($categorias_definicion as $cat_key => $cat_info) {
  $items = [];
  foreach ($cat_info['slugs'] as $slug) {
    if (isset($permisos_indexados[$slug])) {
      $items[] = $permisos_indexados[$slug];
      $slugs_utilizados[$slug] = true;
    }
  }
  if (!empty($items)) {
    $grupos_render[$cat_key] = [
      'info' => $cat_info,
      'items' => $items
    ];
  }
}

$otros_items = [];
foreach ($permisos_indexados as $slug => $p) {
  if (!isset($slugs_utilizados[$slug])) {
    $otros_items[] = $p;
  }
}
if (!empty($otros_items)) {
  $grupos_render['otros'] = [
    'info' => [
      'titulo' => 'Otros Permisos',
      'icono'  => 'fas fa-key text-muted',
      'badge'  => 'bg-secondary text-white',
      'slugs'  => []
    ],
    'items' => $otros_items
  ];
}
?>

<style>
  .border-bottom-dashed {
    border-bottom: 1px dashed #e3e6f0;
  }
  .border-bottom-dashed:last-child {
    border-bottom: none;
  }
  .perm-group-card {
    transition: all 0.2s ease-in-out;
  }
  .perm-group-card:hover {
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08) !important;
  }
</style>

<div class="row">
  <!-- Formulario para agregar rol -->
  <div class="col-12 col-md-5 col-lg-5 col-xl-5">
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-user-shield me-2"></i>Crear Nuevo Rol</h6>
      </div>
      <div class="card-body">
        <form action="admin/post_roles" method="post">
          <?php echo insert_inputs(); ?>

          <div class="mb-3">
            <label for="nombre" class="form-label font-weight-bold">Nombre del Rol <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Responsable de Acervo Arqueológico" required>
          </div>

          <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <label class="form-label font-weight-bold mb-0">Permisos Asignados</label>
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" id="btn_sel_all_add" title="Seleccionar todos los permisos">
                  <i class="fas fa-check-square me-1"></i> Todos
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" id="btn_desel_all_add" title="Deseleccionar todos los permisos">
                  <i class="far fa-square me-1"></i> Ninguno
                </button>
              </div>
            </div>

            <div class="border rounded p-3 bg-light" style="max-height: 480px; overflow-y: auto;">
              <?php if (!empty($grupos_render)): ?>
                <?php foreach ($grupos_render as $gKey => $grupo): ?>
                  <div class="card perm-group-card mb-3 border-0 shadow-sm">
                    <div class="card-header py-2 px-3 bg-white d-flex align-items-center justify-content-between border-bottom">
                      <span class="font-weight-bold text-dark small">
                        <i class="<?php echo $grupo['info']['icono']; ?> me-1"></i> <?php echo $grupo['info']['titulo']; ?>
                        <span class="badge rounded-pill <?php echo $grupo['info']['badge']; ?> ms-1" style="font-size: 0.7rem;"><?php echo count($grupo['items']); ?></span>
                      </span>
                      <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none btn-toggle-cat" data-prefix="perm_add_" data-cat="<?php echo $gKey; ?>" title="Marcar / Desmarcar este grupo">
                        <i class="fas fa-tasks small"></i>
                      </button>
                    </div>
                    <div class="card-body py-2 px-3 bg-white">
                      <?php foreach ($grupo['items'] as $p): ?>
                        <?php 
                          $pId = is_object($p) ? $p->id : $p['id'];
                          $pNombre = is_object($p) ? $p->nombre : $p['nombre'];
                          $pDesc = is_object($p) ? $p->descripcion : $p['descripcion'];
                        ?>
                        <div class="form-check py-1 border-bottom-dashed">
                          <input class="form-check-input perm-add-check perm-cat-perm_add_<?php echo $gKey; ?>" type="checkbox" name="permisos[]" value="<?php echo $pId; ?>" id="perm_add_<?php echo $pId; ?>">
                          <label class="form-check-label text-dark small" for="perm_add_<?php echo $pId; ?>" style="cursor: pointer;">
                            <strong><?php echo htmlspecialchars($pNombre); ?></strong>
                            <?php if (!empty($pDesc)): ?>
                              <br><span class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($pDesc); ?></span>
                            <?php endif; ?>
                          </label>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted m-0">No hay permisos registrados en el sistema.</p>
              <?php endif; ?>
            </div>
          </div>

          <button class="btn btn-success btn-lg w-100" type="submit">
            <i class="fas fa-save me-1"></i> Guardar Rol
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabla de Roles existentes -->
  <div class="col-12 col-md-7 col-lg-7 col-xl-7">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-ul me-2"></i>Roles Registrados</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="min-height: 300px;">
          <table class="table table-hover table-striped mb-0 align-middle">
            <thead>
              <tr>
                <th>Rol</th>
                <th class="text-center">Permisos Asignados</th>
                <th class="text-center">Fecha de Creación</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($d->roles)): ?>
                <?php foreach ($d->roles as $r): ?>
                  <?php 
                    $rId = is_object($r) ? $r->id : $r['id'];
                    $rNombre = is_object($r) ? $r->nombre : $r['nombre'];
                    $rTotal = is_object($r) ? $r->total_permisos : $r['total_permisos'];
                    $rCreado = is_object($r) ? $r->creado : $r['creado'];
                  ?>
                  <tr>
                    <td><span class="font-weight-bold text-dark"><?php echo htmlspecialchars($rNombre); ?></span></td>
                    <td class="text-center">
                      <span class="badge bg-info text-white px-2 py-1"><?php echo $rTotal; ?> permiso(s)</span>
                    </td>
                    <td class="text-center text-muted"><?php echo !empty($rCreado) ? date('d/m/Y H:i', strtotime($rCreado)) : '-'; ?></td>
                    <td class="text-end">
                      <div class="dropdown">
                        <a class="btn btn-sm btn-secondary" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item btn-editar-rol" 
                               href="#" 
                               data-id="<?php echo $rId; ?>" 
                               data-nombre="<?php echo htmlspecialchars($rNombre); ?>" 
                               data-toggle="modal" 
                               data-target="#modalEditarRol">
                              <i class="fas fa-edit me-1 text-primary"></i> Editar rol y permisos
                            </a>
                          </li>
                          <?php if ($rId != 1): ?>
                            <li><a class="dropdown-item confirmar text-danger" href="<?php echo build_url(sprintf('admin/borrar-rol/%s', $rId)) ?>"><i class="fas fa-trash-alt me-1"></i> Borrar rol</a></li>
                          <?php endif; ?>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">No hay roles registrados en la base de datos.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Rol -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" role="dialog" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold" id="modalEditarRolLabel"><i class="fas fa-user-edit me-2"></i>Editar Rol y Permisos</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="admin/post_editar_rol" method="post">
        <?php echo insert_inputs(); ?>
        <input type="hidden" id="edit_role_id" name="id_role" value="">

        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_role_nombre" class="form-label font-weight-bold">Nombre del Rol <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_role_nombre" name="nombre" required>
          </div>

          <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <label class="form-label font-weight-bold mb-0">Permisos Asignados</label>
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" id="btn_sel_all_edit" title="Seleccionar todos los permisos">
                  <i class="fas fa-check-square me-1"></i> Todos
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" id="btn_desel_all_edit" title="Deseleccionar todos los permisos">
                  <i class="far fa-square me-1"></i> Ninguno
                </button>
              </div>
            </div>

            <div class="border rounded p-3 bg-light" style="max-height: 480px; overflow-y: auto;">
              <?php if (!empty($grupos_render)): ?>
                <?php foreach ($grupos_render as $gKey => $grupo): ?>
                  <div class="card perm-group-card mb-3 border-0 shadow-sm">
                    <div class="card-header py-2 px-3 bg-white d-flex align-items-center justify-content-between border-bottom">
                      <span class="font-weight-bold text-dark small">
                        <i class="<?php echo $grupo['info']['icono']; ?> me-1"></i> <?php echo $grupo['info']['titulo']; ?>
                        <span class="badge rounded-pill <?php echo $grupo['info']['badge']; ?> ms-1" style="font-size: 0.7rem;"><?php echo count($grupo['items']); ?></span>
                      </span>
                      <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none btn-toggle-cat" data-prefix="perm_edit_" data-cat="<?php echo $gKey; ?>" title="Marcar / Desmarcar este grupo">
                        <i class="fas fa-tasks small"></i>
                      </button>
                    </div>
                    <div class="card-body py-2 px-3 bg-white">
                      <?php foreach ($grupo['items'] as $p): ?>
                        <?php 
                          $pId = is_object($p) ? $p->id : $p['id'];
                          $pNombre = is_object($p) ? $p->nombre : $p['nombre'];
                          $pDesc = is_object($p) ? $p->descripcion : $p['descripcion'];
                        ?>
                        <div class="form-check py-1 border-bottom-dashed">
                          <input class="form-check-input edit-perm-check perm-cat-perm_edit_<?php echo $gKey; ?>" type="checkbox" name="permisos[]" value="<?php echo $pId; ?>" id="perm_edit_<?php echo $pId; ?>">
                          <label class="form-check-label text-dark small" for="perm_edit_<?php echo $pId; ?>" style="cursor: pointer;">
                            <strong><?php echo htmlspecialchars($pNombre); ?></strong>
                            <?php if (!empty($pDesc)): ?>
                              <br><span class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($pDesc); ?></span>
                            <?php endif; ?>
                          </label>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar / Deseleccionar todos en el formulario de creación
    $('#btn_sel_all_add').on('click', function(e) {
      e.preventDefault();
      $('.perm-add-check').prop('checked', true);
    });
    $('#btn_desel_all_add').on('click', function(e) {
      e.preventDefault();
      $('.perm-add-check').prop('checked', false);
    });

    // Seleccionar / Deseleccionar todos en el modal de edición
    $('#btn_sel_all_edit').on('click', function(e) {
      e.preventDefault();
      $('.edit-perm-check').prop('checked', true);
    });
    $('#btn_desel_all_edit').on('click', function(e) {
      e.preventDefault();
      $('.edit-perm-check').prop('checked', false);
    });

    // Toggle grupal por categoría
    $(document).on('click', '.btn-toggle-cat', function(e) {
      e.preventDefault();
      var prefix = $(this).data('prefix');
      var cat = $(this).data('cat');
      var checks = $('.perm-cat-' + prefix + cat);
      var anyUnchecked = checks.filter(':not(:checked)').length > 0;
      checks.prop('checked', anyUnchecked);
    });

    // Cargar permisos en modal de edición
    $(document).on('click', '.btn-editar-rol', function(e) {
      var id = $(this).data('id');
      var nombre = $(this).data('nombre');

      $('#edit_role_id').val(id);
      $('#edit_role_nombre').val(nombre);
      $('.edit-perm-check').prop('checked', false);

      // Cargar permisos actuales del rol por AJAX
      $.ajax({
        url: 'admin/get_permisos_rol/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.status === 200 && response.permisos) {
            response.permisos.forEach(function(permId) {
              $('#perm_edit_' + permId).prop('checked', true);
            });
          }
        }
      });
    });
  });
</script>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
