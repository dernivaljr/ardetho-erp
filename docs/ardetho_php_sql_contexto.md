# Ardetho ERP — Contexto da Migração para PHP + SQL

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
