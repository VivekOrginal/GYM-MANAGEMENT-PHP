# 🏋️ Gym Management System

A comprehensive web-based gym management system built with PHP and MySQL, designed to streamline gym operations and enhance member experience.

## 🌟 Features

### 👥 Member Management
- Member registration and profile management
- Membership booking and status tracking
- Diet plan requests and management
- Payment processing for memberships and diet plans

### 🏃‍♂️ Trainer Management
- Trainer registration and profile management
- Diet plan creation and management
- Member assignment and tracking
- Trainer dashboard with comprehensive controls

### 🛠️ Admin Panel
- Complete gym administration
- Category management for workout plans
- Trainer approval system
- Membership and diet request oversight
- Comprehensive reporting and analytics

### 💰 Payment System
- Secure payment processing
- Membership fee management
- Diet plan fee handling
- Payment status tracking

## 🚀 Getting Started

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/gym-management-php.git
   cd gym-management-php
   ```

2. **Setup Database**
   - Start your XAMPP/WAMP server
   - Create a new MySQL database
   - Import the database schema (if provided)

3. **Configure Database Connection**
   - Update database credentials in configuration files
   - Ensure proper database connectivity

4. **Install Dependencies**
   ```bash
   composer install
   ```

5. **Access the Application**
   - Navigate to `http://localhost/gym/` in your browser
   - Start using the gym management system

## 📁 Project Structure

```
gym/
├── assets/                 # Static assets (CSS, JS, images)
├── uploads/               # File uploads (images, documents)
├── PHPMailer/            # Email functionality
├── vendor/               # Composer dependencies
├── Doc/                  # Documentation files
├── *.php                 # Core PHP application files
└── *.html               # Static HTML pages
```

## 🎯 Usage

### For Members
1. Register for a new account
2. Browse available gym plans and categories
3. Book memberships and request diet plans
4. Make payments securely
5. Track membership status and diet progress

### For Trainers
1. Register as a trainer
2. Create and manage diet plans
3. Handle member requests
4. Update member progress
5. Manage trainer profile

### For Administrators
1. Access admin dashboard
2. Approve trainer registrations
3. Manage gym categories and plans
4. Monitor all system activities
5. Generate reports and analytics

## 🛡️ Security Features

- Secure user authentication
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Secure file upload handling

## 📧 Email Configuration

The system uses PHPMailer for email functionality. Configure your email settings in the email configuration file for:
- Registration confirmations
- Password reset emails
- Booking notifications
- Payment confirmations

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Bootstrap for responsive UI components
- PHPMailer for email functionality
- Font Awesome for icons
- jQuery for enhanced user interactions

## 📞 Support

For support and queries, please open an issue in the GitHub repository or contact the development team.

---

**Made with ❤️ for fitness enthusiasts and gym management**