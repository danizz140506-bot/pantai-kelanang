# Pantai Kelanang — Restaurant Order & Table Management System

FYP (CSC2854, Diploma Computer Science, KPM Beranang) — **Muhammad Danish Iskandar (BCS2402-032)**.
Web-based system for **Asam Pedas Claypot Pantai Kelanang**. Built to match the Assignment 1 (BRD),
Assignment 2 (Project Plan + SRS + SDD), and Assignment 3 (Final Project) documents.

## Tech stack
- **Laravel 12** (PHP 8.3) · MVC · Blade · **Tailwind CSS** (Vite) · Alpine.js
- **MySQL** (DB name `pantai_kelanang`)
- Auth: **Laravel Breeze**, customised to log in with **username** (not email) + role middleware
- Payment: **CHIP (chip-in.asia)** live gateway — reservation deposit (redirect + verify)

## Status — ALL 10 functional requirements complete ✅
| FR | Feature | Where |
|----|---------|-------|
| FR-01 | Online reservation + 50% CHIP deposit (public `/`) | ReservationController, `reservations/*` |
| FR-02 | Real-time table availability | TableController, `tables/index` |
| FR-03 | Digital table assignment | TableController |
| FR-04 | Digital order taking | OrderController, `orders/create` |
| FR-05 | Kitchen Display System (KDS) | KitchenController, `kds/index` |
| FR-06 | Order status Preparing→Ready→Served | KitchenController |
| FR-07 | Automated billing | BillingController, `billing/*` |
| FR-08 | Multiple payment methods + table release | BillingController + ChipService |
| FR-09 | Daily sales report | ReportController, `reports/index` |
| FR-10 | Login + role-based access + user management | Auth + UserController, `users/index` |

- **Deposit → Balance:** reservation deposit is credited at billing; cashier collects only the balance;
  reservation marked Completed. (Payment.total_amount = balance; deposit = subtotal − discount − total_amount.)
- **Reservation flow follows SDD 5.2** (record-first): create RESERVATION as `Pending` → payDeposit via CHIP →
  on success confirm + table Reserved; on failure stays `Pending`.
- **Tests:** `php artisan test` — 35 feature tests pass (Table, Order/KDS, Billing, Report/User, Reservation).

## Seeded logins (password: `password`) — one account per role
`owner` · `waiter` · `cashier` · `kitchen`

## Roles / 8 DB tables (exact SDD names)
users · customers · table_info · reservations · menu_items · orders · order_items · payments

---

## Setup on macOS

1. **Prereqs:** PHP 8.3, Composer, Node, and a MySQL server (e.g. DBngin, or Laravel Herd's MySQL).
2. Clone, then:
   ```bash
   composer install
   npm install && npm run build
   ```
3. Create the database: `CREATE DATABASE pantai_kelanang;`
4. `.env` is committed (includes APP_KEY + live CHIP keys). **Adjust for your Mac:**
   - `DB_USERNAME` / `DB_PASSWORD` — Mac MySQL often uses `root` with a password (Windows/Laragon used no password).
   - `APP_URL` — set to whatever host you serve on (see note below).
5. Migrate + seed:
   ```bash
   php artisan migrate --seed
   php artisan config:clear
   php artisan serve
   ```
6. Open the served URL. Public reservation page = `/`. Staff login = `/login`.

### CHIP (already configured in .env)
- Keys are live sandbox creds in `.env` (`CHIP_BRAND_ID`, `CHIP_SECRET_KEY`, `CHIP_BASE_URL`).
- Flow is **redirect + verify** (no webhook/tunnel needed — browser-driven, works on localhost).
- **APP_URL must match the host you browse on**, so CHIP's redirect back reaches your session. If you use
  `php artisan serve`, set `APP_URL=http://127.0.0.1:8000`. If you use Herd/Valet (`pantai-kelanang.test`),
  set `APP_URL=http://pantai-kelanang.test`.
- Tests force CHIP into simulation mode (see `phpunit.xml`), so they never call the live gateway.

### Notes
- Frontend theme: dark "espresso + ember" (design tokens in `tailwind.config.js`). Fonts via bunny.net.
- Logo: `public/images/logo.png` (claypot line-art).
- The public reservation page is a 2-step wizard (① Details + table map → ② Pre-order menu + pay).
