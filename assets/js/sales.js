function getSalesData() {
  return getAppSection("sales", appData.sales);
}

function renderSalesUser() {
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

function formatSalesCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(value);
}

function getSalesBadgeClass(status) {
  const normalizedStatus = status.toLowerCase();

  if (normalizedStatus.includes("concluído")) return "badge-success";
  if (normalizedStatus.includes("aprovado")) return "badge-success";
  if (normalizedStatus.includes("pendente")) return "badge-warning";
  if (normalizedStatus.includes("análise")) return "badge-info";
  if (normalizedStatus.includes("cancelado")) return "badge-danger";

  return "badge-info";
}

function renderSalesTable(sales) {
  const tbody = document.getElementById("sales-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  sales.forEach((sale) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${sale.id}</td>
      <td>${sale.client}</td>
      <td>${sale.date}</td>
      <td>${sale.owner}</td>
      <td>${formatSalesCurrency(sale.value)}</td>
      <td><span class="${getSalesBadgeClass(sale.status)}">${sale.status}</span></td>
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

function filterSalesByPeriod(sales, periodFilter) {
  if (!periodFilter || periodFilter === "todos") {
    return sales;
  }

  const referenceDate = new Date("2026-04-14T00:00:00");

  return sales.filter((sale) => {
    const [day, month, year] = sale.date.split("/");
    const saleDate = new Date(`${year}-${month}-${day}T00:00:00`);

    const diffInMs = referenceDate - saleDate;
    const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

    if (periodFilter === "hoje") return diffInDays === 0;
    if (periodFilter === "7dias") return diffInDays <= 7;
    if (periodFilter === "30dias") return diffInDays <= 30;

    return true;
  });
}

function filterSales(sales, searchTerm, statusFilter, periodFilter) {
  const filteredByPeriod = filterSalesByPeriod(sales, periodFilter);

  return filteredByPeriod.filter((sale) => {
    const matchesSearch =
      !searchTerm ||
      sale.id.toLowerCase().includes(searchTerm) ||
      sale.client.toLowerCase().includes(searchTerm) ||
      sale.owner.toLowerCase().includes(searchTerm);

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      sale.status.toLowerCase() === statusFilter;

    return matchesSearch && matchesStatus;
  });
}

function bindSalesFilters() {
  const searchInput = document.getElementById("sales-search");
  const statusSelect = document.getElementById("sales-status-filter");
  const periodSelect = document.getElementById("sales-period-filter");

  const applyFilters = () => {
    const sales = getSalesData();

    const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
    const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";
    const periodFilter = periodSelect ? periodSelect.value.toLowerCase() : "";

    const filteredSales = filterSales(
      sales,
      searchTerm,
      statusFilter,
      periodFilter
    );

    renderSalesTable(filteredSales);
  };

  if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", applyFilters);
  }

  if (periodSelect) {
    periodSelect.addEventListener("change", applyFilters);
  }
}

function initializeSalesPage() {
  const salesPage = document.body.dataset.page === "sales";

  if (!salesPage) {
    return;
  }

  renderSalesUser();
  renderSalesTable(getSalesData());
  bindSalesFilters();
}

document.addEventListener("DOMContentLoaded", initializeSalesPage);