<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/database.php';

iniciarSessao();

if (usuarioAutenticado()) {
    header('Location: ' . ((int) (usuarioAtual()['trocar_senha'] ?? 0) === 1 ? 'alterar-senha.php' : 'dashboard.php'));
    exit;
}

$loginError = '';
$emailInformado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailFiltrado = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senhaFiltrada = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $emailInformado = is_string($emailFiltrado) ? strtolower(trim($emailFiltrado)) : '';
    $senhaInformada = is_string($senhaFiltrada) ? $senhaFiltrada : '';

    if ($emailInformado === '' || $senhaInformada === '') {
        $loginError = 'E-mail ou senha invalidos.';
    } else {
        try {
            $usuario = autenticarUsuario(obterConexaoBanco(), $emailInformado, $senhaInformada);

            if ($usuario === null) {
                $loginError = 'E-mail ou senha invalidos.';
            } else {
                registrarUsuarioNaSessao($usuario);

                header('Location: ' . ((int) $usuario['trocar_senha'] === 1 ? 'alterar-senha.php' : 'dashboard.php'));
                exit;
            }
        } catch (Throwable $exception) {
            error_log('Falha no login Ardetho ERP: ' . $exception->getMessage());
            $loginError = 'Nao foi possivel autenticar agora. Verifique o banco de dados e tente novamente.';
        }
    }
}

$pageTitle = 'Login | Ardetho ERP';
$stylesheets = ['assets/css/auth.css'];

require __DIR__ . '/includes/header.php';
?>
<main class="login-page">
    <section class="login-brand-panel">
      <div class="brand-panel-content">
        <div class="brand brand-link">
          <img src="assets/images/ardetho-logo.png" alt="Ardetho ERP" class="brand-logo" />
        </div>
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

        <form id="loginForm" class="login-form" method="post" action="login.php">
          <div class="form-group">
            <label for="email">Email</label>
            <input
              class="input-default"
              type="email"
              id="email"
              name="email"
              value="<?= htmlspecialchars($emailInformado, ENT_QUOTES, 'UTF-8') ?>"
              placeholder="email@empresa.com"
              autocomplete="username"
              required
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
              autocomplete="current-password"
              required
            />
          </div>

          <div class="form-options">
            <label class="remember-option">
              <input type="checkbox" />
              <span>Remember access</span>
            </label>
          </div>

          <button type="submit" class="btn-primary w-full">Login</button>

<?php if ($loginError !== ''): ?>
          <p id="loginError" class="error-message">
            <?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?>
          </p>
<?php endif; ?>
        </form>

      </div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
