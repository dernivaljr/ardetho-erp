function getHrData() {
  return getAppSection("hr", []);
}

function formatHrCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(Number(value) || 0);
}

function formatHrDate(dateString) {
  if (!dateString) return "—";

  const [year, month, day] = dateString.split("-");
  if (!year || !month || !day) return dateString;

  return `${day}/${month}/${year}`;
}

function getHrBadgeClass(status) {
  const normalized = (status || "").toLowerCase();

  if (normalized === "ativo") return "badge-success";
  if (normalized === "férias") return "badge-info";
  if (normalized === "afastado") return "badge-warning";
  if (normalized === "desligado") return "badge-danger";

  return "badge-neutral";
}

function renderHrMetrics() {
  const data = getHrData();

  const active = data.filter((item) => item.status === "Ativo");
  const vacation = data.filter((item) => item.status === "Férias");
  const away = data.filter((item) => item.status === "Afastado");

  const payroll = active.reduce((sum, item) => {
    return sum + (Number(item.salary) || 0);
  }, 0);

  const activeEl = document.getElementById("hr-metric-active");
  const activeTextEl = document.getElementById("hr-metric-active-text");
  const vacationEl = document.getElementById("hr-metric-vacation");
  const vacationTextEl = document.getElementById("hr-metric-vacation-text");
  const awayEl = document.getElementById("hr-metric-away");
  const awayTextEl = document.getElementById("hr-metric-away-text");
  const payrollEl = document.getElementById("hr-metric-payroll");
  const payrollTextEl = document.getElementById("hr-metric-payroll-text");

  if (activeEl) activeEl.textContent = active.length;
  if (activeTextEl) {
    activeTextEl.textContent =
      active.length > 0
        ? `${active.length} colaborador(es) em atividade.`
        : "Nenhum colaborador ativo.";
  }

  if (vacationEl) vacationEl.textContent = vacation.length;
  if (vacationTextEl) {
    vacationTextEl.textContent =
      vacation.length > 0
        ? `${vacation.length} colaborador(es) em período de férias.`
        : "Nenhum colaborador em férias.";
  }

  if (awayEl) awayEl.textContent = away.length;
  if (awayTextEl) {
    awayTextEl.textContent =
      away.length > 0
        ? `${away.length} colaborador(es) afastado(s).`
        : "Nenhum colaborador afastado.";
  }

  if (payrollEl) payrollEl.textContent = formatHrCurrency(payroll);
  if (payrollTextEl) {
    payrollTextEl.textContent = "Soma salarial dos colaboradores ativos.";
  }
}

function populateHrDepartmentFilter() {
  const filter = document.getElementById("hr-department-filter");

  if (!filter) return;

  const data = getHrData();
  const departments = [
    ...new Set(
      data
        .map((employee) => employee.department)
        .filter(Boolean)
        .sort((a, b) => a.localeCompare(b, "pt-BR"))
    )
  ];

  filter.innerHTML = `<option value="all">Todos os departamentos</option>`;

  departments.forEach((department) => {
    const option = document.createElement("option");
    option.value = department;
    option.textContent = department;
    filter.appendChild(option);
  });
}

function getFilteredHrData() {
  const data = getHrData();
  const search = document.getElementById("hr-search")?.value.trim().toLowerCase() || "";
  const status = document.getElementById("hr-status-filter")?.value || "all";
  const department = document.getElementById("hr-department-filter")?.value || "all";

  return data.filter((employee) => {
    const matchesSearch =
      !search ||
      employee.fullName?.toLowerCase().includes(search) ||
      employee.role?.toLowerCase().includes(search) ||
      employee.department?.toLowerCase().includes(search) ||
      employee.email?.toLowerCase().includes(search);

    const matchesStatus = status === "all" || employee.status === status;
    const matchesDepartment = department === "all" || employee.department === department;

    return matchesSearch && matchesStatus && matchesDepartment;
  });
}

function renderHrTable() {
  const tbody = document.getElementById("hr-table-body");

  if (!tbody) return;

  const data = getFilteredHrData();

  tbody.innerHTML = "";

  if (!data.length) {
    const isFiltered = Boolean(
      document.getElementById("hr-search")?.value.trim() ||
      (document.getElementById("hr-status-filter")?.value || "all") !== "all" ||
      (document.getElementById("hr-department-filter")?.value || "all") !== "all"
    );

    tbody.innerHTML = `
      <tr>
        <td colspan="7">
          <div class="empty-state">
            <h3>${isFiltered ? "Nenhum colaborador encontrado" : "Nenhum colaborador cadastrado"}</h3>
            <p>
              ${
                isFiltered
                  ? "Não há registros compatíveis com os filtros aplicados. Ajuste a busca ou os filtros para continuar."
                  : "Ainda não há colaboradores registrados no módulo de RH. Cadastre o primeiro colaborador para começar."
              }
            </p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  data.forEach((employee) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>
        <strong>${employee.fullName || "—"}</strong><br />
        <span style="color: var(--color-text-secondary); font-size: 13px;">${employee.email || "—"}</span>
      </td>
      <td>${employee.role || "—"}</td>
      <td>${employee.department || "—"}</td>
      <td>${formatHrCurrency(employee.salary)}</td>
      <td>${formatHrDate(employee.admissionDate)}</td>
      <td><span class="${getHrBadgeClass(employee.status)}">${employee.status || "—"}</span></td>
      <td>
        <div class="action-group">
          <a href="hr-form.html?id=${employee.id}" class="btn-secondary">Editar</a>
          <a href="#" class="btn-danger" data-hr-delete="${employee.id}">Excluir</a>
        </div>
      </td>
    `;

    tbody.appendChild(row);
  });
}

function deleteHrEmployee(employeeId) {
  const data = getHrData();
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
  const success = updateAppData("hr", updatedData);

  if (!success) {
    alert("Erro ao excluir colaborador.");
    return;
  }

  renderHrMetrics();
  renderHrTable();
}

function bindHrTableActions() {
  document.addEventListener("click", (event) => {
    const deleteButton = event.target.closest("[data-hr-delete]");

    if (!deleteButton) {
      return;
    }

    event.preventDefault();

    const employeeId = deleteButton.getAttribute("data-hr-delete");

    if (!employeeId) {
      return;
    }

    deleteHrEmployee(employeeId);
  });
}

function bindHrFilters() {
  const searchInput = document.getElementById("hr-search");
  const statusFilter = document.getElementById("hr-status-filter");
  const departmentFilter = document.getElementById("hr-department-filter");

  if (searchInput) {
    searchInput.addEventListener("input", renderHrTable);
  }

  if (statusFilter) {
    statusFilter.addEventListener("change", renderHrTable);
  }

  if (departmentFilter) {
    departmentFilter.addEventListener("change", renderHrTable);
  }
}

function initializeHrPage() {
  if (document.body.dataset.page !== "hr") {
    return;
  }

  populateHrDepartmentFilter();
  renderHrMetrics();
  renderHrTable();
  bindHrFilters();
  bindHrTableActions();
}

document.addEventListener("DOMContentLoaded", initializeHrPage);