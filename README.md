# AION PTS Website

**AION PTS Web CMS** built with **Laravel 12** and **Filament 5**.

A web portal and administration panel for AION private-server projects. The application connects to a website database for CMS data and a separate Microsoft SQL Server database for AION account/game data.

> **Project status:** This repository is under active development. Installation and compatibility should be verified against your specific AION Producer/database schema before using it on a production server.

## Features

- Public website pages, news, downloads, support FAQs, and donation options.
- Filament administration panel and website settings.
- AION account registration and password-change integration through SQL Server stored procedures.
- AION account and character-related pages, subject to the connected Producer schema.
- Game-server reachability/status display and player activity widgets.
- Module/plugin management facilities.
- Gemini-powered news-writing integration when a valid API key is configured.

Some features depend on the schema and stored procedures available in the selected AION Producer. Review the SQL installation section before enabling registration or password changes.

## Requirements

- PHP **8.2 or newer**, with the PHP extensions required by Laravel 12 and the project dependencies.
- Composer 2.
- Node.js and npm, for frontend asset installation/building.
- Microsoft SQL Server and the PHP `sqlsrv` / `pdo_sqlsrv` drivers enabled for the PHP runtime.
- An AION Producer database compatible with the application's expected tables and stored procedures.
- A separate website database, recommended for the CMS data.

Confirm the PHP extensions and SQL Server drivers in the same PHP environment used to run the web server and CLI commands.

## Installation

### 1. Get the source

```bash
git clone https://github.com/<OWNER>/<REPOSITORY>.git
cd <REPOSITORY>
```

Replace the URL and directory with the repository you published.

### 2. Install PHP dependencies and create the environment file

```bash
composer install
```

Copy the example environment file:

**Windows (PowerShell):**

```powershell
Copy-Item .env.example .env
```

**Linux/macOS/Git Bash:**

```bash
cp .env.example .env
```

Generate a unique application key:

```bash
php artisan key:generate
```

Never publish your real `.env` file or share its secrets. Each installation must use its own `APP_KEY` and credentials.

### 3. Configure `.env`

Set the website database connection using `DB_*`. Set the AION account database connection using `AION_DB_*`.

Example structure (replace all example values for your environment):

```dotenv
APP_NAME="Aion PTS Website"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=

# Website/CMS database
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=AionPTS_Web
DB_USERNAME=your_website_db_user
DB_PASSWORD=replace_me
DB_TRUST_SERVER_CERTIFICATE=true

# AION account database
AION_DB_CONNECTION=aion_game
AION_DB_HOST=127.0.0.1
AION_DB_PORT=1433
AION_DB_DATABASE=AionAccounts
AION_DB_USERNAME=your_aion_db_user
AION_DB_PASSWORD=replace_me
AION_DB_TRUST_SERVER_CERTIFICATE=true

# Separate admin session cookie
ADMIN_SESSION_COOKIE=aion-pts-admin-session

# Optional Gemini integration
GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.5-flash-lite
```

The example above is a template, not a universal Producer configuration. `AION_DB_DATABASE`, `AION_DB_USERNAME`, and other values must match your SQL Server setup. The connection name `aion_game` is defined in this project's `config/database.php`.

For production, use `APP_DEBUG=false`, HTTPS, strong least-privilege SQL logins, and a unique `APP_KEY`. Do not use the SQL Server `sa` account for the website unless you understand and accept the security implications. Do not commit credentials to Git.

Other supported environment variables are documented in `.env.example` and the files under `config/`.

### 4. Prepare the AION Producer database

**Back up the AION account database before running any SQL script.** Run these scripts in SQL Server Management Studio (SSMS) against the intended AionAccounts database, in this order:

1. `sql/AionAccounts.web_account.sql` — creates `dbo.web_account` if it does not exist. Review the file before running it and confirm that it does not drop or recreate an existing table.
2. `sql/AionAccounts.ap_AutoReg.sql` — creates/updates `dbo.ap_AutoReg`, used for website account registration.
3. `sql/AionAccounts.ap_AutoChangePwd.sql` — creates/updates `dbo.ap_AutoChangePwd`, used for password changes.
4. `sql/fix_ap_SetConcurrentUserStatistics.sql` — **optional**. Apply only if the Producer has the expected `dbo.user_count` schema and you intend to use this compatibility fix.

The procedure scripts currently contain `USE [AionAccounts]`. If your account database has a different name, update the `USE` statement to the correct database before execution. The SQL scripts assume that Producer tables such as `dbo.user_auth`, `dbo.user_account`, `dbo.ssn`, and `dbo.user_info` already exist and have compatible columns.

> Do not run a script that drops or recreates an existing table on a live database. The `web_account` installer must preserve existing rows and must not contain a `DROP TABLE` operation. If your local copy differs from this rule, correct and review it before running.

The scripts are SQL Server scripts and contain batch separators (`GO`); execute them in SSMS or another SQL Server tool that supports `GO`, not as a single Laravel migration.

### 5. Run website migrations

After confirming that `DB_*` points to the **website/CMS database**, run:

```bash
php artisan migrate
```

Do not point the Laravel migration command at the AION account database. Keep CMS tables and game/Producer tables separated unless you have intentionally designed and reviewed a different deployment.

### 6. Install and build frontend assets

```bash
npm ci
npm run build
```

### 7. Start locally

```bash
php artisan serve
```

Open the URL printed by Laravel, commonly `http://127.0.0.1:8000`. For local development with live asset rebuilding, use `npm run dev` in a separate terminal.

## Production deployment checklist

- Configure the real domain and HTTPS in `APP_URL`.
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Use unique application and database credentials; never deploy a development `.env`.
- Ensure the PHP web-server user can write to `storage/` and `bootstrap/cache/`.
- Build assets with `npm ci && npm run build`.
- Run migrations only against the website database after taking a backup.
- Verify the AION SQL Server connection and Producer schema before enabling registration/password-change features.
- Test admin login, game-account login, session separation, logout behavior, CSRF/Livewire requests, and plugin enable/disable behavior.
- Confirm the web server document root points to Laravel's `public/` directory, not the project root.
- Configure backups and monitor Laravel logs without publishing log files.

## Troubleshooting

### SQL Server connection errors

Check that SQL Server accepts connections, the server/instance and port are correct, the PHP `sqlsrv` and `pdo_sqlsrv` extensions are enabled, and the configured login has only the required permissions.

### Registration or password changes fail

Verify that the Producer tables and the `dbo.ap_AutoReg` / `dbo.ap_AutoChangePwd` procedures exist in the configured AION account database. Check procedure parameters and password format against your exact Producer version. Do not run the SQL scripts against a different database by mistake.

### Missing frontend styling or scripts

Run `npm ci` and `npm run build`, then confirm the `public/build` assets are deployed.

### Admin session / CSRF errors

Verify that the admin session cookie configuration is present, clear stale browser cookies for the site, run `php artisan optimize:clear`, and retest admin and game-account login/logout independently. Validate this on the actual deployment; do not assume configuration alone proves the issue is resolved.

## Development checks

```bash
php artisan test
```

Run tests after installing dependencies and configuring a suitable test environment. A passing syntax check alone does not guarantee SQL Server/Producer compatibility or end-to-end behavior.

## Security

Do not commit `.env`, API keys, database passwords, local database files, logs, or production data. If a secret was committed or exposed, rotate it; deleting the file from the latest commit does not remove it from Git history.

For a security concern, do not publish credentials or exploit details in a public issue. Contact the repository maintainer privately.

## License

Add the project's intended license before publishing. Until a license is explicitly included, do not assume that the source is available for unrestricted reuse or redistribution.
