-- Tarefa 4.1 do plano de trabalho: inativacao (nunca exclusao fisica, RN037).
ALTER TABLE grupos_impacto ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE itens ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE instituicoes ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1;
-- projetos.status ja e VARCHAR livre; passa a aceitar 'Ativo'/'Inativo'/'Encerrado'
-- (validado na aplicacao, sem mudanca de schema).
