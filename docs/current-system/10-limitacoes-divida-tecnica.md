# Limitacoes e divida tecnica

Este documento separa comportamento atual, limitacao conhecida e divida tecnica. Ele descreve a versao Web/PWA existente, sem propor alternativas tecnologicas.

## Comportamento atual

- aplicacao estatica multi-page;
- dados simulados em `assets/js/data.js`;
- persistencia em `localStorage`;
- autenticacao simulada em `assets/js/auth.js`;
- UI interna com sidebar, topbar e user chip repetidos em cada HTML;
- CRUDs simulados substituem arrays inteiros em `ardetho_app_data`;
- relatorios calculados no cliente;
- PWA com service worker cache-first;
- CSS dividido por responsabilidade.

## Limitacoes conhecidas

### Persistencia

- dados ficam apenas no navegador local;
- limpar o storage remove o estado do sistema;
- nao ha controle de concorrencia;
- nao ha historico de alteracoes;
- `ardetho_settings` nao participa de `STORAGE_KEYS.clearAll()`.

### Autenticacao

- usuarios e senhas ficam em `data.js`;
- login compara e-mail e senha no front-end;
- a protecao depende da existencia de `ardetho_current_user`;
- permissoes existem nos dados, mas nao formam um controle granular aplicado nos modulos.

### Renderizacao

- uso amplo de `innerHTML` para tabelas, cards e opcoes;
- dados editaveis podem voltar para a interface como HTML renderizado;
- mensagens de feedback variam entre `alert`, `confirm`, texto em tela e console.

### PWA

- cache manual;
- sem fallback offline dedicado;
- icone real diferente dos tamanhos declarados no manifest;
- algumas imagens usadas por branding dinamico nao estao cacheadas.

### Modulos

- `advanced-stock` existe em `data.js`, mas `auth.js` usa `inventory`;
- `schedule.html` e `inventory.html` nao existem;
- `saveModulesConfiguration()` apenas exibe alerta, pois os toggles ja persistem imediatamente;
- controle de modulo inativo e visual/client-side.

### Documentacao historica

- `README.MD` e documentos em `documents/` mencionam arquivos que nao existem na estrutura atual, como `reset.css`, `main.js`, `navigation.js` e `utils.js`;
- `.gitignore` contem `/documents`, embora a pasta esteja presente.

## Divida tecnica identificada

### JavaScript duplicado

Duplicacoes verificadas:

- `getClientsData`: 6 ocorrencias;
- `getProductsData`: 5 ocorrencias;
- `getSalesData`: 5 ocorrencias;
- `getFinancialData`: 4 ocorrencias;
- `onlyDigits`: 4 ocorrencias;
- `formatCurrencyInput`: 3 ocorrencias;
- `getClientDisplayName`: 3 ocorrencias;
- `parseCurrencyValue`: 3 ocorrencias;
- `formatCurrencyValue`: 2 ocorrencias;
- `getActiveModulesData`: 2 ocorrencias;
- funcoes `save*Data`: repetidas por listagem/formulario;
- `updateProductStatusOptions`: duplicada em `product-form.js`.

### Mistura de responsabilidades

Scripts de pagina concentram:

- leitura de dados;
- filtragem;
- calculos;
- renderizacao HTML;
- eventos;
- validacao;
- persistencia;
- redirecionamento.

### HTML repetido

As paginas internas repetem sidebar, topbar, navegacao, user chip e logout. Qualquer ajuste estrutural precisa ser replicado manualmente.

### CSS acumulado

Pontos verificados:

- varias media queries repetidas em `responsive.css`;
- variaveis inexistentes `--color-text` e `--color-muted`;
- regra mobile com `padding: 0px` para botoes;
- tema escuro com valores hardcoded;
- classes de formulario de cliente reutilizadas em outros formularios.

### Codigo aparentemente nao utilizado

- modal de cliente em `clients.html` com `id="client-form"`;
- botao `data-action="close-client-modal"`;
- `clients.js` nao contem fluxo para abrir/fechar ou submeter esse modal;
- o fluxo ativo de cliente usa `client-form.html` e `client-form.js`.

### Assets e PWA

- `ardetho-icon-normal.png`, `ardetho-marca.png` e `preview-home.png` existem, mas nao aparecem como assets principais de runtime verificados;
- imagens da Mecânica XYZ sao usadas por dados de empresa, mas nao cacheadas pelo service worker;
- `manifest.json` declara tamanhos 192 e 512 usando uma imagem 211x211.

## Pontos que exigem cuidado ao alterar a versao atual

- manter ordem de scripts no HTML;
- preservar `data-page` esperado por cada script;
- atualizar `service-worker.js` quando arquivos entram ou saem;
- revisar `localStorage` antigo ao mudar formato de `appData`;
- evitar alterar documentos historicos em `documents/` sem decisao explicita;
- diferenciar comportamento de demonstracao de comportamento persistente.
