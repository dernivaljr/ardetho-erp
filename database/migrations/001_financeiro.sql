USE ardetho_erp;

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
