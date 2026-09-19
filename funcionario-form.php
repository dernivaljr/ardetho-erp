<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/FuncionarioController.php';
require __DIR__ . '/includes/view.php';

$controller = new FuncionarioController();
$viewData = $controller->formulario();
$modoEdicao = $viewData['modoEdicao'];
$dados = $viewData['dados'];
$erros = $viewData['erros'];
$csrf = $viewData['csrf'];
$idFuncionario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formAction = $modoEdicao && $idFuncionario ? 'funcionario-form.php?id=' . (int) $idFuncionario : 'funcionario-form.php';
$tituloPagina = $modoEdicao ? 'Editar colaborador' : 'Novo colaborador';
$descricaoPagina = $modoEdicao
    ? 'Atualize os dados cadastrais e organizacionais do colaborador.'
    : 'Preencha os dados para registrar um colaborador no módulo de RH.';

$pageTitle = ($modoEdicao ? 'Editar Colaborador' : 'Novo Colaborador') . ' | RH | Ardetho ERP';
$bodyPage = 'hr-form';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = true;
$activeNav = 'hr';
$topbarTitle = 'RH';
$topbarSubtitle = 'Cadastro e edição de colaborador';
$scripts = [
    'assets/js/layout.js',
];
$isInternal = true;

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>
<section class="app-content">
        <div class="content-container">
          <div class="page-header">
            <div class="page-header-content">
              <h1 class="page-title"><?= e($tituloPagina) ?></h1>
              <p class="page-description">
                <?= e($descricaoPagina) ?>
              </p>
            </div>

            <div class="page-header-actions">
              <a href="rh.php" class="btn-secondary">Voltar</a>
            </div>
          </div>

          <form class="content-stack client-form-layout" method="post" action="<?= e($formAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Informações principais</h2>
                  <p class="page-card-description">Dados cadastrais e organizacionais do colaborador.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="hr-full-name">Nome completo</label>
                  <input id="hr-full-name" name="nome_completo" class="input-default" type="text" placeholder="Nome do colaborador" value="<?= e($dados['nome_completo'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="hr-email">E-mail</label>
                  <input id="hr-email" name="email" class="input-default" type="email" placeholder="email@empresa.com" value="<?= e($dados['email'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="hr-phone">Telefone</label>
                  <input id="hr-phone" name="telefone" class="input-default" type="text" placeholder="(11) 99999-9999" value="<?= e($dados['telefone'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="hr-role">Cargo</label>
                  <input id="hr-role" name="cargo" class="input-default" type="text" placeholder="Cargo do colaborador" value="<?= e($dados['cargo'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="hr-department">Departamento</label>
                  <input id="hr-department" name="departamento" class="input-default" type="text" placeholder="Departamento" value="<?= e($dados['departamento'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="hr-salary">Salário</label>
                  <input id="hr-salary" name="salario" class="input-default" type="text" placeholder="0,00" value="<?= e($dados['salario'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="hr-admission-date">Data de admissão</label>
                  <input id="hr-admission-date" name="data_admissao" class="input-default" type="date" value="<?= e($dados['data_admissao'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="hr-status">Status</label>
                  <select id="hr-status" name="status" class="select-default">
<?php foreach (FuncionarioController::status() as $status): ?>
                    <option value="<?= e($status) ?>"<?= selectedIf($dados['status'] ?? 'Ativo', $status) ?>><?= e($status) ?></option>
<?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group client-form-col-span-2">
                  <label for="hr-notes">Observações</label>
                  <textarea id="hr-notes" name="observacoes" class="textarea-default" placeholder="Observações internas sobre o colaborador"><?= e($dados['observacoes'] ?? '') ?></textarea>
                </div>
              </div>
            </section>

<?php if ($erros): ?>
            <div class="error-message client-form-error" role="alert">
<?php foreach ($erros as $erro): ?>
              <p><?= e($erro) ?></p>
<?php endforeach; ?>
            </div>
<?php endif; ?>

            <div class="client-form-actions">
              <a href="rh.php" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">Salvar colaborador</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
