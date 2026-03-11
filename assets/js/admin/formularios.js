$(document).ready(function () {
  obtenerTiposAcervo();

  // // Inicializar Select2
  // $("#tipoAcervo").select2({
  //   placeholder: "Selecciona un tipo de acervo",
  //   allowClear: true,
  //   width: "100%",
  // });

  // const camposContainer = document.getElementById("camposDisponibles");
  // const toggleBtn = $("#toggleSeleccion");
  // const busquedaInput = $("#busquedaCampos");
  // const contador = $("#contadorCampos");

  // const camposPorAcervo = {
  //   libros: [
  //     "Título",
  //     "Autor",
  //     "ISBN",
  //     "Editorial",
  //     "Año de publicación",
  //     "Número de páginas",
  //     "Idioma",
  //     "Edición",
  //     "Lugar de publicación",
  //     "Categoría",
  //     "Temática",
  //     "Resumen",
  //     "Notas",
  //     "Clasificación Dewey",
  //     "Ubicación física",
  //   ],
  //   revistas: [
  //     "Título",
  //     "Editor",
  //     "Fecha de publicación",
  //     "ISSN",
  //     "Volumen",
  //     "Número",
  //     "Sección",
  //     "Resumen",
  //     "Categoría",
  //     "Frecuencia",
  //   ],
  //   videos: [
  //     "Título",
  //     "Director",
  //     "Duración",
  //     "Formato",
  //     "Año",
  //     "Idioma",
  //     "Subtítulos",
  //     "Productora",
  //     "País de origen",
  //     "Clasificación",
  //   ],
  // };

  // // Función para mostrar campos en la lista
  // function mostrarCampos(tipo) {
  //   const campos = camposPorAcervo[tipo] || [];
  //   camposContainer.innerHTML = "";

  //   if (campos.length > 0) {
  //     busquedaInput.prop("disabled", false);
  //     toggleBtn.prop("disabled", false);
  //   } else {
  //     busquedaInput.prop("disabled", true).val("");
  //     toggleBtn.prop("disabled", true);
  //   }

  //   campos.forEach((campo, index) => {
  //     const item = `
  //           <li class="list-group-item d-flex justify-content-between align-items-center" data-campo="${campo.toLowerCase()}">
  //             <span class="campo-nombre">${campo}</span>
  //             <div class="form-check form-switch m-0">
  //               <input class="form-check-input" type="checkbox" id="campo${index}">
  //             </div>
  //           </li>
  //         `;
  //     camposContainer.innerHTML += item;
  //   });

  //   filtrarCampos(); // Aplica filtro si hay texto (limpia si no)
  //   actualizarBotonSeleccion(); // Actualiza botón y contador
  // }

  // // Función para filtrar campos
  // function filtrarCampos() {
  //   const filtro = busquedaInput.val().toLowerCase().trim();

  //   if (filtro === "") {
  //     const items = camposContainer.getElementsByTagName("li");
  //     for (let i = 0; i < items.length; i++) {
  //       items[i].classList.remove("hiddenItem");
  //     }
  //   } else {
  //     const lista = document.getElementById("camposDisponibles");
  //     const items = lista.getElementsByTagName("li");
  //     const arrayItems = Array.from(items);
  //     for (let i = 0; i < arrayItems.length; i++) {
  //       const campo = arrayItems[i]
  //         .getAttribute("data-campo")
  //         .toLowerCase()
  //         .trim();
  //       const contieneFiltro = campo.includes(filtro);
  //       arrayItems[i].classList.toggle("hiddenItem", !contieneFiltro);
  //     }
  //   }

  //   actualizarBotonSeleccion();
  // }

  // // Función para actualizar botón y contador
  // function actualizarBotonSeleccion() {
  //   const checkboxesVisibles = $(
  //     "#camposDisponibles li:visible input[type='checkbox']"
  //   );
  //   const total = checkboxesVisibles.length;
  //   const seleccionados = checkboxesVisibles.filter(":checked").length;

  //   if (total === 0) {
  //     toggleBtn.prop("disabled", true).text("Seleccionar todo");
  //     contador.text("Campos seleccionados: 0 de 0");
  //     return;
  //   }

  //   toggleBtn.prop("disabled", false);
  //   toggleBtn.text(
  //     seleccionados === total ? "Deseleccionar todo" : "Seleccionar todo"
  //   );
  //   contador.text(`Campos seleccionados: ${seleccionados} de ${total}`);
  // }

  // // Al cambiar tipo de acervo
  // $("#tipoAcervo").on("change", function () {
  //   const tipoSeleccionado = this.value;
  //   const btnLimpiarFiltro = document.getElementById("btnLimpiarFiltro");
  //   const btnGuardarCambios = document.getElementById("btnGuardarCambios");
  //   const contDatosContador = document.getElementById("contDatosContador");

  //   mostrarCampos(tipoSeleccionado);

  //   // Mostrar botón guardar cambios si hay campos
  //   if (tipoSeleccionado) {
  //     contDatosContador.classList.remove("hiddenItem");
  //     btnGuardarCambios.style.display = "block";
  //   } else {
  //     contDatosContador.classList.add("hiddenItem");
  //     btnGuardarCambios.style.display = "none";
  //   }

  //   // Actualizar placeholder
  //   busquedaInput.attr(
  //     "placeholder",
  //     tipoSeleccionado
  //       ? "Buscar campo..."
  //       : "Selecciona un tipo de acervo para buscar campos..."
  //   );

  //   btnLimpiarFiltro.removeAttribute("hidden");
  //   btnLimpiarFiltro.addEventListener("click", function () {
  //     busquedaInput.val("");
  //     filtrarCampos();
  //   });

  //   if (!tipoSeleccionado) {
  //     busquedaInput.attr("disabled", true).val("");
  //     toggleBtn.prop("disabled", true);
  //     camposContainer.innerHTML = "";
  //     contador.text("Campos seleccionados: 0 de 0");
  //     btnLimpiarFiltro.setAttribute("hidden", "true");
  //   }
  // });

  // // Al escribir en búsqueda
  // busquedaInput.on("input", filtrarCampos);

  // // Botón seleccionar/deseleccionar todos visibles
  // toggleBtn.on("click", function () {
  //   const checkboxesVisibles = $(
  //     "#camposDisponibles li:visible input[type='checkbox']"
  //   );
  //   const total = checkboxesVisibles.length;
  //   const seleccionados = checkboxesVisibles.filter(":checked").length;

  //   const seleccionarTodos = seleccionados < total;

  //   checkboxesVisibles.prop("checked", seleccionarTodos);
  //   actualizarBotonSeleccion();
  // });

  // // Detectar cambio manual en checkboxes
  // $(document).on(
  //   "change",
  //   "#camposDisponibles input[type='checkbox']",
  //   function () {
  //     actualizarBotonSeleccion();
  //   }
  // );
});

