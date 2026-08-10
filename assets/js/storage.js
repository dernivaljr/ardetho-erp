const STORAGE_KEYS = {
  appData: "ardetho_app_data",
  currentUser: "ardetho_current_user",
  currentCompany: "ardetho_current_company_profile",
  activeModules: "ardetho_active_modules",
  settings: "ardetho_settings"
};

const storage = {
  save(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      return true;
    } catch (error) {
      console.error(`Error saving data to localStorage (${key}):`, error);
      return false;
    }
  },

  get(key, fallback = null) {
    try {
      const rawValue = localStorage.getItem(key);

      if (rawValue === null) {
        return fallback;
      }

      return JSON.parse(rawValue);
    } catch (error) {
      console.error(`Error reading data from localStorage (${key}):`, error);
      return fallback;
    }
  },

  remove(key) {
    try {
      localStorage.removeItem(key);
      return true;
    } catch (error) {
      console.error(`Error removing data from localStorage (${key}):`, error);
      return false;
    }
  },

  clearAll() {
    try {
      [
        STORAGE_KEYS.appData,
        STORAGE_KEYS.currentUser,
        STORAGE_KEYS.currentCompany,
        STORAGE_KEYS.activeModules
      ].forEach((key) => localStorage.removeItem(key));

      return true;
    } catch (error) {
      console.error("Error clearing Ardetho storage:", error);
      return false;
    }
  }
};

function cloneAppData() {
  return JSON.parse(JSON.stringify(appData));
}

function getStoredAppData() {
  return storage.get(STORAGE_KEYS.appData, cloneAppData());
}

function getDefaultActiveModules() {
  return appData.modules.map((module) => ({
    id: module.id,
    slug: module.slug,
    active: module.active
  }));
}

function reconcileActiveModules(storedModules) {
  const defaultModules = getDefaultActiveModules();

  if (!Array.isArray(storedModules)) {
    return defaultModules;
  }

  const storedBySlug = new Map(
    storedModules
      .filter((module) => module && typeof module.slug === "string")
      .map((module) => [module.slug, module])
  );

  return defaultModules.map((module) => {
    const storedModule = storedBySlug.get(module.slug);

    return {
      ...module,
      active:
        storedModule && typeof storedModule.active === "boolean"
          ? storedModule.active
          : module.active
    };
  });
}

function initializeAppData() {
  const existingData = storage.get(STORAGE_KEYS.appData);

  if (!existingData) {
    storage.save(STORAGE_KEYS.appData, cloneAppData());
  }

  const existingModules = storage.get(STORAGE_KEYS.activeModules);
  const activeModules = reconcileActiveModules(existingModules);

  if (JSON.stringify(activeModules) !== JSON.stringify(existingModules)) {
    storage.save(STORAGE_KEYS.activeModules, activeModules);
  }

  const existingCompany = storage.get(STORAGE_KEYS.currentCompany);

  if (!existingCompany) {
    const companies = appData.companies || [];
    const defaultCompany = companies.find(
      (company) => company.id === appData.currentUser.companyId
    );

    if (defaultCompany) {
      storage.save(STORAGE_KEYS.currentCompany, defaultCompany);
    }
  }
}

function resetAppData() {
  storage.save(STORAGE_KEYS.appData, cloneAppData());

  const defaultUser = { ...appData.currentUser };
  delete defaultUser.password;
  storage.save(STORAGE_KEYS.currentUser, defaultUser);

  const activeModules = getDefaultActiveModules();
  storage.save(STORAGE_KEYS.activeModules, activeModules);

  const companies = appData.companies || [];
  const defaultCompany = companies.find(
    (company) => company.id === appData.currentUser.companyId
  );

  if (defaultCompany) {
    storage.save(STORAGE_KEYS.currentCompany, defaultCompany);
  }
}

function getCurrentUser() {
  return storage.get(STORAGE_KEYS.currentUser, null);
}

function setCurrentUser(user) {
  return storage.save(STORAGE_KEYS.currentUser, user);
}

function getActiveModules() {
  return reconcileActiveModules(storage.get(STORAGE_KEYS.activeModules));
}

function setActiveModules(modules) {
  return storage.save(STORAGE_KEYS.activeModules, modules);
}

function updateAppData(section, newData) {
  const currentData = getStoredAppData();

  if (!Object.prototype.hasOwnProperty.call(currentData, section)) {
    console.warn(`Section "${section}" does not exist in app data.`);
    return false;
  }

  currentData[section] = newData;
  return storage.save(STORAGE_KEYS.appData, currentData);
}

function getAppSection(section, fallback = []) {
  const currentData = getStoredAppData();

  if (!Object.prototype.hasOwnProperty.call(currentData, section)) {
    return fallback;
  }

  return currentData[section];
}

function getClientsData() {
  return getAppSection("clients", appData.clients);
}

function getProductsData() {
  return getAppSection("products", appData.products);
}

function getSalesData() {
  return getAppSection("sales", appData.sales);
}

function getFinancialData() {
  return getAppSection("financial", appData.financial);
}

function getHrData() {
  return getAppSection("hr", []);
}

function getCurrentCompany() {
  return storage.get(STORAGE_KEYS.currentCompany, null);
}

function setCurrentCompany(company) {
  return storage.save(STORAGE_KEYS.currentCompany, company);
}

initializeAppData();