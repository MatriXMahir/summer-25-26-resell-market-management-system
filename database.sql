DROP DATABASE IF EXISTS resell_market;

CREATE DATABASE resell_market
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE resell_market;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','buyer','seller','delivery') NOT NULL,
    status ENUM('pending','active','banned') NOT NULL DEFAULT 'pending',
    ban_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (full_name, email, password_hash, role, status) VALUES
('Dhrubo (Admin)',    'dhrubo@resell.com',  '$2y$10$K5f5WsaLowY1igG4U88iT.YmkkrbTbtVdR5ToGu7zc/0VwDxxVEW.', 'admin',    'active'),
('Mahir (Buyer)',     'mahir@resell.com',   '$2y$10$npbvSCCvIhZfxOuS5UVqpOsw112GHFilS5Zq89Mzdm/qUDjLtpXiu', 'buyer',    'active'),
('Sanjida (Seller)',  'sanjida@resell.com', '$2y$10$npbvSCCvIhZfxOuS5UVqpOsw112GHFilS5Zq89Mzdm/qUDjLtpXiu', 'seller',   'active'),
('Tonmoy (Delivery)', 'tonmoy@resell.com',  '$2y$10$npbvSCCvIhZfxOuS5UVqpOsw112GHFilS5Zq89Mzdm/qUDjLtpXiu', 'delivery', 'active');

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO products (seller_id, name, description, price, stock_qty, status) VALUES
(3, 'Used iPhone 12',      '128GB, good battery health', 32000.00, 4, 'approved'),
(3, 'Used iPhone 13',      '256GB, like new',            48000.00, 2, 'approved'),
(3, 'Study Table',         'Wooden, 3x2 feet',            3500.00, 6, 'approved'),
(3, 'Study Lamp',          'LED, adjustable arm',          850.00, 10, 'approved'),
(3, 'Gaming Mouse',        'RGB, 6 buttons',              1200.00, 8, 'approved'),
(3, 'Mechanical Keyboard', 'Blue switches',                3200.00, 3, 'approved');


CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    buyer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('bkash','nagad','card','cod') NOT NULL,
    payment_status ENUM('pending','success','failed') NOT NULL DEFAULT 'success',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);


CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    product_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    seller_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    product_id INT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    vehicle VARCHAR(50),
    status ENUM('available','busy') NOT NULL DEFAULT 'available'
);

INSERT INTO drivers (name, phone, vehicle, status) VALUES
('Karim Sheikh',  '01710000001', 'Motorbike', 'available'),
('Jamal Hossain', '01710000002', 'Van',       'available'),
('Rafiq Islam',   '01710000003', 'Motorbike', 'available');

CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    driver_id INT NULL,
    delivery_man_id INT NOT NULL,
    scheduled_date DATE NOT NULL,
    status ENUM('assigned','in_transit','delivered','cancelled') NOT NULL DEFAULT 'assigned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (delivery_man_id) REFERENCES users(id) ON DELETE CASCADE
);
