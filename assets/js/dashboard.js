const SETTINGS_STORAGE_KEY = "ardetho_settings";

const DEFAULT_SETTINGS = {
  themeLight: true,
  sidebarCompact: false,
  dashboardShortcuts: true,
  alertsExpiration: true,
  alertsOrders: true,
  dailySummary: false,
  language: "Português (Brasil)",
  dateFormat: "DD/MM/AAAA",
  timezone: "America/Sao_Paulo",
  mainModule: "Dashboard",
  priorityModule: "Financeiro",
  startupView: "Resumo executivo"
};

function getDashboardSettings() {
  const stored = storage.get(SETTINGS_STORAGE_KEY, null);
  return stored ? { ...DEFAULT_SETTINGS, ...stored } : { ...DEFAULT_SETTINGS };
}

function renderDashboardUser() {
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

function formatDashboardCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(Number(value) || 0);
}

function getDashboardBadgeClass(status) {
  const normalizedStatus = (status || "").toLowerCase();

  if (normalizedStatus.includes("cancelado")) return "badge-danger";
  if (normalizedStatus.includes("pendente")) return "badge-warning";
  if (normalizedStatus.includes("em análise")) return "badge-info";
  if (normalizedStatus.includes("concluído")) return "badge-success";
  if (normalizedStatus.includes("aprovado")) return "badge-success";
  if (normalizedStatus.includes("recebido")) return "badge-success";
  if (normalizedStatus.includes("pago")) return "badge-info";

  return "badge-info";
}

function formatDashboardDate(dateString) {
  if (!dateString) {
    return "—";
  }

  const [year, month, day] = dateString.split("-");
  if (!year || !month || !day) {
    return dateString;
  }

  return `${day}/${month}/${year}`;
}

function renderDashboardMetrics() {
  const clients = getClientsData();
  const products = getProductsData();
  const sales = getSalesData();
  const financial = getFinancialData();

  const activeClientsEl = document.getElementById("metric-active-clients");
  const registeredProductsEl = document.getElementById("metric-registered-products");
  const monthlySalesEl = document.getElementById("metric-monthly-sales");
  const pendingBillsEl = document.getElementById("metric-pending-bills");

  const activeClients = clients.filter((client) => {
    return (client.status || "").toLowerCase() === "ativo";
  }).length;

  const registeredProducts = products.length;

  const validReceitas = financial.filter((entry) => {
    return entry.entryType === "Receita" && (entry.status || "").toLowerCase() !== "cancelado";
  });

  const monthlySales = validReceitas.reduce((sum, entry) => {
    return sum + (Number(entry.amount) || 0);
  }, 0);

  const pendingBills = financial.filter((entry) => {
    return (entry.status || "").toLowerCase() === "pendente";
  }).length;

  if (activeClientsEl) activeClientsEl.textContent = activeClients;
  if (registeredProductsEl) registeredProductsEl.textContent = registeredProducts;
  if (monthlySalesEl) monthlySalesEl.textContent = formatDashboardCurrency(monthlySales);
  if (pendingBillsEl) pendingBillsEl.textContent = pendingBills;
}

function renderDashboardSummary() {
  const modules = getActiveModulesData();
  const sales = getSalesData();
  const financial = getFinancialData();

  const activeModulesEl = document.getElementById("summary-active-modules");
  const openOrdersEl = document.getElementById("summary-open-orders");
  const expectedIncomeEl = document.getElementById("summary-expected-income");
  const scheduledPaymentsEl = document.getElementById("summary-scheduled-payments");

  const activeModulesCount = modules.filter((module) => module.active).length;

  const openOrders = sales.filter((sale) => {
    const status = (sale.status || "").toLowerCase();
    return status === "em análise" || status === "aprovado" || status === "faturado";
  }).length;

  const expectedIncome = financial.reduce((sum, entry) => {
    return entry.entryType === "Receita" && (entry.status || "").toLowerCase() === "pendente"
      ? sum + (Number(entry.amount) || 0)
      : sum;
  }, 0);

  const scheduledPayments = financial.reduce((sum, entry) => {
    return entry.entryType === "Despesa" && (entry.status || "").toLowerCase() === "pendente"
      ? sum + (Number(entry.amount) || 0)
      : sum;
  }, 0);

  if (activeModulesEl) activeModulesEl.textContent = activeModulesCount;
  if (openOrdersEl) openOrdersEl.textContent = openOrders;
  if (expectedIncomeEl) expectedIncomeEl.textContent = formatDashboardCurrency(expectedIncome);
  if (scheduledPaymentsEl) scheduledPaymentsEl.textContent = formatDashboardCurrency(scheduledPayments);
}

