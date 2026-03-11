<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="col-12 d-flex flex-direction-column flex-wrap">
  <!-- Formulario para agregar usuario -->
  <div class="container-fluid mb-4">
    <div class="row g-2 align-items-end flex-wrap">
      <!-- Filtro: Ubicación -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-map'></i> Ubicación
            </h6>
          </div>
          <div class="card-body">
            <select name="ubicacion" id="ubicacion" class="form-select">
              <option value="" hidden>Seleccione...</option>
              <option value="1">Acervo Acambay</option>
              <option value="2">Acervo Tenancingo</option>
              <option value="3">Acervo Toluca</option>
              <option value="4">Acervo Zinacantepec</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Filtro: Tipo de registro -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-arch'></i> Tipo de acervo
            </h6>
          </div>
          <div class="card-body">
            <select name="tipo_registro" id="tipo_registro" class="form-select">
              <option value="" hidden>Seleccione...</option>
              <option value="1">Arqueológico</option>
              <option value="2">Arte Moderno</option>
              <option value="3">Etnográfico</option>
              <option value="4">Histórico</option>
              <option value="5">Paleontológico</option>
              <option value="6">Natural</option>
              <option value="7">Documental</option>
              <option value="8">Bibliográfico</option>
              <option value="9">Fotográfico</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Filtro: Año -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-calendar'></i> Año
            </h6>
          </div>
          <div class="card-body">
            <select name="anio" id="anio" class="form-select">
              <option value="" hidden>Seleccione...</option>
              <option value="1852">1852</option>
              <option value="1853">1853</option>
              <option value="1854">1854</option>
              <option value="1855">1855</option>F
            </select>
          </div>
        </div>
      </div>

      <!-- Filtro: Cultura -->
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow h-100">
          <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary title__card">
              <i class='bx bx-globe'></i> Cultura
            </h6>
          </div>
          <div class="card-body">
            <select name="cultura" id="cultura" class="form-select">
              <option value="" hidden>Seleccione...</option>
              <option value="2">Mexicas</option>
              <option value="3">Teotihuacanos</option>
              <option value="4">Toltecas</option>
              <option value="5">Chichimecas</option>
              <option value="6">Otomíes</option>
              <option value="7">Matlatzincas</option>
              <option value="8">Mazahuas</option>
              <option value="9">Purhépechas</option>
              <option value="10">Tlaxcaltecas</option>
              <option value="11">Náhuatl</option>
              <option value="12">Mazatecos</option>
              <option value="13">Mixtecos</option>
              <option value="14">Zapotecos</option>
              <option value="15">Totonacas</option>
              <option value="16">Huastecos</option>
              <option value="17">Mayas</option>
              <option value="18">Otros</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de resultados -->
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary title__card col-6 p-0">
          <i class='bx  bx-database-alt'></i> Registros
        </h6>
      </div>
      <div class="card-body p-0">
        <div class="d-flex flex-wrap p-2">
          <div class="col-6 p-1 d-flex align-items-center">
            <select class="form-select col-2" style="min-width: 3rem;" name="numero" id="numeroRegistros">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="25">25</option>
            </select>
            <label class="col-10" for="numeroRegistros">Registros por página</label>
          </div>

          <div class="col-6 p-1">
            <input type="text" id="buscar-registro" class="form-control" placeholder="Buscar registro...">
          </div>
        </div>

        <div class="table-responsive">
          <!-- Loader para la carga de datos -->
          <div id="loader-tabla" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
              <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando registros...</p>
          </div>

          <table class="table table-hover" id="tabla-acervo">
            <thead class="thead-light">
              <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Cultura</th>
                <th>Ubicación</th>
                <th>Fecha</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody id="tabla-piezas">
              <!-- Filas dinámicas aquí -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-body" id="paginacion-container">

      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>