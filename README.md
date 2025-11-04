# Kiosk Backend Project

A PHP-based kiosk ordering system with admin panel and API endpoints.

## Requirements

- **XAMPP** (or any local server with PHP and MySQL)
  - PHP 7.4 or higher
  - MySQL 5.7 or higher (or MariaDB)
  - Apache web server

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/BryonMatt28/KIOSK_BE.git
cd KIOSK_BE
```

### 2. Database Setup

1. **Start XAMPP** and ensure MySQL is running
2. Open **phpMyAdmin** (usually at http://localhost/phpmyadmin)
3. Import the database:
   - Go to phpMyAdmin
   - Select "Import" tab
   - Choose the file: `database/migrations.sql`
   - Click "Go" to execute

   OR manually run the SQL file:
   - Copy the contents of `database/migrations.sql`
   - Paste and execute in phpMyAdmin SQL tab

### 3. Configure Database Connection

Edit `src/config/db.php` if your database credentials differ:
```php
$DB_HOST = '127.0.0.1';  // Change if needed
$DB_USER = 'root';       // Change if different
$DB_PASS = '';           // Add password if set
$DB_NAME = 'kiosk_db';   // Database name
$DB_PORT = 3306;         // Change if using different port
```

### 4. Place Files in Web Server Directory

**If using XAMPP:**
- Copy the entire project folder to: `C:\xampp\htdocs\KIOSK\`
- OR if cloned directly into htdocs, ensure the path is correct

**If using different server:**
- Place files in your web server's document root
- Ensure the web server points to the `public/` directory or adjust paths

### 5. Access URLs

- **Homepage:** http://localhost/KIOSK/public/index.php
- **Kiosk:** http://localhost/KIOSK/public/kiosk.php
- **Admin Login:** http://localhost/KIOSK/public/admin/login.php
- **Admin Dashboard:** http://localhost/KIOSK/public/admin/dashboard.php

### 6. Initial Setup (One-Time)

1. **Create Super Admin Account:**
   - Visit: http://localhost/KIOSK/public/admin/seed_superadmin.php
   - This creates the default superadmin account
   - Default credentials (change after first login):
     - Username: `admin`
     - Password: `admin123`

2. **Seed Sample Products (Optional):**
   - Visit: http://localhost/KIOSK/public/admin/seed_products.php
   - This adds sample products to the database

## Project Structure

```
KIOSK_BE/
├── database/
│   └── migrations.sql          # Database schema and initial setup
├── public/
│   ├── admin/                  # Admin panel pages
│   │   ├── login.php
│   │   ├── dashboard.php
│   │   ├── products.php
│   │   ├── reports.php
│   │   ├── reports_pdf.php
│   │   ├── pos.php
│   │   ├── users.php
│   │   ├── seed_products.php
│   │   └── seed_superadmin.php
│   ├── api/                    # API endpoints
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── products.php
│   │   ├── users.php
│   │   ├── orders.php
│   │   └── order_create.php
│   ├── assets/
│   │   └── style.css
│   ├── index.php               # Homepage
│   └── kiosk.php               # Kiosk interface
└── src/
    ├── config/
    │   └── db.php              # Database configuration
    └── lib/
        └── auth.php            # Authentication library
```

## API Endpoints

- `POST /api/login.php` - User login
- `POST /api/logout.php` - User logout
- `GET /api/products.php` - Get all products
- `POST /api/products.php` - Create product (admin only)
- `PATCH /api/products.php` - Update product (admin only)
- `DELETE /api/products.php` - Delete product (admin only)
- `GET /api/users.php` - Get users (admin only)
- `POST /api/users.php` - Create admin user (superadmin only)
- `PATCH /api/users.php` - Update user (suspend/unsuspend, superadmin only)
- `DELETE /api/users.php` - Delete admin user (superadmin only)
- `GET /api/orders.php` - Get orders (with optional status filter)
- `PATCH /api/orders.php` - Update order status (admin only)
- `POST /api/order_create.php` - Create new order (from kiosk)

## Notes

- Ensure Apache and MySQL are running in XAMPP
- All database credentials are in `src/config/db.php` (update if needed)
- Sessions are used for authentication
- The project uses MySQLi for database operations

## Troubleshooting

- **Database connection error:** Check MySQL is running and credentials in `src/config/db.php`
- **404 errors:** Verify the project is in the correct directory (htdocs/KIOSK/)
- **Session errors:** Ensure PHP sessions are enabled (enabled by default)
- **Permission errors:** Check file permissions on your web server

