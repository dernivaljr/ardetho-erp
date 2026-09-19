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

function syncPhpFinancialDataFromSelectedSale() {
  const saleField = document.getElementById("financial-sale-id");
  const clientField = document.getElementById("financial-client-id");
  const categoryField = document.getElementById("financial-category");
  const descriptionField = document.getElementById("financial-description");
  const amountField = document.getElementById("financial-amount");
  const paymentMethodField = document.getElementById("financial-payment-method");

  if (!saleField || !clientField || !categoryField || !descriptionField || !amountField || !paymentMethodField) {
    return;
  }

  const selectedOption = saleField.selectedOptions[0];

  if (!selectedOption || !selectedOption.value) {
    return;
  }

  if (selectedOption.dataset.clientId) {
    clientField.value = selectedOption.dataset.clientId;
  }

  categoryField.value = selectedOption.dataset.category || "Venda";
  descriptionField.value = selectedOption.dataset.description || "";

  if (selectedOption.dataset.amount) {
    amountField.value = formatCurrencyValue(Number(selectedOption.dataset.amount));
  }

  if (selectedOption.dataset.paymentMethod) {
    paymentMethodField.value = selectedOption.dataset.paymentMethod;
  }
}

function bindFinancialFormPhpActions() {
  const form = document.getElementById("financial-form-page");
  const amountField = document.getElementById("financial-amount");
  const saleField = document.getElementById("financial-sale-id");
  const entryTypeField = document.getElementById("financial-entry-type");

  if (amountField) {
    amountField.addEventListener("input", () => {
      if (amountField.hasAttribute("readonly")) {
        return;
      }

      amountField.value = formatCurrencyInput(amountField.value);
    });
  }

  if (saleField) {
    saleField.addEventListener("change", syncPhpFinancialDataFromSelectedSale);
  }

  if (entryTypeField) {
    entryTypeField.addEventListener("change", () => {
      updateFinancialEntryTypeFields();
      syncPhpFinancialDataFromSelectedSale();
    });
  }

  if (form) {
    form.addEventListener("submit", () => {
      if (amountField && !amountField.hasAttribute("readonly")) {
        amountField.value = formatCurrencyInput(amountField.value);
      }
    });
  }

  updateFinancialEntryTypeFields();
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "financial-form") {
    bindFinancialFormPhpActions();
  }
});
