# Estrutura do projeto

O projeto esta organizado como uma aplicacao estatica com paginas HTML na raiz, assets em `assets/`, documentacao historica em `documents/` e esta documentacao em `docs/current-system/`.

## Estrutura atual verificada

```text
/
├── about.html
├── client-form.html
├── clients.html
├── contact.html
├── dashboard.html
├── erp-modules.html
├── financial-form.html
├── financial.html
├── hr-form.html
├── hr.html
├── index.html
├── LICENSE
├── login.html
├── manifest.json
├── modules.html
├── product-form.html
├── products.html
├── profile.html
├── README.MD
├── reports.html
├── sale-form.html
├── sales.html
├── service-worker.js
├── settings.html
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── docs/
│   └── current-system/
└── documents/
```

## Arquivos da raiz

### Paginas publicas

- `index.html`: home publica.
- `about.html`: pagina institucional sobre o projeto.
- `modules.html`: apresentacao publica dos modulos.
- `contact.html`: pagina institucional de contato, sem formulario funcional.
- `login.html`: tela de autenticacao simulada.

### Paginas internas

- `dashboard.html`: visao geral operacional.
- `clients.html`: listagem e filtros de clientes.
- `client-form.html`: formulario de criacao/edicao de cliente.
- `products.html`: listagem e filtros de produtos/servicos.
- `product-form.html`: formulario de criacao/edicao de item.
- `sales.html`: listagem e filtros de vendas.
- `sale-form.html`: formulario de criacao/edicao de venda.
- `financial.html`: listagem, filtros e resumo financeiro.
- `financial-form.html`: formulario de criacao/edicao de lancamento financeiro.
- `reports.html`: relatorios e exportacao CSV.
- `erp-modules.html`: configuracao visual de modulos ativos/inativos.
- `settings.html`: configuracoes da interface e dashboard.
- `profile.html`: perfil do usuario e marca da empresa.
- `hr.html`: listagem, filtros e metricas de RH.
- `hr-form.html`: formulario de criacao/edicao de colaborador.

### PWA

- `manifest.json`: metadados de instalacao da PWA.
- `service-worker.js`: cache offline basico.

### Outros

- `README.MD`: documentacao principal do repositorio.
- `LICENSE`: licenca do projeto.
- `.gitignore`: ignora `/documents`.

## Pasta `assets/css`

Arquivos atuais:

- `variables.css`;
- `global.css`;
- `layout.css`;
- `components.css`;
- `public.css`;
- `auth.css`;
- `dashboard.css`;
- `pages.css`;
- `responsive.css`.

Observacao: a documentacao historica menciona `reset.css`, mas esse arquivo nao existe na estrutura atual verificada.

## Pasta `assets/js`

Arquivos atuais:

- `data.js`;
- `storage.js`;
- `auth.js`;
- `layout.js`;
- `utils.js`;
- `dashboard.js`;
- `clients.js`;
- `client-form.js`;
- `products.js`;
- `product-form.js`;
- `sales.js`;
- `sale-form.js`;
- `financial.js`;
- `financial-form.js`;
- `reports.js`;
- `modules.js`;
- `settings.js`;
- `profile.js`;
- `hr.js`;
- `hr-form.js`;
- `pwa.js`.

Observacao: a documentacao historica menciona `main.js` e `navigation.js`, mas esses arquivos nao existem na estrutura atual verificada.

## Pasta `assets/images`

Arquivos atuais:

- `ardetho-logo.png`;
- `ardetho-icon.png`;
- `ardetho-marca.png`;
- `mecanica-xyz-logo.png`;
- `mecanica-xyz-icon.png`;
- `preview-dashboard.png`.

As imagens `ardetho-logo.png` e `ardetho-icon.png` sao usadas no layout e na PWA. As imagens da Mecânica XYZ sao referenciadas nos dados simulados de empresa e fazem parte do precache atual. `preview-dashboard.png` e usado pelo `README.MD`. `ardetho-marca.png` permanece como asset de marca reservado/ambiguo, sem uso de runtime verificado.

## Pasta `documents`

Contem material historico e de concepcao do projeto:

- `blueprint.txt`;
- `checklist.txt`;
- `content.txt`;
- `design.txt`;
- `geral.txt`.

Comportamento atual: a pasta existe e os arquivos aparecem no repositorio, embora `.gitignore` contenha `/documents`.

## Pasta `docs/current-system`

Contem a documentacao consolidada da versao Web/PWA atual.

Esta pasta nao substitui `documents/`; ela registra o estado real verificado no codigo.
