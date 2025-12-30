# online-tourism
🌍 Online Tourism Services Website
📋 Project Overview
A comprehensive online tourism platform built using PHP, MySQL, HTML, CSS, and JavaScript that allows users to explore tourist destinations, book tour packages, and manage their travel experiences with secure payment integration.

🏗️ Technology Stack
Backend: PHP (Procedural)

Frontend: HTML5, CSS3, JavaScript

Database: MySQL

Payment Gateway: PayU Integration

Server: XAMPP/WAMP (Localhost)

🚀 Features
👤 User Features
✅ User registration & login with secure authentication

✅ Browse destinations with images & descriptions

✅ View tour packages with detailed itineraries

✅ Search & filter packages by destination, duration, price

✅ Online booking system with traveler details

✅ Secure payment integration (PayU Gateway)

✅ User dashboard for managing bookings

✅ Download payment receipts

✅ Review & rating system for packages

✅ Profile management & travel history

👨‍💼 Admin Features
✅ Secure admin login panel

✅ Dashboard with analytics & statistics

✅ Manage destinations (CRUD operations)

✅ Manage tour packages (CRUD operations)

✅ Manage bookings & confirmations

✅ Manage users & their profiles

✅ Moderate reviews & ratings

✅ Export data functionality

🔧 Technical Features
✅ Responsive design (Mobile, Tablet, Desktop)

✅ Form validation (Client & Server side)

✅ Secure password handling (bcrypt hashing)

✅ Session management & security

✅ File upload with validation

✅ PDF receipt generation

✅ Email notification system (ready for integration)

✅ Database backup & recovery

📂 Project Structure
text
online-tourism/
├── assets/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Images & uploads
├── includes/             # Core PHP files
├── admin/                # Admin panel
├── user/                 # User dashboard
├── pages/                # Frontend pages
├── api/                  # API endpoints
├── index.php             # Home page
├── .htaccess             # URL rewriting & security
└── online_tourism.sql    # Database schema
🔐 Default Login Credentials
👨‍💼 Admin Account
Username: admin

Password: admin123

Email: admin@tourism.com

Role: Super Admin

Access URL: http://localhost/online-tourism/admin/

👤 User Accounts (Pre-loaded)
User 1:

Username: john_doe

Password: user123

Email: john@example.com

User 2:

Username: jane_smith

Password: user123

Email: jane@example.com

User 3:

Username: robert_wilson

Password: user123

Email: robert@example.com

💳 Payment Gateway Setup
The project integrates PayU Money payment gateway. For testing:

Test Credentials:
Merchant Key: gtKFFx (Test Key)

Merchant Salt: eCwWELxi (Test Salt)

Test Card Details:

Card Number: 5123456789012346

Expiry: 05/25

CVV: 123

Name: Test User

Payment Methods Supported:
Credit/Debit Cards

Net Banking

UPI Payments

Digital Wallets

🗄️ Database Information
Database Name: online_tourism
Key Tables:
users - User account information

admin - Admin account information

destinations - Tourist destinations

tour_packages - Tour packages with pricing

itinerary - Day-wise tour plans

inclusions - Package inclusions/exclusions

bookings - User bookings

payments - Payment transactions

reviews - User reviews & ratings

contact_messages - Contact form submissions

Sample Data Pre-loaded:
6 Popular Destinations

6 Tour Packages

3 Sample Users

Sample Bookings & Reviews

🛠️ Installation Guide
Prerequisites:
XAMPP/WAMP installed

PHP 7.4+ with MySQLi extension

Web browser

Step-by-Step Setup:
Install XAMPP:

bash
Download from: https://www.apachefriends.org/
Install and start Apache & MySQL
Setup Project:

bash
# Copy project to htdocs
C:\xampp\htdocs\online-tourism\

# Or use git
git clone <repository-url> C:\xampp\htdocs\online-tourism\
Create Database:

sql
# Method 1: Using phpMyAdmin
1. Open http://localhost/phpmyadmin
2. Create new database: 'online_tourism'
3. Import 'online_tourism.sql' file

# Method 2: Using MySQL CLI
mysql -u root -p
CREATE DATABASE online_tourism;
USE online_tourism;
SOURCE online_tourism.sql;
Configure Database Connection:
Edit includes/config.php:

php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Default XAMPP
define('DB_PASS', '');          // Default XAMPP (no password)
define('DB_NAME', 'online_tourism');
Set Upload Permissions:

bash
# Create upload directories
mkdir assets/images/uploads/
mkdir assets/images/uploads/destinations/
mkdir assets/images/uploads/packages/
mkdir assets/images/uploads/gallery/
mkdir assets/images/uploads/profiles/

# Set permissions (Linux/Mac)
chmod -R 755 assets/images/uploads/

# Windows: Give write permissions to these folders
Configure PayU (Optional for Testing):
Edit includes/config.php:

php
define('PAYU_MERCHANT_KEY', 'gtKFFx');
define('PAYU_MERCHANT_SALT', 'eCwWELxi');
define('PAYU_BASE_URL', 'https://test.payu.in');
🚀 Running the Application
Start XAMPP Control Panel:

Start Apache

Start MySQL

Access the Application:

Main Website: http://localhost/online-tourism/

Admin Panel: http://localhost/online-tourism/admin/

User Login: http://localhost/online-tourism/pages/login.php

