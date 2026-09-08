# 📚 Innovatech Library (Laravel application)

This is the Laravel application behind the **Innovatech Library** Mini Library Management System.
The full documentation (features, roles, API reference, demo accounts and design notes) lives in the repository root [`README.md`](../README.md).

## Quick start

```bash
# from this directory (app/)
composer install

# environment
copy .env.example .env          # Windows
# cp .env.example .env          # Linux/macOS

# database (SQLite by default — no server needed)
php -r "touch('database/database.sqlite');"
php artisan key:generate

# schema + demo data
php artisan migrate --seed

# web server
php artisan serve
```

Open **http://localhost:8000**.

## Demo accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@library.test` | `password` |
| Librarian | `librarian@library.test` | `password` |
| Member | `member@library.test` | `password` |

## Tests

```bash
php artisan test
```

## Main routes

| Area | Routes |
| --- | --- |
| Web | `/` catalog, `/books/{id}`, `/dashboard`, `/login`, `/register`, `/admin/books`, `/admin/loans`, `/admin/users` |
| API | `/api/books`, `/api/books/{id}`, `/api/books/{id}/checkout`, `/api/loans`, `/api/loans/{id}/check-in`, `/api/stats`, `/api/me`, `/api/login`, `/api/register` |

## Requirements

- PHP 7.4+ (8.x recommended) with `pdo_sqlite`, `openssl`, `mbstring`, `fileinfo`
- Composer
