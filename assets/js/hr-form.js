function getHrStorageData() {
  return getAppSection("hr", []);
}

function updateHrStorageData(data) {
  return updateAppData("hr", data);
}

function getHrFormElements() {
  return {
    title: document.getElementById("hr-form-title"),
    saveButton: document.getElementById("hr-save-button"),
    deleteButton: document.getElementById("hr-delete-button"),

    fullName: document.getElementById("hr-full-name"),
    email: document.getElementById("hr-email"),
    phone: document.getElementById("hr-phone"),
    role: document.getElementById("hr-role"),
    department: document.getElementById("hr-department"),
    salary: document.getElementById("hr-salary"),
    admissionDate: document.getElementById("hr-admission-date"),
    status: document.getElementById("hr-status"),
    notes: document.getElementById("hr-notes")
  };
}

function getHrQueryId() {
  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}

function generateHrId() {
  return `EMP-${Date.now()}`;
}

function getHrEmployeeById(id) {
  const data = getHrStorageData();
  return data.find((employee) => employee.id === id) || null;
}

function fillHrForm(employee) {
  const els = getHrFormElements();

  if (!employee) {
    return;
  }

  if (els.fullName) els.fullName.value = employee.fullName || "";
  if (els.email) els.email.value = employee.email || "";
  if (els.phone) els.phone.value = employee.phone || "";
  if (els.role) els.role.value = employee.role || "";
  if (els.department) els.department.value = employee.department || "";
  if (els.salary) els.salary.value = employee.salary ?? "";
  if (els.admissionDate) els.admissionDate.value = employee.admissionDate || "";
  if (els.status) els.status.value = employee.status || "Ativo";
  if (els.notes) els.notes.value = employee.notes || "";
}

function setHrFormMode() {
  const employeeId = getHrQueryId();
  const els = getHrFormElements();

  if (!els.title || !els.saveButton || !els.deleteButton) {
    return;
  }

  if (employeeId) {
    els.title.textContent = "Editar colaborador";
    els.saveButton.textContent = "Salvar alterações";
    els.deleteButton.classList.remove("hidden");
  } else {
    els.title.textContent = "Novo colaborador";
    els.saveButton.textContent = "Salvar colaborador";
    els.deleteButton.classList.add("hidden");
  }
}

function getHrFormData() {
  const els = getHrFormElements();

  return {
    fullName: els.fullName?.value.trim() || "",
    email: els.email?.value.trim() || "",
    phone: els.phone?.value.trim() || "",
    role: els.role?.value.trim() || "",
    department: els.department?.value.trim() || "",
    salary: Number(els.salary?.value || 0),
    admissionDate: els.admissionDate?.value || "",
    status: els.status?.value || "Ativo",
    notes: els.notes?.value.trim() || ""
  };
}

function validateHrFormData(data) {
  if (!data.fullName) {
    alert("Preencha o nome completo do colaborador.");
    return false;
  }

  if (!data.email) {
    alert("Preencha o e-mail do colaborador.");
    return false;
  }

  if (!data.role) {
    alert("Preencha o cargo do colaborador.");
    return false;
  }

  if (!data.department) {
    alert("Preencha o departamento do colaborador.");
    return false;
  }

  if (!data.salary || data.salary < 0) {
    alert("Preencha um salário válido.");
    return false;
  }

  if (!data.admissionDate) {
    alert("Preencha a data de admissão.");
    return false;
  }

  return true;
}

function saveHrEmployee() {
  const employeeId = getHrQueryId();
  const data = getHrStorageData();
  const formData = getHrFormData();

  if (!validateHrFormData(formData)) {
    return;
  }

  if (employeeId) {
    const updatedData = data.map((employee) => {
      if (employee.id !== employeeId) {
        return employee;
      }

      return {
        ...employee,
        ...formData
      };
    });

    const success = updateHrStorageData(updatedData);

    if (!success) {
      alert("Erro ao salvar alterações do colaborador.");
      return;
    }

    alert("Colaborador atualizado com sucesso.");
    window.location.href = "hr.html";
    return;
  }

  const newEmployee = {
    id: generateHrId(),
    ...formData
  };

  const success = updateHrStorageData([...data, newEmployee]);

  if (!success) {
    alert("Erro ao cadastrar colaborador.");
    return;
  }

  alert("Colaborador cadastrado com sucesso.");
  window.location.href = "hr.html";
}

function deleteHrEmployeeFromForm() {
  const employeeId = getHrQueryId();

  if (!employeeId) {
    return;
  }

  const data = getHrStorageData();
  const employee = data.find((item) => item.id === employeeId);

  if (!employee) {
    alert("Colaborador não encontrado.");
    return;
  }

  const confirmed = window.confirm(
    `Deseja realmente excluir o colaborador "${employee.fullName}"?`
  );

  if (!confirmed) {
    return;
  }

  const updatedData = data.filter((item) => item.id !== employeeId);
  const success = updateHrStorageData(updatedData);

  if (!success) {
    alert("Erro ao excluir colaborador.");
    return;
  }

  alert("Colaborador excluído com sucesso.");
  window.location.href = "hr.html";
}

function bindHrFormActions() {
  const els = getHrFormElements();

  if (els.saveButton) {
    els.saveButton.addEventListener("click", (event) => {
      event.preventDefault();
      saveHrEmployee();
    });
  }

  if (els.deleteButton) {
    els.deleteButton.addEventListener("click", (event) => {
      event.preventDefault();
      deleteHrEmployeeFromForm();
    });
  }
}

function initializeHrFormPage() {
  if (document.body.dataset.page !== "hr-form") {
    return;
  }

  setHrFormMode();

  const employeeId = getHrQueryId();

  if (employeeId) {
    const employee = getHrEmployeeById(employeeId);

    if (!employee) {
      alert("Colaborador não encontrado.");
      window.location.href = "hr.html";
      return;
    }

    fillHrForm(employee);
  }

  bindHrFormActions();
}

document.addEventListener("DOMContentLoaded", initializeHrFormPage);