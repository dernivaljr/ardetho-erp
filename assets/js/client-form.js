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
    state: document.getElementById("client-state")?.value.trim() || "",

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

  if (data.personType === "PF") {
    const requiredPF = ["fullName", "cpf", "rg", "birthDate"];

    for (const field of requiredPF) {
      if (!data[field]) {
        return "Preencha todos os campos obrigatórios da pessoa física.";
      }
    }
  }

  if (data.personType === "PJ") {
    const requiredPJ = ["companyName", "tradeName", "cnpj", "stateRegistration"];

    for (const field of requiredPJ) {
      if (!data[field]) {
        return "Preencha todos os campos obrigatórios da pessoa jurídica.";
      }
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

  if (form) {
    form.addEventListener("submit", handleClientFormSubmit);
  }

  if (personTypeField) {
    personTypeField.addEventListener("change", updateClientPersonTypeFields);
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
  bindClientFormActions();
}

document.addEventListener("DOMContentLoaded", initializeClientFormPage);