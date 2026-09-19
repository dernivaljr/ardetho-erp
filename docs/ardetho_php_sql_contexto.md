# Ardetho ERP — Contexto da Migração para PHP + SQL

> Documento histórico da migração. Na versão de entrega, a aplicação usa exclusivamente rotas PHP + MariaDB; os HTMLs e o Service Worker da antiga PWA foram removidos da branch de limpeza e continuam disponíveis no histórico Git. Os dados demonstrativos usados pelo importador estão em `database/seed-demo.json`. Para instalação e uso atuais, consulte `README.MD`.

## 1. Objetivo do projeto

Criar uma nova versão acadêmica e funcional do Ardetho ERP utilizando PHP + SQL, tomando como base a versão Web/PWA já revisada.

A intenção não é reconstruir o ERP inteiro, nem redesenhar a interface existente. O foco é reaproveitar o frontend atual e substituir gradualmente os dados simulados/localStorage por persistência real em banco de dados.

A nova versão deve permanecer visualmente coerente com a PWA atual.

---

## 2. Estratégia de repositório

A versão Web/PWA atual deve ser preservada como referência estável.

Preferência inicial:

- manter o repositório atual;
- criar uma nova branch para a versão PHP + SQL;
- sugestão de nome: `php-sql`;
- evitar fork neste primeiro momento, salvo se surgir necessidade de separar completamente os projetos.

A branch atual da versão revisada é `pwa-review`.

Fluxo sugerido:

```text
pwa-review
   └── versão Web/PWA revisada e estável

php-sql
   └── nova versão PHP + SQL
```

---

## 3. Ambiente de desenvolvimento

Ambiente principal recomendado:

- XAMPP;
- Apache;
- PHP 8+;
- MariaDB/MySQL;
- phpMyAdmin.

Estrutura local típica:

```text
C:\xampp\htdocs\ardetho\
```

O USBWebserver utilizado em aula poderá ser usado para testes de compatibilidade, mas não será o ambiente principal de desenvolvimento.

A aplicação deve evitar dependências desnecessárias que dificultem sua execução em outro ambiente PHP + MySQL/MariaDB.

---

## 4. Escopo funcional

O projeto será deliberadamente reduzido para permitir implementação, testes, documentação e apresentação adequados.

### Módulos obrigatórios

1. Login
2. Dashboard
3. Clientes
4. Produtos
5. Vendas

Outros módulos existentes na versão Web podem permanecer visualmente no menu como indisponíveis ou “Em desenvolvimento”, se isso for coerente com a interface atual.

Não desenvolver nesta etapa, salvo necessidade acadêmica:

- financeiro completo;
- compras;
- fornecedores;
- RH;
- estoque avançado;
- configurações complexas;
- multiempresa;
- permissões avançadas;
- relatórios complexos.

---

## 5. Login

O sistema deverá possuir autenticação obrigatória.

Estrutura mínima:

```text
usuarios
--------
id_usuario
nome
email
senha_hash
ativo
data_cadastro
```

Requisitos:

- autenticação com e-mail e senha;
- senha armazenada com hash seguro;
- uso de `password_hash()` e `password_verify()`;
- sessão PHP;
- páginas internas protegidas;
- logout;
- redirecionamento para login quando não houver sessão válida.

Neste estágio não é necessário implementar:

- recuperação de senha;
- autenticação multifator;
- múltiplos níveis complexos de permissão;
- cadastro público de usuários.

Pode existir inicialmente um usuário administrador criado pelo script SQL ou seed de instalação.

---

## 6. Arquitetura técnica

Tecnologias:

- PHP 8+;
- PDO;
- MySQL/MariaDB;
- HTML;
- CSS;
- JavaScript.

Não utilizar framework PHP nesta etapa.

Evitar Laravel, Symfony ou similares para manter explícitos os conceitos de:

- PHP;
- SQL;
- sessões;
- CRUD;
- relacionamentos;
- prepared statements;
- organização de código.

A estrutura pode seguir uma organização simples inspirada em MVC, sem necessidade de MVC rígido.

Exemplo:

