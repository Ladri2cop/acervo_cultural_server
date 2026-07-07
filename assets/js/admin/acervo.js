$(document).ready(function () {
  console.log("registros.js loaded");
  // mostrarListaAcervo();
  mostrarListaPaginada();
  inicializarVistaPreviaEdicion();

  // Aplica Select2 a todos los selects dentro de la barra de filtros
  $("#ubicacion, #tipo_registro, #anio, #cultura").select2({
    dropdownAutoWidth: true,
    width: "100%",
    minimumResultsForSearch: 5, // Muestra buscador solo si hay más de 5 opciones
    dropdownCssClass: "select2-scroll-limit",
  });

  // Funcionalidad de búsqueda con debounce
  let searchTimeout;
  $("#buscar-registro").on("input", function () {
    clearTimeout(searchTimeout);
    const searchTerm = $(this).val();
    
    // Esperar 500ms después de que el usuario deje de escribir
    searchTimeout = setTimeout(function () {
      console.log("Buscando:", searchTerm);
      mostrarListaPaginada(1, 10, searchTerm); // Reiniciar a página 1 con el término de búsqueda
    }, 500);
  });
});

function inicializarVistaPreviaEdicion() {
  const imageInput = document.getElementById('editar-fotografia');
  const previewContainer = document.getElementById('editar-previewContainer');
  const previewText = document.getElementById('editar-previewText');
  const previewIcon = document.getElementById('editar-previewIcon');

  if (!imageInput || !previewContainer || !previewText || !previewIcon) return;

  let previewImage = document.getElementById('editar-previewImage');

  if (!previewImage) {
    previewImage = document.createElement('img');
    previewImage.id = 'editar-previewImage';
    previewImage.className = 'img-fluid mt-3 fade-in w-100';
    previewImage.style.maxHeight = '240px';
    previewImage.style.display = 'none';
    previewContainer.appendChild(previewImage);
  }

  window.mostrarPreviewEdicionFotografia = function (src, nombreArchivo = '') {
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

  imageInput.addEventListener('change', function () {
    const file = this.files[0];

    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        window.mostrarPreviewEdicionFotografia(e.target.result, file.name);
      };
      reader.readAsDataURL(file);
    } else {
      window.mostrarPreviewEdicionFotografia('', 'No hay imagen seleccionada');
    }
  });
}

function mostrarListaPaginada(page = 1, perPage = 10, search = "") {
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
        
        innerListaAcervo(piezas, pagination);
        construirPaginacion(pagination, search);
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

function innerListaAcervo(piezas, pagination = null) {
  const tabla = document.getElementById("tabla-piezas");
  
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

  piezas.forEach((pieza) => {
    const fila = document.createElement("tr");
    fila.innerHTML = `
        <td><img src="${pieza.image}" alt="${pieza.nombre}" class="img__miniatura" /></td>
        <td>${pieza.nombre}</td>
        <td>${pieza.ubicacion}</td>
        <td>${pieza.descripcion}</td>
        <td>${pieza.fecha}</td>
        <td>
          <div class="dropdown">
            <button class="btn btn__actions btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx  bx-caret-down'></i> 
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item btn-ver" href="#" data-id="${pieza.id}"> <i class='bx text-info bx__iconmenu bx-eye-alt'></i> Ver</a></li>
              <li><a class="dropdown-item btn-editar" href="#" data-id="${pieza.id}"><i class='bx text-warning bx__iconmenu bx-pencil-circle'></i>  Editar</a></li>
              <hr class="dropdown-divider">
              <li><a class="dropdown-item btn-eliminar" href="#" data-id="${pieza.id}"><i class='bx text-danger bx__iconmenu bx-trash'></i>  Eliminar</a></li>
            </ul>
          </div>
        </td>
      `;
    tabla.appendChild(fila);
  });

  // Delegación de eventos para Editar y Eliminar
  tabla.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.getAttribute('data-id');
      if (confirm('¿Seguro que deseas eliminar esta pieza?')) {
        eliminarPieza(id);
      }
    });
  });

  tabla.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = this.getAttribute('data-id');
      abrirModalEditarPieza(id);
    });
  });
}

