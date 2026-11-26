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
      sincronizarBotonesRapidos(estado.tipoMovimiento, estado.estatus);
      cargarMovimientos();
    });

    $('#filter-estatus').on('change', function () {
      estado.estatus = $(this).val();
      estado.page = 1;
      sincronizarBotonesRapidos(estado.tipoMovimiento, estado.estatus);
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

    // Click en botón del banner de alerta de vencimientos
    $('#btn-filtrar-vencidos-banner').on('click', function () {
      $('#filter-tipo-movimiento').val('prestamo');
      $('#filter-estatus').val('en_riesgo');
      estado.tipoMovimiento = 'prestamo';
      estado.estatus = 'en_riesgo';
      estado.page = 1;
      sincronizarBotonesRapidos('prestamo', 'en_riesgo');
      cargarMovimientos();
    });

    // Click en botones de filtro rápido (píldoras)
    $('.btn-filtro-rapido').on('click', function () {
      const tipo = $(this).data('tipo') || 'todos';
      const estatus = $(this).data('estatus') || 'todos';

      $('.btn-filtro-rapido').removeClass('active');
      $(this).addClass('active');

      $('#filter-tipo-movimiento').val(tipo);
      $('#filter-estatus').val(estatus);
      estado.tipoMovimiento = tipo;
      estado.estatus = estatus;
      estado.page = 1;
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

      sincronizarBotonesRapidos(estado.tipoMovimiento, estado.estatus);
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

    // Alternar visibilidad de campos origen y destino según tipo de dependencia
    $('#select_tipo_origen').on('change', function () {
      alternarCamposEntidad($(this).val(), 'origen');
    });

    $('#select_tipo_destino').on('change', function () {
      alternarCamposEntidad($(this).val(), 'destino');
    });

    // Autocompletar origen si se selecciona museo registrado
    $('#select_museo_origen_catalogo').on('change', function () {
      const opt = $(this).find('option:selected');
      const val = $(this).val();
      const idMuseo = opt.data('id') || '';
      $('#input_id_museo_origen').val(idMuseo);
      $('#input_lugar_origen').val(val || '');
      if (val) {
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
      const idMuseo = opt.data('id') || '';
      $('#input_id_museo_destino').val(idMuseo);
      $('#input_lugar_destino').val(val || '');
      if (val) {
        const resp = opt.data('resp') || '';
        if (resp) {
          $('#input_responsable_destino').val(resp);
        }
      }
    });

    // Manejo de cambio en el combo box de Colección
    $('#select_coleccion').on('change', function () {
      const val = $(this).val();
      if (val === '__otra__') {
        $('#wrapper-otra-coleccion').removeClass('d-none');
        $('#input_otra_coleccion').prop('required', true).focus();
        const otraVal = $('#input_otra_coleccion').val().trim();
        $('#sel-pieza-coleccion').text(otraVal || '-');
      } else {
        $('#wrapper-otra-coleccion').addClass('d-none');
        $('#input_otra_coleccion').prop('required', false).val('');
        $('#sel-pieza-coleccion').text(val || '-');
      }
    });

    // Sincronizar texto de colección personalizada con la vista previa
    $('#input_otra_coleccion').on('input', function () {
      if ($('#select_coleccion').val() === '__otra__') {
        const otraVal = $(this).val().trim();
        $('#sel-pieza-coleccion').text(otraVal || '-');
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

    // Abrir modal visor de oficio sin descarga automática
    $(document).on('click', '.btn-ver-oficio-modal', function (e) {
      e.preventDefault();
      const url = $(this).data('url');
      const archivo = $(this).data('archivo');
      const folio = $(this).data('folio');
      abrirVisorOficio(url, archivo, folio);
    });

    // Limpiar visor de oficio al cerrar modal y preservar scroll en modal de detalle si estaba abierto
    $('#modalVisorOficio').on('hidden.bs.modal', function () {
      $('#visor-iframe-pdf').attr('src', '');
      $('#visor-img-preview').attr('src', '');
      if ($('#modalDetalleMovimiento').hasClass('show')) {
        $('body').addClass('modal-open');
      }
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
          if (res.user_context) {
            if (res.user_context.is_super_admin !== undefined) {
              $('#auth_user_is_super_admin').val(res.user_context.is_super_admin ? '1' : '0');
            }
            if (res.user_context.id_museo !== undefined) {
              $('#auth_user_museum_id').val(res.user_context.id_museo || '');
            }
            if (res.user_context.nombre_museo !== undefined) {
              $('#auth_user_museum_name').val(res.user_context.nombre_museo || '');
            }
          }
          actualizarKPIs(res.kpis || {});
          renderizarTabla(res.data || [], res.total || 0);
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
    const total = kpis.total || 0;
    const pendientes = kpis.pendientes_aprobacion || 0;
    const activos = kpis.prestamos_activos || 0;
    const porVencer = kpis.prestamos_por_vencer || 0;
    const vencidos = kpis.prestamos_vencidos || 0;
    const devueltos = kpis.prestamos_devueltos || 0;
    const transferencias = kpis.transferencias || 0;

    $('#kpi-total').text(total);
    $('#kpi-pendientes').text(pendientes);
    $('#kpi-activos').text(activos);
    $('#kpi-por-vencer').text(porVencer);
    $('#kpi-vencidos').text(vencidos);
    $('#kpi-devueltos').text(devueltos);
    $('#kpi-transferencias').text(transferencias);

    // Banner de alerta de préstamos en riesgo
    const $banner = $('#banner-alerta-vencimientos');
    const totalEnRiesgo = vencidos + porVencer;

    if (totalEnRiesgo > 0) {
      $('#banner-conteo-vencidos').text(vencidos);
      $('#banner-conteo-por-vencer').text(porVencer);

      if (vencidos > 0 && porVencer > 0) {
        $('#banner-titulo-alerta').html("<i class='bx bx-alarm-exclamation me-1'></i> ¡Atención urgente! Se detectaron préstamos vencidos y por vencer");
      } else if (vencidos > 0) {
        $('#banner-titulo-alerta').html("<i class='bx bx-alarm-exclamation me-1'></i> ¡Atención urgente! Se detectaron préstamos con plazo de devolución vencido");
      } else {
        $('#banner-titulo-alerta').html("<i class='bx bx-time me-1'></i> Recordatorio preventivo: Préstamos próximos a vencer en los siguientes 7 días");
      }

      $banner.removeClass('d-none').addClass('d-flex');
    } else {
      $banner.addClass('d-none').removeClass('d-flex');
    }
  }

  // =========================================================================
  // RENDERIZADO DE TABLA DE MOVIMIENTOS
  // =========================================================================
  function renderizarTabla(movimientos, total) {
    const $tbody = $('#tbody-prestamos');
    $tbody.empty();

    if (!movimientos || movimientos.length === 0) {
      $('#empty-state-prestamos').removeClass('d-none');
      $('#lbl-total-registros').text('0 registros');
      return;
    }

    const totalMostrar = (total !== undefined && total !== null) ? total : movimientos.length;
    $('#lbl-total-registros').text(totalMostrar + ' registros');

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

      // Estatus Badge con semáforo institucional
      let badgeEstatus = '';
      if (m.estatus === 'pendiente_aprobacion') {
        badgeEstatus = `<span class="badge bg-warning text-dark"><i class='bx bx-time me-1'></i>Pendiente</span>`;
      } else if (m.estatus === 'activo') {
        if (m.es_vencido) {
          const txtVenc = (m.dias_vencido > 0) ? `+${m.dias_vencido}d` : 'Hoy';
          badgeEstatus = `<span class="badge bg-danger text-white pulse-danger d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1" title="Plazo agotado hace ${m.dias_vencido} día(s)"><i class='bx bx-alarm-exclamation'></i>Vencido (${txtVenc})</span>`;
        } else if (m.es_por_vencer) {
          const txtPv = (m.dias_restantes === 0) ? 'Vence hoy' : (m.dias_restantes === 1 ? 'Vence mañana' : `Vence en ${m.dias_restantes}d`);
          badgeEstatus = `<span class="badge text-dark pulse-warning d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1" style="background-color: #ffc107;" title="Préstamo próximo a vencer"><i class='bx bx-time'></i>${txtPv}</span>`;
        } else {
          const txtEnTiempo = (m.dias_restantes !== null && m.dias_restantes !== undefined) ? ` (${m.dias_restantes}d)` : '';
          badgeEstatus = `<span class="badge bg-info text-white d-inline-flex align-items-center gap-1 px-2 py-1"><i class='bx bx-check-circle'></i>Activo${txtEnTiempo}</span>`;
        }
      } else if (m.estatus === 'devuelto') {
        badgeEstatus = `<span class="badge bg-success"><i class='bx bx-check-double me-1'></i>Devuelto</span>`;
      } else if (m.estatus === 'completada') {
        badgeEstatus = `<span class="badge bg-primary"><i class='bx bx-check me-1'></i>Completada</span>`;
      } else if (m.estatus === 'declinado') {
        badgeEstatus = `<span class="badge bg-danger"><i class='bx bx-x-circle me-1'></i>Declinado</span>`;
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
          if (m.es_vencido) {
            htmlFechas += `<div><span class="badge bg-danger-subtle text-danger border border-danger px-1 py-0" style="font-size: 0.73rem;"><i class='bx bx-alarm-exclamation me-1'></i>Venció: ${fEst}</span></div>`;
          } else if (m.es_por_vencer) {
            htmlFechas += `<div><span class="badge bg-warning-subtle text-dark border border-warning px-1 py-0" style="font-size: 0.73rem;"><i class='bx bx-time me-1'></i>Plazo: ${fEst}</span></div>`;
          } else {
            htmlFechas += `<div><small class="text-muted">Plazo:</small> <span class="text-muted">${fEst}</span></div>`;
          }
        }
      }

      // Dropdown de acciones
      let opcionesDropdown = `
        <li>
          <a class="dropdown-item btn-ver-detalle" href="javascript:void(0);" data-id="${m.id_movimiento}">
            <i class='bx text-primary bx__iconmenu bx-eye-alt'></i> Ver Expediente
          </a>
        </li>
      `;

      if (m.archivo_oficio_url) {
        opcionesDropdown += `
          <li>
            <a class="dropdown-item btn-ver-oficio-modal" href="javascript:void(0);" data-url="${escapeHtml(m.archivo_oficio_url)}" data-archivo="${escapeHtml(m.archivo_oficio || 'Oficio')}" data-folio="${escapeHtml(m.folio)}">
              <i class='bx text-info bx__iconmenu bx-file'></i> Ver Oficio Adjunto
            </a>
          </li>
        `;
      }

      if (m.estatus === 'pendiente_aprobacion' && m.puede_dictaminar) {
        opcionesDropdown += `
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item btn-aceptar-movimiento text-success font-weight-bold" href="javascript:void(0);" data-id="${m.id_movimiento}">
              <i class='bx text-success bx__iconmenu bx-check-circle'></i> Aceptar Movimiento
            </a>
          </li>
          <li>
            <a class="dropdown-item btn-declinar-movimiento text-danger font-weight-bold" href="javascript:void(0);" data-id="${m.id_movimiento}">
              <i class='bx text-danger bx__iconmenu bx-x-circle'></i> Declinar Movimiento
            </a>
          </li>
        `;
      }

      if (esPrestamo && m.estatus === 'activo') {
        opcionesDropdown += `
          <li>
            <a class="dropdown-item btn-devolver-prestamo text-success" href="javascript:void(0);" data-id="${m.id_movimiento}">
              <i class='bx text-success bx__iconmenu bx-undo'></i> Registrar Devolución
            </a>
          </li>
        `;
      }

      if (m.estatus === 'activo' || m.estatus === 'pendiente_aprobacion') {
        opcionesDropdown += `
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item btn-cancelar-movimiento text-danger" href="javascript:void(0);" data-id="${m.id_movimiento}">
              <i class='bx text-danger bx__iconmenu bx-trash'></i> Cancelar Movimiento
            </a>
          </li>
        `;
      }

      const htmlAcciones = `
        <div class="dropdown">
          <button class="btn btn__actions btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="Acciones">
            <i class='bx bx-caret-down'></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            ${opcionesDropdown}
          </ul>
        </div>
      `;

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
    $('.btn-ver-detalle').on('click', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      mostrarDetalleMovimiento(id);
    });

    $('.btn-aceptar-movimiento').on('click', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      confirmarAceptarMovimiento(id);
    });

    $('.btn-declinar-movimiento').on('click', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      confirmarDeclinarMovimiento(id);
    });

    $('.btn-devolver-prestamo').on('click', function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      abrirModalDevolucion(id);
    });

    $('.btn-cancelar-movimiento').on('click', function (e) {
      e.preventDefault();
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
    sincronizarBotonesRapidos('todos', 'todos');
    cargarMovimientos();
  }

  function sincronizarBotonesRapidos(tipo, estatus) {
    $('.btn-filtro-rapido').removeClass('active');
    const $target = $(`.btn-filtro-rapido[data-tipo="${tipo}"][data-estatus="${estatus}"]`);
    if ($target.length) {
      $target.addClass('active');
    }
  }

  // =========================================================================
  // CONTROL DINÁMICO DE ORIGEN Y DESTINO (MUSEO VS OTRA DEPENDENCIA)
  // =========================================================================
  function alternarCamposEntidad(tipoEntidad, campo) {
    const esMuseo = (tipoEntidad === 'museo');
    const $wrapperMuseo = $('#wrapper-museo-' + campo);
    const $selectMuseo = $('#select_museo_' + campo + '_catalogo');
    const $wrapperLugar = $('#wrapper-lugar-' + campo);
    const $inputLugar = $('#input_lugar_' + campo);
    const $inputIdMuseo = $('#input_id_museo_' + campo);

    if (esMuseo) {
      $wrapperMuseo.removeClass('d-none').show();
      $wrapperLugar.addClass('d-none').hide();
      $inputLugar.prop('required', false);
      $selectMuseo.prop('required', true);

      // Si ya hay museo seleccionado en el combo, sincronizar
      const selVal = $selectMuseo.val();
      if (selVal) {
        $inputLugar.val(selVal);
        const opt = $selectMuseo.find('option:selected');
        $inputIdMuseo.val(opt.data('id') || '');
      }
    } else {
      $wrapperMuseo.addClass('d-none').hide();
      $wrapperLugar.removeClass('d-none').show();
      $selectMuseo.prop('required', false).val('');
      $inputIdMuseo.val('');
      $inputLugar.prop('required', true);
    }
  }

  // =========================================================================
  // MODAL NUEVO MOVIMIENTO
  // =========================================================================
  function abrirModalNuevoMovimiento() {
    $('#formNuevoMovimiento')[0].reset();
    deseleccionarPieza();
    $('#select_tipo_origen').val('museo');
    alternarCamposEntidad('museo', 'origen');
    $('#select_tipo_destino').val('museo');
    alternarCamposEntidad('museo', 'destino');
    $('#select_museo_origen_catalogo').val('');
    $('#select_museo_destino_catalogo').val('');
    $('#input_id_museo_origen').val('');
    $('#input_id_museo_destino').val('');
    $('#input_lugar_origen').val('');
    $('#input_lugar_destino').val('');
    $('#input_responsable_origen').val('');
    $('#input_responsable_destino').val('');
    $('#input_archivo_oficio').val('');
    $('#badge-origen-autodetectado').addClass('d-none');

    // Si el usuario es administrador de sede, sugerir por defecto su museo asignado
    const isSuperAdmin = $('#auth_user_is_super_admin').val() === '1';
    const authMuseumName = ($('#auth_user_museum_name').val() || '').trim();
    const authMuseumId = ($('#auth_user_museum_id').val() || '').trim();
    if (!isSuperAdmin && authMuseumName) {
      $('#select_tipo_origen').val('museo');
      alternarCamposEntidad('museo', 'origen');
      $('#select_museo_origen_catalogo').val(authMuseumName).trigger('change');
      if (authMuseumId) {
        $('#input_id_museo_origen').val(authMuseumId);
      }
    }

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

    let coleccionTexto = (p.coleccion || '').trim();
    if (!coleccionTexto) {
      if (p.tipo_acervo === 'general') coleccionTexto = 'Acervo General';
      else if (p.tipo_acervo === 'arqueologico') coleccionTexto = 'Acervo Arqueológico';
      else if (p.tipo_acervo === 'numismatica') coleccionTexto = 'Acervo Numismático';
    }
    $('#sel-pieza-coleccion').text(coleccionTexto || '-');

    // =======================================================================
    // RECUPERACIÓN AUTOMÁTICA DE LA COLECCIÓN EN EL COMBO BOX
    // =======================================================================
    let coleccionEncontrada = false;
    $('#select_coleccion option').each(function () {
      const optVal = ($(this).val() || '').trim();
      if (optVal && optVal.toLowerCase() === coleccionTexto.toLowerCase()) {
        $('#select_coleccion').val(optVal);
        coleccionEncontrada = true;
        return false;
      }
    });

    if (!coleccionEncontrada && coleccionTexto !== '') {
      // Si la colección de la pieza no existía en las opciones, se agrega al combo box y se selecciona
      const nuevaOpcion = new Option(coleccionTexto, coleccionTexto, true, true);
      const $optOtra = $('#select_coleccion option[value="__otra__"]');
      if ($optOtra.length > 0) {
        $optOtra.before(nuevaOpcion);
      } else {
        $('#select_coleccion').append(nuevaOpcion);
      }
      $('#select_coleccion').val(coleccionTexto);
    } else if (!coleccionTexto) {
      $('#select_coleccion').val('');
    }

    // Ocultar campo de otra colección si estuviera visible
    $('#wrapper-otra-coleccion').addClass('d-none');
    $('#input_otra_coleccion').prop('required', false).val('');

    // Mostrar badge indicando la recuperación automática de la colección
    if (coleccionTexto) {
      $('#badge-coleccion-recuperada')
        .removeClass('d-none')
        .html("<i class='bx bx-check-circle me-1'></i> Colección recuperada: <strong>" + escapeHtml(coleccionTexto) + "</strong>");
    } else {
      $('#badge-coleccion-recuperada').addClass('d-none');
    }

    // =======================================================================
    // RECUPERACIÓN AUTOMÁTICA DEL ORIGEN DE LA PIEZA
    // =======================================================================
    const orig = p.origen_detectado;
    if (orig && orig.tipo_origen === 'museo' && orig.nombre_museo) {
      $('#select_tipo_origen').val('museo');
      alternarCamposEntidad('museo', 'origen');

      // Seleccionar el museo en el combo por ID o por nombre
      let selEncontrado = false;
      if (orig.id_museo) {
        $('#select_museo_origen_catalogo option').each(function () {
          if (parseInt($(this).data('id'), 10) === parseInt(orig.id_museo, 10)) {
            $('#select_museo_origen_catalogo').val($(this).val());
            selEncontrado = true;
            return false;
          }
        });
      }
      if (!selEncontrado) {
        $('#select_museo_origen_catalogo').val(orig.nombre_museo);
      }

      $('#input_id_museo_origen').val(orig.id_museo || '');
      $('#input_lugar_origen').val(orig.nombre_museo);
      if (orig.responsable_origen) {
        $('#input_responsable_origen').val(orig.responsable_origen);
      }

      $('#badge-origen-autodetectado')
        .removeClass('d-none')
        .html("<i class='bx bx-check-circle me-1'></i> Origen recuperado: <strong>" + escapeHtml(orig.nombre_museo) + "</strong>");
    } else if (orig && orig.tipo_origen !== 'museo' && orig.lugar_origen) {
      $('#select_tipo_origen').val(orig.tipo_origen || 'unidad');
      alternarCamposEntidad(orig.tipo_origen || 'unidad', 'origen');

      $('#select_museo_origen_catalogo').val('');
      $('#input_id_museo_origen').val('');
      $('#input_lugar_origen').val(orig.lugar_origen);
      $('#badge-origen-autodetectado')
        .removeClass('d-none')
        .html("<i class='bx bx-check-circle me-1'></i> Origen recuperado: <strong>" + escapeHtml(orig.lugar_origen) + "</strong>");
    } else if (p.ubicacion_fisica) {
      // Fallback si no viniera origen_detectado: búsqueda inteligente en las opciones
      const locClean = (p.ubicacion_fisica || '').trim().toLowerCase();
      let encontrado = false;

      $('#select_museo_origen_catalogo option').each(function () {
        const val = ($(this).val() || '').trim().toLowerCase();
        if (val && (val === locClean || locClean.indexOf(val) !== -1 || val.indexOf(locClean) !== -1)) {
          $('#select_tipo_origen').val('museo');
          alternarCamposEntidad('museo', 'origen');
          $('#select_museo_origen_catalogo').val($(this).val()).trigger('change');
          encontrado = true;
          return false;
        }
      });

      if (!encontrado) {
        $('#select_tipo_origen').val('unidad');
        alternarCamposEntidad('unidad', 'origen');
        $('#select_museo_origen_catalogo').val('');
        $('#input_id_museo_origen').val('');
        $('#input_lugar_origen').val(p.ubicacion_fisica);
      }

      $('#badge-origen-autodetectado')
        .removeClass('d-none')
        .html("<i class='bx bx-check-circle me-1'></i> Origen recuperado: <strong>" + escapeHtml(p.ubicacion_fisica) + "</strong>");
    }

    $('#modal-pieza-seleccionada').removeClass('d-none');
    $('#modal-piezas-resultados').addClass('d-none').empty();
    $('#modal-search-pieza').val('').prop('disabled', true);
  }

  function deseleccionarPieza() {
    estado.piezaSeleccionada = null;
    $('#input_tipo_acervo').val('');
    $('#input_id_pieza').val('');
    $('#sel-pieza-coleccion').text('-');
    $('#select_coleccion').val('');
    $('#badge-coleccion-recuperada').addClass('d-none');
    $('#wrapper-otra-coleccion').addClass('d-none');
    $('#input_otra_coleccion').prop('required', false).val('');
    $('#modal-pieza-seleccionada').addClass('d-none');
    $('#badge-origen-autodetectado').addClass('d-none');
    $('#select_museo_origen_catalogo').val('');
    $('#input_id_museo_origen').val('');
    $('#input_lugar_origen').val('');
    $('#input_responsable_origen').val('');
    $('#select_tipo_origen').val('museo');
    alternarCamposEntidad('museo', 'origen');
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

    // Validar origen según tipo de dependencia
    const tipoOrigen = $('#select_tipo_origen').val();
    let lugarOrigen = '';
    if (tipoOrigen === 'museo') {
      const nomMuseoOrig = $('#select_museo_origen_catalogo').val();
      if (!nomMuseoOrig) {
        Swal.fire({
          icon: 'warning',
          title: 'Museo de origen requerido',
          text: 'Por favor seleccione el museo de origen de la pieza.'
        });
        return;
      }
      lugarOrigen = nomMuseoOrig;
      $('#input_lugar_origen').val(nomMuseoOrig);
    } else {
      lugarOrigen = $('#input_lugar_origen').val().trim();
      if (!lugarOrigen) {
        Swal.fire({
          icon: 'warning',
          title: 'Sede de origen requerida',
          text: 'Por favor ingrese el nombre del lugar o sede de origen.'
        });
        return;
      }
    }

    // Validar destino según tipo de dependencia
    const tipoDestino = $('#select_tipo_destino').val();
    let lugarDestino = '';
    if (tipoDestino === 'museo') {
      const nomMuseoDest = $('#select_museo_destino_catalogo').val();
      if (!nomMuseoDest) {
        Swal.fire({
          icon: 'warning',
          title: 'Museo de destino requerido',
          text: 'Por favor seleccione el museo institucional de destino.'
        });
        return;
      }
      lugarDestino = nomMuseoDest;
      $('#input_lugar_destino').val(nomMuseoDest);
    } else {
      lugarDestino = $('#input_lugar_destino').val().trim();
      if (!lugarDestino) {
        Swal.fire({
          icon: 'warning',
          title: 'Sede de destino requerida',
          text: 'Por favor ingrese el nombre del lugar o sede de destino.'
        });
        return;
      }
    }

    // Validar que origen y destino no sean el mismo museo
    if (tipoOrigen === 'museo' && tipoDestino === 'museo') {
      const idOrig = $('#input_id_museo_origen').val();
      const idDest = $('#input_id_museo_destino').val();
      if (idOrig && idDest && String(idOrig) === String(idDest)) {
        Swal.fire({
          icon: 'warning',
          title: 'Origen y Destino Idénticos',
          text: 'El museo de destino debe ser diferente al museo de origen.'
        });
        return;
      }
    }

    const motivo = $('#input_motivo').val().trim();
    const tipoMov = $('input[name="tipo_movimiento"]:checked').val();

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

    const fileOficio = document.getElementById('input_archivo_oficio');
    if (!fileOficio || !fileOficio.files || fileOficio.files.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Oficio obligatorio',
        text: 'Debe adjuntar obligatoriamente el oficio o documento de autorización del movimiento (.pdf, .doc, .docx, .jpg, .png).'
      });
      return;
    }

    // Validar colección seleccionada
    let coleccionFinal = $('#select_coleccion').val();
    if (!coleccionFinal) {
      Swal.fire({
        icon: 'warning',
        title: 'Colección requerida',
        text: 'Por favor seleccione la colección a la que pertenece la pieza.'
      });
      return;
    }
    if (coleccionFinal === '__otra__') {
      coleccionFinal = $('#input_otra_coleccion').val().trim();
      if (!coleccionFinal) {
        Swal.fire({
          icon: 'warning',
          title: 'Colección personalizada requerida',
          text: 'Por favor escriba el nombre de la colección.'
        });
        return;
      }
    }

    const formData = new FormData($('#formNuevoMovimiento')[0]);
    formData.set('coleccion', coleccionFinal);
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
            html: `Se ha generado el folio institucional: <strong class="text-primary font-monospace fs-5">${res.folio}</strong>.<br>El movimiento ha quedado en estado <strong>Pendiente de Aprobación</strong> por el museo de destino.`,
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
          let estatusClass = 'badge bg-info text-white';
          if (m.estatus === 'pendiente_aprobacion') {
            estatusTexto = 'Pendiente de Aprobación';
            estatusClass = 'badge bg-warning text-dark';
          } else if (m.estatus === 'declinado') {
            estatusTexto = 'Declinado';
            estatusClass = 'badge bg-danger text-white';
          } else if (m.estatus === 'devuelto') {
            estatusTexto = 'Devuelto';
            estatusClass = 'badge bg-success text-white';
          } else if (m.estatus === 'completada') {
            estatusTexto = 'Completada';
            estatusClass = 'badge bg-primary text-white';
          } else if (m.estatus === 'cancelado') {
            estatusTexto = 'Cancelado';
            estatusClass = 'badge bg-secondary text-white';
          } else if (m.es_vencido) {
            estatusTexto = 'Plazo Vencido (' + (m.dias_vencido > 0 ? '+' + m.dias_vencido + ' días' : 'Hoy') + ')';
            estatusClass = 'badge bg-danger text-white pulse-danger';
          } else if (m.es_por_vencer) {
            estatusTexto = 'Por Vencer (' + (m.dias_restantes === 0 ? 'Vence hoy' : (m.dias_restantes === 1 ? 'Vence mañana' : m.dias_restantes + ' días restantes')) + ')';
            estatusClass = 'badge text-dark pulse-warning';
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
          $('#det-pieza-coleccion').text(m.coleccion || '--');
          $('#det-pieza-ubicacion').text((m.pieza_actual && m.pieza_actual.ubicacion_fisica) ? m.pieza_actual.ubicacion_fisica : '--');

          // Origen
          $('#det-lugar-origen').text(m.lugar_origen || '--');
          $('#det-tipo-origen').text(formatearTipoDependencia(m.tipo_origen));
          $('#det-responsable-origen').text(m.responsable_origen || 'No especificado');

          // Destino
          $('#det-lugar-destino').text(m.lugar_destino || '--');
          $('#det-tipo-destino').text(formatearTipoDependencia(m.tipo_destino));
          $('#det-responsable-destino').text(m.responsable_destino || 'No especificado');

          // Documentación y Oficio
          $('#det-no-oficio').text(m.no_oficio_autorizacion || 'Sin oficio registrado');
          $('#det-usuario-registro').text(m.usuario_registro_nombre ? `${m.usuario_registro_nombre} (${formatearFecha(m.created_at)})` : '--');

          if (m.archivo_oficio_url) {
            $('#det-oficio-container').html(`
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-primary btn-sm btn-ver-oficio-modal shadow-sm" data-url="${escapeHtml(m.archivo_oficio_url)}" data-archivo="${escapeHtml(m.archivo_oficio || 'Oficio')}" data-folio="${escapeHtml(m.folio)}">
                  <i class='bx bx-show me-1'></i> Ver Oficio en Pantalla
                </button>
                <a href="${escapeHtml(m.archivo_oficio_url)}" download="${escapeHtml(m.archivo_oficio || 'Oficio')}" class="btn btn-outline-secondary btn-sm">
                  <i class='bx bx-download me-1'></i> Descargar Archivo (${escapeHtml(m.archivo_oficio || 'Oficio')})
                </a>
              </div>
            `);
          } else {
            $('#det-oficio-container').html('<span class="text-muted small">Sin documento digital adjunto</span>');
          }

          $('#det-motivo').text(m.motivo || 'No especificado');
          $('#det-condiciones-entrega').text(m.condiciones_entrega || 'Sin observaciones al momento de la entrega');
          $('#det-condiciones-devolucion').text(m.condiciones_devolucion || (m.estatus === 'devuelto' ? 'Recibido sin observaciones adicionales' : 'Pendiente de devolución'));
          $('#det-observaciones').text(m.observaciones || 'Sin observaciones registradas');

          // Dictamen de Sede Receptora
          if (m.estatus === 'declinado' || m.fecha_dictamen) {
            $('#det-card-dictamen').removeClass('d-none');
            $('#det-dictamen-usuario').text(m.usuario_dictamen_nombre || 'Administrador Receptor');
            $('#det-dictamen-fecha').text(formatearFecha(m.fecha_dictamen));

            if (m.estatus === 'declinado') {
              $('#det-dictamen-badge').text('Declinado').attr('class', 'badge bg-danger');
              $('#det-wrapper-motivo-declinacion').removeClass('d-none');
              $('#det-motivo-declinacion').text(m.motivo_declinacion || 'Sin motivo registrado');
            } else {
              $('#det-dictamen-badge').text('Aceptado').attr('class', 'badge bg-success');
              $('#det-wrapper-motivo-declinacion').addClass('d-none');
            }

            if (m.observaciones_dictamen) {
              $('#det-wrapper-obs-dictamen').removeClass('d-none');
              $('#det-observaciones-dictamen').text(m.observaciones_dictamen);
            } else {
              $('#det-wrapper-obs-dictamen').addClass('d-none');
            }
          } else {
            $('#det-card-dictamen').addClass('d-none');
          }

          // Botones de Dictamen en el Footer del Modal
          if (m.estatus === 'pendiente_aprobacion' && m.puede_dictaminar) {
            $('#det-acciones-dictamen').removeClass('d-none').addClass('d-flex');
            $('#btn-detalle-aceptar').off('click').on('click', function () {
              confirmarAceptarMovimiento(m.id_movimiento, true);
            });
            $('#btn-detalle-declinar').off('click').on('click', function () {
              confirmarDeclinarMovimiento(m.id_movimiento, true);
            });
          } else {
            $('#det-acciones-dictamen').addClass('d-none').removeClass('d-flex');
          }

          $('#modalDetalleMovimiento').modal('show');
        } else {
          toastr.error('No se pudo cargar el detalle del movimiento.');
        }
      }
    });
  }

  // =========================================================================
  // ACEPTAR MOVIMIENTO (DICTAMEN SEDE DESTINO)
  // =========================================================================
  function confirmarAceptarMovimiento(id, cerrarModalDetalle) {
    Swal.fire({
      title: '¿Aceptar préstamo o transferencia?',
      text: 'Al aceptar, el movimiento quedará formalmente aprobado y la pieza se reubicará a la sede de destino.',
      icon: 'question',
      input: 'text',
      inputPlaceholder: 'Observaciones de aceptación (opcional)...',
      showCancelButton: true,
      confirmButtonColor: '#198754',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bx bx-check-circle me-1"></i> Sí, Aceptar Movimiento',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Procesando dictamen...',
          text: 'Por favor espere mientras se valida y actualiza la ubicación de la pieza.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.ajax({
          url: 'admin/post_aceptar_movimiento',
          type: 'POST',
          data: {
            id_movimiento: id,
            observaciones_dictamen: result.value || ''
          },
          dataType: 'json',
          success: function (res) {
            if (res.status === 200) {
              if (cerrarModalDetalle) {
                $('#modalDetalleMovimiento').modal('hide');
              }
              Swal.fire({
                icon: 'success',
                title: '¡Movimiento Aceptado!',
                text: res.msg,
                confirmButtonColor: '#198754'
              });
              cargarMovimientos();
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error al aceptar',
                text: res.msg || 'No se pudo procesar la aceptación del movimiento.'
              });
            }
          },
          error: function (xhr, status, error) {
            Swal.fire({
              icon: 'error',
              title: 'Error de servidor',
              text: 'Ocurrió un error inesperado al comunicarse con el servidor.'
            });
            console.error(error);
          }
        });
      }
    });
  }

  // =========================================================================
  // DECLINAR MOVIMIENTO (DICTAMEN SEDE DESTINO)
  // =========================================================================
  function confirmarDeclinarMovimiento(id, cerrarModalDetalle) {
    Swal.fire({
      title: '¿Declinar préstamo o transferencia?',
      text: 'Indique obligatoriamente el motivo de la declinación. La pieza permanecerá resguardada en su sede de origen.',
      icon: 'warning',
      input: 'textarea',
      inputPlaceholder: 'Escriba la justificación institucional de la declinación...',
      inputAttributes: {
        'aria-label': 'Motivo de la declinación'
      },
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bx bx-x-circle me-1"></i> Sí, Declinar Movimiento',
      cancelButtonText: 'Cancelar',
      inputValidator: (value) => {
        if (!value || !value.trim()) {
          return 'Debe ingresar obligatoriamente el motivo de la declinación.';
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Procesando dictamen...',
          text: 'Por favor espere...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.ajax({
          url: 'admin/post_declinar_movimiento',
          type: 'POST',
          data: {
            id_movimiento: id,
            motivo_declinacion: result.value
          },
          dataType: 'json',
          success: function (res) {
            if (res.status === 200) {
              if (cerrarModalDetalle) {
                $('#modalDetalleMovimiento').modal('hide');
              }
              Swal.fire({
                icon: 'success',
                title: 'Movimiento Declinado',
                text: res.msg,
                confirmButtonColor: '#6c757d'
              });
              cargarMovimientos();
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error al declinar',
                text: res.msg || 'No se pudo procesar la declinación del movimiento.'
              });
            }
          },
          error: function (xhr, status, error) {
            Swal.fire({
              icon: 'error',
              title: 'Error de servidor',
              text: 'Ocurrió un error inesperado al comunicarse con el servidor.'
            });
            console.error(error);
          }
        });
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
  // VISOR MODAL DE OFICIO ADJUNTO
  // =========================================================================
  function abrirVisorOficio(url, nombreArchivo, folio) {
    if (!url) {
      toastr.warning('No se localizó la ruta del archivo de oficio digital.');
      return;
    }

    const cleanUrl = url.trim();
    const filename = (nombreArchivo || cleanUrl.split('/').pop() || 'Oficio').trim();
    const ext = filename.split('.').pop().toLowerCase();

    const folioBadge = folio ? ` &bull; Folio: <span class="font-monospace text-warning">${escapeHtml(folio)}</span>` : '';
    $('#visor-oficio-titulo').html(`Oficio Adjunto${folioBadge} &bull; <small class="text-white-50">${escapeHtml(filename)}</small>`);
    $('#btn-descargar-oficio-visor').attr('href', cleanUrl).attr('download', filename);
    $('#btn-descargar-oficio-alt').attr('href', cleanUrl).attr('download', filename);
    $('#btn-abrir-nueva-pestana').attr('href', cleanUrl);

    // Ocultar contenidos previos y mostrar loader
    $('#visor-oficio-loading').removeClass('d-none');
    $('#visor-container-pdf').addClass('d-none');
    $('#visor-container-img').addClass('d-none');
    $('#visor-container-no-preview').addClass('d-none');
    $('#visor-iframe-pdf').attr('src', '');
    $('#visor-img-preview').attr('src', '');

    const modalEl = document.getElementById('modalVisorOficio');
    if (modalEl) {
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }

    if (ext === 'pdf') {
      $('#visor-container-pdf').removeClass('d-none');
      $('#visor-oficio-loading').addClass('d-none');
      $('#visor-iframe-pdf').attr('src', cleanUrl);
    } else if (['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'].includes(ext)) {
      $('#visor-img-preview').off('load error').on('load', function () {
        $('#visor-oficio-loading').addClass('d-none');
        $('#visor-container-img').removeClass('d-none');
      }).on('error', function () {
        $('#visor-oficio-loading').addClass('d-none');
        $('#visor-container-no-preview').removeClass('d-none');
        $('#visor-no-preview-filename').text(filename);
      });
      $('#visor-img-preview').attr('src', cleanUrl);
    } else {
      $('#visor-oficio-loading').addClass('d-none');
      $('#visor-container-no-preview').removeClass('d-none');
      $('#visor-no-preview-filename').text(filename);
    }
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
