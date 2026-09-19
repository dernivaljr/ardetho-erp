<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
exigirAutenticacao();
require __DIR__ . '/controllers/ProdutoController.php';
require __DIR__ . '/includes/view.php';

$controller = new ProdutoController();
$viewData = $controller->formulario();
$modoEdicao = $viewData['modoEdicao'];
$dados = $viewData['dados'];
$erros = $viewData['erros'];
$csrf = $viewData['csrf'];
$idProduto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formAction = $modoEdicao && $idProduto ? 'produto-form.php?id=' . (int) $idProduto : 'produto-form.php';
$tituloPagina = $modoEdicao ? 'Editar item' : 'Novo item';
$descricaoPagina = $modoEdicao
    ? 'Atualize os dados cadastrais do item selecionado.'
    : 'Cadastre produtos e serviços com dados comerciais, operacionais e classificação fiscal.';
$tipoItem = $dados['tipo_item'] ?? 'Produto';
$statusAtual = $dados['status'] ?? 'Ativo';

$pageTitle = ($modoEdicao ? 'Editar Item' : 'Novo Item') . ' | Ardetho ERP';
$bodyPage = 'product-form';
$stylesheets = ['assets/css/pages.css'];
$includeFavicon = false;
$activeNav = 'products';
$topbarTitle = 'Produtos e Serviços';
$topbarSubtitle = 'Cadastro completo de item';
$scripts = [
    'assets/js/layout.js',
    'assets/js/utils.js',
    'assets/js/product-form.js',
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
              <a href="produtos.php" class="btn-secondary">Voltar</a>
            </div>
          </div>

          <form id="product-form-page" class="content-stack client-form-layout" method="post" action="<?= e($formAction) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Tipo e status</h2>
                  <p class="page-card-description">Defina o tipo do item e a situação atual no sistema.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="product-item-type">Tipo do item</label>
                  <select id="product-item-type" name="tipo_item" class="select-default">
                    <option value="Produto"<?= selectedIf($tipoItem, 'Produto') ?>>Produto</option>
                    <option value="Serviço"<?= selectedIf($tipoItem, 'Serviço') ?>>Serviço</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="product-status">Status</label>
                  <select id="product-status" name="status" class="select-default">
<?php foreach (ProdutoController::opcoesStatusFormulario($tipoItem) as $status): ?>
                    <option value="<?= e($status) ?>"<?= selectedIf($statusAtual, $status) ?>><?= e($status) ?></option>
<?php endforeach; ?>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Identificação</h2>
                  <p class="page-card-description">Dados principais de identificação e descrição do item.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="product-code">Código interno</label>
                  <input id="product-code" name="codigo" class="input-default" type="text" placeholder="Ex.: EQP-001" value="<?= e($dados['codigo'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="product-category">Categoria</label>
                  <input id="product-category" name="categoria" class="input-default" type="text" placeholder="Ex.: Equipamentos" value="<?= e($dados['categoria'] ?? '') ?>" />
                </div>

                <div class="form-group client-form-col-span-2">
                  <label for="product-name">Nome do item</label>
                  <input id="product-name" name="nome" class="input-default" type="text" placeholder="Nome do produto ou serviço" value="<?= e($dados['nome'] ?? '') ?>" />
                </div>

                <div class="form-group client-form-col-span-2">
                  <label for="product-description">Descrição</label>
                  <textarea id="product-description" name="descricao" class="textarea-default" placeholder="Descrição resumida do item"><?= e($dados['descricao'] ?? '') ?></textarea>
                </div>
              </div>
            </section>

            <section class="page-card">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Comercial</h2>
                  <p class="page-card-description">Informações comerciais de venda e unidade.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="product-price">Preço</label>
                  <input id="product-price" name="preco" class="input-default" type="text" placeholder="0,00" value="<?= e($dados['preco'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="product-unit">Unidade</label>
                  <select id="product-unit" name="unidade" class="select-default">
                    <option value="">Selecione</option>
                    <option value="un"<?= selectedIf($dados['unidade'] ?? '', 'un') ?>>un</option>
                    <option value="cx"<?= selectedIf($dados['unidade'] ?? '', 'cx') ?>>cx</option>
                    <option value="kit"<?= selectedIf($dados['unidade'] ?? '', 'kit') ?>>kit</option>
                    <option value="serviço"<?= selectedIf($dados['unidade'] ?? '', 'serviço') ?>>serviço</option>
                    <option value="hora"<?= selectedIf($dados['unidade'] ?? '', 'hora') ?>>hora</option>
                  </select>
                </div>
              </div>
            </section>

            <section class="page-card<?= $tipoItem === 'Serviço' ? ' hidden' : '' ?>" id="product-section-product">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados do produto</h2>
                  <p class="page-card-description">Informações de estoque, fornecedor e classificação fiscal.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="product-stock">Estoque atual</label>
                  <input id="product-stock" name="estoque" class="input-default" type="number" min="0" step="0.001" placeholder="0" value="<?= e($dados['estoque'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="product-minimum-stock">Estoque mínimo</label>
                  <input id="product-minimum-stock" name="estoque_minimo" class="input-default" type="number" min="0" step="0.001" placeholder="0" value="<?= e($dados['estoque_minimo'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="product-brand">Marca</label>
                  <input id="product-brand" name="marca" class="input-default" type="text" placeholder="Marca do produto" value="<?= e($dados['marca'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="product-supplier">Fornecedor</label>
                  <input id="product-supplier" name="fornecedor" class="input-default" type="text" placeholder="Nome do fornecedor" value="<?= e($dados['fornecedor'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="product-ncm">NCM</label>
                  <input id="product-ncm" name="ncm" class="input-default" type="text" placeholder="0000.00.00" value="<?= e($dados['ncm'] ?? '') ?>" />
                </div>
              </div>
            </section>

            <section class="page-card<?= $tipoItem === 'Serviço' ? '' : ' hidden' ?>" id="product-section-service">
              <div class="page-card-header">
                <div>
                  <h2 class="page-card-title">Dados do serviço</h2>
                  <p class="page-card-description">Informações operacionais para execução do serviço.</p>
                </div>
              </div>

              <div class="client-form-grid">
                <div class="form-group">
                  <label for="product-estimated-deadline">Prazo estimado</label>
                  <input id="product-estimated-deadline" name="prazo_estimado" class="input-default" type="text" placeholder="Ex.: 3 dias úteis" value="<?= e($dados['prazo_estimado'] ?? '') ?>" />
                </div>

                <div class="form-group">
                  <label for="product-department">Setor / responsável</label>
                  <input id="product-department" name="departamento" class="input-default" type="text" placeholder="Ex.: Metrologia" value="<?= e($dados['departamento'] ?? '') ?>" />
                </div>
              </div>
            </section>

<?php if ($erros): ?>
            <div id="product-form-error" class="error-message client-form-error" role="alert">
<?php foreach ($erros as $erro): ?>
              <p><?= e($erro) ?></p>
<?php endforeach; ?>
            </div>
<?php else: ?>
            <p id="product-form-error" class="error-message client-form-error hidden"></p>
<?php endif; ?>

            <div class="client-form-actions">
              <a href="produtos.php" class="btn-secondary">Cancelar</a>
              <button type="submit" class="btn-primary">Salvar item</button>
            </div>
          </form>
        </div>
      </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
