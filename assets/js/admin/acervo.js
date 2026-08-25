$(document).ready(function () {
  console.log("registros.js loaded");
  inicializarVistaPreviaEdicion();
  mostrarEstadoInicialAcervo();
  cargarAniosDinamicos();

  let searchTimeout;
  let perPageTimeout;

  $('#tipo_registro, #anio, #cultura').on('change', function () {
    const tipoAcervo = $('#tipo_registro').val();
    if (!tipoAcervo) {
      mostrarEstadoInicialAcervo();
      return;
    }

    const perPage = parseInt($('#numeroRegistros').val(), 10) || 10;
    const searchTerm = $('#buscar-registro').val() || '';
    mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo);
  });

  // Aplica Select2 a todos los selects dentro de la barra de filtros
  $("#tipo_registro, #anio, #cultura").select2({
    dropdownAutoWidth: true,
    width: "100%",
    minimumResultsForSearch: 5, // Muestra buscador solo si hay más de 5 opciones
    dropdownCssClass: "select2-scroll-limit",
  });

  // Funcionalidad de búsqueda con debounce
  $("#buscar-registro").on("input", function () {
    clearTimeout(searchTimeout);
    const searchTerm = $(this).val();
    const tipoAcervo = $("#tipo_registro").val();

    if (!tipoAcervo) {
      return;
    }

    // Esperar 500ms después de que el usuario deje de escribir
    searchTimeout = setTimeout(function () {
      console.log("Buscando:", searchTerm);
      const perPage = parseInt($('#numeroRegistros').val(), 10) || 10;
      mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo); // Reiniciar a página 1 con el término de búsqueda
    }, 500);
  });

  $("#numeroRegistros").on("change", function () {
    const tipoAcervo = $("#tipo_registro").val();
    if (!tipoAcervo) {
      return;
    }

    clearTimeout(perPageTimeout);
    perPageTimeout = setTimeout(function () {
      const perPage = parseInt($("#numeroRegistros").val(), 10) || 10;
      const searchTerm = $("#buscar-registro").val() || '';
      mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo);
    }, 100);
  });

  // Botones de exportación Excel y PDF
  $('#btn-exportar-excel, #btn-exportar-pdf').on('click', function (e) {
    e.preventDefault();
    const action = $(this).attr('id') === 'btn-exportar-excel' ? 'exportar_excel' : 'exportar_pdf';
    const tipoAcervo = $('#tipo_registro').val();
    
    if (!tipoAcervo) {
      toastr.warning('Por favor, selecciona un tipo de acervo primero', 'Advertencia');
      return;
    }

    const search = $('#buscar-registro').val() || '';
    const anio = $('#anio').val() || '';
    const cultura = $('#cultura').val() || '';

    // Generar la URL con los parámetros actuales
    const url = `admin/${action}?tipo_registro=${encodeURIComponent(tipoAcervo)}&search=${encodeURIComponent(search)}&ubicacion=&anio=${encodeURIComponent(anio)}&cultura=${encodeURIComponent(cultura)}`;

    // Redirigir para iniciar la descarga del reporte
    window.open(url, '_blank');
  });
});

