<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary title__card">
            <i class='bx bx-receipt'></i> Instrucciones para agregar un nuevo registro
        </h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="toggleInstructionsBtn">
            <i class='bx bx-hide'></i> Mostrar
        </button>
    </div>

    <div class="card-body" id="instructionsContent" style="display: none;">
        <p class="mb-0">Aquí puedes agregar un nuevo registro al sistema. Completa el formulario a continuación y haz clic en "Guardar" para enviar los datos.</p>
        <div class="row">
            <div class="col-12 mt-3">
                <div id="alertPlaceholder"></div>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary title__card">
                            <i class='bx bx-info-circle'></i> Instrucciones
                        </h6>
                    </div>
                </div>
                <ul>
                    <li>Completa todos los campos obligatorios marcados con un asterisco (*).</li>
                    <li>Revisa cuidadosamente la información antes de enviarla.</li>
                    <li>Si tienes alguna duda, consulta el manual de usuario o contacta al administrador del sistema.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header py-3">
        <label for="select-acervo" class="label">Selecciona el tipo de acervo que deseas registrar</label>
        <select name="select-acervo" id="select-acervo" class="form-select">
            <option value="1">Acervo General</option>
            <option value="2">Acervo Toluca</option>
            <option value="3">Acervo Metepec</option>
        </select>
    </div>
</div>

<div class="card shadow mb-4" id="formCard" style="display: none;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary title__card">
            <i class='bx bx-plus'></i> Agregar un nuevo registro
        </h6>
    </div>
    <div class="card-body" id="formulario-dinamico">
        <?php echo $d->form; ?>
    </div>
</div>

<?php echo $d->script; ?> <!-- Este es el script que activa el envío por fetch -->

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>