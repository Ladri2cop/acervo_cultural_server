/**
 * JavaScript para el módulo de Préstamos y Transferencias de Acervo
 * Secretaría de Cultura y Turismo
 */

$(document).ready(function () {
  'use strict';

  // =========================================================================
  // ESTADO DE LA VISTA
  // =========================================================================
  let estado = {
    page: 1,
    perPage: 10,
    tipoMovimiento: 'todos',
    estatus: 'todos',
    tipoAcervo: 'todos',
    search: '',
    searchTimer: null,
    piezaSearchTimer: null,
    piezaSeleccionada: null
  };

  // =========================================================================
  // INICIALIZACIÓN
  // =========================================================================
  cargarMovimientos();
  configurarEventos();

  // =========================================================================
  // CONFIGURACIÓN DE EVENTOS
  // =========================================================================
  function configurarEventos() {
    // Filtros de tabla
    $('#filter-tipo-movimiento').on('change', function () {
      estado.tipoMovimiento = $(this).val();
      estado.page = 1;
      cargarMovimientos();
    });

    $('#filter-estatus').on('change', function () {
      estado.estatus = $(this).val();
      estado.page = 1;
      cargarMovimientos();
    });

    $('#filter-tipo-acervo').on('change', function () {
      estado.tipoAcervo = $(this).val();
      estado.page = 1;
      cargarMovimientos();
    });

    // Búsqueda en tiempo real con debounce
    $('#filter-search').on('input', function () {
      clearTimeout(estado.searchTimer);
      const val = $(this).val().trim();
      estado.searchTimer = setTimeout(function () {
        estado.search = val;
        estado.page = 1;
        cargarMovimientos();
      }, 350);
    });

    $('#btn-limpiar-busqueda').on('click', function () {
      $('#filter-search').val('');
      estado.search = '';
      estado.page = 1;
      cargarMovimientos();
    });

    $('#btn-reset-filtros').on('click', function () {
      resetearFiltros();
    });

    $('#btn-refresh-prestamos').on('click', function () {
      cargarMovimientos();
    });

    // Click en tarjetas KPI para filtrar rápido
    $('.kpi-card').on('click', function () {
      const tipo = $(this).data('filter-tipo');
      const estatus = $(this).data('filter-estatus');

      if (tipo !== undefined) {
        $('#filter-tipo-movimiento').val(tipo);
        estado.tipoMovimiento = tipo;
      }
      if (estatus !== undefined) {
        $('#filter-estatus').val(estatus);
        estado.estatus = estatus;
      }

      estado.page = 1;
      cargarMovimientos();
    });

    // Abrir modal nuevo movimiento
    $('#btn-nuevo-movimiento, #btn-empty-nuevo').on('click', function () {
      abrirModalNuevoMovimiento();
    });

    // Cambio de tipo de movimiento en modal (Radio buttons)
    $('input[name="tipo_movimiento"]').on('change', function () {
      actualizarCamposTipoMovimiento($(this).val());
    });

    // Búsqueda de piezas en modal con debounce
    $('#modal-search-pieza').on('input', function () {
      clearTimeout(estado.piezaSearchTimer);
      const query = $(this).val().trim();
      const catalogo = $('#modal-select-catalogo').val();

      if (query.length < 2) {
        $('#modal-piezas-resultados').addClass('d-none').empty();
        return;
      }

      estado.piezaSearchTimer = setTimeout(function () {
        buscarPiezasDisponibles(query, catalogo);
      }, 300);
    });

    $('#modal-select-catalogo').on('change', function () {
      const query = $('#modal-search-pieza').val().trim();
      if (query.length >= 2) {
        buscarPiezasDisponibles(query, $(this).val());
      }
    });

    // Botón para cambiar pieza seleccionada
    $('#btn-cambiar-pieza').on('click', function () {
      deseleccionarPieza();
    });

    // Autocompletar origen si se selecciona museo registrado
    $('#select_museo_origen_catalogo').on('change', function () {
      const opt = $(this).find('option:selected');
      const val = $(this).val();
      if (val) {
        $('#input_lugar_origen').val(val);
        const resp = opt.data('resp') || '';
        if (resp) {
          $('#input_responsable_origen').val(resp);
        }
      }
    });

    // Autocompletar destino si se selecciona museo registrado
    $('#select_museo_destino_catalogo').on('change', function () {
      const opt = $(this).find('option:selected');
      const val = $(this).val();
      if (val) {
        $('#input_lugar_destino').val(val);
        const resp = opt.data('resp') || '';
        if (resp) {
          $('#input_responsable_destino').val(resp);
        }
      }
    });

    // Enviar formulario de nuevo movimiento
    $('#formNuevoMovimiento').on('submit', function (e) {
      e.preventDefault();
      guardarNuevoMovimiento();
    });

    // Enviar formulario de devolución
    $('#formDevolverPrestamo').on('submit', function (e) {
      e.preventDefault();
      guardarDevolucionPrestamo();
    });

    // Botón Imprimir expediente
    $('#btn-imprimir-expediente').on('click', function () {
      imprimirExpediente();
    });
  }

  // =========================================================================
  // CARGAR LISTA DE MOVIMIENTOS VÍA AJAX
  // =========================================================================
  function cargarMovimientos() {
    $('#loading-state-prestamos').removeClass('d-none');
    $('#empty-state-prestamos').addClass('d-none');
    $('#tbody-prestamos').empty();

    const params = {
      tipo_movimiento: estado.tipoMovimiento,
      estatus: estado.estatus,
      tipo_acervo: estado.tipoAcervo,
      search: estado.search,
      page: estado.page,
      per_page: estado.perPage
    };

    $.ajax({
      url: 'admin/get_prestamos_transferencias_data',
      type: 'GET',
      data: params,
      dataType: 'json',
      success: function (res) {
        $('#loading-state-prestamos').addClass('d-none');

        if (res.status === 200) {
          actualizarKPIs(res.kpis || {});
          renderizarTabla(res.data || []);
          renderizarPaginacion(res.total || 0, res.page || 1, res.per_page || 10, res.total_pages || 1);
        } else {
          toastr.error(res.msg || 'Error al obtener la lista de movimientos.');
        }
      },
      error: function (xhr, status, error) {
        $('#loading-state-prestamos').addClass('d-none');
        toastr.error('Error de conexión con el servidor al cargar movimientos.');
        console.error(error);
      }
    });
  }

  // =========================================================================
  // ACTUALIZACIÓN DE INDICADORES (KPIS)
  // =========================================================================
  function actualizarKPIs(kpis) {
    $('#kpi-total').text(kpis.total || 0);
    $('#kpi-activos').text(kpis.prestamos_activos || 0);
    $('#kpi-vencidos').text(kpis.prestamos_vencidos || 0);
    $('#kpi-devueltos').text(kpis.prestamos_devueltos || 0);
    $('#kpi-transferencias').text(kpis.transferencias || 0);
  }

  // =========================================================================
  // RENDERIZADO DE TABLA DE MOVIMIENTOS
  // =========================================================================
  function renderizarTabla(movimientos) {
    const $tbody = $('#tbody-prestamos');
    $tbody.empty();

    if (!movimientos || movimientos.length === 0) {
      $('#empty-state-prestamos').removeClass('d-none');
      $('#lbl-total-registros').text('0 registros');
      return;
    }

    $('#lbl-total-registros').text(movimientos.length + ' registros');

    movimientos.forEach(function (m) {
      const tr = document.createElement('tr');

      // Folio y Tipo
      const esPrestamo = (m.tipo_movimiento === 'prestamo');
      const badgeTipo = esPrestamo
        ? `<span class="badge bg-info-subtle text-info border border-info px-2 py-1"><i class='bx bx-time-five me-1'></i>Préstamo</span>`
        : `<span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1"><i class='bx bx-right-arrow-alt me-1'></i>Transferencia</span>`;

      // Foto de la pieza
      const fotoUrl = m.fotografia_url ? m.fotografia_url : 'assets/img/placeholder.png';

      // Badge de catálogo
      let badgeCat = `<span class="badge bg-secondary" style="font-size: 0.7rem;">General</span>`;
      if (m.tipo_acervo === 'arqueologico') {
        badgeCat = `<span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Arqueológico</span>`;
      } else if (m.tipo_acervo === 'numismatica') {
        badgeCat = `<span class="badge bg-success" style="font-size: 0.7rem;">Numismático</span>`;
      }

      // Estatus Badge
      let badgeEstatus = '';
      if (m.estatus === 'activo') {
        if (m.es_vencido) {
          badgeEstatus = `<span class="badge bg-danger text-white"><i class='bx bx-alarm-exclamation me-1'></i>Vencido</span>`;
        } else {
          badgeEstatus = `<span class="badge bg-warning text-dark"><i class='bx bx-time me-1'></i>Activo</span>`;
        }
      } else if (m.estatus === 'devuelto') {
        badgeEstatus = `<span class="badge bg-success"><i class='bx bx-check-double me-1'></i>Devuelto</span>`;
      } else if (m.estatus === 'completada') {
        badgeEstatus = `<span class="badge bg-primary"><i class='bx bx-check me-1'></i>Completada</span>`;
      } else if (m.estatus === 'cancelado') {
        badgeEstatus = `<span class="badge bg-secondary"><i class='bx bx-x me-1'></i>Cancelado</span>`;
      }

      // Fechas
      const fSalida = formatearFecha(m.fecha_salida);
      let htmlFechas = `<div><small class="text-muted">Salida:</small> <strong>${fSalida}</strong></div>`;
      if (esPrestamo) {
        if (m.estatus === 'devuelto' && m.fecha_devolucion_real) {
          htmlFechas += `<div><small class="text-success">Retorno:</small> <span class="text-success font-weight-bold">${formatearFecha(m.fecha_devolucion_real)}</span></div>`;
        } else if (m.fecha_devolucion_estimada) {
          const fEst = formatearFecha(m.fecha_devolucion_estimada);
          const colorClass = m.es_vencido ? 'text-danger font-weight-bold' : 'text-muted';
          htmlFechas += `<div><small class="${colorClass}">Plazo:</small> <span class="${colorClass}">${fEst}</span></div>`;
        }
      }

      // Botones de acción
      let htmlAcciones = `
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-outline-primary btn-ver-detalle" data-id="${m.id_movimiento}" title="Ver Expediente Completo">
            <i class='bx bx-file-blank'></i>
          </button>
      `;

      if (esPrestamo && m.estatus === 'activo') {
        htmlAcciones += `
          <button type="button" class="btn btn-outline-success btn-devolver-prestamo" data-id="${m.id_movimiento}" title="Registrar Devolución de la Pieza">
            <i class='bx bx-check-shield'></i> Devolver
          </button>
        `;
      }

      if (m.estatus === 'activo') {
        htmlAcciones += `
          <button type="button" class="btn btn-outline-danger btn-cancelar-movimiento" data-id="${m.id_movimiento}" title="Cancelar Movimiento">
            <i class='bx bx-trash'></i>
          </button>
        `;
      }

      htmlAcciones += `</div>`;

      // Origen ➔ Destino
      const htmlTrayecto = `
        <div class="small">
          <div class="d-flex align-items-center mb-1">
            <i class='bx bx-log-out-circle text-info me-1'></i>
            <span class="font-weight-bold text-gray-800">${escapeHtml(m.lugar_origen)}</span>
          </div>
          <div class="d-flex align-items-center">
            <i class='bx bx-log-in-circle text-primary me-1'></i>
            <span class="font-weight-bold text-primary">${escapeHtml(m.lugar_destino)}</span>
          </div>
          ${m.responsable_destino ? `<div class="text-muted" style="font-size: 0.72rem;">Receptor: ${escapeHtml(m.responsable_destino)}</div>` : ''}
        </div>
      `;

      tr.innerHTML = `
        <td class="ps-3 align-middle">
          <div class="font-monospace font-weight-bold text-primary">${escapeHtml(m.folio)}</div>
          <div class="mt-1">${badgeTipo}</div>
        </td>
        <td class="align-middle">
          <div class="d-flex align-items-center">
            <img src="${fotoUrl}" alt="Pieza" class="rounded border me-2 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='assets/img/placeholder.png'">
            <div>
              <div class="d-flex align-items-center gap-1 mb-1">
                ${badgeCat}
                <span class="font-weight-bold text-dark font-monospace" style="font-size: 0.8rem;">${escapeHtml(m.codigo_interno_pieza || 'S/C')}</span>
              </div>
              <div class="text-gray-900 font-weight-bold text-truncate" style="max-width: 220px;" title="${escapeHtml(m.titulo_pieza || '')}">
                ${escapeHtml(m.titulo_pieza || 'Sin título')}
              </div>
            </div>
          </div>
        </td>
        <td class="align-middle">${htmlTrayecto}</td>
        <td class="align-middle">${htmlFechas}</td>
        <td class="align-middle text-center">${badgeEstatus}</td>
        <td class="align-middle text-center pe-3">${htmlAcciones}</td>
      `;

      $tbody.append(tr);
    });

    // Enlazar eventos de botones de acción
    $('.btn-ver-detalle').on('click', function () {
      const id = $(this).data('id');
      mostrarDetalleMovimiento(id);
    });

    $('.btn-devolver-prestamo').on('click', function () {
      const id = $(this).data('id');
      abrirModalDevolucion(id);
    });

    $('.btn-cancelar-movimiento').on('click', function () {
      const id = $(this).data('id');
      confirmarCancelarMovimiento(id);
    });
  }

  // =========================================================================
  // PAGINACIÓN DINÁMICA
  // =========================================================================
  function renderizarPaginacion(total, page, perPage, totalPages) {
    const $ul = $('#paginacion-prestamos');
    $ul.empty();

    if (total === 0) {
      $('#info-paginacion').text('Mostrando 0 de 0 movimientos');
      return;
    }

    const start = (page - 1) * perPage + 1;
    const end = Math.min(page * perPage, total);
    $('#info-paginacion').text(`Mostrando ${start} a ${end} de ${total} movimientos`);

    if (totalPages <= 1) return;

    // Botón Anterior
    const liPrev = $(`<li class="page-item ${page === 1 ? 'disabled' : ''}"><a class="page-link" href="#"><i class='bx bx-chevron-left'></i></a></li>`);
    liPrev.on('click', function (e) {
      e.preventDefault();
      if (page > 1) {
        estado.page = page - 1;
        cargarMovimientos();
      }
    });
    $ul.append(liPrev);

    // Botones de Páginas numeradas
    let maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage + 1 < maxVisible) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }

    for (let p = startPage; p <= endPage; p++) {
      const li = $(`<li class="page-item ${p === page ? 'active' : ''}"><a class="page-link" href="#">${p}</a></li>`);
      li.on('click', function (e) {
        e.preventDefault();
        estado.page = p;
        cargarMovimientos();
      });
      $ul.append(li);
    }

    // Botón Siguiente
    const liNext = $(`<li class="page-item ${page === totalPages ? 'disabled' : ''}"><a class="page-link" href="#"><i class='bx bx-chevron-right'></i></a></li>`);
    liNext.on('click', function (e) {
      e.preventDefault();
      if (page < totalPages) {
        estado.page = page + 1;
        cargarMovimientos();
      }
    });
    $ul.append(liNext);
  }

  // =========================================================================
  // RESETEAR FILTROS
  // =========================================================================
  function resetearFiltros() {
    $('#filter-tipo-movimiento').val('todos');
    $('#filter-estatus').val('todos');
    $('#filter-tipo-acervo').val('todos');
    $('#filter-search').val('');
    estado.tipoMovimiento = 'todos';
    estado.estatus = 'todos';
    estado.tipoAcervo = 'todos';
    estado.search = '';
    estado.page = 1;
    cargarMovimientos();
  }

  // =========================================================================
  // MODAL NUEVO MOVIMIENTO
  // =========================================================================
  function abrirModalNuevoMovimiento() {
    $('#formNuevoMovimiento')[0].reset();
    deseleccionarPieza();
    $('#tipo_prestamo').prop('checked', true);
    actualizarCamposTipoMovimiento('prestamo');
    $('#input_fecha_salida').val(obtenerFechaHoy());
    $('#modalNuevoMovimiento').modal('show');
  }

  function actualizarCamposTipoMovimiento(tipo) {
    if (tipo === 'transferencia') {
      $('#wrapper-fecha-devolucion').addClass('d-none');
      $('#input_fecha_devolucion_estimada').prop('required', false).val('');
    } else {
      $('#wrapper-fecha-devolucion').removeClass('d-none');
      $('#input_fecha_devolucion_estimada').prop('required', true);
    }
  }

  // Búsqueda de piezas para asociar
  function buscarPiezasDisponibles(query, catalogo) {
    $.ajax({
      url: 'admin/buscar_piezas_para_movimiento',
      type: 'GET',
      data: { q: query, tipo_acervo: catalogo, limit: 15 },
      dataType: 'json',
      success: function (res) {
        if (res.status === 200) {
          renderizarResultadosPiezas(res.data || []);
        }
      }
    });
  }

  function renderizarResultadosPiezas(piezas) {
    const $container = $('#modal-piezas-resultados');
    $container.empty();

    if (!piezas || piezas.length === 0) {
      $container.html('<div class="p-3 text-center text-muted small">No se encontraron piezas aprobadas con ese término.</div>').removeClass('d-none');
      return;
    }

    piezas.forEach(function (p) {
      const fotoUrl = p.fotografia_url || 'assets/img/placeholder.png';
      const item = document.createElement('a');
      item.href = '#';
      item.className = 'list-group-item list-group-item-action p-2 d-flex align-items-center';

      let catBadge = `<span class="badge bg-secondary me-2">General</span>`;
      if (p.tipo_acervo === 'arqueologico') catBadge = `<span class="badge bg-warning text-dark me-2">Arqueológico</span>`;
      if (p.tipo_acervo === 'numismatica') catBadge = `<span class="badge bg-success me-2">Numismático</span>`;

      item.innerHTML = `
        <img src="${fotoUrl}" alt="Thumb" class="rounded border me-2" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='assets/img/placeholder.png'">
        <div class="flex-grow-1 overflow-hidden">
          <div class="d-flex align-items-center mb-1">
            ${catBadge}
            <strong class="font-monospace text-dark me-2">${escapeHtml(p.codigo_interno || 'S/C')}</strong>
            <small class="text-muted text-truncate">${escapeHtml(p.no_inventario || '')}</small>
          </div>
          <div class="text-dark font-weight-bold text-truncate" style="font-size: 0.85rem;">${escapeHtml(p.titulo || 'Sin título')}</div>
          <div class="text-muted" style="font-size: 0.72rem;"><i class='bx bx-map-pin text-danger'></i> Ubicación: ${escapeHtml(p.ubicacion_fisica || 'No especificada')}</div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm ms-2">Seleccionar</button>
      `;

      $(item).on('click', function (e) {
        e.preventDefault();
        seleccionarPieza(p);
      });

      $container.append(item);
    });

    $container.removeClass('d-none');
  }

  function seleccionarPieza(p) {
    estado.piezaSeleccionada = p;
    $('#input_tipo_acervo').val(p.tipo_acervo);
    $('#input_id_pieza').val(p.id_pieza);

    // Actualizar vista previa
    $('#sel-pieza-foto').attr('src', p.fotografia_url || 'assets/img/placeholder.png');
    $('#sel-pieza-codigo').text(p.codigo_interno || 'S/C');
    $('#sel-pieza-titulo').text(p.titulo || 'Sin título');
    $('#sel-pieza-ubicacion').text(p.ubicacion_fisica || 'No especificada');

    let catBadgeText = 'General';
    if (p.tipo_acervo === 'arqueologico') catBadgeText = 'Arqueológico';
    if (p.tipo_acervo === 'numismatica') catBadgeText = 'Numismático';
    $('#sel-pieza-badge-catalogo').text(catBadgeText);

    // Sugerir la ubicación física actual como lugar de origen
    if (p.ubicacion_fisica && !$('#input_lugar_origen').val()) {
      $('#input_lugar_origen').val(p.ubicacion_fisica);
    }

    $('#modal-pieza-seleccionada').removeClass('d-none');
    $('#modal-piezas-resultados').addClass('d-none').empty();
    $('#modal-search-pieza').val('').prop('disabled', true);
  }

  function deseleccionarPieza() {
    estado.piezaSeleccionada = null;
    $('#input_tipo_acervo').val('');
    $('#input_id_pieza').val('');
    $('#modal-pieza-seleccionada').addClass('d-none');
    $('#modal-search-pieza').val('').prop('disabled', false).focus();
  }

  // Guardar nuevo movimiento
  function guardarNuevoMovimiento() {
    if (!estado.piezaSeleccionada || !$('#input_id_pieza').val()) {
      Swal.fire({
        icon: 'warning',
        title: 'Pieza no seleccionada',
        text: 'Debe buscar y seleccionar una pieza válida del catálogo cultural.'
      });
      return;
    }

    const lugarOrigen = $('#input_lugar_origen').val().trim();
    const lugarDestino = $('#input_lugar_destino').val().trim();
    const motivo = $('#input_motivo').val().trim();
    const tipoMov = $('input[name="tipo_movimiento"]:checked').val();

    if (!lugarOrigen || !lugarDestino) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        text: 'El lugar de origen y el lugar de destino son obligatorios.'
      });
      return;
    }

    if (!motivo) {
      Swal.fire({
        icon: 'warning',
        title: 'Motivo requerido',
        text: 'Por favor proporcione la justificación o motivo institucional del movimiento.'
      });
      return;
    }

    if (tipoMov === 'prestamo' && !$('#input_fecha_devolucion_estimada').val()) {
      Swal.fire({
        icon: 'warning',
        title: 'Fecha estimada requerida',
        text: 'Para préstamos temporales es obligatoria la fecha estimada de devolución.'
      });
      return;
    }

    const formData = new FormData($('#formNuevoMovimiento')[0]);
    const $btn = $('#btn-guardar-movimiento');
    $btn.prop('disabled', true).html("<i class='bx bx-loader-alt bx-spin me-1'></i> Procesando...");

    $.ajax({
      url: 'admin/post_crear_movimiento',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (res) {
        $btn.prop('disabled', false).html("<i class='bx bx-check-circle me-1'></i> Confirmar y Registrar Movimiento");

        if (res.status === 201) {
          $('#modalNuevoMovimiento').modal('hide');
          Swal.fire({
            icon: 'success',
            title: '¡Movimiento Registrado!',
            html: `Se ha generado el folio institucional: <strong class="text-primary font-monospace fs-5">${res.folio}</strong>.<br>La ubicación física de la pieza ha sido actualizada.`,
            confirmButtonColor: '#4e73df'
          });
          cargarMovimientos();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error al registrar',
            text: res.msg || 'No se pudo completar el registro del movimiento.'
          });
        }
      },
      error: function (xhr, status, error) {
        $btn.prop('disabled', false).html("<i class='bx bx-check-circle me-1'></i> Confirmar y Registrar Movimiento");
        Swal.fire({
          icon: 'error',
          title: 'Error de servidor',
          text: 'Ocurrió un error inesperado al comunicarse con el servidor.'
        });
        console.error(error);
      }
    });
  }

  // =========================================================================
  // MODAL REGISTRAR DEVOLUCIÓN
  // =========================================================================
  function abrirModalDevolucion(id) {
    $.ajax({
      url: 'admin/get_detalle_movimiento',
      type: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function (res) {
        if (res.status === 200 && res.data) {
          const m = res.data;
          $('#formDevolverPrestamo')[0].reset();
          $('#dev_id_movimiento').val(m.id_movimiento);
          $('#dev_folio').text(m.folio);
          $('#dev_pieza_codigo').text(m.codigo_interno_pieza || 'S/C');
          $('#dev_pieza_titulo').text(m.titulo_pieza || 'Sin título');
          $('#dev_pieza_foto').attr('src', m.fotografia_url || 'assets/img/placeholder.png');
          $('#dev_lugar_destino').text(m.lugar_destino);
          $('#dev_fecha_salida').text(formatearFecha(m.fecha_salida));
          $('#dev_fecha_real').val(obtenerFechaHoy());
          $('#dev_lugar_reinstalacion').val(m.lugar_origen); // Sugiere volver a su origen original
          $('#modalDevolucionPrestamo').modal('show');
        } else {
          toastr.error('No se pudo obtener información del préstamo.');
        }
      }
    });
  }

  function guardarDevolucionPrestamo() {
    const lugar = $('#dev_lugar_reinstalacion').val().trim();
    const condiciones = $('#dev_condiciones').val().trim();

    if (!lugar || !condiciones) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        text: 'El lugar de reincorporación y la descripción de condiciones físicas al recibir son obligatorios.'
      });
      return;
    }

    const formData = new FormData($('#formDevolverPrestamo')[0]);
    const $btn = $('#btn-confirmar-devolucion');
    $btn.prop('disabled', true).html("<i class='bx bx-loader-alt bx-spin me-1'></i> Registrando...");

    $.ajax({
      url: 'admin/post_devolver_prestamo',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (res) {
        $btn.prop('disabled', false).html("<i class='bx bx-check-double me-1'></i> Registrar Devolución Concluida");

        if (res.status === 200) {
          $('#modalDevolucionPrestamo').modal('hide');
          Swal.fire({
            icon: 'success',
            title: '¡Devolución Concluida!',
            text: res.msg,
            confirmButtonColor: '#1cc88a'
          });
          cargarMovimientos();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error al registrar devolución',
            text: res.msg || 'No se pudo registrar la devolución.'
          });
        }
      },
      error: function (xhr, status, error) {
        $btn.prop('disabled', false).html("<i class='bx bx-check-double me-1'></i> Registrar Devolución Concluida");
        Swal.fire({
          icon: 'error',
          title: 'Error de servidor',
          text: 'Ocurrió un error inesperado al procesar la devolución.'
        });
        console.error(error);
      }
    });
  }

  // =========================================================================
  // DETALLE / EXPEDIENTE DEL MOVIMIENTO
  // =========================================================================
  function mostrarDetalleMovimiento(id) {
    $.ajax({
      url: 'admin/get_detalle_movimiento',
      type: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function (res) {
        if (res.status === 200 && res.data) {
          const m = res.data;
          $('#det-folio').text(m.folio);
          $('#det-folio-sub').text('Folio: ' + m.folio);

          // Badges de encabezado
          const esPrestamo = (m.tipo_movimiento === 'prestamo');
          $('#det-tipo-badge').text(esPrestamo ? 'Préstamo Temporal' : 'Transferencia Definitiva')
            .attr('class', esPrestamo ? 'badge bg-info' : 'badge bg-primary');

          let estatusTexto = 'Activo';
          let estatusClass = 'badge bg-warning text-dark';
          if (m.estatus === 'devuelto') {
            estatusTexto = 'Devuelto';
            estatusClass = 'badge bg-success';
          } else if (m.estatus === 'completada') {
            estatusTexto = 'Completada';
            estatusClass = 'badge bg-primary';
          } else if (m.estatus === 'cancelado') {
            estatusTexto = 'Cancelado';
            estatusClass = 'badge bg-secondary';
          } else if (m.es_vencido) {
            estatusTexto = 'Plazo Vencido';
            estatusClass = 'badge bg-danger';
          }
          $('#det-estatus-badge').text(estatusTexto).attr('class', estatusClass);

          // Fechas
          $('#det-fecha-salida').text(formatearFecha(m.fecha_salida));
          if (esPrestamo && m.fecha_devolucion_estimada) {
            $('#det-wrapper-estimada').removeClass('d-none');
            $('#det-fecha-estimada').text(formatearFecha(m.fecha_devolucion_estimada));
          } else {
            $('#det-wrapper-estimada').addClass('d-none');
          }

          if (m.estatus === 'devuelto' && m.fecha_devolucion_real) {
            $('#det-wrapper-real').removeClass('d-none');
            $('#det-fecha-real').text(formatearFecha(m.fecha_devolucion_real));
          } else {
            $('#det-wrapper-real').addClass('d-none');
          }

          // Pieza
          $('#det-pieza-foto').attr('src', m.fotografia_url || 'assets/img/placeholder.png');
          $('#det-pieza-titulo').text(m.titulo_pieza || 'Sin título');
          $('#det-pieza-codigo').text(m.codigo_interno_pieza || 'S/C');
          $('#det-pieza-catalogo').text(m.tipo_acervo.toUpperCase());
          $('#det-pieza-ubicacion').text((m.pieza_actual && m.pieza_actual.ubicacion_fisica) ? m.pieza_actual.ubicacion_fisica : '--');

          // Origen
          $('#det-lugar-origen').text(m.lugar_origen || '--');
          $('#det-tipo-origen').text(formatearTipoDependencia(m.tipo_origen));
          $('#det-responsable-origen').text(m.responsable_origen || 'No especificado');

          // Destino
          $('#det-lugar-destino').text(m.lugar_destino || '--');
          $('#det-tipo-destino').text(formatearTipoDependencia(m.tipo_destino));
          $('#det-responsable-destino').text(m.responsable_destino || 'No especificado');

          // Documentación
          $('#det-no-oficio').text(m.no_oficio_autorizacion || 'Sin oficio registrado');
          $('#det-usuario-registro').text(m.usuario_registro_nombre ? `${m.usuario_registro_nombre} (${formatearFecha(m.created_at)})` : '--');
          $('#det-motivo').text(m.motivo || 'No especificado');
          $('#det-condiciones-entrega').text(m.condiciones_entrega || 'Sin observaciones al momento de la entrega');
          $('#det-condiciones-devolucion').text(m.condiciones_devolucion || (m.estatus === 'devuelto' ? 'Recibido sin observaciones adicionales' : 'Pendiente de devolución'));
          $('#det-observaciones').text(m.observaciones || 'Sin observaciones registradas');

          $('#modalDetalleMovimiento').modal('show');
        } else {
          toastr.error('No se pudo cargar el detalle del movimiento.');
        }
      }
    });
  }

  // =========================================================================
  // CANCELAR MOVIMIENTO
  // =========================================================================
  function confirmarCancelarMovimiento(id) {
    Swal.fire({
      title: '¿Cancelar movimiento?',
      text: 'La cancelación revertirá la ubicación física de la pieza a su sede de origen original.',
      icon: 'warning',
      input: 'textarea',
      inputPlaceholder: 'Indique el motivo de la cancelación...',
      inputAttributes: {
        'aria-label': 'Motivo de la cancelación'
      },
      showCancelButton: true,
      confirmButtonColor: '#e74a3b',
      cancelButtonColor: '#858796',
      confirmButtonText: 'Sí, cancelar movimiento',
      cancelButtonText: 'No, mantener activo',
      inputValidator: (value) => {
        if (!value || !value.trim()) {
          return 'Debe ingresar una justificación para cancelar el movimiento.';
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: 'admin/post_cancelar_movimiento',
          type: 'POST',
          data: {
            id_movimiento: id,
            motivo_cancelacion: result.value
          },
          dataType: 'json',
          success: function (res) {
            if (res.status === 200) {
              Swal.fire('Cancelado', res.msg, 'success');
              cargarMovimientos();
            } else {
              Swal.fire('Error', res.msg || 'No se pudo cancelar el movimiento.', 'error');
            }
          },
          error: function () {
            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
          }
        });
      }
    });
  }

  // =========================================================================
  // IMPRIMIR EXPEDIENTE
  // =========================================================================
  function imprimirExpediente() {
    const printContents = document.getElementById('printable-expediente-body').innerHTML;
    const originalContents = document.body.innerHTML;

    const ventanaImpresion = window.open('', '_blank', 'height=700,width=900');
    ventanaImpresion.document.write('<html><head><title>Expediente Institucional de Movimiento</title>');
    ventanaImpresion.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">');
    ventanaImpresion.document.write('<style>body{padding:20px;font-family:sans-serif;} .badge{border:1px solid #ccc;color:#000!important;background:none!important;}</style>');
    ventanaImpresion.document.write('</head><body>');
    ventanaImpresion.document.write('<div class="text-center mb-4"><h4 class="font-weight-bold">SECRETARÍA DE CULTURA Y TURISMO</h4><h5>EXPEDIENTE OFICIAL DE PRÉSTAMO / TRANSFERENCIA DE BIEN CULTURAL</h5><hr></div>');
    ventanaImpresion.document.write(printContents);
    ventanaImpresion.document.write('<div class="mt-5 pt-4 text-center"><div class="row"><div class="col-6"><p>___________________________________<br><strong>Entrega Sede de Origen</strong><br>Firma y Sello</p></div><div class="col-6"><p>___________________________________<br><strong>Recibe Sede de Destino</strong><br>Firma y Sello</p></div></div></div>');
    ventanaImpresion.document.write('</body></html>');
    ventanaImpresion.document.close();
    ventanaImpresion.focus();
    setTimeout(function () {
      ventanaImpresion.print();
      ventanaImpresion.close();
    }, 400);
  }

  // =========================================================================
  // FUNCIONES AUXILIARES
  // =========================================================================
  function formatearFecha(fechaStr) {
    if (!fechaStr) return '--';
    const partes = fechaStr.split(' ')[0].split('-');
    if (partes.length === 3) {
      return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return fechaStr;
  }

  function obtenerFechaHoy() {
    const d = new Date();
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const dia = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${mes}-${dia}`;
  }

  function formatearTipoDependencia(tipo) {
    switch (tipo) {
      case 'museo': return 'Museo Institucional';
      case 'unidad': return 'Unidad Administrativa';
      case 'area_scyt': return 'Área / Dirección SCyT';
      case 'otra_dependencia': return 'Otra Dependencia de Gobierno';
      default: return tipo || 'Dependencia';
    }
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
});
