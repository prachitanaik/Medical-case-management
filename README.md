# Jajal Medical Case Management

A web application for tracking medical implant cases from draft to completion. Authorized users can log in, create cases, update their status, and view dashboard summaries. A JSON REST API using Laravel Sanctum provides access to the same data.

Built for the **Software Developer Intern practical assignment**. All included case data is dummy data; no real patient information should be used.

## Tech Stack

- PHP 8.3+, Laravel 13
- MySQL 8 (automated tests use an in-memory SQLite database)
- Blade templates with plain CSS
- Laravel Sanctum for API authentication
- Eloquent Policies for role-based access control
- PHPUnit for automated testing

## Features

| Screen | Description |
|---|---|
| Login / Logout | Session-based authentication and clear login error messages |
| Dashboard | Displays total, Planning, In Review, Approved, and Completed case counts |
| Case List | Pagination, search, status and priority filters, sorting, and role-based actions |
| Create / Edit | Shared form with validation and field-level error messages |
| Case Details | Displays case information, creator, and timestamps |

## User Roles

| Role | View | Create / Edit | Change Status | Delete |
|---|---|---|---|---|
| Admin | Yes | Yes | Yes | Yes |
| Engineer | Yes | Yes | Yes | No |
| Viewer | Yes | No | No | No |

Role permissions are enforced