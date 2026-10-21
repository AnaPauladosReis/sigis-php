-- =========================================================
-- SIGIS - Dados de teste (seed)
-- Preenche o estoque com entradas de materiais e registra
-- algumas doacoes de exemplo, para permitir testar o fluxo
-- de Direcionamento de Doacoes sem depender da planilha real.
-- Idempotente apenas se as tabelas entradas/doacoes estiverem
-- vazias (reexecutar duplicaria os dados).
-- =========================================================

USE sigis;

-- ---------------------------------------------------------
-- Entradas de teste: estoque inicial para todos os itens
-- ---------------------------------------------------------
INSERT INTO entradas (data_entrada, origem, projeto_id, item_id, quantidade, peso, criado_por)
SELECT '2026-07-01', 'Entrada de teste (seed)', projeto_id, id, 60, NULL, 1
FROM itens;

UPDATE estoque e
JOIN (
  SELECT item_id, SUM(quantidade) AS qtd
  FROM entradas
  WHERE origem = 'Entrada de teste (seed)'
  GROUP BY item_id
) x ON x.item_id = e.item_id
SET e.saldo = e.saldo + x.qtd;

-- ---------------------------------------------------------
-- Doacoes de teste (Direcionamento de Doacoes a instituicoes)
-- ---------------------------------------------------------
-- IDs nao sao fixos (auto_increment pode ja estar avancado), entao
-- capturamos o primeiro id gerado com LAST_INSERT_ID() e usamos offsets.
INSERT INTO doacoes (instituicao_id, projeto_id, data_doacao, vidas_impactadas, criado_por) VALUES
(1, 1, '2026-08-15', 49.0, 1),
(1, 9, '2026-08-28', 160.0, 1),
(2, 10, '2026-09-05', 183.0, 1),
(2, 11, '2026-09-12', 120.0, 1),
(3, 6, '2026-09-20', 42.0, 1),
(3, 12, '2026-09-28', 55.0, 1),
(4, 12, '2026-10-05', 85.0, 1),
(4, 8, '2026-10-12', 22.0, 1);

SET @d0 = LAST_INSERT_ID();

INSERT INTO doacao_itens (doacao_id, item_id, quantidade) VALUES
(@d0+0, 1, 5), (@d0+0, 9, 3), (@d0+0, 12, 8),
(@d0+1, 46, 20), (@d0+1, 48, 30),
(@d0+2, 53, 50), (@d0+2, 58, 30), (@d0+2, 63, 20),
(@d0+3, 69, 15), (@d0+3, 71, 15),
(@d0+4, 30, 10), (@d0+4, 35, 8),
(@d0+5, 76, 15), (@d0+5, 79, 20), (@d0+5, 80, 10),
(@d0+6, 79, 25), (@d0+6, 80, 30),
(@d0+7, 43, 12), (@d0+7, 44, 10);

UPDATE estoque e
JOIN (SELECT item_id, SUM(quantidade) AS q FROM doacao_itens GROUP BY item_id) x ON x.item_id = e.item_id
SET e.saldo = e.saldo - x.q;

-- termos de doacao (um por doacao, numeracao NNN/AAAA)
INSERT INTO termos (doacao_id, numero, emitido_em) VALUES
(@d0+0, '001/2026', '2026-08-15'),
(@d0+1, '002/2026', '2026-08-28'),
(@d0+2, '003/2026', '2026-09-05'),
(@d0+3, '004/2026', '2026-09-12'),
(@d0+4, '005/2026', '2026-09-20'),
(@d0+5, '006/2026', '2026-09-28'),
(@d0+6, '007/2026', '2026-10-05'),
(@d0+7, '008/2026', '2026-10-12');
