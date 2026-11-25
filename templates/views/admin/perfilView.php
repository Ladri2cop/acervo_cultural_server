<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <div class="col-12">
    <?php echo Flasher::flash(); ?>
  </div>

  <!-- Tarjeta de Identidad Institucional -->
  <div class="col-12 col-md-4 mb-4">
    <div class="card shadow border-0 text-center py-4">
      <div class="card-body">
        <div class="position-relative d-inline-block mb-3">
          <img src="<?php echo get_user_avatar(); ?>" alt="Avatar Institucional" class="img-profile rounded-circle shadow border p-1" style="width: 120px; height: 120px; object-fit: cover;">
        </div>
        <h5 class="font-weight-bold text-gray-800 mb-1"><?php echo htmlspecialchars($d->user->username ?? get_user('username')); ?></h5>
        <p class="text-muted small mb-2"><?php echo htmlspecialchars($d->user->email ?? get_user('email')); ?></p>
        
        <div class="mb-3">
          <?php if (is_super_admin()): ?>
            <span class="badge bg-dark text-white px-3 py-2"><i class="fas fa-shield-alt me-1"></i> Super Administrador</span>
          <?php elseif (is_admin_user()): ?>
            <span class="badge bg-primary text-white px-3 py-2"><i class="fas fa-user-shield me-1"></i> Administrador</span>
          <?php else: ?>
            <span class="badge bg-info text-white px-3 py-2"><i class="fas fa-user-edit me-1"></i> Capturista</span>
          <?php endif; ?>
        </div>

        <?php if (get_user_museum_name()): ?>
          <div class="p-2 bg-light rounded border text-muted small mt-2">
            <i class="fas fa-landmark text-primary me-1"></i> <?php echo htmlspecialchars(get_user_museum_name()); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-8">
    <div class="card shadow">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Cuenta</h6>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Información técnica registrada para la cuenta de usuario en la plataforma institucional.</p>
        <div class="table-responsive rounded">
          <table class="table table-sm table-striped table-hover table-bordered mb-0">
            <tbody>
              <?php foreach ($d->user as $k => $v) : ?>
                <?php if ($k === 'password') continue; // Ocultar hash de contraseña ?>
                <tr>
                  <th class="text-capitalize bg-light" style="width: 35%;"><?php echo htmlspecialchars(str_replace('_', ' ', $k)); ?></th>
                  <td><?php echo htmlspecialchars((string)$v); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-light clearfix">
        <a href="logout" class="btn btn-danger btn-sm float-end" data-toggle="modal" data-target="#logoutModal">
          <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>