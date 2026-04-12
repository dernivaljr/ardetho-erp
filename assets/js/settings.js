const SETTINGS_STORAGE_KEY = "ardetho_settings";

const DEFAULT_SETTINGS = {
  themeMode: "light",
  sidebarCompact: false,
  dashboardShortcuts: true,
  alertsExpiration: true,
  alertsOrders: true,
  dailySummary: false,
  language: "Português (Brasil)",
  dateFormat: "DD/MM/AAAA",
  timezone: "America/Sao_Paulo",
  mainModule: "Dashboard",
  priorityModule: "Financeiro",
  startupView: "Resumo executivo"
};

function renderSettingsUser() {
  const currentUser = getCurrentUser() || appData.currentUser;

  const userNameEls = document.querySelectorAll("[data-user='name']");
  const userRoleEls = document.querySelectorAll("[data-user='role']");
  const userAvatarEls = document.querySelectorAll("[data-user='avatar']");

  userNameEls.forEach((el) => {
    el.textContent = currentUser.name;
  });

  userRoleEls.forEach((el) => {
    el.textContent = currentUser.role;
  });

  userAvatarEls.forEach((el) => {
    el.textContent = currentUser.avatar || "AD";
  });
}

function getStoredSettings() {
  const stored = storage.get(SETTINGS_STORAGE_KEY, null);
  return stored ? { ...DEFAULT_SETTINGS, ...stored } : { ...DEFAULT_SETTINGS };
}

function saveStoredSettings(settings) {
  return storage.save(SETTINGS_STORAGE_KEY, settings);
}

function resetStoredSettings() {
  return storage.save(SETTINGS_STORAGE_KEY, { ...DEFAULT_SETTINGS });
}

function setSwitchState(element, isActive) {
  if (!element) {
    return;
  }

  element.classList.toggle("active", Boolean(isActive));
  element.setAttribute("aria-pressed", Boolean(isActive) ? "true" : "false");
}

function getSwitchState(element) {
  return element ? element.classList.contains("active") : false;
}

function applySettingsToForm() {
  const settings = getStoredSettings();

  const themeMode = document.getElementById("theme-mode");
  if (themeMode) themeMode.value = settings.themeMode || "light";
  setSwitchState(document.getElementById("setting-sidebar-compact"), settings.sidebarCompact);
  setSwitchState(document.getElementById("setting-dashboard-shortcuts"), settings.dashboardShortcuts);

  setSwitchState(document.getElementById("setting-alerts-expiration"), settings.alertsExpiration);
  setSwitchState(document.getElementById("setting-alerts-orders"), settings.alertsOrders);
  setSwitchState(document.getElementById("setting-daily-summary"), settings.dailySummary);

  const language = document.getElementById("language");
  const dateFormat = document.getElementById("date-format");
  const timezone = document.getElementById("timezone");
  const mainModule = document.getElementById("main-module");
  const priorityModule = document.getElementById("priority-module");
  const startupView = document.getElementById("startup-view");

  if (language) language.value = settings.language;
  if (dateFormat) dateFormat.value = settings.dateFormat;
  if (timezone) timezone.value = settings.timezone;
  if (mainModule) mainModule.value = settings.mainModule;
  if (priorityModule) priorityModule.value = settings.priorityModule;
  if (startupView) startupView.value = settings.startupView;
}

function getSettingsFormData() {
  return {
    themeMode: document.getElementById("theme-mode")?.value || DEFAULT_SETTINGS.themeMode,
    sidebarCompact: getSwitchState(document.getElementById("setting-sidebar-compact")),
    dashboardShortcuts: getSwitchState(document.getElementById("setting-dashboard-shortcuts")),
    alertsExpiration: getSwitchState(document.getElementById("setting-alerts-expiration")),
    alertsOrders: getSwitchState(document.getElementById("setting-alerts-orders")),
    dailySummary: getSwitchState(document.getElementById("setting-daily-summary")),
    language: document.getElementById("language")?.value || DEFAULT_SETTINGS.language,
    dateFormat: document.getElementById("date-format")?.value || DEFAULT_SETTINGS.dateFormat,
    timezone: document.getElementById("timezone")?.value || DEFAULT_SETTINGS.timezone,
    mainModule: document.getElementById("main-module")?.value || DEFAULT_SETTINGS.mainModule,
    priorityModule: document.getElementById("priority-module")?.value || DEFAULT_SETTINGS.priorityModule,
    startupView: document.getElementById("startup-view")?.value || DEFAULT_SETTINGS.startupView
  };
}

