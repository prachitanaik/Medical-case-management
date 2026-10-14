# Jajal Case Management System

## 1. Project Overview

The Jajal Case Management System is a web application developed using Laravel. It allows users to log in and manage cases through a simple dashboard.

### Features
- User authentication and login
- Role-based access for Admin and Viewer users
- Dashboard for viewing case information
- Create and edit cases
- View individual case details
- Search and filter cases
- Dummy data for testing and demonstration

The project uses fictional data only and is intended for demonstration and evaluation purposes.

## 2. Installation and Setup

### Requirements
- PHP 8.4 or a compatible version
- Composer
- SQLite
- Git (optional, for cloning the repository)

### Steps

1. Clone or download the project from GitHub.
2. Open a terminal in the project directory.
3. Install the PHP dependencies:

   ```bash
   composer install
   ```

4. Create the environment file:

   ```bash
   cp .env.example .env
   ```

   On Windows PowerShell, use:

   ```powershell
   Copy-Item .env.example .env
   ```

5. Generate the application key:

   ```bash
   php artisan key:generate
   ```

6. Ensure the SQLite database file exists at `database/database.sqlite`. If it does not exist, create an empty file there.

7. Check that the `.env` file uses SQLite and points to the correct database file. For example:

   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

8. Run the database migrations:

   ```bash
   php artisan migrate
   ```

9. Configure the demo account credentials as explained in the Demo Login Setup section. If demo records are needed, run the seeder using the configured credentials:

   ```bash
   php artisan db:seed
   ```

10. Start the development server:

    ```bash
    php artisan serve
    ```

11. Open `http://127.0.0.1:8000` in your browser.

## 3. Running Tests

To run the automated test suite, execute:

```bash
php artisan test
```

The current test run completed successfully with **45 tests passed and 186 assertions**.

The tests cover application functionality, including authentication, case management, and role-related behavior.

## 4. Demo Login Setup

The application supports Admin and Viewer demo accounts.

Configure the following variables in your local `.env` file:

```env
SEED_ADMIN_EMAIL=admin@example.com
SEED_ADMIN_PASSWORD=your-local-admin-password
SEED_VIEWER_EMAIL=viewer@example.com
SEED_VIEWER_PASSWORD=your-local-viewer-password
```

Replace the example password values with your own local demo passwords. These variables are used by the database seeder to create or update the demo accounts.

After configuring them, run:

```bash
php artisan db:seed
```

Then log in using the configured email addresses and passwords.

**Security note:** Use demo credentials only. Never commit your `.env` file, real passwords, API keys, or other secrets to GitHub.

## 5. Known Limitations and Future Improvements

The main implemented features have been tested locally, and the automated test suite passes.

Potential future improvements include:
- Adding more automated tests for unusual inputs and edge cases.
- Improving validation and error messages where needed.
- Enhancing the dashboard with additional reports and statistics.
- Improving the interface for mobile devices.

These are possible future enhancements, not confirmed unfinished requirements. Any feature required by the assignment that remains incomplete should be documented separately, along with the steps needed to finish it.

## 6. Data Privacy

This project uses fictional demonstration data. Do not enter real patient information or other sensitive personal data.

## 7. Technology Stack

- **Backend:** PHP and Laravel
- **Database:** SQLite
- **Frontend:** Project's existing frontend technologies
- **Testing:** Laravel automated testing framework
# Medical-case-management
A Laravel-based case management system with secure login, role-based access, case creation, editing, searching, filtering, and dummy data for testing.
