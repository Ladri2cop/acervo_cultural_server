/**
 * Módulo de Revisión y Semáforo de Acervo Cultural
 * Gestión de aprobaciones, solicitudes de cambios y rechazos por administradores
 */

document.addEventListener('DOMContentLoaded', function () {
  let estadoRevision = {
    tipo: 'todos',
    semaforo: 'pendiente',
    search: '',
    pagina: 1,
    perPage: 10,
    totalPaginas: 1,
    cargando: false
  };

  const tbody = document.getElementById('tbody-revision');
  const paginacion = document.getElementById('paginacion-revision');
  const infoPaginacion = document.getElementById('info-paginacion');
  const badgeTotalFilas = document.getElementById('badge-total-filas');
  const selectTipo = document.getElementById('filtro_tipo_acervo');
  const selectSemaforo = document.getElementById('filtro_semaforo');
  const inputSearch = document.getElementById('busqueda_revision');
  const btnLimpiarSearch = document.getElementById('btn-limpiar-busqueda');
  const selectPerPage = document.getElementById('per_page_revision');
  const btnRefresh = document.getElementById('btn-refresh-revision');

  // Inicializar vista
  cargarDatos();

  // Event Listeners de Filtros
  if (selectTipo) {
    selectTipo.addEventListener('change', function () {
      estadoRevision.tipo = this.value;
      estadoRevision.pagina = 1;
      cargarDatos();
    });
  }

  if (selectSemaforo) {
    selectSemaforo.addEventListener('change', function () {
      estadoRevision.semaforo = this.value;
      estadoRevision.pagina = 1;
      actualizarEstiloKpis();
      cargarDatos();
    });
  }

  if (selectPerPage) {
    selectPerPage.addEventListener('change', function () {
      estadoRevision.perPage = parseInt(this.value) || 10;
      estadoRevision.pagina = 1;
      cargarDatos();
    });
  }

  if (btnRefresh) {
    btnRefresh.addEventListener('click', function () {
      cargarDatos();
    });
  }

  // Búsqueda con debounce
  let timeoutBusqueda = null;
  if (inputSearch) {
    inputSearch.addEventListener('input', function () {
      clearTimeout(timeoutBusqueda);
      timeoutBusqueda = setTimeout(function () {
        estadoRevision.search = inputSearch.value.trim();
        estadoRevision.pagina = 1;
        cargarDatos();
      }, 350);
    });
  }

  if (btnLimpiarSearch) {
    btnLimpiarSearch.addEventListener('click', function () {
      if (inputSearch) {
        inputSearch.value = '';
        estadoRevision.search = '';
        estadoRevision.pagina = 1;
        cargarDatos();
      }
    });
  }

  // Clic en KPIs para filtrar directamente
  document.querySelectorAll('.kpi-card').forEach(function (card) {
    card.addEventListener('click', function () {
      const semaforoCard = this.getAttribute('data-semaforo');
      if (semaforoCard) {
        estadoRevision.semaforo = semaforoCard;
        if (selectSemaforo) selectSemaforo.value = semaforoCard;
        estadoRevision.pagina = 1;
        actualizarEstiloKpis();
        cargarDatos();
      }
    });
  });

  function actualizarEstiloKpis() {
    document.querySelectorAll('.kpi-card').forEach(function (card) {
      if (card.getAttribute('data-semaforo') === estadoRevision.semaforo) {
        card.style.outline = '3px solid #4e73df';
        card.style.boxShadow = '0 .5rem 1rem rgba(0,0,0,.15)';
      } else {
        card.style.outline = 'none';
        card.style.boxShadow = '';
      }
    });
  }

  // Función principal para cargar datos vía AJAX
  function cargarDatos() {
    if (estadoRevision.cargando) return;
    estadoRevision.cargando = true;

    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="text-center py-5 text-muted">
          <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
          Cargando registros en revisión...
        </td>
      </tr>
    `;

    const params = new URLSearchParams({
      tipo: estadoRevision.tipo,
      semaforo: estadoRevision.semaforo,
      search: estadoRevision.search,
      page: estadoRevision.pagina,
      per_page: estadoRevision.perPage
    });

    fetch(`admin/get_revision_data?${params.toString()}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(res => {
        if (!res.ok) throw new Error('Error de conexión con el servidor');
        return res.json();
      })
      .then(resp => {
        estadoRevision.cargando = false;
        if (resp.status === 200) {
          actualizarKpis(resp.kpis);
          renderizarTabla(resp.data);
          renderizarPaginacion(resp.total, resp.page, resp.per_page, resp.total_pages);
        } else {
          tbody.innerHTML = `
            <tr>
              <td colspan="8" class="text-center py-4 text-danger">
                <i class='bx bx-error-circle'></i> ${resp.msg || 'Error al obtener los datos.'}
              </td>
            </tr>
          `;
        }
      })
      .catch(err => {
        estadoRevision.cargando = false;
        console.error(err);
        tbody.innerHTML = `
          <tr>
            <td colspan="8" class="text-center py-4 text-danger">
              <i class='bx bx-wifi-off'></i> Error al comunicar con el servidor. Verifique su conexión.
            </td>
          </tr>
        `;
      });
  }

  // Actualizar contadores del semáforo en los KPIs
  function actualizarKpis(kpis) {
    if (!kpis) return;
    const elPend = document.getElementById('kpi-pendientes');
    const elCamb = document.getElementById('kpi-cambios');
    const elRech = document.getElementById('kpi-rechazados');
    const elApro = document.getElementById('kpi-aprobados');

    if (elPend) elPend.textContent = Number(kpis.pendientes || 0).toLocaleString();
    if (elCamb) elCamb.textContent = Number(kpis.cambios || 0).toLocaleString();
    if (elRech) elRech.textContent = Number(kpis.rechazados || 0).toLocaleString();
    if (elApro) elApro.textContent = Number(kpis.aprobados || 0).toLocaleString();
  }

  // Renderizar filas de la tabla
  function renderizarTabla(filas) {
    if (!filas || filas.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="8" class="text-center py-5 text-muted">
            <i class='bx bx-folder-open fa-3x d-block mb-2 text-gray-400'></i>
            <strong>No se encontraron piezas con los filtros seleccionados.</strong>
            <p class="small mb-0 mt-1">Pruebe seleccionando "Todos los estados" o cambiando el término de búsqueda.</p>
          </td>
        </tr>
      `;
      if (badgeTotalFilas) badgeTotalFilas.textContent = '0 registros';
      return;
    }

    let html = '';
    filas.forEach(p => {
      const fotoUrl = p.fotografia_url ? p.fotografia_url : 'assets/images/preview.svg';
      const badgeTipo = obtenerBadgeTipo(p.tipo);
      const semaforoBadge = obtenerSemaforoBadge(p.estatus_revision);
      const obsHtml = renderizarObservacionSnippet(p.observacion_revision, p.revisor_username, p.fecha_revision);
      const fechaFormateada = p.created_at ? p.created_at.substring(0, 16) : 'S/F';

      html += `
        <tr data-id="${p.id}" data-tipo="${p.tipo}">
          <!-- Fotografía / Miniatura -->
          <td class="text-center">
            <img src="${fotoUrl}" alt="Miniatura" class="rounded shadow-sm border" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" onclick="window.abrirModalDetalleRevision('${p.tipo}', ${p.id})">
          </td>

          <!-- Código Interno -->
          <td>
            <span class="fw-bold text-dark font-monospace">${escapeHtml(p.codigo_interno || 'S/C')}</span>
          </td>

          <!-- Título / Denominación -->
          <td>
            <a href="javascript:void(0)" class="text-decoration-none fw-semibold text-primary" onclick="window.abrirModalDetalleRevision('${p.tipo}', ${p.id})">
              ${escapeHtml(p.titulo || 'Sin Título')}
            </a>
            ${p.ubicacion_fisica ? `<div class="small text-muted"><i class='bx bx-map-pin'></i> ${escapeHtml(p.ubicacion_fisica)}</div>` : ''}
          </td>

          <!-- Tipo de Acervo -->
          <td>${badgeTipo}</td>

          <!-- Capturista y Fecha -->
          <td>
            <div class="small fw-semibold text-gray-800">
              <i class='bx bx-user text-muted'></i> ${escapeHtml(p.creador_username || 'Sistema / Desconocido')}
            </div>
            <div class="small text-muted">
              <i class='bx bx-calendar'></i> ${escapeHtml(fechaFormateada)}
            </div>
          </td>

          <!-- Semáforo Institucional -->
          <td>${semaforoBadge}</td>

          <!-- Observaciones del Administrador -->
          <td>${obsHtml}</td>

          <!-- Botones de Acción -->
          <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-info" title="Ver Expediente Completo" onclick="window.abrirModalDetalleRevision('${p.tipo}', ${p.id})">
                <i class='bx bx-show'></i>
              </button>
              ${p.estatus_revision !== 'aprobado' ? `
                <button type="button" class="btn btn-outline-success" title="Aprobar y Autorizar (Verde)" onclick="window.confirmarAprobacion('${p.tipo}', ${p.id}, '${escapeJs(p.titulo || p.codigo_interno)}')">
                  <i class='bx bx-check'></i>
                </button>
              ` : ''}
              ${p.estatus_revision !== 'cambios_solicitados' ? `
                <button type="button" class="btn btn-outline-warning" title="Solicitar Correcciones (Amarillo)" onclick="window.abrirModalCambios('${p.tipo}', ${p.id}, '${escapeJs(p.codigo_interno)}', '${escapeJs(p.titulo)}')">
                  <i class='bx bx-edit-alt'></i>
                </button>
              ` : ''}
              ${p.estatus_revision !== 'rechazado' ? `
                <button type="button" class="btn btn-outline-danger" title="Rechazar Registro (Rojo)" onclick="window.abrirModalRechazo('${p.tipo}', ${p.id}, '${escapeJs(p.codigo_interno)}', '${escapeJs(p.titulo)}')">
                  <i class='bx bx-x'></i>
                </button>
              ` : ''}
            </div>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  // Generador de badge del Tipo de Acervo
  function obtenerBadgeTipo(tipo) {
    switch (tipo) {
      case 'arqueologico':
        return '<span class="badge bg-light text-info border border-info"><i class="bx bx-pyramid"></i> Arqueológico</span>';
      case 'numismatica':
        return '<span class="badge bg-light text-warning border border-warning"><i class="bx bx-coin"></i> Numismática</span>';
      case 'general':
      default:
        return '<span class="badge bg-light text-primary border border-primary"><i class="bx bx-arch"></i> General</span>';
    }
  }

  // Generador de badge del Semáforo
  function obtenerSemaforoBadge(estatus) {
    switch (estatus) {
      case 'aprobado':
        return `<span class="badge bg-success text-white py-1 px-2 d-inline-flex align-items-center">
                  <i class='bx bxs-check-circle me-1'></i> Aprobado (Verde)
                </span>`;
      case 'cambios_solicitados':
        return `<span class="badge bg-warning text-dark py-1 px-2 d-inline-flex align-items-center">
                  <i class='bx bxs-error me-1'></i> Cambios (Amarillo)
                </span>`;
      case 'rechazado':
        return `<span class="badge bg-danger text-white py-1 px-2 d-inline-flex align-items-center">
                  <i class='bx bxs-x-circle me-1'></i> Rechazado (Rojo)
                </span>`;
      case 'pendiente':
      default:
        return `<span class="badge bg-primary text-white py-1 px-2 d-inline-flex align-items-center">
                  <i class='bx bxs-time-five me-1'></i> Pendiente
                </span>`;
    }
  }

  // Snippet para mostrar la observación con tooltip/modal
  function renderizarObservacionSnippet(obs, revisor, fecha) {
    if (!obs || obs.trim() === '') {
      return '<span class="text-muted small fst-italic">Sin observaciones</span>';
    }
    const cleanObs = escapeHtml(obs);
    const shortObs = cleanObs.length > 55 ? cleanObs.substring(0, 52) + '...' : cleanObs;
    return `
      <div class="small p-1 rounded bg-light border" title="${cleanObs}">
        <span class="text-dark">${shortObs}</span>
        ${revisor ? `<div class="text-muted text-xs mt-1" style="font-size: 0.72rem;"><i class='bx bx-user-check'></i> ${escapeHtml(revisor)}</div>` : ''}
      </div>
    `;
  }

  // Paginación
  function renderizarPaginacion(total, pagina, perPage, totalPaginas) {
    estadoRevision.totalPaginas = totalPaginas || 1;
    if (badgeTotalFilas) {
      badgeTotalFilas.textContent = `${total} registro${total === 1 ? '' : 's'}`;
    }

    if (infoPaginacion) {
      const inicio = total === 0 ? 0 : (pagina - 1) * perPage + 1;
      const fin = Math.min(pagina * perPage, total);
      infoPaginacion.textContent = `Mostrando ${inicio} a ${fin} de ${total} registros (Página ${pagina} de ${totalPaginas || 1})`;
    }

    if (!paginacion) return;

    if (totalPaginas <= 1) {
      paginacion.innerHTML = '';
      return;
    }

    let pHtml = '';
    // Botón Anterior
    pHtml += `
      <li class="page-item ${pagina <= 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="window.cambiarPaginaRevision(${pagina - 1})">
          <i class='bx bx-chevron-left'></i>
        </a>
      </li>
    `;

    const startP = Math.max(1, pagina - 2);
    const endP = Math.min(totalPaginas, pagina + 2);

    if (startP > 1) {
      pHtml += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="window.cambiarPaginaRevision(1)">1</a></li>`;
      if (startP > 2) pHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    for (let i = startP; i <= endP; i++) {
      pHtml += `
        <li class="page-item ${i === pagina ? 'active' : ''}">
          <a class="page-link" href="javascript:void(0)" onclick="window.cambiarPaginaRevision(${i})">${i}</a>
        </li>
      `;
    }

    if (endP < totalPaginas) {
      if (endP < totalPaginas - 1) pHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      pHtml += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="window.cambiarPaginaRevision(${totalPaginas})">${totalPaginas}</a></li>`;
    }

    // Botón Siguiente
    pHtml += `
      <li class="page-item ${pagina >= totalPaginas ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="window.cambiarPaginaRevision(${pagina + 1})">
          <i class='bx bx-chevron-right'></i>
        </a>
      </li>
    `;

    paginacion.innerHTML = pHtml;
  }

  // Cambio de página accesible globalmente
  window.cambiarPaginaRevision = function (nuevaPagina) {
    if (nuevaPagina < 1 || nuevaPagina > estadoRevision.totalPaginas || nuevaPagina === estadoRevision.pagina) return;
    estadoRevision.pagina = nuevaPagina;
    cargarDatos();
  };

  // ==========================================================
  // ACCIONES DEL SEMÁFORO
  // ==========================================================

  // 1. Confirmar Aprobación (Verde)
  window.confirmarAprobacion = function (tipo, id, titulo) {
    Swal.fire({
      title: '¿Aprobar y Autorizar Registro?',
      html: `
        <p class="text-muted mb-2">Está a punto de autorizar la siguiente pieza:</p>
        <div class="p-2 bg-light rounded text-dark fw-bold mb-3 border">
          ${escapeHtml(titulo)}
        </div>
        <p class="small text-success mb-0">
          <i class='bx bxs-check-circle'></i> Pasará a estatus <strong>Aprobado (Verde)</strong> y se reflejará inmediatamente en el catálogo oficial de acervo.
        </p>
      `,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#198754',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bx bx-check"></i> Sí, Aprobar Pieza',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        ejecutarAprobacion(tipo, id);
      }
    });
  };

  function ejecutarAprobacion(tipo, id) {
    const formData = new FormData();
    formData.append('tipo', tipo);
    formData.append('id', id);
    if (window.Bee && window.Bee.csrf) formData.append('csrf', window.Bee.csrf);

    fetch('admin/autorizar_pieza', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 200) {
          Swal.fire({
            title: '¡Pieza Aprobada!',
            text: resp.msg,
            icon: 'success',
            confirmButtonColor: '#198754'
          });
          // Cerrar modal de detalle si estaba abierto
          const modalDetalleEl = document.getElementById('modalDetalleRevision');
          if (modalDetalleEl) {
            const modalDetalle = bootstrap.Modal.getInstance(modalDetalleEl);
            if (modalDetalle) modalDetalle.hide();
          }
          cargarDatos();
        } else {
          Swal.fire('Error', resp.msg || 'No se pudo autorizar la pieza', 'error');
        }
      })
      .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Fallo de comunicación con el servidor', 'error');
      });
  }

  // 2. Abrir Modal de Solicitar Cambios (Amarillo)
  window.abrirModalCambios = function (tipo, id, codigo, titulo) {
    document.getElementById('cambios_pieza_id').value = id;
    document.getElementById('cambios_pieza_tipo').value = tipo;
    document.getElementById('cambios_pieza_info').innerHTML = `
      <strong>${escapeHtml(codigo || 'S/C')}</strong> — ${escapeHtml(titulo || 'Sin Título')}
    `;
    document.getElementById('cambios_observacion').value = '';

    const modal = new bootstrap.Modal(document.getElementById('modalSolicitarCambios'));
    modal.show();
  };

  // Envío del Formulario de Cambios (Amarillo)
  const formCambios = document.getElementById('formSolicitarCambios');
  if (formCambios) {
    formCambios.addEventListener('submit', function (e) {
      e.preventDefault();
      const id = document.getElementById('cambios_pieza_id').value;
      const tipo = document.getElementById('cambios_pieza_tipo').value;
      const observacion = document.getElementById('cambios_observacion').value.trim();

      if (!observacion) {
        toastr.warning('Por favor ingrese las observaciones detallando qué debe corregir el capturista.');
        return;
      }

      const formData = new FormData();
      formData.append('id', id);
      formData.append('tipo', tipo);
      formData.append('observacion', observacion);
      if (window.Bee && window.Bee.csrf) formData.append('csrf', window.Bee.csrf);

      const btnSubmit = document.getElementById('btn-confirmar-cambios');
      btnSubmit.disabled = true;
      btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...`;

      fetch('admin/solicitar_cambios_pieza', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(res => res.json())
        .then(resp => {
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = `<i class='bx bx-send'></i> Enviar a Corrección (Amarillo)`;

          if (resp.status === 200) {
            const modalEl = document.getElementById('modalSolicitarCambios');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Si el modal de detalle estaba abierto, cerrarlo también
            const modalDetalleEl = document.getElementById('modalDetalleRevision');
            if (modalDetalleEl) {
              const modalDetalle = bootstrap.Modal.getInstance(modalDetalleEl);
              if (modalDetalle) modalDetalle.hide();
            }

            Swal.fire({
              title: 'Cambios Solicitados (Amarillo)',
              text: resp.msg,
              icon: 'warning',
              confirmButtonColor: '#ffc107',
              confirmButtonText: 'Entendido'
            });
            cargarDatos();
          } else {
            Swal.fire('Error', resp.msg || 'No se pudo registrar la solicitud de cambios', 'error');
          }
        })
        .catch(err => {
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = `<i class='bx bx-send'></i> Enviar a Corrección (Amarillo)`;
          console.error(err);
          Swal.fire('Error', 'Fallo de comunicación con el servidor', 'error');
        });
    });
  }

  // 3. Abrir Modal de Rechazo (Rojo)
  window.abrirModalRechazo = function (tipo, id, codigo, titulo) {
    document.getElementById('rechazar_pieza_id').value = id;
    document.getElementById('rechazar_pieza_tipo').value = tipo;
    document.getElementById('rechazar_pieza_info').innerHTML = `
      <strong>${escapeHtml(codigo || 'S/C')}</strong> — ${escapeHtml(titulo || 'Sin Título')}
    `;
    document.getElementById('rechazar_observacion').value = '';

    const modal = new bootstrap.Modal(document.getElementById('modalRechazarPieza'));
    modal.show();
  };

  // Envío del Formulario de Rechazo (Rojo)
  const formRechazar = document.getElementById('formRechazarPieza');
  if (formRechazar) {
    formRechazar.addEventListener('submit', function (e) {
      e.preventDefault();
      const id = document.getElementById('rechazar_pieza_id').value;
      const tipo = document.getElementById('rechazar_pieza_tipo').value;
      const observacion = document.getElementById('rechazar_observacion').value.trim();

      if (!observacion) {
        toastr.warning('Por favor ingrese el motivo del rechazo del registro.');
        return;
      }

      const formData = new FormData();
      formData.append('id', id);
      formData.append('tipo', tipo);
      formData.append('observacion', observacion);
      if (window.Bee && window.Bee.csrf) formData.append('csrf', window.Bee.csrf);

      const btnSubmit = document.getElementById('btn-confirmar-rechazo');
      btnSubmit.disabled = true;
      btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...`;

      fetch('admin/rechazar_pieza', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(res => res.json())
        .then(resp => {
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = `<i class='bx bx-x-circle'></i> Confirmar Rechazo (Rojo)`;

          if (resp.status === 200) {
            const modalEl = document.getElementById('modalRechazarPieza');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Cerrar modal de detalle si estaba abierto
            const modalDetalleEl = document.getElementById('modalDetalleRevision');
            if (modalDetalleEl) {
              const modalDetalle = bootstrap.Modal.getInstance(modalDetalleEl);
              if (modalDetalle) modalDetalle.hide();
            }

            Swal.fire({
              title: 'Registro Rechazado (Rojo)',
              text: resp.msg,
              icon: 'error',
              confirmButtonColor: '#dc3545',
              confirmButtonText: 'Entendido'
            });
            cargarDatos();
          } else {
            Swal.fire('Error', resp.msg || 'No se pudo rechazar el registro', 'error');
          }
        })
        .catch(err => {
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = `<i class='bx bx-x-circle'></i> Confirmar Rechazo (Rojo)`;
          console.error(err);
          Swal.fire('Error', 'Fallo de comunicación con el servidor', 'error');
        });
    });
  }

  // 4. Modal de Expediente / Ver Detalle Completo para Revisión
  window.abrirModalDetalleRevision = function (tipo, id) {
    const bodyEl = document.getElementById('modalDetalleRevisionBody');
    const footerAcciones = document.getElementById('modalDetalleRevisionAcciones');
    const modalEl = document.getElementById('modalDetalleRevision');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    bodyEl.innerHTML = `
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Cargando expediente completo de la pieza...</p>
      </div>
    `;
    footerAcciones.innerHTML = '';

    fetch(`admin/get_pieza_detalle_revision?tipo=${encodeURIComponent(tipo)}&id=${encodeURIComponent(id)}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 200) {
          const p = resp.data;
          renderizarCuerpoDetalleRevision(p, bodyEl, footerAcciones);
        } else {
          bodyEl.innerHTML = `
            <div class="alert alert-danger">
              <i class='bx bx-error'></i> ${resp.msg || 'No se pudo cargar la información de la pieza.'}
            </div>
          `;
        }
      })
      .catch(err => {
        console.error(err);
        bodyEl.innerHTML = `
          <div class="alert alert-danger">
            <i class='bx bx-wifi-off'></i> Error de conexión con el servidor.
          </div>
        `;
      });
  };

  function renderizarCuerpoDetalleRevision(p, bodyEl, footerAcciones) {
    const fotoUrl = p.fotografia_url ? p.fotografia_url : 'assets/images/preview.svg';
    const semaforoBadge = obtenerSemaforoBadge(p.estatus_revision);
    const titulo = p.nombre_titulo_pieza || p.denominacion || 'Sin Título';

    let estadoAlert = '';
    if (p.estatus_revision === 'aprobado') {
      estadoAlert = `
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3">
          <i class='bx bxs-check-circle fa-2x me-3'></i>
          <div>
            <strong>Pieza Aprobada y Autorizada (Verde)</strong><br>
            <span class="small">Este registro cumple con todos los requisitos y se encuentra publicado en el catálogo oficial de la institución.</span>
            ${p.revisor_username ? `<div class="small text-muted mt-1">Autorizado por: <strong>${escapeHtml(p.revisor_username)}</strong> el ${escapeHtml(p.fecha_revision || '')}</div>` : ''}
          </div>
        </div>
      `;
    } else if (p.estatus_revision === 'cambios_solicitados') {
      estadoAlert = `
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3">
          <i class='bx bxs-error-circle fa-2x me-3 text-warning'></i>
          <div>
            <strong>Cambios y Correcciones Solicitados (Amarillo)</strong><br>
            <span class="small">Se solicitaron correcciones al capturista antes de autorizar.</span>
            <div class="p-2 bg-white rounded mt-2 border text-dark font-monospace small">
              ${escapeHtml(p.observacion_revision || 'Sin detalle de observaciones')}
            </div>
            ${p.revisor_username ? `<div class="small text-muted mt-1">Revisor: <strong>${escapeHtml(p.revisor_username)}</strong> el ${escapeHtml(p.fecha_revision || '')}</div>` : ''}
          </div>
        </div>
      `;
    } else if (p.estatus_revision === 'rechazado') {
      estadoAlert = `
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-3">
          <i class='bx bxs-x-circle fa-2x me-3 text-danger'></i>
          <div>
            <strong>Registro Rechazado (Rojo)</strong><br>
            <span class="small">El registro no fue admitido para el catálogo oficial.</span>
            <div class="p-2 bg-white rounded mt-2 border text-danger small">
              <strong>Motivo de rechazo:</strong> ${escapeHtml(p.observacion_revision || 'Sin motivo especificado')}
            </div>
            ${p.revisor_username ? `<div class="small text-muted mt-1">Rechazado por: <strong>${escapeHtml(p.revisor_username)}</strong> el ${escapeHtml(p.fecha_revision || '')}</div>` : ''}
          </div>
        </div>
      `;
    } else {
      estadoAlert = `
        <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center mb-3">
          <i class='bx bxs-time-five fa-2x me-3 text-primary'></i>
          <div>
            <strong>Registro en Espera de Dictamen (Pendiente)</strong><br>
            <span class="small">Revise minuciosamente los campos y ortografía para dictaminar si se aprueba, requiere cambios o se rechaza.</span>
          </div>
        </div>
      `;
    }

    bodyEl.innerHTML = `
      ${estadoAlert}

      <div class="row g-4">
        <!-- Columna Izquierda: Fotografía e Identificación -->
        <div class="col-12 col-md-4 text-center">
          <div class="card shadow-sm border">
            <div class="card-body p-2">
              <a href="${fotoUrl}" target="_blank" title="Ver imagen original en grande">
                <img src="${fotoUrl}" class="img-fluid rounded" alt="Fotografía de la pieza" style="max-height: 320px; object-fit: contain;">
              </a>
              <div class="mt-2 text-muted small">
                <i class='bx bx-zoom-in'></i> Clic en la foto para ampliar
              </div>
            </div>
          </div>

          <div class="card shadow-sm border mt-3 text-start">
            <div class="card-header bg-light py-2">
              <h6 class="m-0 font-weight-bold text-dark small">
                <i class='bx bx-info-circle'></i> Metadatos de Captura
              </h6>
            </div>
            <div class="card-body p-2 small">
              <div class="mb-1"><strong>Colección:</strong> ${escapeHtml(p.tipo_acervo_label)}</div>
              <div class="mb-1"><strong>Capturado por:</strong> ${escapeHtml(p.creador_username || 'No registrado')}</div>
              <div class="mb-1"><strong>Fecha de registro:</strong> ${escapeHtml(p.created_at || 'S/F')}</div>
              <div class="mb-1"><strong>Semáforo:</strong> ${semaforoBadge}</div>
            </div>
          </div>
        </div>

        <!-- Columna Derecha: Atributos y Descripciones -->
        <div class="col-12 col-md-8">
          <div class="card shadow-sm border mb-3">
            <div class="card-header bg-light py-2">
              <h6 class="m-0 font-weight-bold text-primary">
                <i class='bx bx-detail'></i> Ficha Técnica y Características
              </h6>
            </div>
            <div class="card-body p-3">
              <div class="row g-2 small">
                <div class="col-sm-6">
                  <span class="text-muted d-block">Código Interno:</span>
                  <span class="fw-bold font-monospace">${escapeHtml(p.codigo_interno || '-')}</span>
                </div>
                <div class="col-sm-6">
                  <span class="text-muted d-block">Número de Inventario:</span>
                  <span class="fw-bold">${escapeHtml(p.no_inventario || p.no_inventario_scyt || '-')}</span>
                </div>

                <div class="col-sm-12">
                  <span class="text-muted d-block">Nombre o Título:</span>
                  <span class="fw-bold fs-6 text-dark">${escapeHtml(titulo)}</span>
                </div>

                ${p.autor ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Autor:</span>
                  <span class="fw-bold">${escapeHtml(p.autor)}</span>
                </div>` : ''}

                ${p.epoca || p.fecha_epoca ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Época / Periodo:</span>
                  <span>${escapeHtml(p.epoca || p.fecha_epoca)}</span>
                </div>` : ''}

                ${p.procedencia ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Procedencia:</span>
                  <span>${escapeHtml(p.procedencia)}</span>
                </div>` : ''}

                ${p.material ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Material:</span>
                  <span>${escapeHtml(p.material)}</span>
                </div>` : ''}

                ${p.tecnica || p.tecnica_manufactura ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Técnica:</span>
                  <span>${escapeHtml(p.tecnica || p.tecnica_manufactura)}</span>
                </div>` : ''}

                ${p.medidas || p.dimensiones || p.cm ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Dimensiones / Medidas:</span>
                  <span>${escapeHtml(p.medidas || p.dimensiones || p.cm)}</span>
                </div>` : ''}

                ${p.ubicacion_fisica ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Ubicación Física:</span>
                  <span><i class='bx bx-map-pin text-danger'></i> ${escapeHtml(p.ubicacion_fisica)}</span>
                </div>` : ''}

                ${p.estado_conservacion ? `
                <div class="col-sm-6">
                  <span class="text-muted d-block">Estado de Conservación:</span>
                  <span class="badge bg-light text-dark border">${escapeHtml(p.estado_conservacion)}</span>
                </div>` : ''}
              </div>
            </div>
          </div>

          <!-- Bloques de Texto / Descripciones -->
          ${p.descripcion || p.descripcion_cara_a || p.observaciones ? `
          <div class="card shadow-sm border">
            <div class="card-header bg-light py-2">
              <h6 class="m-0 font-weight-bold text-dark small">
                <i class='bx bx-align-left'></i> Descripciones y Observaciones de Captura
              </h6>
            </div>
            <div class="card-body p-3 small">
              ${p.descripcion ? `
                <div class="mb-3">
                  <span class="text-muted fw-bold d-block">Descripción de la Pieza:</span>
                  <p class="mb-0 text-dark" style="white-space: pre-wrap;">${escapeHtml(p.descripcion)}</p>
                </div>` : ''}

              ${p.descripcion_cara_a ? `
                <div class="mb-3">
                  <span class="text-muted fw-bold d-block">Descripción Cara A:</span>
                  <p class="mb-0 text-dark" style="white-space: pre-wrap;">${escapeHtml(p.descripcion_cara_a)}</p>
                </div>` : ''}

              ${p.descripcion_cara_b ? `
                <div class="mb-3">
                  <span class="text-muted fw-bold d-block">Descripción Cara B:</span>
                  <p class="mb-0 text-dark" style="white-space: pre-wrap;">${escapeHtml(p.descripcion_cara_b)}</p>
                </div>` : ''}

              ${p.observaciones ? `
                <div>
                  <span class="text-muted fw-bold d-block">Observaciones de Registro:</span>
                  <p class="mb-0 text-dark" style="white-space: pre-wrap;">${escapeHtml(p.observaciones)}</p>
                </div>` : ''}
            </div>
          </div>` : ''}
        </div>
      </div>
    `;

    // Botones de acción en el footer del modal
    footerAcciones.innerHTML = `
      <div class="btn-group" role="group">
        ${p.estatus_revision !== 'rechazado' ? `
          <button type="button" class="btn btn-danger btn-sm" onclick="window.abrirModalRechazo('${p.tipo_acervo}', ${p.id_acervo_general || p.id_acervo_arq || p.id_acervo_numismatica}, '${escapeJs(p.codigo_interno)}', '${escapeJs(titulo)}')">
            <i class='bx bx-x-circle'></i> Rechazar (Rojo)
          </button>
        ` : ''}
        ${p.estatus_revision !== 'cambios_solicitados' ? `
          <button type="button" class="btn btn-warning btn-sm font-weight-bold" onclick="window.abrirModalCambios('${p.tipo_acervo}', ${p.id_acervo_general || p.id_acervo_arq || p.id_acervo_numismatica}, '${escapeJs(p.codigo_interno)}', '${escapeJs(titulo)}')">
            <i class='bx bx-edit-alt'></i> Solicitar Cambios (Amarillo)
          </button>
        ` : ''}
        ${p.estatus_revision !== 'aprobado' ? `
          <button type="button" class="btn btn-success btn-sm font-weight-bold" onclick="window.confirmarAprobacion('${p.tipo_acervo}', ${p.id_acervo_general || p.id_acervo_arq || p.id_acervo_numismatica}, '${escapeJs(titulo)}')">
            <i class='bx bx-check-double'></i> Aprobar y Publicar (Verde)
          </button>
        ` : ''}
      </div>
    `;
  }

  // Utilidades para sanitización
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escapeJs(text) {
    if (text === null || text === undefined) return '';
    return String(text).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
  }

});
