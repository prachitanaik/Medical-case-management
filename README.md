# Jajal Medical Case Management

A small web app for tracking medical implant cases from draft to completion. Authorized users log in, create cases, update their status and see the totals on a dashboard. A JSON REST API (Laravel Sanctum tokens) exposes the same data.

Built for the **Software Developer Intern practical assignment**. All data is dummy data - there is no real patient information anywhere in this project.

## Tech stack

- PHP 8.3+, Laravel 13
- MySQL 8 (SQLite in-memory is used only for the automated tests)
- Blade views with plain CSS (no Node / npm build step needed)
- Laravel Sanctum for API tokens, Eloquent Policies for roles
- PHPUnit for tests

## Features

| Screen | Notes |
| --- | --- |
| Login / Logout | Session auth, clear "Invalid email or password." message, every case page requires login |
| Dashboard | Total, Planning, In Review, Approved, Completed counts |
| Case list | 10 per page, search by Case Number or Surgeon, filter by Status and Priority, sortable columns, View / Edit / Delete buttons only for roles allowed to use them |
| Create / Edit | One shared form, validation errors shown next to each field |
| Case details | All fields, who created the case and when |

### Roles

| Role | View | Create / edit | Change status | Delete |
| --- | --- | --- | --- | --- |
| Admin | Yes | Yes | Yes | Yes |
| Engineer (bonus) | Yes | Yes | Yes | No |
| Viewer | Yes | No | No | No |

Roles are enforced on the server by `App\Policies\MedicalCasePolicy` (web and API). Hiding a button is only cosmetic: a Viewer who sends a delete request directly gets `403`.

## Setup

Requirements: PHP 8.3+ (with `pdo_mysql` and `pdo_sqlite`), Composer 2, MySQL 8.

```bash
# 1. Clone
git clone <your-repo-url>
cd jajal-case-management

# 2. Install dependencies
composer install

# 3. Environment file
cp .env.example .env
php artisan key:generate

# 4. Create the database (use your own MySQL user), then set DB_USERNAME / DB_PASSWORD in .env
mysql -u root -p -e "CREATE DATABASE jajal_cases CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Create tables and load dummy data (2 users + 24 cases)
php artisan migrate --seed

# 6. Run
php artisan serve
```

Open <http://localhost:8000>.

## Test login credentials (dummy)

Created by `php artisan db:seed`. They come from the `SEED_*` variables in `.env` (copied from `.env.example`), so you can change them there before seeding.

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `Admin@12345` |
| Viewer | `viewer@example.com` | `Viewer@12345` |

The Engineer role has no seeded user (the assignment asks for 2 users). To try it, run `php artisan tinker` and execute:

```php
User::factory()->engineer()->create(['email' => 'engineer@example.com']);   // password: "password"
```

## Running the tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (see `phpunit.xml`), so they never touch your MySQL data. The suite covers the 6 required feature tests plus extra checks for roles, validation, filters, the dashboard and the API:

1. Valid login succeeds
2. Invalid login is rejected
3. A guest cannot open case pages or APIs (redirect / 401)
4. Admin can create a case
5. Duplicate Case Number fails (422)
6. Viewer cannot delete a case (403)

## REST API

Base URL: `http://localhost:8000/api`. Send `Accept: application/json`. Authenticated calls need `Authorization: Bearer <token>`.

| Method | Endpoint | Purpose | Who can call |
| --- | --- | --- | --- |
| POST | `/api/login` | Get an API token | Anyone |
| POST | `/api/logout` | Revoke the current token | Logged-in users |
| GET | `/api/cases` | List cases (paginated) | Admin, Engineer, Viewer |
| POST | `/api/cases` | Create a case | Admin, Engineer |
| GET | `/api/cases/{id}` | Get one case | Admin, Engineer, Viewer |
| PUT | `/api/cases/{id}` | Update a case | Admin, Engineer |
| DELETE | `/api/cases/{id}` | Delete a case | Admin |

`GET /api/cases` accepts `search`, `status`, `priority`, `sort`, `direction` (`asc`/`desc`), `per_page` (1-100, default 10) and `page`.

Every response has the same shape:

```json
{ "success": true, "data": { }, "message": "Case created." }
```

List responses add a `meta` object (`current_page`, `last_page`, `per_page`, `total`). Errors use `"success": false`, `"data": null` and a `message`; validation errors (422) also include field-level messages under `errors`:

```json
{
  "success": false,
  "data": null,
  "message": "The given data was invalid.",
  "errors": { "case_number": ["This Case Number is already in use."] }
}
```

Status codes: `200`, `201`, `401` (not logged in), `403` (not allowed), `404` (not found), `422` (validation error).

Example:

```bash
# Get a token
curl -s -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Admin@12345"}'

# Use it
curl -s http://localhost:8000/api/cases -H "Accept: application/json" -H "Authorization: Bearer <token>"
```

A Postman collection is in [`docs/postman_collection.json`](docs/postman_collection.json) (import it, set the `baseUrl` variable, run **Login** first - it stores the token automatically).

## Project documents

- [`docs/database.md`](docs/database.md) - tables, columns and relations
- [`docs/traceability.md`](docs/traceability.md) - requirement -> code -> test
- [`docs/deployment.md`](docs/deployment.md) - how I would host this on AWS

## Security notes

- Role checks use a Laravel Policy (`MedicalCasePolicy`), applied in controllers and Form Requests.
- Only Eloquent / query builder is used; no raw SQL touches user input. Sort columns are whitelisted.
- `$fillable` is set on `MedicalCase`; `created_by` and `role` can never be mass assigned.
- CSRF protection stays on for all web forms. (Laravel skips CSRF checks only while the test suite runs.)
- Login endpoints are rate limited (10 attempts per minute).
- `.env` is git-ignored; only `.env.example` (with dummy values) is committed.

## Assumptions and known limitations

- A "case" model is named `MedicalCase` because `Case` is a reserved word in PHP. It maps to the `cases` table.
- "Change status" is part of editing a case: anyone allowed to edit may change the status. There is no workflow that restricts which status can follow which.
- Case Number is typed by the user (the create form suggests the next one, e.g. `CASE-0021`). The value must be unique.
- Search is a simple `LIKE` match on Case Number and Surgeon Name; fine for small data, a full-text index would be next for large data.
- No user management screen, password reset or email verification - users are created by the seeder or Tinker.
- API tokens do not expire by default (Sanctum default). Next step: set `expiration` in `config/sanctum.php` (publish it first).
- The optional "public holidays" HTTP client task and the live deployment were not done. `docs/deployment.md` covers the AWS plan.
- Audit trail (who changed what) is not implemented; only `created_by`, `created_at` and `updated_at` are stored.
