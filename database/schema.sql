-- =========================================================
-- SIGIS - Sistema Integrado de Gestao de Impacto Social,
-- Producao Social e Doacoes
-- Banco de dados MySQL
-- =========================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS sigis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigis;

-- ---------------------------------------------------------
-- Usuarios (login)
-- ---------------------------------------------------------
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  perfil VARCHAR(50) NOT NULL DEFAULT 'Administrador',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- senha: gersc123
INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES
('Administrador GERSC', 'admin@gersc.org.br', '$2b$10$7rUgaHesmfHwsVdCbHc28.IsPv2BCkcco4N7koVrWhuE8ZbjFEiRe', 'Administrador');

-- ---------------------------------------------------------
-- Grupos de impacto (taxonomia por tipo de material/produto)
-- ---------------------------------------------------------
CREATE TABLE grupos_impacto (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cor VARCHAR(7) NOT NULL DEFAULT '#2B5FAD'
) ENGINE=InnoDB;

INSERT INTO grupos_impacto (id, nome, cor) VALUES
(1, 'Têxteis', '#2B5FAD'),
(2, 'Plásticos', '#5B85C4'),
(3, 'Móveis e Madeira', '#C1662F'),
(4, 'Eletrônicos', '#8FA8D6'),
(5, 'Alimentos', '#2E8B57'),
(6, 'Higiene', '#8E6FCE'),
(7, 'Limpeza', '#4FB0C6'),
(8, 'Material Escolar', '#D6A72B'),
(9, 'Brinquedos e Lazer', '#E0657D'),
(10, 'Diversos', '#98A2B3');

-- ---------------------------------------------------------
-- Projetos sociais (frentes de doacao)
-- ---------------------------------------------------------
CREATE TABLE projetos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  descricao TEXT,
  grupo_id INT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Ativo',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (grupo_id) REFERENCES grupos_impacto(id)
) ENGINE=InnoDB;

INSERT INTO projetos (id, nome, descricao, grupo_id, status) VALUES
(1, 'Retalhos do Bem', 'Reaproveitamento de tecidos e retalhos industriais para produção de naninhas, almofadas e brinquedos.', 1, 'Ativo'),
(2, 'Tampinhas do Bem', 'Coleta de tampinhas plásticas para reciclagem e geração de renda social.', 9, 'Ativo'),
(3, 'Troca Solidária', 'Troca de materiais recicláveis por itens de necessidade básica com parceiros locais.', 1, 'Ativo'),
(4, 'Economia Circular Industrial', 'Reaproveitamento de resíduos e sobras industriais de empresas parceiras (ex: Krona) em roupas e calçados.', 1, 'Ativo'),
(5, 'Campanha do Agasalho 2025', 'Arrecadação sazonal de roupas e cobertores para famílias em situação de vulnerabilidade.', 1, 'Encerrado'),
(6, 'SESI Escola Moinho', 'Doações de material escolar, brinquedos e utensílios para a SESI Escola Moinho.', 9, 'Ativo'),
(7, 'SESI Lazer', 'Equipamentos e itens esportivos/recreativos para atividades de lazer do SESI.', 9, 'Ativo'),
(8, 'Vestuário', 'Roupas, calçados e itens de cama/banho para instituições atendidas.', 1, 'Ativo'),
(9, 'Alimentos', 'Doação de alimentos não perecíveis para instituições atendidas.', 5, 'Ativo'),
(10, 'Higiene, Multa Social e Gincana SESI', 'Itens de higiene pessoal arrecadados via multa social e gincanas SESI.', 6, 'Ativo'),
(11, 'Limpeza', 'Produtos de limpeza doados para instituições atendidas.', 7, 'Ativo'),
(12, 'Material Escolar', 'Material escolar diverso doado para instituições atendidas.', 8, 'Ativo'),
(13, 'Diversos', 'Itens diversos que não se encaixam em outras frentes (eletrodomésticos, utensílios de cozinha, itens sazonais).', 10, 'Ativo');

-- ---------------------------------------------------------
-- Itens (materiais doados) + multiplicador de vidas impactadas
-- ---------------------------------------------------------
CREATE TABLE itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  grupo_id INT NOT NULL,
  projeto_id INT NULL,
  nome VARCHAR(150) NOT NULL,
  unidade VARCHAR(20) NOT NULL,
  peso_unitario DECIMAL(10,3) NOT NULL DEFAULT 0,
  multiplicador DECIMAL(10,3) NOT NULL DEFAULT 0,
  FOREIGN KEY (grupo_id) REFERENCES grupos_impacto(id),
  FOREIGN KEY (projeto_id) REFERENCES projetos(id)
) ENGINE=InnoDB;

