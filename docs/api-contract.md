# üK295 LB1 API (Bruno collection)

Source: Bruno/OpenCollection requests in `docs/bruno/`.

Base URL in the Bruno collection (exact):

`http://localhost/api/v1`

| Bruno request | Method | URL path |
|---------------|--------|----------|
| Authenticate | POST | `/api/v1/authenticate` |
| List Products | GET | `/api/v1/products` |
| Create/Update Product | PUT | `/api/v1/product/12345678` |
| Get Product | GET | `/api/v1/product/12345678` |
| Delete Product | DELETE | `/api/v1/product/12345678` |
| List Categories | GET | `/api/v1/categories` |
| Create Category | POST | `/api/v1/category` |
| Update Category | PATCH | `/api/v1/category/1` |
| Get Category | GET | `/api/v1/category/1` |
| Delete Category | DELETE | `/api/v1/category/1` |

**Apache:** Bruno expects `http://localhost/api/v1/...` (no `/online_shop` in the path). Set **DocumentRoot** to `online_shop/public` — see [`apache/localhost-api-v1.conf`](../apache/localhost-api-v1.conf). Alternative: `http://localhost/online_shop/api/v1/...` also works (base path is detected automatically).

| Request | Method | Path | Auth |
|---------|--------|------|------|
| Authenticate | POST | `/api/v1/authenticate` | No |
| List Products | GET | `/api/v1/products` | Bearer JWT |
| Create/Update Product | PUT | `/api/v1/product/{id}` | Bearer JWT |
| Get Product | GET | `/api/v1/product/{id}` | Bearer JWT |
| Delete Product | DELETE | `/api/v1/product/{id}` | Bearer JWT |
| List Categories | GET | `/api/v1/categories` | Bearer JWT |
| Create Category | POST | `/api/v1/category` | Bearer JWT |
| Update Category | PATCH | `/api/v1/category/{id}` | Bearer JWT |
| Get Category | GET | `/api/v1/category/{id}` | Bearer JWT |
| Delete Category | DELETE | `/api/v1/category/{id}` | Bearer JWT |

**Credentials:** `username` `admin`, `password` `sec!ReT423*&`

**Login response:** `token`, `token_type`, `expires_in` — send `Authorization: Bearer <token>` on every `/api/v1/*` request except **Authenticate**.

## MySQL (phpMyAdmin)

Import [`database/online_shop.sql`](../database/online_shop.sql) into database **`lb1_uek295`** — tables `category` and `product` match the course ER diagram (`category_id`, `product_id`, `sku`, optional `id_category`). See [`database/README.md`](../database/README.md).

## XAMPP

Point the vhost or alias so requests hit this repo’s `.htaccess` (rewrites to `public/index.php`). Example vhost DocumentRoot: `C:/xampp/htdocs/online_shop`.
