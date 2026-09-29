# Karenderya Orders

A small app to manage a karenderya's menu and orders.
Built with Laravel, Vue (Laravel Vue starter kit), and PostgreSQL.

## Features

- Add and edit food items (name, price, stock).
- Create orders. Sold-out items can't be ordered.
- An order is rejected if there is not enough stock.
- View previous orders in a table (10 per page) with their items and totals.

## How to run

Needs PHP 8.4+, Composer, Node 22+, and PostgreSQL.

```bash
createdb -U postgres karenderya
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Open http://localhost:8000 and log in with `test@example.com` / `password`.

If your PostgreSQL user or password is different, update the `DB_*` values in `.env`.

## How to test

```bash
php artisan test
```

`composer test` also runs the code style check (Pint) and static analysis (PHPStan).

## Design decisions

- **Three tables**: `food_items`, `orders`, and `order_items`.
- **Price is saved on each order item**, so old orders don't change when a menu price changes.
- **The server calculates the total.** The browser only sends item IDs and quantities.
- **Safe stock check.** Stock is reduced with one query: `UPDATE ... WHERE stock >= quantity`. If it fails, the whole order is cancelled (database transaction). Two orders at the same time can't take the last serving.
- **Kept it simple.** Two controllers with validation inside. No extra service classes.
- **Login only.** It's a tool for staff, so sign-up is turned off.

## Assumptions

- Prices are whole pesos.
- Rice and other "unlimited" items just get a big stock number (999999).
- All users share the same menu and orders.
- An order can't be changed after it is placed.

## Not built

- Deleting food items (it would break order history). Set stock to 0 instead.
- Editing or cancelling orders, order status, payments, reports, user roles.

## Known limitations

- If several items are short on stock, only the first one is reported.
- Tests run on SQLite (in memory), not PostgreSQL.

## AI usage

- **Tool**: Claude Code (Claude Opus).
- **Used for**: writing the migration, models, controllers, Vue pages, and tests, and running the checks.
- **What I changed or decided after the first version**:
    - Asked for a simple build on the existing Laravel starter kit. No extra layers like service classes.
    - Switched the database from SQLite to PostgreSQL, since that is what the team commonly uses.
    - Turned off sign-up and kept login only, because this is a staff tool. The home page now goes straight to login.
    - Changed the order history from cards to a table, and added pagination (10 per page) so it stays fast as orders grow.
    - Did not let the AI commit to git. I made the commits myself.
    - Fixed wrong database settings in `.env` (`postgresql` → `pgsql`, `root` → `postgres`).
- **How I checked it**: feature tests for the order rules, PHPStan, and running the app with PostgreSQL.
