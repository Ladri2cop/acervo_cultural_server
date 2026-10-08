<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <!-- Formulario para agregar usuario -->
  <div class="col-12 col-md-6 col-lg-6 col-xl-3">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Agregar un usuario</h6>
      </div>
      <div class="card-body">
        <form action="admin/post_usuarios" method="post">
          <?php echo insert_inputs(); ?>

          <div class="mb-3">
            <label for="username" class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="username" name="username" placeholder="admin" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" placeholder="admin@beeframework.com" required>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>

          <div class="mb-3">
            <label for="id_role" class="form-label">Rol de usuario <span class="text-danger">*</span></label>
            <select class="form-control" id="id_role" name="id_role" required>
              <option value="">-- Selecciona un rol --</option>
              <?php if (!empty($d->roles)): ?>
                <?php foreach ($d->roles as $role): ?>
                  <?php 
                    $roleId = is_object($role) ? $role->id : $role['id'];
                    $roleNombre = is_object($role) ? $role->nombre : $role['nombre'];
                  ?>
                  <option value="<?php echo $roleId; ?>"><?php echo htmlspecialchars($roleNombre); ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <button class="btn btn-success btn-lg btn-block" type="submit">Agregar ahora</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabla de resultados -->
  <div class="col-12 col-md-6 col-lg-6 col-xl-9">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Todos los usuarios</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="min-height: 300px;">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>Usuario</th>
                <th class="text-center">Correo electrónico</th>
                <th class="text-center">Rol</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($d->users->rows)): ?>
                <?php foreach ($d->users->rows as $user) : ?>
                  <tr>
                    <td><?php echo $user->id == get_user('id') ? $user->username . ' (Tú)' : $user->username; ?></td>
                    <td class="text-center"><?php echo $user->email; ?></td>
                    <td class="text-center"><span class="badge bg-primary text-white"><?php echo !empty($user->role_name) ? $user->role_name : 'Sin rol'; ?></span></td>
                    <td class="text-end">
                      <div class="dropdown">
                        <a class="btn btn-sm btn-secondary" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                          <li>
                            <a class="dropdown-item btn-editar-usuario" 
                               href="#" 
                               data-id="<?php echo $user->id; ?>" 
                               data-username="<?php echo htmlspecialchars($user->username); ?>" 
                               data-email="<?php echo htmlspecialchars($user->email); ?>" 
                               data-role="<?php echo $user->id_role; ?>"
                               data-toggle="modal" 
                               data-target="#modalEditarUsuario">
                              Editar
                            </a>
                          </li>
                          <?php if (!empty($user->auth_token)): ?>
                            <li><a class="dropdown-item" href="<?php echo build_url(sprintf('admin/destruir-sesion/%s', $user->id)) ?>">Destruir sesión</a></li>
                          <?php endif; ?>
                          <li><a class="dropdown-item confirmar" href="<?php echo build_url(sprintf('admin/borrar-usuario/%s', $user->id)) ?>">Borrar</a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center">No hay usuarios registrados en la base de datos.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-body">
        <?php echo $d->users->pagination; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold" id="modalEditarUsuarioLabel">Editar Usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="admin/post_editar_usuario" method="post">
        <?php echo insert_inputs(); ?>
        <input type="hidden" id="edit_id_usuario" name="id_usuario" value="">

        <div class="modal-body">
          <div class="mb-3">
            <label for="edit_username" class="form-label font-weight-bold">Nombre de usuario <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_username" name="username" required>
          </div>

          <div class="mb-3">
            <label for="edit_email" class="form-label font-weight-bold">Correo electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="edit_email" name="email" required>
          </div>

          <div class="mb-3">
            <label for="edit_id_role" class="form-label font-weight-bold">Rol de usuario <span class="text-danger">*</span></label>
            <select class="form-control" id="edit_id_role" name="id_role" required>
              <option value="">-- Selecciona un rol --</option>
              <?php if (!empty($d->roles)): ?>
                <?php foreach ($d->roles as $role): ?>
                  <?php 
                    $roleId = is_object($role) ? $role->id : $role['id'];
                    $roleNombre = is_object($role) ? $role->nombre : $role['nombre'];
                  ?>
                  <option value="<?php echo $roleId; ?>"><?php echo htmlspecialchars($roleNombre); ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="edit_password" class="form-label font-weight-bold">Nueva contraseña <small class="text-muted">(Opcional, dejar en blanco para conservar la actual)</small></label>
            <input type="password" class="form-control" id="edit_password" name="password" placeholder="••••••••">
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
    $(document).on('click', '.btn-editar-usuario', function(e) {
      var id = $(this).data('id');
      var username = $(this).data('username');
      var email = $(this).data('email');
      var role = $(this).data('role');

      $('#edit_id_usuario').val(id);
      $('#edit_username').val(username);
      $('#edit_email').val(email);
      $('#edit_id_role').val(role);
      $('#edit_password').val('');
    });
  });
</script>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>