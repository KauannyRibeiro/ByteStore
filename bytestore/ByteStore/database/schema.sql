-- =====================================================================
-- ByteStore - script de criação do banco de dados (MySQL)
-- =====================================================================
-- COMO RODAR (escolha UMA forma):
--   a) Terminal, dentro da pasta do projeto:
--         mysql -u root -p < database/schema.sql
--   b) MySQL Workbench: File > Open SQL Script > escolha este arquivo > clique no raio.
--   c) Extensão MySQL do VS Code: abra este arquivo e execute.
--
-- Para APAGAR TUDO e recriar do zero, tire o "-- " da linha abaixo:
-- DROP DATABASE IF EXISTS bytestore;
-- =====================================================================

-- CREATE DATABASE IF NOT EXISTS bytestore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE bytestore;

-- 1. Categorias
CREATE TABLE IF NOT EXISTS categorias (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT NULL
) ENGINE=InnoDB;

-- 2. Produtos (um produto pertence a UMA categoria)
--    As colunas socket/tdp são usadas por Hardware; dpi/conexao por Periferico.
CREATE TABLE IF NOT EXISTS produtos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(150) NOT NULL,
    descricao    TEXT NULL,
    preco        DECIMAL(10,2) NOT NULL,
    estoque      INT NOT NULL DEFAULT 0,
    categoria_id INT NOT NULL,
    tipo         ENUM('hardware','periferico') NOT NULL,
    socket       VARCHAR(50) NULL,
    tdp          INT NULL,
    dpi          INT NULL,
    conexao      VARCHAR(50) NULL,
    criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_produtos_categoria (categoria_id),
    CONSTRAINT fk_produtos_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 3. Usuários administradores
CREATE TABLE IF NOT EXISTS usuarios (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    nome  VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,           -- hash gerado por password_hash()
    nivel VARCHAR(50) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB;

-- ---------- Dados iniciais ----------
INSERT INTO categorias (nome, descricao) VALUES
('Processadores',   'CPUs para desktop'),
('Placas de Vídeo', 'GPUs para jogos e criação'),
('Placas-Mãe',      'Motherboards'),
('Memória RAM',     'Módulos de memória'),
('Teclados',        'Teclados mecânicos e de membrana'),
('Mouses',          'Mouses gamer e de escritório');

INSERT INTO produtos (nome, descricao, preco, estoque, categoria_id, tipo, socket, tdp, dpi, conexao) VALUES
('Processador Ryzen 7 5700X',  '8 núcleos e 16 threads.',        1399.90, 12, 1, 'hardware',   'AM4',            65,  NULL,  NULL),
('Processador Core i5-12400F', '6 núcleos, sem vídeo integrado.',  899.90, 20, 1, 'hardware',   'LGA1700',        65,  NULL,  NULL),
('Placa de Vídeo RTX 4060 8GB','Ótima para jogos em Full HD/QHD.',2199.00,  7, 2, 'hardware',   'PCIe 4.0 x8',   115,  NULL,  NULL),
('Placa de Vídeo RX 7600 8GB', 'Entrada para jogos em Full HD.',  1649.00,  5, 2, 'hardware',   'PCIe 4.0 x8',   165,  NULL,  NULL),
('Placa-Mãe B550M',            'Formato micro-ATX.',               749.90, 10, 3, 'hardware',   'AM4',             0,  NULL,  NULL),
('Memória DDR4 16GB 3200MHz',  'Módulo único de 16GB.',            259.90, 30, 4, 'hardware',   'DDR4 / DIMM',     0,  NULL,  NULL),
('Teclado Mecânico TKL',       'Switch azul, ABNT2.',              289.90, 15, 5, 'periferico', NULL,           NULL,     0, 'USB'),
('Mouse Gamer 12000 DPI',      'Sensor óptico, 6 botões.',         129.90, 25, 6, 'periferico', NULL,           NULL, 12000, 'USB'),
('Mouse Sem Fio 16000 DPI',    'Leve, bateria de longa duração.',  549.90,  0, 6, 'periferico', NULL,           NULL, 16000, 'Wireless 2.4GHz');

-- Administrador padrão -> e-mail: admin@bytestore.com | senha: admin123 (Senha para prototipo, deve ser trocada depois)
INSERT INTO usuarios (nome, email, senha, nivel) VALUES
('Administrador', 'admin@bytestore.com', '$2y$10$BOqUwbK45KvleIlpM8ANM.UiMOzpodoR8h2eJIxzA4AkuCeSR1dAK', 'admin'); -- bcrypt para aumentar a segurança
