function toggleInstructions(e) {
  const content = document.getElementById("instructionsContent");
  const button = (e && e.currentTarget) ? e.currentTarget : document.getElementById("toggleInstructionsBtn");
  if (!content) return;

  if (content.style.display === "none") {
    content.style.display = "block";
    if (button) button.innerHTML = "<i class='bx bx-hide'></i> Ocultar";
  } else {
    content.style.display = "none";
    if (button) button.innerHTML = "<i class='bx bx-show'></i> Mostrar";
  }
}

$(document).ready(function () {
  const toggleBtn = document.getElementById("toggleInstructionsBtn");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", toggleInstructions);
  }

  // Inicializar Select2 en #select-acervo para evitar opciones en blanco en navegadores
  if (typeof $.fn.select2 !== "undefined" && $("#select-acervo").length) {
    $("#select-acervo").select2({
      placeholder: "Seleccione el tipo de acervo...",
      allowClear: false,
      width: "100%",
      minimumResultsForSearch: Infinity
    });
  }

  const contenedor = document.getElementById("formulario-dinamico");

  $("#select-acervo").on("change", function () {
    const tipo = $(this).val();

    if (!tipo) {
      if (contenedor) contenedor.innerHTML = "";
      const formCard = document.getElementById("formCard");
      if (formCard) formCard.style.display = "none";
      return;
    }

    const formData = new URLSearchParams();
    formData.append("tipo_acervo", tipo);

    fetch("admin/get_formulario_tipo", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json"
      },
      body: formData.toString(),
    })
      .then((response) => {
        if (response.status === 403) {
          return response.json().then(data => {
            throw new Error(data.msg || "No tienes permisos suficientes para realizar esta acción.");
          });
        }
        return response.json();
      })
      .then((data) => {
        const formCard = document.getElementById("formCard");

        if (data.status === true || data.status === 200) {
          if (contenedor) contenedor.innerHTML = data.html;
          if (formCard) formCard.style.display = "block";
          inicializarVistaPrevia(); // Ejecutar script para vista previa
          inicializarCamposDinamicos(); // Lógica de dimensiones y ubicación física
          inicializarEnvioFormulario();
        } else {
          const errorMsg = data.msg || data.message || "No tienes permisos suficientes para realizar esta acción.";
          toastr.error(errorMsg, "Acceso Denegado");
          if (contenedor) {
            contenedor.innerHTML = `
              <div class="text-center py-5 text-danger">
                <i class='bx bx-lock-alt bx-lg mb-2'></i>
                <h5 class="fw-bold">${errorMsg}</h5>
              </div>
            `;
          }
          if (formCard) formCard.style.display = "block";
        }
      })
      .catch((error) => {
        const formCard = document.getElementById("formCard");
        const errorMsg = error.message || "No tienes permisos suficientes para realizar esta acción.";
        toastr.error(errorMsg, "Acceso Denegado");
        if (contenedor) {
          contenedor.innerHTML = `
            <div class="text-center py-5 text-danger">
              <i class='bx bx-lock-alt bx-lg mb-2'></i>
              <h5 class="fw-bold">${errorMsg}</h5>
            </div>
          `;
        }
        if (formCard) formCard.style.display = "block";
        console.error("Error en fetch:", error);
      });
  });

  // Si solo hay una opción de acervo permitida para el rol, seleccionarla y cargar formulario
  const selectElem = document.getElementById("select-acervo");
  if (selectElem) {
    const validOptions = Array.from(selectElem.options).filter(opt => opt.value !== "");
    if (validOptions.length === 1) {
      $("#select-acervo").val(validOptions[0].value).trigger("change");
    }
  }
});

function inicializarVistaPrevia() {
  const imageInput = document.querySelector('input[type="file"]');
  const previewContainer = document.getElementById("previewContainer");
  const previewText = document.getElementById("previewText");
  const previewIcon = previewContainer ? previewContainer.querySelector("i") : null;

  if (!imageInput || !previewContainer || !previewText || !previewIcon) return;

  let previewImage = previewContainer.querySelector("img");

  if (!previewImage) {
    previewImage = document.createElement("img");
    previewImage.id = "previewImage";
    previewImage.className = "img-fluid mt-3 fade-in"; // clase animada
    previewImage.style.maxHeight = "300px";
    previewContainer.appendChild(previewImage);
  }

  imageInput.addEventListener("change", function () {
    const file = this.files[0];

    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        previewImage.src = e.target.result;
        previewImage.classList.add("show"); // 👈 activa fade-in
        previewText.innerText = file.name;
        previewText.classList.add("name-image_success");
        previewIcon.style.display = "none";
        previewContainer.classList.add("preview-reverse");
      };
      reader.readAsDataURL(file);
    } else {
      previewImage.src = "";
      previewImage.classList.remove("show");
      previewText.style.display = "inline";
      previewText.classList.remove("name-image_success");
      previewIcon.style.display = "inline";
      previewText.innerText = "No hay imagen seleccionada";
      previewContainer.classList.remove("preview-reverse");
    }
  });
}

