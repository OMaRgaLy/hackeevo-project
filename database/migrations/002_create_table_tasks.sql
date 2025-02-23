CREATE TABLE IF NOT EXISTS users (
                                     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                     nickname VARCHAR(63) NOT NULL UNIQUE,
                                     firstname VARCHAR(63) NOT NULL,
                                     lastname VARCHAR(63),
                                     phone VARCHAR(15) UNIQUE,
                                     email VARCHAR(255) NOT NULL UNIQUE,
                                     bio TEXT,
                                     role ENUM('base', 'moderator', 'admin') NOT NULL DEFAULT 'base',
                                     avatar VARCHAR(255),
                                     password VARCHAR(255) NOT NULL,
                                     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
                                     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                     title VARCHAR(255) NOT NULL,
                                     description TEXT,
                                     status ENUM('new', 'in_progress', 'completed') DEFAULT 'new',
                                     priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                                     user_id INT UNSIGNED NOT NULL,
                                     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                     FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Индексы для оптимизации поиска
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_priority ON tasks(priority);