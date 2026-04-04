function getProfileUser() {
  return getCurrentUser();
}

function renderProfileTopbarUser() {
  const currentUser = getProfileUser();

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

function renderProfileCard() {
  const currentUser = getProfileUser();

  const largeAvatar = document.getElementById("profile-avatar-large");
  const profileName = document.getElementById("profile-name");
  const profileEmail = document.getElementById("profile-email");
  const profileRole = document.getElementById("profile-role");
  const profileLastAccess = document.getElementById("profile-last-access");

  if (largeAvatar) {
    largeAvatar.textContent = currentUser.avatar || "AD";
  }

  if (profileName) {
    profileName.textContent = currentUser.name;
  }

  if (profileEmail) {
    profileEmail.textContent = currentUser.email;
  }

  if (profileRole) {
    profileRole.textContent = currentUser.role;
  }

  if (profileLastAccess) {
    profileLastAccess.textContent = currentUser.lastAccess || "—";
  }
}

function renderProfileForm() {
  const currentUser = getProfileUser();

  const fullNameField = document.getElementById("full-name");
  const emailField = document.getElementById("email");
  const roleField = document.getElementById("role");
  const departmentField = document.getElementById("department");

  if (fullNameField) {
    fullNameField.value = currentUser.name || "";
  }

  if (emailField) {
    emailField.value = currentUser.email || "";
  }

  if (roleField) {
    roleField.value = currentUser.role || "";
  }

  if (departmentField) {
    departmentField.value = currentUser.department || "";
  }
}

function renderProfilePermissions() {
  const currentUser = getProfileUser();
  const permissions = currentUser.permissions || {};

  const accessLevel = document.getElementById("profile-access-level");
  const settingsPermission = document.getElementById("profile-settings-permission");
  const reportsPermission = document.getElementById("profile-reports-permission");

  if (accessLevel) {
    accessLevel.textContent = permissions.fullAccess ? "Total" : "Parcial";
    accessLevel.className = permissions.fullAccess ? "badge-success" : "badge-warning";
  }

  if (settingsPermission) {
    settingsPermission.textContent = permissions.canEditSettings ? "Permitido" : "Restrito";
    settingsPermission.className = permissions.canEditSettings ? "badge-success" : "badge-warning";
  }

  if (reportsPermission) {
    reportsPermission.textContent = permissions.canExportReports ? "Ativo" : "Inativo";
    reportsPermission.className = permissions.canExportReports ? "badge-info" : "badge-neutral";
  }
}

function renderProfilePreferences() {
  const currentUser = getProfileUser();
  const preferences = currentUser.preferences || {};

  const dashboardAlertsSwitch = document.getElementById("profile-dashboard-alerts");
  const dailySummarySwitch = document.getElementById("profile-daily-summary");

  if (dashboardAlertsSwitch) {
    dashboardAlertsSwitch.classList.toggle("active", Boolean(preferences.receiveDashboardAlerts));
    dashboardAlertsSwitch.setAttribute("aria-pressed", String(Boolean(preferences.receiveDashboardAlerts)));
  }

  if (dailySummarySwitch) {
    dashboardAlertsSwitch?.classList.contains("active");
    dailySummarySwitch.classList.toggle("active", Boolean(preferences.showDailySummary));
    dailySummarySwitch.setAttribute("aria-pressed", String(Boolean(preferences.showDailySummary)));
  }
}

function bindProfileSwitches() {
  const switches = document.querySelectorAll("[data-profile-switch]");

  switches.forEach((switchElement) => {
    switchElement.addEventListener("click", () => {
      const isActive = switchElement.classList.contains("active");
      switchElement.classList.toggle("active", !isActive);
      switchElement.setAttribute("aria-pressed", String(!isActive));
    });

    switchElement.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        const isActive = switchElement.classList.contains("active");
        switchElement.classList.toggle("active", !isActive);
        switchElement.setAttribute("aria-pressed", String(!isActive));
      }
    });
  });
}

function initializeProfilePage() {
  const profilePage = document.body.dataset.page === "profile";

  if (!profilePage) {
    return;
  }

  renderProfileTopbarUser();
  renderProfileCard();
  renderProfileForm();
  renderProfilePermissions();
  renderProfilePreferences();
  bindProfileSwitches();
}

document.addEventListener("DOMContentLoaded", initializeProfilePage);