# 📚 Innovatech Library — Mini Library Management System

A complete **Library Management System** built with **Laravel 8** for the *Senior Backend Engineer (PHP/Laravel)* technical challenge.

It ships as two complementary surfaces:

- A **robust REST API** (JSON) secured with **Laravel Sanctum** tokens and **role-based permissions**.
- A **web application** (Blade + Bootstrap 5) that works like a real library catalog: browse, search, borrow and return books, plus staff/admin management screens.

---

## ✅ Features

### Core (assignment requirements)
- **Book management** — add, edit and delete books (`title`, `author`, `isbn`, `publisher`, `category`, `language`, `published_year`, `total_copies`, `description`, `cover_url`).
- **Check-out / Check-in** — borrow a copy (creates a loan with a due date) and return it. Availability is always derived from real loan state, so multiple copies and concurrency are handled correctly.
- **Search** — free-text search across `title`, `author`, `publisher`, `isbn`, `category` and `description`, with extra filters (`category`, `available`, sorting) and pagination.

### Roles & permissions
| Role | Permissions |
| --- | --- |
| **Admin** | Everything a librarian can do + manage users and change roles |
| **Librarian** | Manage the catalog (CRUD books), view/return **all** loans, check books out on behalf of members, view stats |
| **Member** | Browse/search the catalog, check books out for themselves, return their own books, view own history |
| Guest | Browse and search the public catalog, register |

### Extra / creative features
- **Recommendations engine** — hybrid collaborative + content-based suggestions ("Readers also like" and personalized picks on the dashboard) and a most-popular feed.
- **Overdue detection** — loans overdue are detected automatically and surfaced in dashboards, admin screens and API filters.
- **Self-service returns** — members can return their own copies without staff.
- **Stats dashboard** — totals, availability, overdue count, top categories and recent activity.
- **User management (admin)** — search members/staff and change roles from the web UI or the API.
- **Validation everywhere** — ISBN uniqueness, copy limits, business-rule guards (cannot borrow a book with no copies, cannot borrow the same book twice, cannot delete a book that is currently checked out).
- **Seed data** — demo users, a realistic catalog and loan history so the app is alive on first run.
- **Test suite** — 35 feature tests covering auth, roles, book CRUD, search, check-out/check-in flows and web pages.

---

## 🧰 Tech stack

- PHP 7.4+ / 8.x (tested on PHP 8.3)
- Laravel 8.83
- Laravel Sanctum (API tokens)
- SQLite by default / MySQL optional
- Bootstrap 5 (CDN) for the UI
- PHPUnit 9

---

## 📁 Project layout

The Laravel application lives in the **`app/`** folder:

```
app/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Web + Api controllers
│   │   ├── Middleware/           # EnsureUserHasRole
│   │   ├── Requests/             # StoreBookRequest, UpdateBookRequest
│   │   └── Resources/            # BookResource, BookLoanResource, UserResource
│   ├── Models/                   # Book, BookLoan, User (role-aware)
│   ├── Services/                 # LibraryService, StatsService, RecommendationService
│   └── Exceptions/               # LibraryRuleException
├── database/
│   ├── migrations/               # users (+role), books, book_loans
│   ├── factories/                # User, Book, BookLoan
│   └── seeders/                  # demo users, catalog and loan history
├── resources/views/              # Blade UI (catalog, dashboard, management…)
├── routes/                       # web.php + api.php
└── tests/Feature/                # 35 tests (API + web)
```

---

## 🚀 How to run the project

### 1. Prerequisites
- **PHP** ≥ 7.4 (8.x recommended) with the `pdo_sqlite`, `openssl`, `mbstring` and `fileinfo` extensions.
- **Composer**.
- No database server is required: the project uses **SQLite** out of the box.

> PHP for Windows / WAMP users: make sure PHP is on your `PATH`, or call it explicitly, e.g. `C:\wamp64\bin\php\php8.3.14\php.exe`.

### 2. Install dependencies
```bash
cd app
composer install
```

### 3. Configure the environment
```bash
copy .env.example .env        # Windows
# or: cp .env.example .env    # Linux / macOS
```

Create the SQLite database file (the folder already includes an empty one, but this ensures a clean start):