// Abre el modal de edición y rellena los campos
function abrirModalEditarPieza(id) {
  // Petición AJAX para obtener todos los datos de la pieza
  let formData = new FormData();
  formData.append('id', id);
  formData.append('csrf', Bee.csrf);

  $.ajax({
    url: 'admin/acervo_general_get_by_id',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(resp) {
      if (resp.status === 200 && resp.data) {
        const pieza = resp.data;
        const previewContainer = document.getElementById('editar-previewContainer');
        const previewText = document.getElementById('editar-previewText');
        const previewIcon = document.getElementById('editar-previewIcon');
        const previewImage = document.getElementById('editar-previewImage');
        const inputFotografia = document.getElementById('editar-fotografia');
        $('#editar-id').val(pieza.id_acervo_general);
        $('#editar-codigo_interno').val(pieza.codigo_interno);
        $('#editar-no_inventario').val(pieza.no_inventario);
        $('#editar-nombre').val(pieza.nombre_titulo_pieza);
        $('#editar-cm').val(pieza.cm);
        // Si tienes un campo para la imagen, puedes mostrar la miniatura aquí
        $('#editar-autor').val(pieza.autor);
        $('#editar-fecha').val(pieza.anio);
        $('#editar-epoca').val(pieza.epoca);
        $('#editar-tecnica').val(pieza.tecnica);
        $('#editar-material').val(pieza.material);
        $('#editar-medidas').val(pieza.medidas);
        $('#editar-lote').val(pieza.lote);
        $('#editar-peso').val(pieza.peso);
        $('#editar-coleccion').val(pieza.coleccion);
        $('#editar-tipo').val(pieza.tipo);
        $('#editar-ubicacion').val(pieza.ubicacion_fisica);
        $('#editar-estado_conservacion').val(pieza.estado_conservacion);
        $('#editar-observaciones').val(pieza.observaciones);
        $('#editar-descripcion').val(pieza.descripcion);
        $('#editar-fotografia_actual').val(pieza.fotografia || '');

        const fotografiaUrl = pieza.fotografia_url || (pieza.fotografia ? `assets/uploads/${pieza.fotografia}` : '');
        if (typeof window.mostrarPreviewEdicionFotografia === 'function') {
          window.mostrarPreviewEdicionFotografia(fotografiaUrl, pieza.fotografia || 'Imagen cargada');
        }

        if (inputFotografia) {
          inputFotografia.value = '';
          inputFotografia.onchange = function (event) {
            const file = event.target.files && event.target.files[0];
            if (!file || !previewImage || !previewText || !previewIcon || !previewContainer) return;
            const reader = new FileReader();
            reader.onload = function (e) {
              previewImage.src = e.target.result;
              previewImage.style.display = 'block';
              previewImage.classList.add('show');
              previewText.innerText = file.name;
              previewText.classList.add('name-image_success');
              previewIcon.style.display = 'none';
              previewContainer.classList.add('preview-reverse');
            };
            reader.readAsDataURL(file);
          };
        }

        const modal = new bootstrap.Modal(document.getElementById('modalEditarPieza'));
        modal.show();
      } else {
        toastr.error('No se pudo obtener la información de la pieza', 'Error');
      }
    },
    error: function() {
      toastr.error('Error de red al obtener la pieza', 'Error');
    }
  });
}

