function renderProductFormUser() {
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

function getProductsData() {
  return getAppSection("products", appData.products);
}

function saveProductsData(products) {
  return updateAppData("products", products);
}

function generateProductId(itemType) {
  const prefix = itemType === "Serviço" ? "SRV" : "PRD";
  return `${prefix}-${Date.now()}`;
}

function getProductIdFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

function findProductById(productId) {
  if (!productId) {
    return null;
  }

  const products = getProductsData();
  return products.find((product) => product.id === productId) || null;
}

function isProductEditMode() {
  return Boolean(getProductIdFromUrl());
}

function updateProductFormPageTitle() {
  const title = document.querySelector(".page-title");
  const description = document.querySelector(".page-description");
  const documentTitle = document.querySelector("title");

  if (!isProductEditMode()) {
    return;
  }

  if (title) {
    title.textContent = "Editar item";
  }

  if (description) {
    description.textContent = "Atualize os dados cadastrais do item selecionado.";
  }

  if (documentTitle) {
    documentTitle.textContent = "Editar Item | Ardetho ERP";
  }
}
function updateProductStatusOptions() {
  const itemType = document.getElementById("product-item-type")?.value || "Produto";
  const statusField = document.getElementById("product-status");

  if (!statusField) {
    return;
  }

  const currentValue = statusField.value;

  if (itemType === "Produto") {
    statusField.innerHTML = `
      <option value="Ativo">Ativo</option>
      <option value="Inativo">Inativo</option>
    `;
  } else {
    statusField.innerHTML = `
      <option value="Ativo">Ativo</option>
      <option value="Em análise">Em análise</option>
      <option value="Inativo">Inativo</option>
    `;
  }

  const optionExists = [...statusField.options].some(
    (option) => option.value === currentValue
  );

  statusField.value = optionExists ? currentValue : "Ativo";
}

function updateProductItemTypeFields() {
  const itemType = document.getElementById("product-item-type")?.value || "Produto";
  const productSection = document.getElementById("product-section-product");
  const serviceSection = document.getElementById("product-section-service");

  if (itemType === "Produto") {
    productSection?.classList.remove("hidden");
    serviceSection?.classList.add("hidden");
  } else {
    productSection?.classList.add("hidden");
    serviceSection?.classList.remove("hidden");
  }

  updateProductStatusOptions();
}

function fillProductForm(product) {
  if (!product) {
    return;
  }

  document.getElementById("product-item-type").value = product.itemType || "Produto";
    const normalizedStatus =
    product.itemType === "Produto"
    ? (product.status === "Inativo" ? "Inativo" : "Ativo")
    : (product.status || "Ativo");
  document.getElementById("product-status").value = normalizedStatus;
  document.getElementById("product-brand").value = product.brand || "";

  document.getElementById("product-code").value = product.code || "";
  document.getElementById("product-name").value = product.name || "";
  document.getElementById("product-category").value = product.category || "";
  document.getElementById("product-description").value = product.description || "";

  document.getElementById("product-price").value = product.price ?? "";
  document.getElementById("product-unit").value = product.unit || "";

  document.getElementById("product-stock").value = product.stock ?? "";
  document.getElementById("product-minimum-stock").value = product.minimumStock ?? "";
  document.getElementById("product-supplier").value = product.supplier || "";
  document.getElementById("product-ncm").value = product.ncm || "";

  document.getElementById("product-estimated-deadline").value = product.estimatedDeadline || "";
  document.getElementById("product-department").value = product.department || "";

  updateProductItemTypeFields();
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

function formatNcm(value) {
  const digits = onlyDigits(value).slice(0, 8);

  if (digits.length <= 4) {
    return digits;
  }

  if (digits.length <= 6) {
    return digits.replace(/^(\d{4})(\d+)/, "$1.$2");
  }

  return digits.replace(/^(\d{4})(\d{2})(\d{0,2}).*/, (_, a, b, c) => {
    return c ? `${a}.${b}.${c}` : `${a}.${b}`;
  });
}

function applyProductInputMasks() {
  const priceField = document.getElementById("product-price");
  const ncmField = document.getElementById("product-ncm");

  if (priceField) {
    priceField.addEventListener("input", () => {
      priceField.value = formatCurrencyInput(priceField.value);
    });
  }

  if (ncmField) {
    ncmField.addEventListener("input", () => {
      ncmField.value = formatNcm(ncmField.value);
    });
  }
}

function parseCurrencyValue(value) {
  if (!value) {
    return 0;
  }

  const normalized = value.replace(/\./g, "").replace(",", ".");
  return Number(normalized) || 0;
}

function getProductFormData() {
  return {
    itemType: document.getElementById("product-item-type")?.value || "Produto",
    status: document.getElementById("product-status")?.value || "Ativo",

    code: document.getElementById("product-code")?.value.trim() || "",
    name: document.getElementById("product-name")?.value.trim() || "",
    category: document.getElementById("product-category")?.value.trim() || "",
    description: document.getElementById("product-description")?.value.trim() || "",

    price: parseCurrencyValue(document.getElementById("product-price")?.value.trim() || ""),
    unit: document.getElementById("product-unit")?.value || "",

    stock: document.getElementById("product-stock")?.value !== ""
      ? Number(document.getElementById("product-stock").value)
      : null,

    minimumStock: document.getElementById("product-minimum-stock")?.value !== ""
      ? Number(document.getElementById("product-minimum-stock").value)
      : null,

    brand: document.getElementById("product-brand")?.value.trim() || "",
    supplier: document.getElementById("product-supplier")?.value.trim() || "",
    ncm: document.getElementById("product-ncm")?.value.trim() || "",

    estimatedDeadline: document.getElementById("product-estimated-deadline")?.value.trim() || "",
    department: document.getElementById("product-department")?.value.trim() || ""
  };
}

function isValidNcm(ncm) {
  return onlyDigits(ncm).length === 8;
}

function showProductFormError(message) {
  const error = document.getElementById("product-form-error");

  if (!error) {
    return;
  }

  error.textContent = message;
  error.classList.remove("hidden");
}

function hideProductFormError() {
  const error = document.getElementById("product-form-error");

  if (!error) {
    return;
  }

  error.textContent = "";
  error.classList.add("hidden");
}

function validateProductForm(data) {
  const commonRequired = ["code", "name", "category", "unit"];

  for (const field of commonRequired) {
    if (!data[field]) {
      return "Preencha todos os campos obrigatórios de identificação e comercial.";
    }
  }

  if (!data.price || data.price <= 0) {
    return "Informe um preço válido maior que zero.";
  }

  if (data.itemType === "Produto") {
    const requiredProduct = ["brand", "supplier", "ncm"];

    for (const field of requiredProduct) {
      if (!data[field]) {
        return "Preencha todos os campos obrigatórios do produto.";
      }
    }

    if (data.stock === null || data.stock < 0) {
      return "Informe um estoque atual válido.";
    }

    if (data.minimumStock === null || data.minimumStock < 0) {
      return "Informe um estoque mínimo válido.";
    }

    if (!isValidNcm(data.ncm)) {
      return "Informe um NCM válido com 8 dígitos.";
    }
  }

  if (data.itemType === "Serviço") {
    const requiredService = ["estimatedDeadline", "department"];

    for (const field of requiredService) {
      if (!data[field]) {
        return "Preencha todos os campos obrigatórios do serviço.";
      }
    }
  }

  return "";
}

function createProduct(formData) {
  const products = getProductsData();

  const normalizedData = {
    ...formData,
    status: getComputedProductStatus(formData)
  };

  const newProduct = {
    id: generateProductId(formData.itemType),
    ...normalizedData,
    createdAt: new Date().toISOString().split("T")[0]
  };

  const updatedProducts = [newProduct, ...products];
  saveProductsData(updatedProducts);
}

function updateProduct(productId, formData) {
  const products = getProductsData();

  const normalizedData = {
    ...formData,
    status: getComputedProductStatus(formData)
  };

  const updatedProducts = products.map((product) => {
    if (product.id !== productId) {
      return product;
    }

    return {
      ...product,
      ...normalizedData
    };
  });

  saveProductsData(updatedProducts);
}

function handleProductFormSubmit(event) {
  event.preventDefault();

  const formData = getProductFormData();
  const validationError = validateProductForm(formData);
  const productId = getProductIdFromUrl();

  hideProductFormError();

  if (validationError) {
    showProductFormError(validationError);
    return;
  }

  if (productId) {
    updateProduct(productId, formData);
  } else {
    createProduct(formData);
  }

  window.location.href = "products.html";
}

function bindProductFormActions() {
  const form = document.getElementById("product-form-page");
  const itemTypeField = document.getElementById("product-item-type");

  if (form) {
    form.addEventListener("submit", handleProductFormSubmit);
  }

  if (itemTypeField) {
    itemTypeField.addEventListener("change", updateProductItemTypeFields);
  }
}

function loadProductForEdit() {
  const productId = getProductIdFromUrl();

  if (!productId) {
    updateProductItemTypeFields();
    return;
  }

  const product = findProductById(productId);

  if (!product) {
    window.location.href = "products.html";
    return;
  }

  fillProductForm(product);
}

function initializeProductFormPage() {
  const productFormPage = document.body.dataset.page === "product-form";

  if (!productFormPage) {
    return;
  }

  renderProductFormUser();
  updateProductFormPageTitle();
  loadProductForEdit();
  applyProductInputMasks();
  bindProductFormActions();
}
function getComputedProductStatus(data) {
  if (data.itemType !== "Produto") {
    return data.status;
  }

  if (data.status === "Inativo") {
    return "Inativo";
  }

  if (data.stock === 0) {
    return "Indisponível";
  }

  if (data.stock > 0 && data.stock <= data.minimumStock) {
    return "Baixo estoque";
  }

  return "Disponível";
}
function updateProductStatusOptions() {
  const itemType = document.getElementById("product-item-type")?.value || "Produto";
  const statusField = document.getElementById("product-status");

  if (!statusField) {
    return;
  }

  const currentValue = statusField.value;

  if (itemType === "Produto") {
    statusField.innerHTML = `
      <option value="Ativo">Ativo</option>
      <option value="Inativo">Inativo</option>
    `;
  } else {
    statusField.innerHTML = `
      <option value="Ativo">Ativo</option>
      <option value="Em análise">Em análise</option>
      <option value="Inativo">Inativo</option>
    `;
  }

  const optionExists = [...statusField.options].some(
    (option) => option.value === currentValue
  );

  statusField.value = optionExists ? currentValue : "Ativo";
}
document.addEventListener("DOMContentLoaded", initializeProductFormPage);