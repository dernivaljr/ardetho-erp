function getFinancialData() {
  return getAppSection("financial", appData.financial);
}

function saveFinancialData(entries) {
  return updateAppData("financial", entries);
}

function renderFinancialUser() {
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

function getFinancialBadgeClass(status) {
  const normalizedStatus = (status || "").toLowerCase();

  if (normalizedStatus.includes("cancelado")) return "badge-danger";
  if (normalizedStatus.includes("pendente")) return "badge-warning";
  if (normalizedStatus.includes("recebido")) return "badge-success";
  if (normalizedStatus.includes("pago")) return "badge-info";

  return "badge-info";
}

function formatFinancialCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(Number(value) || 0);
}

function formatFinancialDate(dateString) {
  if (!dateString) {
    return "—";
  }

  const [year, month, day] = dateString.split("-");
  if (!year || !month || !day) {
    return dateString;
  }

  return `${day}/${month}/${year}`;
}

function isPendingFinancialEntry(entry) {
  return (entry.status || "").toLowerCase() === "pendente";
}

function isCancelledFinancialEntry(entry) {
  return (entry.status || "").toLowerCase() === "cancelado";
}

function getUpcomingDueValue(entries) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const nextSevenDays = new Date(today);
  nextSevenDays.setDate(today.getDate() + 7);

  return entries.reduce((sum, entry) => {
    if (isCancelledFinancialEntry(entry) || !isPendingFinancialEntry(entry)) {
      return sum;
    }

    if (!entry.dueDate) {
      return sum;
    }

    const dueDate = new Date(`${entry.dueDate}T00:00:00`);

    if (dueDate >= today && dueDate <= nextSevenDays) {
      return sum + (Number(entry.amount) || 0);
    }

    return sum;
  }, 0);
}