Test the Features:

Register a new user account

Browse destinations & packages

Make a test booking

Try test payment (use test credentials)

Login as admin to manage content

📱 User Flow
For Regular Users:
Register/Login → Create account or sign in

Browse → View destinations & packages

Select → Choose package & travel dates

Book → Enter traveler details

Pay → Complete payment via PayU

Manage → View bookings in dashboard

Review → Share experience after travel

For Admin:
Login → Access admin panel

Dashboard → View statistics & analytics

Manage Content → Add/edit destinations & packages

Manage Bookings → Confirm/cancel bookings

Manage Users → View & manage user accounts

Moderate → Approve/reject reviews

🔒 Security Features
Password Security:

Bcrypt password hashing

Password strength validation

Secure password reset mechanism

Session Security:

Session regeneration

Session timeout (30 minutes)

Secure cookie settings

Input Validation:

Client-side JavaScript validation

Server-side PHP validation

SQL injection prevention

XSS protection

File Upload Security:

File type validation

File size restrictions

Virus scanning (placeholder)

Payment Security:

SSL encryption

Secure payment gateway

Transaction logging

📊 Database Schema Details
Users Table (users):
sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Bcrypt hashed
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Tour Packages Table (tour_packages):
sql
CREATE TABLE tour_packages (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    package_name VARCHAR(200) NOT NULL,
    destination_id INT,
    duration_days INT NOT NULL,
    duration_nights INT NOT NULL,
    price_per_person DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    max_capacity INT NOT NULL,
    current_bookings INT DEFAULT 0,
    status ENUM('active','inactive','sold_out') DEFAULT 'active'
);
🎨 Design Features
Responsive Design:
Mobile First approach

Bootstrap-like grid system

Flexible layouts for all devices

Touch-friendly navigation

UI Components:
Modern card-based design

Interactive forms with validation

Image galleries with lightbox

Progress indicators for bookings

Notification system (toasts)

Color Scheme:
Primary: #3498db (Blue)

Secondary: #2ecc71 (Green)

Accent: #e74c3c (Red)

Dark: #2c3e50 (Dark Blue)

Light: #ecf0f1 (Light Gray)

📧 Email Notifications (Placeholder)
The system is designed to support email notifications for:

Registration confirmation

Booking confirmation

Payment receipts

Travel reminders

Password reset

To enable emails: Configure SMTP settings in includes/config.php

🧪 Testing Guide
Functional Testing:
User Registration: Test with valid/invalid data

Login/Logout: Test authentication flow

Booking Flow: Complete booking process

Payment: Test with PayU sandbox

Admin Functions: Test all CRUD operations

Browser Compatibility:
✅ Chrome 80+

✅ Firefox 75+

✅ Safari 13+

✅ Edge 80+

✅ Mobile browsers

Performance Testing:
Page load time < 3 seconds

Database queries optimized

Image compression implemented

Caching enabled

🔄 Update & Maintenance
Regular Maintenance Tasks:
Database Backup:

sql
mysqldump -u root -p online_tourism > backup_$(date +%Y%m%d).sql
Clear Old Data:

sql
-- Auto-clean pending bookings after 24 hours
UPDATE bookings 
SET booking_status = 'cancelled'
WHERE booking_status = 'pending' 
AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
Update Configuration:

Update payment gateway credentials

Change admin passwords regularly

Update contact information

Adding New Features:
New Payment Gateway:

Create new payment processor class

Update payment form

Add to configuration

Social Login:

Integrate OAuth providers

Update registration flow

Modify user table

🐛 Troubleshooting
Common Issues & Solutions:
Database Connection Error:

bash
# Check if MySQL is running
# Verify credentials in config.php
# Check database exists
File Upload Errors:

bash
# Check folder permissions
# Verify upload limits in php.ini
# Check file size restrictions
Payment Gateway Issues:

bash
# Verify PayU credentials
# Check internet connection
# Test with sandbox credentials
Session Problems:

bash
# Clear browser cookies
# Check session.save_path in php.ini
# Verify session_start() in files
Debug Mode:
Enable in includes/config.php:

php
error_reporting(E_ALL);
ini_set('display_errors', 1);
📄 Documentation Files
The project includes:

✅ This README.md - Complete setup guide

✅ Code Comments - Inline documentation

✅ Database Schema - Complete SQL file

✅ API Documentation - In code comments

✅ User Manual - Inline help texts

👥 Project Team & Credits
Developed For:
College Final Year Project

Computer Science/IT Department

Academic Year 2024-2025

Technologies Used:
PHP - Server-side scripting

MySQL - Database management

HTML5/CSS3 - Frontend structure & styling

JavaScript - Client-side interactivity

PayU API - Payment processing

FPDF - PDF generation

Learning Outcomes:
Full-stack web development

Database design & management

Payment gateway integration

User authentication & security

Responsive web design

Project documentation

📞 Support & Contact
For support or queries:

Email: support@tourism.com

Phone: +91 9876543210

Website: http://localhost/online-tourism/pages/contact.php

📜 License
This project is developed for educational purposes only. Not for commercial use without proper licensing.

🎯 Quick Start Summary
Install XAMPP

Import database online_tourism.sql

Configure includes/config.php

Set permissions for upload folders

Access http://localhost/online-tourism/

Login with admin/user credentials

Start exploring!