```text
ardetho/
│
├── index.php
├── login.php
├── logout.php
├── dashboard.php
├── clientes.php
├── produtos.php
├── vendas.php
├── venda-nova.php
│
├── config/
│   └── database.php
│
├── controllers/
│   ├── AuthController.php
│   ├── ClienteController.php
│   ├── ProdutoController.php
│   └── VendaController.php
│
├── models/
│   ├── Usuario.php
│   ├── Cliente.php
│   ├── Produto.php
│   └── Venda.php
│
├── includes/
│   ├── auth.php
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
│
└── database/
    └── ardetho.sql
```

Esta estrutura ainda será validada após auditoria da PWA atual.

---

## 7. Banco de dados — estrutura conceitual inicial

### usuarios

```text
id_usuario
nome
email
senha_hash
ativo
data_cadastro
```

### clientes

```text
id_cliente
nome
cpf_cnpj
email
telefone
endereco
cidade
estado
data_cadastro
ativo
```

### produtos

```text
id_produto
nome
descricao
categoria
preco
estoque
ativo
data_cadastro
```

### vendas

```text
id_venda
id_cliente
data_venda
valor_total
status
```

### venda_itens

```text
id_item
id_venda
id_produto
quantidade
valor_unitario
subtotal
```

Relacionamentos principais:

```text
CLIENTE
   │
   └── VENDA
          │
          ├── ITEM ── PRODUTO
          ├── ITEM ── PRODUTO
          └── ITEM ── PRODUTO
```

---

## 8. Dashboard

O Dashboard deverá utilizar dados reais do banco.

Indicadores iniciais desejados:

- total de clientes;
- total de produtos;
- total de vendas;
- faturamento total ou do período.

Exemplos de consultas:

```sql
SELECT COUNT(*) FROM clientes;
SELECT COUNT(*) FROM produtos;
SELECT COUNT(*) FROM vendas;
SELECT SUM(valor_total) FROM vendas;
```

O objetivo é demonstrar claramente que operações realizadas nos módulos atualizam os indicadores do Dashboard.

---

## 9. Clientes

CRUD mínimo:

- listar;
- cadastrar;
- visualizar quando necessário;
- editar;
- excluir logicamente ou desativar.

Preferência por desativação quando houver dependências históricas.

---

## 10. Produtos

CRUD mínimo:

- listar;
- cadastrar;
- editar;
- excluir logicamente ou desativar.

Campos poderão ser ajustados após análise da versão Web atual.

---

## 11. Vendas

A venda deverá relacionar:

- cliente;
- um ou mais produtos;
- quantidade;
- valor unitário;
- subtotal;
- total;
- data;
- status.

O JavaScript poderá ser utilizado para cálculos e comportamento visual no frontend.

O PHP será responsável por:

- validação definitiva;
- persistência;
- transações;
- gravação de venda;
- gravação dos itens;
- leitura do banco.

A gravação de venda e itens deverá preferencialmente utilizar transação SQL.

---

## 12. Persistência e segurança

Utilizar exclusivamente PDO para conexão ao banco.

Requisitos:

- conexão centralizada;
- prepared statements;
- evitar SQL espalhado indiscriminadamente pelas páginas;
- validar dados no backend;
- escapar saída HTML quando necessário;
- proteger páginas internas por sessão;
- senhas nunca armazenadas em texto puro.

---

## 13. Reaproveitamento da PWA

A versão Web/PWA atual será a principal referência visual.

Preservar sempre que possível:

- identidade visual;
- layout;
- sidebar;
- cabeçalho;
- cards;
- tabelas;
- responsividade;
- estrutura de navegação;
- assets já consolidados;
- comportamento visual existente.

Não redesenhar o Ardetho durante a migração.

Princípio:

```text
Ardetho Web atual
+
PHP
+
SQL
```

e não:

```text
novo Ardetho criado do zero
```

---

## 14. Ordem de implementação

Ordem recomendada:

```text
ETAPA 0
Auditoria da PWA atual

ETAPA 1
Preparação da branch PHP + SQL e estrutura base

ETAPA 2
Banco de dados + conexão PDO

ETAPA 3
Login e proteção por sessão

ETAPA 4
Clientes

ETAPA 5
Produtos

ETAPA 6
Vendas

ETAPA 7
Dashboard com dados reais

ETAPA 8
Revisão, testes, responsividade e documentação
```

