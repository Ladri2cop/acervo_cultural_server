<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow px-2">

  <!-- Sidebar Toggle (Topbar) -->
  <button class="btn btn-menu__custom" id="sidebarToggle">
    <i class="bx bx-arrow-to-left-stroke" id="toggleIcon"></i>
  </button>

  <!-- Topbar Navbar -->
  <ul class="navbar-nav ml-auto">

    <!-- Nav Item - User Information -->
    <li class="nav-item dropdown no-arrow">
      <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="mr-2 d-none d-lg-inline text-gray-700 font-weight-bold small"><?php echo htmlspecialchars(get_user('username') ?: 'Usuario'); ?></span>
        <img class="img-profile rounded-circle border shadow-sm" src="<?php echo get_user_avatar(); ?>" alt="<?php echo htmlspecialchars(get_user('username') ?: 'Avatar Institucional'); ?>" style="width: 34px; height: 34px; object-fit: cover;">
      </a>
      <!-- Dropdown - User Information -->
      <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
        <a class="dropdown-item" href="<?php echo URL; ?>admin/perfil">
          <i class="fas fa-user fa-sm fa-fw mr-2 text-primary"></i>
          Mi Perfil
        </a>
        <a class="dropdown-item" href="<?php echo URL; ?>admin/perfil#seguridad">
          <i class="fas fa-key fa-sm fa-fw mr-2 text-warning"></i>
          Seguridad y Contraseña
        </a>
        <?php if (is_admin_user()): ?>
        <a class="dropdown-item" href="<?php echo URL; ?>admin/auditoria">
          <i class="fas fa-history fa-sm fa-fw mr-2 text-info"></i>
          Bitácora de Auditoría
        </a>
        <?php else: ?>
        <a class="dropdown-item" href="<?php echo URL; ?>admin/revision">
          <i class="fas fa-clipboard-check fa-sm fa-fw mr-2 text-info"></i>
          Mis Registros
        </a>
        <?php endif; ?>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
          <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
          Cerrar sesión
        </a>
      </div>
    </li>

  </ul>

</nav>
<!-- End of Topbar -->