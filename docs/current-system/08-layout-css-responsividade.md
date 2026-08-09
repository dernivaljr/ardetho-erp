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
- tema escuro via `body.theme-dark`;
- redefinicao final de `--brand-primary-custom` e `--brand-accent-custom`.

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
- botao de logout.

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
- tabelas sao escondidas;
- `.mobile-card-list` passa a ser exibido.

### Small mobile

Em 480px:

- titulos reduzem;
- subtitulo da topbar some;
- logout dentro do user chip some;
- cards de acao mobile viram coluna unica.

## Cards mobile

Os scripts de listagem renderizam duas estruturas:

- tabela desktop em `.table-wrapper`;
- cards mobile em `.mobile-card-list`.

O CSS mostra uma ou outra conforme breakpoint.

## Tema escuro

O tema escuro e aplicado por classe:

```text
body.theme-dark
```

`auth.js` e `settings.js` alternam as classes `theme-light` e `theme-dark` com base em `ardetho_settings.themeMode`.

## Comportamento atual

O CSS esta dividido por intencao e permite manter um visual consistente sem ferramenta de build.

## Limitacoes conhecidas

- `responsive.css` possui varias media queries repetidas para o mesmo breakpoint;
- `responsive.css` usa variaveis inexistentes `--color-text` e `--color-muted`;
- uma regra mobile define `padding: 0px` para botoes;
- `pages.css` usa classes de formulario de cliente tambem em outros formularios;
- ha estilos inline gerados por JS em alguns pontos, como e-mail em `hr.js`.

## Divida tecnica

- responsividade funciona por camadas de correcoes acumuladas;
- tema escuro usa valores hardcoded em vez de tokens dedicados;
- estilos de pagina e componentes se misturam em alguns arquivos;
- sidebar/topbar repetidas no HTML aumentam risco de divergencia visual.