function renderFinancialSummary(entries) {
  const currentBalanceEl = document.getElementById("financial-current-balance");
  const accountsReceivableEl = document.getElementById("financial-accounts-receivable");
  const accountsPayableEl = document.getElementById("financial-accounts-payable");
  const upcomingDueEl = document.getElementById("financial-upcoming-due");

  const validEntries = entries.filter((entry) => !isCancelledFinancialEntry(entry));

  const totalReceitas = validEntries.reduce((sum, entry) => {
    return entry.entryType === "Receita" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const totalDespesas = validEntries.reduce((sum, entry) => {
    return entry.entryType === "Despesa" ? sum + (Number(entry.amount) || 0) : sum;
  }, 0);

  const accountsReceivable = validEntries.reduce((sum, entry) => {
    return entry.entryType === "Receita" && isPendingFinancialEntry(entry)
      ? sum + (Number(entry.amount) || 0)
      : sum;
  }, 0);

  const accountsPayable = validEntries.reduce((sum, entry) => {
    return entry.entryType === "Despesa" && isPendingFinancialEntry(entry)
      ? sum + (Number(entry.amount) || 0)
      : sum;
  }, 0);

  const currentBalance = totalReceitas - totalDespesas;
  const upcomingDue = getUpcomingDueValue(validEntries);

  if (currentBalanceEl) {
    currentBalanceEl.textContent = formatFinancialCurrency(currentBalance);
  }

  if (accountsReceivableEl) {
    accountsReceivableEl.textContent = formatFinancialCurrency(accountsReceivable);
  }

  if (accountsPayableEl) {
    accountsPayableEl.textContent = formatFinancialCurrency(accountsPayable);
  }

  if (upcomingDueEl) {
    upcomingDueEl.textContent = formatFinancialCurrency(upcomingDue);
  }
}

function renderFinancialTable(entries) {
  const tbody = document.getElementById("financial-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  if (!entries.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="9">
          <div class="empty-state">
            <h3>Nenhum lançamento encontrado</h3>
            <p>Não há registros compatíveis com os filtros atuais.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  entries.forEach((entry) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${entry.code || "—"}</td>
      <td>${entry.entryType || "—"}</td>
      <td>${entry.clientName || "—"}</td>
      <td>${entry.category || "—"}</td>
      <td>${entry.description || "—"}</td>
      <td>${formatFinancialDate(entry.dueDate)}</td>
      <td>${formatFinancialCurrency(entry.amount)}</td>
      <td><span class="${getFinancialBadgeClass(entry.status)}">${entry.status || "—"}</span></td>
      <td>
        <div class="action-group">
          <a href="financial-form.html?id=${entry.id}" class="btn-secondary">Editar</a>
          <a href="#" class="btn-danger" data-action="delete-financial" data-financial-id="${entry.id}">Excluir</a>
        </div>
      </td>
    `;

    tbody.appendChild(row);
  });
}

function filterFinancialEntries(entries, searchTerm, typeFilter, statusFilter, clientFilter) {
  return entries.filter((entry) => {
    const code = (entry.code || "").toLowerCase();
    const type = (entry.entryType || "").toLowerCase();
    const clientName = (entry.clientName || "").toLowerCase();
    const category = (entry.category || "").toLowerCase();
    const description = (entry.description || "").toLowerCase();
    const status = (entry.status || "").toLowerCase();

    const matchesSearch =
      !searchTerm ||
      code.includes(searchTerm) ||
      clientName.includes(searchTerm) ||
      category.includes(searchTerm) ||
      description.includes(searchTerm);

    const matchesType =
      !typeFilter ||
      typeFilter === "todos" ||
      type === typeFilter;

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      status === statusFilter;

    const matchesClient =
      !clientFilter ||
      clientFilter === "todos" ||
      clientName === clientFilter;

    return matchesSearch && matchesType && matchesStatus && matchesClient;
  });
}

function populateFinancialClientFilter() {
  const clientSelect = document.getElementById("financial-client-filter");

  if (!clientSelect) {
    return;
  }

  const currentValue = clientSelect.value || "todos";
  const entries = getFinancialData();

  const uniqueClients = [...new Set(
    entries
      .map((entry) => (entry.clientName || "").trim())
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

function getFilteredFinancialEntries() {
  const entries = getFinancialData();

  const searchInput = document.getElementById("financial-search");
  const typeSelect = document.getElementById("financial-type-filter");
  const statusSelect = document.getElementById("financial-status-filter");
  const clientSelect = document.getElementById("financial-client-filter");

  const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
  const typeFilter = typeSelect ? typeSelect.value.toLowerCase() : "";
  const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";
  const clientFilter = clientSelect ? clientSelect.value.toLowerCase() : "";

  return filterFinancialEntries(
    entries,
    searchTerm,
    typeFilter,
    statusFilter,
    clientFilter
  );
}

function refreshFinancialTable() {
  const allEntries = getFinancialData();

  populateFinancialClientFilter();
  renderFinancialSummary(allEntries);
  renderFinancialTable(getFilteredFinancialEntries());
}

function bindFinancialFilters() {
  const searchInput = document.getElementById("financial-search");
  const typeSelect = document.getElementById("financial-type-filter");
  const statusSelect = document.getElementById("financial-status-filter");
  const clientSelect = document.getElementById("financial-client-filter");

  if (searchInput) {
    searchInput.addEventListener("input", refreshFinancialTable);
  }

  if (typeSelect) {
    typeSelect.addEventListener("change", refreshFinancialTable);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", refreshFinancialTable);
  }

  if (clientSelect) {
    clientSelect.addEventListener("change", refreshFinancialTable);
  }
}

function deleteFinancialEntry(entryId) {
  const entries = getFinancialData();
  const updatedEntries = entries.filter((entry) => entry.id !== entryId);

  saveFinancialData(updatedEntries);
  refreshFinancialTable();
}

function bindFinancialActions() {
  document.addEventListener("click", (event) => {
    const deleteButton = event.target.closest("[data-action='delete-financial']");

    if (!deleteButton) {
      return;
    }

    event.preventDefault();

    const entryId = deleteButton.dataset.financialId;

    if (!entryId) {
      return;
    }

    const confirmed = window.confirm("Deseja realmente excluir este lançamento?");

    if (!confirmed) {
      return;
    }

    deleteFinancialEntry(entryId);
  });
}

function initializeFinancialPage() {
  const financialPage = document.body.dataset.page === "financial";

  if (!financialPage) {
    return;
  }

  renderFinancialUser();
  refreshFinancialTable();
  bindFinancialFilters();
  bindFinancialActions();
}

document.addEventListener("DOMContentLoaded", initializeFinancialPage);