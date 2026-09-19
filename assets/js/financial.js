function bindFinancialPhpActions() {
  document.addEventListener("submit", (event) => {
    const form = event.target.closest("[data-confirm-financial-status='cancelar']");

    if (!form) {
      return;
    }

    const confirmed = window.confirm("Deseja realmente cancelar este lançamento?");

    if (!confirmed) {
      event.preventDefault();
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "financial") {
    bindFinancialPhpActions();
  }
});
