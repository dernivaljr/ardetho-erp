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
