# Kajak Verleih

## 1. Requirements

Install [PHP](https://www.php.net/manual/de/install.php), [Composer](https://getcomposer.org/)
and [Docker](https://www.docker.com/).

## 2. Development

### 2.1 Start Development Server

Use the following command to start the development server:

```
docker-compose up
```

This will start the php service and the mysql service. Then open `localhost:8080` in the browser.

## 2.2 Development Environment

### 2.2.1 MySQL

When using `docker-compose up`, the database is created automatically. It can then be connected in the IDE or
via [MySQL Workbench](https://dev.mysql.com/downloads/workbench/).

### 2.2.2 Environment File

Fill in the `.env` file with the following values:

```
MYSQL_SERVER=mysql-test-service:3306
MYSQL_DATABASE=db
MYSQL_USERNAME=user
MYSQL_PASSWORD=password

ADMIN_USERNAME=admin
ADMIN_PASSWORD=123

MAIL_USERNAME=foo
MAIL_ADDRESS=foo@bar.de
MAIL_PASSWORD=foobar123
MAIL_HOST=foo.bar.de
MAIL_PORT=587
```