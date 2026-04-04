function renderSettingsUser() {
  const currentUser = getCurrentUser();

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

function getSettingsPreferences() {
  const currentUser = getCurrentUser();
  return currentUser.preferences || appData.currentUser.preferences;
}

function setSwitchState(element, isActive) {
  if (!element) {
    return;
  }

  element.classList.toggle("active", isActive);
  element.setAttribute("aria-pressed", String(isActive));
}

function renderSettingsPreferences() {
  const preferences = getSettingsPreferences();

  setSwitchState(document.getElementById("setting-theme-light"), preferences.theme === "light");
  setSwitchState(document.getElementById("setting-sidebar-compact"), false);
  setSwitchState(document.getElementById("setting-dashboard-shortcuts"), true);

  setSwitchState(document.getElementById("setting-alerts-expiration"), true);
  setSwitchState(document.getElementById("setting-alerts-orders"), true);
  setSwitchState(document.getElementById("setting-daily-summary"), preferences.showDailySummary);

  const languageField = document.getElementById("language");
  const dateFormatField = document.getElementById("date-format");
  const timezoneField = document.getElementById("timezone");
  const mainModuleField = document.getElementById("main-module");
  const priorityModuleField = document.getElementById("priority-module");
  const startupViewField = document.getElementById("startup-view");

  if (languageField) {
    languageField.value = "Português (Brasil)";
  }

  if (dateFormatField) {
    dateFormatField.value = "DD/MM/AAAA";
  }

  if (timezoneField) {
    timezoneField.value = "America/Sao_Paulo";
  }

  if (mainModuleField) {
    mainModuleField.value = "Dashboard";
  }

  if (priorityModuleField) {
    priorityModuleField.value = preferences.priorityModule || "Financeiro";
  }

  if (startupViewField) {
    startupViewField.value = preferences.startupView || "Resumo executivo";
  }
}

function bindSettingsSwitches() {
  const switches = document.querySelectorAll("[data-setting-switch]");

  switches.forEach((switchElement) => {
    switchElement.addEventListener("click", () => {
      const isActive = switchElement.classList.contains("active");
      setSwitchState(switchElement, !isActive);
    });

    switchElement.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        const isActive = switchElement.classList.contains("active");
        setSwitchState(switchElement, !isActive);
      }
    });
  });
}

function saveSettingsPreferences() {
  const currentUser = getCurrentUser();

  const updatedUser = {
    ...currentUser,
    preferences: {
      ...currentUser.preferences,
      theme: document.getElementById("setting-theme-light")?.classList.contains("active")
        ? "light"
        : "light",
      showDailySummary: document.getElementById("setting-daily-summary")?.classList.contains("active") || false,
      language: "pt-BR",
      startupView: document.getElementById("startup-view")?.value || "Resumo executivo",
      priorityModule: document.getElementById("priority-module")?.value || "Financeiro"
    }
  };

  setCurrentUser(updatedUser);
}

function bindSettingsSaveButton() {
  const saveButton = document.querySelector("[data-action='save-settings']");

  if (!saveButton) {
    return;
  }

  saveButton.addEventListener("click", (event) => {
    event.preventDefault();
    saveSettingsPreferences();
    alert("Configurações salvas com sucesso.");
  });
}

function initializeSettingsPage() {
  const settingsPage = document.body.dataset.page === "settings";

  if (!settingsPage) {
    return;
  }

  renderSettingsUser();
  renderSettingsPreferences();
  bindSettingsSwitches();
  bindSettingsSaveButton();
}

document.addEventListener("DOMContentLoaded", initializeSettingsPage);