# Restaurant Order & Table Management System

A web-based restaurant management system built for **Asam Pedas Claypot Pantai Kelanang**, a single-branch restaurant. The system digitises and integrates the restaurant's four core operations — online table reservation, order management, kitchen communication, and billing — into one platform.

**Live site:** [asampedasclaypot.my](https://asampedasclaypot.my)

This is a Final Year Project (FYP) for **CSC2854 Project**, Diploma in Computer Science, Kolej Profesional MARA Beranang.

---

## Features

The system fulfils ten functional requirements across five roles (Customer, Waiter, Kitchen Staff, Cashier, Owner):

| # | Feature | Description |
|---|---------|-------------|
| FR-01 | Online Table Reservation | Public reservation with a 50% deposit paid through the CHIP payment gateway |
| FR-02 | Real-Time Table Availability | Live floor view (Available / Reserved / Occupied) that refreshes automatically |
| FR-03 | Digital Table Assignment | Staff seat a party and the table status updates automatically |
| FR-04 | Digital Order Taking | POS-style order screen; a booked guest's pre-order is carried over to the waiter |
| FR-05 | Kitchen Display System (KDS) | Confirmed orders are shown to the kitchen in real time |
| FR-06 | Order Status Tracking | Orders move through Preparing, Ready, and Served |
| FR-07 | Automated Billing | Itemised bill with SST 6% tax and the reservation deposit credited to the balance |
| FR-08 | Multiple Payment Methods | Cash, Card, and QR; the table is released after payment |
| FR-09 | Daily Sales Report | Revenue, transaction count, and popular items, with day-by-day navigation |
| FR-10 | Login & Role-Based Access | Username login with role-based access control and staff user management |

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.3), MVC, Eloquent ORM
- **Frontend:** Blade, Tailwind CSS (Vite), Alpine.js
- **Database:** MySQL
- **Payment:** CHIP payment gateway (chip-in.asia)
- **Testing:** PHPUnit feature tests

## Roles

| Role | Access |
|------|--------|
| Owner | Full access: dashboard, reports, and user management |
| Waiter | Tables and order taking |
| Cashier | Billing and payment |
| Kitchen Staff | Kitchen Display System |

---

## Getting Started (Local)

**Requirements:** PHP 8.3, Composer, Node.js, and MySQL.

```bash
# 1. Clone and install dependencies
git clone https://github.com/danizz140506-bot/pantai-kelanang.git
cd pantai-kelanang
composer install
npm install && npm run build

# 2. Set up the environment
cp .env.example .env
php artisan key:generate

# 3. Configure the database in .env (DB_DATABASE=pantai_kelanang), then create it
#    and run the migrations with sample data
php artisan migrate --seed

# 4. Serve the application
php artisan serve
```

Open the served URL. The public reservation page is at `/` and the staff login is at `/login`.

### Payment Gateway (CHIP)

The reservation deposit and card/QR payments use the CHIP gateway, configured through `CHIP_BRAND_ID` and `CHIP_SECRET_KEY` in `.env`. When these keys are left empty, the system runs in **simulation mode** so the full flow can be demonstrated without a live gateway.

## Seeded Accounts

After `migrate --seed`, one account is created per role. These are for local development only.

| Username | Role |
|----------|------|
| `owner` | Owner |
| `waiter` | Waiter |
| `cashier` | Cashier |
| `kitchen` | Kitchen Staff |

Default password: `password`

## Testing

```bash
php artisan test
```

The suite covers all ten functional requirements with feature tests for the reservation, table, order/KDS, billing, and reporting modules.

---

## Author

**Muhammad Danish Iskandar bin Mohd Haffizul** (BCS2402-032)
Diploma in Computer Science, Kolej Profesional MARA Beranang
CSC2854 Project — Session 1 2026/2027

## Note

This is an academic project. The database credentials and payment keys are supplied through an untracked `.env` file and are not included in this repository.
