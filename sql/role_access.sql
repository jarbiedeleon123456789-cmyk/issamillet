-- Run against defaultdb if setting up a fresh copy of the role-based app.
USE defaultdb;

SET @add_role_column = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT ''user''',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'role'
);
PREPARE add_role_column FROM @add_role_column;
EXECUTE add_role_column;
DEALLOCATE PREPARE add_role_column;

INSERT INTO users (firstname, lastname, email, username, password, role)
SELECT 'Pantry', 'Admin', 'admin@marrows.local', 'admin', '$2y$12$Cg1.zk4hIYqBMlG88DEFYuKMks86UAAkvENzgP0Llt5LovoBOEuGy', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

INSERT INTO users (firstname, lastname, email, username, password, role)
SELECT 'Pantry', 'User', 'user@marrows.local', 'user', '$2y$12$Cg1.zk4hIYqBMlG88DEFYuKMks86UAAkvENzgP0Llt5LovoBOEuGy', 'user'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'user');
