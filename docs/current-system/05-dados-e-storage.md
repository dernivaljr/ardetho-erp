# Dados e storage

O modelo de dados atual e simulado em JavaScript e persistido no navegador com `localStorage`.

## `assets/js/data.js`

`data.js` define o objeto global `appData`.

Secoes verificadas:

- `currentUser`;
- `companies`;
- `users`;
- `hr`;
- `modules`;
- `clients`;
- `products`;
- `sales`;
- `financial`;
- `reports`;
- `dashboard`.

Quantidades iniciais verificadas:

```text
companies: 2
users: 2
hr: 5
modules: 9
clients: 5
products: 8
sales: 5
financial: 6
reports: 4
```

## Modelo de dados atual

### Usuario atual

`currentUser` contem id, nome, e-mail, senha simulada, cargo, departamento, avatar, ultimo acesso, empresa, permissoes e preferencias.

### Empresas

`companies` contem perfis de marca:

- nome;
- nome de exibicao;
- URL de logo;
- URL de icone;
- cor primaria;
- cor de destaque.

### Usuarios

`users` contem as contas simuladas usadas pelo login.

Contas documentadas no sistema:

- `admin@ardetho.com` / `123456`;
- `admin@mecanicaxyz.com` / `123456`.

### RH

`hr` contem colaboradores com nome, e-mail, telefone, cargo, departamento, salario, admissao, status e observacoes.

### Modulos

`modules` contem nome, slug, descricao, status ativo e categoria.

Slugs verificados:

- `clients`;
- `products`;
- `sales`;
- `financial`;
- `reports`;
- `advanced-stock`;
- `hr`;
- `schedule`;
- `advanced-reports`.

### Clientes

`clients` suporta pessoa fisica e pessoa juridica. Campos principais:

- `personType`;
- `status`;
- dados PF;
- dados PJ;
- contato;
- endereco;
- observacoes;
- `createdAt`.

### Produtos e servicos

`products` mistura itens do tipo `Produto` e `Servico`.

Produtos usam estoque, estoque minimo, fornecedor, marca e NCM. Servicos usam prazo estimado e departamento.

### Vendas

`sales` contem pedido, cliente, item, quantidade, valor unitario, total, forma e condicoes de pagamento.

### Financeiro

`financial` contem receitas e despesas com status, datas, cliente, venda relacionada, categoria, descricao, valor e forma de pagamento.

### Relatorios

`reports` contem registros simulados de relatorios, mas a tela `reports.html` calcula indicadores principalmente a partir de `clients`, `products`, `sales`, `financial` e modulos ativos.

### Dashboard

`dashboard` contem metricas e listas iniciais, mas `dashboard.js` recalcula grande parte da tela a partir dos dados persistidos.

## `assets/js/storage.js`

`storage.js` centraliza a persistencia local.

### Chaves do `localStorage`

| Chave | Origem | Uso atual |
| --- | --- | --- |
| `ardetho_app_data` | `STORAGE_KEYS.appData` | Guarda a copia persistida de `appData`. |
| `ardetho_current_user` | `STORAGE_KEYS.currentUser` | Guarda o usuario autenticado sem senha. |
| `ardetho_current_company_profile` | `STORAGE_KEYS.currentCompany` e `COMPANY_PROFILE_STORAGE_KEY` | Guarda o perfil de empresa/marca atual. |
| `ardetho_active_modules` | `STORAGE_KEYS.activeModules` | Guarda estado ativo/inativo dos modulos. |
| `ardetho_settings` | `SETTINGS_STORAGE_KEY` em `settings.js` e `dashboard.js` | Guarda preferencias visuais e de dashboard. |

Tambem ha remocoes em `sessionStorage` para `ardetho_current_user` e `ardetho_current_company_profile` durante logout, embora o fluxo principal use `localStorage`.

## Inicializacao de dados

Ao carregar `storage.js`, `initializeAppData()` e executada automaticamente.

Fluxo atual:

1. verifica se existe `ardetho_app_data`;
2. se nao existir, salva um clone de `appData`;
3. verifica se existe `ardetho_active_modules`;
4. se nao existir, salva id, slug e active dos modulos iniciais;
5. verifica se existe `ardetho_current_company_profile`;
6. se nao existir, salva a empresa do `appData.currentUser`.

## Reset de dados

`resetAppData()`:

- restaura `ardetho_app_data`;
- salva `currentUser` sem senha;
- restaura `ardetho_active_modules`;
- restaura empresa atual padrao.

## Atualizacao de secoes

Os modulos usam:

- `getAppSection(section, fallback)`;
- `updateAppData(section, newData)`.

Exemplo:

```text
clients.js
  getAppSection("clients", appData.clients)
  updateAppData("clients", updatedClients)
```

## Comportamento atual

Cada CRUD substitui o array inteiro da secao correspondente em `ardetho_app_data`.

## Limitacoes conhecidas

- dados ficam presos ao navegador e ao perfil local;
- nao ha sincronizacao entre abas alem do comportamento nativo do storage;
- nao ha historico de alteracoes;
- nao ha validacao centralizada do modelo;
- a chave `ardetho_settings` nao faz parte de `STORAGE_KEYS.clearAll()`.

## Divida tecnica

- `data.js` funciona como banco inicial e tambem como fallback;
- dados de usuario, empresa, sessoes e configuracoes ficam espalhados entre `storage.js`, `auth.js`, `settings.js`, `dashboard.js` e `profile.js`;
- algumas entidades armazenam campos derivados, como `clientName` e `productName` nas vendas.

