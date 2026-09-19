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

function isValidZipCode(zipCode) {
  return onlyDigits(zipCode).length === 8;
}

function bindClientFormPhpActions() {
  const personTypeField = document.getElementById("client-person-type");
  const zipCodeField = document.getElementById("client-zip-code");

  if (personTypeField) {
    personTypeField.addEventListener("change", updateClientPersonTypeFields);
  }

  if (zipCodeField) {
    zipCodeField.addEventListener("blur", handleZipCodeLookup);
  }

  const allFields = document.querySelectorAll(
    "#client-form-page input, #client-form-page select, #client-form-page textarea"
  );

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

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "client-form") {
    updateClientPersonTypeFields();
    applyInputMasks();
    bindClientFormPhpActions();
  }
});
