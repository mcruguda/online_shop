# Online Shop API (üK295 LB1)

Slim 4 REST API for products, categories, and users. JWT authentication, MySQL (mysqli), OpenAPI/Swagger UI, and a [Bruno](docs/bruno/) collection.

**Bruno base URL:** `http://localhost/api/v1`

---

## Requirements

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL) or equivalent
- PHP **8.2+** (see `composer.json`)
- [Composer](https://getcomposer.org/)

---

## Quick start

1. Clone or copy this repo into your web root, e.g. `C:\xampp\htdocs\online_shop`.
2. Install PHP dependencies:

   ```
   cd C:\xampp\htdocs\online_shop
   composer install
   ```

3. Configure the database in [`config.php`](config.php) if needed 
4. Create tables and seed data — see [`database/README.md`](database/README.md):

   ```
   php database/setup.php
   ```

5. Set up Apache routing so `http://localhost/api/v1/...` works (see below). This is required for the Bruno collection URLs.

6. **Authenticate:** `POST http://localhost/api/v1/authenticate` with JSON `{"username":"admin","password":"sec!ReT423*&"}`. Use the returned token as `Authorization: Bearer <token>` on other `/api/v1/*` routes.

API details: [`docs/api-contract.md`](docs/api-contract.md). Interactive docs (when the app is reachable): [`public/docs.html`](public/docs.html).

---

## Routing: `htdocs/api/.htaccess` (recommended for Bruno)

The Bruno requests use **`http://localhost/api/v1/...`** (no `/online_shop` in the path). The app itself lives under `online_shop/public/`. A small rewrite in **`C:\xampp\htdocs\api\.htaccess`** forwards `/api/v1/*` to the Slim front controller.

This file is **not** committed inside `htdocs/api` (that path is outside the repo). Instead, copy the template from this repository:

| In the repo | Install on your machine |
|-------------|-------------------------|
| [`apache/htdocs-api.htaccess`](apache/htdocs-api.htaccess) | `C:\xampp\htdocs\api\.htaccess` |

### Steps (XAMPP on Windows)

1. Ensure **Apache `mod_rewrite`** is enabled (XAMPP usually enables it by default).

2. Create the `api` folder if it does not exist:

3. Copy the template and rename it to `.htaccess`:

   (Adjust the source path if your project folder is not named `online_shop`.)

5. Restart Apache from the XAMPP Control Panel.

6. Verify: open `http://localhost/api/v1/products` (after login) or run the **Authenticate** request in Bruno.


## Project layout

| Path | Purpose |
|------|---------|
| `public/index.php` | Slim application entry point |
| `src/` | Controllers, repositories, auth |
| `database/` | SQL dump and setup scripts |
| `apache/` | **Deploy helpers** — `htdocs-api.htaccess` and optional vhost snippet |
| `docs/bruno/` | API request collection |
| `config.php` | DB and JWT settings |

---

## Default login

| Field | Value |
|-------|--------|
| Username | `admin` |
| Password | `sec!ReT423*&` |