// btnGuardarCambios = document.getElementById("btnGuardarCambios");
// if (btnGuardarCambios) {
//   btnGuardarCambios.addEventListener("click", function () {
//     const tipoAcervo = document.getElementById("tipoAcervo").value;
//     if (!tipoAcervo) {
//       alert("Por favor, selecciona un tipo de acervo.");
//       return;
//     }

//     const checkboxes = document.querySelectorAll(
//       "#camposDisponibles input[type='checkbox']"
//     );
//     const camposSeleccionados = [];
//     checkboxes.forEach((checkbox, index) => {
//       if (checkbox.checked) {
//         const campoNombre = checkbox
//           .closest("li")
//           .querySelector(".campo-nombre").textContent;
//         camposSeleccionados.push(campoNombre);
//       }
//     });

//     // Aquí puedes enviar 'tipoAcervo' y 'camposSeleccionados' al servidor o procesarlos como necesites
//     console.log("Tipo de Acervo:", tipoAcervo);
//     console.log("Campos Seleccionados:", camposSeleccionados);

//     toastr.success(
//       `Cambios guardados para ${tipoAcervo} con ${camposSeleccionados.length} campos seleccionados.`,
//       "LISTO!"
//     );

//     $("#tipoAcervo").val(null).trigger("change");
//   });
// }

function obtenerTiposAcervo() {
  const formData = new URLSearchParams();

  fetch("pruebas/obtenerTiposAcervo", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    }
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Resultado en data: " + data);
    })
    .catch((error) => {
      console.error("Error en fetch:", error);
    });
}