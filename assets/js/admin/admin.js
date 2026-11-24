document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("accordionSidebar");
  const toggleIcon = document.getElementById("toggleIcon");

  if (!sidebar || !toggleIcon) return;

  initializeSidebarState(sidebar, toggleIcon);
  observeSidebarToggle(sidebar, toggleIcon);
  pageActiveSidebar();
});

/**
 * Aplica el estado inicial del sidebar según sessionStorage.
 */
function initializeSidebarState(sidebar, toggleIcon) {
  const isToggled = parseInt(sessionStorage.getItem("toggled")) === 1;

  if (isToggled) {
    sidebar.classList.add("toggled");
    updateToggleIcon(toggleIcon, true);
    sessionStorage.setItem("toggled", 1);
  }
}

/**
 * Observa cambios en la clase del sidebar para actualizar el ícono y el estado.
 */
function observeSidebarToggle(sidebar, toggleIcon) {
  const observer = new MutationObserver(() => {
    const isToggled = sidebar.classList.contains("toggled");
    updateToggleIcon(toggleIcon, isToggled);
    sessionStorage.setItem("toggled", isToggled ? 1 : 0);
  });

  observer.observe(sidebar, {
    attributes: true,
    attributeFilter: ["class"],
  });
}

/**
 * Actualiza el ícono del botón de toggle según el estado del sidebar.
 */
function updateToggleIcon(icon, isToggled) {
  icon.classList.toggle("bx-menu", isToggled);
  icon.classList.toggle("bx-arrow-to-left-stroke", !isToggled);
}

function pageActiveSidebar() {
  const sidebar = document.getElementById("accordionSidebar");
  if (!sidebar) return;

  const navItems = sidebar.querySelectorAll(".nav-item");
  const currentPage = getCurrentPage();

  navItems.forEach((item) => {
    const page = item.getAttribute("data-page");
    const isDashboardFallback = currentPage === "admin" && page === "dashboard";
    const isExactMatch = currentPage === page;

    if (isDashboardFallback || isExactMatch) {
      item.classList.add("active");
    }
  });
}

function getCurrentPage() {
  const segments = window.location.pathname.split("/").filter(Boolean);
  return segments.pop() || "";
}

/**
 * Control global para evitar que los menús dropdown dentro de tablas (.table-responsive)
 * queden atrapados por el overflow o generen scrollbars verticales innecesarios.
 */
document.addEventListener("DOMContentLoaded", function () {
  $(document).on("show.bs.dropdown", ".table-responsive", function () {
    $(this).css("overflow", "inherit");
  });

  $(document).on("hidden.bs.dropdown", ".table-responsive", function () {
    $(this).css("overflow", "");
  });
});

/**
 * Monitor Global de Inactividad de Sesión (30 Minutos)
 * - Registra la actividad del usuario en todas las pestañas abiertas vía localStorage.
 * - Si el usuario está activo, renueva la sesión silenciosamente mediante ping cada 5 minutos.
 * - Si no hay actividad durante 28 minutos, muestra una alerta modal interactiva con cuenta regresiva.
 * - Al alcanzar los 30 minutos de inactividad, finaliza la sesión y redirige a login.
 * - Captura errores 401 en cualquier llamada AJAX para redirigir a login de inmediato.
 */
