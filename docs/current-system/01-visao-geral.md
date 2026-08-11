# Visao geral do Ardetho ERP

O Ardetho ERP e uma aplicacao Web/PWA estatica criada como projeto academico de front-end. A versao atual simula um sistema ERP modular com area publica, area interna autenticada, dados persistidos no navegador e personalizacao visual por empresa.

## Proposito da versao atual

O sistema demonstra:

- uma landing institucional publica;
- um ambiente interno de ERP;
- modulos operacionais simulados;
- persistencia local com `localStorage`;
- autenticacao simulada;
- customizacao de marca;
- comportamento responsivo;
- suporte PWA basico.

## Tecnologias usadas

- HTML5;
- CSS3;
- JavaScript puro;
- LocalStorage;
- Service Worker;
- Web App Manifest;
- Google Fonts, fonte Inter.

Nao ha bundler, framework JavaScript, sistema de rotas ou camada remota de dados nesta versao.

## Areas do sistema

### Area publica

Arquivos principais:

- `index.html`;
- `about.html`;
- `modules.html`;
- `contact.html`;
- `login.html`.

As paginas publicas apresentam o produto, seus modulos e o acesso ao sistema. Elas carregam os estilos base e `assets/js/pwa.js`. Apenas `login.html` tambem carrega `data.js`, `storage.js` e `auth.js`.

### Area interna

Arquivos principais:

- `dashboard.html`;
- `clients.html`;
- `client-form.html`;
- `products.html`;
- `product-form.html`;
- `sales.html`;
- `sale-form.html`;
- `financial.html`;
- `financial-form.html`;
- `reports.html`;
- `erp-modules.html`;
- `settings.html`;
- `profile.html`;
- `hr.html`;
- `hr-form.html`.

As paginas internas compartilham sidebar, topbar, user chip, logout, branding dinamico e menu mobile. A protecao de acesso e feita por JavaScript em `assets/js/auth.js`.

## Funcionalidades implementadas

- login simulado com duas contas;
- logout;
- protecao de paginas internas por presenca de usuario no `localStorage`;
- aplicacao de nome, cargo e avatar do usuario na interface;
- aplicacao de logo, favicon e cores da empresa atual;
- dashboard com metricas, alertas, resumo, grafico visual e pedidos recentes;
- listagem, filtros, criacao, edicao e exclusao simuladas de clientes;
- listagem, filtros, criacao, edicao e exclusao simuladas de produtos e servicos;
- listagem, filtros, criacao, edicao e exclusao simuladas de vendas;
- listagem, filtros, criacao, edicao e exclusao simuladas de lancamentos financeiros;
- relatorios com indicadores e exportacao CSV local;
- ativacao/desativacao simulada de modulos;
- configuracoes visuais e de dashboard;
- edicao simulada de perfil e marca;
- listagem, filtros, criacao, edicao e exclusao simuladas de RH;
- PWA instalavel com cache local de arquivos principais.

## Comportamento atual

O estado principal da aplicacao e guardado no navegador. `assets/js/data.js` contem os dados iniciais e `assets/js/storage.js` copia esses dados para `localStorage` quando ainda nao existem dados salvos.

Depois disso, cada modulo le e grava secoes do objeto persistido em `ardetho_app_data`.

## Limitacoes conhecidas

- dados e login dependem do navegador atual;
- nao existe separacao real entre usuarios no armazenamento principal;
- credenciais ficam em dados simulados no front-end;
- relatorios sao calculados no cliente;
- cache PWA e manual e precisa ser mantido quando novos arquivos entram;
- modulos futuros aparecem como "Em breve" sem pagina implementada.

## Divida tecnica em destaque

- duplicacao residual de funcoes entre modulos;
- uso frequente de `innerHTML` para renderizacao;
- responsividade ainda depende da ordem manual da cascade;
- inconsistencias entre documentacao historica e estrutura real;
- alguns slugs futuros ainda nao possuem pagina implementada.