-- Itens, grupos, projetos e multiplicadores importados da planilha real de
-- controle de doacoes (aba "Oficios de entrega", linhas 2-4). O multiplicador
-- e o peso_unitario (kg) seguem os valores da planilha quando disponiveis;
-- itens sem peso conhecido na planilha ficam com peso_unitario = 0.
INSERT INTO itens (id, grupo_id, projeto_id, nome, unidade, peso_unitario, multiplicador) VALUES
  (1, 1, 1, 'Travesseiro Grande', 'un', 1.750, 1.000),
  (2, 1, 1, 'Travesseiro Viscose Lez a Lez', 'un', 2.000, 1.000),
  (3, 1, 1, 'Travesseiro Externo K', 'un', 2.000, 1.000),
  (4, 1, 1, 'Travesseiro Externo D', 'un', 2.000, 1.000),
  (5, 1, 1, 'Travesseiro Pequeno', 'un', 0.500, 1.000),
  (6, 1, 1, 'Travesseiro Lombar/ Dado', 'un', 2.500, 1.000),
  (7, 1, 1, 'Mantas de Malha', 'un', 0.250, 2.000),
  (8, 1, 1, 'Bolsas', 'un', 0.150, 2.000),
  (9, 1, 1, 'Toalhas de Mesa', 'un', 1.200, 4.000),
  (10, 1, 1, 'Naninhas', 'un', 1.500, 1.000),
  (11, 1, 1, 'Fraldas de Tecido', 'un', 0.800, 1.000),
  (12, 1, 1, 'Toalhas de Banho (Felpudo)', 'un', 0.300, 4.000),
  (13, 8, 1, 'Cadernos Encapados', 'un', 0.400, 1.000),
  (14, 1, 1, 'Cachecol Peludo', 'un', 0.100, 1.000),
  (15, 1, 1, 'Cortinas de Renda', 'un', 1.500, 4.000),
  (16, 1, 1, 'Edredon Karsten', 'un', 0.000, 2.000),
  (17, 1, 4, 'Calças ou Bermudas Economia Circular Krona', 'un', 0.000, 1.000),
  (18, 1, 4, 'Blusas ou Blusões Krona', 'un', 0.000, 1.000),
  (19, 1, 4, 'Roupas CAJ', 'un', 0.000, 1.000),
  (20, 1, 4, 'Uniformes SESI Customizados', 'un', 0.000, 1.000),
  (21, 1, 4, 'Calçados de Economia Circular', 'par', 0.000, 1.000),
  (22, 9, 2, 'Bolas Tampinhas do Bem', 'un', 0.000, 3.000),
  (23, 9, 2, 'Jogo de Badington', 'un', 0.000, 4.000),
  (24, 9, 2, 'Bambole Tampinhas do Bem', 'un', 0.000, 3.000),
  (25, 9, 2, 'Baralho ou Jogo Uno', 'un', 0.000, 4.000),
  (26, 9, 2, 'Bomba de Encher', 'un', 0.000, 4.000),
  (27, 9, 2, 'Bingo', 'un', 0.000, 20.000),
  (28, 9, 2, 'Jogo de Xadrez', 'un', 0.000, 4.000),
  (29, 9, 2, 'Trave de Futebol', 'un', 0.000, 10.000),
  (30, 9, 6, 'Lancheiras/Estojos', 'un', 0.000, 1.000),
  (31, 9, 6, 'Bolsa de Bola', 'un', 0.000, 4.000),
  (32, 9, 6, 'Bolas Diversa', 'un', 0.000, 4.000),
  (33, 2, 6, 'Potes Plásticos', 'un', 0.000, 1.000),
  (34, 2, 6, 'Copos ou Garrafas', 'un', 0.000, 1.000),
  (35, 9, 6, 'Brinquedos', 'un', 0.000, 4.000),
  (36, 9, 7, 'Bastões de Ginástica', 'un', 0.000, 4.000),
  (37, 3, 7, 'Colchonete', 'un', 0.000, 4.000),
  (38, 9, 7, 'Bolas SESI EVA ou Bocha', 'un', 0.000, 4.000),
  (39, 1, 8, 'Lençol Coberta ou Cobertores ou Edredon', 'un', 0.000, 2.000),
  (40, 1, 8, 'Toalhas Felpudas Banho ou Rosto', 'un', 0.000, 2.000),
  (41, 1, 8, 'Toucas Luvas e Cachecois', 'un', 0.000, 1.000),
  (42, 1, 8, 'Meias', 'un', 0.000, 1.000),
  (43, 1, 8, 'Roupas', 'un', 0.000, 1.000),
  (44, 1, 8, 'Calçados', 'par', 0.000, 1.000),
  (45, 1, 8, 'Mochilas, Bolsas, Lancheiras, Penal ou Acessórios', 'un', 0.000, 2.000),
  (46, 5, 9, 'Leite', 'L', 0.000, 2.000),
  (47, 5, 9, 'Leite em Pó', 'kg', 0.000, 2.000),
  (48, 5, 9, 'Alimentos em Kg', 'kg', 0.000, 4.000),
  (49, 5, 9, 'Achocolatado Lata', 'un', 0.000, 4.000),
  (50, 5, 9, 'Biscoitos', 'un', 0.000, 2.000),
  (51, 5, 9, 'Óleo de Soja', 'L', 0.000, 4.000),
  (52, 6, 10, 'Hastes Flexíveis', 'un', 0.000, 0.100),
  (53, 6, 10, 'Sabonete', 'un', 0.000, 2.000),
  (54, 6, 10, 'Fraldas Descartáveis', 'un', 0.000, 0.100),
  (55, 6, 10, 'Lenços Umedecidos', 'un', 0.000, 0.200),
  (56, 6, 10, 'Sabonete Líquido', 'un', 0.000, 4.000),
  (57, 6, 10, 'Hidratante/Pomada', 'un', 0.000, 4.000),
  (58, 6, 10, 'Absorventes', 'un', 0.000, 0.100),
  (59, 6, 10, 'Pasta de Dente', 'un', 0.000, 4.000),
  (60, 6, 10, 'Escova de Dente', 'un', 0.000, 1.000),
  (61, 6, 10, 'Fio Dental', 'un', 0.000, 4.000),
  (62, 6, 10, 'Talco', 'un', 0.000, 2.000),
  (63, 6, 10, 'Shampoo', 'un', 0.000, 4.000),
  (64, 6, 10, 'Condicionador', 'un', 0.000, 4.000),
  (65, 6, 10, 'Desodorante', 'un', 0.000, 2.000),
  (66, 6, 10, 'Papel Higiênico', 'un', 0.000, 4.000),
  (67, 6, 10, 'Enxaguante Bucal', 'un', 0.000, 4.000),
  (68, 6, 10, 'Aparelho de Barbear', 'un', 0.000, 2.000),
  (69, 7, 11, 'Sabão em Pó', 'un', 0.000, 4.000),
  (70, 7, 11, 'Água Sanitária Litro', 'L', 0.000, 4.000),
  (71, 7, 11, 'Sabão em Barra', 'un', 0.000, 4.000),
  (72, 7, 11, 'Desinfetante', 'un', 0.000, 4.000),
  (73, 7, 11, 'Detergente de Louça', 'un', 0.000, 4.000),
  (74, 7, 11, 'Esponja', 'un', 0.000, 4.000),
  (75, 8, 12, 'Tesouras', 'un', 0.000, 1.000),
  (76, 8, 12, 'Caixa de Lápis de Cor', 'un', 0.000, 1.000),
  (77, 8, 12, 'Lápis/Canetas', 'un', 0.000, 1.000),
  (78, 8, 12, 'Item Escolar Diverso', 'un', 0.000, 1.000),
  (79, 8, 12, 'Cadernos', 'un', 0.000, 1.000),
  (80, 8, 12, 'Livros', 'un', 0.000, 2.000),
  (81, 8, 12, 'Jogos Didáticos', 'un', 0.000, 4.000),
  (82, 10, 13, 'Chaveiro', 'un', 0.000, 1.000),
  (83, 3, 13, 'Colchão Inflável', 'un', 0.000, 1.000),
  (84, 10, 13, 'Itens de Cozinha', 'un', 0.000, 4.000),
  (85, 4, 13, 'Eletrodoméstico', 'un', 0.000, 4.000),
  (86, 10, 13, 'Latas de Páscoa ou Natal', 'un', 0.000, 1.000),
  (87, 5, 13, 'Itens de Páscoa (Chocolate)', 'kg', 0.000, 1.000);

