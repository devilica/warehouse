# Warehouse Management System (WMS)

Full-stack warehouse management application for inventory, procurement, and staff operations — built as a portfolio project.

**Laravel 12** · **Vue 3** · **PHP 8.2+**

**Preview:** the application is available at [https://warehouse-jrfx.vercel.app/](https://warehouse-jrfx.vercel.app/)

## Concept

Warehouse CMS is a single-company admin for the people who run a warehouse. Staff use one Vue application. That application talks to one Laravel API, which records stock, purchasing, and who is allowed to do what.

The product is organized around a working day, not around empty tables:

- See whether stock is healthy, low, or gone, and how purchasing has moved over time.
- Keep a catalog of products, categories, and suppliers, stored across warehouses, zones, shelves, and locations.
- Move stock with adjustments, transfers, and cycle counts.
- Buy stock through purchase orders and confirm it with goods receipts.
- Sign in as a person with a role, and only see the actions that role allows.

The seeded demo is meant to look occupied: lists have multiple pages of realistic rows, and the dashboard has numbers, a trend, and recent activity.

### Features

- **Dashboard** — stock value, low stock, out of stock, pending purchase orders, and a procurement trend
- **Catalog** — products, nested categories, suppliers, and barcodes
- **Warehouses** — several warehouses, each with zones, shelves, and locations
- **Inventory** — stock levels, movements, adjustments, transfers, and cycle counts
- **Procurement** — purchase orders and goods receipts
- **People** — users, employees, and roles (Spatie Permission)
- **System** — PDF, Excel, and CSV reports, an audit log, and notifications
- **Responsive** — on a phone the menu opens from a button and lists become cards; on a wide screen the sidebar and tables stay in place

## Getting started

### Requirements

- PHP **8.2+** with `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, and `bcmath`
- Composer 2.x
- Node.js **18+** and npm
- MySQL 8+ if you are not using SQLite

### Install

```bash
git clone https://github.com/devilica/warehouse.git
cd warehouse
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

### Database

SQLite is the fastest local setup. In `.env`:

```
DB_CONNECTION=sqlite
```

Then:

```bash
touch database/database.sqlite   # Windows: type nul > database\database.sqlite
php artisan migrate --seed
```

For MySQL, set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`, create the database, and run the same `php artisan migrate --seed`. TLS is configured with `MYSQL_ATTR_SSL_CA` pointing at a CA file. `DB_SSL` is not read by Laravel.

`migrate --seed` creates roles, the admin accounts, and demo operations data. To add more demo rows later without deleting what is already there:

```bash
php artisan db:seed --class=DemoDataSeeder
```

The seeder tops each business table up to a target count. It does not wipe users or re-run from an empty database.

### Run

Two terminals:

```bash
npm install
npm run dev          # terminal 1
php artisan serve    # terminal 2
```

Or everything in one process (server, queue, logs, and Vite):

```bash
composer dev
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

On Windows, `php artisan serve` loads [bootstrap/dev-router.php](bootstrap/dev-router.php) so the built-in server finds `public/index.php`. That file is part of the repo.

With a remote database such as Aiven, local speed depends mostly on PHP itself:

- Enable OPcache in `php.ini` (`zend_extension=opcache`, `opcache.enable=1`, `opcache.enable_cli=1`). Without it, every request recompiles Laravel, which takes about half a second.
- Set `DB_PERSISTENT=true` so the built-in server reuses one TLS connection instead of opening a new one for each request.
- Keep `SESSION_DRIVER=file` and `CACHE_STORE=file`, so sessions and cache are not stored in the remote database.

### Sign in

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@wms.test` | `password` |
| Warehouse Manager | `manager@wms.test` | `password` |

Extra staff accounts created by the demo seeder use the same password. These accounts are for a local demo only.

## AI tools and development process

The application was built in Cursor. The in-editor agent was used in passes, not as a single generation:

1. Scaffold the API. Generators in [scripts/](scripts/) wrote services, controllers, and related classes from a repeated shape, then those files were kept under version control and edited by hand where the workflow needed real rules.
2. Make the demo look lived-in. `DemoDataSeeder` was changed so a second run fills tables up to a target instead of stopping at the first row.
3. Make the screens consistent. Row actions, modal footers, and report downloads were aligned to one button order and one set of styles, then checked in the browser.
4. Write this guide from the project as it actually runs, including the limits below.

Each pass was reviewed against the running app. Generated code that did not match a status rule or a screen was corrected rather than left as the model first wrote it.

## Architectural decisions

The UI is a Vue 3 single-page app (Vue Router, Pinia, Tailwind CSS, Vite). It calls `/api/v1` with a Sanctum bearer token stored in the browser. The layout is responsive: below 1024 px the sidebar is hidden behind a menu button and each table is shown as cards, so the screens fit a phone.

Controllers stay thin. A status change such as sending a purchase order or shipping a transfer is decided in a service, for example [app/Services/PurchaseOrderService.php](app/Services/PurchaseOrderService.php), [app/Services/StockTransferService.php](app/Services/StockTransferService.php), and [app/Services/InventoryService.php](app/Services/InventoryService.php). The service checks the current status and records the stock movement. The screen only shows the button when that same status applies.

Access has two layers. Spatie roles decide which permission a person has. Laravel policies enforce that permission on the model. List endpoints use Spatie Query Builder for filters and includes. Product categories use a nested set so a category can sit under a parent.

The dashboard reads aggregate queries and caches them briefly. The demo seeder clears that cache after it writes, so the first load shows the new numbers.

These patterns are the ones the API is built on:

1. Form requests validate input before the controller runs. Shared rules live in [app/Http/Requests](app/Http/Requests), including zone, shelf, and location requests under [app/Http/Requests/Warehouse](app/Http/Requests/Warehouse).
2. Policies gate every model. Each policy is in [app/Policies](app/Policies) and registered in [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php).
3. API resources name the fields a screen reads. [app/Http/Resources/ProductResource.php](app/Http/Resources/ProductResource.php), [app/Http/Resources/PurchaseOrderResource.php](app/Http/Resources/PurchaseOrderResource.php), and [app/Http/Resources/StockLevelResource.php](app/Http/Resources/StockLevelResource.php) list those fields and any relation that was loaded.
4. Stock, receipt, and order writes run inside a database transaction. The stock write is in [app/Domains/Inventory/Services/InventoryService.php](app/Domains/Inventory/Services/InventoryService.php).
5. Eloquent relationships stay on the models. [app/Models/Product.php](app/Models/Product.php) links a product to its category, supplier, barcodes, and stock.
6. Services hold the workflow. Status changes and stock movements go through classes in [app/Services](app/Services).
7. Pest covers the behavior that must stay true. The suite lives in [tests](tests) and runs with `php artisan test`.
8. Slow work is queued. [app/Jobs/CheckExpiringProducts.php](app/Jobs/CheckExpiringProducts.php) runs daily, and [app/Listeners/SendLowStockNotification.php](app/Listeners/SendLowStockNotification.php) implements `ShouldQueue`.
9. Domain events notify listeners. Events are in [app/Events](app/Events), listeners in [app/Listeners](app/Listeners), and the map is [app/Providers/EventServiceProvider.php](app/Providers/EventServiceProvider.php).
10. Changes to products and purchase orders are stored by Spatie's activity log on [app/Models/Product.php](app/Models/Product.php) and [app/Models/PurchaseOrder.php](app/Models/PurchaseOrder.php). The audit screen still reads [app/Services/AuditService.php](app/Services/AuditService.php).
11. The API allows 60 requests a minute per user or IP address, and login allows 5 a minute per IP address. Both limiters are defined in [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php). The login route uses `throttle:login`.

## Quality assurance

Automated checks are Pest tests, run with:

```bash
php artisan test
```

They cover sign-in and the current user ([tests/Feature/AuthTest.php](tests/Feature/AuthTest.php)), stock movements ([tests/Feature/InventoryServiceTest.php](tests/Feature/InventoryServiceTest.php) and [tests/Unit/InventoryServiceTest.php](tests/Unit/InventoryServiceTest.php)), dashboard order trends ([tests/Feature/DashboardOrderTrendsTest.php](tests/Feature/DashboardOrderTrendsTest.php)), product search and pagination ([tests/Feature/SearchTest.php](tests/Feature/SearchTest.php), [tests/Feature/ProductIndexTest.php](tests/Feature/ProductIndexTest.php)), purchase orders ([tests/Unit/PurchaseOrderServiceTest.php](tests/Unit/PurchaseOrderServiceTest.php), [tests/Feature/PurchaseOrderSendTest.php](tests/Feature/PurchaseOrderSendTest.php)), goods receipts ([tests/Unit/GoodsReceiptServiceTest.php](tests/Unit/GoodsReceiptServiceTest.php)), and password changes ([tests/Unit/AuthServiceTest.php](tests/Unit/AuthServiceTest.php)). [tests/Feature/ArchitectureTest.php](tests/Feature/ArchitectureTest.php) checks a missing `products.create` permission (403), a product edit in `activity_log`, a sixth login in the same minute (429), and the queued low-stock listener. Laravel Pint is available to format PHP.

The main screens were also exercised in the browser after the seed and the button changes: sign-in, purchase orders, inventory counts, transfers, products, and reports. A button that the API would reject for the current status is not rendered.

## Known limitations

- Product images, documents, and media rows store a path only. No file is uploaded, so those paths do not open a real document.
- Some create forms and table columns still show a numeric id (supplier, warehouse) instead of a name.
- Approving an adjustment requires status `pending`. Seeded adjustments use `pending_approval`, so the Approve button does not appear on those rows.
- `php artisan db:show` needs the PHP `intl` extension. Without it, the command connects and then fails while formatting numbers.
- Demo passwords are shared. Do not use them anywhere except a local demo.
- Hosted MySQL that requires TLS needs `MYSQL_ATTR_SSL_CA`. Setting `DB_SSL=REQUIRED` has no effect.
- PHP's built-in server on Windows handles one request at a time (`PHP_CLI_SERVER_WORKERS` is not supported there). The dashboard's parallel API calls therefore run one after another locally.
- Each query to the Aiven database (Amsterdam) costs one network round trip, about 60–70 ms from the Balkans. The app and the database should run in the same region.

## Tech stack

| Layer | Stack |
|-------|-------|
| Backend | Laravel 12, Sanctum, Spatie Permission, Query Builder, and Activity Log |
| Frontend | Vue 3, Vue Router, Pinia, Tailwind CSS 4, Vite, ApexCharts |
| Documents | DomPDF, Maatwebsite Excel, Picqer barcode generator |
| Database | SQLite locally, or MySQL 8 |

## Useful commands

| Command | Description |
|---------|-------------|
| `composer dev` | Laravel server, queue worker, logs, and Vite together |
| `php artisan test` | Pest test suite |
| `npm run build` | Production frontend build |
| `php artisan db:seed --class=DemoDataSeeder` | Top up demo data without deleting existing rows |

## Deployment (Vercel)

[vercel.json](vercel.json) is set up for a serverless frontend build. Before a hosted deploy, set `APP_KEY`, `APP_URL`, the `DB_*` MySQL settings, and `CACHE_STORE=array`, then run migrations against that database. The function region is set to `fra1` (Frankfurt) to sit close to the Aiven database in Amsterdam; change `regions` if the database moves. `DB_PERSISTENT=true` lets a warm function reuse its database connection.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
