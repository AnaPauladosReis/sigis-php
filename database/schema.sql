-- =========================================================
-- SIGIS - Sistema Integrado de Gestao de Impacto Social,
-- Producao Social e Doacoes
-- Banco de dados MySQL
-- =========================================================

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
-- Grupos de impacto + itens
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
(4, 'Eletrônicos', '#8FA8D6');

CREATE TABLE itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  grupo_id INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  unidade VARCHAR(20) NOT NULL,
  peso_unitario DECIMAL(10,3) NOT NULL DEFAULT 0,
  multiplicador DECIMAL(10,3) NOT NULL DEFAULT 0,
  FOREIGN KEY (grupo_id) REFERENCES grupos_impacto(id)
) ENGINE=InnoDB;

INSERT INTO itens (id, grupo_id, nome, unidade, peso_unitario, multiplicador) VALUES
(1, 1, 'Retalho de algodão', 'kg', 1.000, 1.200),
(2, 1, 'Malha industrial', 'kg', 1.000, 1.000),
(3, 1, 'Roupas usadas', 'peça', 0.700, 0.600),
(4, 2, 'Tampinha PET', 'kg', 1.000, 0.050),
(5, 2, 'Embalagem PET', 'kg', 1.000, 0.080),
(6, 3, 'Móvel reaproveitado', 'un', 12.000, 3.500),
(7, 4, 'Equipamento eletrônico', 'un', 4.000, 2.000);

-- ---------------------------------------------------------
-- Projetos sociais
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
(2, 'Tampinhas do Bem', 'Coleta de tampinhas plásticas para reciclagem e geração de renda social.', 2, 'Ativo'),
(3, 'Troca Solidária', 'Troca de materiais recicláveis por itens de necessidade básica com parceiros locais.', 1, 'Ativo'),
(4, 'Economia Circular Industrial', 'Reaproveitamento de resíduos industriais de empresas parceiras em novos produtos.', 3, 'Ativo'),
(5, 'Campanha do Agasalho 2025', 'Arrecadação sazonal de roupas e cobertores para famílias em situação de vulnerabilidade.', 1, 'Encerrado');

-- ---------------------------------------------------------
-- Instituicoes beneficiadas
-- ---------------------------------------------------------
CREATE TABLE instituicoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(150) NOT NULL,
  cnpj VARCHAR(20) NOT NULL,
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

INSERT INTO estoque (item_id, saldo, minimo) VALUES
(1, 340.000, 100.000),
(2, 58.000, 80.000),
(4, 1240.000, 200.000),
(3, 95.000, 50.000),
(6, 4.000, 5.000);

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

INSERT INTO entradas (data_entrada, origem, projeto_id, item_id, quantidade, peso) VALUES
('2026-08-03', 'Doação espontânea', 1, 1, 120, 84),
('2026-08-01', 'Indústria parceira', 4, 4, 3400, 62),
('2026-07-29', 'Multa social', 3, 3, 210, 145),
('2026-07-27', 'Biblioteca', 2, 5, 900, 40),
('2026-07-22', 'Campanha', 1, 2, 75, 75);

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

INSERT INTO producoes (data_producao, projeto_id, item_consumido_id, qtd_consumida, produto_gerado, quantidade_gerada, unidade_gerada, residuo_kg) VALUES
('2026-08-05', 1, 1, 40, 'Naninhas', 85, 'un', 3),
('2026-08-04', 1, 2, 20, 'Almofadas', 40, 'un', 2),
('2026-07-30', 2, 4, 500, 'Blocos ecológicos', 60, 'un', 15);

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

-- doacao/termo de exemplo (para a tela de Termo ter algo pra mostrar antes da 1a doacao real)
INSERT INTO doacoes (id, instituicao_id, projeto_id, data_doacao, vidas_impactadas) VALUES
(1, 1, 1, '2026-08-02', 312.0);
INSERT INTO doacao_itens (doacao_id, item_id, quantidade) VALUES
(1, 1, 40),
(1, 2, 20);

CREATE TABLE termos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doacao_id INT NOT NULL UNIQUE,
  numero VARCHAR(30) NOT NULL UNIQUE,
  emitido_em DATE NOT NULL,
  FOREIGN KEY (doacao_id) REFERENCES doacoes(id)
) ENGINE=InnoDB;

INSERT INTO termos (doacao_id, numero, emitido_em) VALUES
(1, 'TD-2026-00146', '2026-08-02');

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
