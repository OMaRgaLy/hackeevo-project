#MyProjectFiles
# docker-compose.yml
version: '3.8'

services:
nginx:
image: nginx:stable-alpine  # Используем облегчённую версию
ports:
- "80:80"
volumes:
- ./:/var/www/html
- ./docker/nginx/conf.d:/etc/nginx/conf.d
depends_on:
- php
networks:
- app-network
# Ограничиваем ресурсы
deploy:
resources:
limits:
memory: 128M

php:
build:
context: ./docker/php
dockerfile: Dockerfile
volumes:
- ./:/var/www/html
networks:
- app-network
deploy:
resources:
limits:
memory: 256M  # Учитывая ваш 1GB RAM

mysql:
image: mysql:8.0
environment:
MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}  # Будем использовать переменные окружения
MYSQL_DATABASE: ${DB_DATABASE}
MYSQL_USER: ${DB_USERNAME}
MYSQL_PASSWORD: ${DB_PASSWORD}
ports:
- "3306:3306"
volumes:
- mysql_data:/var/lib/mysql
networks:
- app-network
deploy:
resources:
limits:
memory: 384M  # MySQL нужно больше памяти

networks:
app-network:
driver: bridge

volumes:
mysql_data:
#eof (end of file)
# .env
APP_ENV=development
APP_DEBUG=true
DB_HOST=mysql
DB_ROOT_PASSWORD=root
DB_DATABASE=hackeevo_project
DB_USERNAME=hackeevo_su
DB_PASSWORD=root
#eof
#/public/index.php:
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

set_error_handler(/**
 * @throws ErrorException
 */ function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
});

require_once __DIR__ . '/../src/routes.php';
#eof