function renderSettingsSummary() {
  const settings = getStoredSettings();

  const themeSummary = document.getElementById("settings-summary-theme");
  const themeBadge = document.getElementById("settings-summary-theme-badge");
  const languageSummary = document.getElementById("settings-summary-language");
  const languageBadge = document.getElementById("settings-summary-language-badge");
  const prioritySummary = document.getElementById("settings-summary-priority-module");
  const priorityBadge = document.getElementById("settings-summary-priority-badge");

  if (themeSummary) {
    themeSummary.textContent =
      settings.themeMode === "dark"
        ? "Tema escuro configurado para o ambiente."
        : "Interface clara com foco em produtividade e leitura.";
  }

  if (themeBadge) {
    themeBadge.textContent = settings.themeMode === "dark" ? "Escuro" : "Claro";
    themeBadge.className = settings.themeMode === "dark" ? "badge-warning" : "badge-success";
  }

  if (languageSummary) {
    languageSummary.textContent = `${settings.language} definido como idioma principal do sistema.`;
  }

  if (languageBadge) {
    languageBadge.textContent = "Atual";
    languageBadge.className = "badge-info";
  }

  if (prioritySummary) {
    prioritySummary.textContent = `${settings.priorityModule} configurado como foco principal do ambiente.`;
  }

  if (priorityBadge) {
    priorityBadge.textContent = "Config.";
    priorityBadge.className = "badge-warning";
  }
}

function applyVisualSettings() {
  const settings = getStoredSettings();
  const sidebar = document.querySelector(".sidebar");
  const body = document.body;

  if (sidebar) {
    sidebar.classList.toggle("sidebar-compact", Boolean(settings.sidebarCompact));
  }

  if (body) {
  body.classList.toggle("theme-light", settings.themeMode === "light");
  body.classList.toggle("theme-dark", settings.themeMode === "dark");
  }
}

function refreshSettingsPage() {
  applySettingsToForm();
  renderSettingsSummary();
  applyVisualSettings();
}

function toggleSettingSwitch(element) {
  if (!element) {
    return;
  }

  const isActive = element.classList.contains("active");
  setSwitchState(element, !isActive);
}

function saveSettings() {
  const data = getSettingsFormData();
  saveStoredSettings(data);
  refreshSettingsPage();
  alert("Configurações salvas com sucesso.");
}

function resetSettings() {
  resetStoredSettings();
  refreshSettingsPage();
  alert("Configurações restauradas para o padrão.");
}

function bindSettingsActions() {
  document.addEventListener("click", (event) => {
    const switchButton = event.target.closest("[data-setting-switch]");
    const resetButton = event.target.closest("[data-action='reset-settings']");
    const saveButton = event.target.closest("[data-action='save-settings']");

    if (switchButton) {
      event.preventDefault();
      toggleSettingSwitch(switchButton);
      return;
    }

    if (resetButton) {
      event.preventDefault();
      resetSettings();
      return;
    }

    if (saveButton) {
      event.preventDefault();
      saveSettings();
    }
  });

  document.addEventListener("keydown", (event) => {
    const switchButton = event.target.closest("[data-setting-switch]");

    if (!switchButton) {
      return;
    }

    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      toggleSettingSwitch(switchButton);
    }
  });
}

function initializeSettingsPage() {
  const settingsPage = document.body.dataset.page === "settings";

  if (!settingsPage) {
    return;
  }

  renderSettingsUser();
  refreshSettingsPage();
  bindSettingsActions();
}

document.addEventListener("DOMContentLoaded", initializeSettingsPage);