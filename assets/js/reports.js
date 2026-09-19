function bindReportsPhpActions() {
  const periodField = document.getElementById("reports-period-filter");
  const periodForm = document.querySelector("[data-reports-period-form]");

  if (periodField && periodForm) {
    periodField.addEventListener("change", () => {
      periodForm.submit();
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.body.dataset.page === "reports") {
    bindReportsPhpActions();
  }
});
