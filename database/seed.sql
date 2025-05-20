-- =============================================
-- SEED PARA O MINI ERP PHP - DATABASE INICIAL
-- =============================================

-- Conectar ao banco de dados
USE mini_erp;

-- Limpar dados existentes para evitar conflitos
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE stock;
TRUNCATE TABLE variations;
TRUNCATE TABLE coupons;
TRUNCATE TABLE products;

-- Inserir Produtos
INSERT INTO products (id, name, price) VALUES
(1, 'Camiseta Básica', 39.90),
(2, 'Jaqueta Corta-Vento', 129.90),
(3, 'Calça Jeans Slim', 89.90),
(4, 'Tênis Esportivo', 159.90),
(5, 'Bermuda Casual', 69.90);

-- Inserir Variações
INSERT INTO variations (id, product_id, name) VALUES
-- Camiseta Básica
(1, 1, 'Preta - P'),
(2, 1, 'Preta - M'),
(3, 1, 'Preta - G'),
(4, 1, 'Branca - P'),
(5, 1, 'Branca - M'),
(6, 1, 'Branca - G'),

-- Jaqueta Corta-Vento
(7, 2, 'Verde Militar - P'),
(8, 2, 'Verde Militar - M'),
(9, 2, 'Verde Militar - G'),
(10, 2, 'Preta - P'),
(11, 2, 'Preta - M'),
(12, 2, 'Preta - G'),

-- Calça Jeans Slim
(13, 3, 'Azul Escuro - 38'),
(14, 3, 'Azul Escuro - 40'),
(15, 3, 'Azul Escuro - 42'),
(16, 3, 'Azul Claro - 38'),
(17, 3, 'Azul Claro - 40'),
(18, 3, 'Azul Claro - 42'),

-- Tênis Esportivo
(19, 4, 'Preto - 39'),
(20, 4, 'Preto - 40'),
(21, 4, 'Preto - 41'),
(22, 4, 'Preto - 42'),
(23, 4, 'Branco - 39'),
(24, 4, 'Branco - 40'),
(25, 4, 'Branco - 41'),
(26, 4, 'Branco - 42'),

-- Bermuda Casual
(27, 5, 'Bege - M'),
(28, 5, 'Bege - G'),
(29, 5, 'Preta - M'),
(30, 5, 'Preta - G');

-- Inserir Estoque
INSERT INTO stock (variation_id, quantity) VALUES
-- Camiseta Básica
(1, 20),  -- Preta - P
(2, 35),  -- Preta - M
(3, 15),  -- Preta - G
(4, 18),  -- Branca - P
(5, 30),  -- Branca - M
(6, 12),  -- Branca - G

-- Jaqueta Corta-Vento
(7, 8),   -- Verde Militar - P
(8, 12),  -- Verde Militar - M
(9, 6),   -- Verde Militar - G
(10, 5),  -- Preta - P
(11, 10), -- Preta - M
(12, 7),  -- Preta - G

-- Calça Jeans Slim
(13, 10), -- Azul Escuro - 38
(14, 15), -- Azul Escuro - 40
(15, 8),  -- Azul Escuro - 42
(16, 7),  -- Azul Claro - 38
(17, 12), -- Azul Claro - 40
(18, 5),  -- Azul Claro - 42

-- Tênis Esportivo
(19, 6),  -- Preto - 39
(20, 8),  -- Preto - 40
(21, 10), -- Preto - 41
(22, 5),  -- Preto - 42
(23, 4),  -- Branco - 39
(24, 7),  -- Branco - 40
(25, 9),  -- Branco - 41
(26, 3),  -- Branco - 42

-- Bermuda Casual
(27, 15), -- Bege - M
(28, 12), -- Bege - G
(29, 18), -- Preta - M
(30, 14); -- Preta - G

-- Inserir Cupons de Desconto
INSERT INTO coupons (code, discount, min_amount, expires_at) VALUES
('PROMO10', 10.00, 50.00, '2025-12-31 23:59:59'),
('WELCOME20', 20.00, 100.00, '2025-12-31 23:59:59'),
('FRETE0', 15.00, 80.00, '2025-12-31 23:59:59'),
('PRIMERA30', 30.00, 200.00, '2025-06-30 23:59:59'),
('OFERTA15', 15.00, 75.00, '2025-12-31 23:59:59');

-- Inserir alguns pedidos de exemplo
INSERT INTO orders (customer_email, cep, address, subtotal, shipping, discount, coupon_code, total, status) VALUES
('cliente1@exemplo.com', '62900000', 'Rua das Flores, 123 - Centro', 219.80, 0.00, 0.00, NULL, 219.80, 'completed'),
('cliente2@exemplo.com', '62901000', 'Av. Principal, 456 - Bairro Novo', 159.90, 0.00, 15.00, 'FRETE0', 144.90, 'processing'),
('cliente3@exemplo.com', '62902000', 'Travessa da Paz, 78 - Jardim', 299.70, 0.00, 30.00, 'PRIMERA30', 269.70, 'shipped');

-- Inserir itens dos pedidos
INSERT INTO order_items (order_id, product_name, variation_name, quantity, price) VALUES
-- Pedido 1
(1, 'Camiseta Básica', 'Preta - G', 2, 39.90),
(1, 'Jaqueta Corta-Vento', 'Verde Militar - M', 1, 129.90),

-- Pedido 2
(2, 'Tênis Esportivo', 'Preto - 40', 1, 159.90),

-- Pedido 3
(3, 'Calça Jeans Slim', 'Azul Escuro - 40', 2, 89.90),
(3, 'Bermuda Casual', 'Bege - G', 1, 69.90),
(3, 'Camiseta Básica', 'Branca - M', 1, 39.90);

-- Confirmar commit das alterações
COMMIT;

SELECT 'Dados de seed inseridos com sucesso!' AS 'Resultado';