# 🏋️ GYM MANAGEMENT SYSTEM - Complete Fitness Platform

<div align="center">

![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-green.svg)
![License](https://img.shields.io/badge/License-MIT-brightgreen.svg)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success.svg)

</div>

## 🌟 COMPLETE WORKING PROJECT

**THIS IS A FULLY FUNCTIONAL GYM MANAGEMENT SYSTEM**

All features including member management, trainer systems, payment processing, and admin controls are fully implemented and production-ready.

### 📧 Contact Developer

**Developer:** Vivek P S  
**Email:** viveksubhash4@gmail.com  
**GitHub:** [@VivekOrginal](https://github.com/VivekOrginal)

💼 **Need customization or support?** Contact me for professional development services and technical assistance.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [System Architecture](#system-architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Schema](#database-schema)
- [Screenshots](#screenshots)
- [API Documentation](#api-documentation)
- [Security Features](#security-features)
- [License](#license)
- [Contact](#contact)

---

## 🌟 Overview

The Gym Management System is a comprehensive PHP-based web application designed to streamline gym operations and enhance member experience. This platform combines member management, trainer coordination, payment processing, and administrative controls to create a complete fitness center ecosystem.

### Mission Statement
**"Empowering fitness centers with technology for efficient management and enhanced member experience"**

### Key Objectives
- Provide comprehensive member management capabilities
- Enable efficient trainer-member coordination
- Streamline payment and membership processes
- Offer detailed analytics and reporting
- Create a seamless user experience across all roles

---

## ✨ Features

### 🔐 User Authentication & Management
- **Multi-Role System** - Members, Trainers, and Administrators
- **Secure Registration** - Email verification and validation
- **Password Management** - Reset and change password functionality
- **Profile Management** - Complete user profile customization
- **Session Security** - Secure session handling and timeout
- **Role-Based Access** - Different permissions for each user type

### 👥 Member Management System
- **Member Registration** - Complete registration with profile setup
- **Membership Plans** - Multiple membership tiers and packages
- **Booking System** - Book gym sessions and classes
- **Payment Integration** - Secure payment processing for memberships
- **Diet Plan Requests** - Request personalized diet plans from trainers
- **Progress Tracking** - Monitor fitness progress and achievements
- **Membership Status** - Real-time membership status and expiry tracking
- **Payment History** - Complete transaction history and receipts

### 🏃♂️ Trainer Management System
- **Trainer Registration** - Professional trainer onboarding
- **Profile Verification** - Admin approval system for trainers
- **Diet Plan Creation** - Create and manage personalized diet plans
- **Member Assignment** - Assign and manage members
- **Progress Monitoring** - Track member progress and performance
- **Schedule Management** - Manage training schedules and availability
- **Revenue Tracking** - Monitor earnings from diet plans and sessions
- **Communication Tools** - Direct communication with assigned members

### 🛠️ Admin Panel & Controls
- **Complete Dashboard** - Comprehensive overview of gym operations
- **Member Analytics** - Detailed member statistics and insights
- **Trainer Management** - Approve, manage, and monitor trainers
- **Category Management** - Create and manage workout categories
- **Plan Management** - Configure membership and diet plans
- **Payment Oversight** - Monitor all financial transactions
- **Report Generation** - Generate detailed reports and analytics
- **System Configuration** - Configure system settings and preferences

### 💰 Payment & Financial System
- **Secure Payment Processing** - Multiple payment gateway support
- **Membership Fee Management** - Automated membership billing
- **Diet Plan Payments** - Separate billing for diet consultations
- **Payment Status Tracking** - Real-time payment status updates
- **Invoice Generation** - Automatic invoice creation and delivery
- **Revenue Analytics** - Detailed financial reporting and insights
- **Refund Management** - Handle refunds and payment disputes
- **Payment Reminders** - Automated payment reminder system

### 📊 Analytics & Reporting
- **Member Analytics** - Registration trends, retention rates
- **Revenue Reports** - Income analysis and financial insights
- **Trainer Performance** - Trainer efficiency and member satisfaction
- **Usage Statistics** - Gym facility utilization reports
- **Growth Metrics** - Business growth and expansion insights
- **Custom Reports** - Generate custom reports based on specific criteria

### 📱 Modern UI/UX Design
- **Responsive Design** - Optimized for all devices and screen sizes
- **Modern Interface** - Clean, professional, and user-friendly design
- **Dark Theme Support** - Modern dark theme with gradient accents
- **Mobile-First** - Optimized for mobile devices and tablets
- **Fast Loading** - Optimized performance and quick page loads
- **Accessibility** - WCAG compliant and accessible design

---

## 🛠️ Technology Stack

### Backend Technologies
```
- PHP 7.4+ (Server-side scripting)
- MySQL 5.7+ (Database management)
- Apache/Nginx (Web server)
- PHPMailer 6.10+ (Email functionality)
- Composer (Dependency management)
```

### Frontend Technologies
```
- HTML5 (Semantic markup)
- CSS3 (Modern styling)
- JavaScript ES6+ (Interactive functionality)
- Bootstrap 4.5+ (Responsive framework)
- jQuery 3.6+ (DOM manipulation)
- Font Awesome 5.15+ (Icons)
```

### Development Tools
```
- Git (Version control)
- Composer (Package manager)
- XAMPP/WAMP (Development environment)
- phpMyAdmin (Database management)
- Visual Studio Code (Recommended IDE)
```

### Security Features
```
- Password hashing (bcrypt)
- SQL injection prevention
- XSS protection
- CSRF protection
- Session security
- Input validation and sanitization
```

---

## 🏗️ System Architecture

### Project Structure
```
gym/
├── assets/                     # Static assets
│   ├── css/                   # Stylesheets
│   │   ├── bootstrap.min.css
│   │   ├── main.css
│   │   └── responsive.css
│   ├── js/                    # JavaScript files
│   │   ├── bootstrap.min.js
│   │   ├── jquery.min.js
│   │   └── main.js
│   ├── img/                   # Images and graphics
│   └── fonts/                 # Font files
├── uploads/                   # User uploads
│   ├── gym_images/           # Gym facility images
│   ├── trainer_id_proofs/    # Trainer verification documents
│   ├── diet_plans/           # Diet plan documents
│   └── videos/               # Promotional videos
├── PHPMailer/                # Email functionality
│   ├── src/                  # PHPMailer source files
│   └── language/             # Language files
├── vendor/                   # Composer dependencies
├── Doc/                      # Documentation files
├── screenshots/              # Application screenshots
├── config/                   # Configuration files
├── includes/                 # Common PHP includes
├── admin/                    # Admin panel files
├── trainer/                  # Trainer dashboard files
├── member/                   # Member dashboard files
├── auth/                     # Authentication files
├── api/                      # API endpoints
├── database/                 # Database files
│   ├── migrations/           # Database migrations
│   └── seeds/                # Sample data
├── composer.json             # Composer configuration
├── .htaccess                 # Apache configuration
├── index.html                # Main entry point
└── README.md                 # This file
```

### Application Flow
```
1. User Access
   ↓
2. Authentication Check
   ↓
3. Role-Based Routing:
   - Member Dashboard
   - Trainer Dashboard  
   - Admin Panel
   ↓
4. Feature Access:
   - Profile Management
   - Membership Operations
   - Payment Processing
   - Analytics & Reports
   ↓
5. Database Operations:
   - CRUD Operations
   - Transaction Processing
   - Data Analytics
   ↓
6. Response Generation:
   - Dynamic Content
   - Status Updates
   - Notifications
```

---

## 📦 Installation

### Prerequisites

**Required Software:**
- PHP 7.4 or higher ([Download](https://www.php.net/downloads))
- MySQL 5.7 or higher ([Download](https://dev.mysql.com/downloads/))
- Apache Web Server (XAMPP recommended)
- Composer ([Download](https://getcomposer.org/))
- Modern web browser

**Recommended:**
- XAMPP Control Panel
- phpMyAdmin
- Visual Studio Code
- Git for version control

### Step-by-Step Installation

#### 1. Clone the Repository
```bash
git clone https://github.com/VivekOrginal/GYM-MANAGEMENT-PHP.git
cd GYM-MANAGEMENT-PHP
```

#### 2. Setup Web Server
```bash
# Copy project to web server directory
# For XAMPP: C:\xampp\htdocs\gym\
# For WAMP: C:\wamp64\www\gym\
# For Linux: /var/www/html/gym/
```

#### 3. Install Dependencies
```bash
# Install Composer dependencies
composer install

# Verify PHPMailer installation
composer require phpmailer/phpmailer
```

#### 4. Database Configuration
```sql
-- Create database
CREATE DATABASE gym_management;

-- Import database schema
-- Use phpMyAdmin or command line:
mysql -u username -p gym_management < database/gym_management.sql
```

#### 5. Configure Database Connection
```php
// config/database.php
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gym_management";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

#### 6. Email Configuration
```php
// config/email.php
<?php
// SMTP Configuration
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'your-email@gmail.com';
$smtp_password = 'your-app-password';
$smtp_secure = 'tls';
?>
```

#### 7. Start Services
```bash
# Start Apache and MySQL
# Using XAMPP Control Panel or command line:
sudo service apache2 start
sudo service mysql start
```

#### 8. Access Application
- **Main Site:** http://localhost/gym/
- **Admin Panel:** http://localhost/gym/admin/
- **Member Dashboard:** http://localhost/gym/member/
- **Trainer Dashboard:** http://localhost/gym/trainer/

### Quick Setup (Windows with XAMPP)
1. Download and install XAMPP
2. Clone repository to `C:\xampp\htdocs\gym\`
3. Start Apache and MySQL from XAMPP Control Panel
4. Import database using phpMyAdmin
5. Configure email settings
6. Access http://localhost/gym/

---

## ⚙️ Configuration

### Database Configuration

**config/database.php:**
```php
<?php
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gym_management');

// PDO connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
```

### Email Configuration

**config/email.php:**
```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'Gym Management System');
?>
```

### Application Configuration

**config/app.php:**
```php
<?php
// Application settings
define('APP_NAME', 'Gym Management System');
define('APP_URL', 'http://localhost/gym/');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Kolkata');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov']);

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
?>
```

---

## 🚀 Usage

### Admin Usage

#### 1. Admin Login
```
URL: /admin/login.php
Default Credentials:
- Username: admin
- Password: admin123
```

#### 2. Dashboard Overview
- View total members, trainers, and revenue
- Monitor recent registrations and activities
- Access quick action buttons for common tasks

#### 3. Member Management
- View all registered members
- Approve/reject membership applications
- Monitor payment status and membership expiry
- Generate member reports

#### 4. Trainer Management
- Review trainer applications
- Approve trainer registrations
- Monitor trainer performance and ratings
- Manage trainer categories and specializations

### Trainer Usage

#### 1. Trainer Registration
```
URL: /trainer_register.php
Required Information:
- Personal details
- Qualifications and certifications
- ID proof upload
- Specialization areas
```

#### 2. Trainer Dashboard
- View assigned members
- Create and manage diet plans
- Monitor member progress
- Handle diet plan requests

#### 3. Diet Plan Management
- Create personalized diet plans
- Upload diet plan documents
- Set pricing for consultations
- Track plan effectiveness

### Member Usage

#### 1. Member Registration
```
URL: /register.php
Registration Process:
- Fill personal information
- Choose membership plan
- Complete payment
- Account activation
```

#### 2. Member Dashboard
- View membership status and expiry
- Browse available gym plans
- Request diet plans from trainers
- Make payments for services

#### 3. Booking System
- Browse gym categories
- Select membership plans
- Process secure payments
- Download membership receipts

---

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    role ENUM('admin', 'member', 'trainer') DEFAULT 'member',
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Memberships Table
```sql
CREATE TABLE memberships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    plan_type VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    payment_status ENUM('paid', 'pending', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Trainers Table
```sql
CREATE TABLE trainers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    specialization VARCHAR(200),
    experience_years INT,
    certification TEXT,
    id_proof_path VARCHAR(255),
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Diet Plans Table
```sql
CREATE TABLE diet_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trainer_id INT NOT NULL,
    member_id INT,
    plan_name VARCHAR(100) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    price DECIMAL(10,2),
    duration_days INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id),
    FOREIGN KEY (member_id) REFERENCES users(id)
);
```

### Payments Table
```sql
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('membership', 'diet_plan', 'other') NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Gym Categories Table
```sql
CREATE TABLE gym_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 📸 Screenshots

<div align="center">

### Homepage
![Homepage](screenshots/home.png)

### User Authentication
<table>
  <tr>
    <td><img src="screenshots/login.png" alt="Login" width="400"/></td>
    <td><img src="screenshots/register.png" alt="Register" width="400"/></td>
  </tr>
</table>

### Trainer Registration
![Trainer Registration](screenshots/trainer-register.png)

### Additional Views
<table>
  <tr>
    <td><img src="screenshots/home-1.png" alt="Home View 1" width="400"/></td>
    <td><img src="screenshots/home-2.png" alt="Home View 2" width="400"/></td>
  </tr>
  <tr>
    <td><img src="screenshots/home-3.png" alt="Home View 3" width="400"/></td>
    <td><img src="screenshots/home-4.png" alt="Home View 4" width="400"/></td>
  </tr>
</table>

### Payment Integration
![Payment QR](screenshots/payment-qr.png)

</div>

---

## 🔌 API Documentation

### Authentication Endpoints
```
POST /api/auth/login          - User login
POST /api/auth/register       - User registration
POST /api/auth/logout         - User logout
POST /api/auth/forgot-password - Password reset request
POST /api/auth/reset-password  - Password reset
```

### Member Endpoints
```
GET  /api/members/            - List all members
GET  /api/members/{id}        - Get member details
PUT  /api/members/{id}        - Update member profile
DELETE /api/members/{id}      - Delete member account
GET  /api/members/{id}/memberships - Get member memberships
```

### Trainer Endpoints
```
GET  /api/trainers/           - List all trainers
GET  /api/trainers/{id}       - Get trainer details
PUT  /api/trainers/{id}       - Update trainer profile
GET  /api/trainers/{id}/diet-plans - Get trainer's diet plans
POST /api/trainers/{id}/diet-plans - Create new diet plan
```

### Payment Endpoints
```
POST /api/payments/process    - Process payment
GET  /api/payments/{id}       - Get payment details
GET  /api/payments/history/{user_id} - Get payment history
POST /api/payments/refund     - Process refund
```

### Admin Endpoints
```
GET  /api/admin/dashboard     - Get dashboard statistics
GET  /api/admin/users         - List all users
PUT  /api/admin/users/{id}/status - Update user status
GET  /api/admin/reports       - Generate reports
```

---

## 🛡️ Security Features

### Authentication Security
- **Password Hashing:** bcrypt with salt
- **Session Management:** Secure session handling with timeout
- **Login Attempts:** Rate limiting for failed login attempts
- **Password Policy:** Minimum length and complexity requirements
- **Account Lockout:** Temporary lockout after multiple failed attempts

### Data Protection
- **SQL Injection Prevention:** Prepared statements and parameterized queries
- **XSS Protection:** Input sanitization and output encoding
- **CSRF Protection:** Token-based CSRF prevention
- **File Upload Security:** File type validation and secure storage
- **Data Encryption:** Sensitive data encryption at rest

### Access Control
- **Role-Based Access:** Different permissions for admin, trainer, and member
- **Route Protection:** Authentication required for protected routes
- **Input Validation:** Server-side validation for all user inputs
- **Error Handling:** Secure error messages without information disclosure
- **Audit Logging:** Activity logging for security monitoring

---

## 📄 License

**MIT License**

Copyright (c) 2025 Vivek P S

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

---

## 📞 Contact

### Developer Information

**Vivek P S**

📧 **Email:** viveksubhash4@gmail.com  
🐙 **GitHub:** [@VivekOrginal](https://github.com/VivekOrginal)  
💼 **LinkedIn:** [Connect with me](https://linkedin.com/in/vivekps)

### Professional Services

**Available for:**
- ✅ Custom gym management solutions
- ✅ Feature enhancements and modifications
- ✅ Integration with third-party services
- ✅ Technical support and maintenance
- ✅ Training and documentation
- ✅ Deployment and hosting assistance

**Payment Methods:**

<div align="center">

![Google Pay](screenshots/payment-qr.png)

*Scan to pay via Google Pay/UPI*

</div>

**Contact:** viveksubhash4@gmail.com

---

## 🙏 Acknowledgments

- PHP Community for excellent documentation
- Bootstrap team for responsive framework
- PHPMailer contributors for email functionality
- MySQL team for robust database system
- Open source community for inspiration

---

## 📊 Project Statistics

- **Lines of Code:** 12,000+
- **Files:** 85+
- **Database Tables:** 15+
- **Features:** 50+
- **User Roles:** 3
- **Payment Methods:** Multiple
- **Responsive Breakpoints:** 5+

---

<div align="center">

**Made with ❤️ for fitness enthusiasts and gym management**

© 2025 Vivek P S. All Rights Reserved.

[⬆ Back to Top](#-gym-management-system---complete-fitness-platform)

</div>