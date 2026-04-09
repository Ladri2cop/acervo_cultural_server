$(document).ready(function () {
  console.log("registros.js loaded");
  // mostrarListaAcervo();
  mostrarListaPaginada();

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
  // Buscar la fila correspondiente para obtener los datos actuales
  const fila = document.querySelector(`a.btn-editar[data-id='${id}']`).closest('tr');
  const nombre = fila.children[1].textContent;
  const ubicacion = fila.children[2].textContent;
  const descripcion = fila.children[3].textContent;
  const fecha = fila.children[4].textContent;

  document.getElementById('editar-id').value = id;
  document.getElementById('editar-nombre').value = nombre;
  document.getElementById('editar-ubicacion').value = ubicacion;
  document.getElementById('editar-descripcion').value = descripcion;
  document.getElementById('editar-fecha').value = fecha;

  // Mostrar el modal (Bootstrap 5)
  const modal = new bootstrap.Modal(document.getElementById('modalEditarPieza'));
  modal.show();
}

// Manejar el envío del formulario de edición
document.addEventListener('DOMContentLoaded', function() {
  const formEditar = document.getElementById('formEditarPieza');
  if (formEditar) {
    formEditar.addEventListener('submit', function(e) {
      e.preventDefault();
      const id = document.getElementById('editar-id').value;
      const nombre = document.getElementById('editar-nombre').value;
      const ubicacion = document.getElementById('editar-ubicacion').value;
      const descripcion = document.getElementById('editar-descripcion').value;
      const fecha = document.getElementById('editar-fecha').value;

      let formData = new FormData();
      formData.append('id', id);
      formData.append('nombre_titulo_pieza', nombre);
      formData.append('ubicacion_fisica', ubicacion);
      formData.append('descripcion', descripcion);
      formData.append('anio', fecha);
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
    url: 'index.php?uri=admin/acervo_general_editar',
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
