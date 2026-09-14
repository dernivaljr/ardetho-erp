<?php
$pageTitle = 'Login | Ardetho ERP';
$stylesheets = ['assets/css/auth.css'];
$scripts = [
    'assets/js/data.js',
    'assets/js/storage.js',
    'assets/js/auth.js',
    'assets/js/pwa.js'
];

require __DIR__ . '/includes/header.php';
?>
<main class="login-page">
    <section class="login-brand-panel">
      <div class="brand-panel-content">
        <a href="index.html" class="brand brand-link">
          <img src="assets/images/ardetho-logo.png" alt="Ardetho ERP" class="brand-logo" />
        </a>
        <span class="auth-badge">Business management platform</span>

        <h1>Control your business with clarity, speed and modular intelligence.</h1>

        <p>
          Access the platform and centralize essential operations in a modern,
          scalable and organized environment.
        </p>

        <ul class="brand-benefits">
          <li>Centralized operational control</li>
          <li>Customizable business modules</li>
          <li>Modern dashboard experience</li>
        </ul>
      </div>
    </section>

    <section class="login-form-panel">
      <div class="login-card">
        <div class="login-card-header">
          <h2>Access System</h2>
          <p>Enter your credentials to continue.</p>
        </div>

        <form id="loginForm" class="login-form">
          <div class="form-group">
            <label for="email">Email</label>
            <input
              class="input-default"
              type="email"
              id="email"
              name="email"
              placeholder="admin@ardetho.com"
            />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              class="input-default"
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
            />
          </div>

          <div class="form-options">
            <label class="remember-option">
              <input type="checkbox" />
              <span>Remember access</span>
            </label>
          </div>

          <button type="submit" class="btn-primary w-full">Login</button>

          <p class="form-demo">
            Demo access: <strong>admin@ardetho.com</strong> / <strong>123456</strong>
          </p>

          <p id="loginError" class="error-message hidden">
            Invalid email or password.
          </p>
        </form>

        <div class="login-footer">
          <a href="index.html" class="btn-secondary">Back to Home</a>
        </div>
      </div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>