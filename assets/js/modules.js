function renderModulesUser() {
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

function getModulesData() {
  return getAppSection("modules", appData.modules);
}

function getModuleStates() {
  const activeModules = getActiveModules();

  const activeMap = new Map(
    activeModules.map((module) => [module.slug, Boolean(module.active)])
  );

  return getModulesData().map((module) => ({
    ...module,
    active: activeMap.has(module.slug) ? activeMap.get(module.slug) : Boolean(module.active)
  }));
}

function saveModulesState(modules) {
  const activeModules = modules.map((module) => ({
    id: module.id,
    slug: module.slug,
    active: module.active
  }));

  setActiveModules(activeModules);
}

function getModuleGroup(module) {
  const slug = (module.slug || "").toLowerCase();
  const mainModules = ["clients", "products", "sales", "financial"];

  return mainModules.includes(slug) ? "main" : "complementary";
}

function renderModuleSwitch(module) {
  return `
    <button
      type="button"
      class="switch ${module.active ? "active" : ""}"
      data-action="toggle-module"
      data-module-slug="${module.slug}"
      aria-label="Alternar módulo ${module.name}"
      aria-pressed="${module.active ? "true" : "false"}"
    ></button>
  `;
}

function renderModulesLists() {
  const modules = getModuleStates();

  const mainList = document.getElementById("modules-main-list");
  const complementaryList = document.getElementById("modules-complementary-list");

  if (!mainList || !complementaryList) {
    return;
  }

  mainList.innerHTML = "";
  complementaryList.innerHTML = "";

  modules.forEach((module) => {
    const item = document.createElement("div");
    item.className = "option-item";

    item.innerHTML = `
      <div class="option-content">
        <span class="option-title">${module.name || "Módulo"}</span>
        <span class="option-description">${module.description || "Sem descrição disponível."}</span>
      </div>
      ${renderModuleSwitch(module)}
    `;

    if (getModuleGroup(module) === "main") {
      mainList.appendChild(item);
    } else {
      complementaryList.appendChild(item);
    }
  });
}

function renderModulesSummary() {
  const modules = getModuleStates();

  const activeCountEl = document.getElementById("modules-active-count");
  const inactiveCountEl = document.getElementById("modules-inactive-count");
  const customizationsCountEl = document.getElementById("modules-customizations-count");

  const activeCount = modules.filter((module) => module.active).length;
  const inactiveCount = modules.filter((module) => !module.active).length;

  const defaultModules = getModulesData();
  const customizationsCount = modules.filter((module) => {
    const defaultModule = defaultModules.find((item) => item.slug === module.slug);
    return defaultModule ? Boolean(defaultModule.active) !== Boolean(module.active) : false;
  }).length;

  if (activeCountEl) {
    activeCountEl.textContent = activeCount;
  }

  if (inactiveCountEl) {
    inactiveCountEl.textContent = inactiveCount;
  }

  if (customizationsCountEl) {
    customizationsCountEl.textContent = customizationsCount;
  }
}

function refreshModulesPage() {
  renderModulesLists();
  renderModulesSummary();
  renderModulesConfigurationSummary();
}

function toggleModuleBySlug(slug) {
  const modules = getModuleStates().map((module) => {
    if (module.slug !== slug) {
      return module;
    }

    return {
      ...module,
      active: !module.active
    };
  });

  saveModulesState(modules);
  refreshModulesPage();
}

function resetModulesToDefault() {
  const defaultModules = getModulesData();

  const normalizedModules = defaultModules.map((module) => ({
    ...module,
    active: Boolean(module.active)
  }));

  saveModulesState(normalizedModules);
  refreshModulesPage();
}

function saveModulesConfiguration() {
  alert("Configuração de módulos salva com sucesso.");
}

function bindModulesActions() {
  document.addEventListener("click", (event) => {
    const toggleButton = event.target.closest("[data-action='toggle-module']");
    const resetButton = event.target.closest("[data-action='reset-modules']");
    const saveButton = event.target.closest("[data-action='save-modules']");

    if (toggleButton) {
      event.preventDefault();
      const slug = toggleButton.dataset.moduleSlug;

      if (!slug) {
        return;
      }

      toggleModuleBySlug(slug);
      return;
    }

    if (resetButton) {
      event.preventDefault();
      resetModulesToDefault();
      return;
    }

    if (saveButton) {
      event.preventDefault();
      saveModulesConfiguration();
    }
  });
}

function initializeModulesPage() {
  const modulesPage = document.body.dataset.page === "modules";

  if (!modulesPage) {
    return;
  }

  renderModulesUser();
  refreshModulesPage();
  bindModulesActions();
}
function renderModulesConfigurationSummary() {
  const modules = getModuleStates();
  const defaultModules = getModulesData();

  const profileSummaryEl = document.getElementById("modules-profile-summary");
  const profileBadgeEl = document.getElementById("modules-profile-badge");
  const navigationSummaryEl = document.getElementById("modules-navigation-summary");
  const customizationSummaryEl = document.getElementById("modules-customization-summary");
  const customizationBadgeEl = document.getElementById("modules-customization-badge");

  const activeSlugs = modules
    .filter((module) => module.active)
    .map((module) => module.slug);

  const customizationsCount = modules.filter((module) => {
    const defaultModule = defaultModules.find((item) => item.slug === module.slug);
    return defaultModule ? Boolean(defaultModule.active) !== Boolean(module.active) : false;
  }).length;

  let profileText = "Perfil operacional personalizado.";
  let profileBadge = "Atual";

  const hasCommercialCore =
    activeSlugs.includes("clients") &&
    activeSlugs.includes("products") &&
    activeSlugs.includes("sales") &&
    activeSlugs.includes("financial");

  const complementaryCount = modules.filter((module) => {
    return !["clients", "products", "sales", "financial"].includes(module.slug) && module.active;
  }).length;

  if (hasCommercialCore && complementaryCount === 0) {
    profileText = "Perfil de operação comercial com foco em clientes, vendas e financeiro.";
    profileBadge = "Comercial";
  } else if (hasCommercialCore && complementaryCount > 0) {
    profileText = "Ambiente expandido com operação comercial e módulos complementares ativos.";
    profileBadge = "Expandido";
  } else if (activeSlugs.length <= 3) {
    profileText = "Estrutura enxuta com poucos módulos ativos no ambiente.";
    profileBadge = "Enxuto";
  }

  if (profileSummaryEl) {
    profileSummaryEl.textContent = profileText;
  }

  if (profileBadgeEl) {
    profileBadgeEl.textContent = profileBadge;
  }

  if (navigationSummaryEl) {
    navigationSummaryEl.textContent = "Módulos inativos podem ser ocultados da navegação e bloqueados no acesso direto.";
  }

  if (customizationSummaryEl) {
    customizationSummaryEl.textContent =
      customizationsCount > 0
        ? `${customizationsCount} alteração(ões) em relação à configuração padrão foram detectadas.`
        : "A configuração atual segue o padrão inicial do sistema.";
  }

  if (customizationBadgeEl) {
    customizationBadgeEl.textContent = customizationsCount > 0 ? "Customizado" : "Padrão";
    customizationBadgeEl.className = customizationsCount > 0 ? "badge-warning" : "badge-neutral";
  }
}

document.addEventListener("DOMContentLoaded", initializeModulesPage);