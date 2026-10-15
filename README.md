# Inventory Management System

A small inventory website for the Web Application Development final project.
Stack: HTML5, Bootstrap 5 (sidebar dashboard layout), vanilla JavaScript, PHP (PDO), MySQL.

Staff record stock coming in and going out. The dashboard warns when a product is low or out of stock.
The admin manages products, suppliers, and users.

## Test credentials (both use the password `admin123`)
- Admin: `admin@inventory.com`
- Staff: `staff@inventory.com`

New staff can register on the Register page. The admin can change roles or delete users on the Users page.

## Features
- Register / login with `password_hash()` and sessions
- Roles: `admin` and `staff`
- **Everyone:** dashboard (totals, stock value, low-stock alerts, latest movements), product list with search and a "low stock only" filter, Stock In / Out, and the movements history with filters
- **Admin only:** full CRUD on products and suppliers, and user management
- Stock rules: stock can never go below zero, every change is saved in the movements history with who did it, and quantity can only be changed through Stock In / Out (not by editing the product)
- Quick In / Out buttons on the product list use `fetch()`, so the quantity and status update without reloading
- Client-side validation (HTML5 + JavaScript) and server-side validation (PHP)
- Security: prepared statements (SQL injection), `htmlspecialchars` (XSS), CSRF tokens, row locking when updating stock

## Setup
1. Create a MySQL database (for example `inventory_system`).
2. In phpMyAdmin, select it and import `database/schema.sql`.
3. Open `config.php` and set your host, database name, user, and password.
4. Upload all files to your host's `htdocs` folder (InfinityFree) or put them in `htdocs` on XAMPP.
5. Visit the site and log in with an account above.

## Folder layout
```
config.php        database + helper functions
header.php        sidebar layout (shared)
footer.php        footer + scripts (shared)
index.php         sends visitors to the right page
login.php, register.php, logout.php
dashboard.php     totals, low-stock alerts, latest movements
products.php      product list, search, quick stock, delete (admin)
stock.php         Stock In / Out form and logic
movements.php     stock history
admin/            product_form, suppliers, supplier_form, users
assets/           style.css, app.js
database/         schema.sql
```
