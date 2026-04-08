function getClientsData() {
  return getAppSection("clients", appData.clients);
}

function saveClientsData(clients) {
  return updateAppData("clients", clients);
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
  const normalizedStatus = (status || "").toLowerCase();

  if (normalizedStatus.includes("inativo")) return "badge-neutral";
  if (normalizedStatus.includes("análise")) return "badge-info";
  if (normalizedStatus.includes("pendente")) return "badge-warning";
  if (normalizedStatus.includes("ativo")) return "badge-success";

  return "badge-info";
}

function getClientDisplayType(client) {
  return client.personType || "—";
}

function getClientDisplayName(client) {
  if (client.personType === "PF") {
    return client.fullName || "—";
  }

  return client.companyName || client.tradeName || "—";
}

function getClientDisplayDocument(client) {
  if (client.personType === "PF") {
    return client.cpf || "—";
  }

  return client.cnpj || "—";
}

function getClientDisplayContact(client) {
  if (client.contactName) return client.contactName;
  if (client.mainEmail) return client.mainEmail;
  if (client.phone) return client.phone;
  return "—";
}

function renderClientsTable(clients) {
  const tbody = document.getElementById("clients-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  if (!clients.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7">
          <div class="empty-state">
            <h3>Nenhum cliente encontrado</h3>
            <p>Não há clientes compatíveis com os filtros atuais.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  clients.forEach((client) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${getClientDisplayType(client)}</td>
      <td>${getClientDisplayName(client)}</td>
      <td>${getClientDisplayDocument(client)}</td>
      <td>${getClientDisplayContact(client)}</td>
      <td>${client.city || "—"}</td>
      <td><span class="${getClientBadgeClass(client.status)}">${client.status || "—"}</span></td>
      <td>
        <div class="action-group">
          <a href="client-form.html?id=${client.id}" class="btn-secondary">Editar</a>
          <a href="#" class="btn-danger" data-action="delete-client" data-client-id="${client.id}">Excluir</a>
        </div>
      </td>
    `;

    tbody.appendChild(row);
  });
}

function filterClients(clients, searchTerm, statusFilter, cityFilter) {
  return clients.filter((client) => {
    const type = (getClientDisplayType(client) || "").toLowerCase();
    const name = (getClientDisplayName(client) || "").toLowerCase();
    const documentValue = (getClientDisplayDocument(client) || "").toLowerCase();
    const contact = (getClientDisplayContact(client) || "").toLowerCase();
    const city = (client.city || "").toLowerCase();
    const status = (client.status || "").toLowerCase();

    const matchesSearch =
      !searchTerm ||
      name.includes(searchTerm) ||
      documentValue.includes(searchTerm) ||
      contact.includes(searchTerm) ||
      type.includes(searchTerm);

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      status === statusFilter;

    const matchesCity =
      !cityFilter ||
      cityFilter === "todas" ||
      city === cityFilter;

    return matchesSearch && matchesStatus && matchesCity;
  });
}

function getFilteredClients() {
  const clients = getClientsData();

  const searchInput = document.getElementById("clients-search");
  const statusSelect = document.getElementById("clients-status-filter");
  const citySelect = document.getElementById("clients-city-filter");

  const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
  const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";
  const cityFilter = citySelect ? citySelect.value.toLowerCase() : "";

  return filterClients(clients, searchTerm, statusFilter, cityFilter);
}

function refreshClientsTable() {
  populateCityFilter();
  renderClientsTable(getFilteredClients());
}

function bindClientsFilters() {
  const searchInput = document.getElementById("clients-search");
  const statusSelect = document.getElementById("clients-status-filter");
  const citySelect = document.getElementById("clients-city-filter");

  if (searchInput) {
    searchInput.addEventListener("input", refreshClientsTable);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", refreshClientsTable);
  }

  if (citySelect) {
    citySelect.addEventListener("change", refreshClientsTable);
  }
}

function deleteClient(clientId) {
  const clients = getClientsData();
  const updatedClients = clients.filter((client) => client.id !== clientId);

  saveClientsData(updatedClients);
  refreshClientsTable();
}

function bindClientActions() {
  document.addEventListener("click", (event) => {
    const deleteButton = event.target.closest("[data-action='delete-client']");

    if (!deleteButton) {
      return;
    }

    event.preventDefault();

    const clientId = deleteButton.dataset.clientId;

    if (!clientId) {
      return;
    }

    const confirmed = window.confirm("Deseja realmente excluir este cliente?");

    if (!confirmed) {
      return;
    }

    deleteClient(clientId);
  });
}

function initializeClientsPage() {
  const clientsPage = document.body.dataset.page === "clients";

  if (!clientsPage) {
    return;
  }

  renderClientsUser();
  populateCityFilter();
  refreshClientsTable();
  bindClientsFilters();
  bindClientActions();
}

document.addEventListener("DOMContentLoaded", initializeClientsPage);
function populateCityFilter() {
  const citySelect = document.getElementById("clients-city-filter");

  if (!citySelect) {
    return;
  }

  const currentValue = citySelect.value || "todas";
  const clients = getClientsData();

  const uniqueCities = [...new Set(
    clients
      .map((client) => (client.city || "").trim())
      .filter((city) => city)
      .sort((a, b) => a.localeCompare(b, "pt-BR"))
  )];

  citySelect.innerHTML = `<option value="todas">Cidade</option>`;

  uniqueCities.forEach((city) => {
    const option = document.createElement("option");
    option.value = city.toLowerCase();
    option.textContent = city;
    citySelect.appendChild(option);
  });

  const optionExists = [...citySelect.options].some(
    (option) => option.value === currentValue
  );

  citySelect.value = optionExists ? currentValue : "todas";
}