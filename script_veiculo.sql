--=========================================
-- TABELA: ENDERECO
-- =========================================
CREATE TABLE enderecos (
    id SERIAL PRIMARY KEY,
    rua VARCHAR(255),
    numero VARCHAR(50),
    complemento VARCHAR(255),
    bairro VARCHAR(100),
    cep VARCHAR(20),
    cidade VARCHAR(100),
    estado VARCHAR(50)
);


-- =========================================
-- TABELA: USUARIO (INTERNO)
-- =========================================
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    login VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL CHECK (perfil IN ('ADMIN', 'INTERNO'))
);


-- =========================================
-- TABELA: CLIENTE
-- =========================================
CREATE TABLE clientes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    telefone VARCHAR(50),
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    endereco_id INT,

    CONSTRAINT fk_cliente_endereco
        FOREIGN KEY (endereco_id)
        REFERENCES enderecos(id)
        ON DELETE SET NULL
);


-- =========================================
-- TABELA: FORNECEDOR
-- =========================================
CREATE TABLE fornecedores (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    telefone VARCHAR(50),
    email VARCHAR(150),
    endereco_id INT,

    CONSTRAINT fk_fornecedor_endereco
        FOREIGN KEY (endereco_id)
        REFERENCES enderecos(id)
        ON DELETE SET NULL
);


-- =========================================
-- TABELA: PRODUTO
-- =========================================
CREATE TABLE produtos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    foto BYTEA, 
    fornecedor_id INT NOT NULL,

    CONSTRAINT fk_produto_fornecedor
        FOREIGN KEY (fornecedor_id)
        REFERENCES fornecedores(id)
        ON DELETE CASCADE
);


-- =========================================
-- TABELA: ESTOQUE
-- =========================================
CREATE TABLE estoques (
    id SERIAL PRIMARY KEY,
    produto_id INT UNIQUE NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    preco NUMERIC(10,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT fk_estoque_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_quantidade_positiva
        CHECK (quantidade >= 0)
);


-- =========================================
-- TABELA: PEDIDO
-- =========================================
CREATE TABLE pedidos (
    id SERIAL PRIMARY KEY,
    numero INT UNIQUE NOT NULL,
    cliente_id INT NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_entrega TIMESTAMP,
    data_cancelamento TIMESTAMP,
    situacao VARCHAR(20) NOT NULL DEFAULT 'NOVO',
    valor_total NUMERIC(10,2) DEFAULT 0.00,

    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (cliente_id)
        REFERENCES clientes(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_situacao
        CHECK (situacao IN ('NOVO', 'ENVIADO', 'CANCELADO'))
);


-- =========================================
-- TABELA: ITEM_PEDIDO
-- =========================================
CREATE TABLE itens_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco NUMERIC(10,2) NOT NULL,

    CONSTRAINT fk_item_pedido
        FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_item_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id),

    CONSTRAINT chk_qtd_item
        CHECK (quantidade > 0)
);


-- =========================================
-- ÍNDICES (IMPORTANTE PRA CONSULTAS)
-- =========================================
CREATE INDEX idx_produto_nome ON produtos(nome);
CREATE INDEX idx_fornecedor_nome ON fornecedores(nome);
CREATE INDEX idx_cliente_nome ON clientes(nome);
CREATE INDEX idx_pedido_numero ON pedidos(numero);