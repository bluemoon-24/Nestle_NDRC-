-- Nestle NDRC Supply Chain Last Mile Visibility System
-- Final Database Schema and Seed Data

DROP DATABASE IF EXISTS ndrc_nestle;
CREATE DATABASE ndrc_nestle;
USE ndrc_nestle;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('retailer', 'wholesaler', 'distributor', 'nestle') NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    region VARCHAR(100),
    territory VARCHAR(100),
    wholesaler_id INT DEFAULT NULL,
    distributor_id INT DEFAULT NULL,
    order_direct TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wholesaler_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (distributor_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_role (role),
    INDEX idx_wholesaler (wholesaler_id),
    INDEX idx_distributor_aff (distributor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    category ENUM('Dairy', 'Beverages', 'Noodles', 'Confectionery', 'Culinary') NOT NULL,
    unit VARCHAR(50) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    retailer_id INT NOT NULL,
    wholesaler_id INT DEFAULT NULL,
    distributor_id INT NOT NULL,
    status ENUM('placed', 'wholesaler_pending', 'wholesaler_accepted', 
                'distributor_pending', 'distributor_confirmed', 
                'dispatched', 'delivered', 'rejected') NOT NULL,
    order_date DATE NOT NULL,
    scheduled_dispatch_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (retailer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wholesaler_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (distributor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_order_date (order_date),
    INDEX idx_distributor (distributor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: order_items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: warehouse_stock
CREATE TABLE warehouse_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNIQUE NOT NULL,
    total_stock INT NOT NULL DEFAULT 0,
    reserved_stock INT NOT NULL DEFAULT 0,
    available_stock INT GENERATED ALWAYS AS (total_stock - reserved_stock) STORED,
    reorder_point INT NOT NULL DEFAULT 100,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_available (available_stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('order_status', 'stock_alert', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500),
    read_status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, read_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: network_requests
CREATE TABLE network_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    retailer_id INT NOT NULL,
    wholesaler_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (retailer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (wholesaler_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_retailer_req (retailer_id),
    INDEX idx_wholesaler_req (wholesaler_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: factory_orders
CREATE TABLE factory_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    distributor_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'dispatched', 'delivered', 'rejected') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (distributor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dist_factory (distributor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: factory_order_items
CREATE TABLE factory_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factory_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (factory_order_id) REFERENCES factory_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED DATA

-- Insert Nestlé admin account (password: admin123)
-- Hash generated for: admin123
INSERT INTO users (name, email, password, role, status) VALUES
('Nestlé Admin', 'admin@nestle.lk', '$2y$10$bGrPvw8/NQ855l82NCC6JuFw6OO5GYpiA67Dmcgxm/HvIEfGf9pri', 'nestle', 'active');

-- Insert sample products (Distributor/Bulk Prices)
INSERT INTO products (name, sku, category, unit, description, price) VALUES
-- Beverages
('MILO 1kg Refill Pack (Distributor Pack)', 'MILO-1KG-D', 'Beverages', '1kg', 'MILO chocolate malt drink powder - 1kg bulk bag', 720.00),
('NESCAFÉ Classic 500g (Distributor Pack)', 'NESCAFE-500G-D', 'Beverages', '500g', 'Instant coffee - 500g economy pack', 1050.00),
('Milo Ready-to-Drink 200ml (Case of 24)', 'MILO-RTD-C24', 'Beverages', 'Case', '24 packs of 200ml Milo RTD', 1800.00),

-- Noodles
('MAGGI 2-Minute Noodles (Family Pack - 40pcs)', 'MAGGI-40PK', 'Noodles', 'Box', 'Bulk box of 40 individual 2-minute noodle packs', 1600.00),
('MAGGI Curry Noodles 8-Pack (Value Pack)', 'MAGGI-8PK-V', 'Noodles', '8-pack', 'Value pack of 8 curry noodle packs', 350.00),

-- Dairy
('Milkmaid Sweetened Condensed Milk (Case of 12)', 'MILKMAID-C12', 'Dairy', 'Case', '12 cans of 390g sweetened condensed milk', 4200.00),
('Nestlé Everyday Milk Powder 1kg', 'EVERYDAY-1KG', 'Dairy', '1kg', 'Full cream milk powder for tea/coffee', 1450.00),
('Anchor Full Cream Milk Powder 400g (Bulk Buy)', 'ANCHOR-400G-B', 'Dairy', '400g', 'Full cream milk powder - 400g pack', 580.00),

-- Confectionery
('KitKat 4-Finger (Display Box - 24pcs)', 'KITKAT-BOX24', 'Confectionery', 'Box', 'Display box containing 24 KitKat 4-finger bars', 1100.00),
('Nestlé Milkybar (Pack of 12)', 'MILKYBAR-P12', 'Confectionery', '12-pack', 'White chocolate bars pack', 480.00),

-- Culinary
('Maggi Coconut Milk Powder 1kg', 'MAGGI-CMP-1KG', 'Culinary', '1kg', 'Premium coconut milk powder in bulk', 1250.00),
('Maggi Seasoning 200ml (Bottle)', 'MAGGI-SEASON-200', 'Culinary', 'Bottle', 'All-purpose liquid seasoning', 280.00);

-- Insert warehouse stock
INSERT INTO warehouse_stock (product_id, total_stock, reserved_stock, reorder_point) 
SELECT id, 10000, 0, 1000 FROM products;

-- Insert sample distributors (No initial seeds as per user request)
