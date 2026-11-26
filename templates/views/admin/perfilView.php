<?php 
  require_once INCLUDES . 'admin/dashboardTop.php'; 
  $perfil = $d->perfil ?? (object) get_user();
  $nombreMuseo = !empty($perfil->nombre_museo) ? $perfil->nombre_museo : get_user_museum_name();
?>

<div class="row">
  <div class="col-12">
    <?php echo Flasher::flash(); ?>
  </div>

  <!-- Columna Izquierda: Cédula de Identidad Institucional -->
  <div class="col-12 col-lg-4 mb-4">
    <div class="card shadow border-0 text-center py-4 mb-4">
      <div class="card-body">
        <div class="position-relative d-inline-block mb-3">
          <img src="<?php echo get_user_avatar(); ?>" alt="Avatar Institucional" class="img-profile rounded-circle shadow border p-1" style="width: 120px; height: 120px; object-fit: cover;">
          <span class="position-absolute bottom-0 right-0 badge rounded-pill bg-success text-white p-2" title="Usuario activo" style="right: 5px; bottom: 5px;">
            <i class="fas fa-check"></i>
          </span>
        </div>
        <h5 class="font-weight-bold text-gray-800 mb-1"><?php echo htmlspecialchars($perfil->username ?? get_user('username')); ?></h5>
        <p class="text-muted small mb-2"><?php echo htmlspecialchars($perfil->email ?? get_user('email')); ?></p>
        
        <div class="mb-3">
          <?php if (is_super_admin()): ?>
            <span class="badge bg-dark text-white px-3 py-2"><i class="fas fa-shield-alt mr-1"></i> Super Administrador</span>
          <?php elseif (is_admin_user()): ?>
            <span class="badge bg-primary text-white px-3 py-2"><i class="fas fa-user-shield mr-1"></i> Administrador</span>
          <?php else: ?>
            <span class="badge bg-info text-white px-3 py-2"><i class="fas fa-user-edit mr-1"></i> Capturista</span>
          <?php endif; ?>
        </div>

        <?php if (!empty($nombreMuseo)): ?>
          <div class="p-2 bg-light rounded border text-muted small mt-2">
            <i class="fas fa-landmark text-primary mr-1"></i> <?php echo htmlspecialchars($nombreMuseo); ?>
          </div>
        <?php elseif (is_super_admin()): ?>
          <div class="p-2 bg-light rounded border text-muted small mt-2">
            <i class="fas fa-globe text-success mr-1"></i> Supervisión Patrimonial Estatal
          </div>
        <?php endif; ?>

        <div class="mt-4 pt-3 border-top text-left small text-muted">
          <div class="d-flex justify-content-between mb-2">
            <span><i class="fas fa-calendar-alt mr-1"></i> Miembro desde:</span>
            <span class="font-weight-bold text-gray-700"><?php echo htmlspecialchars(substr($perfil->created_at ?? date('Y-m-d'), 0, 10)); ?></span>
          </div>
          <div class="d-flex justify-content-between">
            <span><i class="fas fa-fingerprint mr-1"></i> ID de Funcionario:</span>
            <span class="font-weight-bold text-gray-700">#<?php echo (int)($perfil->id ?? get_user('id')); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjeta de Políticas de Seguridad -->
    <div class="card shadow border-left-warning mb-4">
      <div class="card-body">
        <h6 class="font-weight-bold text-warning mb-2"><i class="fas fa-user-lock mr-1"></i> Políticas de Seguridad</h6>
        <ul class="small text-muted pl-3 mb-0">
          <li class="mb-1">Tu contraseña debe tener un mínimo de <strong>8 caracteres</strong>.</li>
          <li class="mb-1">Por tu seguridad, la sesión caduca automáticamente a los <strong>30 minutos</strong> de inactividad.</li>
          <li>Cada actualización de perfil y cambio de clave queda registrada en la bitácora oficial de <strong>auditoría</strong>.</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Columna Derecha: Pestañas de Gestión de Cuenta y Seguridad -->
  <div class="col-12 col-lg-8 mb-4">
    <div class="card shadow">
      <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs card-header-tabs ml-0 mr-0" id="perfilTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active px-4 py-3 font-weight-bold" id="tab-datos-link" data-toggle="tab" href="#tab-datos" role="tab" aria-controls="tab-datos" aria-selected="true">
              <i class="fas fa-id-card mr-2 text-primary"></i>Datos de la Cuenta
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-4 py-3 font-weight-bold" id="tab-seguridad-link" data-toggle="tab" href="#tab-seguridad" role="tab" aria-controls="tab-seguridad" aria-selected="false">
              <i class="fas fa-key mr-2 text-warning"></i>Seguridad y Contraseña
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body p-4">
        <div class="tab-content" id="perfilTabsContent">
          
          <!-- PESTAÑA 1: DATOS DE LA CUENTA -->
          <div class="tab-pane fade show active" id="tab-datos" role="tabpanel" aria-labelledby="tab-datos-link">
            <h6 class="font-weight-bold text-gray-800 mb-3">Información Institucional del Funcionario</h6>
            <p class="text-muted small mb-4">Consulta los datos de tu adscripción patrimonial y actualiza tu cuenta de correo electrónico institucional para notificaciones del sistema.</p>

            <form action="<?php echo URL; ?>admin/post_actualizar_perfil" method="POST" id="formActualizarPerfil">
              <?php echo insert_inputs(); ?>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="small font-weight-bold text-gray-700">Nombre de Usuario</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-light"><i class="fas fa-user text-gray-500"></i></span>
                    </div>
                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($perfil->username ?? get_user('username')); ?>" readonly>
                  </div>
                  <small class="form-text text-muted">El identificador de usuario institucional no puede ser alterado.</small>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="small font-weight-bold text-gray-700">Rol Institucional</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-light"><i class="fas fa-shield-alt text-gray-500"></i></span>
                    </div>
                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($perfil->rol_nombre ?? (is_super_admin() ? 'Super Administrador' : (is_admin_user() ? 'Administrador' : 'Capturista'))); ?>" readonly>
                  </div>
                </div>

                <div class="col-md-12 mb-3">
                  <label class="small font-weight-bold text-gray-700">Museo o Recinto de Adscripción</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-light"><i class="fas fa-landmark text-gray-500"></i></span>
                    </div>
                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(!empty($nombreMuseo) ? $nombreMuseo : (is_super_admin() ? 'Supervisión Estatal Global' : 'Sin museo asignado')); ?>" readonly>
                  </div>
                </div>

                <div class="col-md-12 mb-4">
                  <label class="small font-weight-bold text-gray-700">Correo Electrónico Institucional <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="fas fa-envelope text-primary"></i></span>
                    </div>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($perfil->email ?? get_user('email')); ?>" required>
                  </div>
                  <small class="form-text text-muted">A este correo se envían alertas del semáforo y avisos de préstamo.</small>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <span class="small text-muted"><i class="fas fa-info-circle mr-1"></i> Los cambios son auditados y entran en vigor de inmediato.</span>
                <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                  <i class="fas fa-save mr-1"></i> Guardar Cambios
                </button>
              </div>
            </form>
          </div>

          <!-- PESTAÑA 2: SEGURIDAD Y CONTRASEÑA -->
          <div class="tab-pane fade" id="tab-seguridad" role="tabpanel" aria-labelledby="tab-seguridad-link">
            <h6 class="font-weight-bold text-gray-800 mb-2">Actualizar Contraseña de Acceso</h6>
            <p class="text-muted small mb-4">Por protocolo de seguridad del Estado de México, te recomendamos actualizar periódicamente tu contraseña y no compartirla con ningún otro colaborador.</p>

            <form action="<?php echo URL; ?>admin/post_cambiar_password" method="POST" id="formCambiarPassword">
              <?php echo insert_inputs(); ?>

              <!-- Contraseña Actual -->
              <div class="form-group mb-3">
                <label for="password_actual" class="small font-weight-bold text-gray-700">Contraseña Actual <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-lock text-gray-500"></i></span>
                  </div>
                  <input type="password" class="form-control" name="password_actual" id="password_actual" placeholder="Ingresa tu contraseña actual" required autocomplete="current-password">
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_actual" title="Mostrar/Ocultar">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
              </div>

              <hr class="my-3">

              <!-- Nueva Contraseña -->
              <div class="form-group mb-3">
                <label for="password_nuevo" class="small font-weight-bold text-gray-700">Nueva Contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-key text-warning"></i></span>
                  </div>
                  <input type="password" class="form-control" name="password_nuevo" id="password_nuevo" placeholder="Mínimo 8 caracteres" required minlength="8" autocomplete="new-password">
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_nuevo" title="Mostrar/Ocultar">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
                <div class="d-flex align-items-center mt-1">
                  <small id="longitud-helper" class="text-muted">
                    <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Mínimo 8 caracteres
                  </small>
                </div>
              </div>

              <!-- Confirmar Nueva Contraseña -->
              <div class="form-group mb-4">
                <label for="password_confirmar" class="small font-weight-bold text-gray-700">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-check-double text-success"></i></span>
                  </div>
                  <input type="password" class="form-control" name="password_confirmar" id="password_confirmar" placeholder="Reescribe tu nueva contraseña" required minlength="8" autocomplete="new-password">
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmar" title="Mostrar/Ocultar">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
                <small id="coincidencia-helper" class="form-text mt-1 text-muted">
                  <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Las contraseñas deben ser exactamente iguales.
                </small>
              </div>

              <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <button type="reset" class="btn btn-light btn-sm text-muted">
                  <i class="fas fa-undo mr-1"></i> Restablecer formulario
                </button>
                <button type="submit" class="btn btn-warning px-4 font-weight-bold text-dark" id="btnActualizarPassword">
                  <i class="fas fa-shield-alt mr-1"></i> Actualizar Contraseña
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>

      <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
        <span class="small text-muted">Plataforma SIPAC - Sistema de Inventario Para el Acervo Cultural</span>
        <a href="#" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#logoutModal">
          <i class="fas fa-sign-out-alt mr-1"></i> Cerrar sesión
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1. Activar pestaña de seguridad si la URL contiene el hash #seguridad
  if (window.location.hash === '#seguridad') {
    if (typeof $ !== 'undefined') {
      $('#tab-seguridad-link').tab('show');
    }
  }

  // 2. Conmutador de visualización de contraseña (mostrar/ocultar)
  var toggleButtons = document.querySelectorAll('.toggle-password');
  toggleButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var targetId = this.getAttribute('data-target');
      var input = document.getElementById(targetId);
      var icon = this.querySelector('i');
      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      }
    });
  });

  // 3. Validación interactiva de contraseña en tiempo real
  var passNuevo = document.getElementById('password_nuevo');
  var passConfirm = document.getElementById('password_confirmar');
  var longitudHelper = document.getElementById('longitud-helper');
  var coincidenciaHelper = document.getElementById('coincidencia-helper');
  var btnSubmit = document.getElementById('btnActualizarPassword');

  function validarContrasenas() {
    var valNuevo = passNuevo.value;
    var valConfirm = passConfirm.value;

    // Validación de longitud
    if (valNuevo.length >= 8) {
      longitudHelper.className = 'text-success font-weight-bold';
      longitudHelper.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Cumple con la longitud mínima requerida (8+ caracteres)';
    } else if (valNuevo.length > 0) {
      longitudHelper.className = 'text-danger';
      longitudHelper.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Faltan ' + (8 - valNuevo.length) + ' caracteres para el mínimo';
    } else {
      longitudHelper.className = 'text-muted';
      longitudHelper.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Mínimo 8 caracteres';
    }

    // Validación de coincidencia
    if (valConfirm.length > 0) {
      if (valNuevo === valConfirm) {
        coincidenciaHelper.className = 'text-success font-weight-bold';
        coincidenciaHelper.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Las contraseñas coinciden correctamente';
      } else {
        coincidenciaHelper.className = 'text-danger font-weight-bold';
        coincidenciaHelper.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Las contraseñas aún no coinciden';
      }
    } else {
      coincidenciaHelper.className = 'text-muted';
      coincidenciaHelper.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Las contraseñas deben ser exactamente iguales.';
    }
  }

  if (passNuevo && passConfirm) {
    passNuevo.addEventListener('input', validarContrasenas);
    passConfirm.addEventListener('input', validarContrasenas);
  }

  // Validación final al enviar formulario
  var formPass = document.getElementById('formCambiarPassword');
  if (formPass) {
    formPass.addEventListener('submit', function (e) {
      if (passNuevo.value.length < 8) {
        e.preventDefault();
        alert('La nueva contraseña debe tener al menos 8 caracteres.');
        passNuevo.focus();
        return false;
      }
      if (passNuevo.value !== passConfirm.value) {
        e.preventDefault();
        alert('La nueva contraseña y su confirmación no coinciden.');
        passConfirm.focus();
        return false;
      }
    });
  }
});
</script>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
