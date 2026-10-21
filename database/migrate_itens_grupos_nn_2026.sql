-- Migracao aplicada em 2026-10-20 para levar um banco ja inicializado com o
-- schema.sql antigo ao estado atual (que um `docker compose up --build` do
-- zero ja cria direto via schema.sql). Adiciona projeto_id a itens, novos
-- grupos/projetos, remove os 7 itens de exemplo (e doacoes de teste
-- dependentes) e importa os 87 itens reais da planilha de controle de
-- doacoes (aba "Oficios de entrega").

ALTER TABLE itens ADD COLUMN projeto_id INT NULL AFTER grupo_id;
ALTER TABLE itens ADD CONSTRAINT itens_projeto_fk FOREIGN KEY (projeto_id) REFERENCES projetos(id);

INSERT INTO grupos_impacto (id, nome, cor) VALUES
(5, 'Alimentos', '#2E8B57'),
(6, 'Higiene', '#8E6FCE'),
(7, 'Limpeza', '#4FB0C6'),
(8, 'Material Escolar', '#D6A72B'),
(9, 'Brinquedos e Lazer', '#E0657D'),
(10, 'Diversos', '#98A2B3');

INSERT INTO projetos (id, nome, descricao, grupo_id, status) VALUES
(6, 'SESI Escola Moinho', 'Doações de material escolar, brinquedos e utensílios para a SESI Escola Moinho.', 9, 'Ativo'),
(7, 'SESI Lazer', 'Equipamentos e itens esportivos/recreativos para atividades de lazer do SESI.', 9, 'Ativo'),
(8, 'Vestuário', 'Roupas, calçados e itens de cama/banho para instituições atendidas.', 1, 'Ativo'),
(9, 'Alimentos', 'Doação de alimentos não perecíveis para instituições atendidas.', 5, 'Ativo'),
(10, 'Higiene, Multa Social e Gincana SESI', 'Itens de higiene pessoal arrecadados via multa social e gincanas SESI.', 6, 'Ativo'),
(11, 'Limpeza', 'Produtos de limpeza doados para instituições atendidas.', 7, 'Ativo'),
(12, 'Material Escolar', 'Material escolar diverso doado para instituições atendidas.', 8, 'Ativo'),
(13, 'Diversos', 'Itens diversos que não se encaixam em outras frentes (eletrodomésticos, utensílios de cozinha, itens sazonais).', 10, 'Ativo');

UPDATE projetos SET grupo_id = 9, descricao = 'Coleta de tampinhas plásticas para reciclagem e geração de renda social.' WHERE id = 2;
UPDATE projetos SET grupo_id = 1, descricao = 'Reaproveitamento de resíduos e sobras industriais de empresas parceiras (ex: Krona) em roupas e calçados.' WHERE id = 4;

-- remove dados de demonstracao/teste ligados aos 7 itens de exemplo
-- (inclui doacoes de teste 1, 2, 3 e 5, lancadas manualmente no app, confirmado com o usuario)
CREATE TEMPORARY TABLE doacoes_orfas AS
  SELECT d.id FROM doacoes d
  WHERE NOT EXISTS (
    SELECT 1 FROM doacao_itens di WHERE di.doacao_id = d.id AND di.item_id NOT BETWEEN 1 AND 7
  );

DELETE FROM doacao_itens WHERE doacao_id IN (SELECT id FROM doacoes_orfas);
DELETE FROM termos WHERE doacao_id IN (SELECT id FROM doacoes_orfas);
DELETE FROM doacoes WHERE id IN (SELECT id FROM doacoes_orfas);
DROP TEMPORARY TABLE doacoes_orfas;

