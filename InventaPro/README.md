# InventaPro — Inventory Management System

Layered inventory control system: role-based login, dashboard with stock
alerts, product catalogue, suppliers, stock-in entries and sales
registration with transactional stock validation (UC-02).

## Quick start

1. Copy `InventaPro/` into your XAMPP `htdocs`
2. Start Apache and MySQL
3. phpMyAdmin → Import → `esquema.sql`
4. Visit `http://localhost/InventaPro/index.php`
5. Log in with `admin` / `Admin2026!` (see more demo users below)

---

## Stack

- PHP 8+ with PDO (layered architecture: config / models / controllers / views / helpers)
- MySQL 8 (XAMPP)
- HTML5 + CSS3 (responsive dark theme)
- Vanilla JavaScript (live search, sale cart, dynamic rows, modals)

## Installing on XAMPP

1. Copy the whole `InventaPro/` folder into your `htdocs`, for example:
   ```
   C:\xampp\htdocs\InventaPro\
   ```
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin` → **"Import"** tab → select
   `esquema.sql` (in the project root) → **Go**.

   This creates the `inventapro` database with all its tables, 3 demo
   users (one per role) and a sample product catalogue — including two
   products already at "low stock" and one "out of stock", so the
   dashboard shows alerts right away.

4. Visit in your browser:
   ```
   http://localhost/InventaPro/index.php
   ```

### Demo users

| Username | Password | Role |
|---|---|---|
| `admin` | `Admin2026!` | Administrator |
| `bodeguero` | `Bodega2026!` | Warehouse Clerk |
| `cajero` | `Caja2026!` | Cashier |

Passwords are checked with `password_verify()`; in the database they are
already hashed with bcrypt, never stored as plain text.

## Opening the project in VS Code

1. Open VS Code → **File → Open Folder** → select
   `C:\xampp\htdocs\InventaPro`.
2. Install the **PHP Intelephense** extension (optional, for autocomplete).
3. Always run the site through the Apache URL
   (`http://localhost/InventaPro/...`), not by double-clicking the `.php`
   file directly — PHP needs to go through the XAMPP Apache server to run.

## What each folder does

| Folder | Responsibility |
|---|---|
| `config/` | PDO connection (`getDB()`) |
| `helpers/auth.php` | `requireLogin()`, `requireRole([...])`, `currentUser()`, `registrarAuditoria()` |
| `models/` | One class per main table, with methods that talk directly to the database (a lightweight DAO-style pattern) |
| `controllers/` | Orchestrate model + view, apply business rules and check role-based permissions |
| `views/` | HTML + variables only; `layouts/` holds the sidebar that adapts to the logged-in role |
| `public/css`, `public/js` | Styles and client-side dynamic behaviour |
| `index.php` | Single router (`?accion=...`) |
| `esquema.sql` | Script to rebuild the full database with sample data |

## Roles and permissions

| Section | Administrator | Warehouse Clerk | Cashier |
|---|:---:|:---:|:---:|
| Dashboard | ✅ | ✅ | ✅ |
| Products (view) | ✅ | ✅ | ✅ |
| Products (create/edit) | ✅ | ✅ | ❌ |
| Products (delete) | ✅ | ❌ | ❌ |
| Suppliers | ✅ | ✅ | ❌ |
| Stock-in entries | ✅ | ✅ | ❌ |
| Sales | ✅ | ❌ | ✅ |
| Audit log | ✅ | ❌ | ❌ |

## UC-02 — Register a sale

The **New sale** screen implements the following flow:

1. The cashier searches for a product (by name or barcode) — a live,
   client-side search over the already-loaded catalogue.
2. They add it to the cart; the JavaScript already warns if the quantity
   exceeds the stock shown on screen (an optimistic check, only meant to
   give immediate feedback).
3. On confirmation, the server opens a **transaction** (`models/Salida.php`):
   it locks each product's row (`SELECT ... FOR UPDATE`), checks the real
   stock against the requested quantity, and **if any product falls
   short**, it `ROLLBACK`s the whole cart and shows the warning — sales
   with negative stock are never registered.
4. If the whole cart is valid: it inserts the header into `salidas`, the
   line items into `detalle_salidas`, deducts `stock_actual` from each
   product, and `COMMIT`s within the same transaction.
5. The action is logged in the `auditoria` table with the user and the time.

## How to restore the database from scratch

1. In phpMyAdmin, drop the `inventapro` database if it already exists
   (`DROP DATABASE inventapro;` in the SQL tab, or from Operations).
2. Import `esquema.sql` again.
