-- Laboratory Exercise No. 5 - Marrow's Pantry
-- Run this script in Navicat on the Aiven MySQL database selected for the app.
-- This matches the default database shown in the Aiven connection details.

CREATE DATABASE IF NOT EXISTS defaultdb;
USE defaultdb;

-- Keep this script usable with either a fresh database or the Lab 4 table.
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MySQL versions without ADD COLUMN IF NOT EXISTS support need this check.
SET @add_password_column = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN password VARCHAR(255) NULL',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'password'
);
PREPARE add_password_column FROM @add_password_column;
EXECUTE add_password_column;
DEALLOCATE PREPARE add_password_column;

UPDATE users
SET password = '$2y$12$Cg1.zk4hIYqBMlG88DEFYuKMks86UAAkvENzgP0Llt5LovoBOEuGy'
WHERE username = 'dyarbe' AND (password IS NULL OR password = '');

INSERT INTO users (firstname, lastname, email, username, password, role)
SELECT 'Pantry', 'Admin', 'admin@marrows.local', 'admin', '$2y$12$Cg1.zk4hIYqBMlG88DEFYuKMks86UAAkvENzgP0Llt5LovoBOEuGy', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

INSERT INTO users (firstname, lastname, email, username, password, role)
SELECT 'Pantry', 'User', 'user@marrows.local', 'user', '$2y$12$Cg1.zk4hIYqBMlG88DEFYuKMks86UAAkvENzgP0Llt5LovoBOEuGy', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'user');

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional starter records for the first screenshot.
INSERT INTO products (product_name, description, price, quantity)
SELECT 'Roasted marrow bones', 'House-roasted bones with sourdough and herb salt.', 18.00, 12
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

INSERT INTO products (product_name, description, price, quantity)
SELECT 'Smoked tomato jam', 'Slow-cooked tomatoes, brown sugar, vinegar, and spice.', 9.50, 24
WHERE (SELECT COUNT(*) FROM products) = 1;

SELECT * FROM products ORDER BY created_at DESC;