DELETE FROM producoes WHERE item_consumido_id BETWEEN 1 AND 7;
DELETE FROM entradas WHERE item_id BETWEEN 1 AND 7;
DELETE FROM estoque WHERE item_id BETWEEN 1 AND 7;
DELETE FROM itens WHERE id BETWEEN 1 AND 7;
INSERT INTO itens (id, grupo_id, projeto_id, nome, unidade, peso_unitario, multiplicador) VALUES
  (1, 1, 1, 'TRAVESSEIRO grande', 'un', 1.750, 1.000),
  (2, 1, 1, 'Travesseiro viscose LEZ A LEZ', 'un', 2.000, 1.000),
  (3, 1, 1, 'Travesseiro Externo K', 'un', 2.000, 1.000),
  (4, 1, 1, 'Travesseiro Externo D', 'un', 2.000, 1.000),
  (5, 1, 1, 'TRAVESSEIRO PEQUENO', 'un', 0.500, 1.000),
  (6, 1, 1, 'TRAVESSEIRO LOMBAR/ dado', 'un', 2.500, 1.000),
  (7, 1, 1, 'MANTAS de Malha', 'un', 0.250, 2.000),
  (8, 1, 1, 'BOLSAS', 'un', 0.150, 2.000),
  (9, 1, 1, 'TOALHAS DE MESA', 'un', 1.200, 4.000),
  (10, 1, 1, 'NANINHAS', 'un', 1.500, 1.000),
  (11, 1, 1, 'FRALDAS de TECIDO', 'un', 0.800, 1.000),
  (12, 1, 1, 'Toalhas de banho (felpudo)', 'un', 0.300, 4.000),
  (13, 8, 1, 'CADERNOS ENCAPADOS', 'un', 0.400, 1.000),
  (14, 1, 1, 'Cachecol peludo', 'un', 0.100, 1.000),
  (15, 1, 1, 'cortinas de renda', 'un', 1.500, 4.000),
  (16, 1, 1, 'Edredon Karsten', 'un', 0.000, 2.000),
  (17, 1, 4, 'CALÇAS OU BERMUDAS ECONOMIA CIRCULAR KRONA', 'un', 0.000, 1.000),
  (18, 1, 4, 'BLUSAS OU BLUSÕES KRONA', 'un', 0.000, 1.000),
  (19, 1, 4, 'Roupas CAJ', 'un', 0.000, 1.000),
  (20, 1, 4, 'Uniformes SESI customizados', 'un', 0.000, 1.000),
  (21, 1, 4, 'CALÇADOS DE ECONOMIA CIRCULAR', 'par', 0.000, 1.000),
  (22, 9, 2, 'BOLAS TAMPINHAS DO BEM', 'un', 0.000, 3.000),
  (23, 9, 2, 'JOGO DE BADINGTON', 'un', 0.000, 4.000),
  (24, 9, 2, 'BAMBOLE TAMPINHAS DO BEM', 'un', 0.000, 3.000),
  (25, 9, 2, 'BARALHO OU JOGO UNO', 'un', 0.000, 4.000),
  (26, 9, 2, 'BOMBA DE ENCHER', 'un', 0.000, 4.000),
  (27, 9, 2, 'Bingo', 'un', 0.000, 20.000),
  (28, 9, 2, 'JOGO DE XADREZ', 'un', 0.000, 4.000),
  (29, 9, 2, 'TRAVE DE FUTEBOL', 'un', 0.000, 10.000),
  (30, 9, 6, 'LANCHEIRAS/estojos', 'un', 0.000, 1.000),
  (31, 9, 6, 'BOLSA DE BOL A', 'un', 0.000, 4.000),
  (32, 9, 6, 'BOLAS DIVERSA', 'un', 0.000, 4.000),
  (33, 2, 6, 'POTES PLÁSTICOS', 'un', 0.000, 1.000),
  (34, 2, 6, 'COPOS OU GARRAFAS', 'un', 0.000, 1.000),
  (35, 9, 6, 'BRINQUEDOS', 'un', 0.000, 4.000),
  (36, 9, 7, 'BASTÕES DE GINÁSTICA', 'un', 0.000, 4.000),
  (37, 3, 7, 'COLCHONETE', 'un', 0.000, 4.000),
  (38, 9, 7, 'BOLAS SESI EVA ou bocha', 'un', 0.000, 4.000),
  (39, 1, 8, 'LENÇOL COBERTA OU COBERTORES OU EDREDON', 'un', 0.000, 2.000),
  (40, 1, 8, 'TOALHAS FELPUDAS BANHO OU ROSTO', 'un', 0.000, 2.000),
  (41, 1, 8, 'TOUCAS LUVAS E CACHECOIS', 'un', 0.000, 1.000),
  (42, 1, 8, 'MEIAS', 'un', 0.000, 1.000),
  (43, 1, 8, 'ROUPAS', 'un', 0.000, 1.000),
  (44, 1, 8, 'CALÇADOS', 'par', 0.000, 1.000),
  (45, 1, 8, 'MOCHILAS, bolsas, lancheiras, penal ou acessórios', 'un', 0.000, 2.000),
  (46, 5, 9, 'LEITE', 'L', 0.000, 2.000),
  (47, 5, 9, 'LEITE EM PÓ', 'kg', 0.000, 2.000),
  (48, 5, 9, 'ALIMENTOS em kg', 'kg', 0.000, 4.000),
  (49, 5, 9, 'ACHOCOLATADO LATA', 'un', 0.000, 4.000),
  (50, 5, 9, 'BISCOITOS', 'un', 0.000, 2.000),
  (51, 5, 9, 'ÓLEO DE SOJA', 'L', 0.000, 4.000),
  (52, 6, 10, 'HASTES FLEXÍVEIS', 'un', 0.000, 0.100),
  (53, 6, 10, 'SABONETE', 'un', 0.000, 2.000),
  (54, 6, 10, 'FRALDAS DESCARTÁVEIS', 'un', 0.000, 0.100),
  (55, 6, 10, 'LENÇOS UMEDECIDOS', 'un', 0.000, 0.200),
  (56, 6, 10, 'SABONETE LIQUIDO', 'un', 0.000, 4.000),
  (57, 6, 10, 'HIDRATante/pomada', 'un', 0.000, 4.000),
  (58, 6, 10, 'ABSORVENTES', 'un', 0.000, 0.100),
  (59, 6, 10, 'PASTA DE DENTE', 'un', 0.000, 4.000),
  (60, 6, 10, 'ESCOVA DE DENTE', 'un', 0.000, 1.000),
  (61, 6, 10, 'FIO DENTAL', 'un', 0.000, 4.000),
  (62, 6, 10, 'TALCO', 'un', 0.000, 2.000),
  (63, 6, 10, 'Shampoo', 'un', 0.000, 4.000),
  (64, 6, 10, 'Condicionador', 'un', 0.000, 4.000),
  (65, 6, 10, 'Desodorante', 'un', 0.000, 2.000),
  (66, 6, 10, 'PAPEL HIGIENICO', 'un', 0.000, 4.000),
  (67, 6, 10, 'Enxaguante bucal', 'un', 0.000, 4.000),
  (68, 6, 10, 'APARELHO DE BARBEAR', 'un', 0.000, 2.000),
  (69, 7, 11, 'SABÃO EM PÓ', 'un', 0.000, 4.000),
  (70, 7, 11, 'ÁGUA SANITÁRIA LITRO', 'L', 0.000, 4.000),
  (71, 7, 11, 'sABÃO EM BARRA', 'un', 0.000, 4.000),
  (72, 7, 11, 'DESINFETANTE', 'un', 0.000, 4.000),
  (73, 7, 11, 'DETERGENTE DE LOUÇA', 'un', 0.000, 4.000),
  (74, 7, 11, 'ESPONJA', 'un', 0.000, 4.000),
  (75, 8, 12, 'TESOURAS', 'un', 0.000, 1.000),
  (76, 8, 12, 'Caixa de lápis de cor', 'un', 0.000, 1.000),
  (77, 8, 12, 'LÁPIS/canetas', 'un', 0.000, 1.000),
  (78, 8, 12, 'ÍTEM ESCOLAR DIVERSO', 'un', 0.000, 1.000),
  (79, 8, 12, 'CADERNOS', 'un', 0.000, 1.000),
  (80, 8, 12, 'LIVROS', 'un', 0.000, 2.000),
  (81, 8, 12, 'JOGOS DIDÁTICOS', 'un', 0.000, 4.000),
  (82, 10, 13, 'CHAVEIRO', 'un', 0.000, 1.000),
  (83, 3, 13, 'COLCHÃO INFLÁVEL', 'un', 0.000, 1.000),
  (84, 10, 13, 'ÍTENS DE COZINHA', 'un', 0.000, 4.000),
  (85, 4, 13, 'ELETRODOMESTICO', 'un', 0.000, 4.000),
  (86, 10, 13, 'LATAS DE PASCOA ou NATAL', 'un', 0.000, 1.000),
  (87, 5, 13, 'Ítens de Páscoa (chocolate)', 'kg', 0.000, 1.000);

