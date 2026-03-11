<hr>

<h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Acervo General</h1>

<form id="form-pieza" class="max-w-3xl mx-auto bg-gradient-to-br from-blue-50 via-white to-blue-100 rounded-2xl shadow-xl p-10 mb-10 font-sans border border-blue-200 animate-fade-in">
    <input type="hidden" id="edit-id" value="" style="font-family: 'Fira Mono', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', monospace;">
    <h3 class="text-xl font-bold text-blue-700 mb-6">Registro de pieza</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="flex flex-col gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="codigo_interno" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Código interno</label>
            <input type="text" id="codigo_interno" name="codigo_interno" class="input-tw" autocomplete="off" placeholder="Ej: 001-AQ-2026" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="no_inventario" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">No. Inventario</label>
            <input type="text" id="no_inventario" name="no_inventario" class="input-tw" autocomplete="off" placeholder="Ej: INV-1234" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col md:col-span-2 gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="nombre_titulo_pieza" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Nombre/Título de la pieza</label>
            <input type="text" id="nombre_titulo_pieza" name="nombre_titulo_pieza" class="input-tw" autocomplete="off" placeholder="Ej: Jarrón de cerámica" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="cm" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">CM</label>
            <input type="text" id="cm" name="cm" class="input-tw" autocomplete="off" placeholder="Ej: 12.5" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="fotografia" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Fotografía</label>
            <input type="text" id="fotografia" name="fotografia" class="input-tw" autocomplete="off" placeholder="URL o nombre de archivo" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="autor" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Autor</label>
            <input type="text" id="autor" name="autor" class="input-tw" autocomplete="off" placeholder="Ej: Juan Pérez" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="anio" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Año</label>
            <input type="text" id="anio" name="anio" class="input-tw" autocomplete="off" placeholder="Ej: 1980" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2 font-sans" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
            <label for="epoca" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Época</label>
            <input type="text" id="epoca" name="epoca" class="input-tw" autocomplete="off" placeholder="Ej: Siglo XX" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="tecnica" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Técnica</label>
            <input type="text" id="tecnica" name="tecnica" class="input-tw" autocomplete="off" placeholder="Ej: Acuarela" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="material" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Material</label>
            <input type="text" id="material" name="material" class="input-tw" autocomplete="off" placeholder="Ej: Cerámica" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="medidas" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Medidas</label>
            <input type="text" id="medidas" name="medidas" class="input-tw" autocomplete="off" placeholder="Ej: 20x15x10 cm" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="lote" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Lote</label>
            <input type="text" id="lote" name="lote" class="input-tw" autocomplete="off" placeholder="Ej: L-2026-01" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="peso" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Peso</label>
            <input type="text" id="peso" name="peso" class="input-tw" autocomplete="off" placeholder="Ej: 2.3 kg" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="coleccion" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Colección</label>
            <input type="text" id="coleccion" name="coleccion" class="input-tw" autocomplete="off" placeholder="Ej: Colección privada" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="tipo" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Tipo</label>
            <input type="text" id="tipo" name="tipo" class="input-tw" autocomplete="off" placeholder="Ej: Escultura" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="ubicacion_fisica" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Ubicación física</label>
            <input type="text" id="ubicacion_fisica" name="ubicacion_fisica" class="input-tw" autocomplete="off" placeholder="Ej: Sala 2, vitrina 4" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col gap-2">
            <label for="estado_conservacion" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Estado de conservación</label>
            <input type="text" id="estado_conservacion" name="estado_conservacion" class="input-tw" autocomplete="off" placeholder="Ej: Bueno" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col md:col-span-2 gap-2">
            <label for="observaciones" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Observaciones</label>
            <input type="text" id="observaciones" name="observaciones" class="input-tw" autocomplete="off" placeholder="Notas adicionales" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">
        </div>
        <div class="flex flex-col md:col-span-2 gap-2">
            <label for="descripcion" class="font-semibold text-blue-900" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="input-tw h-24 resize-y" placeholder="Descripción detallada de la pieza" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';"></textarea>
        </div>
    </div>
    <div class="flex gap-4 justify-end mt-10">
        <button type="submit" class="btn-tw bg-blue-600 hover:bg-blue-700 text-white shadow-lg" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">Guardar</button>
        <button type="button" id="cancelar-edicion" style="display:none; font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';" class="btn-tw bg-gray-400 hover:bg-gray-500 text-white shadow">Cancelar</button>
    </div>
</form>

<hr>

<h2>Listado de Piezas</h2>
<div id="piezas-list-json" class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 mb-4 min-h-[80px]">
    <em class="text-gray-500">Cargando piezas...</em>
