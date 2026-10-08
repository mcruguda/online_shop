# MySQL / phpMyAdmin setup (XAMPP)

Database name and credentials come from [`config.php`](../config.php) (default: **`lb1_uek295`**, user `root`, empty password).

## Option A — Runnable setup (recommended)

Creates the database **if it does not exist**, creates tables **if they are missing**, and inserts seed data **only when empty**. Safe to run again.

```bash
php database/setup.php
```

From the project root, e.g.:

```bash
cd c:\xampp\htdocs\online_shop
php database/setup.php
```

You can also open in the browser (local dev only):

`http://localhost/online_shop/database/setup.php`

After setup, use the Bruno URLs: `http://localhost/api/v1/...`

## Option B — phpMyAdmin import

1. Start **Apache** and **MySQL** in XAMPP.
2. Open **http://localhost/phpmyadmin**.
3. **Import** [`online_shop.sql`](online_shop.sql) (creates `lb1_uek295` and all tables), or run **Option A** instead.

## Option C — Full reset (drops all shop tables)

Wipes `product`, `category`, and `users`, then re-imports from SQL:

```bash
php database/import.php
```

## Schema (course ER model)

- **`category`**: `category_id`, `active`, `name`
- **`product`**: `product_id`, `sku`, `active`, `id_category` (nullable FK), `name`, `image`, `description`, `price`, `stock`
- **`users`**: API login (`admin` / `sec!ReT423*&`)

The API uses **mysqli** to connect to this database.
