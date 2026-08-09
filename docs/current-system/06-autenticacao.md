# Autenticacao

A autenticacao atual e simulada no front-end. Ela usa os usuarios definidos em `assets/js/data.js` e persiste o usuario logado em `localStorage`.

## Arquivos responsaveis

- `login.html`;
- `assets/js/data.js`;
- `assets/js/storage.js`;
- `assets/js/auth.js`;
- `assets/js/profile.js`, para alteracoes posteriores do usuario atual;
- `assets/js/layout.js`, para navegacao pelo user chip.

## Contas simuladas

As contas atuais sao:

```text
admin@ardetho.com / 123456
admin@mecanicaxyz.com / 123456
```

As senhas estao em texto puro dentro de `assets/js/data.js`, porque esta versao simula autenticacao no cliente.

## Fluxo de login

1. `login.html` carrega `data.js`, `storage.js`, `auth.js` e `pwa.js`.
2. `auth.js` executa `initializeAuth()` em `DOMContentLoaded`.
3. Se o usuario ja estiver autenticado, `redirectAuthenticatedUserFromLogin()` redireciona para `dashboard.html`.
4. O submit do formulario chama `handleLoginSubmit()`.
5. `findUserByCredentials(email, password)` procura o usuario em `getUsersData()`.
6. Se encontrar usuario:
   - cria uma copia sem `password`;
   - salva em `ardetho_current_user`;
   - encontra a empresa por `companyId`;
   - salva a empresa em `ardetho_current_company_profile`;
   - redireciona para `dashboard.html`.
7. Se nao encontrar usuario, exibe mensagem de erro no elemento `loginError`.

## Protecao de paginas internas

`protectInternalPage()`:

- ignora `login.html`;
- verifica `isUserAuthenticated()`;
- se nao houver usuario atual, redireciona para `login.html`.

`isUserAuthenticated()` considera autenticado quando existe um objeto com e-mail em `ardetho_current_user`.

## Logout

O logout e acionado por elementos com:

```html
data-action="logout"
```

`logoutUser()` remove:

- `STORAGE_KEYS.currentUser`;
- `ardetho_current_user`;
- `ardetho_current_company_profile`;
- equivalentes em `sessionStorage`.

Depois redireciona para `login.html`.

## Controle de modulos inativos

`auth.js` tambem controla acesso visual a modulos:

- `getModulePageMap()` mapeia slugs para paginas;
- `protectInactiveModulePage()` redireciona para `dashboard.html` se a pagina corresponder a modulo inativo;
- `updateSidebarVisibility()` esconde links da sidebar para modulos inativos.

Mapeamento atual em `auth.js`:

```text
clients -> clients.html
products -> products.html
sales -> sales.html
financial -> financial.html
reports -> reports.html
hr -> hr.html
schedule -> schedule.html
inventory -> inventory.html
```

Limitacao conhecida: os dados usam `advanced-stock`, mas o mapa usa `inventory`. Alem disso, `schedule.html` e `inventory.html` nao existem na estrutura atual.

## Branding e usuario na interface

`auth.js` aplica:

- `applyGlobalVisualSettings()`;
- `applyGlobalCompanyBranding()`;
- `applyCurrentUserToInterface()`.

Essas funcoes atualizam:

- classes de tema;
- sidebar compacta;
- atalhos do dashboard;
- variaveis CSS de marca;
- logo e icone;
- favicon quando existe tag de icone na pagina;
- nome, cargo e avatar do usuario.

## Comportamento atual

O login funciona para demonstracao local e para diferenciar visualmente empresas simuladas.

## Limitacoes conhecidas

- autenticacao e feita inteiramente no navegador;
- a presenca de `ardetho_current_user` e suficiente para passar pela protecao;
- permissoes do usuario existem nos dados, mas nao formam um controle granular consistente;
- senhas estao em `data.js`;
- algumas paginas de formulario nao possuem tag `link rel="icon"`, entao a troca dinamica de favicon so ocorre quando a tag existe.

## Divida tecnica

- `auth.js` acumula autenticacao, autorizacao visual, branding, tema, sidebar e user UI;
- dados de configuracao sao lidos diretamente em mais de um arquivo;
- ha remocoes redundantes no logout;
- a protecao de modulos depende de strings de slug e pagina mantidas manualmente.