</div>
<div id="paginacion" class="flex justify-center items-center gap-2 mb-8"></div>

<script>
    let paginaActual = 1;
    let totalPaginas = 1;
    const porPagina = 10;

    function renderPaginacion() {
        const pagCont = document.getElementById('paginacion');
        if (!pagCont) return;
        let html = '';
        if (totalPaginas > 1) {
            html += `<button ${paginaActual === 1 ? 'disabled' : ''} onclick=\"cambiarPagina(1)\" class='btn-tw bg-gray-200 text-gray-700'>&laquo; Primera</button>`;
            html += `<button ${paginaActual === 1 ? 'disabled' : ''} onclick=\"cambiarPagina(${paginaActual - 1})\" class='btn-tw bg-gray-200 text-gray-700'>&lsaquo; Anterior</button>`;
            html += `<span class='mx-2 text-gray-700 font-semibold'>Página ${paginaActual} de ${totalPaginas}</span>`;
            html += `<button ${paginaActual === totalPaginas ? 'disabled' : ''} onclick=\"cambiarPagina(${paginaActual + 1})\" class='btn-tw bg-gray-200 text-gray-700'>Siguiente &rsaquo;</button>`;
            html += `<button ${paginaActual === totalPaginas ? 'disabled' : ''} onclick=\"cambiarPagina(${totalPaginas})\" class='btn-tw bg-gray-200 text-gray-700'>Última &raquo;</button>`;
        }
        pagCont.innerHTML = html;
    }

    function cargarPiezas(page = 1) {
        fetch(`/Bee-Framework/acervoGeneral/api_piezas?page=${page}&per_page=${porPagina}`)
            .then(res => res.json())
            .then(resp => {
                const cont = document.getElementById('piezas-list-json');
                const data = resp.data || [];
                paginaActual = resp.page || 1;
                totalPaginas = resp.total_pages || 1;
                renderPaginacion();
                if (Array.isArray(data) && data.length > 0) {
                    let html = `<div class="w-full flex justify-center animate-fade-in">
                        <table class="min-w-full text-sm text-center border border-gray-200 block md:table rounded-2xl shadow-2xl font-sans">
                            <thead>
                                <tr style="background-color: #8a2036;" class="text-white">` +
                        '<th class="px-3 py-2 text-center min-w-[80px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">ID</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Código interno</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">No. Inventario</th>' +
                        '<th class="px-3 py-2 text-center min-w-[180px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Nombre/Título</th>' +
                        '<th class="px-3 py-2 text-center min-w-[80px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">CM</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Fotografía</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Autor</th>' +
                        '<th class="px-3 py-2 text-center min-w-[80px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Año</th>' +
                        '<th class="px-3 py-2 text-center min-w-[100px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Época</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Técnica</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Material</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Medidas</th>' +
                        '<th class="px-3 py-2 text-center min-w-[80px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Lote</th>' +
                        '<th class="px-3 py-2 text-center min-w-[80px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Peso</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Colección</th>' +
                        '<th class="px-3 py-2 text-center min-w-[100px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Tipo</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Ubicación física</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Estado conservación</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Observaciones</th>' +
                        '<th class="px-3 py-2 text-center min-w-[180px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Descripción</th>' +
                        '<th class="px-3 py-2 text-center min-w-[120px]" style="font-family: Inter, Segoe UI, Arial, Helvetica, sans-serif; color: #fff;">Acciones</th>' +
                        `</tr>
                            </thead>
                            <tbody>`;
                    data.forEach((p, idx) => {
                        window['pieza_' + p.id_acervo_general] = p;
                        html += `<tr class="border-b border-gray-200 text-black hover:bg-[#8a2036] hover:text-white transition text-center" style="background-color: ${idx % 2 === 0 ? '#f8f6f7' : '#eddccc'}; text-align:center;">` +
                            `<td class="px-3 py-2 text-xs text-center min-w-[80px]" style="font-family: 'Fira Mono', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', monospace;">${p.id_acervo_general}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';"><span class="inline-block bg-[#eddccc] text-[#8a2036] rounded-full px-3 py-1 text-xs font-bold" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.codigo_interno || ''}</span></td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.no_inventario || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[180px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.nombre_titulo_pieza || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[80px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.cm || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.fotografia || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.autor || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[80px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.anio || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[100px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.epoca || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.tecnica || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.material || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.medidas || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[80px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.lote || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[80px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.peso || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.coleccion || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[100px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.tipo || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.ubicacion_fisica || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.estado_conservacion || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.observaciones || ''}</td>` +
                            `<td class="px-3 py-2 text-center min-w-[180px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">${p.descripcion || ''}</td>` +
                            `<td class="px-3 py-2 flex gap-2 justify-center text-center min-w-[120px]" style="font-family: 'Inter', 'Segoe UI', 'Arial', 'Helvetica', 'sans-serif';">` +
                                `<button onclick='editarPieza(${p.id_acervo_general})' title="Editar" class="flex items-center justify-center bg-yellow-400 hover:bg-yellow-500 text-gray-800 transition-transform hover:scale-105 shadow rounded-full w-9 h-9 p-0" style="min-width:2.25rem;min-height:2.25rem;width:2.25rem;height:2.25rem;" tabindex="0"><svg class='w-5 h-5' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M15.232 5.232l3.536 3.536M9 11l6.232-6.232a2 2 0 012.828 2.828L11 15l-4 1 1-4z'/></svg></button>` +
                                `<button onclick='eliminarPieza(${p.id_acervo_general})' title="Eliminar" class="flex items-center justify-center bg-red-500 hover:bg-red-600 text-white transition-transform hover:scale-105 shadow rounded-full w-9 h-9 p-0" style="min-width:2.25rem;min-height:2.25rem;width:2.25rem;height:2.25rem;" tabindex="0"><svg class='w-5 h-5' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12'/></svg></button>` +
                            `</td>` +
                            `</tr>`;
                    });
                    html += '</tbody></table></div>';
                    cont.innerHTML = html;
                } else {
                    cont.innerHTML = '<p class="text-gray-500">No hay piezas registradas.</p>';
                }
                // Tailwind utility classes for inputs and buttons
                const style = document.createElement('style');
                style.innerHTML = `
                .input-tw {
                    @apply border border-blue-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white text-gray-800 transition font-sans text-base placeholder-gray-400 shadow-sm;
                }
                .btn-tw {
                    @apply px-5 py-2 rounded-lg shadow text-base font-semibold transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-400 font-sans;
                }
                .animate-fade-in {
                    animation: fadeIn 0.7s ease;
                }
                @keyframes fadeIn {
                    0% { opacity: 0; transform: translateY(20px); }
                    100% { opacity: 1; transform: translateY(0); }
                }
                `;
                document.head.appendChild(style);
            });
    }

    function cambiarPagina(nuevaPag) {
        if (nuevaPag < 1 || nuevaPag > totalPaginas) return;
        cargarPiezas(nuevaPag);
    }

    function agregarEditarPieza(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const url = id ? `/Bee-Framework/acervoGeneral/api_editar/${id}` : '/Bee-Framework/acervoGeneral/api_agregar';
        const formData = new URLSearchParams();
        [
            'codigo_interno', 'no_inventario', 'nombre_titulo_pieza', 'cm', 'fotografia', 'autor', 'anio', 'epoca', 'tecnica', 'material', 'medidas', 'lote', 'peso', 'coleccion', 'tipo', 'ubicacion_fisica', 'estado_conservacion', 'observaciones', 'descripcion'
        ].forEach(function(campo) {
            const el = document.getElementById(campo);
            if (el) formData.append(campo, el.value);
        });
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cargarPiezas();
                    document.getElementById('form-pieza').reset();
                    document.getElementById('edit-id').value = '';
                    document.getElementById('cancelar-edicion').style.display = 'none';
                } else {
                    alert('Error: ' + (data.error || 'No se pudo guardar.'));
                }
            });
    }

    function editarPieza(id) {
        document.getElementById('edit-id').value = id;
        var pieza = window['pieza_' + id] || {};
        [
            'codigo_interno', 'no_inventario', 'nombre_titulo_pieza', 'cm', 'fotografia', 'autor', 'anio', 'epoca', 'tecnica', 'material', 'medidas', 'lote', 'peso', 'coleccion', 'tipo', 'ubicacion_fisica', 'estado_conservacion', 'observaciones', 'descripcion'
        ].forEach(function(campo) {
            if (document.getElementById(campo)) {
                document.getElementById(campo).value = pieza[campo] !== undefined ? pieza[campo] : '';
            }
        });
        document.getElementById('cancelar-edicion').style.display = '';
    }

    function eliminarPieza(id) {
        if (!confirm('¿Seguro que deseas eliminar esta pieza?')) return;
        fetch(`/Bee-Framework/acervoGeneral/api_eliminar/${id}`, {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cargarPiezas();
                } else {
                    alert('Error al eliminar.');
                }
            });
    }

    document.getElementById('form-pieza').addEventListener('submit', agregarEditarPieza);
    document.getElementById('cancelar-edicion').addEventListener('click', function() {
        document.getElementById('form-pieza').reset();
        document.getElementById('edit-id').value = '';
        this.style.display = 'none';
    });

    cargarPiezas();
</script>