O Dashboard será desenvolvido depois dos módulos que fornecem seus dados.

---

## 15. Dinâmica de desenvolvimento com Codex

O Codex será utilizado como agente de implementação.

O ChatGPT será utilizado principalmente para:

- definir estratégia;
- preparar prompts;
- revisar relatórios do Codex;
- analisar mudanças;
- identificar riscos;
- definir a próxima etapa.

Fluxo:

```text
ChatGPT prepara prompt
        ↓
Codex executa
        ↓
Usuário envia resultado/relatório
        ↓
ChatGPT revisa
        ↓
Novo prompt
```

Evitar prompts excessivamente grandes como:

> “Transforme todo o Ardetho em PHP.”

Preferir tarefas pequenas e verificáveis.

Exemplos:

- auditar PWA;
- preparar estrutura;
- criar schema;
- criar autenticação;
- migrar Clientes;
- testar Clientes;
- migrar Produtos;
- implementar Vendas;
- integrar Dashboard.

---

## 16. Regra principal para o Codex

Toda implementação deverá seguir este princípio:

> Preserve a aparência e o comportamento visual da versão Web existente. Altere somente o necessário para integrar PHP, autenticação e banco de dados.

O Codex não deve:

- redesenhar telas sem necessidade;
- trocar a identidade visual;
- introduzir frameworks não solicitados;
- reescrever módulos fora do escopo;
- alterar arquivos desnecessariamente;
- implementar funcionalidades futuras por iniciativa própria.

---

## 17. Primeira atividade

A primeira atividade será uma auditoria da branch `pwa-review`.

Objetivo:

- compreender a estrutura atual;
- identificar páginas e módulos;
- localizar dependências de localStorage;
- localizar JavaScript compartilhado;
- identificar componentes reutilizáveis;
- mapear páginas que deverão virar PHP;
- identificar módulos que serão preservados apenas visualmente;
- propor uma estrutura PHP baseada no código real existente.

Nesta primeira atividade o Codex NÃO deverá alterar nenhum arquivo.

---

## 18. Checkpoint da infraestrutura PHP + SQL

A infraestrutura inicial de backend foi criada para a branch `php-sql`:

- schema SQL em `database/ardetho.sql`;
- conexão PDO centralizada em `config/database.php`;
- autenticação PHP com sessão em `includes/auth.php`;
- criação do primeiro administrador via CLI em `database/criar-admin.php`.

