# Database design

MySQL, created with Laravel migrations in `database/migrations`. Dummy data only.

## Relationship

```text
users (1) ────────────< (many) cases
  id  <──────────────────  created_by (FK)
```

One user can create many cases. Every case has exactly one creator. The foreign key has no cascade: a user who owns cases cannot be deleted by accident.

## Table `users`

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint unsigned, PK | auto increment |
| name | varchar(255) | |
| email | varchar(255) | **unique** |
| email_verified_at | timestamp, null | |
| password | varchar(255) | bcrypt hash |
| role | varchar(20) | `admin`, `engineer` or `viewer`; default `viewer`; indexed |
| remember_token | varchar(100), null | |
| created_at / updated_at | timestamp, null | |

## Table `cases`

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint unsigned, PK | auto increment |
| case_number | varchar(255) | required, **unique index** (e.g. `CASE-0001`) |
| patient_reference | varchar(255) | required, dummy code - not a real name |
| surgeon_name | varchar(255) | required |
| implant_type | varchar(255) | required |
| status | varchar(20) | required, **indexed**; Draft, Planning, In Review, Approved, Completed, Cancelled |
| priority | varchar(20) | required; Low, Medium, High |
| surgery_date | date, null | optional |
| created_by | bigint unsigned | **FK -> users.id**, set from the logged-in user |
| created_at / updated_at | timestamp, null | Laravel timestamps |

Status and priority are stored as strings and validated against PHP enums (`App\Enums\CaseStatus`, `App\Enums\CasePriority`) in the Form Request, so the allowed lists live in one place in code.

## Other tables

- `personal_access_tokens` - Laravel Sanctum API tokens (`tokenable` -> `users`).
- `sessions`, `cache`, `jobs`, `password_reset_tokens` - standard Laravel framework tables.

## Indexes

| Index | Why |
| --- | --- |
| `cases.case_number` unique | Case Number must be unique |
| `cases.status` | Dashboard counts and the status filter |
| `cases.created_by` (foreign key index) | Joins to the creator |
| `users.email` unique | Login lookup |
| `users.role` | Role lookups |

## Seed data

`php artisan migrate --seed` creates 2 users (1 admin, 1 viewer) and 24 cases (`CASE-0001` to `CASE-0024`) through `UserFactory` and `MedicalCaseFactory`.
