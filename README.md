# BaksDev Menu Admin

![Version](https://img.shields.io/badge/version-7.0.5-blue) ![php 8.2+](https://img.shields.io/badge/php-min%208.1-red.svg)

Модуль меню администратора

## Установка

``` bash
$ composer require baks-dev/menu-admin
```

## Дополнительно

Установка файловых ресурсов в публичную директорию (javascript, css, image ...):

``` bash
$ php bin/console baks:assets:install
```

Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff

$ php bin/console doctrine:migrations:migrate
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.
