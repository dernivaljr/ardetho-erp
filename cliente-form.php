<?php
$pageTitle = 'Novo Cliente | Ardetho ERP';
$bodyPage = 'client-form';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = false;
$activeNav = 'clients';
$topbarTitle = 'Clientes';
$topbarSubtitle = 'Cadastro completo de cliente';
$scripts = [
    'assets/js/data.js',
    'assets/js/storage.js',
    'assets/js/auth.js',
    'assets/js/layout.js',
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
              <h1 class="page-title">Novo cliente</h1>
              <p class="page-description">
                Cadastre clientes pessoa física ou jurídica com dados completos de contato e endereço.
              </p>
            </div>

            <div class="page-header-actions">
              <a href="clientes.php" class="btn-secondary">Voltar</a>
            </div>
          </div>

          <form id="client-form-page" class="content-stack client-form-layout">
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
                  <select id="client-person-type" class="select-default">
                    <option value="PF">Pessoa Física</option>
                    <option value="PJ">Pessoa Jurídica</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="client-status">Status</label>
                  <select id="client-status" class="select-default">
                    <option value="Ativo">Ativo</option>
                    <option value="Em análise">Em análise</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Inativo">Inativo</option>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card" id="client-section-pf">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados da pessoa física</h2>
                  <p class="page-card-description">Informações principais para cadastro de cliente PF.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group client-form-col-span-2">
                  <label for="client-full-name">Nome completo</label>
                  <input id="client-full-name" class="input-default" type="text" placeholder="Nome completo" />
                </div>

                <div class="form-group">
                  <label for="client-cpf">CPF</label>
                  <input id="client-cpf" class="input-default" type="text" placeholder="000.000.000-00" />
                </div>

                <div class="form-group">
                  <label for="client-rg">RG</label>
                  <input id="client-rg" class="input-default" type="text" placeholder="RG" />
                </div>

                <div class="form-group">
                  <label for="client-birth-date">Data de nascimento</label>
                  <input id="client-birth-date" class="input-default" type="date" />
                </div>
              </div>
            </section>

            <section class="page-card hidden" id="client-section-pj">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados da pessoa jurídica</h2>
                  <p class="page-card-description">Informações principais para cadastro de cliente PJ.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group client-form-col-span-2">
                  <label for="client-company-name">Razão social</label>
                  <input id="client-company-name" class="input-default" type="text" placeholder="Razão social" />
                </div>

                <div class="form-group client-form-col-span-2">
                  <label for="client-trade-name">Nome fantasia</label>
                  <input id="client-trade-name" class="input-default" type="text" placeholder="Nome fantasia" />
                </div>

                <div class="form-group">
                  <label for="client-cnpj">CNPJ</label>
                  <input id="client-cnpj" class="input-default" type="text" placeholder="00.000.000/0000-00" />
                </div>

                <div class="form-group">
                  <label for="client-state-registration">Inscrição Estadual</label>
                  <input id="client-state-registration" class="input-default" type="text"
                    placeholder="Inscrição Estadual" />
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
                  <input id="client-contact-name" class="input-default" type="text" placeholder="Responsável" />
                </div>

                <div class="form-group">
                  <label for="client-main-email">E-mail principal</label>
                  <input id="client-main-email" class="input-default" type="email" placeholder="email@empresa.com" />
                </div>

                <div class="form-group">
                  <label for="client-invoice-email">E-mail para NF</label>
                  <input id="client-invoice-email" class="input-default" type="email"
                    placeholder="fiscal@empresa.com" />
                </div>

                <div class="form-group">
                  <label for="client-phone">Telefone</label>
                  <input id="client-phone" class="input-default" type="text" placeholder="(11) 99999-9999" />
                </div>

                <div class="form-group">
                  <label for="client-whatsapp">WhatsApp</label>
                  <input id="client-whatsapp" class="input-default" type="text" placeholder="(11) 99999-9999" />
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
                  <input id="client-zip-code" class="input-default" type="text" placeholder="00000-000" />
                </div>

                <div class="form-group">
                  <label for="client-street">Logradouro</label>
                  <input id="client-street" class="input-default" type="text" placeholder="Rua / Avenida" />
                </div>

                <div class="form-group">
                  <label for="client-number">Número</label>
                  <input id="client-number" class="input-default" type="text" placeholder="Número" />
                </div>

                <div class="form-group">
                  <label for="client-complement">Complemento</label>
                  <input id="client-complement" class="input-default" type="text" placeholder="Complemento" />
                </div>

                <div class="form-group">
                  <label for="client-district">Bairro</label>
                  <input id="client-district" class="input-default" type="text" placeholder="Bairro" />
                </div>

                <div class="form-group">
                  <label for="client-city">Cidade</label>
                  <input id="client-city" class="input-default" type="text" placeholder="Cidade" />
                </div>

                <div class="form-group">
                  <label for="client-state">Estado</label>
                  <input id="client-state" class="input-default" type="text" placeholder="UF" />
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
                <textarea id="client-notes" class="textarea-default" placeholder="Observações adicionais"></textarea>
              </div>
            </section>

            <p id="client-form-error" class="error-message client-form-error hidden"></p>

            <div class="client-form-actions">
              <a href="clientes.php" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">Salvar cliente</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>