function mostrarEstadoInicialAcervo() {
  const loader = document.getElementById("loader-tabla");
  const tabla = document.getElementById("tabla-acervo");
  const tablaPiezas = document.getElementById("tabla-piezas");
  const paginacion = document.getElementById("paginacion-container");

  if (loader) loader.style.display = "none";
  if (tabla) tabla.style.display = "table";
  if (paginacion) {
    paginacion.innerHTML = "";
    paginacion.style.opacity = "1";
  }

  if (tablaPiezas) {
    tablaPiezas.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-4">
          <i class='bx bx-filter-alt bx-lg text-muted'></i>
          <p class="text-muted mt-2">Selecciona un tipo de acervo para cargar los registros</p>
        </td>
      </tr>
    `;
  }
}

function obtenerConfiguracionAcervo(tipoAcervo) {
  if (tipoAcervo === 'arqueologico') {
    return {
      listUrl: 'admin/get_acervo_arq',
      deleteUrl: 'admin/acervo_arq_eliminar',
      getByIdUrl: 'admin/acervo_arq_get_by_id',
      editUrl: 'admin/acervo_arq_editar',
      idField: 'id_acervo_arq',
      dateField: 'no_registro_inah',
      tipo: 'arqueologico',
    };
  }

  if (tipoAcervo === 'numismatica') {
    return {
      listUrl: 'admin/get_acervo_numismatica',
      deleteUrl: 'admin/acervo_numismatica_eliminar',
      getByIdUrl: 'admin/acervo_numismatica_get_by_id',
      editUrl: 'admin/acervo_numismatica_editar',
      idField: 'id_acervo_numismatica',
      dateField: 'fecha',
      tipo: 'numismatica',
    };
  }

  return {
    listUrl: 'admin/get_acervo_general',
    deleteUrl: 'admin/acervo_general_eliminar',
    getByIdUrl: 'admin/acervo_general_get_by_id',
    editUrl: 'admin/acervo_general_editar',
    idField: 'id_acervo_general',
    dateField: 'fecha',
    tipo: 'general',
  };
}

function inicializarVistaPreviaEdicion() {
  inicializarVistaPreviaEdicionPorPrefijo('editar');
  inicializarVistaPreviaEdicionPorPrefijo('editar-arq');
  inicializarVistaPreviaEdicionPorPrefijo('editar-num');
}

function inicializarVistaPreviaEdicionPorPrefijo(prefijo) {
  const imageInput = document.getElementById(`${prefijo}-fotografia`);
  const previewContainer = document.getElementById(`${prefijo}-previewContainer`);
  const previewText = document.getElementById(`${prefijo}-previewText`);
  const previewIcon = document.getElementById(`${prefijo}-previewIcon`);

  if (!imageInput || !previewContainer || !previewText || !previewIcon) return;

  let previewImage = document.getElementById(`${prefijo}-previewImage`);

  if (!previewImage) {
    previewImage = document.createElement('img');
    previewImage.id = `${prefijo}-previewImage`;
    previewImage.className = 'img-fluid mt-3 fade-in w-100';
    previewImage.style.maxHeight = '240px';
    previewImage.style.display = 'none';
    previewContainer.appendChild(previewImage);
  }

  const mostrarPreview = function (src, nombreArchivo = '') {
    if (!src) {
      previewImage.src = '';
      previewImage.style.display = 'none';
      previewImage.classList.remove('show');
      previewText.style.display = 'inline';
      previewText.classList.remove('name-image_success');
      previewIcon.style.display = 'inline';
      previewText.innerText = 'No hay imagen seleccionada';
      previewContainer.classList.remove('preview-reverse');
      return;
    }

    previewImage.onload = function () {
      previewImage.style.display = 'block';
      previewImage.classList.add('show');
      previewText.innerText = nombreArchivo || 'Imagen cargada';
      previewText.classList.add('name-image_success');
      previewIcon.style.display = 'none';
      previewContainer.classList.add('preview-reverse');
    };

    previewImage.onerror = function () {
      previewImage.src = '';
      previewImage.style.display = 'none';
      previewText.style.display = 'inline';
      previewText.classList.remove('name-image_success');
      previewIcon.style.display = 'inline';
      previewText.innerText = nombreArchivo || 'No hay imagen seleccionada';
      previewContainer.classList.remove('preview-reverse');
    };

    previewImage.src = src;
  };

  if (prefijo === 'editar') {
    window.mostrarPreviewEdicionFotografia = mostrarPreview;
  }

  if (prefijo === 'editar-arq') {
    window.mostrarPreviewEdicionFotografiaArq = mostrarPreview;
  }

  if (prefijo === 'editar-num') {
    window.mostrarPreviewEdicionFotografiaNum = mostrarPreview;
  }

  imageInput.addEventListener('change', function () {
    const file = this.files[0];

    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        mostrarPreview(e.target.result, file.name);
      };
      reader.readAsDataURL(file);
    } else {
      mostrarPreview('', 'No hay imagen seleccionada');
    }
  });
}

function mostrarListaPaginada(page = 1, perPage = 10, search = "", tipoAcervo = null) {
  const tipoSeleccionado = tipoAcervo || $('#tipo_registro').val();
  if (!tipoSeleccionado) {
    mostrarEstadoInicialAcervo();
    return;
  }

  const config = obtenerConfiguracionAcervo(tipoSeleccionado);

  // Mostrar el loader y ocultar la tabla
  const loader = document.getElementById("loader-tabla");
  const tabla = document.getElementById("tabla-acervo");
  const paginacion = document.getElementById("paginacion-container");

  if (loader) {
    loader.style.display = "block";
  }
  if (tabla) {
    tabla.style.display = "none";
  }
  if (paginacion) {
    paginacion.style.opacity = "0.5";
  }

  // Limpiar la tabla antes de cargar
  const tablaPiezas = document.getElementById("tabla-piezas");
  if (tablaPiezas) {
    tablaPiezas.innerHTML = "";
  }

  // Lógica para mostrar la lista del acervo cultural
  let formData = new FormData();
  formData.append("hook", "action");
  formData.append("action", "get");
  formData.append("page", page);
  formData.append("per_page", perPage);
  formData.append("search", search);
  formData.append("ubicacion", '');
  formData.append("anio", $('#anio').val() || '');
  formData.append("cultura", $('#cultura').val() || '');
  formData.append("csrf", Bee.csrf);

  $.ajax({
    url: config.listUrl,
    type: "POST",
    dataType: "json",
    data: formData,
    error: function (err) {
      console.log(`AJAX error in request: ${JSON.stringify(err, null, 2)}`);
      toastr.error(
        "Ocurrió un error al registrar, intenta más tarde",
        "ERROR!"
      );
      // Ocultar loader y mostrar tabla en caso de error
      if (loader) {
        loader.style.display = "none";
      }
      if (tabla) {
        tabla.style.display = "table";
      }
      if (paginacion) {
        paginacion.style.opacity = "1";
      }
    },
    processData: false,
    contentType: false,
    cache: false,
    success: function (dataresponse) {
      // Ocultar el loader y mostrar la tabla
      if (loader) {
        loader.style.display = "none";
      }
      if (tabla) {
        tabla.style.display = "table";
      }
      if (paginacion) {
        paginacion.style.opacity = "1";
      }

      if (dataresponse.status === 200) {
        console.log(dataresponse);
        const piezas = dataresponse.data;
        const pagination = dataresponse.pagination;

        // Mostrar mensaje si no hay resultados
        if (piezas.length === 0 && search) {
          toastr.info(`No se encontraron resultados para "${search}"`, "Sin resultados");
        }

        innerListaAcervo(piezas, pagination, config);
        construirPaginacion(pagination, search, tipoSeleccionado);
      } else {
        console.log(dataresponse);
        toastr.warning("No se pudieron cargar los datos", "Atención");
      }
    },
  });
}

function mostrarListaAcervo() {
  // Lógica para mostrar la lista del acervo cultural
  let formData = new FormData();
  formData.append("hook", "action");
  formData.append("action", "get");

  formData.append("csrf", Bee.csrf);

  $.ajax({
    url: "admin/get_acervo_general",
    type: "POST",
    dataType: "json",
    data: formData,
    error: function (err) {
      console.log(`AJAX error in request: ${JSON.stringify(err, null, 2)}`);
      toastr.error(
        "Ocurrió un error al registrar, intenta más tarde",
        "ERROR!"
      );
    },
    processData: false,
    contentType: false,
    cache: false,
    success: function (dataresponse) {
      if (dataresponse.status === 200) {
        console.log(dataresponse.msg);
        const piezas = dataresponse.data;
        innerListaAcervo(piezas);
      } else {
        console.log(dataresponse);
      }
    },
  });
}

function innerListaAcervo(piezas, pagination = null, config = null) {
  const tabla = document.getElementById("tabla-piezas");
  const tipoAcervo = config ? config.tipo : ($('#tipo_registro').val() || 'general');

  // Limpiar tabla antes de agregar nuevos datos
  tabla.innerHTML = "";

  // Mostrar información de la paginación si existe
  if (pagination) {
    console.log(`Mostrando página ${pagination.current_page} de ${pagination.total_pages}`);
    console.log(`Total de registros: ${pagination.total}`);
  }

  // Mostrar mensaje si no hay resultados
  if (piezas.length === 0) {
    const fila = document.createElement("tr");
    fila.innerHTML = `
      <td colspan="6" class="text-center py-4">
        <i class='bx bx-search-alt bx-lg text-muted'></i>
        <p class="text-muted mt-2">No se encontraron registros</p>
      </td>
    `;
    tabla.appendChild(fila);
    return;
  }

  // Actualizar encabezados de la tabla dinámicamente según el tipo de acervo
  const headerTabla = document.querySelector("#tabla-acervo thead");
  if (headerTabla) {
    if (tipoAcervo === 'arqueologico') {
      headerTabla.innerHTML = `
        <tr>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Código Interno</th>
          <th>No. INAH</th>
          <th>Descripción</th>
          <th>Acción</th>
        </tr>
      `;
    } else if (tipoAcervo === 'numismatica') {
      headerTabla.innerHTML = `
        <tr>
          <th>Imagen</th>
          <th>Denominación</th>
          <th>Código Interno</th>
          <th>Ubicación Física</th>
          <th>Material</th>
          <th>Acción</th>
        </tr>
      `;
    } else {
      headerTabla.innerHTML = `
        <tr>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Código Interno</th>
          <th>Autor</th>
          <th>Descripción</th>
          <th>Acción</th>
        </tr>
      `;
    }
  }

  piezas.forEach((pieza) => {
    const fila = document.createElement("tr");
    const codigoTxt = pieza.codigo_interno || '-';
    const autorTxt = pieza.autor || '-';
    const tieneEdicion = true;
    fila.innerHTML = `
        <td><img src="${pieza.image}" alt="${pieza.nombre}" class="img__miniatura" /></td>
        <td>${pieza.nombre || '-'}</td>
        <td>${codigoTxt}</td>
        <td>${autorTxt}</td>
        <td>${pieza.descripcion || '-'}</td>
        <td>
          <div class="dropdown">
            <button class="btn btn__actions btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx bx-caret-down'></i> 
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item btn-ver" href="javascript:void(0);" data-id="${pieza.id}" data-tipo="${tipoAcervo}"> <i class='bx text-info bx__iconmenu bx-eye-alt'></i> Ver</a></li>
              ${tieneEdicion ? `<li><a class="dropdown-item btn-editar" href="javascript:void(0);" data-id="${pieza.id}"><i class='bx text-warning bx__iconmenu bx-pencil-circle'></i> Editar</a></li>` : ''}
              <li><a class="dropdown-item btn-ficha" href="javascript:void(0);" data-id="${pieza.id}" data-tipo="${tipoAcervo}"><i class='bx text-success bx__iconmenu bx-file'></i> Ficha Técnica</a></li>
              <hr class="dropdown-divider">
              <li><a class="dropdown-item btn-eliminar" href="javascript:void(0);" data-id="${pieza.id}"><i class='bx text-danger bx__iconmenu bx-trash'></i> Eliminar</a></li>
            </ul>
          </div>
        </td>
      `;
    tabla.appendChild(fila);
  });

  // Delegación de eventos para Ver, Editar, Ficha Técnica y Eliminar
  tabla.querySelectorAll('.btn-ver').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.getAttribute('data-id');
      const tipo = this.getAttribute('data-tipo') || tipoAcervo;
      abrirModalVerPieza(id, tipo);
    });
  });

  tabla.querySelectorAll('.btn-ficha').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.getAttribute('data-id');
      const tipo = this.getAttribute('data-tipo') || tipoAcervo;
      window.open(`admin/ficha_tecnica?id=${id}&tipo_acervo=${tipo}`, '_blank');
    });
  });

  tabla.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.getAttribute('data-id');
      if (confirm('¿Seguro que deseas eliminar esta pieza?')) {
        eliminarPieza(id, tipoAcervo);
      }
    });
  });

  if (tipoAcervo === 'general') {
    tabla.querySelectorAll('.btn-editar').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const id = this.getAttribute('data-id');
        abrirModalEditarPieza(id, 'general');
      });
    });
  } else if (tipoAcervo === 'arqueologico') {
    tabla.querySelectorAll('.btn-editar').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const id = this.getAttribute('data-id');
        abrirModalEditarPiezaArq(id);
      });
    });
  } else if (tipoAcervo === 'numismatica') {
    tabla.querySelectorAll('.btn-editar').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const id = this.getAttribute('data-id');
        abrirModalEditarPiezaNum(id);
      });
    });
  }
}

function setSelectValueOrAppend(selectId, value) {
  const select = $(selectId);
  if (!select.length) return;

  const valToSet = (value !== null && value !== undefined) ? String(value).trim() : '';

  if (valToSet === '') {
    select.val('');
    return;
  }

  let exists = false;
  select.find('option').each(function() {
    if ($(this).val() === valToSet) {
      exists = true;
      return false; // break
    }
  });

  if (!exists) {
    select.append(new Option(valToSet, valToSet));
  }

  select.val(valToSet);
}

function abrirModalEditarPiezaNum(id) {
  let formData = new FormData();
  formData.append('id', id);
  formData.append('csrf', Bee.csrf);

  $.ajax({
    url: 'admin/acervo_numismatica_get_by_id',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (resp) {
      if (resp.status === 200 && resp.data) {
        const pieza = resp.data;

        $('#editar-num-id').val(pieza.id_acervo_numismatica || pieza.id || '');
        $('#editar-num-codigo_interno').val(pieza.codigo_interno || '');
        $('#editar-num-no_inventario').val(pieza.no_inventario || '');
        setSelectValueOrAppend('#editar-num-tipo_obra', pieza.tipo_obra);
        $('#editar-num-ensayador').val(pieza.ensayador || '');
        $('#editar-num-denominacion').val(pieza.denominacion || '');
        setSelectValueOrAppend('#editar-num-material', pieza.material);
        setSelectValueOrAppend('#editar-num-fecha_epoca', pieza.fecha_epoca);
        $('#editar-num-dimensiones').val(pieza.dimensiones || '');
        $('#editar-num-ubicacion_fisica').val(pieza.ubicacion_fisica || '');
        setSelectValueOrAppend('#editar-num-estado_conservacion', pieza.estado_conservacion);
        $('#editar-num-observaciones').val(pieza.observaciones || '');
        $('#editar-num-descripcion_cara_a').val(pieza.descripcion_cara_a || '');
        $('#editar-num-descripcion_cara_b').val(pieza.descripcion_cara_b || '');
        $('#editar-num-fotografia_actual').val(pieza.fotografia || '');

        const fotografiaUrlNum = pieza.fotografia_url || (pieza.fotografia ? `assets/uploads/${pieza.fotografia}` : '');
        if (typeof window.mostrarPreviewEdicionFotografiaNum === 'function') {
          window.mostrarPreviewEdicionFotografiaNum(fotografiaUrlNum, pieza.fotografia || 'Imagen cargada');
        }

        const inputFotografiaNum = document.getElementById('editar-num-fotografia');
        if (inputFotografiaNum) {
          inputFotografiaNum.value = '';
        }

        const modalNum = new bootstrap.Modal(document.getElementById('modalEditarPiezaNum'));
        modalNum.show();
      } else {
        toastr.error('No se pudo obtener la información de la pieza numismática', 'Error');
      }
    },
    error: function () {
      toastr.error('Error de red al obtener la pieza numismática', 'Error');
    }
  });
}

function abrirModalEditarPiezaArq(id) {
  let formData = new FormData();
  formData.append('id', id);
  formData.append('csrf', Bee.csrf);

  $.ajax({
    url: 'admin/acervo_arq_get_by_id',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (resp) {
      if (resp.status === 200 && resp.data) {
        const pieza = resp.data;

        $('#editar-arq-id').val(pieza.id_acervo_arq || pieza.id || '');
        $('#editar-arq-codigo_interno').val(pieza.codigo_interno || '');
        $('#editar-arq-no_inventario_scyt').val(pieza.no_inventario_scyt || '');
        $('#editar-arq-no_registro_inah').val(pieza.no_registro_inah || '');
        $('#editar-arq-otros').val(pieza.otros || '');
        $('#editar-arq-nombre_titulo_pieza').val(pieza.nombre_titulo_pieza || '');
        $('#editar-arq-numero_pieza_por_lote').val(pieza.numero_pieza_por_lote || '');
        setSelectValueOrAppend('#editar-arq-epoca', pieza.epoca);
        $('#editar-arq-procedencia').val(pieza.procedencia || '');
        setSelectValueOrAppend('#editar-arq-material', pieza.material);
        $('#editar-arq-medidas').val(pieza.medidas || '');
        $('#editar-arq-forma').val(pieza.forma || '');
        $('#editar-arq-tecnica_manufactura').val(pieza.tecnica_manufactura || '');
        $('#editar-arq-tecnica_decorativa').val(pieza.tecnica_decorativa || '');
        $('#editar-arq-coleccion').val(pieza.coleccion || '');
        $('#editar-arq-obtencion').val(pieza.obtencion || '');
        $('#editar-arq-ubicacion_fisica').val(pieza.ubicacion_fisica || '');
        setSelectValueOrAppend('#editar-arq-estado_conservacion', pieza.estado_conservacion);
        $('#editar-arq-observaciones').val(pieza.observaciones || '');
        $('#editar-arq-descripcion').val(pieza.descripcion || '');
        $('#editar-arq-representacion').val(pieza.representacion || '');
        $('#editar-arq-fotografia_actual').val(pieza.fotografia || '');

        const fotografiaUrlArq = pieza.fotografia_url || (pieza.fotografia ? `assets/uploads/${pieza.fotografia}` : '');
        if (typeof window.mostrarPreviewEdicionFotografiaArq === 'function') {
          window.mostrarPreviewEdicionFotografiaArq(fotografiaUrlArq, pieza.fotografia || 'Imagen cargada');
        }

        const inputFotografiaArq = document.getElementById('editar-arq-fotografia');
        if (inputFotografiaArq) {
          inputFotografiaArq.value = '';
        }

        const modalArq = new bootstrap.Modal(document.getElementById('modalEditarPiezaArq'));
        modalArq.show();
      } else {
        toastr.error('No se pudo obtener la información de la pieza arqueológica', 'Error');
      }
    },
    error: function () {
      toastr.error('Error de red al obtener la pieza arqueológica', 'Error');
    }
  });
}

// Abre el modal de visualización de detalles (Solo Lectura)
function abrirModalVerPieza(id, tipoAcervo = 'general') {
  let formData = new FormData();
  formData.append('id', id);
  formData.append('csrf', Bee.csrf);

  const config = obtenerConfiguracionAcervo(tipoAcervo);

  $.ajax({
    url: config.getByIdUrl,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (resp) {
      if (resp.status === 200 && resp.data) {
        const pieza = resp.data;

        // Ocultar todas las secciones
        $('#ver-seccion-general, #ver-seccion-arq, #ver-seccion-num').hide();

        const imgUrl = pieza.fotografia_url || (pieza.fotografia ? `assets/uploads/${pieza.fotografia}` : '');

        if (tipoAcervo === 'arqueologico') {
          $('#modalVerPiezaLabel').html('<i class="bx bx-show me-2"></i>Ver Pieza Arqueológica');

          if (imgUrl) {
            $('#ver-arq-imagen').attr('src', imgUrl).show();
            $('#ver-arq-no-imagen').hide();
          } else {
            $('#ver-arq-imagen').attr('src', '').hide();
            $('#ver-arq-no-imagen').show();
          }

          $('#ver-arq-codigo_interno').text(pieza.codigo_interno || '-');
          $('#ver-arq-no_inventario_scyt').text(pieza.no_inventario_scyt || '-');
          $('#ver-arq-no_registro_inah').text(pieza.no_registro_inah || '-');
          $('#ver-arq-otros').text(pieza.otros || '-');
          $('#ver-arq-nombre_titulo_pieza').text(pieza.nombre_titulo_pieza || '-');
          $('#ver-arq-numero_pieza_por_lote').text(pieza.numero_pieza_por_lote || '-');

          $('#ver-arq-epoca').text(pieza.epoca || '-');
          $('#ver-arq-procedencia').text(pieza.procedencia || '-');
          $('#ver-arq-obtencion').text(pieza.obtencion || '-');

          $('#ver-arq-material').text(pieza.material || '-');
          $('#ver-arq-medidas').text(pieza.medidas || '-');
          $('#ver-arq-forma').text(pieza.forma || '-');
          $('#ver-arq-tecnica_manufactura').text(pieza.tecnica_manufactura || '-');
          $('#ver-arq-tecnica_decorativa').text(pieza.tecnica_decorativa || '-');
          $('#ver-arq-coleccion').text(pieza.coleccion || '-');

          $('#ver-arq-ubicacion_fisica').text(pieza.ubicacion_fisica || '-');
          $('#ver-arq-estado_conservacion').text(pieza.estado_conservacion || '-');

          $('#ver-arq-descripcion').text(pieza.descripcion || '-');
          $('#ver-arq-representacion').text(pieza.representacion || '-');
          $('#ver-arq-observaciones').text(pieza.observaciones || '-');

          $('#ver-seccion-arq').show();

        } else if (tipoAcervo === 'numismatica') {
          $('#modalVerPiezaLabel').html('<i class="bx bx-show me-2"></i>Ver Pieza Numismática');

          if (imgUrl) {
            $('#ver-num-imagen').attr('src', imgUrl).show();
            $('#ver-num-no-imagen').hide();
          } else {
            $('#ver-num-imagen').attr('src', '').hide();
            $('#ver-num-no-imagen').show();
          }

          $('#ver-num-codigo_interno').text(pieza.codigo_interno || '-');
          $('#ver-num-no_inventario').text(pieza.no_inventario || '-');
          $('#ver-num-tipo_obra').text(pieza.tipo_obra || '-');

          $('#ver-num-ensayador').text(pieza.ensayador || '-');
          $('#ver-num-denominacion').text(pieza.denominacion || '-');
          $('#ver-num-material').text(pieza.material || '-');
          $('#ver-num-fecha_epoca').text(pieza.fecha_epoca || '-');

          $('#ver-num-dimensiones').text(pieza.dimensiones || '-');
          $('#ver-num-ubicacion_fisica').text(pieza.ubicacion_fisica || '-');
          $('#ver-num-estado_conservacion').text(pieza.estado_conservacion || '-');

          $('#ver-num-descripcion_cara_a').text(pieza.descripcion_cara_a || '-');
          $('#ver-num-descripcion_cara_b').text(pieza.descripcion_cara_b || '-');
          $('#ver-num-observaciones').text(pieza.observaciones || '-');

          $('#ver-seccion-num').show();

        } else {
          // General
          $('#modalVerPiezaLabel').html('<i class="bx bx-show me-2"></i>Ver Pieza de Acervo General');

          if (imgUrl) {
            $('#ver-gen-imagen').attr('src', imgUrl).show();
            $('#ver-gen-no-imagen').hide();
          } else {
            $('#ver-gen-imagen').attr('src', '').hide();
            $('#ver-gen-no-imagen').show();
          }

          $('#ver-gen-codigo_interno').text(pieza.codigo_interno || '-');
          $('#ver-gen-no_inventario').text(pieza.no_inventario || '-');
          $('#ver-gen-nombre').text(pieza.nombre_titulo_pieza || pieza.nombre || '-');
          $('#ver-gen-materia').text(pieza.materia || '-');

          $('#ver-gen-autor').text(pieza.autor || '-');
          $('#ver-gen-anio').text(pieza.anio || '-');
          $('#ver-gen-epoca').text(pieza.epoca || '-');

          $('#ver-gen-tecnica').text(pieza.tecnica || '-');
          $('#ver-gen-origen').text(pieza.origen || '-');
          $('#ver-gen-material').text(pieza.material || '-');
          $('#ver-gen-medidas').text(pieza.medidas || '-');
          $('#ver-gen-lote').text(pieza.lote || '-');
          $('#ver-gen-peso').text(pieza.peso || '-');
          $('#ver-gen-coleccion').text(pieza.coleccion || '-');
          $('#ver-gen-tipo').text(pieza.tipo || '-');

          $('#ver-gen-ubicacion_fisica').text(pieza.ubicacion_fisica || '-');
          $('#ver-gen-estado_conservacion').text(pieza.estado_conservacion || '-');

          $('#ver-gen-descripcion').text(pieza.descripcion || '-');
          $('#ver-gen-observaciones').text(pieza.observaciones || '-');

          $('#ver-seccion-general').show();
        }

        const modalVerEl = document.getElementById('modalVerPieza');
        if (modalVerEl) {
          const modalVer = bootstrap.Modal.getOrCreateInstance(modalVerEl);
          modalVer.show();
        }
      } else {
        toastr.error('No se pudo obtener la información de la pieza', 'Error');
      }
    },
    error: function () {
      toastr.error('Error de red al consultar la pieza', 'Error');
    }
  });
}

// Abre el modal de edición y rellena los campos
function abrirModalEditarPieza(id, tipoAcervo = 'general') {
  // Petición AJAX para obtener todos los datos de la pieza
  let formData = new FormData();
  formData.append('id', id);
  formData.append('csrf', Bee.csrf);

  const config = obtenerConfiguracionAcervo(tipoAcervo);

  $.ajax({
    url: config.getByIdUrl,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (resp) {
      if (resp.status === 200 && resp.data) {
        const pieza = resp.data;
        if (tipoAcervo === 'arqueologico') {
          $('#editar-arq-id').val(pieza.id_acervo_arq);
          $('#editar-arq-codigo_interno').val(pieza.codigo_interno);
          $('#editar-arq-no_inventario_scyt').val(pieza.no_inventario_scyt);
          $('#editar-arq-no_registro_inah').val(pieza.no_registro_inah);
          $('#editar-arq-otros').val(pieza.otros);
          $('#editar-arq-nombre_titulo_pieza').val(pieza.nombre_titulo_pieza);
          $('#editar-arq-numero_pieza_por_lote').val(pieza.numero_pieza_por_lote);
          $('#editar-arq-epoca').val(pieza.epoca);
          $('#editar-arq-procedencia').val(pieza.procedencia);
          $('#editar-arq-material').val(pieza.material);
          $('#editar-arq-medidas').val(pieza.medidas);
          $('#editar-arq-forma').val(pieza.forma);
          $('#editar-arq-tecnica_manufactura').val(pieza.tecnica_manufactura);
          $('#editar-arq-tecnica_decorativa').val(pieza.tecnica_decorativa);
          $('#editar-arq-coleccion').val(pieza.coleccion);
          $('#editar-arq-obtencion').val(pieza.obtencion);
          $('#editar-arq-ubicacion_fisica').val(pieza.ubicacion_fisica);
          $('#editar-arq-estado_conservacion').val(pieza.estado_conservacion);
          $('#editar-arq-observaciones').val(pieza.observaciones);
          $('#editar-arq-descripcion').val(pieza.descripcion);
          $('#editar-arq-representacion').val(pieza.representacion);
          $('#editar-arq-fotografia_actual').val(pieza.fotografia || '');

          const fotografiaUrlArq = pieza.fotografia_url || (pieza.fotografia ? `assets/uploads/${pieza.fotografia}` : '');
          if (typeof window.mostrarPreviewEdicionFotografiaArq === 'function') {
            window.mostrarPreviewEdicionFotografiaArq(fotografiaUrlArq, pieza.fotografia || 'Imagen cargada');
          }

          const inputFotografiaArq = document.getElementById('editar-arq-fotografia');
          if (inputFotografiaArq) {
            inputFotografiaArq.value = '';
          }

          const modalArq = new bootstrap.Modal(document.getElementById('modalEditarPiezaArq'));
          modalArq.show();
          return;
        }

        $('#editar-id').val(pieza.id_acervo_general);
        $('#editar-codigo_interno').val(pieza.codigo_interno);
        $('#editar-no_inventario').val(pieza.no_inventario);
        $('#editar-nombre').val(pieza.nombre_titulo_pieza);
        $('#editar-cm').val(pieza.cm);
        $('#editar-materia').val(pieza.materia);
        $('#editar-autor').val(pieza.autor);
        $('#editar-fecha').val(pieza.anio);
        setSelectValueOrAppend('#editar-epoca', pieza.epoca);
        setSelectValueOrAppend('#editar-tecnica', pieza.tecnica);
        $('#editar-origen').val(pieza.origen);
        setSelectValueOrAppend('#editar-material', pieza.material);
        $('#editar-medidas').val(pieza.medidas);
        $('#editar-lote').val(pieza.lote);
        $('#editar-peso').val(pieza.peso);
        setSelectValueOrAppend('#editar-coleccion', pieza.coleccion);
        setSelectValueOrAppend('#editar-tipo', pieza.tipo);
        $('#editar-ubicacion').val(pieza.ubicacion_fisica);
        setSelectValueOrAppend('#editar-estado_conservacion', pieza.estado_conservacion);
        $('#editar-observaciones').val(pieza.observaciones);
        $('#editar-descripcion').val(pieza.descripcion);
        $('#editar-fotografia_actual').val(pieza.fotografia || '');

        const fotografiaUrl = pieza.fotografia_url || (pieza.fotografia ? `assets/uploads/${pieza.fotografia}` : '');
        if (typeof window.mostrarPreviewEdicionFotografia === 'function') {
          window.mostrarPreviewEdicionFotografia(fotografiaUrl, pieza.fotografia || 'Imagen cargada');
        }

        const inputFotografia = document.getElementById('editar-fotografia');
        if (inputFotografia) {
          inputFotografia.value = '';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalEditarPieza'));
        modal.show();
      } else {
        toastr.error('No se pudo obtener la información de la pieza', 'Error');
      }
    },
    error: function () {
      toastr.error('Error de red al obtener la pieza', 'Error');
    }
  });
}

// Manejar el envío del formulario de edición y eventos globales
document.addEventListener('DOMContentLoaded', function () {
  // Delegación de evento click para el botón Ver
  $(document).on('click', '.btn-ver', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const id = $(this).attr('data-id');
    const tipoAcervo = $('#tipo_registro').val() || 'general';
    if (id) {
      abrirModalVerPieza(id, tipoAcervo);
    }
  });

  const formEditar = document.getElementById('formEditarPieza');
  if (formEditar) {
    formEditar.addEventListener('submit', function (e) {
      e.preventDefault();

      let formData = new FormData();
      formData.append('id', $('#editar-id').val());
      formData.append('codigo_interno', $('#editar-codigo_interno').val());
      formData.append('no_inventario', $('#editar-no_inventario').val());
      formData.append('nombre_titulo_pieza', $('#editar-nombre').val());
      formData.append('cm', $('#editar-cm').val());
      formData.append('materia', $('#editar-materia').val());
      formData.append('autor', $('#editar-autor').val());
      formData.append('anio', $('#editar-fecha').val());
      formData.append('epoca', $('#editar-epoca').val());
      formData.append('tecnica', $('#editar-tecnica').val());
      formData.append('origen', $('#editar-origen').val());
      formData.append('material', $('#editar-material').val());
      formData.append('medidas', $('#editar-medidas').val());
      formData.append('lote', $('#editar-lote').val());
      formData.append('peso', $('#editar-peso').val());
      formData.append('coleccion', $('#editar-coleccion').val());
      formData.append('tipo', $('#editar-tipo').val());
      formData.append('ubicacion_fisica', $('#editar-ubicacion').val());
      formData.append('estado_conservacion', $('#editar-estado_conservacion').val());
      formData.append('observaciones', $('#editar-observaciones').val());
      formData.append('descripcion', $('#editar-descripcion').val());
      const fotografiaNueva = document.getElementById('editar-fotografia').files[0];
      if (fotografiaNueva) {
        formData.append('fotografia', fotografiaNueva);
      }
      formData.append('fotografia_actual', $('#editar-fotografia_actual').val());
      formData.append('csrf', Bee.csrf);

      $.ajax({
        url: 'admin/acervo_general_editar',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (resp) {
          if (resp.status === 200) {
            toastr.success(resp.msg, 'Actualizado');
            const tipoAcervo = $('#tipo_registro').val() || 'general';
            const perPage = parseInt($('#numeroRegistros').val(), 10) || 10;
            const searchTerm = $('#buscar-registro').val() || '';
            mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo);
            // Cerrar el modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPieza'));
            if (modal) modal.hide();
          } else {
            toastr.error(resp.msg || 'No se pudo actualizar', 'Error');
          }
        },
        error: function () {
          toastr.error('Error de red al actualizar', 'Error');
        }
      });
    });
  }

  const formEditarArq = document.getElementById('formEditarPiezaArq');
  if (formEditarArq) {
    formEditarArq.addEventListener('submit', function (e) {
      e.preventDefault();

      let formData = new FormData();
      formData.append('id', $('#editar-arq-id').val());
      formData.append('codigo_interno', $('#editar-arq-codigo_interno').val());
      formData.append('no_inventario_scyt', $('#editar-arq-no_inventario_scyt').val());
      formData.append('no_registro_inah', $('#editar-arq-no_registro_inah').val());
      formData.append('otros', $('#editar-arq-otros').val());
      formData.append('nombre_titulo_pieza', $('#editar-arq-nombre_titulo_pieza').val());
      formData.append('numero_pieza_por_lote', $('#editar-arq-numero_pieza_por_lote').val());
      formData.append('epoca', $('#editar-arq-epoca').val());
      formData.append('procedencia', $('#editar-arq-procedencia').val());
      formData.append('material', $('#editar-arq-material').val());
      formData.append('medidas', $('#editar-arq-medidas').val());
      formData.append('forma', $('#editar-arq-forma').val());
      formData.append('tecnica_manufactura', $('#editar-arq-tecnica_manufactura').val());
      formData.append('tecnica_decorativa', $('#editar-arq-tecnica_decorativa').val());
      formData.append('coleccion', $('#editar-arq-coleccion').val());
      formData.append('obtencion', $('#editar-arq-obtencion').val());
      formData.append('ubicacion_fisica', $('#editar-arq-ubicacion_fisica').val());
      formData.append('estado_conservacion', $('#editar-arq-estado_conservacion').val());
      formData.append('observaciones', $('#editar-arq-observaciones').val());
      formData.append('descripcion', $('#editar-arq-descripcion').val());
      formData.append('representacion', $('#editar-arq-representacion').val());
      const fotografiaNuevaArq = document.getElementById('editar-arq-fotografia').files[0];
      if (fotografiaNuevaArq) {
        formData.append('fotografia', fotografiaNuevaArq);
      }
      formData.append('fotografia_actual', $('#editar-arq-fotografia_actual').val());
      formData.append('csrf', Bee.csrf);

      $.ajax({
        url: 'admin/acervo_arq_editar',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (resp) {
          if (resp.status === 200) {
            toastr.success(resp.msg, 'Actualizado');
            const tipoAcervo = $('#tipo_registro').val() || 'arqueologico';
            const perPage = parseInt($('#numeroRegistros').val(), 10) || 10;
            const searchTerm = $('#buscar-registro').val() || '';
            mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo);
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPiezaArq'));
            if (modal) modal.hide();
          } else {
            toastr.error(resp.msg || 'No se pudo actualizar', 'Error');
          }
        },
        error: function () {
          toastr.error('Error de red al actualizar', 'Error');
        }
      });
    });
  }

  const formEditarNum = document.getElementById('formEditarPiezaNum');
  if (formEditarNum) {
    formEditarNum.addEventListener('submit', function (e) {
      e.preventDefault();

      let formData = new FormData();
      formData.append('id', $('#editar-num-id').val());
      formData.append('codigo_interno', $('#editar-num-codigo_interno').val());
      formData.append('no_inventario', $('#editar-num-no_inventario').val());
      formData.append('tipo_obra', $('#editar-num-tipo_obra').val());
      formData.append('ensayador', $('#editar-num-ensayador').val());
      formData.append('denominacion', $('#editar-num-denominacion').val());
      formData.append('material', $('#editar-num-material').val());
      formData.append('fecha_epoca', $('#editar-num-fecha_epoca').val());
      formData.append('dimensiones', $('#editar-num-dimensiones').val());
      formData.append('ubicacion_fisica', $('#editar-num-ubicacion_fisica').val());
      formData.append('estado_conservacion', $('#editar-num-estado_conservacion').val());
      formData.append('observaciones', $('#editar-num-observaciones').val());
      formData.append('descripcion_cara_a', $('#editar-num-descripcion_cara_a').val());
      formData.append('descripcion_cara_b', $('#editar-num-descripcion_cara_b').val());
      const fotografiaNuevaNum = document.getElementById('editar-num-fotografia').files[0];
      if (fotografiaNuevaNum) {
        formData.append('fotografia', fotografiaNuevaNum);
      }
      formData.append('fotografia_actual', $('#editar-num-fotografia_actual').val());
      formData.append('csrf', Bee.csrf);

      $.ajax({
        url: 'admin/acervo_numismatica_editar',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (resp) {
          if (resp.status === 200) {
            toastr.success(resp.msg, 'Actualizado');
            const tipoAcervo = $('#tipo_registro').val() || 'numismatica';
            const perPage = parseInt($('#numeroRegistros').val(), 10) || 10;
            const searchTerm = $('#buscar-registro').val() || '';
            mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo);
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPiezaNum'));
            if (modal) modal.hide();
          } else {
            toastr.error(resp.msg || 'No se pudo actualizar', 'Error');
          }
        },
        error: function () {
          toastr.error('Error de red al actualizar', 'Error');
        }
      });
    });
  }
});

// Eliminar pieza por ID
function eliminarPieza(id) {
  const tipoAcervo = $('#tipo_registro').val() || 'general';
  const config = obtenerConfiguracionAcervo(tipoAcervo);
  let formData = new FormData();
  formData.append('id', id);
  formData.append('csrf', Bee.csrf);
  $.ajax({
    url: config.deleteUrl,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (resp) {
      if (resp.status === 200) {
        toastr.success(resp.msg, 'Eliminado');
        const perPage = parseInt($('#numeroRegistros').val(), 10) || 10;
        const searchTerm = $('#buscar-registro').val() || '';
        mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo);
      } else {
        toastr.error(resp.msg || 'No se pudo eliminar', 'Error');
      }
    },
    error: function () {
      toastr.error('Error de red al eliminar', 'Error');
    }
  });
}

// Editar pieza por ID (básico: solo muestra un prompt para nombre, puedes mejorar con modal/formulario)
function editarPieza(id) {
  const nuevoNombre = prompt('Nuevo nombre para la pieza:');
  if (!nuevoNombre) return;
  let formData = new FormData();
  formData.append('id', id);
  formData.append('nombre_titulo_pieza', nuevoNombre);
  formData.append('csrf', Bee.csrf);
  $.ajax({
    url: 'admin/acervo_general_editar',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (resp) {
      if (resp.status === 200) {
        toastr.success(resp.msg, 'Actualizado');
        const tipoAcervo = $('#tipo_registro').val() || 'general';
        const perPage = parseInt($('#numeroRegistros').val(), 10) || 10;
        const searchTerm = $('#buscar-registro').val() || '';
        mostrarListaPaginada(1, perPage, searchTerm, tipoAcervo);
      } else {
        toastr.error(resp.msg || 'No se pudo actualizar', 'Error');
      }
    },
    error: function () {
      toastr.error('Error de red al actualizar', 'Error');
    }
  });
}


/**
 * Construye los controles de paginación
 * @param {Object} pagination - Objeto con información de paginación
 * @param {string} search - Término de búsqueda actual
 */
function construirPaginacion(pagination, search = "") {
  const contenedorPaginacion = document.getElementById("paginacion-container");

  if (!contenedorPaginacion) {
    console.warn("No se encontró el contenedor de paginación con id 'paginacion-container'");
    return;
  }

  const { current_page, total_pages, total, per_page } = pagination;
  const tipoAcervo = $('#tipo_registro').val() || 'general';

  // Limpiar paginación anterior
  contenedorPaginacion.innerHTML = "";

  // Si no hay datos, no mostrar paginación
  if (total === 0) {
    return;
  }

  // Crear estructura de paginación
  const nav = document.createElement("nav");
  nav.setAttribute("aria-label", "Navegación de páginas");

  const ul = document.createElement("ul");
  ul.className = "pagination justify-content-center";

  // Información de registros
  const info = document.createElement("div");
  info.className = "text-center mb-2 text-muted";
  const mostrandoDesde = ((current_page - 1) * per_page) + 1;
  const mostrandoHasta = Math.min(current_page * per_page, total);
  info.innerHTML = `Mostrando ${mostrandoDesde} - ${mostrandoHasta} de ${total} registro${total !== 1 ? 's' : ''}`;

  // Agregar indicador de búsqueda si existe
  if (search) {
    info.innerHTML += ` <span class="badge bg-info ms-2">Filtrando: "${search}"</span>`;
  }

  contenedorPaginacion.appendChild(info);

  // Botón anterior
  const liPrev = document.createElement("li");
  liPrev.className = `page-item ${current_page === 1 ? "disabled" : ""}`;
  liPrev.innerHTML = `
    <a class="page-link" href="javascript:void(0);" ${current_page === 1 ? 'tabindex="-1"' : ""}>
      <i class='bx bx-chevron-left'></i> Anterior
    </a>
  `;
  if (current_page > 1) {
    liPrev.querySelector("a").addEventListener("click", (e) => {
      e.preventDefault();
      mostrarListaPaginada(current_page - 1, per_page, search, tipoAcervo);
    });
  }
  ul.appendChild(liPrev);

  // Páginas numéricas
  const maxPagesToShow = 5;
  let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
  let endPage = Math.min(total_pages, startPage + maxPagesToShow - 1);

  // Ajustar si estamos cerca del final
  if (endPage - startPage < maxPagesToShow - 1) {
    startPage = Math.max(1, endPage - maxPagesToShow + 1);
  }

  // Primera página si no está visible
  if (startPage > 1) {
    const li = crearBotonPagina(1, current_page, per_page, search);
    ul.appendChild(li);

    if (startPage > 2) {
      const liDots = document.createElement("li");
      liDots.className = "page-item disabled";
      liDots.innerHTML = '<span class="page-link">...</span>';
      ul.appendChild(liDots);
    }
  }

  // Páginas del rango
  for (let i = startPage; i <= endPage; i++) {
    const li = crearBotonPagina(i, current_page, per_page, search, tipoAcervo);
    ul.appendChild(li);
  }

  // Última página si no está visible
  if (endPage < total_pages) {
    if (endPage < total_pages - 1) {
      const liDots = document.createElement("li");
      liDots.className = "page-item disabled";
      liDots.innerHTML = '<span class="page-link">...</span>';
      ul.appendChild(liDots);
    }

    const li = crearBotonPagina(total_pages, current_page, per_page, search, tipoAcervo);
    ul.appendChild(li);
  }

  // Botón siguiente
  const liNext = document.createElement("li");
  liNext.className = `page-item ${current_page === total_pages ? "disabled" : ""}`;
  liNext.innerHTML = `
    <a class="page-link" href="javascript:void(0);" ${current_page === total_pages ? 'tabindex="-1"' : ""}>
      Siguiente <i class='bx bx-chevron-right'></i>
    </a>
  `;
  if (current_page < total_pages) {
    liNext.querySelector("a").addEventListener("click", (e) => {
      e.preventDefault();
      mostrarListaPaginada(current_page + 1, per_page, search, tipoAcervo);
    });
  }
  ul.appendChild(liNext);

  nav.appendChild(ul);
  contenedorPaginacion.appendChild(nav);
}

/**
 * Crea un botón de página individual
 */
function crearBotonPagina(pageNum, currentPage, perPage, search = "", tipoAcervo = null) {
  const li = document.createElement("li");
  li.className = `page-item ${pageNum === currentPage ? "active" : ""}`;

  const a = document.createElement("a");
  a.className = "page-link";
  a.href = "javascript:void(0);";
  a.textContent = pageNum;

  if (pageNum === currentPage) {
    a.setAttribute("aria-current", "page");
  } else {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      mostrarListaPaginada(pageNum, perPage, search, tipoAcervo || $('#tipo_registro').val() || 'general');
    });
  }

  li.appendChild(a);
  return li;
}

function cargarAniosDinamicos() {
  $.ajax({
    url: 'admin/get_todos_anios',
    type: 'POST',
    dataType: 'json',
    data: { csrf: Bee.csrf },
    success: function (resp) {
      if (resp.status === 200 && resp.data) {
        const selectAnio = $('#anio');
        if (selectAnio.length) {
          const selectedVal = selectAnio.val();
          selectAnio.html('<option value="" hidden>Seleccione...</option>');

          resp.data.forEach(anio => {
            selectAnio.append(`<option value="${anio}">${anio}</option>`);
          });

          if (selectedVal) {
            selectAnio.val(selectedVal);
          }

          if (selectAnio.hasClass('select2-hidden-accessible')) {
            selectAnio.trigger('change.select2');
          }
        }
      }
    },
    error: function () {
      console.error('Error al cargar la lista dinámica de años');
    }
  });
}