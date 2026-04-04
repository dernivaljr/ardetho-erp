function getClientsData() {
  return getAppSection("clients", appData.clients);
}

function renderClientsUser() {
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

function getClientBadgeClass(status) {
  const normalizedStatus = status.toLowerCase();

  if (normalizedStatus.includes("ativo")) return "badge-success";
  if (normalizedStatus.includes("análise")) return "badge-info";
  if (normalizedStatus.includes("pendente")) return "badge-warning";
  if (normalizedStatus.includes("inativo")) return "badge-neutral";

  return "badge-info";
}

function renderClientsTable(clients) {
  const tbody = document.getElementById("clients-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  clients.forEach((client) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${client.name}</td>
      <td>${client.company}</td>
      <td>${client.email}</td>
      <td>${client.phone}</td>
      <td>${client.city}</td>
      <td><span class="${getClientBadgeClass(client.status)}">${client.status}</span></td>
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

function filterClients(clients, searchTerm, statusFilter, cityFilter) {
  return clients.filter((client) => {
    const matchesSearch =
      !searchTerm ||
      client.name.toLowerCase().includes(searchTerm) ||
      client.company.toLowerCase().includes(searchTerm) ||
      client.email.toLowerCase().includes(searchTerm);

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      client.status.toLowerCase() === statusFilter;

    const matchesCity =
      !cityFilter ||
      cityFilter === "todas" ||
      client.city.toLowerCase() === cityFilter;

    return matchesSearch && matchesStatus && matchesCity;
  });
}

function bindClientsFilters() {
  const searchInput = document.getElementById("clients-search");
  const statusSelect = document.getElementById("clients-status-filter");
  const citySelect = document.getElementById("clients-city-filter");

  const applyFilters = () => {
    const clients = getClientsData();

    const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
    const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";
    const cityFilter = citySelect ? citySelect.value.toLowerCase() : "";

    const filteredClients = filterClients(clients, searchTerm, statusFilter, cityFilter);
    renderClientsTable(filteredClients);
  };

  if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", applyFilters);
  }

  if (citySelect) {
    citySelect.addEventListener("change", applyFilters);
  }
}

function initializeClientsPage() {
  const clientsPage = document.body.dataset.page === "clients";

  if (!clientsPage) {
    return;
  }

  renderClientsUser();
  renderClientsTable(getClientsData());
  bindClientsFilters();
}

document.addEventListener("DOMContentLoaded", initializeClientsPage);