```bash
php -r "touch('database/database.sqlite');"   # or right-click New > Text file
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Migrate and seed (demo data)
```bash
php artisan migrate --seed
```

This creates the schema and loads demo users, a catalog of books and loan history.

### 5. Start the development server
```bash
php artisan serve
```

Open **http://localhost:8000** in your browser.

> Optional: `npm install && npm run dev` to compile the (empty) Laravel Mix assets. Not required for the demo.

### 6. Run the tests
```bash
php artisan test
```

---

## 🔑 Demo accounts (after `migrate --seed`)

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@library.test` | `password` |
| Librarian | `librarian@library.test` | `password` |
| Member | `member@library.test` | `password` |

All accounts created via the **Register** page or `POST /api/register` get the `member` role by default.

---

## 🗄️ Switching to MySQL (optional)

Edit the `.env` file in `app/`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then run `php artisan migrate --seed` again.

---

## 🌐 REST API reference

Base URL: `http://localhost:8000/api`

Add the header `Accept: application/json` on every request. Authenticated endpoints require `Authorization: Bearer {token}`.

### Authentication
| Method | Endpoint | Body | Description |
| --- | --- | --- | --- |
| POST | `/api/register` | `name`, `email`, `password`, `password_confirmation` | Create a member account → returns `token` |
| POST | `/api/login` | `email`, `password` | Login → returns `token` |
| POST | `/api/logout` | — | Revoke the current token (auth) |
| GET | `/api/me` | — | Current authenticated user (auth) |

### Books
| Method | Endpoint | Access | Description |
| --- | --- | --- | --- |
| GET | `/api/books` | Public | List/search books (`q`, `category`, `author`, `available`, `sort`, `per_page`, `page`) |
| GET | `/api/books/{id}` | Public | Book details with live availability |
| GET | `/api/books/{id}/recommendations` | Public | Similar books |
| POST | `/api/books` | librarian, admin | Create a book |
| PUT/PATCH | `/api/books/{id}` | librarian, admin | Update a book (partial updates allowed) |
| DELETE | `/api/books/{id}` | librarian, admin | Delete a book (blocked while copies are checked out) |

Available `sort` values: `newest` (default), `oldest`, `title`, `author`, `popular`, `available`.

### Loans
| Method | Endpoint | Access | Description |
| --- | --- | --- | --- |
| POST | `/api/books/{id}/checkout` | member, librarian, admin | Check a copy out. Staff may pass `user_id` to borrow on behalf of a member. Optional `days` (1–30, default 14) |
| POST | `/api/loans/{id}/check-in` | member (own), staff (any) | Return the copy |
| GET | `/api/loans` | auth | Member: own loans only. Staff: all loans, filters `status=active|overdue|returned`, `user_id` |

### Stats & users
| Method | Endpoint | Access | Description |
| --- | --- | --- | --- |
| GET | `/api/stats` | auth | Library-wide statistics |
| GET | `/api/admin/users` | admin | List users (`q`, `role`, paginated) |
| PUT | `/api/admin/users/{id}` | admin | Change a user's role |

### Example API flow
```bash
# 1) Login and store the token
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"member@library.test","password":"password"}' | php -r 'echo json_decode(stream_get_contents(STDIN))->token;')

# 2) Search the catalog
curl -s "http://localhost:8000/api/books?q=Clean&available=1" -H "Accept: application/json"

# 3) Check a book out
curl -s -X POST http://localhost:8000/api/books/1/checkout \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN"
```

---

## 🧠 Design notes

- **Availability is derived, never stored**: `available_copies = total_copies − active_loans`. This avoids drift between the books table and the loans table.
- **Check-out/Check-in rules live in a single service** (`App\Services\LibraryService`) shared by the web UI and the API — no duplicated business logic.
- **Loans are immutable history**: returning a copy only sets `returned_at`, so the full borrowing history remains available for stats and recommendations.
- **Recommendations** combine collaborative signals (readers who borrowed the same books) with content signals (favourite categories/authors), falling back to popularity.
- **Role checks** are enforced with a reusable `role` route middleware for both web and API.

---

## 📜 Assignment coverage

The implementation follows the *"Mini Library Management System Challenge"*:

- ✅ **Book Management** (add / edit / delete + rich metadata)
- ✅ **Check-in / Check-out** (borrow and return with real availability)
- ✅ **Search** (title, author and other fields + filters)
- ✅ **Source code + README on how to run it**
- ✅ **Authentication with roles and permissions** (admin / librarian / member)
- ✅ **AI-flavoured features** (smart recommendations + "Readers also like")
- ✅ **Most valuable extra features** (overdue tracking, stats, self-service returns, user management)


