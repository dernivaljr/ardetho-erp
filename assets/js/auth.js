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
function getUsersData() {
  return getAppSection("users", appData.users || []);
}

function getCompaniesData() {
  return getAppSection("companies", appData.companies || []);
}

function findUserByCredentials(email, password) {
  const users = getUsersData();

  return (
    users.find((user) => {
      return user.email === email && user.password === password;
    }) || null
  );
}

function findCompanyById(companyId) {
  const companies = getCompaniesData();

  return (
    companies.find((company) => {
      return company.id === companyId;
    }) || null
  );
}
function validateCredentials(email, password) {
  return Boolean(findUserByCredentials(email, password));
}

function loginUser(email, password) {
  const user = findUserByCredentials(email, password);

  if (!user) {
    return false;
  }

  const authenticatedUser = { ...user };
  delete authenticatedUser.password;

  setCurrentUser(authenticatedUser);

  const company = findCompanyById(user.companyId);

  if (company) {
    setCurrentCompany(company);
  }

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
    
    if (typeof storage !== "undefined" && STORAGE_KEYS?.currentCompany) {
      storage.remove(STORAGE_KEYS.currentCompany);
      localStorage.removeItem(STORAGE_KEYS.currentCompany);
    }

    localStorage.removeItem("ardetho_current_company_profile");
    sessionStorage.removeItem("ardetho_current_company_profile");
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
const COMPANY_PROFILE_STORAGE_KEY = "ardetho_current_company_profile";

const DEFAULT_COMPANY_PROFILE = {
  companyName: "Ardetho ERP",
  companyDisplayName: "Ardetho ERP",
  companyLogoUrl: "assets/images/ardetho-logo.png",
  companyIconUrl: "assets/images/ardetho-icon.png",
  brandPrimaryColor: "#2563EB",
  brandAccentColor: "#60A5FA"
};

function getCurrentCompanyProfile() {
  try {
    if (typeof storage !== "undefined") {
      const stored = storage.get(COMPANY_PROFILE_STORAGE_KEY, null);
      return stored ? { ...DEFAULT_COMPANY_PROFILE, ...stored } : { ...DEFAULT_COMPANY_PROFILE };
    }

    const raw = localStorage.getItem(COMPANY_PROFILE_STORAGE_KEY);
    return raw ? { ...DEFAULT_COMPANY_PROFILE, ...JSON.parse(raw) } : { ...DEFAULT_COMPANY_PROFILE };
  } catch (error) {
    return { ...DEFAULT_COMPANY_PROFILE };
  }
}

function applyGlobalCompanyBranding() {
  const companyProfile = getCurrentCompanyProfile();
  const root = document.documentElement;

  if (root) {
    root.style.setProperty("--brand-primary-custom", companyProfile.brandPrimaryColor);
    root.style.setProperty("--brand-accent-custom", companyProfile.brandAccentColor);
  }

  const fullLogos = document.querySelectorAll(".brand-logo-full");
  const iconLogos = document.querySelectorAll(".brand-logo-icon");

  fullLogos.forEach((logo) => {
    logo.src = companyProfile.companyLogoUrl || DEFAULT_COMPANY_PROFILE.companyLogoUrl;
    logo.alt = companyProfile.companyDisplayName || companyProfile.companyName || "Empresa";
  });

  iconLogos.forEach((logo) => {
    logo.src = companyProfile.companyIconUrl || DEFAULT_COMPANY_PROFILE.companyIconUrl;
    logo.alt = companyProfile.companyDisplayName || companyProfile.companyName || "Empresa";
  });

  const favicon =
  document.querySelector("link[rel='icon']") ||
  document.querySelector("link[rel='shortcut icon']");
  if (favicon) {
    favicon.href = companyProfile.companyIconUrl || DEFAULT_COMPANY_PROFILE.companyIconUrl;
  }
}
function applyCurrentUserToInterface() {
  const currentUser =
    typeof getCurrentUser === "function" ? getCurrentUser() : null;

  if (!currentUser) {
    return;
  }

  const userName = currentUser.name || "Admin User";
  const userRole = currentUser.role || "Administrador";
  const userInitials = getUserInitialsFromName(userName);

  document.querySelectorAll("[data-user='name']").forEach((el) => {
    el.textContent = userName;
  });

  document.querySelectorAll("[data-user='role']").forEach((el) => {
    el.textContent = userRole;
  });

  document.querySelectorAll("[data-user='avatar']").forEach((el) => {
    el.textContent = userInitials;
  });
}

function getUserInitialsFromName(name) {
  if (!name) {
    return "AD";
  }

  const parts = name.trim().split(/\s+/).filter(Boolean);

  if (parts.length === 1) {
    return parts[0].slice(0, 2).toUpperCase();
  }

  return `${parts[0][0] || ""}${parts[1][0] || ""}`.toUpperCase();
}

function initializeAuth() {
  redirectAuthenticatedUserFromLogin();
  protectInternalPage();
  protectInactiveModulePage();
  bindLoginForm();
  bindLogoutButtons();
  updateSidebarVisibility();
  applyGlobalVisualSettings();
  applyGlobalCompanyBranding();
  applyCurrentUserToInterface();
}

document.addEventListener("DOMContentLoaded", initializeAuth);