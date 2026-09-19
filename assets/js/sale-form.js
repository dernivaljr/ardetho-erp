function getPhpSaleItemRows() {
  return [...document.querySelectorAll("[data-sale-item-row]")];
}

function updatePhpSaleRowSubtotal(row) {
  const quantityField = row.querySelector("[data-sale-quantity]");
  const unitPriceField = row.querySelector("[data-sale-unit-price]");
  const subtotalField = row.querySelector("[data-sale-subtotal]");

  if (!quantityField || !unitPriceField || !subtotalField) {
    return 0;
  }

  const quantity = Number(quantityField.value.replace(",", ".")) || 0;
  const unitPrice = parseCurrencyValue(unitPriceField.value || "");
  const subtotal = quantity * unitPrice;

  subtotalField.value = subtotal > 0 ? formatCurrencyValue(subtotal) : "";

  return subtotal;
}

function updatePhpSaleTotal() {
  const totalField = document.querySelector("[data-sale-total]");
  const total = getPhpSaleItemRows().reduce((sum, row) => {
    return sum + updatePhpSaleRowSubtotal(row);
  }, 0);

  if (totalField) {
    totalField.value = total > 0 ? formatCurrencyValue(total) : "";
  }
}

function fillPhpSaleUnitPrice(row) {
  if (!row) {
    return;
  }

  const productField = row.querySelector("[data-sale-product]");
  const unitPriceField = row.querySelector("[data-sale-unit-price]");

  if (!productField || !unitPriceField) {
    return;
  }

  const selectedOption = productField.selectedOptions[0];
  const price = selectedOption?.dataset.price;

  unitPriceField.value = price ? formatCurrencyValue(Number(price)) : "";
  updatePhpSaleTotal();
}

function clearPhpSaleItemRow(row) {
  const productField = row.querySelector("[data-sale-product]");
  const quantityField = row.querySelector("[data-sale-quantity]");
  const unitPriceField = row.querySelector("[data-sale-unit-price]");
  const subtotalField = row.querySelector("[data-sale-subtotal]");

  if (productField) {
    productField.value = "";
  }

  if (quantityField) {
    quantityField.value = "1";
  }

  if (unitPriceField) {
    unitPriceField.value = "";
  }

  if (subtotalField) {
    subtotalField.value = "";
  }
}

function addPhpSaleItemRow() {
  const list = document.getElementById("sale-items-list");
  const firstRow = document.querySelector("[data-sale-item-row]");

  if (!list || !firstRow) {
    return;
  }

  const newRow = firstRow.cloneNode(true);
  clearPhpSaleItemRow(newRow);
  list.appendChild(newRow);
  updatePhpSaleRemoveButtons();
  updatePhpSaleTotal();
}

function removePhpSaleItemRow(row) {
  if (!row) {
    return;
  }

  const rows = getPhpSaleItemRows();

  if (rows.length <= 1) {
    clearPhpSaleItemRow(row);
  } else {
    row.remove();
  }

  updatePhpSaleRemoveButtons();
  updatePhpSaleTotal();
}

function updatePhpSaleRemoveButtons() {
  const rows = getPhpSaleItemRows();
  rows.forEach((row) => {
    const button = row.querySelector("[data-sale-remove-item]");
    if (button) {
      button.disabled = rows.length <= 1;
    }
  });
}

function bindSaleFormPhpActions() {
  const addItemButton = document.getElementById("sale-add-item");
  const form = document.getElementById("sale-form-page");

  if (addItemButton) {
    addItemButton.addEventListener("click", addPhpSaleItemRow);
  }

  document.addEventListener("change", (event) => {
    const productField = event.target.closest("[data-sale-product]");

    if (productField) {
      fillPhpSaleUnitPrice(productField.closest("[data-sale-item-row]"));
    }
  });

  document.addEventListener("input", (event) => {
    const unitPriceField = event.target.closest("[data-sale-unit-price]");

    if (unitPriceField) {
      unitPriceField.value = formatCurrencyInput(unitPriceField.value);
      updatePhpSaleTotal();
      return;
    }

    if (event.target.closest("[data-sale-quantity]")) {
      updatePhpSaleTotal();
    }
  });

  document.addEventListener("click", (event) => {
    const removeButton = event.target.closest("[data-sale-remove-item]");

    if (!removeButton) {
      return;
    }

    removePhpSaleItemRow(removeButton.closest("[data-sale-item-row]"));
  });

  if (form) {
    form.addEventListener("submit", () => {
      updatePhpSaleTotal();
    });
  }

  updatePhpSaleRemoveButtons();
  updatePhpSaleTotal();
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "sale-form") {
    bindSaleFormPhpActions();
  }
});