Importação local sugerida, a partir da raiz do projeto:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" --default-character-set=utf8mb4 --host=127.0.0.1 --port=3306 --user=root --execute="SOURCE database/ardetho.sql"
```

Criação do primeiro administrador, sem gravar senha no código:

```powershell
$env:ARDETHO_ADMIN_PASS = "informe-a-senha-somente-no-ambiente-local"
& "C:\xampp\php\php.exe" database\criar-admin.php --nome="Administrador" --email="admin@exemplo.local" --password-env=ARDETHO_ADMIN_PASS
Remove-Item Env:\ARDETHO_ADMIN_PASS
```

Servidor local temporário para testes funcionais:

```powershell
& "C:\xampp\php\php.exe" -S 127.0.0.1:8080
```

Nesta etapa, somente o login foi migrado para PDO + `usuarios` + `password_verify()` + `$_SESSION`.
Clientes, Produtos, Vendas e os indicadores do Dashboard continuam temporariamente usando `data.js`, `storage.js` e `localStorage`.

---

## 19. Checkpoint do modulo Clientes em PHP + SQL

O modulo Clientes foi migrado na branch `php-sql` para persistencia em MariaDB via PHP + PDO.

Escopo concluido nesta etapa:

- listagem em `clientes.php` alimentada pela tabela `clientes`;
- cadastro e edicao em `cliente-form.php` com POST, validacao backend e prepared statements;
- filtros de busca, status e cidade aplicados no SQL;
- desativacao e reativacao por alteracao logica do campo `status`;
- protecao CSRF nas acoes de escrita;
- saida HTML escapada com helper centralizado.

A migracao manteve os HTMLs originais da PWA como referencia, sem alterar `clients.html` ou `client-form.html`.

Produtos, Vendas, Dashboard e demais modulos continuam fora do escopo deste checkpoint e ainda deverao ser migrados em etapas futuras.

---

## 20. Checkpoint do modulo Produtos/Servicos em PHP + SQL

O modulo Produtos/Servicos foi migrado na branch `php-sql` para persistencia em MariaDB via PHP + PDO.

Escopo concluido nesta etapa:

- listagem em `produtos.php` alimentada pela tabela `produtos`;
- cadastro e edicao em `produto-form.php` com POST, validacao backend e prepared statements;
- filtros de busca, categoria e status aplicados no SQL;
- status cadastral persistido em `status`;
- status de estoque calculado a partir de `tipo_item`, `estoque` e `estoque_minimo`;
- desativacao e reativacao por alteracao logica do campo `status`;
- validacao de codigo unico com mensagem amigavel;
- protecao CSRF nas acoes de escrita;
- saida HTML escapada com helper centralizado.

A migracao manteve os HTMLs originais da PWA como referencia, sem alterar `products.html` ou `product-form.html`.

Vendas, Dashboard e demais modulos continuam fora do escopo deste checkpoint e ainda deverao ser migrados em etapas futuras.

---

## 21. Checkpoint do modulo Vendas em PHP + SQL

O modulo Vendas foi migrado na branch `php-sql` para persistencia em MariaDB via PHP + PDO.

Escopo concluido nesta etapa:

- listagem em `vendas.php` alimentada pelas tabelas `vendas`, `venda_itens`, `clientes` e `produtos`;
- cadastro e edicao em `venda-form.php` com POST, validacao backend e prepared statements;
- vendas com multiplos itens, persistidos em `venda_itens`;
- gravacao de `vendas` e `venda_itens` dentro de transacao;
- calculo server-side de subtotal e valor total, sem confiar nos valores enviados pelo navegador;
- snapshot de quantidade, valor unitario e subtotal no momento da venda;
- filtros de busca, status e cliente aplicados no SQL;
- cancelamento logico por alteracao do status para `Cancelado`;
- protecao CSRF nas acoes de escrita;
- saida HTML escapada com helper centralizado.

O estoque ainda nao e movimentado automaticamente por vendas.

A migracao manteve os HTMLs originais da PWA como referencia, sem alterar `sales.html` ou `sale-form.html`.

Dashboard continua temporariamente no legado/localStorage e deve ser a proxima etapa de migracao.

---

## 22. Checkpoint do Dashboard em PHP + SQL

O Dashboard foi migrado na branch `php-sql` para indicadores reais em MariaDB via PHP + PDO.

Escopo concluido nesta etapa:

- `dashboard.php` alimentado por consultas agregadas das tabelas `clientes`, `produtos` e `vendas`;
- cards principais com clientes ativos, produtos/servicos ativos, vendas validas e faturamento real;
- faturamento calculado por `SUM(vendas.valor_total)` excluindo vendas com status `Cancelado`;
- pedidos recentes carregados por SQL com `JOIN` em clientes;
- resumo real de vendas por status;
- grafico mensal alimentado por faturamento de vendas;
- blocos fora do escopo, como RH, indicados como em desenvolvimento;
- `dashboard.php` sem dependencia de `data.js`, `storage.js`, `dashboard.js`, `localStorage` ou `ardetho_app_data` para os dados principais.

O arquivo `dashboard.html` permanece preservado como fluxo legado/localStorage da PWA.

Com este checkpoint, o escopo principal PHP + SQL do projeto academico esta concluido para:

- Login;
- Dashboard;
- Clientes;
- Produtos/Servicos;
- Vendas.

---

## 23. Importacao one-time dos dados demonstrativos da PWA

Foi criada uma rotina CLI para importar os dados demonstrativos da PWA para o banco `ardetho_erp`:

```powershell
& "C:\xampp\php\php.exe" database\importar-dados-legados.php
```

A rotina le diretamente `assets/js/data.js`, sem executar JavaScript e sem usar `eval`, extraindo somente as secoes:

- `clients`;
- `products`;
- `sales`;
- `financial`.

Escopo importado:

- 5 clientes demonstrativos;
- 5 produtos demonstrativos;
- 3 servicos demonstrativos;
- 5 vendas demonstrativas;
- 5 itens de venda, um para cada venda legada.
- 6 lancamentos financeiros demonstrativos.

Estrategia:

- clientes sao identificados por CPF/CNPJ quando disponivel, com fallback por e-mail e nome;
- produtos e servicos sao identificados pelo campo `codigo`;
- vendas sao identificadas pelo campo `codigo`;
- lancamentos financeiros sao identificados pelo campo `codigo`;
- IDs legados (`CLI-*`, `PRD-*`, `SRV-*`, `SAL-*`) sao usados somente como mapa temporario da importacao, nunca gravados como IDs SQL;
- cada venda legada de item unico vira um registro em `vendas` e um registro correspondente em `venda_itens`;
- cada lancamento financeiro legado vira um registro em `financeiro`, com vinculo opcional para cliente e venda quando houver correspondencia;
- subtotais e totais sao validados por quantidade x valor unitario;
- produtos legados com status operacional, como `Disponível`, `Baixo estoque` e `Indisponível`, entram como status cadastral `Ativo`; o estado de estoque segue calculado por quantidade/minimo no modulo Produtos.

A importacao inteira roda dentro de uma transacao e e idempotente: execucoes repetidas ignoram registros ja existentes e nao duplicam dados.

Usuarios, senhas, empresas, relatorios, RH, configuracoes e demais modulos fora do escopo nao sao importados.

---

## 24. Checkpoint do modulo Financeiro em PHP + SQL

O modulo Financeiro foi migrado na branch `php-sql` para persistencia em MariaDB via PHP + PDO.

Escopo concluido nesta etapa:

- listagem em `financeiro.php` alimentada pela tabela `financeiro`, com vinculos opcionais a `clientes` e `vendas`;
- cadastro e edicao em `financeiro-form.php` com POST, validacao backend e prepared statements;
- suporte a receitas e despesas com codigo, descricao, categoria, valor, data de lancamento, vencimento, status, forma de pagamento e observacoes;
- filtro de busca, tipo, status e cliente aplicado no SQL;
- resumo financeiro com saldo atual, contas a receber, contas a pagar e vencimentos proximos;
- cancelamento logico por alteracao do status para `Cancelado`, sem exclusao fisica pela interface;
- relacionamento opcional com vendas por `id_venda`, usando FK `ON DELETE RESTRICT`;
- Dashboard atualizado para usar alertas e saldo financeiro reais;
- importador legado ampliado para importar somente a secao `financial`, mantendo CLI-only, transacao e idempotencia;
- protecao CSRF nas acoes de escrita;
- saida HTML escapada com helper centralizado.

A migracao manteve `financial.html` e `financial-form.html` preservados como referencia da PWA/localStorage.

RH, configuracoes e demais modulos permanecem fora do escopo.

---

## 25. Checkpoint do modulo Relatorios em PHP + SQL

O modulo Relatorios foi migrado na branch `php-sql` para uma tela principal PHP alimentada por consultas derivadas do MariaDB.

Escopo concluido nesta etapa:

- `relatorios.php` exige autenticacao PHP e nao usa `data.js`, `storage.js`, `localStorage` ou `ardetho_app_data`;
- dados derivados das tabelas reais `clientes`, `produtos`, `vendas`, `venda_itens` e `financeiro`, sem criacao de tabela `relatorios`;
- modelo `Relatorio.php` limitado a consultas de leitura e agregacao;
- controller `RelatorioController.php` responsavel por filtros GET, whitelist de periodos, preparacao dos dados, metricas e exportacao CSV;
- tipos efetivamente migrados conforme o legado: Desempenho comercial, Clientes ativos e inativos, Financeiro consolidado, Produtos e estoque;
- cards reais de receita total, saldo consolidado e pedidos concluidos;
- resumo analitico real com ticket medio, conversao comercial, pendencias financeiras e modulos em uso;
- tabela dos ultimos lancamentos financeiros alimentada pela tabela `financeiro`;
- filtro de periodo com as opcoes legadas: todos, hoje, ultimos 7 dias, ultimos 30 dias e este mes;
- exportacao CSV adaptada para os mesmos indicadores e ultimos lancamentos financeiros;
- saida HTML escapada com helper centralizado.

O arquivo `reports.html` permanece preservado como fluxo legado/localStorage da PWA.

Com este checkpoint, a aplicacao principal PHP + SQL cobre:

- Login;
- Dashboard;
- Clientes;
- Produtos/Servicos;
- Vendas;
- Financeiro;
- Relatorios.

RH e demais modulos permanecem fora do escopo.

---

## 26. Checkpoint dos modulos Perfil e Configuracoes em PHP + SQL

Os modulos Perfil e Configuracoes foram migrados na branch `php-sql` para a aplicacao PHP principal.

Escopo concluido nesta etapa:

- `perfil.php` exige autenticacao PHP e edita somente o usuario autenticado na tabela `usuarios`;
- `usuarios` recebeu os campos `cargo` e `departamento`, existentes no Perfil legado;
- alteracoes de `nome`, `email`, `cargo` e `departamento` usam POST, CSRF, validacao backend e prepared statements;
- alteracao de e-mail valida formato, respeita unicidade e atualiza a sessao apos sucesso;
- `configuracoes.php` persiste preferencias simples em tabela generica `configuracoes`;
- preferencias migradas: tema, sidebar compacta, atalhos do dashboard, alertas, resumo diario, idioma, formato de data, fuso horario, modulo inicial, modulo prioritario e visao inicial;
- identidade visual da empresa do Perfil legado foi separada conceitualmente e persistida em Configuracoes: nome da empresa, nome exibido, logo, icone e cores da marca;
- migration incremental `database/migrations/002_perfil_configuracoes.sql` criada e `database/ardetho.sql` atualizado para novas instalacoes;
- navegacao PHP atualizada para `perfil.php` e `configuracoes.php`;
- `profile.html` e `settings.html` permanecem preservados como fluxo legado/localStorage.

O Perfil legado nao possuia troca de senha; por isso a funcionalidade nao foi implementada nesta etapa.

Com este checkpoint, a aplicacao principal PHP + SQL cobre:

- Login;
- Dashboard;
- Clientes;
- Produtos/Servicos;
- Vendas;
- Financeiro;
- Relatorios;
- Perfil;
- Configuracoes.

RH e demais modulos permanecem fora do escopo.

---

## 27. Checkpoint PWA para aplicacao PHP + SQL

A aplicacao principal da branch `php-sql` agora e o fluxo PHP + MariaDB.

O `manifest.json` foi alinhado para iniciar a PWA em `./login.php`, preservando `scope: ./`, `display: standalone`, cores e icone existente. O login PHP passa a ser a entrada instalada segura: usuarios sem sessao permanecem no login e usuarios autenticados seguem o fluxo normal da aplicacao.

O `service-worker.js` foi ajustado para a arquitetura autenticada:

- paginas PHP e navegacoes (`request.mode === "navigate"`) usam rede diretamente;
- respostas `.php` nao sao armazenadas no Cache Storage;
- nao ha fallback de `index.html` para paginas autenticadas;
- logout, sessao expirada e falhas de servidor/banco nao devem exibir HTML autenticado antigo vindo do cache;
- cache PWA fica restrito a assets estaticos same-origin apropriados, como `manifest.json`, CSS, JavaScript e imagens em `assets/`;
- os HTMLs legados permanecem no repositorio como referencia historica, mas nao fazem parte do app shell precacheado.

O cache atual usa prefixo proprio `ardetho-erp-` e versao `ardetho-erp-v23`. Na ativacao, somente caches antigos com esse prefixo sao removidos.

---

## 28. Fluxo principal exclusivamente PHP

A aplicacao PHP + SQL e o unico fluxo funcional principal do Ardetho ERP. A navegacao autenticada, os formularios e os redirects dos modulos migrados apontam exclusivamente para rotas PHP.

As paginas institucionais publicas continuam estaticas em HTML, mas seus links de acesso ao sistema direcionam para `login.php`. O painel legado `erp-modules.html` deixou de ser exposto pela sidebar PHP porque depende da autenticacao simulada e dos dados em `localStorage`.

Os HTMLs e scripts antigos permanecem no repositorio apenas como legado e referencia historica. Eles podem ser abertos diretamente, mas nao fazem parte da navegacao normal da aplicacao PHP.
