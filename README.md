<h1 align="center">Task Manager API</h1>
Task Manager - Backend-сервис на PHP и MySQL для управления задачами и пользователями. Работает через API, запущен в Docker-контейнерах (nginx, php-fpm, mysql) по адресу: http://195.80.238.99/. Реализована обработка транзакций (удаление пользователя и его задач с commit/rollback), тестирование API проводил в Postman и деплой через Git.

## Содержание
- [Технологии](#технологии)
- [Использование](#использование)
- [Тестирование](#тестирование)
- [Команда проекта](#команда-проекта)

## Технологии
- [PHP](https://www.php.net/)
- [nginx](https://nginx.org/)
- [MySQL](https://www.mysql.com/)
- [Docker](https://www.docker.com/)
- [Git](https://git-scm.com/)
- ...

## Использование
Перед запуском проекта необходимо создать файл `.env` в корневой директории и заполнить его переменными окружения.  
Пример `.env` файла:

```ini
# Основные настройки приложения
APP_ENV=development
APP_DEBUG=true
DB_HOST=mysql
DB_ROOT_PASSWORD=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_CHARSET=utf8mb4
JWT_SECRET=
```

Далее нужно запустить проект и выполнить следующие команды: 
```sh
docker-compose up -d
docker ps
docker exec -it {mysql_container_name) bash
mysql -u root -p
{DB_ROOT_PASSWORD} //введите свой пароль от БД
//Далее создайте таблицы users и tasks из SQL кода в /Database/Migrations/*.sql
exit
docker-compose down
docker-compose up -d
```

Для использования потребуется Postman или cURL.

## Тестирование
В папке TaskAPI имеются выгруженные коллекции Postman с документацией API как для /tasks так и для /users. А также файл с окружением Postman environment.
Для начала нужно перейти по ссылке: http://{{baseUrl}}/auth/register и ввести данные в следующем формате:
```json
{
    "nickname":"uniquenickname",
    "firstname":"name",
    "lastname":"lastname",
    "email":"uniquemail@gmail.com",
    "password":"somepassword"
    }
```
полученный JWT Token нужно будет сохранить в Environment для работы, а в качестве baseUrl можно использовать localhost. Благодаря импортированным коллекциям и документации удобно пользоваться API.

## Команда проекта
В команде пока только я один.

- [Омаргалы Битебаев](tg://resolve?domain=hAckeeVo) — Backend Developer