document.addEventListener("DOMContentLoaded", function () {
  const TIMEOUT_MS = 30 * 60 * 1000;       // 30 minutos límite
  const WARNING_MS = 28 * 60 * 1000;       // Advertencia a los 28 minutos (2 min para responder)
  const PING_INTERVAL_MS = 5 * 60 * 1000;  // Ping al servidor cada 5 minutos si hubo actividad
  const STORAGE_KEY = "sipac_last_active_time";

  let localActivityTime = Date.now();
  let hasPendingActivity = false;
  let isWarningOpen = false;
  let countdownTimer = null;

  // Inicializar tiempo en storage si no existe
  if (!localStorage.getItem(STORAGE_KEY)) {
    localStorage.setItem(STORAGE_KEY, Date.now());
  }

  function getLatestActivity() {
    const stored = parseInt(localStorage.getItem(STORAGE_KEY), 10);
    return Math.max(localActivityTime, isNaN(stored) ? 0 : stored);
  }

  function registerActivity() {
    localActivityTime = Date.now();
    localStorage.setItem(STORAGE_KEY, localActivityTime);
    hasPendingActivity = true;
  }

  // Escuchar eventos de interacción activa del usuario
  ["mousemove", "mousedown", "keydown", "scroll", "touchstart"].forEach((ev) => {
    window.addEventListener(ev, registerActivity, { passive: true });
  });

  // Heartbeat periódico: si el usuario ha estado interactuando, renovar la sesión en servidor
  setInterval(function () {
    const elapsed = Date.now() - getLatestActivity();
    if (hasPendingActivity && elapsed < WARNING_MS) {
      hasPendingActivity = false;
      $.get("admin/ping_session").catch(function () {});
    }
  }, PING_INTERVAL_MS);

  // Verificador de inactividad cada 5 segundos
  setInterval(function () {
    const elapsed = Date.now() - getLatestActivity();

    // Caso 1: 30 minutos o más de inactividad -> cerrar sesión
    if (elapsed >= TIMEOUT_MS) {
      cerrarSesionInactiva();
      return;
    }

    // Caso 2: Entre 28 y 30 minutos de inactividad -> advertencia previa
    if (elapsed >= WARNING_MS) {
      if (!isWarningOpen) {
        mostrarAdvertenciaInactividad();
      }
    } else {
      // Si la advertencia estaba abierta pero el usuario tuvo actividad en otra pestaña
      if (isWarningOpen && typeof Swal !== "undefined" && Swal.isVisible()) {
        isWarningOpen = false;
        clearInterval(countdownTimer);
        Swal.close();
      }
    }
  }, 5000);

  function mostrarAdvertenciaInactividad() {
    isWarningOpen = true;
    const remainingSeconds = Math.max(0, Math.round((TIMEOUT_MS - (Date.now() - getLatestActivity())) / 1000));

    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "¿Sigues trabajando?",
        html: `Por motivos de seguridad, tu sesión se cerrará automáticamente en <strong id="sipac-countdown-sec" style="color: #611735; font-size: 1.25rem;">${remainingSeconds}</strong> segundos debido a inactividad.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "<i class='bx bx-check-circle me-1'></i> Mantener sesión activa",
        cancelButtonText: "<i class='bx bx-log-out-circle me-1'></i> Salir",
        confirmButtonColor: "#611735",
        cancelButtonColor: "#6c757d",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: function () {
          const $cnt = document.getElementById("sipac-countdown-sec");
          countdownTimer = setInterval(function () {
            const sec = Math.max(0, Math.round((TIMEOUT_MS - (Date.now() - getLatestActivity())) / 1000));
            if ($cnt) $cnt.textContent = sec;
            if (sec <= 0) {
              clearInterval(countdownTimer);
              cerrarSesionInactiva();
            }
          }, 1000);
        },
        willClose: function () {
          clearInterval(countdownTimer);
        }
      }).then(function (result) {
        clearInterval(countdownTimer);
        isWarningOpen = false;

        if (result.isConfirmed) {
          // Usuario confirma que desea continuar
          registerActivity();
          hasPendingActivity = false;
          $.get("admin/ping_session").done(function () {
            if (typeof toastr !== "undefined") {
              toastr.success("Sesión extendida correctamente.");
            }
          });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          window.location.href = "logout";
        }
      });
    }
  }

  function cerrarSesionInactiva() {
    clearInterval(countdownTimer);
    localStorage.removeItem(STORAGE_KEY);
    window.location.href = "login?timeout=1";
  }

  // Interceptor global para llamadas AJAX que fallen con 401 Unauthorized
  $(document).ajaxError(function (event, jqXHR) {
    if (jqXHR.status === 401) {
      try {
        const data = JSON.parse(jqXHR.responseText);
        if (data.expired) {
          window.location.href = data.redirect || "login?timeout=1";
          return;
        }
      } catch (e) {}
      window.location.href = "login?timeout=1";
    }
  });
});
