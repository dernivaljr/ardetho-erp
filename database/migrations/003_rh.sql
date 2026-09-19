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
