# Warehouse Management System (WMS)

Full-stack warehouse management application for inventory, procurement, and staff operations — built as a portfolio project.

**Laravel 12** · **Vue 3** · **PHP 8.2+**

## About

Warehouse CMS is a single-page admin application backed by a REST API. It covers day-to-day warehouse workflows: tracking stock across locations, managing purchase orders and goods receipts, running inventory counts and adjustments, and controlling access with role-based permissions.

### Features

- **Dashboard** — KPI cards (stock value, low stock, pending POs) and procurement trends chart
- **Catalog** — Products, categories, suppliers, barcode support
- **Warehouses** — Multi-warehouse setup with zones, shelves, and locations
- **Inventory** — Stock levels, transactions, adjustments, transfers, cycle counts
- **Procurement** — Purchase orders and goods receipts
- **People** — Users, employees, roles and permissions (Spatie)
- **System** — PDF/Excel reports, audit log, notifications

## Tech Stack

| Layer | Stack |
|-------|-------|
| Backend | Laravel 12, Sanctum, Spatie Permission & Query Builder, Activity Log |
| Frontend | Vue 3, Vue Router, Pinia, Tailwind CSS 4, Vite, ApexCharts |
| Database | MySQL (production) or SQLite (local default) |

## Requirements

- PHP **8.2+** with extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer 2.x
- Node.js **18+** and npm
- MySQL 8+ (optional — SQLite works out of the box for a quick start)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/devilica/warehouse.git
cd warehouse
```

### 2. Install PHP dependencies and configure environment

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

### 3. Set up the database

**SQLite (easiest for local development)**

Ensure `.env` contains:

```
DB_CONNECTION=sqlite
```

Then create the database file and run migrations with seed data:

```bash
touch database/database.sqlite   # Windows: type nul > database\database.sqlite
php artisan migrate --seed
```

**MySQL**

Update the `DB_*` variables in `.env`, create the database, then run:

```bash
php artisan migrate --seed
```

### 4. Install frontend dependencies and run

**Option A — two terminals**

```bash
npm install
npm run dev          # terminal 1
php artisan serve    # terminal 2
```

**Option B — all-in-one dev script**

```bash
npm install
composer dev
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000)

### Quick setup (alternative)

If you prefer a single command after cloning:

```bash
composer setup
php artisan db:seed
npm run dev
```

## Default Login

After seeding, use these accounts:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@wms.test` | `password` |
| Warehouse Manager | `manager@wms.test` | `password` |

`php artisan migrate --seed` loads roles, sample catalog/warehouse data, and demo inventory and purchase order records via `DemoDataSeeder`.

## Useful Commands

| Command | Description |
|---------|-------------|
| `composer dev` | Run Laravel server, queue worker, logs, and Vite together |
| `php artisan test` | Run the test suite |
| `npm run build` | Build frontend assets for production |
| `php artisan db:seed --class=DemoDataSeeder` | Re-seed demo data (skips if purchase orders already exist) |

## Deployment (Vercel)

This project includes a [`vercel.json`](vercel.json) configuration for serverless deployment. Set at minimum:

- `APP_KEY` (copy from local `.env`)
- `APP_URL` (your Vercel domain)
- `DB_*` connection variables for MySQL
- `CACHE_STORE=array`

Run migrations against the production database before going live.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
