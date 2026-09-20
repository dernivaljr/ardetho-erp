# Ardetho ERP

Um sistema ERP acadêmico desenvolvido em PHP e MariaDB para centralizar operações administrativas e comerciais.

## Funcionalidades

- Autenticação de usuários e Dashboard com indicadores da operação.
- Cadastro, gestão, busca e filtros de Clientes e de Produtos e Serviços.
- Vendas com múltiplos itens, cálculo de valores e acompanhamento de status.
- Lançamentos financeiros, vencimentos e saldos.
- Relatórios com filtros e exportação CSV.
- Cadastro de colaboradores em Recursos Humanos.
- Perfil do usuário e Configurações do sistema.

## Interface

**Dashboard**

![Dashboard com indicadores do Ardetho ERP](docs/images/dashboard.png)

**Produtos e serviços**

![Listagem de produtos e serviços](docs/images/produtos.png)

**Venda com múltiplos itens**

![Itens e valor total de uma venda](docs/images/venda-itens.png)

**Financeiro**

![Indicadores e lançamentos financeiros](docs/images/financeiro.png)

**Visualização móvel**

![Dashboard em tela móvel](docs/images/mobile.png)

As telas mostram apenas dados demonstrativos.

## Tecnologias

PHP 8.1 ou superior, MariaDB/MySQL, PDO, Apache, HTML5, CSS3 e JavaScript. O ambiente validado usa XAMPP para Windows com PHP 8.2.12, Apache 2.4.58 e MariaDB 10.4.32.

## Estrutura do projeto

```text
ardetho-erp/
|-- assets/
|   |-- css/
|   |-- images/
|   `-- js/
|-- config/
|-- controllers/
|-- database/
|   |-- ardetho.sql
|   |-- criar-admin.php
|   |-- importar-dados-demo.php
|   |-- seed-demo.json
|   `-- migrations/
|-- docs/
|   `-- images/
|-- includes/
|-- models/
|-- index.php
|-- login.php
|-- dashboard.php
|-- clientes.php
|-- produtos.php
|-- vendas.php
|-- financeiro.php
|-- relatorios.php
|-- rh.php
|-- perfil.php
`-- configuracoes.php
```

As páginas e formulários PHP ficam na raiz. `models/` acessa os dados, `controllers/` trata as requisições, `includes/` reúne componentes e recursos compartilhados, e `config/` define a conexão com o banco. `assets/` contém a interface; `database/` contém o esquema e os utilitários de instalação.

## Como executar

### Requisitos

Instale o XAMPP com Apache, MariaDB/MySQL, phpMyAdmin e PHP 8.1+ com `PDO` e `pdo_mysql` habilitados. Não é necessário instalar dependências com Composer.

### 1. Copiar o projeto

Copie ou clone o projeto para `C:\xampp\htdocs\ardetho-erp`.

### 2. Iniciar o XAMPP

No XAMPP Control Panel, inicie **Apache** e **MySQL**.

### 3. Criar o banco de dados

Abra [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/). Em **Bancos de dados**, crie `ardetho_erp`. Se houver opção de colação, escolha `utf8mb4_unicode_ci`.

### 4. Importar o esquema

1. Selecione `ardetho_erp` na barra lateral do phpMyAdmin.
2. Abra a aba **Importar** desse banco.
3. Selecione `database/ardetho.sql` na pasta do projeto e clique em **Importar**.

O SQL cria as oito tabelas necessárias. Use um banco novo; não execute `database/migrations/` após importar o esquema completo.

### 5. Criar o primeiro administrador

Abra o PowerShell na pasta do projeto e execute, substituindo o e-mail e a senha do exemplo:

```powershell
Set-Location C:\xampp\htdocs\ardetho-erp
$env:ARDETHO_ADMIN_PASS = "SuaSenhaAqui"
& "C:\xampp\php\php.exe" database\criar-admin.php --nome="Administrador" --email="admin@exemplo.com" --password-env=ARDETHO_ADMIN_PASS
Remove-Item Env:\ARDETHO_ADMIN_PASS
```

A senha deve ter pelo menos 8 caracteres. O script armazena seu hash; não existe usuário ou senha padrão. A última linha remove a variável de ambiente temporária.

### 6. Dados demonstrativos (opcional)

Para visualizar o sistema já preenchido com dados de exemplo, execute na mesma pasta:

```powershell
& "C:\xampp\php\php.exe" database\importar-dados-demo.php
```

O importador usa `database/seed-demo.json`, não cria usuários e pode ser executado novamente sem duplicar os registros importados. O sistema também funciona sem esses dados.

### 7. Acessar o sistema

Abra [http://localhost/ardetho-erp/](http://localhost/ardetho-erp/). A raiz redireciona para `login.php`; entre com o administrador criado no passo 5. Se você escolheu outro nome para a pasta em `htdocs`, ajuste a URL.

## Teste rápido

Após entrar, abra o Dashboard, cadastre um Cliente e um Produto ou Serviço, crie uma Venda e volte ao Dashboard para conferir os indicadores. Em seguida, visite Financeiro e Relatórios. Se importou os dados demonstrativos, você também pode navegar pelos módulos já preenchidos.

## Arquitetura

Os Models consultam e gravam no MariaDB por PDO. Os Controllers validam os dados e coordenam o fluxo. As páginas PHP exibem as Views; `includes/` fornece autenticação, sessão, proteção CSRF e partes compartilhadas da interface.

## Segurança implementada

O projeto utiliza `password_hash`/`password_verify`, sessão PHP com regeneração do ID no login, tokens CSRF nos formulários, consultas preparadas com PDO e escape de dados exibidos em HTML. Status como inativação e cancelamento preservam registros quando aplicável. Esses recursos não substituem uma avaliação de segurança para uso em produção.

## Banco de dados

`database/ardetho.sql` é o esquema completo para uma instalação nova. `database/migrations/` contém atualizações incrementais para bancos de etapas anteriores e **não** deve ser executado depois do esquema completo.

### Configuração avançada do banco

Em um XAMPP padrão, `config/database.php` usa `127.0.0.1:3306`, banco `ardetho_erp`, usuário `root` e senha vazia. Para outra configuração, defina `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS` no ambiente do Apache e dos utilitários PHP CLI.

## Observações

Este é um projeto acadêmico. O acesso depende de Apache e MariaDB ativos; os dados são persistidos no banco, não no navegador.
