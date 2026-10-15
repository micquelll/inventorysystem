-- Inventory Management System database
-- Import this file in phpMyAdmin (select your database first).

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contact_person VARCHAR(100) DEFAULT '',
  phone VARCHAR(30) DEFAULT '',
  email VARCHAR(150) DEFAULT ''
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  category VARCHAR(60) DEFAULT '',
  supplier_id INT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  reorder_level INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

CREATE TABLE stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  type ENUM('IN','OUT') NOT NULL,
  qty INT NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Accounts (both use the password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@inventory.com', '$2b$12$OGDPBS2nSVzDy509xBcOJu0GwIXdFT/3AJf2X/US78oWJVBTMTFWO', 'admin'),
('Staff Member', 'staff@inventory.com', '$2b$12$OGDPBS2nSVzDy509xBcOJu0GwIXdFT/3AJf2X/US78oWJVBTMTFWO', 'staff');

-- Sample suppliers
INSERT INTO suppliers (name, contact_person, phone, email) VALUES
('Agoo Grocery Wholesale', 'Maria Santos', '09171234567', 'orders@agoowholesale.example'),
('La Union Food Traders', 'Pedro Reyes', '09181234567', 'sales@lutraders.example'),
('Northern Beverage Supply', 'Ana Cruz', '09191234567', 'info@nbsupply.example');

-- Sample products (three are low on stock and one is out of stock, so the alerts show right away)
INSERT INTO products (sku, name, category, supplier_id, price, quantity, reorder_level) VALUES
('RICE-25',   'Rice (25 kg sack)',      'Grains',         1, 1450.00,  20,  5),
('OIL-1L',    'Cooking Oil 1L',         'Pantry',         1,   95.00,  48, 12),
('SARD-155',  'Canned Sardines 155g',   'Canned Goods',   2,   25.00,   6, 24),
('NOOD-60',   'Instant Noodles 60g',    'Pantry',         2,   14.00, 150, 50),
('WATR-500',  'Bottled Water 500ml',    'Beverages',      3,   15.00,   0, 24),
('SODA-15',   'Soft Drink 1.5L',        'Beverages',      3,   70.00,  30, 12),
('SOAP-LND',  'Laundry Soap Bar',       'Household',      1,   18.00,  40, 15),
('DISH-500',  'Dishwashing Liquid 500ml','Household',     1,   55.00,   9, 10),
('COF-3IN1',  'Coffee 3-in-1 Sachet',   'Beverages',      2,    7.00, 200, 100),
('SUG-1K',    'Sugar 1 kg',             'Pantry',         1,   85.00,  14, 15);

-- Sample stock history
INSERT INTO stock_movements (product_id, user_id, type, qty, note) VALUES
(1, 1, 'IN',  20, 'Starting stock'),
(3, 1, 'IN',  30, 'Starting stock'),
(3, 2, 'OUT', 24, 'Sold during the week'),
(5, 1, 'IN',  48, 'Starting stock'),
(5, 2, 'OUT', 48, 'Sold out'),
(10, 1, 'IN', 20, 'Starting stock'),
(10, 2, 'OUT', 6, 'Sold');
