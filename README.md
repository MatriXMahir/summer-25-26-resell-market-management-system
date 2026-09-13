# Resell Market Management System (PHP + MySQL, MVC)

A teaching project for a 4-role resell marketplace: **admin, buyer, seller, delivery man**.
Written in plain PHP with procedural `mysqli` and prepared statements. No frameworks,
no Composer, no build step. Copy it into XAMPP and it runs.

---

## 1. Install (XAMPP)

1. Copy the `resell-market` folder into `C:\xampp\htdocs\` so it becomes `htdocs/resell-market/`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open `http://localhost/phpmyadmin` → **Import** → choose `database.sql` → **Go**.
4. Open `http://localhost/resell-market/index.php?page=login`.
5. Sign in as the default admin: **dhrubo@resell.com / admin123** (or any demo account below).

The admin account is created automatically the first time a page loads
(see the bottom of `config/config.php`). Everyone else signs up on the register page.

If your MySQL uses a password, change `DB_PASS` in `config/config.php`.

---

## 2. Folder structure

```
resell-market/
├── index.php                  Front controller: the ONLY entry point (router)
├── database.sql               Schema + demo accounts + sample products
├── README.md
│
├── config/
│   └── config.php             DB connection, session settings, app constants
│
├── helpers/
│   └── helpers.php            esc(), CSRF, login guards, flash messages
│
├── models/                    M — every SQL query lives here
│   ├── user_model.php         all 4 roles (one users table)
│   ├── product_model.php      products + notifications + invoices (seller domain)
│   ├── order_model.php        orders + payments + reviews (buyer domain) + revenue
│   └── delivery_model.php     deliveries + drivers (delivery domain)
│
├── controllers/               C — request handling, validation, decisions
│   ├── auth_controller.php    login / register / logout
│   ├── admin_controller.php
│   ├── seller_controller.php
│   ├── buyer_controller.php
│   ├── delivery_controller.php
│   └── ajax_controller.php    all JSON endpoints
│
├── views/                     V — HTML only
│   ├── partials/               header.php, footer.php (shared layout)
│   ├── auth/                   login.php, register.php
│   ├── admin/dashboard.php
│   ├── seller/dashboard.php
│   ├── buyer/dashboard.php
│   └── delivery/dashboard.php
│
└── assets/
    ├── css/style.css           black & white theme
    └── js/app.js               validation, escaping, live search, AJAX tables (jQuery)
```

**The MVC rule used throughout:** a view never runs a query, and a model never
prints HTML. The controller sits in the middle: it reads `$_POST`, validates,
calls the model, then `require`s the view.

---

## 3. How the router works

Every URL looks like this:

```
index.php?page=<dashboard>&action=<what to do>&id=<row id>
```

| URL | What happens |
|---|---|
| `index.php?page=login` | Login page |
| `index.php?page=register` | Signup page |
| `index.php?page=admin` | Admin dashboard (list mode) |
| `index.php?page=seller&action=edit&id=4` | Load product 4 into the form |
| `index.php?page=buyer&action=delete&id=7&csrf_token=…` | Cancel order 7 |
| `index.php?page=ajax&action=autocomplete_products&q=iphone` | Returns JSON |
| `index.php?page=logout` | Sign out |

`index.php` loads config → helpers → models → controllers, checks the session
timeout, then sends the request to one controller. `require_role('admin')` blocks
anyone who is not an admin before the controller even starts.

---

## 4. The four roles

Each role owns one table and does full **Create, Read, Update, Delete and Search**
on its own dashboard. The form sits at the top of the page; the searchable table
sits below it. Clicking **Edit** reloads the same page with the row loaded into
that same form.

| Role | Manages (CRUD) | Feature 1 | Feature 2 | Feature 3 |
|---|---|---|---|---|
| **Admin** | User accounts (all roles) | Ban user temporarily | Product approval | Monthly revenue report |
| **Seller** | Products | Update stock status (live indicator) | Invoice generation | Notification (low-stock alerts) |
| **Buyer** | My orders | Search (autocomplete catalogue) | Payment | Review |
| **Delivery** | My deliveries | Assign a driver | Delivery schedule (collapsible) | Driver history |

No feature appears on two dashboards.

### How the roles connect

