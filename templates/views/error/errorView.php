<?php require_once INCLUDES . 'header.php'; ?>

<?php
  $isLoggedIn = Auth::validate();
  $targetUrl = $isLoggedIn ? 'admin' : 'login';
  $buttonText = $isLoggedIn ? 'Regresar al Inicio' : 'Iniciar Sesión';
?>

<div class="container py-5 min-vh-100 d-flex flex-column justify-content-center align-items-center">
  <div class="row w-100">
    <div class="col-12">
      <?php echo Flasher::flash(); ?>
    </div>
  </div>
  <div class="row w-100">
    <div class="col-xl-6 col-md-8 col-12 text-center mx-auto py-4">
      <div class="mb-4 p-4 rounded-circle bg-primary d-inline-block shadow-sm">
        <a href="<?php echo get_base_url() . $targetUrl; ?>">
          <img src="<?php echo IMAGES . 'LogoAdminWhite.png'; ?>" alt="<?php echo get_sitename(); ?>" class="img-fluid" style="max-width: 180px;">
        </a>
      </div>

      <h1 class="mt-3 mb-2 text-primary fw-bold display-1" style="font-size: 90px; font-weight: 800;">
        <?php echo $d->code ?? 404; ?>
      </h1>
      <h2 class="fw-bold text-dark mb-3">Página no encontrada</h2>

      <p class="text-center text-muted fs-5 mb-4">La página que buscas no existe o ha sido movida.</p>

      <div class="mt-4">
        <a class="btn btn-primary btn-lg px-4 py-2 shadow-sm rounded-pill font-weight-bold" href="<?php echo $targetUrl; ?>">
          <i class="fas fa-arrow-left me-2"></i> <?php echo $buttonText; ?>
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'footer.php'; ?>