# Arquitetura atual

O Ardetho ERP atual usa arquitetura multi-page estatica. Cada tela e um arquivo HTML independente que carrega folhas CSS globais e scripts JavaScript globais ou especificos da pagina.

## Modelo arquitetural

```text
HTML da pagina
  carrega CSS base
  carrega scripts globais
  carrega script especifico da pagina
  registra PWA
```

Nao ha build step, empacotamento, roteamento centralizado, import/export ES Modules ou separacao formal entre camadas.

## Fluxo HTML, CSS e JavaScript

1. O navegador abre uma pagina HTML.
2. A pagina carrega CSS em ordem fixa.
3. Paginas internas carregam `data.js`, `storage.js`, `auth.js`, `layout.js` e o JS especifico.
4. Os scripts registram inicializadores em `DOMContentLoaded`.
5. `storage.js` inicializa dados locais ao ser carregado.
6. `auth.js` valida sessao, aplica branding, ajusta menu e protege paginas internas.
7. `layout.js` ativa menu mobile e navegacao para perfil.
8. O script da pagina renderiza tabelas, cards, filtros, formularios ou relatorios.
9. `pwa.js` registra o service worker no evento `load`.

## Area publica

As paginas publicas carregam:

- `variables.css`;
- `global.css`;
- `layout.css`;
- `components.css`;
- `public.css`;
- `responsive.css`;
- `pwa.js`.

Excecao: `login.html` carrega `auth.css` no lugar de `public.css` e tambem carrega `data.js`, `storage.js` e `auth.js`.

## Area interna

As paginas internas carregam:

- `variables.css`;
- `global.css`;
- `layout.css`;
- `components.css`;
- CSS especifico (`dashboard.css` ou `pages.css`);
- `responsive.css`;
- `data.js`;
- `storage.js`;
- `auth.js`;
- `layout.js`;
- script especifico da pagina;
- `pwa.js`.

## Fluxo de dados

```text
assets/js/data.js
  define appData inicial

assets/js/storage.js
  cria/consulta ardetho_app_data no localStorage

scripts dos modulos
  leem secoes com getAppSection()
  alteram arrays em memoria
  gravam com updateAppData()

HTML
  recebe dados renderizados pelo script da pagina
```

## Estado global

O sistema usa variaveis e funcoes globais definidas por scripts carregados diretamente no HTML.

Exemplos:

- `appData`, em `assets/js/data.js`;
- `storage`, `STORAGE_KEYS`, `getAppSection`, `updateAppData`, em `assets/js/storage.js`;
- `getCurrentUser`, `setCurrentUser`, `getActiveModules`, em `assets/js/storage.js`;
- helpers compartilhados de digitos, moeda e badge financeiro em `assets/js/utils.js`;
- funcoes de autenticacao e branding em `assets/js/auth.js`.

## Controle de acesso

O controle de acesso atual e client-side:

- paginas internas verificam se existe usuario atual;
- se nao houver usuario, redirecionam para `login.html`;
- paginas de modulos inativos podem redirecionar para `dashboard.html`;
- links de modulos inativos podem ser escondidos na sidebar.

Este comportamento e responsabilidade de `assets/js/auth.js`.

## Inicializacao

Cada script especifico verifica `document.body.dataset.page` antes de executar sua rotina principal.

Exemplos:

- `dashboard.js` executa quando `data-page="dashboard"`;
- `clients.js` executa quando `data-page="clients"`;
- `client-form.js` executa quando `data-page="client-form"`;
- `modules.js` executa quando `data-page="modules"` em `erp-modules.html`.

## Comportamento atual

A arquitetura atual privilegia simplicidade e demonstracao academica: cada pagina possui seu proprio HTML e seu proprio script de tela.

## Limitacoes conhecidas

- nao existe roteador central;
- nao existe componentizacao formal de HTML;
- sidebar e topbar sao repetidas nos HTML internos;
- regras de dominio, renderizacao e persistencia ficam misturadas nos scripts de pagina;
- alteracoes globais exigem edicao coordenada em varios arquivos.

## Divida tecnica

- scripts globais dependem da ordem manual no HTML;
- parte dos utilitarios foi centralizada em `assets/js/utils.js`, mas ainda ha duplicacoes residuais;
- `innerHTML` e usado amplamente para montar tabelas e cards;
- scripts de pagina acumulam responsabilidades de busca, filtro, renderizacao, validacao e persistencia.
