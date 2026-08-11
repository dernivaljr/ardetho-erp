# JavaScript

O JavaScript atual e carregado por tags `<script>` diretamente nas paginas HTML. Os arquivos nao usam `import`, `export`, bundler ou namespaces formais.

## Ordem de carregamento

### Paginas publicas

`index.html`, `about.html`, `modules.html` e `contact.html` carregam apenas:

```text
assets/js/pwa.js
```

### Login

`login.html` carrega:

```text
assets/js/data.js
assets/js/storage.js
assets/js/auth.js
assets/js/pwa.js
```

### Paginas internas

As paginas internas seguem este padrao:

```text
assets/js/data.js
assets/js/storage.js
assets/js/auth.js
assets/js/layout.js
assets/js/utils.js
assets/js/[script-da-pagina].js
assets/js/pwa.js
```

Exemplo em `clients.html`:

```text
data.js -> storage.js -> auth.js -> layout.js -> clients.js -> pwa.js
```

Paginas que usam helpers compartilhados carregam `utils.js` imediatamente antes do script especifico. Isso ocorre em `dashboard.html`, `products.html`, `product-form.html`, `sales.html`, `sale-form.html`, `financial.html`, `financial-form.html`, `reports.html`, `hr.html` e `client-form.html`.

## Responsabilidade dos arquivos

### `assets/js/data.js`

Define o objeto global `appData` com dados simulados iniciais: usuario atual, empresas, usuarios, RH, modulos, clientes, produtos, vendas, financeiro, relatorios e dashboard.

### `assets/js/storage.js`

Define chaves, helpers de leitura/escrita no `localStorage`, inicializacao de dados, reset e acesso a secoes de `appData`.

Principais funcoes:

- `initializeAppData()`;
- `resetAppData()`;
- `getStoredAppData()`;
- `getAppSection()`;
- `updateAppData()`;
- `getCurrentUser()`;
- `setCurrentUser()`;
- `getCurrentCompany()`;
- `setCurrentCompany()`;
- `getActiveModules()`;
- `setActiveModules()`;
- `getClientsData()`;
- `getProductsData()`;
- `getSalesData()`;
- `getFinancialData()`;
- `getHrData()`.

Tambem reconcilia `ardetho_active_modules` com `appData.modules` por slug, preservando escolhas do usuario quando o modulo continua disponivel.

### `assets/js/utils.js`

Centraliza helpers compartilhados usados por formularios, listagens, dashboard, financeiro e relatorios:

- `onlyDigits()`;
- `formatCurrencyInput()`;
- `parseCurrencyValue()`;
- `formatCurrencyValue()`;
- `formatCurrencyBRL()`;
- `getFinancialStatusBadgeClass()`.

### `assets/js/auth.js`

Controla login, logout, protecao de paginas internas, redirecionamento, visibilidade da sidebar, aplicacao de tema, branding e dados do usuario na interface.

### `assets/js/layout.js`

Controla o menu mobile, overlay da sidebar, fechamento por `Escape` e navegacao do user chip para `profile.html`.

### Scripts de paginas

- `dashboard.js`: metricas, alertas, grafico, atividades, status e pedidos recentes.
- `clients.js`: listagem, filtros e exclusao de clientes.
- `client-form.js`: formulario de cliente, mascaras, validacoes, CEP via ViaCEP, criacao e edicao.
- `products.js`: listagem, filtros, resumo e exclusao de produtos/servicos.
- `product-form.js`: formulario de produto/servico, mascaras, status calculado, criacao e edicao.
- `sales.js`: listagem, filtros, resumo e exclusao de vendas.
- `sale-form.js`: formulario de venda, selecao de cliente/produto, calculo de total, criacao e edicao.
- `financial.js`: resumo, filtros, listagem e exclusao de lancamentos.
- `financial-form.js`: formulario financeiro, sincronizacao com venda, criacao e edicao.
- `reports.js`: indicadores, filtros por periodo, tabela de lancamentos e exportacao CSV.
- `modules.js`: toggles de modulos, resumo e configuracao visual.
- `settings.js`: preferencias de interface e dashboard.
- `profile.js`: dados do usuario, empresa, logo, favicon e cores.
- `hr.js`: metricas, filtros, listagem e exclusao de colaboradores.
- `hr-form.js`: formulario de colaborador, criacao, edicao e exclusao.
- `pwa.js`: registro do service worker.

## Inicializacao por pagina

Cada arquivo de modulo registra `DOMContentLoaded` e valida `document.body.dataset.page`.

Exemplos:

- `initializeClientsPage()` exige `data-page="clients"`;
- `initializeProductFormPage()` exige `data-page="product-form"`;
- `initializeModulesPage()` exige `data-page="modules"`.

## Dependencias entre scripts

Os scripts de pagina dependem de funcoes globais criadas antes:

- `getAppSection()` e `updateAppData()` vem de `storage.js`;
- `getCurrentUser()` vem de `storage.js`;
- getters compartilhados como `getClientsData()` e `getProductsData()` vem de `storage.js`;
- helpers de digitos, moeda e badge financeiro vem de `utils.js` quando a pagina o carrega;
- `appData` vem de `data.js`;
- protecao e branding vem de `auth.js`;
- menu mobile vem de `layout.js`.

Por isso a ordem dos scripts e parte essencial da arquitetura atual.

## Duplicacoes verificadas

Funcoes que foram centralizadas nesta revisao:

- getters de leitura de dados em `storage.js`;
- `onlyDigits()` em `utils.js`;
- helpers de moeda de formularios em `utils.js`;
- `formatCurrencyBRL()` em `utils.js`;
- badge financeiro compartilhado em `utils.js`.

Duplicacoes que ainda permanecem:

- `saveClientsData`, `saveProductsData`, `saveSalesData`, `saveFinancialData`: repetidas entre listagem e formulario;
- `getClientDisplayName`: ainda aparece em mais de um formulario;
- funcoes de data e badges especificos continuam locais quando as regras diferem por modulo.

## Comportamento atual

Os arquivos JS funcionam como scripts globais por pagina. A verificacao sintatica com `node --check` passou para todos os arquivos em `assets/js`.

## Limitacoes conhecidas

- nao ha isolamento entre arquivos;
- funcoes globais podem colidir;
- a manutencao depende da ordem manual dos scripts;
- nao ha testes automatizados;
- ainda nao ha modularizacao formal com `import`/`export`.

## Divida tecnica

- duplicacao residual de funcoes de gravacao, datas, nomes de exibicao e badges especificos;
- renderizacao via `innerHTML` espalhada;
- validacoes e regras misturadas com manipulacao de DOM;
- mensagens de feedback alternam entre `alert`, `confirm`, texto em pagina e console.
