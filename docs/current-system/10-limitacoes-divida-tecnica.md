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
- manifest declara o icone real existente como 211x211;
- ainda faltam icones PWA dedicados 192x192, 512x512 e maskable.

### Modulos

- `advanced-stock`, `schedule` e `advanced-reports` existem como modulos futuros com `available: false`;
- esses modulos aparecem como "Em breve", sem toggle e sem paginas placeholder;
- `schedule` ainda existe no mapa slug -> pagina de `auth.js`, mas o modulo esta indisponivel e `schedule.html` nao existe;
- `saveModulesConfiguration()` apenas exibe alerta, pois os toggles ja persistem imediatamente;
- controle de modulo inativo e visual/client-side.

### Documentacao historica

- documentos em `documents/` mencionam arquivos que nao existem na estrutura atual, como `reset.css`, `main.js` e `navigation.js`;
- `.gitignore` contem `/documents`, embora a pasta esteja presente.

## Divida tecnica identificada

### JavaScript duplicado

Duplicacoes residuais verificadas:

- funcoes `save*Data`: repetidas por listagem/formulario;
- `getClientDisplayName`: ainda aparece em mais de um formulario;
- funcoes de data e badges especificos permanecem locais quando as regras diferem por modulo.

Ja foram centralizados em `storage.js` e `utils.js`: getters de dados, `onlyDigits()`, helpers de moeda e badge financeiro compartilhado.

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

- `responsive.css` ainda concentra muitos ajustes responsivos de areas diferentes;
- classes de formulario de cliente reutilizadas em outros formularios.

### Codigo aparentemente nao utilizado

- nao ha, nesta revisao ativa, bloco funcional obsoleto confirmado para remover sem nova auditoria.

### Assets e PWA

- `ardetho-marca.png` permanece como asset de marca reservado/ambiguo, sem uso de runtime verificado;
- `manifest.json` usa o tamanho real 211x211 do icone existente, mas ainda faltam icones PWA dedicados 192x192, 512x512 e maskable.

## Pontos que exigem cuidado ao alterar a versao atual

- manter ordem de scripts no HTML;
- preservar `data-page` esperado por cada script;
- atualizar `service-worker.js` quando arquivos entram ou saem;
- revisar `localStorage` antigo ao mudar formato de `appData`;
- evitar alterar documentos historicos em `documents/` sem decisao explicita;
- diferenciar comportamento de demonstracao de comportamento persistente.
