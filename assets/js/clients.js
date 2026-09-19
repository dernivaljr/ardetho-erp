function bindPhpClientStatusConfirmation() {
  document.addEventListener("submit", (event) => {
    const form = event.target.closest("[data-confirm-client-status]");

    if (!form) {
      return;
    }

    const action = form.dataset.confirmClientStatus === "ativar" ? "ativar" : "desativar";
    const message =
      action === "ativar"
        ? "Deseja ativar este cliente?"
        : "Deseja desativar este cliente?";

    if (!window.confirm(message)) {
      event.preventDefault();
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "clients") {
    bindPhpClientStatusConfirmation();
  }
});
