function renderClientFormUser() {
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

function getClientsData() {
  return getAppSection("clients", appData.clients);
}

function saveClientsData(clients) {
  return updateAppData("clients", clients);
}

function generateClientId() {
  return `CLI-${Date.now()}`;
}

function getClientIdFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

function findClientById(clientId) {
  if (!clientId) {
    return null;
  }

  const clients = getClientsData();
  return clients.find((client) => client.id === clientId) || null;
}

function isEditMode() {
  return Boolean(getClientIdFromUrl());
}

function updateClientFormPageTitle() {
  const title = document.querySelector(".page-title");
  const description = document.querySelector(".page-description");
  const documentTitle = document.querySelector("title");

  if (!isEditMode()) {
    return;
  }

  if (title) {
    title.textContent = "Editar cliente";
  }

  if (description) {
    description.textContent = "Atualize os dados cadastrais do cliente selecionado.";
  }

  if (documentTitle) {
    documentTitle.textContent = "Editar Cliente | Ardetho ERP";
  }
}

function updateClientPersonTypeFields() {
  const personType = document.getElementById("client-person-type")?.value || "PF";
  const pfSection = document.getElementById("client-section-pf");
  const pjSection = document.getElementById("client-section-pj");

  if (personType === "PF") {
    pfSection?.classList.remove("hidden");
    pjSection?.classList.add("hidden");
  } else {
    pfSection?.classList.add("hidden");
    pjSection?.classList.remove("hidden");
  }
}

function fillClientForm(client) {
  if (!client) {
    return;
  }

  document.getElementById("client-person-type").value = client.personType || "PF";
  document.getElementById("client-status").value = client.status || "Ativo";

  document.getElementById("client-full-name").value = client.fullName || "";
  document.getElementById("client-cpf").value = client.cpf || "";
  document.getElementById("client-rg").value = client.rg || "";
  document.getElementById("client-birth-date").value = client.birthDate || "";

  document.getElementById("client-company-name").value = client.companyName || "";
  document.getElementById("client-trade-name").value = client.tradeName || "";
  document.getElementById("client-cnpj").value = client.cnpj || "";
  document.getElementById("client-state-registration").value = client.stateRegistration || "";

  document.getElementById("client-contact-name").value = client.contactName || "";
  document.getElementById("client-main-email").value = client.mainEmail || "";
  document.getElementById("client-invoice-email").value = client.invoiceEmail || "";
  document.getElementById("client-phone").value = client.phone || "";
  document.getElementById("client-whatsapp").value = client.whatsapp || "";

  document.getElementById("client-zip-code").value = client.zipCode || "";
  document.getElementById("client-street").value = client.street || "";
  document.getElementById("client-number").value = client.number || "";
  document.getElementById("client-complement").value = client.complement || "";
  document.getElementById("client-district").value = client.district || "";
  document.getElementById("client-city").value = client.city || "";
  document.getElementById("client-state").value = client.state || "";

  document.getElementById("client-notes").value = client.notes || "";

  updateClientPersonTypeFields();
}

function onlyDigits(value) {
  return value.replace(/\D/g, "");
}

function formatCpf(value) {
  const digits = onlyDigits(value).slice(0, 11);

  return digits
    .replace(/^(\d{3})(\d)/, "$1.$2")
    .replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3")
    .replace(/\.(\d{3})(\d)/, ".$1-$2");
}

function formatCnpj(value) {
  const digits = onlyDigits(value).slice(0, 14);

  return digits
    .replace(/^(\d{2})(\d)/, "$1.$2")
    .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
    .replace(/\.(\d{3})(\d)/, ".$1/$2")
    .replace(/(\d{4})(\d)/, "$1-$2");
}

function formatPhone(value) {
  const digits = onlyDigits(value).slice(0, 11);

  if (digits.length <= 10) {
    return digits
      .replace(/^(\d{2})(\d)/, "($1) $2")
      .replace(/(\d{4})(\d)/, "$1-$2");
  }

  return digits
    .replace(/^(\d{2})(\d)/, "($1) $2")
    .replace(/(\d{5})(\d)/, "$1-$2");
}

function formatZipCode(value) {
  const digits = onlyDigits(value).slice(0, 8);
  return digits.replace(/^(\d{5})(\d)/, "$1-$2");
}

function applyInputMasks() {
  const cpfField = document.getElementById("client-cpf");
  const cnpjField = document.getElementById("client-cnpj");
  const phoneField = document.getElementById("client-phone");
  const whatsappField = document.getElementById("client-whatsapp");
  const zipCodeField = document.getElementById("client-zip-code");
  const stateField = document.getElementById("client-state");

  if (cpfField) {
    cpfField.addEventListener("input", () => {
      cpfField.value = formatCpf(cpfField.value);
    });
  }

  if (cnpjField) {
    cnpjField.addEventListener("input", () => {
      cnpjField.value = formatCnpj(cnpjField.value);
    });
  }

  if (phoneField) {
    phoneField.addEventListener("input", () => {
      phoneField.value = formatPhone(phoneField.value);
    });
  }

  if (whatsappField) {
    whatsappField.addEventListener("input", () => {
      whatsappField.value = formatPhone(whatsappField.value);
    });
  }

  if (zipCodeField) {
    zipCodeField.addEventListener("input", () => {
      zipCodeField.value = formatZipCode(zipCodeField.value);
    });
  }

  if (stateField) {
    stateField.addEventListener("input", () => {
      stateField.value = stateField.value.toUpperCase().slice(0, 2);
    });
  }
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidCpf(cpf) {
  const digits = onlyDigits(cpf);

  if (digits.length !== 11 || /^(\d)\1+$/.test(digits)) {
    return false;
  }

  let sum = 0;
  for (let i = 0; i < 9; i++) {
    sum += Number(digits[i]) * (10 - i);
  }

  let firstDigit = (sum * 10) % 11;
  if (firstDigit === 10) firstDigit = 0;

  if (firstDigit !== Number(digits[9])) {
    return false;
  }

  sum = 0;
  for (let i = 0; i < 10; i++) {
    sum += Number(digits[i]) * (11 - i);
  }

  let secondDigit = (sum * 10) % 11;
  if (secondDigit === 10) secondDigit = 0;

  return secondDigit === Number(digits[10]);
}

function isValidCnpj(cnpj) {
  const digits = onlyDigits(cnpj);

  if (digits.length !== 14 || /^(\d)\1+$/.test(digits)) {
    return false;
  }

  const calcDigit = (base, factors) => {
    const total = base.split("").reduce((sum, digit, index) => {
      return sum + Number(digit) * factors[index];
    }, 0);

    const remainder = total % 11;
    return remainder < 2 ? 0 : 11 - remainder;
  };

  const base12 = digits.slice(0, 12);
  const digit1 = calcDigit(base12, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
  const base13 = digits.slice(0, 12) + digit1;
  const digit2 = calcDigit(base13, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

  return digits === base12 + String(digit1) + String(digit2);
}

function isValidPhone(phone) {
  const digits = onlyDigits(phone);
  return digits.length === 10 || digits.length === 11;
}

function isValidZipCode(zipCode) {
  return onlyDigits(zipCode).length === 8;
}

function isValidState(state) {
  return /^[A-Z]{2}$/.test(state);
}

function getClientFormData() {
  return {
    personType: document.getElementById("client-person-type")?.value || "PF",
    status: document.getElementById("client-status")?.value || "Ativo",

    fullName: document.getElementById("client-full-name")?.value.trim() || "",
    cpf: document.getElementById("client-cpf")?.value.trim() || "",
    rg: document.getElementById("client-rg")?.value.trim() || "",
    birthDate: document.getElementById("client-birth-date")?.value || "",

    companyName: document.getElementById("client-company-name")?.value.trim() || "",
    tradeName: document.getElementById("client-trade-name")?.value.trim() || "",
    cnpj: document.getElementById("client-cnpj")?.value.trim() || "",
    stateRegistration: document.getElementById("client-state-registration")?.value.trim() || "",

    contactName: document.getElementById("client-contact-name")?.value.trim() || "",
    mainEmail: document.getElementById("client-main-email")?.value.trim() || "",
    invoiceEmail: document.getElementById("client-invoice-email")?.value.trim() || "",
    phone: document.getElementById("client-phone")?.value.trim() || "",
    whatsapp: document.getElementById("client-whatsapp")?.value.trim() || "",

    zipCode: document.getElementById("client-zip-code")?.value.trim() || "",
    street: document.getElementById("client-street")?.value.trim() || "",
    number: document.getElementById("client-number")?.value.trim() || "",
    complement: document.getElementById("client-complement")?.value.trim() || "",
    district: document.getElementById("client-district")?.value.trim() || "",
    city: document.getElementById("client-city")?.value.trim() || "",
    state: document.getElementById("client-state")?.value.trim().toUpperCase() || "",

    notes: document.getElementById("client-notes")?.value.trim() || ""
  };
}

function validateClientForm(data) {
  const commonRequired = [
    "mainEmail",
    "phone",
    "zipCode",
    "street",
    "number",
    "district",
    "city",
    "state"
  ];

  for (const field of commonRequired) {
    if (!data[field]) {
      return "Preencha todos os campos obrigatórios de contato e endereço.";
    }
  }

  if (!isValidEmail(data.mainEmail)) {
    return "Informe um e-mail principal válido.";
  }

  if (data.invoiceEmail && !isValidEmail(data.invoiceEmail)) {
    return "Informe um e-mail de NF válido.";
  }

  if (!isValidPhone(data.phone)) {
    return "Informe um telefone válido com DDD.";
  }

  if (data.whatsapp && !isValidPhone(data.whatsapp)) {
    return "Informe um WhatsApp válido com DDD.";
  }

  if (!isValidZipCode(data.zipCode)) {
    return "Informe um CEP válido.";
  }

  if (!isValidState(data.state)) {
    return "Informe uma UF válida com 2 letras.";
  }

  if (data.personType === "PF") {
    const requiredPF = ["fullName", "cpf", "rg", "birthDate"];

    for (const field of requiredPF) {
      if (!data[field]) {
        return "Preencha todos os campos obrigatórios da pessoa física.";
      }
    }

    if (!isValidCpf(data.cpf)) {
      return "Informe um CPF válido.";
    }
  }

  if (data.personType === "PJ") {
    const requiredPJ = ["companyName", "tradeName", "cnpj", "stateRegistration"];

    for (const field of requiredPJ) {
      if (!data[field]) {
        return "Preencha todos os campos obrigatórios da pessoa jurídica.";
      }
    }

    if (!isValidCnpj(data.cnpj)) {
      return "Informe um CNPJ válido.";
    }
  }

  return "";
}

function showClientFormError(message) {
  const error = document.getElementById("client-form-error");

  if (!error) {
    return;
  }

  error.textContent = message;
  error.classList.remove("hidden");
}

function hideClientFormError() {
  const error = document.getElementById("client-form-error");

  if (!error) {
    return;
  }

  error.textContent = "";
  error.classList.add("hidden");
}

function createClient(formData) {
  const clients = getClientsData();

  const newClient = {
    id: generateClientId(),
    ...formData,
    createdAt: new Date().toISOString().split("T")[0]
  };

  const updatedClients = [newClient, ...clients];
  saveClientsData(updatedClients);
}

function updateClient(clientId, formData) {
  const clients = getClientsData();

  const updatedClients = clients.map((client) => {
    if (client.id !== clientId) {
      return client;
    }

    return {
      ...client,
      ...formData
    };
  });

  saveClientsData(updatedClients);
}

function handleClientFormSubmit(event) {
  event.preventDefault();

  const formData = getClientFormData();
  const validationError = validateClientForm(formData);
  const clientId = getClientIdFromUrl();

  hideClientFormError();

  if (validationError) {
    showClientFormError(validationError);
    return;
  }

  if (clientId) {
    updateClient(clientId, formData);
  } else {
    createClient(formData);
  }

  window.location.href = "clients.html";
}

function bindClientFormActions() {
  const form = document.getElementById("client-form-page");
  const personTypeField = document.getElementById("client-person-type");
  const zipCodeField = document.getElementById("client-zip-code");

  if (form) {
    form.addEventListener("submit", handleClientFormSubmit);
  }

  if (personTypeField) {
    personTypeField.addEventListener("change", updateClientPersonTypeFields);
  }

  if (zipCodeField) {
    zipCodeField.addEventListener("blur", handleZipCodeLookup);
  }
}

function loadClientForEdit() {
  const clientId = getClientIdFromUrl();

  if (!clientId) {
    updateClientPersonTypeFields();
    return;
  }

  const client = findClientById(clientId);

  if (!client) {
    window.location.href = "clients.html";
    return;
  }

  fillClientForm(client);
}

function initializeClientFormPage() {
  const clientFormPage = document.body.dataset.page === "client-form";

  if (!clientFormPage) {
    return;
  }

  renderClientFormUser();
  updateClientFormPageTitle();
  loadClientForEdit();
  applyInputMasks();
  bindClientFormActions();
}

document.addEventListener("DOMContentLoaded", initializeClientFormPage);
async function fetchAddressByZipCode(zipCode) {
  const digits = onlyDigits(zipCode);

  if (digits.length !== 8) {
    return null;
  }

  try {
    const response = await fetch(`https://viacep.com.br/ws/${digits}/json/`);

    if (!response.ok) {
      return null;
    }

    const data = await response.json();

    if (data.erro) {
      return null;
    }

    return {
      street: data.logradouro || "",
      district: data.bairro || "",
      city: data.localidade || "",
      state: data.uf || ""
    };
  } catch (error) {
    console.error("Error fetching zip code:", error);
    return null;
  }
}
async function handleZipCodeLookup() {
  const zipCodeField = document.getElementById("client-zip-code");
  const streetField = document.getElementById("client-street");
  const districtField = document.getElementById("client-district");
  const cityField = document.getElementById("client-city");
  const stateField = document.getElementById("client-state");

  if (!zipCodeField) {
    return;
  }

  const zipCode = zipCodeField.value;

  if (!isValidZipCode(zipCode)) {
    return;
  }

  const address = await fetchAddressByZipCode(zipCode);

  if (!address) {
    return;
  }

  if (streetField && !streetField.value.trim()) {
    streetField.value = address.street;
  }

  if (districtField && !districtField.value.trim()) {
    districtField.value = address.district;
  }

  if (cityField && !cityField.value.trim()) {
    cityField.value = address.city;
  }

  if (stateField && !stateField.value.trim()) {
    stateField.value = address.state;
  }
}