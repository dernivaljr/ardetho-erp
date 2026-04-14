const SETTINGS_STORAGE_KEY = "ardetho_settings";

const DEFAULT_SETTINGS = {
  themeMode: "light",
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

function getDashboardNotificationSettings() {
  try {
    const stored = storage.get(SETTINGS_STORAGE_KEY, null);

    return {
      alertsExpiration: stored?.alertsExpiration ?? true,
      alertsOrders: stored?.alertsOrders ?? true,
      dailySummary: stored?.dailySummary ?? false
    };
  } catch (error) {
    return {
      alertsExpiration: true,
      alertsOrders: true,
      dailySummary: false
    };
  }
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
  const financial = getFinancialData();

  const activeClientsEl = document.getElementById("metric-active-clients");
  const activeClientsTextEl = document.getElementById("metric-active-clients-text");

  const registeredProductsEl = document.getElementById("metric-registered-products");
  const registeredProductsTextEl = document.getElementById("metric-registered-products-text");

  const monthlySalesEl = document.getElementById("metric-monthly-sales");
  const monthlySalesTextEl = document.getElementById("metric-monthly-sales-text");

  const pendingBillsEl = document.getElementById("metric-pending-bills");
  const pendingBillsTextEl = document.getElementById("metric-pending-bills-text");

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

  if (activeClientsTextEl) {
    activeClientsTextEl.textContent = `${activeClients} cliente(s) com status ativo no sistema.`;
  }

  if (registeredProductsTextEl) {
    registeredProductsTextEl.textContent = `${registeredProducts} item(ns) disponíveis na base atual.`;
  }

  if (monthlySalesTextEl) {
    monthlySalesTextEl.textContent = "Receitas registradas no módulo financeiro.";
  }

  if (pendingBillsTextEl) {
    pendingBillsTextEl.textContent = `${pendingBills} lançamento(s) aguardando movimentação.`;
  }
}

function renderDashboardNotificationCards() {
  const settings = getDashboardNotificationSettings();
  const sales = getSalesData();
  const financial = getFinancialData();
  const clients = getClientsData();

  const notificationSection = document.getElementById("dashboard-notification-cards");

  const expirationCard = document.getElementById("dashboard-alert-expiration-card");
  const ordersCard = document.getElementById("dashboard-alert-orders-card");
  const summaryCard = document.getElementById("dashboard-daily-summary-card");

  const expirationValueEl = document.getElementById("dashboard-alert-expiration-value");
  const expirationTextEl = document.getElementById("dashboard-alert-expiration-text");

  const ordersValueEl = document.getElementById("dashboard-alert-orders-value");
  const ordersTextEl = document.getElementById("dashboard-alert-orders-text");

  const summaryValueEl = document.getElementById("dashboard-daily-summary-value");
  const summaryTextEl = document.getElementById("dashboard-daily-summary-text");

  if (expirationCard) {
    expirationCard.style.display = settings.alertsExpiration ? "" : "none";
  }

  if (ordersCard) {
    ordersCard.style.display = settings.alertsOrders ? "" : "none";
  }

  if (summaryCard) {
    summaryCard.style.display = settings.dailySummary ? "" : "none";
  }

  if (settings.alertsExpiration) {
    const pendingFinancial = financial.filter((entry) => {
      return (entry.status || "").toLowerCase() === "pendente";
    });

    if (expirationValueEl) {
      expirationValueEl.textContent = pendingFinancial.length;
    }

    if (expirationTextEl) {
      expirationTextEl.textContent =
        pendingFinancial.length > 0
          ? `${pendingFinancial.length} lançamento(s) com vencimento próximo.`
          : "Nenhum vencimento próximo.";
    }
  }

  if (settings.alertsOrders) {
    const pendingOrders = sales.filter((sale) => {
      const status = (sale.status || "").toLowerCase();
      return status === "em análise" || status === "aprovado" || status === "faturado";
    });

    if (ordersValueEl) {
      ordersValueEl.textContent = pendingOrders.length;
    }

    if (ordersTextEl) {
      ordersTextEl.textContent =
        pendingOrders.length > 0
          ? `${pendingOrders.length} pedido(s) aguardando andamento.`
          : "Nenhum pedido pendente.";
    }
  }

  if (settings.dailySummary) {
    const activeClients = clients.filter((client) => {
      return (client.status || "").toLowerCase() === "ativo";
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

    const balance = totalRevenue - totalExpenses;

    if (summaryValueEl) {
      summaryValueEl.textContent = formatDashboardCurrency(balance);
    }

    if (summaryTextEl) {
      summaryTextEl.textContent = `${activeClients} cliente(s) ativos no ambiente atual.`;
    }
  }

  if (notificationSection) {
    const allDisabled =
      !settings.alertsExpiration &&
      !settings.alertsOrders &&
      !settings.dailySummary;

    notificationSection.style.display = allDisabled ? "none" : "grid";
  }
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

function renderDashboardFinancialChart() {
  const chartBars = document.getElementById("chart-bars");
  const chartLabels = document.getElementById("chart-labels");

  if (!chartBars || !chartLabels) {
    return;
  }

  const financial = getFinancialData()
    .filter((entry) => (entry.status || "").toLowerCase() !== "cancelado")
    .slice(-7);

  chartBars.innerHTML = "";
  chartLabels.innerHTML = "";

  if (!financial.length) {
    chartBars.innerHTML = `<div class="empty-state">Sem dados financeiros</div>`;
    return;
  }

  const values = financial.map((entry) => Number(entry.amount) || 0);
  const maxValue = Math.max(...values, 1);

  financial.forEach((entry, index) => {
    const bar = document.createElement("div");
    bar.className = "chart-bar";
    bar.style.height = `${Math.max((values[index] / maxValue) * 100, 12)}%`;
    chartBars.appendChild(bar);

    const label = document.createElement("span");
    label.textContent = entry.code || `L${index + 1}`;
    chartLabels.appendChild(label);
  });
}

function renderDashboardActivities() {
  const activityList = document.getElementById("activity-list");

  if (!activityList) {
    return;
  }

  const settings = getDashboardNotificationSettings();
  const sales = [...getSalesData()];
  const clients = [...getClientsData()];
  const financial = [...getFinancialData()];

  const urgentActivities = [];
  const normalActivities = [];

  if (settings.alertsExpiration) {
    financial
      .filter((entry) => (entry.status || "").toLowerCase() === "pendente")
      .slice(0, 2)
      .forEach((entry) => {
        urgentActivities.push({
          type: "warning",
          title: "Vencimento próximo",
          description: `${entry.description || "Lançamento"} com previsão para ${formatDashboardDate(entry.dueDate)}.`,
          time: "Recente"
        });
      });
  }

  if (settings.alertsOrders) {
    sales
      .filter((sale) => {
        const status = (sale.status || "").toLowerCase();
        return status === "em análise" || status === "aprovado" || status === "faturado";
      })
      .slice(0, 2)
      .forEach((sale) => {
        normalActivities.push({
          type: "success",
          title: "Pedido em andamento",
          description: `${sale.code || "Pedido"} está com status ${sale.status || "em aberto"}.`,
          time: "Recente"
        });
      });
  }

  const latestClient = clients[0];
  if (latestClient) {
    normalActivities.push({
      type: "info",
      title: "Cliente cadastrado",
      description: `${latestClient.companyName || latestClient.fullName || "Cliente"} foi adicionado ao sistema.`,
      time: "Recente"
    });
  }

  const activities = [...urgentActivities, ...normalActivities].slice(0, 4);

  activityList.innerHTML = "";

  if (!activities.length) {
    activityList.innerHTML = `
      <div class="activity-item">
        <div class="activity-dot neutral"></div>
        <div class="activity-content">
          <strong>Nenhuma atividade recente</strong>
          <p>Não há eventos relevantes para exibir no momento.</p>
        </div>
        <span class="activity-time">Agora</span>
      </div>
    `;
    return;
  }

  activities.forEach((activity) => {
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
    clients: "Clientes",
    products: "Produtos",
    sales: "Vendas",
    financial: "Financeiro",
    reports: "Relatórios",
    inventory: "Estoque avançado",
    hr: "RH",
    schedule: "Agenda"
  };

  statusList.innerHTML = "";

  if (!modules.length) {
    statusList.innerHTML = `
      <div class="status-item">
        <span class="status-label">Nenhum módulo encontrado</span>
        <span class="badge-neutral">Indefinido</span>
      </div>
    `;
    return;
  }

  modules.forEach((module) => {
    const item = document.createElement("div");
    item.className = "status-item";

    const label = moduleNameMap[module.slug] || module.name || module.slug;
    const badgeClass = module.active ? "badge-success" : "badge-neutral";
    const badgeText = module.active ? "Ativo" : "Inativo";

    item.innerHTML = `
      <span class="status-label">${label}</span>
      <span class="${badgeClass}">${badgeText}</span>
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

function reorderDashboardSections() {
  const settings = getDashboardSettings();
  const contentStack = document.querySelector(".content-stack");

  const notificationSection = document.getElementById("dashboard-notification-cards");
  const metricsSection = document.getElementById("dashboard-metrics-section");
  const analyticsSection = document.getElementById("dashboard-analytics-section");
  const activitySection = document.getElementById("dashboard-activity-section");
  const ordersSection = document.getElementById("dashboard-orders-section");

  if (!contentStack) {
    return;
  }

  const sections = {
    notification: notificationSection,
    metrics: metricsSection,
    analytics: analyticsSection,
    activity: activitySection,
    orders: ordersSection
  };

  const orderMap = {
    "Resumo executivo": ["notification", "analytics", "metrics", "activity", "orders"],
    "Métricas principais": ["metrics", "notification", "analytics", "activity", "orders"],
    "Lista de atividades": ["activity", "notification", "metrics", "analytics", "orders"]
  };

  const selectedOrder = orderMap[settings.startupView] || orderMap["Resumo executivo"];

  selectedOrder.forEach((key) => {
    const section = sections[key];
    if (section) {
      contentStack.appendChild(section);
    }
  });
}

function applyDashboardSettings() {
  const settings = getDashboardSettings();

  const sidebar = document.querySelector(".sidebar");
  const shortcuts = document.getElementById("dashboard-shortcuts");

  if (sidebar) {
    sidebar.classList.toggle("sidebar-compact", Boolean(settings.sidebarCompact));
  }

  if (shortcuts) {
    shortcuts.style.display = settings.dashboardShortcuts ? "" : "none";
  }

  reorderDashboardSections();
}

function initializeDashboardPage() {
  const dashboardPage = document.body.dataset.page === "dashboard";

  if (!dashboardPage) {
    return;
  }

  renderDashboardUser();
  renderDashboardMetrics();
  renderDashboardNotificationCards();
  renderDashboardSummary();
  renderDashboardFinancialChart();
  renderDashboardActivities();
  renderDashboardStatus();
  renderDashboardRecentOrders();
  applyDashboardSettings();
}

document.addEventListener("DOMContentLoaded", initializeDashboardPage);