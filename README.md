# Kajak Verleih

## 1. Requirements

Install [PHP](https://www.php.net/manual/de/install.php) and [Composer](https://getcomposer.org/).

## 2. Development

### 2.1 Start Development Server

```
docker-compose up
```

Then call `localhost:8080`. This will start the php service and the mysql service.

## 2.2 Development Environment

### 2.2.1 MySQL

When using `docker-compose up`, the database is created automatically. It can then be connected in the IDE or via [MySQL Workbench](https://dev.mysql.com/downloads/workbench/).