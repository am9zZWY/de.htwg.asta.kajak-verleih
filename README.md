# Kajak Verleih

## 1. Requirements

Install [PHP](https://www.php.net/manual/de/install.php) and [Composer](https://getcomposer.org/).

## 2. Development

### 2.1 Start Development Server

#### 2.1.1 Via Docker

```
docker-compose up
```

Then call `localhost:8080`.

#### 2.1.2 Via Composer

```shell
composer start
```

Then call `localhost:8080`.

#### 2.1.3 Via PHP

```shell
php -S 127.0.0.1:8080
```

Then call `localhost:8080`.

## 2.2 Development Environment

### 2.2.1 MySQL

When using `docker-compose up`, the database is created automatically. It can then be connected in the IDE or via [MySQL Workbench](https://dev.mysql.com/downloads/workbench/).