# Fairpoint v2 - Laravel Filament Setup Guide

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & pnpm (or npm)
- Database PostgreSQL

## Installation

### 1. Clone and Install Dependencies

```bash
composer install
```

### 2. Environment Configuration

Copy the `.env.example` file to `.env` (if not already present):

```bash
cp .env.example .env
```

Update your `.env` file with your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fairpoint
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Seed Database

#### 5a. Seed Yajra Address Data

Seed the Philippines address lookup data (regions, provinces, cities, barangays):

```bash
php artisan db:seed --class="Yajra\Address\Seeders\AddressSeeder"
```

#### 5b. Seed Application Data

Seed the application's initial data (account classes, business types, tax categories, etc.):

```bash
php artisan db:seed --class="Database\Seeders\DatabaseSeeder"
```

Or seed all at once:

```bash
php artisan db:seed
```

### 6. Install and Build Frontend Assets

```bash
pnpm install
pnpm run build
```

Or if using npm:

```bash
npm install
npm run build
```

### 7. Filament Shield Setup

#### 7a. Install Shield (First Time Only)

If Shield is not yet installed, run:

```bash
php artisan shield:install
```

This will register the Shield plugin and publish necessary files.

#### 7b. Generate Permissions

Generate permissions for all Filament resources, pages, and widgets:

```bash
php artisan shield:generate --all
```

Or generate for specific resources:

```bash
php artisan shield:generate
```

#### 7c. Create Super Admin User

Create a super admin user with full access:

```bash
php artisan shield:super-admin
```

Follow the prompts to enter:
- Name
- Email
- Password

Alternatively, you can create a Filament user and manually assign roles:

```bash
php artisan make:filament-user
```

### 8. Quick Setup (All-in-One)

You can also use the setup script that runs most of the above steps:

```bash
composer run setup
```

**Note:** This script runs:
- `composer install`
- Copies `.env.example` to `.env` (if missing)
- `php artisan key:generate`
- `php artisan migrate --force`
- `pnpm install`
- `pnpm run build`

**Important:** After running `composer run setup`, you still need to:
1. Configure your `.env` file with database credentials
2. Run the seeders (Yajra Address + DatabaseSeeder) - see steps 5a and 5b
3. Run `php artisan shield:generate --all` - see step 7b
4. Create a super admin user - see step 7c

## Development

### Start Development Server

```bash
composer run dev
```

or

```cmd
set FORCE_COLOR=1 && composer run dev
```

or

```powershell
$env:FORCE_COLOR=1
composer run dev
```

This will start:
- Laravel development server
- Vite dev server

### Run Tests

```bash
composer run test
```

## Additional Notes

- **Address Data**: The Yajra Address seeder populates regions, provinces, cities, and barangays tables. This may take a few minutes.
- **Permissions**: After creating new Filament resources, run `php artisan shield:generate --all` to create permissions.
- **Storage Link**: If you need public file storage, run `php artisan storage:link`.
- **Cache**: Clear cache after configuration changes: `php artisan config:clear && php artisan cache:clear`

## Troubleshooting

- **Migration Issues**: If migrations fail, ensure your database is created and credentials are correct.
- **Permission Errors**: Make sure storage and bootstrap/cache directories are writable.
- **Asset Issues**: Clear Vite cache: `rm -rf node_modules/.vite` and rebuild.
