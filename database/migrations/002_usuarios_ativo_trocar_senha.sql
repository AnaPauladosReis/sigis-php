-- Tarefa 3.2 do plano de trabalho: ativar/desativar usuarios e forcar troca de senha.
ALTER TABLE usuarios
  ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN trocar_senha TINYINT(1) NOT NULL DEFAULT 0;
