function getProductsData() {
  return getAppSection("products", appData.products);
}

function renderProductsUser() {
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

function getProductBadgeClass(status) {
  const normalizedStatus = status.toLowerCase();

  if (normalizedStatus.includes("disponível")) return "badge-success";
  if (normalizedStatus.includes("ativo")) return "badge-info";
  if (normalizedStatus.includes("baixo estoque")) return "badge-warning";
  if (normalizedStatus.includes("indisponível")) return "badge-danger";

  return "badge-neutral";
}

function formatProductPrice(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(value);
}

function renderProductsTable(products) {
  const tbody = document.getElementById("products-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  products.forEach((product) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${product.id}</td>
      <td>${product.name}</td>
      <td>${product.category}</td>
      <td>${product.type}</td>
      <td>${formatProductPrice(product.price)}</td>
      <td>${product.stock === null ? "—" : product.stock}</td>
      <td><span class="${getProductBadgeClass(product.status)}">${product.status}</span></td>
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

function filterProducts(products, searchTerm, categoryFilter, statusFilter) {
  return products.filter((product) => {
    const matchesSearch =
      !searchTerm ||
      product.name.toLowerCase().includes(searchTerm) ||
      product.category.toLowerCase().includes(searchTerm) ||
      product.id.toLowerCase().includes(searchTerm);

    const matchesCategory =
      !categoryFilter ||
      categoryFilter === "todas" ||
      product.category.toLowerCase() === categoryFilter;

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      product.status.toLowerCase() === statusFilter;

    return matchesSearch && matchesCategory && matchesStatus;
  });
}

function bindProductsFilters() {
  const searchInput = document.getElementById("products-search");
  const categorySelect = document.getElementById("products-category-filter");
  const statusSelect = document.getElementById("products-status-filter");

  const applyFilters = () => {
    const products = getProductsData();

    const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
    const categoryFilter = categorySelect ? categorySelect.value.toLowerCase() : "";
    const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";

    const filteredProducts = filterProducts(
      products,
      searchTerm,
      categoryFilter,
      statusFilter
    );

    renderProductsTable(filteredProducts);
  };

  if (searchInput) {
    searchInput.addEventListener("input", applyFilters);
  }

  if (categorySelect) {
    categorySelect.addEventListener("change", applyFilters);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", applyFilters);
  }
}

function initializeProductsPage() {
  const productsPage = document.body.dataset.page === "products";

  if (!productsPage) {
    return;
  }

  renderProductsUser();
  renderProductsTable(getProductsData());
  bindProductsFilters();
}

document.addEventListener("DOMContentLoaded", initializeProductsPage);