-- Tarefa 4.6 do plano de trabalho: origens das entradas como cadastro,
-- em vez de texto livre. Mantem entradas.origem por enquanto (so remover
-- depois de validar que tudo usa origem_id).

CREATE TABLE origens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  categoria VARCHAR(30) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

ALTER TABLE entradas ADD COLUMN origem_id INT NULL;

-- cria uma origem para cada texto distinto ja gravado em entradas.origem
INSERT INTO origens (nome, categoria)
SELECT DISTINCT origem, 'Outra' FROM entradas WHERE origem IS NOT NULL AND origem <> '';

UPDATE entradas e
JOIN origens o ON o.nome = e.origem
SET e.origem_id = o.id;

ALTER TABLE entradas ADD CONSTRAINT entradas_origem_fk FOREIGN KEY (origem_id) REFERENCES origens(id);
