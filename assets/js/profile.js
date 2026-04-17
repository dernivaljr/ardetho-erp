function getProfileElements() {
  return {
    fullNameInput: document.getElementById("profile-full-name"),
    emailInput: document.getElementById("profile-email-input"),
    roleInput: document.getElementById("profile-role-input"),
    departmentInput: document.getElementById("profile-department-input"),

    companyNameInput: document.getElementById("company-name"),
    companyDisplayNameInput: document.getElementById("company-display-name"),
    companyLogoUrlInput: document.getElementById("company-logo-url"),
    companyIconUrlInput: document.getElementById("company-icon-url"),
    brandPrimaryColorInput: document.getElementById("brand-primary-color"),
    brandAccentColorInput: document.getElementById("brand-accent-color"),

    profileAvatarLarge: document.getElementById("profile-avatar-large"),
    profileName: document.getElementById("profile-name"),
    profileEmail: document.getElementById("profile-email"),
    profileRole: document.getElementById("profile-role"),
    profileCompanyName: document.getElementById("profile-company-name"),

    brandPrimaryPreview: document.getElementById("brand-primary-preview"),
    brandAccentPreview: document.getElementById("brand-accent-preview"),

    saveButton: document.querySelector("[data-action='save-profile']"),
    resetButton: document.querySelector("[data-action='reset-profile']"),

    sidebarBrandLogoFull: document.getElementById("sidebar-brand-logo-full"),
    sidebarBrandLogoIcon: document.getElementById("sidebar-brand-logo-icon")
  };
}

function getProfileInitials(name) {
  if (!name) return "AD";

  const parts = name.trim().split(/\s+/).filter(Boolean);

  if (parts.length === 1) {
    return parts[0].slice(0, 2).toUpperCase();
  }

  return `${parts[0][0] || ""}${parts[1][0] || ""}`.toUpperCase();
}

function getProfileSessionUser() {
  if (typeof getCurrentUser === "function") {
    return getCurrentUser();
  }

  return null;
}

function getProfileSessionCompany() {
  if (typeof getCurrentCompany === "function") {
    return getCurrentCompany();
  }

  return null;
}

function fillProfilePage() {
  const els = getProfileElements();
  const user = getProfileSessionUser();
  const company = getProfileSessionCompany();

  if (!user || !company) {
    console.warn("Profile page: missing current user or current company.");
    return;
  }

  if (els.fullNameInput) els.fullNameInput.value = user.name || "";
  if (els.emailInput) els.emailInput.value = user.email || "";
  if (els.roleInput) els.roleInput.value = user.role || "";
  if (els.departmentInput) els.departmentInput.value = user.department || "";

  if (els.companyNameInput) els.companyNameInput.value = company.companyName || "";
  if (els.companyDisplayNameInput) els.companyDisplayNameInput.value = company.companyDisplayName || "";
  if (els.companyLogoUrlInput) els.companyLogoUrlInput.value = company.companyLogoUrl || "";
  if (els.companyIconUrlInput) els.companyIconUrlInput.value = company.companyIconUrl || "";
  if (els.brandPrimaryColorInput) els.brandPrimaryColorInput.value = company.brandPrimaryColor || "";
  if (els.brandAccentColorInput) els.brandAccentColorInput.value = company.brandAccentColor || "";

  const initials = getProfileInitials(user.name || "");

  if (els.profileAvatarLarge) els.profileAvatarLarge.textContent = initials;
  if (els.profileName) els.profileName.textContent = user.name || "";
  if (els.profileEmail) els.profileEmail.textContent = user.email || "";
  if (els.profileRole) els.profileRole.textContent = user.role || "";
  if (els.profileCompanyName) {
    els.profileCompanyName.textContent = company.companyDisplayName || company.companyName || "";
  }

  document.querySelectorAll("[data-user='name']").forEach((el) => {
    el.textContent = user.name || "";
  });

  document.querySelectorAll("[data-user='role']").forEach((el) => {
    el.textContent = user.role || "";
  });

  document.querySelectorAll("[data-user='avatar']").forEach((el) => {
    el.textContent = initials;
  });

  if (els.sidebarBrandLogoFull) {
    els.sidebarBrandLogoFull.src = company.companyLogoUrl || "";
    els.sidebarBrandLogoFull.alt = company.companyDisplayName || company.companyName || "Empresa";
  }

  if (els.sidebarBrandLogoIcon) {
    els.sidebarBrandLogoIcon.src = company.companyIconUrl || company.companyLogoUrl || "";
    els.sidebarBrandLogoIcon.alt = company.companyDisplayName || company.companyName || "Empresa";
  }

  if (els.brandPrimaryPreview) {
    els.brandPrimaryPreview.style.background = company.brandPrimaryColor || "#2563EB";
  }

  if (els.brandAccentPreview) {
    els.brandAccentPreview.style.background = company.brandAccentColor || "#60A5FA";
  }

  document.documentElement.style.setProperty(
    "--brand-primary-custom",
    company.brandPrimaryColor || "#2563EB"
  );

  document.documentElement.style.setProperty(
    "--brand-accent-custom",
    company.brandAccentColor || "#60A5FA"
  );

  const favicon =
    document.querySelector("link[rel='icon']") ||
    document.querySelector("link[rel='shortcut icon']");

  if (favicon) {
    favicon.href = company.companyIconUrl || company.companyLogoUrl || "";
  }
}

