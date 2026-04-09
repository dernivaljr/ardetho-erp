function getProductsData() {
  return getAppSection("products", appData.products);
}

function saveProductsData(products) {
  return updateAppData("products", products);
}

function renderProductsUser() {
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

function getProductBadgeClass(status) {
  const normalizedStatus = (status || "").toLowerCase();

  if (normalizedStatus.includes("inativo")) return "badge-danger";
  if (normalizedStatus.includes("indisponível")) return "badge-danger";
  if (normalizedStatus.includes("baixo estoque")) return "badge-warning";
  if (normalizedStatus.includes("em análise")) return "badge-info";
  if (normalizedStatus.includes("disponível")) return "badge-success";
  if (normalizedStatus.includes("ativo")) return "badge-success";

  return "badge-info";
}
function renderProductsSummary(products) {
  const totalItemsEl = document.getElementById("products-total-items");
  const criticalStockEl = document.getElementById("products-critical-stock");
  const activeCategoriesEl = document.getElementById("products-active-categories");

  const totalItems = products.length;

  const criticalStock = products.filter((product) => {
    return (
      product.itemType === "Produto" &&
      product.status === "Baixo estoque"
    );
  }).length;

  const activeCategories = new Set(
    products
      .map((product) => product.category)
      .filter((category) => category)
  ).size;

  if (totalItemsEl) {
    totalItemsEl.textContent = totalItems;
  }

  if (criticalStockEl) {
    criticalStockEl.textContent = criticalStock;
  }

  if (activeCategoriesEl) {
    activeCategoriesEl.textContent = activeCategories;
  }
}
function formatProductPrice(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(Number(value) || 0);
}

function getProductDisplayStock(product) {
  if (product.itemType === "Serviço") {
    return "—";
  }

  if (product.stock === null || product.stock === undefined) {
    return "—";
  }

  return product.stock;
}

function renderProductsTable(products) {
  const tbody = document.getElementById("products-table-body");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  if (!products.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8">
          <div class="empty-state">
            <h3>Nenhum item encontrado</h3>
            <p>Não há produtos ou serviços compatíveis com os filtros atuais.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  products.forEach((product) => {
    const row = document.createElement("tr");

    row.innerHTML = `
      <td>${product.itemType || "—"}</td>
      <td>${product.code || "—"}</td>
      <td>${product.name || "—"}</td>
      <td>${product.category || "—"}</td>
      <td>${formatProductPrice(product.price)}</td>
      <td>${getProductDisplayStock(product)}</td>
      <td><span class="${getProductBadgeClass(product.status)}">${product.status || "—"}</span></td>
      <td>
        <div class="action-group">
          <a href="product-form.html?id=${product.id}" class="btn-secondary">Editar</a>
          <a href="#" class="btn-danger" data-action="delete-product" data-product-id="${product.id}">Excluir</a>
        </div>
      </td>
    `;

    tbody.appendChild(row);
  });
}

function filterProducts(products, searchTerm, categoryFilter, statusFilter) {
  return products.filter((product) => {
    const itemType = (product.itemType || "").toLowerCase();
    const code = (product.code || "").toLowerCase();
    const name = (product.name || "").toLowerCase();
    const category = (product.category || "").toLowerCase();
    const status = (product.status || "").toLowerCase();

    const matchesSearch =
      !searchTerm ||
      code.includes(searchTerm) ||
      name.includes(searchTerm) ||
      category.includes(searchTerm) ||
      itemType.includes(searchTerm);

    const matchesCategory =
      !categoryFilter ||
      categoryFilter === "todas" ||
      category === categoryFilter;

    const matchesStatus =
      !statusFilter ||
      statusFilter === "todos" ||
      status === statusFilter;

    return matchesSearch && matchesCategory && matchesStatus;
  });
}

function populateProductCategoryFilter() {
  const categorySelect = document.getElementById("products-category-filter");

  if (!categorySelect) {
    return;
  }

  const currentValue = categorySelect.value || "todas";
  const products = getProductsData();

  const uniqueCategories = [...new Set(
    products
      .map((product) => (product.category || "").trim())
      .filter((category) => category)
      .sort((a, b) => a.localeCompare(b, "pt-BR"))
  )];

  categorySelect.innerHTML = `<option value="todas">Categoria</option>`;

  uniqueCategories.forEach((category) => {
    const option = document.createElement("option");
    option.value = category.toLowerCase();
    option.textContent = category;
    categorySelect.appendChild(option);
  });

  const optionExists = [...categorySelect.options].some(
    (option) => option.value === currentValue
  );

  categorySelect.value = optionExists ? currentValue : "todas";
}

function getFilteredProducts() {
  const products = getProductsData();

  const searchInput = document.getElementById("products-search");
  const categorySelect = document.getElementById("products-category-filter");
  const statusSelect = document.getElementById("products-status-filter");

  const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : "";
  const categoryFilter = categorySelect ? categorySelect.value.toLowerCase() : "";
  const statusFilter = statusSelect ? statusSelect.value.toLowerCase() : "";

  return filterProducts(products, searchTerm, categoryFilter, statusFilter);
}

function refreshProductsTable() {
  const allProducts = getProductsData();

  populateProductCategoryFilter();
  renderProductsSummary(allProducts);
  renderProductsTable(getFilteredProducts());
}

function bindProductsFilters() {
  const searchInput = document.getElementById("products-search");
  const categorySelect = document.getElementById("products-category-filter");
  const statusSelect = document.getElementById("products-status-filter");

  if (searchInput) {
    searchInput.addEventListener("input", refreshProductsTable);
  }

  if (categorySelect) {
    categorySelect.addEventListener("change", refreshProductsTable);
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", refreshProductsTable);
  }
}

function deleteProduct(productId) {
  const products = getProductsData();
  const updatedProducts = products.filter((product) => product.id !== productId);

  saveProductsData(updatedProducts);
  refreshProductsTable();
}

function bindProductActions() {
  document.addEventListener("click", (event) => {
    const deleteButton = event.target.closest("[data-action='delete-product']");

    if (!deleteButton) {
      return;
    }

    event.preventDefault();

    const productId = deleteButton.dataset.productId;

    if (!productId) {
      return;
    }

    const confirmed = window.confirm("Deseja realmente excluir este item?");

    if (!confirmed) {
      return;
    }

    deleteProduct(productId);
  });
}

function initializeProductsPage() {
  const productsPage = document.body.dataset.page === "products";

  if (!productsPage) {
    return;
  }

  renderProductsUser();
  refreshProductsTable();
  bindProductsFilters();
  bindProductActions();
}

document.addEventListener("DOMContentLoaded", initializeProductsPage);