- A **buyer** places an order → stock drops; if it falls below the low-stock
threshold, the **seller** gets an automatic notification.
- The buyer pays for the order → its status becomes `paid`.
- The seller generates an invoice once an order is `paid`.
- The **delivery man** assigns a paid order to a driver → the order becomes
`shipped`; marking it `delivered` sets it to `completed` and frees the driver.
- The **admin** approves new sign-ups and pending products before they go live,
and can temporarily ban a misbehaving account.

---

## 5. Requirement checklist

| Requirement | Where to look |
|---|---|
| **MVC** | `models/`, `controllers/`, `views/`, routed by `index.php` |
| **DB (MySQLi procedural)** | every function in `models/` uses `mysqli_prepare` |
| **Auth (session + cookie)** | `controllers/auth_controller.php`, `helpers/helpers.php` |
| **PHP validation** | the `if / elseif` chain at the top of every controller action |
| **JS validation** | `validateForm()` in `assets/js/app.js`, called by `onsubmit` |
| **AJAX / JSON (jQuery)** | `controllers/ajax_controller.php` + `setupAutocomplete()` / `setupLiveSearch()` in `app.js` |
| **UI (HTML/CSS)** | `views/`, `assets/css/style.css` (black & white theme) |
| **Basic web security** | see section 6 |
| **Feature completeness** | CRUD + search + 3 features per role |

---

## 6. Security, and why each piece is there

| Attack | Defence | File |
|---|---|---|
| SQL injection | Prepared statements everywhere — user text is never glued into SQL | all `models/` |
| Stolen passwords | `password_hash()` on save, `password_verify()` on login | `user_model.php` |
| XSS (server) | `esc()` wraps every value printed into HTML | `helpers.php`, all views |
| XSS (client) | `escJs()` / `.text()` before any AJAX row is inserted | `app.js` |
| CSRF | A secret token in every POST form and every delete/approve/ban link | `helpers.php`, all views |
| Session fixation | `session_regenerate_id(true)` right after a successful login | `auth_controller.php` |
| Cookie theft | `httponly` + `samesite=Lax` on the session cookie | `config.php` |
| Idle machines | Automatic sign-out after **15 minutes** | `check_session_timeout()` |
| Wrong role | `require_role()` before the controller; each AJAX action re-checks | `index.php`, `ajax_controller.php` |
| URL tampering | A buyer/seller/delivery man can only load their own rows (`WHERE … AND buyer_id = ?`, etc.) | `order_model.php`, `product_model.php`, `delivery_model.php` |
| Username guessing | Wrong email and wrong password give the same message | `auth_controller.php` |
| Self-lockout | An admin cannot delete, ban, or demote themselves | `admin_controller.php` |

Two things worth saying out loud to students:

1. **JavaScript validation is a convenience, not a defence.** Anyone can turn
JavaScript off. That is why every controller repeats the checks in PHP.
2. **"Remember me" only refills the email**, never the password.

---

## 7. Settings you can change

All in `config/config.php`:

```php
define('LOW_STOCK',        5);     // a product at or below this triggers a notification
define('CURRENCY',         'Tk');  // symbol shown next to prices
define('SESSION_TIMEOUT',  900);   // idle sign-out, in seconds (15 minutes)
define('SESSION_REGEN',    300);   // rotate the session id every 5 minutes
```

---

## 8. Test accounts

| Role | Email | Password |
|---|---|---|
| Admin | `dhrubo@resell.com` | `admin123` |
| Buyer | `mahir@resell.com` | `pass1234` |
| Seller | `sanjida@resell.com` | `pass1234` |
| Delivery | `tonmoy@resell.com` | `pass1234` |

New users sign up on the register page (buyer, seller, or delivery man only — the
register page never offers admin, and the controller checks that list again on
the server). New sign-ups need admin approval before they can log in. New admins
are created by an existing admin from the Users dashboard.

---

## Group members & roles

| Member | Role | Owns |
|---|---|---|
| Mahir | Buyer | `controllers/buyer_controller.php`, `models/order_model.php`, `views/buyer/` |
| Sanjida | Seller | `controllers/seller_controller.php`, `models/product_model.php`, `views/seller/` |
| Dhrubo | Admin | `controllers/admin_controller.php`, `models/user_model.php`, `views/admin/` |
| Tonmoy | Delivery Man | `controllers/delivery_controller.php`, `models/delivery_model.php`, `views/delivery/` |

`index.php`, `config/`, `helpers/`, `controllers/auth_controller.php`,
`controllers/ajax_controller.php`, `views/partials/`, and `assets/` are shared
infrastructure all four roles depend on.
# Resell-Market-Management-System_Summer25-26_Sec-L