function renderDashboardActivities() {
  const activityList = document.getElementById("activity-list");

  if (!activityList) {
    return;
  }

  const sales = [...getSalesData()].slice(0, 2);
  const clients = [...getClientsData()].slice(0, 1);
  const financial = [...getFinancialData()].filter((entry) => {
    return (entry.status || "").toLowerCase() === "pendente";
  }).slice(0, 1);

  const activities = [];

  if (clients[0]) {
    activities.push({
      type: "info",
      title: "Novo cliente cadastrado",
      description: `${clients[0].companyName || clients[0].fullName || "Cliente"} adicionado ao sistema.`,
      time: "Recente"
    });
  }

  sales.forEach((sale) => {
    activities.push({
      type: "success",
      title: "Pedido atualizado",
      description: `${sale.code || "Pedido"} com status ${sale.status || "atualizado"}.`,
      time: "Recente"
    });
  });

  if (financial[0]) {
    activities.push({
      type: "warning",
      title: "Lançamento pendente",
      description: `${financial[0].description || "Lançamento"} com vencimento em ${formatDashboardDate(financial[0].dueDate)}.`,
      time: "Recente"
    });
  }

  activityList.innerHTML = "";

  activities.slice(0, 4).forEach((activity) => {
    const item = document.createElement("div");
    item.className = "activity-item";

    item.innerHTML = `
      <div class="activity-dot ${activity.type}"></div>
      <div class="activity-content">
        <strong>${activity.title}</strong>
        <p>${activity.description}</p>
      </div>
      <span class="activity-time">${activity.time}</span>
    `;

    activityList.appendChild(item);
  });
}

function renderDashboardStatus() {
  const statusList = document.getElementById("status-list");

  if (!statusList) {
    return;
  }

  const modules = getActiveModulesData();
  const moduleNameMap = {
    clients: "CRM / Clientes",
    products: "Produtos",
    sales: "Vendas",
    financial: "Financeiro",
    reports: "Relatórios",
    inventory: "Estoque avançado",
    hr: "RH",
    schedule: "Agenda"
  };

  statusList.innerHTML = "";

  modules.forEach((module) => {
    const item = document.createElement("div");
    item.className = "status-item";

    item.innerHTML = `
      <span class="status-label">${moduleNameMap[module.slug] || module.slug}</span>
      <span class="${module.active ? "badge-success" : "badge-neutral"}">${module.active ? "Ativo" : "Inativo"}</span>
    `;

    statusList.appendChild(item);
  });
}

function renderDashboardRecentOrders() {
  const tbody = document.getElementById("recent-orders-body");

  if (!tbody) {
    return;
  }

  const sales = [...getSalesData()].sort((a, b) => {
    return new Date(b.saleDate || 0) - new Date(a.saleDate || 0);
  });

  tbody.innerHTML = "";

  if (!sales.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6">Nenhum pedido recente encontrado.</td>
      </tr>
    `;
    return;
  }

  sales.slice(0, 5).forEach((sale) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${sale.code || "—"}</td>
      <td>${sale.clientName || "—"}</td>
      <td>${formatDashboardDate(sale.saleDate)}</td>
      <td>${formatDashboardCurrency(sale.totalValue)}</td>
      <td><span class="${getDashboardBadgeClass(sale.status)}">${sale.status || "—"}</span></td>
      <td>${appData.currentUser.name || "Admin User"}</td>
    `;

    tbody.appendChild(row);
  });
}

function applyDashboardSettings() {
  const settings = getDashboardSettings();

  const sidebar = document.querySelector(".sidebar");
  const shortcuts = document.getElementById("dashboard-shortcuts");
  const metricsSection = document.getElementById("dashboard-metrics-section");
  const analyticsSection = document.getElementById("dashboard-analytics-section");
  const activitySection = document.getElementById("dashboard-activity-section");

  if (sidebar) {
    sidebar.classList.toggle("sidebar-compact", Boolean(settings.sidebarCompact));
  }

  if (shortcuts) {
    shortcuts.style.display = settings.dashboardShortcuts ? "" : "none";
  }

  if (metricsSection) metricsSection.style.order = "";
  if (analyticsSection) analyticsSection.style.order = "";
  if (activitySection) activitySection.style.order = "";

  if (settings.startupView === "Métricas principais") {
    if (metricsSection) metricsSection.style.order = "-2";
    if (analyticsSection) analyticsSection.style.order = "-1";
  }

  if (settings.startupView === "Lista de atividades") {
    if (activitySection) activitySection.style.order = "-2";
    if (metricsSection) metricsSection.style.order = "-1";
  }

  if (settings.startupView === "Resumo executivo") {
    if (analyticsSection) analyticsSection.style.order = "-2";
    if (metricsSection) metricsSection.style.order = "-1";
  }
}

function initializeDashboardPage() {
  const dashboardPage = document.body.dataset.page === "dashboard";

  if (!dashboardPage) {
    return;
  }

  renderDashboardUser();
  renderDashboardMetrics();
  renderDashboardSummary();
  renderDashboardActivities();
  renderDashboardStatus();
  renderDashboardRecentOrders();
  applyDashboardSettings();
}

document.addEventListener("DOMContentLoaded", initializeDashboardPage);