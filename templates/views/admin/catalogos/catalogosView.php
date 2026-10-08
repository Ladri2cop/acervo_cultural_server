<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="row">
  <div class="container py-4">
    <!-- Buscador -->
    <div class="row mb-4">
      <div class="col-12 col-md-3 ml-auto">
        <input type="text" id="buscadorCatalogos" class="form-control" placeholder="Buscar catálogo...">
      </div>
    </div>
    <hr class="mb-4">
    <!-- Contenedor de cartas -->
    <div class="row" id="contenedorCatalogos">
      <!-- Ejemplo de carta de catálogo -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 catalogo-card" data-nombre="Inmuebles">
        <div class="card shadow h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title card-title__custom text-primary"><i class='bx bx-home'></i> Inmuebles</h5>

            <div class="mt-2">
              <input type="text" class="form-control mb-2" placeholder="Escribe aquí...">
              <button class="btn btn-addoption w-100"><i class='bx bx-plus'></i> Agregar opción</button>
            </div>

            <div class="mt-auto pt-3">
              <button class="btn btn-biewcat__custom w-100"><i class='bx bx-show'></i> Ver catálogo</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Repite esta estructura para cada catálogo -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 catalogo-card" data-nombre="Museos">
        <div class="card shadow h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title card-title__custom text-primary"><i class='bx bx-building'></i> Museos</h5>

            <div class="mt-2">
              <input type="text" class="form-control mb-2" placeholder="Escribe aquí...">
              <button class="btn btn-addoption w-100"><i class='bx bx-plus'></i> Agregar opción</button>
            </div>

            <div class="mt-auto pt-3">
              <button class="btn btn-biewcat__custom w-100"><i class='bx bx-show'></i> Ver catálogo</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 catalogo-card" data-nombre="Museos">
        <div class="card shadow h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title card-title__custom text-primary"><i class='bx bx-building'></i> Museos</h5>

            <div class="mt-2">
              <input type="text" class="form-control mb-2" placeholder="Escribe aquí...">
              <button class="btn btn-addoption w-100"><i class='bx bx-plus'></i> Agregar opción</button>
            </div>

            <div class="mt-auto pt-3">
              <button class="btn btn-biewcat__custom w-100"><i class='bx bx-show'></i> Ver catálogo</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Agrega más cartas según tus catálogos -->
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>