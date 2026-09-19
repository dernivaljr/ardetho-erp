function bindPhpProductStatusConfirmation() {
  document.addEventListener("submit", (event) => {
    const form = event.target.closest("[data-confirm-product-status]");

    if (!form) {
      return;
    }

    const action = form.dataset.confirmProductStatus === "ativar" ? "ativar" : "desativar";
    const message =
      action === "ativar"
        ? "Deseja ativar este item?"
        : "Deseja desativar este item?";

    if (!window.confirm(message)) {
      event.preventDefault();
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "products") {
    bindPhpProductStatusConfirmation();
  }
});
