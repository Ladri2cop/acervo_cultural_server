<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <!-- Formulario para agregar rol -->
  <div class="col-12 col-md-5 col-lg-5 col-xl-4">
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-user-shield me-2"></i>Crear Nuevo Rol</h6>
      </div>
      <div class="card-body">
        <form action="admin/post_roles" method="post">
          <?php echo insert_inputs(); ?>

          <div class="mb-3">
            <label for="nombre" class="form-label font-weight-bold">Nombre del Rol <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Capturista General" required>
          </div>

          <div class="mb-3">
            <label class="form-label font-weight-bold">Permisos Asignados</label>
            <div class="border rounded p-3 bg-light" style="max-height: 350px; overflow-y: auto;">
              <?php if (!empty($d->permisos)): ?>
                <?php foreach ($d->permisos as $p): ?>
                  <?php 
                    $pId = is_object($p) ? $p->id : $p['id'];
                    $pNombre = is_object($p) ? $p->nombre : $p['nombre'];
                    $pDesc = is_object($p) ? $p->descripcion : $p['descripcion'];
                  ?>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="permisos[]" value="<?php echo $pId; ?>" id="perm_add_<?php echo $pId; ?>">
                    <label class="form-check-label text-dark" for="perm_add_<?php echo $pId; ?>">
                      <strong><?php echo htmlspecialchars($pNombre); ?></strong>
                      <br><small class="text-muted"><?php echo htmlspecialchars($pDesc); ?></small>
                    </label>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted m-0">No hay permisos registrados.</p>
              <?php endif; ?>
            </div>
          </div>

          <button class="btn btn-success btn-lg w-100" type="submit">Guardar Rol</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabla de Roles existentes -->
  <div class="col-12 col-md-7 col-lg-7 col-xl-8">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Roles Registrados</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="min-height: 300px;">
          <table class="table table-hover table-striped mb-0 align-middle">
            <thead>
              <tr>
                <th>ID</th>
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
                    <td><strong>#<?php echo $rId; ?></strong></td>
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
                              Editar rol y permisos
                            </a>
                          </li>
                          <?php if ($rId != 1): ?>
                            <li><a class="dropdown-item confirmar text-danger" href="<?php echo build_url(sprintf('admin/borrar-rol/%s', $rId)) ?>">Borrar rol</a></li>
                          <?php endif; ?>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">No hay roles registrados en la base de datos.</td>
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
            <label class="form-label font-weight-bold">Permisos Asignados</label>
            <div class="border rounded p-3 bg-light" style="max-height: 380px; overflow-y: auto;">
              <?php if (!empty($d->permisos)): ?>
                <?php foreach ($d->permisos as $p): ?>
                  <?php 
                    $pId = is_object($p) ? $p->id : $p['id'];
                    $pNombre = is_object($p) ? $p->nombre : $p['nombre'];
                    $pDesc = is_object($p) ? $p->descripcion : $p['descripcion'];
                  ?>
                  <div class="form-check mb-2">
                    <input class="form-check-input edit-perm-check" type="checkbox" name="permisos[]" value="<?php echo $pId; ?>" id="perm_edit_<?php echo $pId; ?>">
                    <label class="form-check-label text-dark" for="perm_edit_<?php echo $pId; ?>">
                      <strong><?php echo htmlspecialchars($pNombre); ?></strong>
                      <br><small class="text-muted"><?php echo htmlspecialchars($pDesc); ?></small>
                    </label>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
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
