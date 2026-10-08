<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary title__card col-6 p-0">
            <i class="bx bx-cog"></i> Configuración de Formularios
        </h6>
    </div>
    <div class="card-body">
        <!-- Select tipo acervo -->
        <select id="tipoAcervo" class="form-select mb-3">
            <!--  -->
        </select>

        <!-- Input búsqueda -->
        <div class="container__busqueda my-2">
            <input
                type="text"
                id="busquedaCampos"
                class="form-control mt-3 mb-2"
                placeholder="Selecciona un tipo de acervo para buscar campos..."
                autocomplete="off"
                disabled />
            <button class="btn btn_clean_filter" type="button" id="btnLimpiarFiltro" hidden>
                <i class='bx  bx-x'></i>
            </button>
        </div>


        <!-- Botón seleccionar/deseleccionar y contador -->
        <div class="d-flex justify-content-between align-items-center my-3 hiddenItem" id="contDatosContador">
            <div id="contadorCampos" class="text-muted small">
                Campos seleccionados: 0 de 0
            </div>
            <button id="toggleSeleccion" class="btn btn-sm btn-outline-primary" disabled>
                Seleccionar todo
            </button>
        </div>

        <!-- Lista de campos -->
        <ul id="camposDisponibles" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
            <!-- Campos dinámicos aquí -->
        </ul>

        <div class="mt-4 d-flex flex-column align-items-center">
            <button type="button" id="btnGuardarCambios" class="btn btn__add" style="display: none;">
                Guardar Cambios
            </button>
        </div>
    </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>