INSERT INTO estoque (item_id, saldo, minimo) VALUES
  (1, 0.000, 0.000),
  (2, 0.000, 0.000),
  (3, 0.000, 0.000),
  (4, 0.000, 0.000),
  (5, 0.000, 0.000),
  (6, 0.000, 0.000),
  (7, 0.000, 0.000),
  (8, 0.000, 0.000),
  (9, 0.000, 0.000),
  (10, 0.000, 0.000),
  (11, 0.000, 0.000),
  (12, 0.000, 0.000),
  (13, 0.000, 0.000),
  (14, 0.000, 0.000),
  (15, 0.000, 0.000),
  (16, 0.000, 0.000),
  (17, 0.000, 0.000),
  (18, 0.000, 0.000),
  (19, 0.000, 0.000),
  (20, 0.000, 0.000),
  (21, 0.000, 0.000),
  (22, 0.000, 0.000),
  (23, 0.000, 0.000),
  (24, 0.000, 0.000),
  (25, 0.000, 0.000),
  (26, 0.000, 0.000),
  (27, 0.000, 0.000),
  (28, 0.000, 0.000),
  (29, 0.000, 0.000),
  (30, 0.000, 0.000),
  (31, 0.000, 0.000),
  (32, 0.000, 0.000),
  (33, 0.000, 0.000),
  (34, 0.000, 0.000),
  (35, 0.000, 0.000),
  (36, 0.000, 0.000),
  (37, 0.000, 0.000),
  (38, 0.000, 0.000),
  (39, 0.000, 0.000),
  (40, 0.000, 0.000),
  (41, 0.000, 0.000),
  (42, 0.000, 0.000),
  (43, 0.000, 0.000),
  (44, 0.000, 0.000),
  (45, 0.000, 0.000),
  (46, 0.000, 0.000),
  (47, 0.000, 0.000),
  (48, 0.000, 0.000),
  (49, 0.000, 0.000),
  (50, 0.000, 0.000),
  (51, 0.000, 0.000),
  (52, 0.000, 0.000),
  (53, 0.000, 0.000),
  (54, 0.000, 0.000),
  (55, 0.000, 0.000),
  (56, 0.000, 0.000),
  (57, 0.000, 0.000),
  (58, 0.000, 0.000),
  (59, 0.000, 0.000),
  (60, 0.000, 0.000),
  (61, 0.000, 0.000),
  (62, 0.000, 0.000),
  (63, 0.000, 0.000),
  (64, 0.000, 0.000),
  (65, 0.000, 0.000),
  (66, 0.000, 0.000),
  (67, 0.000, 0.000),
  (68, 0.000, 0.000),
  (69, 0.000, 0.000),
  (70, 0.000, 0.000),
  (71, 0.000, 0.000),
  (72, 0.000, 0.000),
  (73, 0.000, 0.000),
  (74, 0.000, 0.000),
  (75, 0.000, 0.000),
  (76, 0.000, 0.000),
  (77, 0.000, 0.000),
  (78, 0.000, 0.000),
  (79, 0.000, 0.000),
  (80, 0.000, 0.000),
  (81, 0.000, 0.000),
  (82, 0.000, 0.000),
  (83, 0.000, 0.000),
  (84, 0.000, 0.000),
  (85, 0.000, 0.000),
  (86, 0.000, 0.000),
  (87, 0.000, 0.000);

