<?php require_once INCLUDES . 'admin/publicTop.php'; ?>

<!-- Outer Row -->
<div class="row justify-content-center w-100">
  <div class="col-xl-10 col-lg-12 col-md-9">
    <div class="card o-hidden border-0 shadow-lg my-5">
      <div class="card-body p-0">
        <!-- Nested Row within Card Body -->
        <div class="row">
          <div class="col-lg-6 d-none d-lg-block bg-login-image p-4" style="background: url(<?php echo IMAGES . 'cultura_turismo.png'; ?>); background-size: contain; background-position: center center; background-repeat: no-repeat; background-size: 75%;"></div>
          <div class="col-lg-6">
            <div class="p-5">
              <div class="text-center">
                <h1 class="h4 text-gray-900 mb-4"><?php echo $d->title; ?></h1>
              </div>
              <form class="user" action="login/post_login" method="post" novalidate>
                <?php echo insert_inputs(); ?>

                <div class="mb-3 row">
                  <div class="col-12">
                    <?php echo Flasher::flash(); ?>
                  </div>
                  <div class="col-12 mb-3 text-center">
                    <label class="form-label" for="usuario">Usuario</label>
                    <input type="text" class="form-control form-control-user" id="usuario" name="usuario" placeholder="Ej. PatrimonioCultural" required>
                    <?php if (is_demo() || is_local()) : ?>
                      <small class="text-muted">Ingresa bee</small>
                    <?php endif; ?>
                  </div>
                  <div class="col-12 text-center">
                    <label class="form-label" for="password">Contraseña</label>
                    <input type="password" class="form-control form-control-user" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                    <?php if (is_demo() || is_local()) : ?>
                      <small class="text-muted">Ingresa 123456</small>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="text-center">
                  <button class="btn__login" type="submit"><i class="fas fa-hand-point-right"></i> Ingresar</button>
                </div>
              </form>
              <hr>
              <div class="text-center">
                <a class="small small__color" href="login">¿Olvidaste tu contraseña?</a>
              </div>
              <div class="text-center">
                <a class="small small__color" href="">Crear nueva cuenta</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/publicBottom.php'; ?>