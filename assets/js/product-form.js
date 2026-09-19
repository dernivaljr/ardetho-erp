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

function bindProductFormPhpActions() {
  const itemTypeField = document.getElementById("product-item-type");
  const allFields = document.querySelectorAll(
    "#product-form-page input, #product-form-page select, #product-form-page textarea"
  );

  if (itemTypeField) {
    itemTypeField.addEventListener("change", updateProductItemTypeFields);
  }

  allFields.forEach((field) => {
    field.addEventListener("input", () => {
      const group = field.closest(".form-group");
      if (group) {
        group.classList.remove("field-invalid");
      }
    });

    field.addEventListener("change", () => {
      const group = field.closest(".form-group");
      if (group) {
        group.classList.remove("field-invalid");
      }
    });
  });
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

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "product-form") {
    updateProductItemTypeFields();
    applyProductInputMasks();
    bindProductFormPhpActions();
  }
});
