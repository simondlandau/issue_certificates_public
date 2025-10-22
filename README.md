# Company Certificate Issuing Web Application Suite

A comprehensive PHP-based web application suite for registering clients via email, storing application documents, approval process, invoicing procedure and electronic payment via Stripe or PayPal, Automated certificate issue via email.

---

## 📋 Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Application Components](#application-components)
- [Mobile Scanner Setup](#mobile-scanner-setup)
- [Configuration](#configuration)
- [Security Notes](#security-notes)
- [License](#license)

---

## 🎯 Overview

This application suite provides:
- **Online Client Registration**: Comprehensive client data collection.
- **Status Dashboard**: Real-time dashboard showing client status
- **Online Payment**: On Invoice client makes payment online using Stripe or PayPal
- **Automated Issue**: On receipt of payment client is emailed certificate.

**Tech Stack**: PHP 8.1+, MySQL 8+, Apache2, SMTP, Stripe, PayPal

---

## 🚀 Features

### Process Management
- ✅ Clear list of client applications and statue
- ✅ Application - Vetting - Approval - Invoice - Payment - Issue
- ✅ Inbuilt Logic precludes any step being made without the previous step being complete.
- ✅ Automated mail advises client and subscribed staff of progress.

### Business Intelligence
- ✅ MySQL-based reporting dashboard
- ✅ Real-time client status
- ✅ Audit trail

---

## 🧰 Requirements

### Server Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher
- **Apache2**: With mod_php and mod_rewrite enabled

### PHP Extensions
```bash
php-mysqli
php-pdo
php-pdo-mysql
php-mbstring
php-curl
```

### Optional
- **Docker & Docker Compose**: For containerized deployment
- **DigitalOcean Droplet**: Tested and compatible

---

## ⚙️ Installation

### Option 1: Local LAMP Deployment
```bash
# Clone repository
git clone https://github.com/simondlandau/issue_certificates_public.git
cd svp-webapp

# Copy to Apache web root
sudo cp -r . /var/www/html/company

# Set permissions
sudo chown -R www-data:www-data /var/www/html/company
sudo chmod -R 755 /var/www/html/company

# Create MySQL database
mysql -u root -p
CREATE DATABASE company;
source schema/database.sql;
```

### Option 2: Docker Deployment
```bash
# Using Docker Compose
docker-compose up -d

# Access at https://localhost:9443/company/
```

### Option 3: XAMPP (Development/Testing)
```bash
# Copy to XAMPP htdocs
cp -r . /opt/lampp/htdocs/svp

# Start XAMPP
sudo /opt/lampp/lampp start

# Access at http://localhost/company/
```

---

## 📦 Application Components

### Core Applications

| File | Purpose | Database |
|------|---------|----------|
| `admin_dashboard.php` | Real-time client status | MySQL |
| `index.html` | Client online application | MySQL |
| `registration.php` | Client online application | MySQL + SMTP|
| `admin_login.php` | Secure administration login | MySQL |
| `admin_dashboard.php` | Client process control | MySQL + SMTP|
| `admin_view.php` | Client process control | MSSQL + SMTP|

### API Endpoints

| File | Purpose |
|------|---------|
| `config.php` | Database connections (MySQL + MSSQL) |

### Support Files

| File | Purpose |
|------|---------|
| `header.php` | Common page header with navigation |
| `footer.php` | Common page footer |

---


## 🔧 Configuration

### 1. Database Setup 

Edit `config.php` with your credentials:
```php
// MySQL Configuration
$mysql_host = 'localhost';
$mysql_db = 'svp';
$mysql_user = 'your_username';
$mysql_pass = 'your_password';

// SMTP / Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '@gmail.com');       // Gmail address
define('SMTP_PASS', '');       // Google App Password
define('SMTP_FROM_EMAIL', '@gmail.com');
define('SMTP_FROM_NAME', 'SAMIBLA');
define('SMTP_BCC', '@gmail.com');


// Stripe
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_xxxxxxxxxxxxxxxxxx'); 
define('STRIPE_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxx'); 

// PayPal Sandbox Credentials
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_SECRET', '');
define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com'); // sandbox endpoint
```

### 2. Customize Branding

Edit header and footer files:
- `header.php` - Logo, company name, navigation
- `footer.php` - Copyright, contact information
- Report headers in individual PHP files

---

## 🔒 Security Notes

### Production Deployment

⚠️ **This repository contains a sterilized version** - sensitive configuration has been removed.

**Before deploying:**

1. ✅ Set strong database passwords
2. ✅ Enable HTTPS
3. ✅ Configure firewall rules
4. ✅ Set restrictive file permissions
5. ✅ Review and customize `config.php`
6. ✅ Change default admin credentials
7. ✅ Enable error logging (disable display_errors)

### HTTPS Setup
```bash
# Install Certbot for Let's Encrypt
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d your-domain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### File Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/company
sudo chmod 644 /var/www/html/company/*.php
sudo chmod 755 /var/www/html/company
sudo chmod 600 /var/www/html/company/config.php
```

---

## 📊 Database Schema


For complete schema, see `schema/database.sql`

---

## 🤝 Contributing

This is a production application in active use. Contributions welcome:

1. Fork the repository
2. Create a feature branch
3. Test thoroughly
4. Submit a pull request

**Please note**: Maintain compatibility with existing production deployment.

---

## 📝 License
MIT License

Copyright (c) 2025 Simon D Landau

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


## 📧 Contact

**Developer**: Simon Landau  
**Email**: simon@landau.ws  
**Repository**: [github.com/simondlandau/issue_certificates_public](https://github.com/simondlandau/issue_certificates_public)

---

## 🙏 Acknowledgments

- Open source community

---

**⭐ If this project helps your organization, please consider starring the repository!**
