function initMobileMenu() {
  const menuButton = document.querySelector("[data-action='toggle-mobile-menu']");
  const closeButtons = document.querySelectorAll("[data-action='close-mobile-menu']");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.querySelector(".sidebar-overlay");

  if (!menuButton || !sidebar || !overlay) {
    return;
  }

  function openMenu() {
    sidebar.classList.add("is-open");
    overlay.classList.add("is-visible");
    document.body.classList.add("mobile-menu-open");
  }

  function closeMenu() {
    sidebar.classList.remove("is-open");
    overlay.classList.remove("is-visible");
    document.body.classList.remove("mobile-menu-open");
  }

  menuButton.addEventListener("click", () => {
    const isOpen = sidebar.classList.contains("is-open");

    if (isOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  closeButtons.forEach((button) => {
    button.addEventListener("click", closeMenu);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeMenu();
    }
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 768) {
      closeMenu();
    }
  });
}

document.addEventListener("DOMContentLoaded", initMobileMenu);