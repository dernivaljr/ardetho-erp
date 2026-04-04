function getFinancialData() {
  return getAppSection("financial", appData.financial);
}

function renderFinancialUser() {
  const currentUser = getCurrentUser();

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

function formatFinancialCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(value);
}

function getFinancialBadgeClass(status) {
  const normalizedStatus = status.toLowerCase();

  if (normalizedStatus.includes("recebido")) return "badge-success";
  if (normalizedStatus.includes("pago")) return "badge-success";
  if (normalizedStatus.includes("pendente")) return "badge-warning";
  if (normalizedStatus.includes("agendado")) return "badge-info";
  if (normalizedStatus.includes("vencido")) return "badge-danger";

  return "badge-info";
}

function renderFinancialTable(items) {
  const tbody = document.getElementById("financial-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  items.forEach((item) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${item.description}</td>
      <td>${item.type}</td>
      <td>${item.category}</td>
      <td>${item.dueDate}</td>
      <td>${formatFinancialCurrency(item.value)}</td>
      <td><span class="${getFinancialBadgeClass(item.status)}">${item.status}</span></td>
      <td>
        <div class="action-group">
          <a href="#" class="btn-secondary">Ver</a>
          <a href="#" class="btn-secondary">Editar</a>
        </div>
      </td>
    `;

    tbody.appendChild(row);
  });
}

function filterFinancialByPeriod(items, periodFilter) {
  if (!periodFilter || periodFilter === "todos") {
    return items;
  }

  const referenceDate = new Date("2026-04-20T00:00:00");

  return items.filter((item) => {
    const [day, month, year] = item.dueDate.split("/");
    const itemDate = new Date(`${year}-${month}-${day}T00:00:00`);

    const diffInMs = referenceDate - itemDate;
    const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

    if (periodFilter === "hoje") return diffInDays === 0;
    if (periodFilter === "7dias") return diffInDays <= 7;
    if (periodFilter === "30dias") return diffInDays <= 30;

    return true;
  });
}

function filterFinancial(items, searchTerm, typeFilter, statusFilter, periodFilter) {
  const filteredByPeriod = filterFinancialByPeriod(items, periodFilter);

  return filteredByPeriod.filter((item) => {
    const matchesSearch =
      !searchTerm ||
      item.description.toLowerCase().includes(searchTerm) ||
      item.category.toLowerCase().includes(searchTerm);

    const matchesType =
      !typeFilter ||
      typeFilter === "todos" ||
      item.type.toLowerCase() === typeFilter;

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      item.status.toLowerCase() === statusFilter;

    return matchesSearch && matchesType && matchesStatus;
  });
}

function bindFinancialFilters() {
  const searchInput = document.getElementById("financial-search");
  const typeSelect = document.getElementById("financial-type-filter");
  const statusSelect = document.getElementById("financial-status-filter");
  const periodSelect = document.getElementById("financial-period-filter");

  const applyFilters = () => {
    const items = getFinancialData();

    const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
    const typeFilter = typeSelect ? typeSelect.value.toLowerCase() : "";
    const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";
    const periodFilter = periodSelect ? periodSelect.value.toLowerCase() : "";

    const filteredItems = filterFinancial(
      items,
      searchTerm,
      typeFilter,
      statusFilter,
      periodFilter
    );

    renderFinancialTable(filteredItems);
  };

  if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
  }

  if (typeSelect) {
    typeSelect.addEventListener("change", applyFilters);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", applyFilters);
  }

  if (periodSelect) {
    periodSelect.addEventListener("change", applyFilters);
  }
}

function initializeFinancialPage() {
  const financialPage = document.body.dataset.page === "financial";

  if (!financialPage) {
    return;
  }

  renderFinancialUser();
  renderFinancialTable(getFinancialData());
  bindFinancialFilters();
}

document.addEventListener("DOMContentLoaded", initializeFinancialPage);