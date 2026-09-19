# Ardetho ERP

O Ardetho ERP e um projeto academico de gestao empresarial. A versao de entrega usa PHP e MariaDB para autenticacao e dados, com entrada em `login.php`.

## Tecnologias e ambiente validado

- PHP 8.2.12, Apache 2.4.58 e MariaDB 10.4.32 no XAMPP para Windows.
- PDO com `pdo_mysql`, sessoes PHP, HTML, CSS e JavaScript sem framework.

O codigo usa propriedades `readonly`, que exigem PHP 8.1 ou superior. O teste de instalacao foi feito em PHP 8.2.12; outras combinacoes de versoes nao foram validadas. Confira se `PDO` e `pdo_mysql` estao habilitados no PHP do Apache e no PHP CLI. O projeto tambem usa extensoes comuns do PHP, como `json`, `filter` e `session`. Nao ha etapa de Composer ou `.env`.

## Estrutura

| Caminho | Funcao |
| --- | --- |
| `controllers/` | Validacao, regras e fluxo das requisicoes. |
| `models/` | Consultas e persistencia com PDO. |
| `includes/` | Sessao, autenticacao, CSRF, mensagens e partes compartilhadas das views. |
| `config/` | Conexao com o banco. |
| `database/` | Schema completo, migrations incrementais, dados demonstrativos e utilitarios CLI. |
| `assets/` | CSS, JavaScript e imagens. |
| `docs/` | Contexto da migracao e documentacao historica. |

As paginas `.php` da raiz apresentam a interface. O fluxo atual inclui login, Dashboard, Clientes, Produtos/Servicos, Vendas, Financeiro, Relatorios, RH, Perfil e Configuracoes. Compras, Fornecedores e outros modulos futuros nao estao implementados.

## Instalacao no XAMPP

1. Copie ou clone o projeto para `C:\xampp\htdocs\ardetho-erp` (ou outro nome de subdiretorio).
2. Inicie Apache e MySQL/MariaDB no XAMPP.
3. No PowerShell, entre na pasta do projeto e importe o schema completo:

   ```powershell
   Set-Location C:\xampp\htdocs\ardetho-erp
   Get-Content -Raw -Encoding UTF8 database\ardetho.sql | & C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root
   ```

   O arquivo `database/ardetho.sql` executa `CREATE DATABASE IF NOT EXISTS ardetho_erp` e `USE ardetho_erp`, depois cria as tabelas. Use-o para um banco novo; ele nao limpa dados de um banco existente. Se o usuario do MariaDB exigir senha, use as opcoes apropriadas do cliente `mysql` sem colocar a senha no comando ou no repositorio.
4. Crie o primeiro usuario com o utilitario CLI. Escolha seu proprio nome, e-mail e senha; a senha precisa ter pelo menos 8 caracteres. Este exemplo solicita a senha sem exibi-la e remove a variavel temporaria ao terminar:

   ```powershell
   $senhaAdmin = Read-Host 'Senha do administrador (minimo 8 caracteres)' -AsSecureString
   $env:ARDETHO_ADMIN_PASS = [System.Net.NetworkCredential]::new('', $senhaAdmin).Password
   try {
       & C:\xampp\php\php.exe database\criar-admin.php --nome='Administrador' --email='voce@exemplo.com' --password-env=ARDETHO_ADMIN_PASS
   } finally {
       Remove-Item Env:\ARDETHO_ADMIN_PASS -ErrorAction SilentlyContinue
       Remove-Variable senhaAdmin -ErrorAction SilentlyContinue
   }
   ```

   O script usa `password_hash`; escolha um e-mail seu no lugar do exemplo. Nao existe credencial padrao fixa para a aplicacao PHP.
5. Abra [http://localhost/ardetho-erp/](http://localhost/ardetho-erp/) (redireciona para `login.php`) e entre com o usuario criado. O acesso direto por [login.php](http://localhost/ardetho-erp/login.php) tambem funciona.

Se a pasta em `htdocs` tiver outro nome, ajuste somente a URL. O teste fresh foi executado em um subdiretorio do Apache, nao apenas no servidor embutido do PHP.

### Conexao com o banco

`config/database.php` usa estes valores locais por padrao:

| Variavel | Padrao |
| --- | --- |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_NAME` | `ardetho_erp` |
| `DB_USER` | `root` |
| `DB_PASS` | senha vazia |

As variaveis de ambiente substituem os defaults no processo PHP que executa o codigo. Para outro banco/usuario, configure-as tanto no ambiente do Apache quanto no terminal usado pelos utilitarios CLI e reinicie o Apache depois de alterar seu ambiente. Nao basta definir `DB_NAME` em um terminal separado enquanto o Apache ja esta em execucao. O schema SQL, por sua vez, fixa o nome `ardetho_erp`; importar em outro nome exige adaptar essa selecao de banco na copia usada para a importacao.

### Schema novo versus migrations

`database/ardetho.sql` ja contem o schema final completo: `usuarios`, `clientes`, `produtos`, `vendas`, `venda_itens`, `financeiro`, `funcionarios` e `configuracoes`. **Em instalacao nova, nao execute as migrations depois dele.**

Os arquivos de `database/migrations/` servem apenas para atualizar instalacoes antigas, conforme o ponto de partida: `001_financeiro.sql`, `002_perfil_configuracoes.sql` e `003_rh.sql`. As migrations `001` e `002` selecionam `ardetho_erp` explicitamente; `003` usa o banco selecionado na conexao. Nao ha runner automatico de migrations.

### Dados demonstrativos (opcional)

O ERP funciona sem seed. Para importar a massa historica da antiga PWA depois de criar o schema, execute:

```powershell
& C:\xampp\php\php.exe database\importar-dados-legados.php
```

O importador e CLI-only, le `database/seed-demo.json` e insere clientes, produtos/servicos, vendas e itens, lancamentos financeiros e funcionarios. O arquivo nao contem usuarios nem senhas. A importacao e opcional e idempotente: uma segunda execucao ignora os registros ja importados. Nao a use para substituir dados reais.

## Arquitetura e seguranca

Models consultam o MariaDB com PDO; controllers validam entradas e coordenam o fluxo; paginas PHP apresentam as views. A autenticacao usa `password_hash`/`password_verify` e sessao PHP. Formularios mutaveis usam token CSRF; consultas usam prepared statements; a saida HTML e escapada. Isso descreve mecanismos existentes, nao uma garantia de seguranca completa.

## Versao de entrega e historico

A interface e servida por rotas PHP; servidor e banco precisam estar disponiveis. Esta versao nao registra Service Worker, nao oferece instalacao como PWA e nao funciona offline. Os arquivos HTML e scripts de LocalStorage da fase Web/PWA nao fazem parte desta entrega. Sua historia permanece nos commits e branches do Git; documentos historicos em `docs/` descrevem etapas anteriores, nao rotas atuais.
