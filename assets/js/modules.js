function getModulesData() {
  const storedModules = getActiveModules();
  const fullModules = getAppSection("modules", appData.modules);

  return fullModules.map((module) => {
    const storedModule = storedModules.find((item) => item.id === module.id);

    return {
      ...module,
      active: storedModule ? storedModule.active : module.active
    };
  });
}

function renderModulesUser() {
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

function renderModulesSummary(modules) {
  const activeModulesEl = document.getElementById("modules-active-count");
  const inactiveModulesEl = document.getElementById("modules-inactive-count");
  const savedCustomizationsEl = document.getElementById("modules-customizations-count");

  const activeCount = modules.filter((module) => module.active).length;
  const inactiveCount = modules.filter((module) => !module.active).length;

  if (activeModulesEl) {
    activeModulesEl.textContent = activeCount;
  }

  if (inactiveModulesEl) {
    inactiveModulesEl.textContent = inactiveCount;
  }

  if (savedCustomizationsEl) {
    savedCustomizationsEl.textContent = activeCount + inactiveCount;
  }
}

function createModuleItem(module) {
  const wrapper = document.createElement("div");
  wrapper.className = "option-item";

  wrapper.innerHTML = `
    <div class="option-content">
      <span class="option-title">${module.name}</span>
      <span class="option-description">${module.description}</span>
    </div>
    <div
      class="switch ${module.active ? "active" : ""}"
      data-module-id="${module.id}"
      role="button"
      tabindex="0"
      aria-label="Alternar módulo ${module.name}"
      aria-pressed="${module.active ? "true" : "false"}"
    ></div>
  `;

  return wrapper;
}

function renderModulesLists() {
  const mainModulesContainer = document.getElementById("modules-main-list");
  const complementaryModulesContainer = document.getElementById("modules-complementary-list");

  if (!mainModulesContainer || !complementaryModulesContainer) {
    return;
  }

  const modules = getModulesData();

  mainModulesContainer.innerHTML = "";
  complementaryModulesContainer.innerHTML = "";

  modules.forEach((module) => {
    const moduleItem = createModuleItem(module);

    if (module.category === "principal") {
      mainModulesContainer.appendChild(moduleItem);
    } else {
      complementaryModulesContainer.appendChild(moduleItem);
    }
  });

  renderModulesSummary(modules);
}

function toggleModuleState(moduleId) {
  const modules = getModulesData();

  const updatedModules = modules.map((module) => {
    if (module.id === moduleId) {
      return {
        ...module,
        active: !module.active
      };
    }

    return module;
  });

  const activeModulesPayload = updatedModules.map((module) => ({
    id: module.id,
    slug: module.slug,
    active: module.active
  }));

  setActiveModules(activeModulesPayload);
  updateAppData("modules", updatedModules);

  renderModulesLists();
}

function bindModuleToggles() {
  document.addEventListener("click", (event) => {
    const toggle = event.target.closest("[data-module-id]");

    if (!toggle) {
      return;
    }

    const moduleId = toggle.dataset.moduleId;
    toggleModuleState(moduleId);
  });

  document.addEventListener("keydown", (event) => {
    const toggle = event.target.closest("[data-module-id]");

    if (!toggle) {
      return;
    }

    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      const moduleId = toggle.dataset.moduleId;
      toggleModuleState(moduleId);
    }
  });
}

function initializeModulesPage() {
  const modulesPage = document.body.dataset.page === "modules";

  if (!modulesPage) {
    return;
  }

  renderModulesUser();
  renderModulesLists();
  bindModuleToggles();
}

document.addEventListener("DOMContentLoaded", initializeModulesPage);