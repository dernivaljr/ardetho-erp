function renderReportsUser() {
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

function getClientsData() {
  return getAppSection("clients", appData.clients);
}

function getProductsData() {
  return getAppSection("products", appData.products);
}

function getSalesData() {
  return getAppSection("sales", appData.sales);
}

function getFinancialData() {
  return getAppSection("financial", appData.financial);
}

function getActiveModulesData() {
  return getActiveModules();
}

function formatReportsCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(Number(value) || 0);
}

function getReportsBadgeClass(status) {
  const normalizedStatus = (status || "").toLowerCase();

  if (normalizedStatus.includes("cancelado")) return "badge-danger";
  if (normalizedStatus.includes("pendente")) return "badge-warning";
  if (normalizedStatus.includes("recebido")) return "badge-success";
  if (normalizedStatus.includes("pago")) return "badge-info";

  return "badge-info";
}

function isDateInSelectedPeriod(dateString, period) {
  if (!dateString || !period || period === "todos") {
    return true;
  }

  const targetDate = new Date(`${dateString}T00:00:00`);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (period === "hoje") {
    return targetDate.getTime() === today.getTime();
  }

  if (period === "7dias") {
    const start = new Date(today);
    start.setDate(today.getDate() - 7);
    return targetDate >= start && targetDate <= today;
  }

  if (period === "30dias") {
    const start = new Date(today);
    start.setDate(today.getDate() - 30);
    return targetDate >= start && targetDate <= today;
  }

  if (period === "mes") {
    return (
      targetDate.getMonth() === today.getMonth() &&
      targetDate.getFullYear() === today.getFullYear()
    );
  }

  return true;
}

function getReportsPeriodFilterValue() {
  const periodField = document.getElementById("reports-period-filter");
  return periodField ? periodField.value : "todos";
}

function getFilteredReportsSales() {
  const sales = getSalesData();
  const period = getReportsPeriodFilterValue();

  return sales.filter((sale) => isDateInSelectedPeriod(sale.saleDate, period));
}

function getFilteredReportsFinancial() {
  const financial = getFinancialData();
  const period = getReportsPeriodFilterValue();

  return financial.filter((entry) => isDateInSelectedPeriod(entry.entryDate, period));
}