// Manejar el envío del formulario de edición
document.addEventListener('DOMContentLoaded', function() {
  const formEditar = document.getElementById('formEditarPieza');
  if (formEditar) {
    formEditar.addEventListener('submit', function(e) {
      e.preventDefault();

      let formData = new FormData();
      formData.append('id', $('#editar-id').val());
      formData.append('codigo_interno', $('#editar-codigo_interno').val());
      formData.append('no_inventario', $('#editar-no_inventario').val());
      formData.append('nombre_titulo_pieza', $('#editar-nombre').val());
      formData.append('cm', $('#editar-cm').val());
      // Si tienes campo de imagen para edición, agrégalo aquí
      formData.append('autor', $('#editar-autor').val());
      formData.append('anio', $('#editar-fecha').val());
      formData.append('epoca', $('#editar-epoca').val());
      formData.append('tecnica', $('#editar-tecnica').val());
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
        success: function(resp) {
          if (resp.status === 200) {
            toastr.success(resp.msg, 'Actualizado');
            mostrarListaPaginada();
            // Cerrar el modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPieza'));
            if (modal) modal.hide();
          } else {
            toastr.error(resp.msg || 'No se pudo actualizar', 'Error');
          }
        },
        error: function() {
          toastr.error('Error de red al actualizar', 'Error');
        }
      });
    });
  }
});

// Eliminar pieza por ID
function eliminarPieza(id) {
  let formData = new FormData();
  formData.append('id', id);
  formData.append('csrf', Bee.csrf);
  $.ajax({
    url: 'admin/acervo_general_eliminar',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(resp) {
      if (resp.status === 200) {
        toastr.success(resp.msg, 'Eliminado');
        mostrarListaPaginada();
      } else {
        toastr.error(resp.msg || 'No se pudo eliminar', 'Error');
      }
    },
    error: function() {
      toastr.error('Error de red al eliminar', 'Error');
    }
  });
}

// Editar pieza por ID (básico: solo muestra un prompt para nombre, puedes mejorar con modal/formulario)
function editarPieza(id) {
  // Aquí podrías abrir un modal con los datos actuales, por ahora solo ejemplo con prompt
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
    success: function(resp) {
      if (resp.status === 200) {
        toastr.success(resp.msg, 'Actualizado');
        mostrarListaPaginada();
      } else {
        toastr.error(resp.msg || 'No se pudo actualizar', 'Error');
      }
    },
    error: function() {
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
    <a class="page-link" href="#" ${current_page === 1 ? 'tabindex="-1"' : ""}>
      <i class='bx bx-chevron-left'></i> Anterior
    </a>
  `;
  if (current_page > 1) {
    liPrev.querySelector("a").addEventListener("click", (e) => {
      e.preventDefault();
      mostrarListaPaginada(current_page - 1, per_page, search);
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
    const li = crearBotonPagina(i, current_page, per_page, search);
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
    
    const li = crearBotonPagina(total_pages, current_page, per_page, search);
    ul.appendChild(li);
  }

  // Botón siguiente
  const liNext = document.createElement("li");
  liNext.className = `page-item ${current_page === total_pages ? "disabled" : ""}`;
  liNext.innerHTML = `
    <a class="page-link" href="#" ${current_page === total_pages ? 'tabindex="-1"' : ""}>
      Siguiente <i class='bx bx-chevron-right'></i>
    </a>
  `;
  if (current_page < total_pages) {
    liNext.querySelector("a").addEventListener("click", (e) => {
      e.preventDefault();
      mostrarListaPaginada(current_page + 1, per_page, search);
    });
  }
  ul.appendChild(liNext);

  nav.appendChild(ul);
  contenedorPaginacion.appendChild(nav);
}

/**
 * Crea un botón de página individual
 * @param {number} pageNum - Número de página
 * @param {number} currentPage - Página actual
 * @param {number} perPage - Registros por página
 * @param {string} search - Término de búsqueda
 * @returns {HTMLElement} - Elemento li con el botón
 */
function crearBotonPagina(pageNum, currentPage, perPage, search = "") {
  const li = document.createElement("li");
  li.className = `page-item ${pageNum === currentPage ? "active" : ""}`;
  
  const a = document.createElement("a");
  a.className = "page-link";
  a.href = "#";
  a.textContent = pageNum;
  
  if (pageNum === currentPage) {
    a.setAttribute("aria-current", "page");
  } else {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      mostrarListaPaginada(pageNum, perPage, search);
    });
  }
  
  li.appendChild(a);
  return li;
}
