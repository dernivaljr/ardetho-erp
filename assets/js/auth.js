const loginForm = document.getElementById("loginForm");
const loginError = document.getElementById("loginError");

if (loginForm) {
  loginForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    const validEmail = "admin@ardetho.com";
    const validPassword = "123456";

    if (email === validEmail && password === validPassword) {
      localStorage.setItem(
        "ardethoUser",
        JSON.stringify({
          name: "Admin User",
          email: validEmail,
          role: "Administrator"
        })
      );

      window.location.href = "dashboard.html";
    } else {
      loginError.classList.remove("hidden");
    }
  });
}