-- Padroniza a capitalizacao dos nomes (Title Case em pt-BR), preservando
-- siglas conhecidas (SESI, EVA, CAJ) e corrigindo acentos obvios faltantes
-- (ex.: Pascoa -> Páscoa, eletrodomestico -> eletrodoméstico).
UPDATE itens SET nome = 'Travesseiro Grande' WHERE id = 1;
UPDATE itens SET nome = 'Travesseiro Viscose Lez a Lez' WHERE id = 2;
UPDATE itens SET nome = 'Travesseiro Pequeno' WHERE id = 5;
UPDATE itens SET nome = 'Travesseiro Lombar/ Dado' WHERE id = 6;
UPDATE itens SET nome = 'Mantas de Malha' WHERE id = 7;
UPDATE itens SET nome = 'Bolsas' WHERE id = 8;
UPDATE itens SET nome = 'Toalhas de Mesa' WHERE id = 9;
UPDATE itens SET nome = 'Naninhas' WHERE id = 10;
UPDATE itens SET nome = 'Fraldas de Tecido' WHERE id = 11;
UPDATE itens SET nome = 'Toalhas de Banho (Felpudo)' WHERE id = 12;
UPDATE itens SET nome = 'Cadernos Encapados' WHERE id = 13;
UPDATE itens SET nome = 'Cachecol Peludo' WHERE id = 14;
UPDATE itens SET nome = 'Cortinas de Renda' WHERE id = 15;
UPDATE itens SET nome = 'Calças ou Bermudas Economia Circular Krona' WHERE id = 17;
UPDATE itens SET nome = 'Blusas ou Blusões Krona' WHERE id = 18;
UPDATE itens SET nome = 'Uniformes SESI Customizados' WHERE id = 20;
UPDATE itens SET nome = 'Calçados de Economia Circular' WHERE id = 21;
UPDATE itens SET nome = 'Bolas Tampinhas do Bem' WHERE id = 22;
UPDATE itens SET nome = 'Jogo de Badington' WHERE id = 23;
UPDATE itens SET nome = 'Bambole Tampinhas do Bem' WHERE id = 24;
UPDATE itens SET nome = 'Baralho ou Jogo Uno' WHERE id = 25;
UPDATE itens SET nome = 'Bomba de Encher' WHERE id = 26;
UPDATE itens SET nome = 'Jogo de Xadrez' WHERE id = 28;
UPDATE itens SET nome = 'Trave de Futebol' WHERE id = 29;
UPDATE itens SET nome = 'Lancheiras/Estojos' WHERE id = 30;
UPDATE itens SET nome = 'Bolsa de Bola' WHERE id = 31;
UPDATE itens SET nome = 'Bolas Diversa' WHERE id = 32;
UPDATE itens SET nome = 'Potes Plásticos' WHERE id = 33;
UPDATE itens SET nome = 'Copos ou Garrafas' WHERE id = 34;
UPDATE itens SET nome = 'Brinquedos' WHERE id = 35;
UPDATE itens SET nome = 'Bastões de Ginástica' WHERE id = 36;
UPDATE itens SET nome = 'Colchonete' WHERE id = 37;
UPDATE itens SET nome = 'Bolas SESI EVA ou Bocha' WHERE id = 38;
UPDATE itens SET nome = 'Lençol Coberta ou Cobertores ou Edredon' WHERE id = 39;
UPDATE itens SET nome = 'Toalhas Felpudas Banho ou Rosto' WHERE id = 40;
UPDATE itens SET nome = 'Toucas Luvas e Cachecois' WHERE id = 41;
UPDATE itens SET nome = 'Meias' WHERE id = 42;
UPDATE itens SET nome = 'Roupas' WHERE id = 43;
UPDATE itens SET nome = 'Calçados' WHERE id = 44;
UPDATE itens SET nome = 'Mochilas, Bolsas, Lancheiras, Penal ou Acessórios' WHERE id = 45;
UPDATE itens SET nome = 'Leite' WHERE id = 46;
UPDATE itens SET nome = 'Leite em Pó' WHERE id = 47;
UPDATE itens SET nome = 'Alimentos em Kg' WHERE id = 48;
UPDATE itens SET nome = 'Achocolatado Lata' WHERE id = 49;
UPDATE itens SET nome = 'Biscoitos' WHERE id = 50;
UPDATE itens SET nome = 'Óleo de Soja' WHERE id = 51;
UPDATE itens SET nome = 'Hastes Flexíveis' WHERE id = 52;
UPDATE itens SET nome = 'Sabonete' WHERE id = 53;
UPDATE itens SET nome = 'Fraldas Descartáveis' WHERE id = 54;
UPDATE itens SET nome = 'Lenços Umedecidos' WHERE id = 55;
UPDATE itens SET nome = 'Sabonete Líquido' WHERE id = 56;
UPDATE itens SET nome = 'Hidratante/Pomada' WHERE id = 57;
UPDATE itens SET nome = 'Absorventes' WHERE id = 58;
UPDATE itens SET nome = 'Pasta de Dente' WHERE id = 59;
UPDATE itens SET nome = 'Escova de Dente' WHERE id = 60;
UPDATE itens SET nome = 'Fio Dental' WHERE id = 61;
UPDATE itens SET nome = 'Talco' WHERE id = 62;
UPDATE itens SET nome = 'Papel Higiênico' WHERE id = 66;
UPDATE itens SET nome = 'Enxaguante Bucal' WHERE id = 67;
UPDATE itens SET nome = 'Aparelho de Barbear' WHERE id = 68;
UPDATE itens SET nome = 'Sabão em Pó' WHERE id = 69;
UPDATE itens SET nome = 'Água Sanitária Litro' WHERE id = 70;
UPDATE itens SET nome = 'Sabão em Barra' WHERE id = 71;
UPDATE itens SET nome = 'Desinfetante' WHERE id = 72;
UPDATE itens SET nome = 'Detergente de Louça' WHERE id = 73;
UPDATE itens SET nome = 'Esponja' WHERE id = 74;
UPDATE itens SET nome = 'Tesouras' WHERE id = 75;
UPDATE itens SET nome = 'Caixa de Lápis de Cor' WHERE id = 76;
UPDATE itens SET nome = 'Lápis/Canetas' WHERE id = 77;
UPDATE itens SET nome = 'Item Escolar Diverso' WHERE id = 78;
UPDATE itens SET nome = 'Cadernos' WHERE id = 79;
UPDATE itens SET nome = 'Livros' WHERE id = 80;
UPDATE itens SET nome = 'Jogos Didáticos' WHERE id = 81;
UPDATE itens SET nome = 'Chaveiro' WHERE id = 82;
UPDATE itens SET nome = 'Colchão Inflável' WHERE id = 83;
UPDATE itens SET nome = 'Itens de Cozinha' WHERE id = 84;
UPDATE itens SET nome = 'Eletrodoméstico' WHERE id = 85;
UPDATE itens SET nome = 'Latas de Páscoa ou Natal' WHERE id = 86;
UPDATE itens SET nome = 'Itens de Páscoa (Chocolate)' WHERE id = 87;
