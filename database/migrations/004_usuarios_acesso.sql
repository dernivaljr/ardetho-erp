USE ardetho_erp;

ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS perfil_acesso VARCHAR(20) NOT NULL DEFAULT 'Usuário' AFTER ativo,
  ADD COLUMN IF NOT EXISTS status VARCHAR(10) NOT NULL DEFAULT 'Ativo' AFTER perfil_acesso,
  ADD COLUMN IF NOT EXISTS trocar_senha TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

UPDATE usuarios SET status = 'Inativo' WHERE ativo = 0;

INSERT INTO usuarios (nome, email, senha_hash, ativo, perfil_acesso, status, trocar_senha)
SELECT 'Administrador', 'admin@ardetho.local',
       '$2y$10$mPDqnHUiXFlz/ci811CuJ.D/2Mt329.6Oi085jf6zn63EfkrnihVq',
       1, 'Administrador', 'Ativo', 0
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@ardetho.local');

UPDATE usuarios SET perfil_acesso = 'Administrador', status = 'Ativo', ativo = 1
WHERE email = 'admin@ardetho.local';
