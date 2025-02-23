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

