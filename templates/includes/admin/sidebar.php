<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Sidebar - Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo get_base_url() . (is_admin_user() ? 'admin' : 'admin/acervo'); ?>">
    <img src="<?php echo IMAGES . 'LogoAdminWhite.png' ?>" alt="<?php echo get_bee_name(); ?>" width="100px" class="logo-full">
    <img src="<?php echo IMAGES . 'ColibriWhite.png'; ?>" alt="<?php echo get_bee_name(); ?>" width="50px" class="logo-icon">
  </a>

  <!-- Divider -->
  <hr class="sidebar-divider my-0">

  <?php if (is_admin_user()): ?>
  <!-- Nav Item - Dashboard -->
  <li class="nav-item" data-page="dashboard">
    <a class="nav-link" href="admin">
      <i class='bx  bx-chart-spline'></i>
      <span>Dashboard</span>
    </a>
  </li>

  <!-- Divider -->
  <hr class="sidebar-divider">
  <?php endif; ?>

  <!-- Heading -->
  <div class="sidebar-heading">
    TAREAS
  </div>

  <!-- <li class="nav-item" data-page="">
    <a class="nav-link" href="creator">
      <i class="fas fa-fw fa-pen"></i>
      <span>Creator</span>
    </a>
  </li> -->

  <?php if (user_can_register() && !user_has_only_view_permissions()): ?>
  <li class="nav-item" data-page="registrar">
    <a class="nav-link" href="admin/registrar">
      <i class='bx  bx-plus-square'></i>
      <span>Registrar</span>
    </a>
  </li>
  <?php endif; ?>

  <?php if (user_can_view_acervo()): ?>
  <li class="nav-item" data-page="acervo">
    <a class="nav-link" href="admin/acervo">
      <i class='bx  bx-database-alt'></i>
      <span>Acervo</span>
    </a>
  </li>
  <?php endif; ?>

  <?php if (is_admin_user()): ?>
  <li class="nav-item" data-page="revision">
    <a class="nav-link" href="admin/revision">
      <i class='bx bx-check-shield'></i>
      <span>Revisión de Acervo</span>
    </a>
  </li>
  <li class="nav-item" data-page="prestamos_transferencias">
    <a class="nav-link" href="admin/prestamos_transferencias">
      <i class='bx bx-transfer-alt'></i>
      <span>Préstamos y Transferencias</span>
    </a>
  </li>
  <?php endif; ?>

  <?php if (is_admin_user()): ?>
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
      GESTIÓN
    </div>

    <!-- Nav Item - Usuarios Collapse Menu -->
    <li class="nav-item" data-page="componentes">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapeseUsers" aria-expanded="true" aria-controls="collapeseUsers">
        <i class='bx  bx-group'></i>
        <span>Usuarios</span>
      </a>
      <div id="collapeseUsers" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">Gestión de usuarios</h6>
          <a class="collapse-item" href="admin/usuarios">Usuarios</a>
          <a class="collapse-item" href="admin/roles">Roles y permisos</a>
        </div>
      </div>
    </li>

    <li class="nav-item" data-page="auditoria">
      <a class="nav-link" href="admin/auditoria">
        <i class='bx bx-history'></i>
        <span>Auditoría</span>
      </a>
    </li>
  <?php endif; ?>
</ul>
<!-- End of Sidebar -->