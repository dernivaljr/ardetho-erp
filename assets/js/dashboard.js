function formatCurrencyBR(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(value);
}

function getDashboardData() {
  const storedDashboard = getAppSection("dashboard", appData.dashboard);
  const storedSales = getAppSection("sales", appData.sales);
  return {
    dashboard: storedDashboard,
    sales: storedSales
  };
}

function renderMetricCards(dashboard) {
  const activeClientsEl = document.getElementById("metric-active-clients");
  const registeredProductsEl = document.getElementById("metric-registered-products");
  const monthlySalesEl = document.getElementById("metric-monthly-sales");
  const pendingBillsEl = document.getElementById("metric-pending-bills");

  if (activeClientsEl) {
    activeClientsEl.textContent = dashboard.metrics.activeClients;
  }

  if (registeredProductsEl) {
    registeredProductsEl.textContent = dashboard.metrics.registeredProducts;
  }

  if (monthlySalesEl) {
    monthlySalesEl.textContent = formatCurrencyBR(dashboard.metrics.monthlySales);
  }

  if (pendingBillsEl) {
    pendingBillsEl.textContent = dashboard.metrics.pendingBills;
  }
}

function renderSummary(dashboard) {
  const activeModulesEl = document.getElementById("summary-active-modules");
  const openOrdersEl = document.getElementById("summary-open-orders");
  const expectedIncomeEl = document.getElementById("summary-expected-income");
  const scheduledPaymentsEl = document.getElementById("summary-scheduled-payments");

  if (activeModulesEl) {
    activeModulesEl.textContent = dashboard.summary.activeModules;
  }

  if (openOrdersEl) {
    openOrdersEl.textContent = dashboard.summary.openOrders;
  }

  if (expectedIncomeEl) {
    expectedIncomeEl.textContent = formatCurrencyBR(dashboard.summary.expectedIncome);
  }

  if (scheduledPaymentsEl) {
    scheduledPaymentsEl.textContent = formatCurrencyBR(dashboard.summary.scheduledPayments);
  }
}

function renderChart(dashboard) {
  const chartBarsContainer = document.getElementById("chart-bars");
  const chartLabelsContainer = document.getElementById("chart-labels");

  if (!chartBarsContainer || !chartLabelsContainer) {
    return;
  }

  chartBarsContainer.innerHTML = "";
  chartLabelsContainer.innerHTML = "";

  dashboard.financialPerformance.forEach((value) => {
    const bar = document.createElement("div");
    bar.className = "chart-bar";
    bar.style.height = `${value}%`;
    chartBarsContainer.appendChild(bar);
  });

  dashboard.financialLabels.forEach((label) => {
    const labelEl = document.createElement("span");
    labelEl.textContent = label;
    chartLabelsContainer.appendChild(labelEl);
  });
}

function getStatusBadgeClass(status) {
  const normalizedStatus = status.toLowerCase();

  if (normalizedStatus.includes("ativo")) return "badge-success";
  if (normalizedStatus.includes("sincronizado")) return "badge-info";
  if (normalizedStatus.includes("parcial")) return "badge-warning";
  if (normalizedStatus.includes("inativo")) return "badge-neutral";

  return "badge-info";
}

function renderModuleStatus(dashboard) {
  const statusList = document.getElementById("status-list");

  if (!statusList) {
    return;
  }

  statusList.innerHTML = "";

  dashboard.moduleStatus.forEach((item) => {
    const row = document.createElement("div");
    row.className = "status-item";

    row.innerHTML = `
      <span class="status-label">${item.name}</span>
      <span class="${getStatusBadgeClass(item.status)}">${item.status}</span>
    `;

    statusList.appendChild(row);
  });
}

function getActivityDotClass(type) {
  const allowedTypes = ["info", "success", "warning", "neutral"];
  return allowedTypes.includes(type) ? type : "info";
}

function renderActivities(dashboard) {
  const activityList = document.getElementById("activity-list");

  if (!activityList) {
    return;
  }

  activityList.innerHTML = "";

  dashboard.activities.forEach((activity) => {
    const row = document.createElement("div");
    row.className = "activity-item";

    row.innerHTML = `
      <div class="activity-dot ${getActivityDotClass(activity.type)}"></div>
      <div class="activity-content">
        <strong>${activity.title}</strong>
        <p>${activity.description}</p>
      </div>
      <span class="activity-time">${activity.time}</span>
    `;

    activityList.appendChild(row);
  });
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

function renderRecentOrders(sales) {
  const recentOrdersTableBody = document.getElementById("recent-orders-body");

  if (!recentOrdersTableBody) {
    return;
  }

  recentOrdersTableBody.innerHTML = "";

  sales.slice(0, 5).forEach((order) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${order.id}</td>
      <td>${order.client}</td>
      <td>${order.date}</td>
      <td>${formatCurrencyBR(order.value)}</td>
      <td><span class="${getSalesBadgeClass(order.status)}">${order.status}</span></td>
      <td>${order.owner}</td>
    `;

    recentOrdersTableBody.appendChild(row);
  });
}

function renderCurrentUser() {
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

function initializeDashboard() {
  const dashboardPage = document.body.dataset.page === "dashboard";

  if (!dashboardPage) {
    return;
  }

  const { dashboard, sales } = getDashboardData();

  renderCurrentUser();
  renderMetricCards(dashboard);
  renderSummary(dashboard);
  renderChart(dashboard);
  renderActivities(dashboard);
  renderModuleStatus(dashboard);
  renderRecentOrders(sales);
}

document.addEventListener("DOMContentLoaded", initializeDashboard);