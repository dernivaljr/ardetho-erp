if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("./service-worker.js")
      .then((registration) => {
        console.log("Service Worker registrado:", registration.scope);
      })
      .catch((error) => {
        if (error.name !== "AbortError") {
          console.error("Erro ao registrar Service Worker:", error);
        }
      });
  });
}