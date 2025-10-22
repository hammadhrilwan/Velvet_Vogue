-- Create Velvet Vogue Database
CREATE DATABASE IF NOT EXISTS velvet_vogue;
USE velvet_vogue;

-- Users table for customers
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    zip_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT(11),
    gender ENUM('men', 'women', 'unisex') NOT NULL,
    sizes JSON,
    colors JSON,
    image_url VARCHAR(255),
    stock_quantity INT(11) DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_new_arrival BOOLEAN DEFAULT FALSE,
    is_on_sale BOOLEAN DEFAULT FALSE,
    sale_price DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    size VARCHAR(10),
    color VARCHAR(30),
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('T-Shirts', 'Comfortable and stylish t-shirts for everyday wear'),
('Jeans', 'High-quality denim jeans in various styles'),
('Dresses', 'Elegant dresses for formal and casual occasions'),
('Jackets', 'Stylish jackets and outerwear'),
('Shoes', 'Trendy footwear for all occasions'),
('Accessories', 'Fashion accessories to complete your look');

-- Insert default admin user (password: admin123)
INSERT INTO admin (username, email, password, full_name) VALUES
('admin', 'admin@velvetvogue.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, gender, sizes, colors, image_url, stock_quantity, is_featured) VALUES
-- Featured Products
('Classic White T-Shirt', 'Premium cotton t-shirt perfect for everyday wear', 29.99, 1, 'unisex', '["XS", "S", "M", "L", "XL"]', '["White", "Black", "Gray"]', 'images/products/white-tshirt.jpg', 50, TRUE),
('Slim Fit Jeans', 'Modern slim fit jeans with stretch comfort', 79.99, 2, 'unisex', '["28", "30", "32", "34", "36"]', '["Blue", "Black", "Dark Blue"]', 'images/products/slim-jeans.jpg', 30, TRUE),
('Elegant Black Dress', 'Sophisticated black dress perfect for evening events', 129.99, 3, 'women', '["XS", "S", "M", "L", "XL"]', '["Black", "Navy", "Burgundy"]', 'images/products/black-dress.jpg', 20, TRUE),
('Leather Jacket', 'Genuine leather jacket with modern styling', 199.99, 4, 'unisex', '["S", "M", "L", "XL"]', '["Black", "Brown", "Tan"]', 'images/products/leather-jacket.jpg', 15, TRUE),

-- Additional Products
('Casual Button Shirt', 'Comfortable button-up shirt for casual occasions', 49.99, 1, 'men', '["S", "M", "L", "XL", "XXL"]', '["White", "Blue", "Light Blue"]', 'images/products/casual-shirt.jpg', 40, FALSE),
('Summer Floral Dress', 'Light and breezy dress perfect for summer days', 89.99, 3, 'women', '["XS", "S", "M", "L", "XL"]', '["Floral", "White", "Pink"]', 'images/products/summer-dress.jpg', 25, FALSE),
('Classic Sneakers', 'Comfortable sneakers for everyday wear', 89.99, 5, 'unisex', '["6", "7", "8", "9", "10", "11", "12"]', '["White", "Black", "Gray"]', 'images/products/sneakers.jpg', 35, FALSE),

-- More T-Shirts
('Premium Black T-Shirt', 'High-quality black t-shirt with superior comfort', 34.99, 1, 'unisex', '["XS", "S", "M", "L", "XL"]', '["Black", "Charcoal", "Navy"]', 'images/products/black-tshirt.jpg', 45, FALSE),
('Vintage Style Tee', 'Retro-inspired t-shirt with vintage appeal', 27.99, 1, 'unisex', '["S", "M", "L", "XL"]', '["Gray", "Cream", "Olive"]', 'images/products/vintage-tee.jpg', 30, FALSE),

-- More Jeans
('Bootcut Jeans', 'Classic bootcut jeans with timeless style', 74.99, 2, 'women', '["26", "28", "30", "32", "34"]', '["Dark Blue", "Medium Blue", "Black"]', 'images/products/bootcut-jeans.jpg', 28, FALSE),
('Relaxed Fit Jeans', 'Comfortable relaxed fit jeans for everyday wear', 69.99, 2, 'men', '["30", "32", "34", "36", "38"]', '["Blue", "Dark Blue", "Gray"]', 'images/products/relaxed-jeans.jpg', 32, FALSE),

-- More Dresses
('Cocktail Dress', 'Stylish cocktail dress for special occasions', 149.99, 3, 'women', '["XS", "S", "M", "L"]', '["Black", "Red", "Navy"]', 'images/products/cocktail-dress.jpg', 18, FALSE),
('Maxi Dress', 'Elegant floor-length dress for formal events', 109.99, 3, 'women', '["XS", "S", "M", "L", "XL"]', '["Black", "Navy", "Burgundy"]', 'images/products/maxi-dress.jpg', 22, FALSE),

-- More Jackets
('Denim Jacket', 'Classic denim jacket with modern fit', 89.99, 4, 'unisex', '["S", "M", "L", "XL"]', '["Blue", "Black", "Light Blue"]', 'images/products/denim-jacket.jpg', 20, FALSE),
('Bomber Jacket', 'Trendy bomber jacket for casual styling', 119.99, 4, 'unisex', '["S", "M", "L", "XL"]', '["Black", "Olive", "Navy"]', 'images/products/bomber-jacket.jpg', 15, FALSE),

-- Shoes
('Running Shoes', 'High-performance running shoes for active lifestyle', 129.99, 5, 'unisex', '["6", "7", "8", "9", "10", "11", "12"]', '["Black", "White", "Blue"]', 'images/products/running-shoes.jpg', 40, FALSE),
('Casual Loafers', 'Comfortable loafers for business casual wear', 99.99, 5, 'unisex', '["6", "7", "8", "9", "10", "11", "12"]', '["Brown", "Black", "Tan"]', 'images/products/loafers.jpg', 25, FALSE);