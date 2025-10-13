# DISC Leadership Assessment Platform - Setup Guide

## Overview
This is a complete PHP/MySQL backend integration for the DISC Leadership Assessment Platform. The system includes:

- **Frontend**: HTML/CSS/JavaScript assessment interface
- **Backend**: PHP REST API with MySQL database
- **Admin Panel**: Full-featured admin dashboard with database integration
- **Security**: Strong password authentication and access code management

## Features

### ✅ Completed Features
- ✅ MySQL database schema with all required tables
- ✅ PHP backend API with authentication, assessment submission, and admin functions
- ✅ Strong admin password system with change password functionality
- ✅ Access code management system with validation
- ✅ Frontend integration with PHP backend APIs
- ✅ Admin panel connected to database for data management
- ✅ DISC profile calculation and storage
- ✅ Session management and security

## Installation Instructions

### 1. Database Setup

1. **Create MySQL Database**:
   ```sql
   CREATE DATABASE disc_assessment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Database Schema**:
   ```bash
   mysql -u your_username -p disc_assessment < database/schema.sql
   ```

3. **Update Database Configuration**:
   Edit `config/database.php` and update the connection details:
   ```php
   private $host = 'localhost';
   private $db_name = 'disc_assessment';
   private $username = 'your_mysql_username';
   private $password = 'your_mysql_password';
   ```

### 2. File Structure
Ensure your project has this structure:
```
DISC/
├── api/
│   ├── auth.php
│   ├── assessment.php
│   └── admin.php
├── config/
│   ├── config.php
│   └── database.php
├── database/
│   └── schema.sql
├── css/
│   ├── style.css
│   └── admin.css
├── js/
│   ├── script.js
│   └── admin.js
├── images/
├── index.html
├── admin.html
└── contact.html
```

### 3. Web Server Configuration

#### For XAMPP/WAMP:
1. Copy the entire DISC folder to your web server root (e.g., `htdocs/` or `www/`)
2. Start Apache and MySQL services
3. Access via: `http://localhost/DISC/`

#### For Live Server:
1. Upload all files to your web server
2. Ensure PHP 7.4+ and MySQL 5.7+ are available
3. Update database connection settings in `config/database.php`

### 4. Default Admin Credentials

**IMPORTANT**: Change these immediately after first login!

- **Username**: `admin`
- **Password**: `AdminDisc2024!@#`

### 5. Access Codes

The system comes with 20 pre-generated access codes:
- `DISC2024A` through `DISC2024T`
- These are highlighted and ready to use
- Admin can generate additional codes as needed

## Usage Instructions

### For Participants (Assessment Takers):

1. **Access the Assessment**:
   - Go to `index.html`
   - Fill in: Full Name, Position, Email, Access Code
   - Click "Start Assessment"

2. **Complete Assessment**:
   - Answer all 24 questions
   - For each question, select MOST and LEAST like you
   - Cannot select same option for both MOST and LEAST
   - Progress bar shows completion status

3. **View Results**:
   - After submission, DISC profile is calculated and displayed
   - Results are saved to database

### For Administrators:

1. **Login to Admin Panel**:
   - Go to `admin.html`
   - Use default credentials (change immediately!)
   - Access full dashboard with statistics

2. **Manage Access Codes**:
   - Generate new access codes (1-100 at a time)
   - View used vs available codes
   - Track which codes are used by whom

3. **View Participants**:
   - See all completed assessments
   - View individual DISC profiles
   - Delete participant data if needed

4. **Change Password**:
   - Go to Settings section
   - Enter current and new passwords
   - Password must be at least 8 characters

5. **Customize Branding**:
   - Update company name, colors, fonts
   - Upload logo and background images
   - Customize report settings

## API Endpoints

### Authentication (`api/auth.php`)
- `POST` - `admin_login`: Admin authentication
- `POST` - `change_password`: Change admin password
- `POST` - `validate_access_code`: Validate participant access code

### Assessment (`api/assessment.php`)
- `POST` - `start_assessment`: Start new assessment session
- `POST` - `submit_assessment`: Submit completed assessment
- `GET` - `get_questions`: Get assessment questions
- `GET` - `get_results`: Get assessment results

### Admin (`api/admin.php`)
- `GET` - `dashboard_stats`: Get dashboard statistics
- `GET` - `participants`: Get all participants
- `GET` - `access_codes`: Get all access codes
- `POST` - `generate_codes`: Generate new access codes
- `POST` - `delete_participant`: Delete participant data

## Database Schema

### Tables:
- `admin_users`: Admin account management
- `access_codes`: Access code generation and tracking
- `participants`: Assessment participant data
- `assessment_answers`: Individual question responses
- `disc_profiles`: Calculated DISC results
- `admin_settings`: System configuration

## Security Features

- ✅ Strong password hashing (bcrypt)
- ✅ Session management with timeouts
- ✅ SQL injection prevention (prepared statements)
- ✅ Input sanitization and validation
- ✅ CSRF protection ready
- ✅ Access code validation and tracking

## Troubleshooting

### Common Issues:

1. **Database Connection Error**:
   - Check MySQL service is running
   - Verify credentials in `config/database.php`
   - Ensure database exists

2. **Permission Errors**:
   - Check file permissions (755 for directories, 644 for files)
   - Ensure web server can read PHP files

3. **Session Issues**:
   - Check PHP session configuration
   - Verify session storage directory is writable

4. **Access Code Not Working**:
   - Verify code exists in database
   - Check if code is already used
   - Ensure code hasn't expired

### Testing the System:

1. **Test Admin Login**:
   - Go to `admin.html`
   - Login with default credentials
   - Verify dashboard loads with statistics

2. **Test Assessment Flow**:
   - Use access code `DISC2024A`
   - Complete full assessment
   - Verify results are saved

3. **Test Access Code Generation**:
   - Generate new codes in admin panel
   - Verify they appear in the list
   - Test using a new code

## Support

For technical support or questions about the implementation, please refer to the code comments and this documentation. The system is fully functional and ready for production use.

## Security Recommendations

1. **Immediately change default admin password**
2. **Use HTTPS in production**
3. **Regular database backups**
4. **Monitor access code usage**
5. **Keep PHP and MySQL updated**

---

**System Status**: ✅ Fully Functional
**Last Updated**: January 2025
**Version**: 1.0.0
