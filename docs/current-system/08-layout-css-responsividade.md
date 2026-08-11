# Layout, CSS e responsividade

O layout interno do Ardetho ERP e repetido nas paginas HTML internas e controlado por CSS compartilhado e por `assets/js/layout.js`.

## Layout compartilhado

Elementos principais:

- `.app-layout`;
- `.sidebar`;
- `.sidebar-brand`;
- `.sidebar-nav`;
- `.app-main`;
- `.topbar`;
- `.app-content`;
- `.content-container`;
- `.page-header`;
- `.user-chip`;
- `.sidebar-overlay`;
- `.mobile-menu-toggle`.

## `assets/js/layout.js`

Responsabilidades:

- abrir e fechar menu mobile;
- mostrar e esconder overlay;
- fechar menu com tecla `Escape`;
- fechar menu ao redimensionar acima de 768px;
- navegar para `profile.html` ao clicar no user chip;
- preservar o clique de logout dentro do user chip.

## CSS por responsabilidade

### `variables.css`

Define tokens de:

- cores;
- tipografia;
- pesos;
- espacamentos;
- raios;
- sombras;
- cores do tema escuro;
- larguras de layout;
- transicoes.

Tambem define variaveis customizaveis de marca:

- `--brand-primary-custom`;
- `--brand-accent-custom`;
- `--brand-primary-soft-custom`.

### `global.css`

Define:

- estilos base de `html`, `body`, links, botoes, inputs, imagens, listas e titulos;
- containers e utilitarios simples;
- classe `.hidden`;
- tema escuro via `body.theme-dark`.

### `layout.css`

Define:

- shell interno;
- sidebar;
- topbar;
- content wrapper;
- page header;
- grids do dashboard;
- toolbar;
- user chip;
- logo;
- estado base de `.mobile-menu-toggle` e `.sidebar-overlay`;
- sidebar compacta.

### `components.css`

Define:

- botoes;
- inputs;
- cards;
- metric cards;
- badges;
- tabelas;
- busca e filtros;
- grupos de acao;
- empty state;
- dropdown;
- modal;
- toast;
- botao de logout;
- estado base de `.mobile-card-list`.

### `public.css`

Define paginas publicas:

- header publico;
- hero;
- mockup cards;
- cards flutuantes;
- secoes de beneficios;
- grid de modulos;
- highlight;
- CTA;
- footer.

### `auth.css`

Define tela de login:

- layout em duas colunas;
- painel de branding;
- painel de formulario;
- card de login;
- beneficios;
- mensagem de erro;
- badge de autenticacao.

### `dashboard.css`

Define:

- grafico visual;
- cards de notificacao;
- resumo rapido;
- lista de atividades;
- lista de status;
- ajustes especificos do dashboard;
- media queries locais do dashboard.

### `pages.css`

Define:

- page cards;
- toolbar de paginas internas;
- status cards;
- grids de formularios;
- blocos de configuracao e modulos;
- switches;
- perfil;
- relatorios;
- financeiro;
- formulario de cliente e formularios que reutilizam a mesma classe.

### `responsive.css`

Centraliza a maior parte dos ajustes responsivos:

- breakpoints em 1280px, 1024px, 768px e 480px;
- responsividade da area publica;
- responsividade do login;
- menu mobile interno;
- refinamento da topbar mobile;
- dashboard mobile;
- prevencao de overflow horizontal;
- conversao de tabelas para cards mobile;
- excecao de tabela com scroll horizontal para paginas sem cards mobile;
- ajustes de botoes e campos.

## Responsividade atual

### Desktop

O layout interno usa grid com sidebar fixa na primeira coluna e conteudo na segunda.

### Tablet

Em torno de 1024px:

- grids publicos e internos reduzem colunas;
- login vira uma coluna;
- page header empilha;
- conteudo ganha padding menor.

### Mobile

Em 768px:

- `.app-layout` deixa de ser grid;
- sidebar vira menu lateral fixo escondido;
- botao hamburger aparece;
- overlay aparece ao abrir menu;
- topbar compacta;
- tabelas sao escondidas quando a pagina possui alternativa em `.mobile-card-list`;
- `.mobile-card-list` passa a ser exibido;
- Financeiro e RH mantem tabela visivel com rolagem horizontal por meio de `.table-wrapper-mobile-scroll`.

### Small mobile

Em 480px:

- titulos reduzem;
- subtitulo da topbar some;
- logout dentro do user chip some;
- cards de acao mobile viram coluna unica;
- `.card` e `.section-block` usam `border-radius: 16px`.

## Cards mobile

As paginas de listagem com alternativa mobile renderizam duas estruturas:

- tabela desktop em `.table-wrapper`;
- cards mobile em `.mobile-card-list`.

O CSS mostra uma ou outra conforme breakpoint.

Excecao atual: `financial.html` e `hr.html` nao possuem cards mobile. Nessas paginas, o wrapper da tabela usa `table-wrapper-mobile-scroll`, que reverte o `display: none` aplicado a `.table-wrapper` em telas ate 768px e preserva o `overflow-x: auto` definido no componente base.

Entre 481px e 768px, `.card` e `.section-block` usam `border-radius: 18px`. Em telas ate 480px, uma sobrescrita posterior garante `border-radius: 16px`.

Os botoes mobile mantem o padding base de `components.css` (`padding: 0 18px`); nao ha mais sobrescrita responsiva com `padding: 0px`.

## Tema escuro

O tema escuro e aplicado por classe:

```text
body.theme-dark
```

`auth.js` e `settings.js` alternam as classes `theme-light` e `theme-dark` com base em `ardetho_settings.themeMode`.

## Comportamento atual

O CSS esta dividido por intencao e permite manter um visual consistente sem ferramenta de build.

## Limitacoes conhecidas

- `responsive.css` ainda concentra ajustes de varias areas da interface;
- `pages.css` usa classes de formulario de cliente tambem em outros formularios;
- ha estilos inline gerados por JS em alguns pontos, como e-mail em `hr.js`.

## Divida tecnica

- responsividade ainda depende da ordem manual da cascade;
- estilos de pagina e componentes se misturam em alguns arquivos;
- sidebar/topbar repetidas no HTML aumentam risco de divergencia visual.
