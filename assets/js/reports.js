function getReportsData() {
  return getAppSection("reports", appData.reports);
}

function renderReportsUser() {
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

function getReportsBadgeClass(status) {
  const normalizedStatus = status.toLowerCase();

  if (normalizedStatus.includes("exportado")) return "badge-success";
  if (normalizedStatus.includes("gerado")) return "badge-info";
  if (normalizedStatus.includes("pendente")) return "badge-warning";

  return "badge-neutral";
}

function renderReportsTable(reports) {
  const tbody = document.getElementById("reports-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  reports.forEach((report) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${report.name}</td>
      <td>${report.category}</td>
      <td>${report.period}</td>
      <td>${report.generatedBy}</td>
      <td>${report.date}</td>
      <td><span class="${getReportsBadgeClass(report.status)}">${report.status}</span></td>
    `;

    tbody.appendChild(row);
  });
}

function filterReports(reports, searchTerm) {
  return reports.filter((report) => {
    return (
      !searchTerm ||
      report.name.toLowerCase().includes(searchTerm) ||
      report.category.toLowerCase().includes(searchTerm) ||
      report.period.toLowerCase().includes(searchTerm) ||
      report.generatedBy.toLowerCase().includes(searchTerm)
    );
  });
}

function bindReportsFilters() {
  const searchInput = document.getElementById("reports-search");

  const applyFilters = () => {
    const reports = getReportsData();
    const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";

    const filteredReports = filterReports(reports, searchTerm);
    renderReportsTable(filteredReports);
  };

  if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
  }
}

function initializeReportsPage() {
  const reportsPage = document.body.dataset.page === "reports";

  if (!reportsPage) {
    return;
  }

  renderReportsUser();
  renderReportsTable(getReportsData());
  bindReportsFilters();
}

document.addEventListener("DOMContentLoaded", initializeReportsPage);