function saveProfilePage() {
  const els = getProfileElements();
  const currentUser = getProfileSessionUser();
  const currentCompany = getProfileSessionCompany();

  if (!currentUser || !currentCompany) {
    alert("Não foi possível localizar a sessão atual.");
    return;
  }

  const updatedUser = {
    ...currentUser,
    name: els.fullNameInput?.value.trim() || currentUser.name,
    email: els.emailInput?.value.trim() || currentUser.email,
    role: els.roleInput?.value.trim() || currentUser.role,
    department: els.departmentInput?.value.trim() || currentUser.department
  };

  const updatedCompany = {
    ...currentCompany,
    companyName: els.companyNameInput?.value.trim() || currentCompany.companyName,
    companyDisplayName:
      els.companyDisplayNameInput?.value.trim() || currentCompany.companyDisplayName,
    companyLogoUrl:
      els.companyLogoUrlInput?.value.trim() || currentCompany.companyLogoUrl,
    companyIconUrl:
      els.companyIconUrlInput?.value.trim() || currentCompany.companyIconUrl,
    brandPrimaryColor:
      els.brandPrimaryColorInput?.value.trim() || currentCompany.brandPrimaryColor,
    brandAccentColor:
      els.brandAccentColorInput?.value.trim() || currentCompany.brandAccentColor
  };

  const userSaved =
    typeof setCurrentUser === "function" ? setCurrentUser(updatedUser) : false;

  const companySaved =
    typeof setCurrentCompany === "function" ? setCurrentCompany(updatedCompany) : false;

  if (!userSaved || !companySaved) {
    alert("Erro ao salvar perfil.");
    return;
  }

  fillProfilePage();
  alert("Perfil salvo com sucesso.");
}

function resetProfilePage() {
  const currentUser = getProfileSessionUser();
  const currentCompany = getProfileSessionCompany();

  if (!currentUser || !currentCompany) {
    alert("Não foi possível restaurar o perfil.");
    return;
  }

  const resetUser = {
    ...currentUser,
    name: "Admin User",
    email: "admin@ardetho.com",
    role: "Administrador",
    department: "Gestão"
  };

  const resetCompany = {
    ...currentCompany,
    companyName: "Ardetho ERP",
    companyDisplayName: "Ardetho ERP",
    companyLogoUrl: "assets/images/ardetho-logo.png",
    companyIconUrl: "assets/images/ardetho-icon.png",
    brandPrimaryColor: "#2563EB",
    brandAccentColor: "#60A5FA"
  };

  const userSaved =
    typeof setCurrentUser === "function" ? setCurrentUser(resetUser) : false;

  const companySaved =
    typeof setCurrentCompany === "function" ? setCurrentCompany(resetCompany) : false;

  if (!userSaved || !companySaved) {
    alert("Erro ao restaurar perfil.");
    return;
  }

  fillProfilePage();
  alert("Perfil restaurado para o padrão.");
}

