const STORAGE_KEYS = {
  appData: "ardetho_app_data",
  currentUser: "ardetho_current_user",
  activeModules: "ardetho_active_modules"
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
      Object.values(STORAGE_KEYS).forEach((key) => localStorage.removeItem(key));
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

function initializeAppData() {
  const existingData = storage.get(STORAGE_KEYS.appData);

  if (!existingData) {
    storage.save(STORAGE_KEYS.appData, cloneAppData());
  }

  const existingUser = storage.get(STORAGE_KEYS.currentUser);

  if (!existingUser) {
    storage.save(STORAGE_KEYS.currentUser, appData.currentUser);
  }

  const existingModules = storage.get(STORAGE_KEYS.activeModules);

  if (!existingModules) {
    const activeModules = appData.modules.map((module) => ({
      id: module.id,
      slug: module.slug,
      active: module.active
    }));

    storage.save(STORAGE_KEYS.activeModules, activeModules);
  }
}

function resetAppData() {
  storage.save(STORAGE_KEYS.appData, cloneAppData());
  storage.save(STORAGE_KEYS.currentUser, appData.currentUser);

  const activeModules = appData.modules.map((module) => ({
    id: module.id,
    slug: module.slug,
    active: module.active
  }));

  storage.save(STORAGE_KEYS.activeModules, activeModules);
}

function getCurrentUser() {
  return storage.get(STORAGE_KEYS.currentUser, appData.currentUser);
}

function setCurrentUser(user) {
  return storage.save(STORAGE_KEYS.currentUser, user);
}

function getActiveModules() {
  return storage.get(
    STORAGE_KEYS.activeModules,
    appData.modules.map((module) => ({
      id: module.id,
      slug: module.slug,
      active: module.active
    }))
  );
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

initializeAppData();