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
    region VARCHAR(100),
    territory VARCHAR(100),
    wholesaler_id INT DEFAULT NULL,
    order_direct TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wholesaler_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_role (role),
    INDEX idx_wholesaler (wholesaler_id)
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

-- SEED DATA

-- Insert Nestlé admin account (password: admin123)
INSERT INTO users (name, email, password, role, status) VALUES
('Nestlé Admin', 'admin@nestle.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nestle', 'active');

-- Insert sample products
INSERT INTO products (name, sku, category, unit, description, price) VALUES
('MILO 1kg Refill Pack', 'MILO-1KG-001', 'Beverages', '1kg', 'MILO chocolate malt drink powder', 850.00),
('NESCAFÉ Classic 500g', 'NESCAFE-500G-001', 'Beverages', '500g', 'Instant coffee', 1250.00),
('MAGGI 2-Minute Noodles 8-Pack', 'MAGGI-8PK-001', 'Noodles', '8-pack', 'Instant noodles', 420.00),
('Anchor Full Cream Milk Powder 400g', 'ANCHOR-400G-001', 'Dairy', '400g', 'Full cream milk powder', 680.00),
('KitKat 4-Finger 10-Pack', 'KITKAT-10PK-001', 'Confectionery', '10-pack', 'Chocolate wafer', 550.00);

-- Insert warehouse stock
INSERT INTO warehouse_stock (product_id, total_stock, reserved_stock, reorder_point) 
SELECT id, 5000, 0, 500 FROM products;

-- Insert sample distributors (5) (password: password123)
INSERT INTO users (name, email, password, role, territory, status) VALUES
('Distributor 1 (Western)', 'dist1@ndrc.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'distributor', 'Western Province', 'active'),
('Distributor 2 (Central)', 'dist2@ndrc.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'distributor', 'Central Province', 'active'),
('Distributor 3 (Southern)', 'dist3@ndrc.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'distributor', 'Southern Province', 'active'),
('Distributor 4 (Northern)', 'dist4@ndrc.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'distributor', 'Northern Province', 'active'),
('Distributor 5 (Eastern)', 'dist5@ndrc.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'distributor', 'Eastern Province', 'active');
