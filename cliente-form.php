<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/ClienteController.php';
require __DIR__ . '/includes/view.php';

$controller = new ClienteController();
$viewData = $controller->formulario();
$modoEdicao = $viewData['modoEdicao'];
$dados = $viewData['dados'];
$erros = $viewData['erros'];
$csrf = $viewData['csrf'];
$idCliente = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formAction = $modoEdicao && $idCliente ? 'cliente-form.php?id=' . (int) $idCliente : 'cliente-form.php';
$tituloPagina = $modoEdicao ? 'Editar cliente' : 'Novo cliente';
$descricaoPagina = $modoEdicao
    ? 'Atualize os dados cadastrais do cliente selecionado.'
    : 'Cadastre clientes pessoa física ou jurídica com dados completos de contato e endereço.';
$tipoPessoa = $dados['tipo_pessoa'] ?? 'PF';

$pageTitle = ($modoEdicao ? 'Editar Cliente' : 'Novo Cliente') . ' | Ardetho ERP';
$bodyPage = 'client-form';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = false;
$activeNav = 'clients';
$topbarTitle = 'Clientes';
$topbarSubtitle = 'Cadastro completo de cliente';
$scripts = [
    'assets/js/utils.js',
    'assets/js/client-form.js',
    'assets/js/pwa.js'
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
              <a href="clientes.php" class="btn-secondary">Voltar</a>
            </div>
          </div>

          <form id="client-form-page" class="content-stack client-form-layout" method="post" action="<?= e($formAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Tipo e status</h2>
                  <p class="page-card-description">Defina a natureza do cadastro e a situação atual do cliente.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="client-person-type">Tipo de pessoa</label>
                  <select id="client-person-type" name="tipo_pessoa" class="select-default">
                    <option value="PF"<?= selectedIf($tipoPessoa, 'PF') ?>>Pessoa Física</option>
                    <option value="PJ"<?= selectedIf($tipoPessoa, 'PJ') ?>>Pessoa Jurídica</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="client-status">Status</label>
                  <select id="client-status" name="status" class="select-default">
                    <option value="Ativo"<?= selectedIf($dados['status'] ?? '', 'Ativo') ?>>Ativo</option>
                    <option value="Em análise"<?= selectedIf($dados['status'] ?? '', 'Em análise') ?>>Em análise</option>
                    <option value="Pendente"<?= selectedIf($dados['status'] ?? '', 'Pendente') ?>>Pendente</option>
                    <option value="Inativo"<?= selectedIf($dados['status'] ?? '', 'Inativo') ?>>Inativo</option>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card<?= $tipoPessoa === 'PJ' ? ' hidden' : '' ?>" id="client-section-pf">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados da pessoa física</h2>
                  <p class="page-card-description">Informações principais para cadastro de cliente PF.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group client-form-col-span-2">
                  <label for="client-full-name">Nome completo</label>
                  <input id="client-full-name" name="nome" class="input-default" type="text" placeholder="Nome completo" value="<?= e($dados['nome'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-cpf">CPF</label>
                  <input id="client-cpf" name="cpf" class="input-default" type="text" placeholder="000.000.000-00" value="<?= e($dados['cpf'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-rg">RG</label>
                  <input id="client-rg" name="rg" class="input-default" type="text" placeholder="RG" value="<?= e($dados['rg'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-birth-date">Data de nascimento</label>
                  <input id="client-birth-date" name="data_nascimento" class="input-default" type="date" value="<?= e($dados['data_nascimento'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card<?= $tipoPessoa === 'PJ' ? '' : ' hidden' ?>" id="client-section-pj">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados da pessoa jurídica</h2>
                  <p class="page-card-description">Informações principais para cadastro de cliente PJ.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group client-form-col-span-2">
                  <label for="client-company-name">Razão social</label>
                  <input id="client-company-name" name="razao_social" class="input-default" type="text" placeholder="Razão social" value="<?= e($dados['razao_social'] ?? '') ?>" />
                </div>

                <div class="form-group client-form-col-span-2">
                  <label for="client-trade-name">Nome fantasia</label>
                  <input id="client-trade-name" name="nome_fantasia" class="input-default" type="text" placeholder="Nome fantasia" value="<?= e($dados['nome_fantasia'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-cnpj">CNPJ</label>
                  <input id="client-cnpj" name="cnpj" class="input-default" type="text" placeholder="00.000.000/0000-00" value="<?= e($dados['cnpj'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-state-registration">Inscrição Estadual</label>
                  <input id="client-state-registration" name="inscricao_estadual" class="input-default" type="text" placeholder="Inscrição Estadual" value="<?= e($dados['inscricao_estadual'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Contato</h2>
                  <p class="page-card-description">Canais principais para contato e faturamento.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="client-contact-name">Nome do contato / responsável</label>
                  <input id="client-contact-name" name="contato" class="input-default" type="text" placeholder="Responsável" value="<?= e($dados['contato'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-main-email">E-mail principal</label>
                  <input id="client-main-email" name="email" class="input-default" type="email" placeholder="email@empresa.com" value="<?= e($dados['email'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-invoice-email">E-mail para NF</label>
                  <input id="client-invoice-email" name="email_nf" class="input-default" type="email" placeholder="fiscal@empresa.com" value="<?= e($dados['email_nf'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-phone">Telefone</label>
                  <input id="client-phone" name="telefone" class="input-default" type="text" placeholder="(11) 99999-9999" value="<?= e($dados['telefone'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-whatsapp">WhatsApp</label>
                  <input id="client-whatsapp" name="whatsapp" class="input-default" type="text" placeholder="(11) 99999-9999" value="<?= e($dados['whatsapp'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Endereço</h2>
                  <p class="page-card-description">Dados completos para localização e futuro faturamento.</p>
                </div>
              </div>

              <div class="client-form-grid-address">
                <div class="form-group">
                  <label for="client-zip-code">CEP</label>
                  <input id="client-zip-code" name="cep" class="input-default" type="text" placeholder="00000-000" value="<?= e($dados['cep'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-street">Logradouro</label>
                  <input id="client-street" name="logradouro" class="input-default" type="text" placeholder="Rua / Avenida" value="<?= e($dados['logradouro'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-number">Número</label>
                  <input id="client-number" name="numero" class="input-default" type="text" placeholder="Número" value="<?= e($dados['numero'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-complement">Complemento</label>
                  <input id="client-complement" name="complemento" class="input-default" type="text" placeholder="Complemento" value="<?= e($dados['complemento'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-district">Bairro</label>
                  <input id="client-district" name="bairro" class="input-default" type="text" placeholder="Bairro" value="<?= e($dados['bairro'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-city">Cidade</label>
                  <input id="client-city" name="cidade" class="input-default" type="text" placeholder="Cidade" value="<?= e($dados['cidade'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="client-state">Estado</label>
                  <input id="client-state" name="estado" class="input-default" type="text" placeholder="UF" value="<?= e($dados['estado'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Observações</h2>
                  <p class="page-card-description">Informações adicionais úteis para o relacionamento e operação.</p>
                </div>
              </div>

              <div class="form-group">
                <label for="client-notes">Observações internas</label>
                <textarea id="client-notes" name="observacoes" class="textarea-default" placeholder="Observações adicionais"><?= e($dados['observacoes'] ?? '') ?></textarea>
              </div>
            </section>

<?php if ($erros): ?>
            <div id="client-form-error" class="error-message client-form-error" role="alert">
<?php foreach ($erros as $erro): ?>
              <p><?= e($erro) ?></p>
<?php endforeach; ?>
            </div>
<?php else: ?>
            <p id="client-form-error" class="error-message client-form-error hidden"></p>
<?php endif; ?>

            <div class="client-form-actions">
              <a href="clientes.php" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">Salvar cliente</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
