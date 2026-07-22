# ГравЕкб PHP

Интернет-магазин оснасток на **PHP 8.1+** и **SQLite**. Отдельный проект от Next.js-версии.

## Требования

- PHP 8.1+ с расширениями `pdo_sqlite`, `json`, `mbstring`, `openssl`
- Apache `mod_rewrite` (или встроенный PHP-сервер для локальной проверки)

## Быстрый старт (локально)

```bash
cp config.example.php config.php
# отредактируйте config.php при необходимости

php scripts/migrate.php
cd public
php -S localhost:8080 ../scripts/router.php
```

Откройте http://localhost:8080

Админка: http://localhost:8080/admin  
Логин/пароль — из `config.php` (`admin_login` / `admin_password`).

## Деплой на Timeweb (shared)

1. Загрузите файлы проекта на хостинг.
2. Document root сайта укажите на папку `public/`.
3. Папка `data/` должна быть **выше** web-root или закрыта от HTTP.
4. Скопируйте `config.example.php` → `config.php` и заполните:
   - `site_url` — ваш домен `https://...`
   - `admin_*`, `company`, `smtp`, `telegram` (по желанию)
5. На сервере выполните: `php scripts/migrate.php`
6. Права на запись для `data/` (создание `gravekb.sqlite`).

## Структура

- `public/` — точка входа и статика
- `app/` — PHP-логика
- `templates/` — HTML-шаблоны (дизайн 1:1 с текущим сайтом)
- `data/seed/` — JSON каталога для импорта
- `data/gravekb.sqlite` — база (не в git)

## Функции

Каталог, поиск, корзина, оформление заказа со счётом, регистрация/кабинет, контакт-форма, админка (заявки, клиенты, товары), УПД, уведомления SMTP/Telegram.
