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
