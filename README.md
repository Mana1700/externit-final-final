# ExternIT - Student-Company Collaboration Platform

ExternIT is a web-based platform that connects students with companies, allowing students to complete tasks, earn certificates, and build their portfolios while companies find solutions for their needs.

## Project Overview

ExternIT serves as a bridge between talented students and innovative companies. The platform enables:
- Companies to post tasks and requirements
- Students to submit solutions and showcase their skills
- Both parties to collaborate and build professional relationships
- Students to earn certificates and build their portfolios
- Companies to find and evaluate potential future employees

## Features

### For Students
- Create and manage student profiles
- Browse and apply for tasks
- Submit solutions and track submissions
- Earn certificates for completed tasks
- Build a professional portfolio
- Receive notifications about task updates
- View and manage personal dashboard

### For Companies
- Create and manage company profiles
- Post tasks with detailed requirements
- Review student submissions
- Manage task deadlines and requirements
- Evaluate and select solutions
- Generate certificates for students
- Access company dashboard for task management

## Technical Stack

- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **Backend**: PHP
- **Database**: MySQL
- **Server**: XAMPP (Apache, MySQL, PHP)

## Project Structure

```
ExternIT-final/
├── app/                 # Application core files
│   ├── Controllers/     # Controller classes for handling business logic
│   ├── Models/          # Database models and data handling
│   └── Views/           # View templates and UI components
├── assets/             # Static assets
│   ├── images/         # Image files
│   ├── js/             # JavaScript files
│   └── css/            # Additional CSS files
├── config/             # Configuration files
│   ├── database.php    # Database connection settings
│   └── config.php      # General application settings
├── css/                # Main stylesheets
│   ├── _variables.css  # CSS variables and themes
│   ├── _student.css    # Student-specific styles
│   └── _company.css    # Company-specific styles
├── includes/           # Common PHP includes
│   ├── header.php      # Common header
│   ├── footer.php      # Common footer
│   ├── navigation.php  # Navigation menu
│   └── functions.php   # Helper functions
├── sql/                # Database files
│   ├── schema.sql      # Database schema
│   ├── complete_database.sql  # Complete database setup
│   └── update_*.sql    # Database update scripts
├── uploads/            # User uploads directory
│   ├── submissions/    # Student submission files
│   ├── certificates/   # Generated certificates
│   └── profiles/       # User profile images
└── *.php              # Main application files
```

## File Descriptions

### Core Application Files
- `index.php` - Main landing page with platform overview and registration options
- `login.php` - User authentication and login handling
- `register.php` - User registration for both students and companies
- `logout.php` - Session termination and logout handling
- `process.php` - "How It Works" page explaining platform workflow
- `features.php` - Detailed platform features and capabilities
- `help.php` - Help documentation and FAQs
- `about.php` - Information about the platform

### Student Interface
- `student_dashboard.php` - Main student dashboard with tasks and submissions
- `view_tasks.php` - Browse and search available tasks
- `view_task.php` - View detailed task information
- `submit_task.php` - Submit solutions for tasks
- `my_submissions.php` - Manage and track submitted work
- `my_certificates.php` - View and download earned certificates
- `profile.php` - Manage student profile and settings

### Company Interface
- `company_dashboard.php` - Main company dashboard with tasks and submissions
- `post_task.php` - Create and publish new tasks
- `manage_tasks.php` - Manage existing tasks and requirements
- `review_submissions.php` - List and filter student submissions
- `review_submission.php` - Review individual submissions
- `generate_certificate_pdf.php` - Generate certificates for approved submissions

### Utility Files
- `download_submission.php` - Handle file downloads for submissions
- `mark_notification_read.php` - Update notification status
- `notifications.php` - Display and manage user notifications
- `process.php` - Handle form submissions and data processing

### Configuration Files
- `composer.json` - PHP dependencies and autoloading
- `config/database.php` - Database connection settings
- `config/config.php` - Application configuration

### Database Files
- `sql/schema.sql` - Database table structure
- `sql/complete_database.sql` - Complete database setup
- `sql/update_*.sql` - Database update scripts for schema changes

### Style Files
- `css/_variables.css` - CSS variables and theme settings
- `css/_student.css` - Student-specific styles
- `css/_company.css` - Company-specific styles
- `css/_dashboard.css` - Dashboard-specific styles

### Include Files
- `includes/header.php` - Common header with navigation
- `includes/footer.php` - Common footer with scripts
- `includes/navigation.php` - Navigation menu structure
- `includes/functions.php` - Helper functions and utilities

## Key Files

- `index.php` - Main landing page
- `login.php` - User authentication
- `register.php` - User registration
- `student_dashboard.php` - Student interface
- `company_dashboard.php` - Company interface
- `post_task.php` - Task creation
- `submit_task.php` - Task submission
- `review_submissions.php` - Submission review
- `generate_certificate_pdf.php` - Certificate generation
- `my_submissions.php` - Student submission management
- `my_certificates.php` - Certificate management
- `notifications.php` - Notification system
- `profile.php` - User profile management
- `manage_tasks.php` - Company task management
- `view_tasks.php` - Task browsing
- `view_task.php` - Individual task view
- `download_submission.php` - File download handler
- `process.php` - Form processing
- `help.php` - Help documentation
- `features.php` - Platform features
- `about.php` - About page

## Database Structure

The database includes tables for:
- Users (students and companies)
- Tasks
- Submissions
- Certificates
- Notifications
- Profiles

## Setup Instructions

1. **Prerequisites**
   - XAMPP installed and running
   - PHP 7.4 or higher
   - MySQL 5.7 or higher

2. **Installation**
   - Clone the repository to your XAMPP htdocs directory
   - Import the database schema from `sql/complete_database.sql`
   - Configure database connection in `config/database.php`
   - Start Apache and MySQL services in XAMPP

3. **Configuration**
   - Update database credentials in config files
   - Set up proper file permissions for uploads directory
   - Configure email settings for notifications

## Usage

1. **For Students**
   - Register as a student
   - Browse available tasks
   - Submit solutions
   - Track submissions and earn certificates

2. **For Companies**
   - Register as a company
   - Post tasks with requirements
   - Review submissions
   - Generate certificates for completed tasks

## Security Features

- Password hashing
- Session management
- Input validation
- File upload restrictions
- SQL injection prevention
- XSS protection

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please contact the development team or create an issue in the repository. 