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
    url: "pruebas/mockDataPaginated",
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
    url: "pruebas/mockData",
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
        <td><img src="${pieza.image}" alt="${pieza.nombre}" class="img__miniatura" onerror="this.src='https://via.placeholder.com/200x300?text=Sin+Imagen'" /></td>
        <td>${pieza.nombre}</td>
        <td>${pieza.autor}</td>
        <td>${pieza.descripcion}</td>
        <td>${pieza.fecha}</td>
        <td>
          <div class="dropdown">
            <button class="btn btn__actions btn-sm btn-outline-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class='bx  bx-caret-down'></i> 
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#"> <i class='bx text-info bx__iconmenu bx-eye-alt'></i> Ver</a></li>
              <li><a class="dropdown-item" href="#"><i class='bx text-warning bx__iconmenu bx-pencil-circle'></i>  Editar</a></li>
              <hr class="dropdown-divider">
              <li><a class="dropdown-item" href="#"><i class='bx text-danger bx__iconmenu bx-trash'></i>  Eliminar</a></li>
            </ul>
          </div>
        </td>
      `;
    tabla.appendChild(fila);
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
