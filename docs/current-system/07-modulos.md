# Fluxo dos modulos

Este documento descreve o comportamento atual dos modulos da versao Web/PWA.

## Dashboard

Arquivos:

- `dashboard.html`;
- `assets/js/dashboard.js`;
- `assets/css/dashboard.css`.

Fluxo atual:

- le clientes, produtos, vendas, financeiro e modulos ativos;
- calcula clientes ativos, produtos cadastrados, receitas, contas pendentes e pedidos em aberto;
- renderiza alertas conforme `ardetho_settings`;
- renderiza grafico visual a partir dos ultimos lancamentos financeiros;
- renderiza atividades recentes e status operacional;
- renderiza pedidos recentes em tabela desktop e cards mobile;
- reordena secoes de acordo com `startupView`.

## Clientes

Arquivos:

- `clients.html`;
- `client-form.html`;
- `assets/js/clients.js`;
- `assets/js/client-form.js`.

Fluxo de listagem:

- `clients.js` le `clients`;
- popula filtro de cidade;
- filtra por busca, status e cidade;
- renderiza tabela desktop e cards mobile;
- exclui cliente apos `window.confirm`.

Fluxo de formulario:

- `client-form.js` identifica modo de edicao pelo parametro `?id=`;
- alterna campos PF/PJ;
- aplica mascaras de CPF, CNPJ, telefone, WhatsApp, CEP e UF;
- valida e-mail, CPF, CNPJ, telefone, CEP e UF;
- consulta endereco via `https://viacep.com.br/ws/{cep}/json/`;
- cria cliente com id `CLI-${Date.now()}`;
- edita cliente existente;
- salva em `ardetho_app_data.clients`;
- redireciona para `clients.html`.

Limitacao conhecida: `clients.html` ainda contem um modal antigo de cadastro com `id="client-form"`, mas o fluxo atual usa `client-form.html` e `client-form.js` procura `id="client-form-page"`.

## Produtos e servicos

Arquivos:

- `products.html`;
- `product-form.html`;
- `assets/js/products.js`;
- `assets/js/product-form.js`.

Fluxo de listagem:

- le `products`;
- calcula total de itens, estoque critico e categorias;
- filtra por busca, categoria e status;
- renderiza tabela desktop e cards mobile;
- exclui item apos confirmacao.

Fluxo de formulario:

- identifica modo de edicao por `?id=`;
- alterna campos de produto e servico;
- formata preco e NCM;
- valida campos obrigatorios;
- calcula status de produto com base em estoque, estoque minimo e inatividade;
- cria id com prefixo `PRD` ou `SRV`;
- salva em `ardetho_app_data.products`;
- redireciona para `products.html`.

Divida tecnica: `updateProductStatusOptions()` esta duplicada em `product-form.js`.

## Vendas

Arquivos:

- `sales.html`;
- `sale-form.html`;
- `assets/js/sales.js`;
- `assets/js/sale-form.js`.

Fluxo de listagem:

- le `sales`;
- calcula total de pedidos, pedidos em analise e receita total;
- filtra por busca, status e cliente;
- renderiza tabela desktop e cards mobile;
- exclui pedido apos confirmacao.

Fluxo de formulario:

- identifica modo de edicao por `?id=`;
- popula clientes a partir de `clients`;
- popula itens a partir de `products`;
- gera codigo `PED-${Date.now().slice(-6)}` quando novo;
- preenche valor unitario ao escolher produto;
- calcula total por quantidade x valor unitario;
- salva em `ardetho_app_data.sales`;
- redireciona para `sales.html`.

## Financeiro

Arquivos:

- `financial.html`;
- `financial-form.html`;
- `assets/js/financial.js`;
- `assets/js/financial-form.js`.

Fluxo de listagem:

- le `financial`;
- calcula saldo atual, contas a receber, contas a pagar e vencimentos proximos;
- filtra por busca, tipo, status e cliente;
- renderiza tabela;
- exclui lancamento apos confirmacao.

