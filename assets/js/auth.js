function getLoginFormElements() {
  return {
    form: document.getElementById("loginForm"),
    emailInput: document.getElementById("email"),
    passwordInput: document.getElementById("password"),
    errorMessage: document.getElementById("loginError")
  };
}

function showLoginError(message) {
  const { errorMessage } = getLoginFormElements();

  if (!errorMessage) {
    return;
  }

  errorMessage.textContent = message;
  errorMessage.classList.remove("hidden");
}

function hideLoginError() {
  const { errorMessage } = getLoginFormElements();

  if (!errorMessage) {
    return;
  }

  errorMessage.textContent = "";
  errorMessage.classList.add("hidden");
}

function validateCredentials(email, password) {
  const systemUser = appData.currentUser;

  return email === systemUser.email && password === systemUser.password;
}

function loginUser(email, password) {
  if (!validateCredentials(email, password)) {
    return false;
  }

  const authenticatedUser = {
    ...appData.currentUser
  };

  delete authenticatedUser.password;

  setCurrentUser(authenticatedUser);

  return true;
}

function logoutUser() {
  try {
    if (typeof storage !== "undefined" && STORAGE_KEYS?.currentUser) {
      storage.remove(STORAGE_KEYS.currentUser);
      localStorage.removeItem(STORAGE_KEYS.currentUser);
    }

    localStorage.removeItem("ardetho_current_user");
    sessionStorage.removeItem("ardetho_current_user");
  } catch (error) {
    console.error("Logout error:", error);
  }

  window.location.replace("login.html");
}

function isUserAuthenticated() {
  try {
    const currentUser =
      (typeof storage !== "undefined" && STORAGE_KEYS?.currentUser
        ? storage.get(STORAGE_KEYS.currentUser)
        : null) ||
      JSON.parse(localStorage.getItem("ardetho_current_user") || "null");

    return Boolean(currentUser && currentUser.email);
  } catch (error) {
    return false;
  }
}

function isLoginPage() {
  return window.location.pathname.includes("login.html");
}

function protectInternalPage() {
  if (isLoginPage()) {
    return;
  }

  if (!isUserAuthenticated()) {
    window.location.replace("login.html");
  }
}

function redirectAuthenticatedUserFromLogin() {
  if (!isLoginPage()) {
    return;
  }

  if (isUserAuthenticated()) {
    window.location.replace("dashboard.html");
  }
}

function handleLoginSubmit(event) {
  event.preventDefault();

  const { emailInput, passwordInput } = getLoginFormElements();

  if (!emailInput || !passwordInput) {
    return;
  }

  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  hideLoginError();

  if (!email || !password) {
    showLoginError("Preencha e-mail e senha para continuar.");
    return;
  }

  const loginSuccess = loginUser(email, password);

  if (!loginSuccess) {
    showLoginError("E-mail ou senha inválidos.");
    return;
  }

  window.location.replace("dashboard.html");
}

function bindLoginForm() {
  const { form } = getLoginFormElements();

  if (!form) {
    return;
  }

  form.addEventListener("submit", handleLoginSubmit);
}

function bindLogoutButtons() {
  document.addEventListener("click", function (event) {
    const logoutButton = event.target.closest("[data-action='logout']");

    if (!logoutButton) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    logoutUser();
  });
}
function getCurrentPageName() {
  const path = window.location.pathname.split("/").pop();
  return path || "dashboard.html";
}

function getModulePageMap() {
  return {
    clients: "clients.html",
    products: "products.html",
    sales: "sales.html",
    financial: "financial.html",
    reports: "reports.html",
    hr: "hr.html",
    schedule: "schedule.html",
    inventory: "inventory.html"
  };
}

function getAlwaysAllowedPages() {
  return [
    "dashboard.html",
    "erp-modules.html",
    "settings.html",
    "profile.html",
    "login.html"
  ];
}

function getInactiveModulePages() {
  const activeModules = getActiveModules();
  const pageMap = getModulePageMap();

  return activeModules
    .filter((module) => !module.active && pageMap[module.slug])
    .map((module) => pageMap[module.slug]);
}

function protectInactiveModulePage() {
  const currentPage = getCurrentPageName();

  if (getAlwaysAllowedPages().includes(currentPage)) {
    return;
  }

  const inactivePages = getInactiveModulePages();

  if (inactivePages.includes(currentPage)) {
    window.location.replace("dashboard.html");
  }
}

function updateSidebarVisibility() {
  const activeModules = getActiveModules();
  const pageMap = getModulePageMap();

  const pageToSlugMap = Object.entries(pageMap).reduce((acc, [slug, page]) => {
    acc[page] = slug;
    return acc;
  }, {});

  const activeMap = new Map(
    activeModules.map((module) => [module.slug, Boolean(module.active)])
  );

  const sidebarLinks = document.querySelectorAll(".sidebar-nav a");

  sidebarLinks.forEach((link) => {
    const href = link.getAttribute("href");
    const slug = pageToSlugMap[href];

    if (!slug) {
      link.style.display = "";
      return;
    }

    const isActive = activeMap.has(slug) ? activeMap.get(slug) : true;
    link.style.display = isActive ? "" : "none";
  });
}

function getGlobalSettings() {
    const fallbackSettings = {
      themeMode: "light",
      sidebarCompact: false,
      dashboardShortcuts: true
    };

  try {
    if (typeof storage !== "undefined") {
      const stored = storage.get("ardetho_settings", null);
      return stored ? { ...fallbackSettings, ...stored } : fallbackSettings;
    }

    const raw = localStorage.getItem("ardetho_settings");
    return raw ? { ...fallbackSettings, ...JSON.parse(raw) } : fallbackSettings;
  } catch (error) {
    return fallbackSettings;
  }
}

function applyGlobalVisualSettings() {
  const settings = getGlobalSettings();
  const sidebar = document.querySelector(".sidebar");
  const body = document.body;
  const dashboardShortcuts = document.getElementById("dashboard-shortcuts");

  if (sidebar) {
    sidebar.classList.toggle("sidebar-compact", Boolean(settings.sidebarCompact));
  }

  if (body) {
  body.classList.toggle("theme-light", settings.themeMode === "light");
  body.classList.toggle("theme-dark", settings.themeMode === "dark");
  }

  if (dashboardShortcuts) {
    dashboardShortcuts.style.display = settings.dashboardShortcuts ? "" : "none";
  }
}

function initializeAuth() {
  redirectAuthenticatedUserFromLogin();
  protectInternalPage();
  protectInactiveModulePage();
  bindLoginForm();
  bindLogoutButtons();
  updateSidebarVisibility();
  applyGlobalVisualSettings();
}

document.addEventListener("DOMContentLoaded", initializeAuth);