-- ---------------------------------------------------------
-- Instituicoes beneficiadas
-- ---------------------------------------------------------
CREATE TABLE instituicoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  razao_social VARCHAR(200),
  cnpj VARCHAR(20) NOT NULL UNIQUE,
  bairro VARCHAR(100),
  responsavel VARCHAR(150),
  tipo VARCHAR(100),
  acolhimento TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO instituicoes (id, nome, cnpj, bairro, responsavel, tipo, acolhimento) VALUES
(1, 'Abrigo Casa Feliz', '12.345.678/0001-90', 'Boa Vista', 'Maria Andrade', 'Casa de acolhimento', 1),
(2, 'Lar dos Idosos São José', '23.456.789/0001-11', 'Centro', 'João Pereira', 'ILPI', 0),
(3, 'Creche Pequenos Passos', '34.567.890/0001-22', 'Aventureiro', 'Ana Souza', 'Educação Infantil', 0),
(4, 'Biblioteca Comunitária Vila Nova', '45.678.901/0001-33', 'Vila Nova', 'Carlos Lima', 'Biblioteca', 0);

-- ---------------------------------------------------------
-- Estoque (um saldo por item)
-- ---------------------------------------------------------
CREATE TABLE estoque (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL UNIQUE,
  saldo DECIMAL(12,3) NOT NULL DEFAULT 0,
  minimo DECIMAL(12,3) NOT NULL DEFAULT 0,
  FOREIGN KEY (item_id) REFERENCES itens(id)
) ENGINE=InnoDB;

