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

  if (!errorMessage) return;

  errorMessage.textContent = message;
  errorMessage.classList.remove("hidden");
}

function hideLoginError() {
  const { errorMessage } = getLoginFormElements();

  if (!errorMessage) return;

  errorMessage.textContent = "";
  errorMessage.classList.add("hidden");
}

function validateCredentials(email, password) {
  const systemUser = appData.currentUser;

  return (
    email === systemUser.email &&
    password === systemUser.password
  );
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
  storage.remove(STORAGE_KEYS.currentUser);
  window.location.href = "login.html";
}

function isUserAuthenticated() {
  const currentUser = storage.get(STORAGE_KEYS.currentUser);
  return Boolean(currentUser && currentUser.email);
}

function protectInternalPage() {
  const isLoginPage = window.location.pathname.includes("login.html");

  if (isLoginPage) {
    return;
  }

  if (!isUserAuthenticated()) {
    window.location.href = "login.html";
  }
}

function redirectAuthenticatedUserFromLogin() {
  const isLoginPage = window.location.pathname.includes("login.html");

  if (!isLoginPage) {
    return;
  }

  if (isUserAuthenticated()) {
    window.location.href = "dashboard.html";
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

  window.location.href = "dashboard.html";
}

function bindLoginForm() {
  const { form } = getLoginFormElements();

  if (!form) {
    return;
  }

  form.addEventListener("submit", handleLoginSubmit);
}

function bindLogoutButtons() {
  const logoutButtons = document.querySelectorAll("[data-action='logout']");

  if (!logoutButtons.length) {
    return;
  }

  logoutButtons.forEach((button) => {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      logoutUser();
    });
  });
}

function initializeAuth() {
  redirectAuthenticatedUserFromLogin();
  protectInternalPage();
  bindLoginForm();
  bindLogoutButtons();
}

document.addEventListener("DOMContentLoaded", initializeAuth);