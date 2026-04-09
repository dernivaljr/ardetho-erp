function getSalesData() {
  return getAppSection("sales", appData.sales);
}

function saveSalesData(sales) {
  return updateAppData("sales", sales);
}

function renderSalesUser() {
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

function getSaleBadgeClass(status) {
  const normalizedStatus = (status || "").toLowerCase();

  if (normalizedStatus.includes("cancelado")) return "badge-danger";
  if (normalizedStatus.includes("em análise")) return "badge-info";
  if (normalizedStatus.includes("aprovado")) return "badge-success";
  if (normalizedStatus.includes("faturado")) return "badge-info";
  if (normalizedStatus.includes("concluído")) return "badge-success";

  return "badge-info";
}

function formatSaleCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(Number(value) || 0);
}

function formatSaleDate(dateString) {
  if (!dateString) {
    return "—";
  }

  const [year, month, day] = dateString.split("-");
  if (!year || !month || !day) {
    return dateString;
  }

  return `${day}/${month}/${year}`;
}

function renderSalesSummary(sales) {
  const totalOrdersEl = document.getElementById("sales-total-orders");
  const analysisOrdersEl = document.getElementById("sales-analysis-orders");
  const totalRevenueEl = document.getElementById("sales-total-revenue");

  const totalOrders = sales.length;

  const analysisOrders = sales.filter((sale) => {
    return (sale.status || "").toLowerCase() === "em análise";
  }).length;

  const totalRevenue = sales.reduce((sum, sale) => {
    return sum + (Number(sale.totalValue) || 0);
  }, 0);

  if (totalOrdersEl) {
    totalOrdersEl.textContent = totalOrders;
  }

  if (analysisOrdersEl) {
    analysisOrdersEl.textContent = analysisOrders;
  }

  if (totalRevenueEl) {
    totalRevenueEl.textContent = formatSaleCurrency(totalRevenue);
  }
}

function renderSalesTable(sales) {
  const tbody = document.getElementById("sales-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  if (!sales.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8">
          <div class="empty-state">
            <h3>Nenhum pedido encontrado</h3>
            <p>Não há vendas compatíveis com os filtros atuais.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  sales.forEach((sale) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${sale.code || "—"}</td>
      <td>${sale.clientName || "—"}</td>
      <td>${sale.productName || "—"}</td>
      <td>${sale.itemType || "—"}</td>
      <td>${formatSaleCurrency(sale.totalValue)}</td>
      <td><span class="${getSaleBadgeClass(sale.status)}">${sale.status || "—"}</span></td>
      <td>${formatSaleDate(sale.saleDate)}</td>
      <td>
        <div class="action-group">
          <a href="sale-form.html?id=${sale.id}" class="btn-secondary">Editar</a>
          <a href="#" class="btn-danger" data-action="delete-sale" data-sale-id="${sale.id}">Excluir</a>
        </div>
      </td>
    `;

    tbody.appendChild(row);
  });
}

function filterSales(sales, searchTerm, statusFilter, clientFilter) {
  return sales.filter((sale) => {
    const code = (sale.code || "").toLowerCase();
    const clientName = (sale.clientName || "").toLowerCase();
    const productName = (sale.productName || "").toLowerCase();
    const itemType = (sale.itemType || "").toLowerCase();
    const status = (sale.status || "").toLowerCase();

    const matchesSearch =
      !searchTerm ||
      code.includes(searchTerm) ||
      clientName.includes(searchTerm) ||
      productName.includes(searchTerm) ||
      itemType.includes(searchTerm);

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      status === statusFilter;

    const matchesClient =
      !clientFilter ||
      clientFilter === "todos" ||
      clientName === clientFilter;

    return matchesSearch && matchesStatus && matchesClient;
  });
}

function populateSalesClientFilter() {
  const clientSelect = document.getElementById("sales-client-filter");

  if (!clientSelect) {
    return;
  }

  const currentValue = clientSelect.value || "todos";
  const sales = getSalesData();

  const uniqueClients = [...new Set(
    sales
      .map((sale) => (sale.clientName || "").trim())
      .filter((client) => client)
      .sort((a, b) => a.localeCompare(b, "pt-BR"))
  )];

  clientSelect.innerHTML = `<option value="todos">Cliente</option>`;

  uniqueClients.forEach((client) => {
    const option = document.createElement("option");
    option.value = client.toLowerCase();
    option.textContent = client;
    clientSelect.appendChild(option);
  });

  const optionExists = [...clientSelect.options].some(
    (option) => option.value === currentValue
  );

  clientSelect.value = optionExists ? currentValue : "todos";
}

function getFilteredSales() {
  const sales = getSalesData();

  const searchInput = document.getElementById("sales-search");
  const statusSelect = document.getElementById("sales-status-filter");
  const clientSelect = document.getElementById("sales-client-filter");

  const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
  const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";
  const clientFilter = clientSelect ? clientSelect.value.toLowerCase() : "";

  return filterSales(sales, searchTerm, statusFilter, clientFilter);
}

function refreshSalesTable() {
  const allSales = getSalesData();

  populateSalesClientFilter();
  renderSalesSummary(allSales);
  renderSalesTable(getFilteredSales());
}

function bindSalesFilters() {
  const searchInput = document.getElementById("sales-search");
  const statusSelect = document.getElementById("sales-status-filter");
  const clientSelect = document.getElementById("sales-client-filter");

  if (searchInput) {
    searchInput.addEventListener("input", refreshSalesTable);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", refreshSalesTable);
  }

  if (clientSelect) {
    clientSelect.addEventListener("change", refreshSalesTable);
  }
}

function deleteSale(saleId) {
  const sales = getSalesData();
  const updatedSales = sales.filter((sale) => sale.id !== saleId);

  saveSalesData(updatedSales);
  refreshSalesTable();
}

function bindSaleActions() {
  document.addEventListener("click", (event) => {
    const deleteButton = event.target.closest("[data-action='delete-sale']");

    if (!deleteButton) {
      return;
    }

    event.preventDefault();

    const saleId = deleteButton.dataset.saleId;

    if (!saleId) {
      return;
    }

    const confirmed = window.confirm("Deseja realmente excluir este pedido?");

    if (!confirmed) {
      return;
    }

    deleteSale(saleId);
  });
}

function initializeSalesPage() {
  const salesPage = document.body.dataset.page === "sales";

  if (!salesPage) {
    return;
  }

  renderSalesUser();
  refreshSalesTable();
  bindSalesFilters();
  bindSaleActions();
}

document.addEventListener("DOMContentLoaded", initializeSalesPage);