function bindPhpSaleStatusConfirmation() {
  document.addEventListener("submit", (event) => {
    const form = event.target.closest("[data-confirm-sale-status]");

    if (!form) {
      return;
    }

    if (!window.confirm("Deseja cancelar esta venda?")) {
      event.preventDefault();
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "sales") {
    bindPhpSaleStatusConfirmation();
  }
});