Fluxo de formulario:

- identifica modo de edicao por `?id=`;
- gera codigo `LAN-${Date.now().slice(-6)}` quando novo;
- alterna comportamento entre `Receita` e `Despesa`;
- para receita, exige pedido relacionado e sincroniza cliente, categoria, descricao, valor e forma de pagamento a partir da venda;
- para despesa, permite preenchimento manual;
- salva em `ardetho_app_data.financial`;
- redireciona para `financial.html`.

## Relatorios

Arquivos:

- `reports.html`;
- `assets/js/reports.js`.

Fluxo atual:

- filtra vendas por periodo;
- filtra financeiro por periodo;
- calcula receita, saldo, pedidos concluidos, ticket medio, conversao comercial, pendencias e modulos ativos;
- renderiza cards e tabela dos ultimos lancamentos financeiros;
- filtra blocos de resumo por busca textual;
- exporta CSV local com `Blob`, `URL.createObjectURL()` e download automatico.

## Modulos ERP

Arquivos:

- `erp-modules.html`;
- `assets/js/modules.js`;
- `assets/js/auth.js`.

Fluxo atual:

- `erp-modules.html` usa `data-page="modules"`;
- `modules.js` combina `appData.modules` com `ardetho_active_modules`;
- renderiza modulos principais e complementares;
- alterna estado ativo/inativo por slug;
- salva apenas id, slug e active em `ardetho_active_modules`;
- renderiza contadores e resumo de configuracao;
- `Salvar configuracao` exibe `alert`, mas a alteracao ja foi persistida no toggle.

Limitacao conhecida: alguns slugs de `data.js` nao possuem pagina correspondente.

## Configuracoes

Arquivos:

- `settings.html`;
- `assets/js/settings.js`;
- `assets/js/auth.js`;
- `assets/js/dashboard.js`.

Fluxo atual:

- le `ardetho_settings`;
- aplica tema claro/escuro;
- aplica sidebar compacta;
- controla atalhos e alertas do dashboard;
- salva preferencias em `ardetho_settings`;
- restaura padroes;
- usa switches com `aria-pressed`.

## Perfil

Arquivos:

- `profile.html`;
- `assets/js/profile.js`;
- `assets/js/auth.js`.

Fluxo atual:

- le usuario atual e empresa atual;
- preenche formulario e resumo lateral;
- permite editar nome, e-mail, cargo, departamento, nome da empresa, logo, icone e cores;
- atualiza preview em tempo real;
- salva em `ardetho_current_user` e `ardetho_current_company_profile`;
- restaura valores padrao fixos do Ardetho.

Limitacao conhecida: alterar o perfil atual nao atualiza automaticamente o array `users` dentro de `ardetho_app_data`.

## RH

Arquivos:

- `hr.html`;
- `hr-form.html`;
- `assets/js/hr.js`;
- `assets/js/hr-form.js`.

Fluxo de listagem:

- le `hr`;
- calcula ativos, ferias, afastados e folha ativa;
- popula filtro de departamento;
- filtra por busca, status e departamento;
- renderiza tabela;
- exclui colaborador apos confirmacao.

Fluxo de formulario:

- identifica modo de edicao por `?id=`;
- preenche campos do colaborador;
- valida nome, e-mail, cargo, departamento, salario e admissao;
- cria id `EMP-${Date.now()}`;
- salva em `ardetho_app_data.hr`;
- usa `alert` para feedback;
- redireciona para `hr.html`.

## Paginas publicas

Arquivos:

- `index.html`;
- `about.html`;
- `modules.html`;
- `contact.html`.

Fluxo atual:

- paginas estaticas de apresentacao;
- carregam CSS publico;
- carregam `pwa.js`;
- nao manipulam dados locais;
- `contact.html` apresenta informacoes institucionais, mas nao possui formulario funcional.

