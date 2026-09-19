CREATE DATABASE IF NOT EXISTS ardetho_erp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ardetho_erp;

CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  cargo VARCHAR(100) NULL,
  departamento VARCHAR(100) NULL,
  senha_hash VARCHAR(255) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_login DATETIME NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
  id_cliente INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo_pessoa VARCHAR(2) NOT NULL DEFAULT 'PJ',
  status VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  nome VARCHAR(160) NULL,
  cpf VARCHAR(20) NULL,
  rg VARCHAR(30) NULL,
  data_nascimento DATE NULL,
  razao_social VARCHAR(180) NULL,
  nome_fantasia VARCHAR(160) NULL,
  cnpj VARCHAR(24) NULL,
  inscricao_estadual VARCHAR(40) NULL,
  contato VARCHAR(120) NULL,
  email VARCHAR(190) NULL,
  email_nf VARCHAR(190) NULL,
  telefone VARCHAR(30) NULL,
  whatsapp VARCHAR(30) NULL,
  cep VARCHAR(12) NULL,
  logradouro VARCHAR(180) NULL,
  numero VARCHAR(30) NULL,
  complemento VARCHAR(120) NULL,
  bairro VARCHAR(100) NULL,
  cidade VARCHAR(100) NULL,
  estado CHAR(2) NULL,
  observacoes TEXT NULL,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_cliente),
  KEY idx_clientes_cpf (cpf),
  KEY idx_clientes_cnpj (cnpj)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
  id_produto INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo_item VARCHAR(20) NOT NULL DEFAULT 'Produto',
  codigo VARCHAR(40) NOT NULL,
  nome VARCHAR(160) NOT NULL,
  categoria VARCHAR(100) NULL,
  descricao TEXT NULL,
  preco DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  unidade VARCHAR(30) NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  estoque DECIMAL(12,3) NULL,
  estoque_minimo DECIMAL(12,3) NULL,
  marca VARCHAR(100) NULL,
  fornecedor VARCHAR(120) NULL,
  ncm VARCHAR(20) NULL,
  prazo_estimado VARCHAR(60) NULL,
  departamento VARCHAR(100) NULL,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_produto),
  UNIQUE KEY uq_produtos_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vendas (
  id_venda INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(40) NULL,
  id_cliente INT UNSIGNED NOT NULL,
  data_venda DATE NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Em análise',
  forma_pagamento VARCHAR(40) NULL,
  condicao_pagamento VARCHAR(80) NULL,
  valor_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  observacoes TEXT NULL,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_venda),
  KEY idx_vendas_codigo (codigo),
  KEY idx_vendas_id_cliente (id_cliente),
  CONSTRAINT fk_vendas_clientes
    FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venda_itens (
  id_item INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_venda INT UNSIGNED NOT NULL,
  id_produto INT UNSIGNED NOT NULL,
  quantidade DECIMAL(12,3) NOT NULL,
  valor_unitario DECIMAL(12,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id_item),
  KEY idx_venda_itens_id_venda (id_venda),
  KEY idx_venda_itens_id_produto (id_produto),
  CONSTRAINT fk_venda_itens_vendas
    FOREIGN KEY (id_venda) REFERENCES vendas (id_venda)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_venda_itens_produtos
    FOREIGN KEY (id_produto) REFERENCES produtos (id_produto)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS financeiro (
  id_financeiro INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(40) NOT NULL,
  tipo VARCHAR(20) NOT NULL,
  descricao VARCHAR(180) NOT NULL,
  categoria VARCHAR(100) NULL,
  valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  data_lancamento DATE NOT NULL,
  data_vencimento DATE NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pendente',
  forma_pagamento VARCHAR(40) NOT NULL,
  id_cliente INT UNSIGNED NULL,
  id_venda INT UNSIGNED NULL,
  observacoes TEXT NULL,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_financeiro),
  UNIQUE KEY uq_financeiro_codigo (codigo),
  KEY idx_financeiro_tipo (tipo),
  KEY idx_financeiro_status (status),
  KEY idx_financeiro_data_vencimento (data_vencimento),
  KEY idx_financeiro_id_cliente (id_cliente),
  KEY idx_financeiro_id_venda (id_venda),
  CONSTRAINT fk_financeiro_clientes
    FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
  CONSTRAINT fk_financeiro_vendas
    FOREIGN KEY (id_venda) REFERENCES vendas (id_venda)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS funcionarios (
  id_funcionario INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome_completo VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  telefone VARCHAR(30) NULL,
  cargo VARCHAR(120) NOT NULL,
  departamento VARCHAR(100) NOT NULL,
  salario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  data_admissao DATE NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  observacoes TEXT NULL,
  data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_funcionario),
  KEY idx_funcionarios_email (email),
  KEY idx_funcionarios_status (status),
  KEY idx_funcionarios_departamento (departamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracoes (
  chave VARCHAR(80) NOT NULL,
  valor TEXT NOT NULL,
  data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
