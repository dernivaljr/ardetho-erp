function renderFinancialFormUser() {
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

function getFinancialData() {
  return getAppSection("financial", appData.financial);
}

function saveFinancialData(entries) {
  return updateAppData("financial", entries);
}

function getClientsData() {
  return getAppSection("clients", appData.clients);
}

function getSalesData() {
  return getAppSection("sales", appData.sales);
}

function generateFinancialId() {
  return `FIN-${Date.now()}`;
}

function generateFinancialCode() {
  return `LAN-${Date.now().toString().slice(-6)}`;
}

function getFinancialIdFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

function findFinancialEntryById(entryId) {
  if (!entryId) {
    return null;
  }

  const entries = getFinancialData();
  return entries.find((entry) => entry.id === entryId) || null;
}

function isFinancialEditMode() {
  return Boolean(getFinancialIdFromUrl());
}

function updateFinancialFormPageTitle() {
  const title = document.querySelector(".page-title");
  const description = document.querySelector(".page-description");
  const documentTitle = document.querySelector("title");

  if (!isFinancialEditMode()) {
    return;
  }

  if (title) {
    title.textContent = "Editar lançamento";
  }

  if (description) {
    description.textContent = "Atualize os dados do lançamento financeiro selecionado.";
  }

  if (documentTitle) {
    documentTitle.textContent = "Editar Lançamento | Ardetho ERP";
  }
}

function getClientDisplayName(client) {
  if (client.personType === "PF") {
    return client.fullName || "Cliente";
  }

  return client.companyName || client.tradeName || "Cliente";
}

function populateFinancialClientOptions(selectedClientId = "") {
  const clientField = document.getElementById("financial-client-id");

  if (!clientField) {
    return;
  }

  const clients = getClientsData();

  clientField.innerHTML = `<option value="">Selecione um cliente</option>`;

  clients.forEach((client) => {
    const option = document.createElement("option");
    option.value = client.id;
    option.textContent = getClientDisplayName(client);
    clientField.appendChild(option);
  });

  clientField.value = selectedClientId || "";
}

function populateFinancialSaleOptions(selectedSaleId = "") {
  const saleField = document.getElementById("financial-sale-id");

  if (!saleField) {
    return;
  }

  const sales = getSalesData();

  saleField.innerHTML = `<option value="">Selecione um pedido</option>`;

  sales.forEach((sale) => {
    const option = document.createElement("option");
    option.value = sale.id;
    option.textContent = `${sale.code || "PED"} - ${sale.clientName || "Cliente"}`;
    saleField.appendChild(option);
  });

  saleField.value = selectedSaleId || "";
}

function onlyDigits(value) {
  return value.replace(/\D/g, "");
}

function formatCurrencyInput(value) {
  const digits = onlyDigits(value);

  if (!digits) {
    return "";
  }

  const number = Number(digits) / 100;

  return number.toLocaleString("pt-BR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function parseCurrencyValue(value) {
  if (!value) {
    return 0;
  }

  const normalized = value.replace(/\./g, "").replace(",", ".");
  return Number(normalized) || 0;
}

function formatCurrencyValue(value) {
  return Number(value || 0).toLocaleString("pt-BR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function getSelectedClientData(clientId) {
  const client = getClientsData().find((item) => item.id === clientId);

  if (!client) {
    return {
      clientName: ""
    };
  }

  return {
    clientName: getClientDisplayName(client)
  };
}

function getSelectedSaleData(saleId) {
  const sale = getSalesData().find((item) => item.id === saleId);

  if (!sale) {
    return {
      saleCode: "",
      clientId: "",
      clientName: "",
      totalValue: 0,
      paymentMethod: ""
    };
  }

  return {
    saleCode: sale.code || "",
    clientId: sale.clientId || "",
    clientName: sale.clientName || "",
    totalValue: sale.totalValue || 0,
    paymentMethod: sale.paymentMethod || ""
  };
}

function updateFinancialEntryTypeFields() {
  const entryType = document.getElementById("financial-entry-type")?.value || "Receita";
  const saleGroup = document.getElementById("financial-sale-group");
  const clientField = document.getElementById("financial-client-id");
  const saleField = document.getElementById("financial-sale-id");
  const categoryField = document.getElementById("financial-category");
  const descriptionField = document.getElementById("financial-description");
  const amountField = document.getElementById("financial-amount");
  const paymentMethodField = document.getElementById("financial-payment-method");
  const statusField = document.getElementById("financial-status");

  if (!clientField || !saleField || !categoryField || !descriptionField || !amountField || !paymentMethodField || !statusField) {
    return;
  }

  const currentStatus = statusField.value || "Pendente";

  if (entryType === "Receita") {
    saleGroup?.classList.remove("hidden");

    clientField.setAttribute("disabled", "disabled");
    categoryField.setAttribute("readonly", "readonly");
    descriptionField.setAttribute("readonly", "readonly");
    amountField.setAttribute("readonly", "readonly");

    statusField.innerHTML = `
      <option value="Pendente">Pendente</option>
      <option value="Recebido">Recebido</option>
      <option value="Cancelado">Cancelado</option>
    `;
  } else {
    saleGroup?.classList.add("hidden");

    clientField.removeAttribute("disabled");
    categoryField.removeAttribute("readonly");
    descriptionField.removeAttribute("readonly");
    amountField.removeAttribute("readonly");

    saleField.value = "";

    statusField.innerHTML = `
      <option value="Pendente">Pendente</option>
      <option value="Pago">Pago</option>
      <option value="Cancelado">Cancelado</option>
    `;
  }

  const optionExists = [...statusField.options].some(
    (option) => option.value === currentStatus
  );

  statusField.value = optionExists ? currentStatus : "Pendente";
}

function syncClientFromSelectedSale() {
  const saleField = document.getElementById("financial-sale-id");
  const clientField = document.getElementById("financial-client-id");

  if (!saleField || !clientField) {
    return;
  }

  const selectedSaleId = saleField.value;

  if (!selectedSaleId) {
    return;
  }

  const saleData = getSelectedSaleData(selectedSaleId);

  if (saleData.clientId) {
    clientField.value = saleData.clientId;
  }
}

function syncFinancialDataFromSelectedSale() {
  const saleField = document.getElementById("financial-sale-id");
  const clientField = document.getElementById("financial-client-id");
  const categoryField = document.getElementById("financial-category");
  const descriptionField = document.getElementById("financial-description");
  const amountField = document.getElementById("financial-amount");
  const paymentMethodField = document.getElementById("financial-payment-method");

  if (!saleField || !clientField || !categoryField || !descriptionField || !amountField || !paymentMethodField) {
    return;
  }

  const selectedSaleId = saleField.value;

  if (!selectedSaleId) {
    return;
  }

  const sale = getSalesData().find((item) => item.id === selectedSaleId);

  if (!sale) {
    return;
  }

  clientField.value = sale.clientId || "";
  categoryField.value = "Venda";
  descriptionField.value = `Recebimento referente ao pedido ${sale.code || ""}.`;
  amountField.value = formatCurrencyValue(sale.totalValue || 0);

  if (sale.paymentMethod) {
    paymentMethodField.value = sale.paymentMethod;
  }
}

function fillFinancialForm(entry) {
  if (!entry) {
    return;
  }

  document.getElementById("financial-code").value = entry.code || "";
  document.getElementById("financial-entry-type").value = entry.entryType || "Receita";
  updateFinancialEntryTypeFields();

  document.getElementById("financial-status").value = entry.status || "Pendente";

  document.getElementById("financial-entry-date").value = entry.entryDate || "";
  document.getElementById("financial-due-date").value = entry.dueDate || "";

  populateFinancialClientOptions(entry.clientId || "");
  populateFinancialSaleOptions(entry.saleId || "");

  document.getElementById("financial-category").value = entry.category || "";
  document.getElementById("financial-description").value = entry.description || "";
  document.getElementById("financial-amount").value = formatCurrencyValue(entry.amount || 0);
  document.getElementById("financial-payment-method").value = entry.paymentMethod || "";
  document.getElementById("financial-notes").value = entry.notes || "";
}

function getFinancialFormData() {
  const clientIdField = document.getElementById("financial-client-id");
  const saleId = document.getElementById("financial-sale-id")?.value || "";

  const clientId = clientIdField ? clientIdField.value : "";
  const selectedClientData = getSelectedClientData(clientId);
  const selectedSaleData = getSelectedSaleData(saleId);

  return {
    code: document.getElementById("financial-code")?.value.trim() || "",
    entryType: document.getElementById("financial-entry-type")?.value || "Receita",
    status: document.getElementById("financial-status")?.value || "Pendente",

    entryDate: document.getElementById("financial-entry-date")?.value || "",
    dueDate: document.getElementById("financial-due-date")?.value || "",

    clientId: saleId && selectedSaleData.clientId ? selectedSaleData.clientId : clientId,
    clientName: saleId && selectedSaleData.clientName ? selectedSaleData.clientName : selectedClientData.clientName,

    saleId,
    saleCode: selectedSaleData.saleCode,

    category: document.getElementById("financial-category")?.value.trim() || "",
    description: document.getElementById("financial-description")?.value.trim() || "",
    amount: parseCurrencyValue(document.getElementById("financial-amount")?.value || ""),
    paymentMethod: document.getElementById("financial-payment-method")?.value || "",

    notes: document.getElementById("financial-notes")?.value.trim() || ""
  };
}

function showFinancialFormError(message) {
  const error = document.getElementById("financial-form-error");

  if (!error) {
    return;
  }

  error.textContent = message;
  error.classList.remove("hidden");
}

function hideFinancialFormError() {
  const error = document.getElementById("financial-form-error");

  if (!error) {
    return;
  }

  error.textContent = "";
  error.classList.add("hidden");
}

function validateFinancialForm(data) {
  const baseRequired = [
    "code",
    "entryType",
    "status",
    "entryDate",
    "dueDate",
    "paymentMethod"
  ];

  for (const field of baseRequired) {
    if (!data[field]) {
      return "Preencha todos os campos obrigatórios do lançamento.";
    }
  }

  if (data.entryType === "Receita") {
    if (!data.saleId) {
      return "Selecione um pedido relacionado para a receita.";
    }

    if (!data.clientId) {
      return "A receita precisa estar vinculada a um cliente.";
    }
  }

  if (data.entryType === "Despesa") {
    if (!data.category || !data.description) {
      return "Preencha categoria e descrição da despesa.";
    }
  }

  if (!data.amount || data.amount <= 0) {
    return "Informe um valor válido maior que zero.";
  }

  return "";
}

function createFinancialEntry(formData) {
  const entries = getFinancialData();

  const newEntry = {
    id: generateFinancialId(),
    ...formData,
    createdAt: new Date().toISOString().split("T")[0]
  };

  const updatedEntries = [newEntry, ...entries];
  saveFinancialData(updatedEntries);
}

function updateFinancialEntry(entryId, formData) {
  const entries = getFinancialData();

  const updatedEntries = entries.map((entry) => {
    if (entry.id !== entryId) {
      return entry;
    }

    return {
      ...entry,
      ...formData
    };
  });

  saveFinancialData(updatedEntries);
}

function handleFinancialFormSubmit(event) {
  event.preventDefault();

  const formData = getFinancialFormData();
  const validationError = validateFinancialForm(formData);
  const entryId = getFinancialIdFromUrl();

  hideFinancialFormError();

  if (validationError) {
    showFinancialFormError(validationError);
    return;
  }

  if (entryId) {
    updateFinancialEntry(entryId, formData);
  } else {
    createFinancialEntry(formData);
  }

  window.location.href = "financial.html";
}

function bindFinancialFormActions() {
  const form = document.getElementById("financial-form-page");
  const amountField = document.getElementById("financial-amount");
  const saleField = document.getElementById("financial-sale-id");
  const entryTypeField = document.getElementById("financial-entry-type");

  if (form) {
    form.addEventListener("submit", handleFinancialFormSubmit);
  }

  if (amountField) {
    amountField.addEventListener("input", () => {
      if (amountField.hasAttribute("readonly")) {
        return;
      }

      amountField.value = formatCurrencyInput(amountField.value);
    });
  }

  if (saleField) {
    saleField.addEventListener("change", () => {
      syncClientFromSelectedSale();
      syncFinancialDataFromSelectedSale();
    });
  }

  if (entryTypeField) {
    entryTypeField.addEventListener("change", updateFinancialEntryTypeFields);
  }
}

function loadFinancialEntryForEdit() {
  const entryId = getFinancialIdFromUrl();

  if (!entryId) {
    const codeField = document.getElementById("financial-code");
    const entryDateField = document.getElementById("financial-entry-date");

    if (codeField && !codeField.value) {
      codeField.value = generateFinancialCode();
    }

    if (entryDateField && !entryDateField.value) {
      entryDateField.value = new Date().toISOString().split("T")[0];
    }

    return;
  }

  const entry = findFinancialEntryById(entryId);

  if (!entry) {
    window.location.href = "financial.html";
    return;
  }

  fillFinancialForm(entry);
}

function initializeFinancialFormPage() {
  const financialFormPage = document.body.dataset.page === "financial-form";

  if (!financialFormPage) {
    return;
  }

  renderFinancialFormUser();
  updateFinancialFormPageTitle();
  populateFinancialClientOptions();
  populateFinancialSaleOptions();
  loadFinancialEntryForEdit();
  updateFinancialEntryTypeFields();
  bindFinancialFormActions();
}

document.addEventListener("DOMContentLoaded", initializeFinancialFormPage);