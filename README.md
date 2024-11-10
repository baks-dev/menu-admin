# BaksDev Menu Admin

[![Version](https://img.shields.io/badge/version-7.1.9-blue)](https://github.com/baks-dev/menu-admin/releases)
![php 8.3+](https://img.shields.io/badge/php-min%208.3-red.svg)

Модуль меню администратора

## Установка

``` bash
$ composer require baks-dev/menu-admin
```

## Дополнительно

Установка конфигурации и файловых ресурсов:

``` bash
$ php bin/console baks:assets:install
```

Обновите меню


``` bash
$ php bin/console baks:menu-admin:section
$ php bin/console baks:menu-admin:path

```

Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff

$ php bin/console doctrine:migrations:migrate
```

## Тестирование

``` bash
$ php bin/phpunit --group=nenu-admin
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.