function renderReportsSummaryCards() {
  const sales = getFilteredReportsSales();
  const financial = getFilteredReportsFinancial();

  const revenueEl = document.getElementById("reports-total-revenue");
  const balanceEl = document.getElementById("reports-total-balance");
  const completedOrdersEl = document.getElementById("reports-completed-orders");

  const validFinancial = financial.filter((entry) => {
    return (entry.status || "").toLowerCase() !== "cancelado";
  });

  const totalRevenue = validFinancial.reduce((sum, entry) => {
    return entry.entryType === "Receita" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const totalExpenses = validFinancial.reduce((sum, entry) => {
    return entry.entryType === "Despesa" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const totalBalance = totalRevenue - totalExpenses;

  const completedOrders = sales.filter((sale) => {
    return (sale.status || "").toLowerCase() === "concluído";
  }).length;

  if (revenueEl) {
    revenueEl.textContent = formatReportsCurrency(totalRevenue);
  }

  if (balanceEl) {
    balanceEl.textContent = formatReportsCurrency(totalBalance);
  }

  if (completedOrdersEl) {
    completedOrdersEl.textContent = completedOrders;
  }
}

function renderReportsAvailableSummaries() {
  const clients = getClientsData();
  const products = getProductsData();
  const sales = getFilteredReportsSales();
  const financial = getFilteredReportsFinancial();

  const salesEl = document.getElementById("reports-available-sales");
  const clientsEl = document.getElementById("reports-available-clients");
  const financialEl = document.getElementById("reports-available-financial");
  const productsEl = document.getElementById("reports-available-products");

  const completedSales = sales.filter((sale) => {
    return (sale.status || "").toLowerCase() === "concluído";
  }).length;

  const activeClients = clients.filter((client) => {
    return (client.status || "").toLowerCase() === "ativo";
  }).length;

  const inactiveClients = clients.filter((client) => {
    return (client.status || "").toLowerCase() === "inativo";
  }).length;

  const validFinancial = financial.filter((entry) => {
    return (entry.status || "").toLowerCase() !== "cancelado";
  });

  const totalRevenue = validFinancial.reduce((sum, entry) => {
    return entry.entryType === "Receita" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const totalExpenses = validFinancial.reduce((sum, entry) => {
    return entry.entryType === "Despesa" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const lowStockProducts = products.filter((product) => {
    return (product.status || "").toLowerCase() === "baixo estoque";
  }).length;

  if (salesEl) {
    salesEl.textContent = `${sales.length} pedidos registrados, ${completedSales} concluídos.`;
  }

  if (clientsEl) {
    clientsEl.textContent = `${clients.length} clientes cadastrados, ${activeClients} ativos e ${inactiveClients} inativos.`;
  }

  if (financialEl) {
    financialEl.textContent = `${formatReportsCurrency(totalRevenue)} em receitas e ${formatReportsCurrency(totalExpenses)} em despesas.`;
  }

  if (productsEl) {
    productsEl.textContent = `${products.length} itens cadastrados, ${lowStockProducts} com baixo estoque.`;
  }
}

function renderReportsAnalytics() {
  const sales = getFilteredReportsSales();
  const financial = getFilteredReportsFinancial();
  const activeModules = getActiveModulesData();

  const averageTicketEl = document.getElementById("reports-average-ticket");
  const conversionEl = document.getElementById("reports-sales-conversion");
  const pendingEl = document.getElementById("reports-financial-pending");
  const activeModulesEl = document.getElementById("reports-active-modules");

  const totalSales = sales.length;

  const totalSalesValue = sales.reduce((sum, sale) => {
    return sum + (Number(sale.totalValue) || 0);
  }, 0);

  const averageTicket = totalSales > 0 ? totalSalesValue / totalSales : 0;

  const convertedSales = sales.filter((sale) => {
    const status = (sale.status || "").toLowerCase();
    return status === "aprovado" || status === "faturado" || status === "concluído";
  }).length;

  const conversionRate = totalSales > 0 ? Math.round((convertedSales / totalSales) * 100) : 0;

  const pendingFinancial = financial.filter((entry) => {
    return (entry.status || "").toLowerCase() === "pendente";
  }).length;

  const activeModulesCount = activeModules.filter((module) => module.active).length;

  if (averageTicketEl) {
    averageTicketEl.textContent = `${formatReportsCurrency(averageTicket)} por pedido registrado.`;
  }

  if (conversionEl) {
    conversionEl.textContent = `${conversionRate}% dos pedidos avançaram para aprovação.`;
  }

  if (pendingEl) {
    pendingEl.textContent = `${pendingFinancial} lançamentos com status pendente.`;
  }

  if (activeModulesEl) {
    activeModulesEl.textContent = `${activeModulesCount} módulos ativos no ambiente atual.`;
  }
}

function renderReportsTable() {
  const tbody = document.getElementById("reports-table-body");

  if (!tbody) {
    return;
  }

  const financial = [...getFilteredReportsFinancial()].sort((a, b) => {
    return new Date(b.createdAt || b.entryDate || 0) - new Date(a.createdAt || a.entryDate || 0);
  });

  tbody.innerHTML = "";

  if (!financial.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6">
          <div class="empty-state">
            <h3>Nenhum lançamento encontrado</h3>
            <p>Não há dados financeiros para exibir no relatório.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  financial.slice(0, 10).forEach((entry) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${entry.code || "—"}</td>
      <td>${entry.entryType || "—"}</td>
      <td>${entry.category || "—"}</td>
      <td>${entry.description || "—"}</td>
      <td>${formatReportsCurrency(entry.amount)}</td>
      <td><span class="${getReportsBadgeClass(entry.status)}">${entry.status || "—"}</span></td>
    `;

    tbody.appendChild(row);
  });
}

function filterReportBlocks(searchTerm) {
  const items = document.querySelectorAll(".option-item");

  if (!items.length) {
    return;
  }

  items.forEach((item) => {
    const text = item.textContent.toLowerCase();
    const matches = !searchTerm || text.includes(searchTerm);
    item.style.display = matches ? "" : "none";
  });
}

function bindReportsSearch() {
  const searchInput = document.getElementById("reports-search");

  if (!searchInput) {
    return;
  }

  searchInput.addEventListener("input", () => {
    const searchTerm = searchInput.value.trim().toLowerCase();
    filterReportBlocks(searchTerm);
  });
}

function exportReportsData() {
  const sales = getFilteredReportsSales();
  const financial = getFilteredReportsFinancial();
  const clients = getClientsData();
  const products = getProductsData();
  const activeModules = getActiveModulesData();

  const validFinancial = financial.filter(
    (entry) => (entry.status || "").toLowerCase() !== "cancelado"
  );

  const totalRevenue = validFinancial.reduce((sum, entry) => {
    return entry.entryType === "Receita" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const totalExpenses = validFinancial.reduce((sum, entry) => {
    return entry.entryType === "Despesa" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const totalBalance = totalRevenue - totalExpenses;

  const completedOrders = sales.filter((sale) => {
    return (sale.status || "").toLowerCase() === "concluído";
  }).length;

  const totalSalesValue = sales.reduce((sum, sale) => {
    return sum + (Number(sale.totalValue) || 0);
  }, 0);

  const averageTicket = sales.length > 0 ? totalSalesValue / sales.length : 0;

  const convertedSales = sales.filter((sale) => {
    const status = (sale.status || "").toLowerCase();
    return status === "aprovado" || status === "faturado" || status === "concluído";
  }).length;

  const conversionRate = sales.length > 0 ? Math.round((convertedSales / sales.length) * 100) : 0;

  const pendingFinancial = financial.filter((entry) => {
    return (entry.status || "").toLowerCase() === "pendente";
  }).length;

  const activeModulesCount = activeModules.filter((module) => module.active).length;

  const lines = [
    ["Indicador", "Valor"],
    ["Receita total", totalRevenue],
    ["Despesa total", totalExpenses],
    ["Saldo consolidado", totalBalance],
    ["Pedidos concluídos", completedOrders],
    ["Ticket médio", averageTicket],
    ["Conversão comercial (%)", conversionRate],
    ["Pendências financeiras", pendingFinancial],
    ["Clientes cadastrados", clients.length],
    ["Itens cadastrados", products.length],
    ["Pedidos registrados", sales.length],
    ["Módulos ativos", activeModulesCount],
    [],
    ["Últimos lançamentos financeiros"],
    ["Código", "Tipo", "Categoria", "Descrição", "Valor", "Status"]
  ];

  financial.slice(0, 10).forEach((entry) => {
    lines.push([
      entry.code || "",
      entry.entryType || "",
      entry.category || "",
      entry.description || "",
      entry.amount || 0,
      entry.status || ""
    ]);
  });

  const csvContent = lines
    .map((row) => row.map((cell) => `"${String(cell ?? "").replace(/"/g, '""')}"`).join(","))
    .join("\n");

  const bom = "\uFEFF";
  const blob = new Blob([bom, csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);

  const link = document.createElement("a");
  link.href = url;
  link.download = "relatorio-ardetho.csv";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  URL.revokeObjectURL(url);
}

function bindReportsActions() {
  const periodField = document.getElementById("reports-period-filter");
  const exportButton = document.getElementById("reports-export-button");

  if (periodField) {
    periodField.addEventListener("change", () => {
      renderReportsSummaryCards();
      renderReportsAvailableSummaries();
      renderReportsAnalytics();
      renderReportsTable();
    });
  }

  if (exportButton) {
    exportButton.addEventListener("click", (event) => {
      event.preventDefault();
      exportReportsData();
    });
  }
}

function initializeReportsPage() {
  const reportsPage = document.body.dataset.page === "reports";

  if (!reportsPage) {
    return;
  }

  renderReportsUser();
  renderReportsSummaryCards();
  renderReportsAvailableSummaries();
  renderReportsAnalytics();
  renderReportsTable();
  bindReportsSearch();
  bindReportsActions();
}

document.addEventListener("DOMContentLoaded", initializeReportsPage);