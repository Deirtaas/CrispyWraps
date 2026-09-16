# CrispyWraps — Inventory & Ordering System

A lightweight web-based inventory and ordering system for a small food business. Customers browse the menu and place orders; staff manage inventory, recipes, orders, analytics, and feedback from a dedicated console.

Built with **PHP (PDO)**, **MySQL**, and **vanilla JS/CSS** — no frameworks, no build step.

---

## Features

**Storefront (Customer)**
- Browse menu with category filters and live search
- Cart with quantity controls and checkout (Dine-in / Takeout / Delivery)
- Track order status: `New → Cooking → Ready → Completed`
- Leave product ratings and comments; view staff replies

**Staff Console**
- **Dashboard** — daily sales, open orders, low stock alerts
- **Order Operations** — filter, advance status, cancel, refund
- **Inventory** — stock in/out, low stock and expiry alerts, waste tracking
- **Recipe Mapping** — link menu items to ingredients; orders deduct stock automatically
- **Menu Configuration** — add/delete products and categories, image uploads
- **Analytics** — revenue totals, top products chart, sales report
- **Customer Reviews** — view and reply to feedback
- **Manage Accounts** — Admin-only staff/admin creation and revocation

**System-wide**
- Password hashing (`password_hash` / `password_verify`)
- CSRF protection on every POST
- Prepared statements everywhere
- Transactional checkout (order + stock deduction succeed or fail together)
- Responsive layout, works on mobile

---

## Tech Stack

| Layer    | Technology                       |
|----------|----------------------------------|
| Backend  | PHP 8+ (PDO, no framework)       |
| Database | MySQL / MariaDB                  |
| Frontend | HTML5, CSS3, vanilla JavaScript  |
| Charts   | Chart.js (CDN)                   |
| Server   | Apache (WAMP / XAMPP / MAMP)     |

---

## Project Structure

```
CrispyWraps/
├── index.php, menu.php, cart.php, checkout.php
├── my-orders.php, feedback.php, login.php, logout.php
├── config/db.php              # DB connection, session, CSRF helpers
├── includes/                  # header.php, staff-sidebar.php
├── css/styles.css
├── js/ui.js
├── uploads/                   # Product images (auto-created)
└── staff/                     # dashboard, orders, inventory, recipes,
                               # menu, analytics, reviews, accounts
```

---

## Installation (WAMP / XAMPP)

**1. Clone into your web root**

```bash
git clone https://github.com/<your-username>/crispywraps.git
```

Place in `C:\wamp64\www\crispywraps` (WAMP) or `C:\xampp\htdocs\crispywraps` (XAMPP).

**2. Create the database**

Open phpMyAdmin → SQL tab → run:

```sql
CREATE DATABASE IF NOT EXISTS `crispywraps`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `crispywraps`;

CREATE TABLE `users` (
  `id` varchar(32) PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customers` (
  `id` varchar(32) PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
  `name` varchar(100) PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
  `id` varchar(32) PRIMARY KEY,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `desc` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ingredients` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(150) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'g',
  `stock` int NOT NULL DEFAULT 0,
  `reorder` int NOT NULL DEFAULT 100,
  `expiry` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `recipes` (
  `product_id` varchar(32) NOT NULL,
  `ingredient_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`,`ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cart` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `customer_id` varchar(32) NOT NULL,
  `product_id` varchar(32) NOT NULL,
  `qty` int NOT NULL DEFAULT 1,
  UNIQUE KEY (`customer_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `orders` (
  `id` varchar(32) PRIMARY KEY,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Dine-in',
  `payment` varchar(50) NOT NULL DEFAULT 'Cash',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'New',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `order_id` varchar(32) NOT NULL,
  `product_id` varchar(32) NOT NULL,
  `qty` int NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stock_log` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `type` varchar(3) NOT NULL,
  `ingredient_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `waste` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `ingredient_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT 0,
  `reason` varchar(100) DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `feedback` (
  `id` varchar(32) PRIMARY KEY,
  `customer` varchar(100) NOT NULL,
  `product_id` varchar(32) DEFAULT NULL,
  `rating` tinyint NOT NULL DEFAULT 5,
  `comment` text NOT NULL,
  `reply` text DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data
INSERT INTO `categories` (`name`) VALUES
('Wraps'), ('Rice Meals'), ('Sides'), ('Drinks');

INSERT INTO `users` VALUES
('usr-admin','Administrator','admin@crispywraps.local','admin123','Admin');

INSERT INTO `ingredients` (`name`,`unit`,`stock`,`reorder`,`expiry`) VALUES
('Chicken','g',1000,200,DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
('Rice','g',2000,500,DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('Tortilla','pcs',200,50,DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
('Lettuce','g',500,100,DATE_ADD(CURDATE(), INTERVAL 5 DAY));
```

**3. Configure `config/db.php`**

```php
$host   = 'localhost';
$dbname = 'crispywraps';
$user   = 'root';
$pass   = '';       // set your MySQL password if needed
$port   = '3306';
```

**4. Run**

```
http://localhost/crispywraps/
```

---

## Default Credentials

| Role  | Email                     | Password   |
|-------|---------------------------|------------|
| Admin | `admin@crispywraps.local` | `admin123` |

> ⚠️ **Change this password immediately after first login.** The seeded value is stored in plain text and is automatically rehashed to a secure hash on first successful login.

---

## How It Works

1. Customer places an order → `checkout.php` opens a database transaction.
2. Order and line items are inserted.
3. For each product, mapped ingredients in `recipes` are deducted from `ingredients.stock`.
4. Stock movements are logged into `stock_log`.
5. Staff advance the order through `New → Cooking → Ready → Completed`.
6. Low stock and expiry alerts surface on the staff dashboard and inventory page.

---

## Security

- **Passwords** — hashed with `password_hash()`, verified with `password_verify()`; legacy plain-text passwords are auto-migrated on first login
- **CSRF** — every POST requires a session-bound token
- **SQL injection** — all input flows through PDO prepared statements
- **XSS** — user output escaped with `htmlspecialchars()`
- **Checkout** — transactional; partial order writes are rolled back
- **Uploads** — extension and MIME type validation on product images

Not implemented: login rate limiting, email verification, password reset, HTTPS enforcement, real payment gateway.

---

## License

Released under the [MIT License](LICENSE).

---

## Author

**<Your Name>** — GitHub: [@<your-username>](https://github.com/<your-username>)