function bindProfileButtons() {
  const els = getProfileElements();

  if (els.saveButton) {
    els.saveButton.addEventListener("click", function (event) {
      event.preventDefault();
      saveProfilePage();
    });
  }

  if (els.resetButton) {
    els.resetButton.addEventListener("click", function (event) {
      event.preventDefault();
      resetProfilePage();
    });
  }
}

function bindProfileLivePreview() {
  const els = getProfileElements();

  const inputs = [
    els.fullNameInput,
    els.emailInput,
    els.roleInput,
    els.departmentInput,
    els.companyNameInput,
    els.companyDisplayNameInput,
    els.companyLogoUrlInput,
    els.companyIconUrlInput,
    els.brandPrimaryColorInput,
    els.brandAccentColorInput
  ];

  inputs.forEach((input) => {
    if (!input) return;

    input.addEventListener("input", function () {
      const currentUser = getProfileSessionUser();
      const currentCompany = getProfileSessionCompany();

      if (!currentUser || !currentCompany) return;

      const previewUser = {
        ...currentUser,
        name: els.fullNameInput?.value.trim() || currentUser.name,
        email: els.emailInput?.value.trim() || currentUser.email,
        role: els.roleInput?.value.trim() || currentUser.role,
        department: els.departmentInput?.value.trim() || currentUser.department
      };

      const previewCompany = {
        ...currentCompany,
        companyName: els.companyNameInput?.value.trim() || currentCompany.companyName,
        companyDisplayName:
          els.companyDisplayNameInput?.value.trim() || currentCompany.companyDisplayName,
        companyLogoUrl:
          els.companyLogoUrlInput?.value.trim() || currentCompany.companyLogoUrl,
        companyIconUrl:
          els.companyIconUrlInput?.value.trim() || currentCompany.companyIconUrl,
        brandPrimaryColor:
          els.brandPrimaryColorInput?.value.trim() || currentCompany.brandPrimaryColor,
        brandAccentColor:
          els.brandAccentColorInput?.value.trim() || currentCompany.brandAccentColor
      };

      const initials = getProfileInitials(previewUser.name || "");

      if (els.profileAvatarLarge) els.profileAvatarLarge.textContent = initials;
      if (els.profileName) els.profileName.textContent = previewUser.name || "";
      if (els.profileEmail) els.profileEmail.textContent = previewUser.email || "";
      if (els.profileRole) els.profileRole.textContent = previewUser.role || "";
      if (els.profileCompanyName) {
        els.profileCompanyName.textContent =
          previewCompany.companyDisplayName || previewCompany.companyName || "";
      }

      document.querySelectorAll("[data-user='name']").forEach((el) => {
        el.textContent = previewUser.name || "";
      });

      document.querySelectorAll("[data-user='role']").forEach((el) => {
        el.textContent = previewUser.role || "";
      });

      document.querySelectorAll("[data-user='avatar']").forEach((el) => {
        el.textContent = initials;
      });

      if (els.sidebarBrandLogoFull) {
        els.sidebarBrandLogoFull.src = previewCompany.companyLogoUrl || "";
      }

      if (els.sidebarBrandLogoIcon) {
        els.sidebarBrandLogoIcon.src =
          previewCompany.companyIconUrl || previewCompany.companyLogoUrl || "";
      }

      if (els.brandPrimaryPreview) {
        els.brandPrimaryPreview.style.background =
          previewCompany.brandPrimaryColor || "#2563EB";
      }

      if (els.brandAccentPreview) {
        els.brandAccentPreview.style.background =
          previewCompany.brandAccentColor || "#60A5FA";
      }

      document.documentElement.style.setProperty(
        "--brand-primary-custom",
        previewCompany.brandPrimaryColor || "#2563EB"
      );

      document.documentElement.style.setProperty(
        "--brand-accent-custom",
        previewCompany.brandAccentColor || "#60A5FA"
      );
    });
  });
}

function initializeProfilePage() {
  if (document.body.dataset.page !== "profile") {
    return;
  }

  fillProfilePage();
  bindProfileButtons();
  bindProfileLivePreview();
}

document.addEventListener("DOMContentLoaded", initializeProfilePage);