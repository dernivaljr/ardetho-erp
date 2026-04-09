function renderSaleFormUser() {
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

function getSalesData() {
  return getAppSection("sales", appData.sales);
}

function saveSalesData(sales) {
  return updateAppData("sales", sales);
}

function getClientsData() {
  return getAppSection("clients", appData.clients);
}

function getProductsData() {
  return getAppSection("products", appData.products);
}

function generateSaleId() {
  return `SAL-${Date.now()}`;
}

function generateSaleCode() {
  return `PED-${Date.now().toString().slice(-6)}`;
}

function getSaleIdFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

function findSaleById(saleId) {
  if (!saleId) {
    return null;
  }

  const sales = getSalesData();
  return sales.find((sale) => sale.id === saleId) || null;
}

function isSaleEditMode() {
  return Boolean(getSaleIdFromUrl());
}

function updateSaleFormPageTitle() {
  const title = document.querySelector(".page-title");
  const description = document.querySelector(".page-description");
  const documentTitle = document.querySelector("title");

  if (!isSaleEditMode()) {
    return;
  }

  if (title) {
    title.textContent = "Editar venda";
  }

  if (description) {
    description.textContent = "Atualize os dados do pedido selecionado.";
  }

  if (documentTitle) {
    documentTitle.textContent = "Editar Venda | Ardetho ERP";
  }
}

function getClientDisplayName(client) {
  if (client.personType === "PF") {
    return client.fullName || "Cliente";
  }

  return client.companyName || client.tradeName || "Cliente";
}

function getProductDisplayName(product) {
  return `${product.code || "ITEM"} - ${product.name || "Item sem nome"}`;
}

function populateSaleClientOptions(selectedClientId = "") {
  const clientField = document.getElementById("sale-client-id");

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

function populateSaleProductOptions(selectedProductId = "") {
  const productField = document.getElementById("sale-product-id");

  if (!productField) {
    return;
  }

  const products = getProductsData();

  productField.innerHTML = `<option value="">Selecione um item</option>`;

  products.forEach((product) => {
    const option = document.createElement("option");
    option.value = product.id;
    option.textContent = getProductDisplayName(product);
    productField.appendChild(option);
  });

  productField.value = selectedProductId || "";
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

function updateSaleTotalValue() {
  const quantityField = document.getElementById("sale-quantity");
  const unitPriceField = document.getElementById("sale-unit-price");
  const totalValueField = document.getElementById("sale-total-value");

  if (!quantityField || !unitPriceField || !totalValueField) {
    return;
  }

  const quantity = Number(quantityField.value) || 0;
  const unitPrice = parseCurrencyValue(unitPriceField.value);
  const totalValue = quantity * unitPrice;

  totalValueField.value = totalValue > 0 ? formatCurrencyValue(totalValue) : "";
}

function fillSaleUnitPriceFromSelectedProduct() {
  const productField = document.getElementById("sale-product-id");
  const unitPriceField = document.getElementById("sale-unit-price");

  if (!productField || !unitPriceField) {
    return;
  }

  const selectedProductId = productField.value;

  if (!selectedProductId) {
    unitPriceField.value = "";
    updateSaleTotalValue();
    return;
  }

  const selectedProduct = getProductsData().find(
    (product) => product.id === selectedProductId
  );

  if (!selectedProduct) {
    unitPriceField.value = "";
    updateSaleTotalValue();
    return;
  }

  unitPriceField.value = formatCurrencyValue(selectedProduct.price || 0);
  updateSaleTotalValue();
}

function getSelectedClientName(clientId) {
  const client = getClientsData().find((item) => item.id === clientId);
  return client ? getClientDisplayName(client) : "";
}

function getSelectedProductData(productId) {
  const product = getProductsData().find((item) => item.id === productId);

  if (!product) {
    return {
      productName: "",
      itemType: ""
    };
  }

  return {
    productName: product.name || "",
    itemType: product.itemType || ""
  };
}

function fillSaleForm(sale) {
  if (!sale) {
    return;
  }

  document.getElementById("sale-code").value = sale.code || "";
  document.getElementById("sale-status").value = sale.status || "Em análise";
  document.getElementById("sale-date").value = sale.saleDate || "";

  populateSaleClientOptions(sale.clientId || "");
  populateSaleProductOptions(sale.productId || "");

  document.getElementById("sale-quantity").value = sale.quantity ?? 1;
  document.getElementById("sale-unit-price").value = formatCurrencyValue(sale.unitPrice || 0);
  document.getElementById("sale-total-value").value = formatCurrencyValue(sale.totalValue || 0);

  document.getElementById("sale-payment-method").value = sale.paymentMethod || "";
  document.getElementById("sale-payment-terms").value = sale.paymentTerms || "";
  document.getElementById("sale-notes").value = sale.notes || "";
}

function getSaleFormData() {
  const clientId = document.getElementById("sale-client-id")?.value || "";
  const productId = document.getElementById("sale-product-id")?.value || "";

  const selectedProductData = getSelectedProductData(productId);

  const quantity = Number(document.getElementById("sale-quantity")?.value || 0);
  const unitPrice = parseCurrencyValue(document.getElementById("sale-unit-price")?.value || "");
  const totalValue = quantity * unitPrice;

  return {
    code: document.getElementById("sale-code")?.value.trim() || "",
    status: document.getElementById("sale-status")?.value || "Em análise",
    saleDate: document.getElementById("sale-date")?.value || "",

    clientId,
    clientName: getSelectedClientName(clientId),

    productId,
    productName: selectedProductData.productName,
    itemType: selectedProductData.itemType,

    quantity,
    unitPrice,
    totalValue,

    paymentMethod: document.getElementById("sale-payment-method")?.value || "",
    paymentTerms: document.getElementById("sale-payment-terms")?.value.trim() || "",
    notes: document.getElementById("sale-notes")?.value.trim() || ""
  };
}

function showSaleFormError(message) {
  const error = document.getElementById("sale-form-error");

  if (!error) {
    return;
  }

  error.textContent = message;
  error.classList.remove("hidden");
}

function hideSaleFormError() {
  const error = document.getElementById("sale-form-error");

  if (!error) {
    return;
  }

  error.textContent = "";
  error.classList.add("hidden");
}

function validateSaleForm(data) {
  const requiredFields = [
    "code",
    "status",
    "saleDate",
    "clientId",
    "productId",
    "paymentMethod"
  ];

  for (const field of requiredFields) {
    if (!data[field]) {
      return "Preencha todos os campos obrigatórios da venda.";
    }
  }

  if (!data.quantity || data.quantity <= 0) {
    return "Informe uma quantidade válida maior que zero.";
  }

  if (!data.unitPrice || data.unitPrice <= 0) {
    return "Informe um valor unitário válido maior que zero.";
  }

  return "";
}

function createSale(formData) {
  const sales = getSalesData();

  const newSale = {
    id: generateSaleId(),
    ...formData,
    createdAt: new Date().toISOString().split("T")[0]
  };

  const updatedSales = [newSale, ...sales];
  saveSalesData(updatedSales);
}

function updateSale(saleId, formData) {
  const sales = getSalesData();

  const updatedSales = sales.map((sale) => {
    if (sale.id !== saleId) {
      return sale;
    }

    return {
      ...sale,
      ...formData
    };
  });

  saveSalesData(updatedSales);
}

function handleSaleFormSubmit(event) {
  event.preventDefault();

  const formData = getSaleFormData();
  const validationError = validateSaleForm(formData);
  const saleId = getSaleIdFromUrl();

  hideSaleFormError();

  if (validationError) {
    showSaleFormError(validationError);
    return;
  }

  if (saleId) {
    updateSale(saleId, formData);
  } else {
    createSale(formData);
  }

  window.location.href = "sales.html";
}

function bindSaleFormActions() {
  const form = document.getElementById("sale-form-page");
  const productField = document.getElementById("sale-product-id");
  const quantityField = document.getElementById("sale-quantity");
  const unitPriceField = document.getElementById("sale-unit-price");

  if (form) {
    form.addEventListener("submit", handleSaleFormSubmit);
  }

  if (productField) {
    productField.addEventListener("change", fillSaleUnitPriceFromSelectedProduct);
  }

  if (quantityField) {
    quantityField.addEventListener("input", updateSaleTotalValue);
  }

  if (unitPriceField) {
    unitPriceField.addEventListener("input", () => {
      unitPriceField.value = formatCurrencyInput(unitPriceField.value);
      updateSaleTotalValue();
    });
  }
}

function loadSaleForEdit() {
  const saleId = getSaleIdFromUrl();

  if (!saleId) {
    const codeField = document.getElementById("sale-code");
    const dateField = document.getElementById("sale-date");

    if (codeField && !codeField.value) {
      codeField.value = generateSaleCode();
    }

    if (dateField && !dateField.value) {
      dateField.value = new Date().toISOString().split("T")[0];
    }

    return;
  }

  const sale = findSaleById(saleId);

  if (!sale) {
    window.location.href = "sales.html";
    return;
  }

  fillSaleForm(sale);
}

function initializeSaleFormPage() {
  const saleFormPage = document.body.dataset.page === "sale-form";

  if (!saleFormPage) {
    return;
  }

  renderSaleFormUser();
  updateSaleFormPageTitle();
  populateSaleClientOptions();
  populateSaleProductOptions();
  loadSaleForEdit();
  bindSaleFormActions();
  updateSaleTotalValue();
}

document.addEventListener("DOMContentLoaded", initializeSaleFormPage);