-- Toda instituicao comeca com saldo zerado; o estoque real e alimentado
-- pelas Entradas de Materiais (a importar separadamente da planilha).
INSERT INTO estoque (item_id, saldo, minimo)
SELECT id, 0, 0 FROM itens;

-- ---------------------------------------------------------
-- Entradas de materiais
-- ---------------------------------------------------------
CREATE TABLE entradas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data_entrada DATE NOT NULL,
  origem VARCHAR(150),
  projeto_id INT,
  item_id INT NOT NULL,
  quantidade DECIMAL(12,3) NOT NULL,
  peso DECIMAL(12,3),
  criado_por INT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (projeto_id) REFERENCES projetos(id),
  FOREIGN KEY (item_id) REFERENCES itens(id),
  FOREIGN KEY (criado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Producao social
-- ---------------------------------------------------------
CREATE TABLE producoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data_producao DATE NOT NULL,
  projeto_id INT,
  item_consumido_id INT,
  qtd_consumida DECIMAL(12,3),
  produto_gerado VARCHAR(150),
  quantidade_gerada DECIMAL(12,3),
  unidade_gerada VARCHAR(20),
  residuo_kg DECIMAL(12,3) DEFAULT 0,
  criado_por INT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (projeto_id) REFERENCES projetos(id),
  FOREIGN KEY (item_consumido_id) REFERENCES itens(id),
  FOREIGN KEY (criado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Doacoes (cabecalho + itens)
-- ---------------------------------------------------------
CREATE TABLE doacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instituicao_id INT NOT NULL,
  projeto_id INT,
  data_doacao DATE NOT NULL,
  vidas_impactadas DECIMAL(10,1) NOT NULL DEFAULT 0,
  criado_por INT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id),
  FOREIGN KEY (projeto_id) REFERENCES projetos(id),
  FOREIGN KEY (criado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE doacao_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doacao_id INT NOT NULL,
  item_id INT NOT NULL,
  quantidade DECIMAL(12,3) NOT NULL,
  FOREIGN KEY (doacao_id) REFERENCES doacoes(id),
  FOREIGN KEY (item_id) REFERENCES itens(id)
) ENGINE=InnoDB;

CREATE TABLE termos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doacao_id INT NOT NULL UNIQUE,
  numero VARCHAR(30) NOT NULL UNIQUE,
  emitido_em DATE NOT NULL,
  FOREIGN KEY (doacao_id) REFERENCES doacoes(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Evidencias fotograficas
-- ---------------------------------------------------------
CREATE TABLE evidencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instituicao_id INT,
  arquivo VARCHAR(255) NOT NULL,
  data_evidencia DATE NOT NULL,
  FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id)
) ENGINE=InnoDB;

INSERT INTO evidencias (instituicao_id, arquivo, data_evidencia) VALUES
(1, 'evidencia-0148.jpg', '2026-08-02'),
(3, 'evidencia-0147.jpg', '2026-07-28'),
(2, 'evidencia-0146.jpg', '2026-07-20'),
(4, 'evidencia-0145.jpg', '2026-07-15');