function inicializarCamposDinamicos() {
  // 1. Lógica para dimensiones en Acervo General: Bidimensional vs Tridimensional
  const radiosDimension = $('input[name="tipo_dimension"]');
  if (radiosDimension.length) {
    function toggleDimensiones() {
      const valor = $('input[name="tipo_dimension"]:checked').val();
      if (valor === 'tridimensional') {
        $('.container-campo-cm').hide();
        $('#cm').prop('disabled', true).val('');
        $('.container-campo-medidas').show();
        $('#medidas').prop('disabled', false);
      } else {
        $('.container-campo-cm').show();
        $('#cm').prop('disabled', false);
        $('.container-campo-medidas').hide();
        $('#medidas').prop('disabled', true).val('');
      }
    }
    radiosDimension.off('change').on('change', toggleDimensiones);
    toggleDimensiones();
  }

  // 2. Lógica para Ubicación Física: Museos de la Secretaría vs Otro
  const selectUbi = $('#ubicacion-fisica');
  if (selectUbi.length) {
    function toggleUbiOtro() {
      const valor = selectUbi.val();
      const containerOtro = $('.container-ubicacion-otro');
      const inputOtro = $('#ubicacion-fisica-otro');

      if (valor === 'Otro') {
        containerOtro.slideDown(200);
        inputOtro.prop('required', true);
      } else {
        containerOtro.slideUp(200);
        inputOtro.prop('required', false).val('');
      }
    }
    selectUbi.off('change').on('change', toggleUbiOtro);
    toggleUbiOtro();
  }
}

function inicializarEnvioFormulario() {
  const form = document.getElementById("nuevo-registro");

  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault(); // 👈 Evita el envío tradicional

    // Validación previa para Ubicación Física = 'Otro'
    const selectUbi = $('#ubicacion-fisica');
    if (selectUbi.length && selectUbi.val() === 'Otro') {
      const ubiOtro = ($('#ubicacion-fisica-otro').val() || '').trim();
      if (!ubiOtro) {
        toastr.warning('Por favor especifica la sede o dependencia en Ubicación Física.', 'Campo Requerido');
        $('#ubicacion-fisica-otro').focus();
        return;
      }
    }

    const formData = new FormData(form); // 👈 Captura todos los campos y archivos
    const endpoint = form.getAttribute("action") || "admin/post_registro";

    fetch(endpoint, {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json"
      },
      body: formData,
    })
      .then((response) => {
        if (response.status === 403) {
          return response.json().then(data => {
            throw new Error(data.msg || "No tienes permisos suficientes para realizar esta acción.");
          });
        }
        return response.json();
      })
      .then((data) => {
        if (data.status === 200) {
          // ✅ Mostrar mensaje sin redirigir
          toastr.success(data.msg || "Registro guardado con éxito.", "EXCELENTE");
          console.log("Datos recibidos:", data);
          form.reset();
          setTimeout(inicializarCamposDinamicos, 50);
          const previewImage = document.getElementById("previewImage");
          const previewText = document.getElementById("previewText");
          const previewContainer = document.getElementById("previewContainer");
          const previewIcon = previewContainer ? previewContainer.querySelector("i") : null;
          if (previewImage) {
            previewImage.src = "";
            previewImage.classList.remove("show");
          }
          if (previewText) previewText.style.display = "inline";
          if (previewIcon) previewIcon.style.display = "inline";
          if (previewContainer) window.scrollTo({ top: 0, behavior: "smooth" });
          if (previewText) previewText.classList.remove("name-image_success");
          if (previewText) previewText.innerText = "No hay imagen seleccionada";
          if (previewContainer) previewContainer.classList.remove("preview-reverse");
        } else {
          toastr.error(
            data.msg || data.message || "No tienes permisos suficientes para realizar esta acción.",
            "Acceso Denegado"
          );
          console.error("Error en datos recibidos:", data);
        }
      })
      .catch((error) => {
        console.error("Error al enviar el formulario:", error);
        toastr.error(error.message || "No tienes permisos suficientes para realizar esta acción.", "Acceso Denegado");
      });
  });
}
