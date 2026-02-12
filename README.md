# Student Management System (SMS)

A lightweight, fast, and responsive Student Management System built with PHP and Bootstrap 5.

## Features
- **Admin Dashboard**: Overview of students and classes.
- **Student Management**: Full CRUD (Create, Read, Update, Delete) with photo upload.
- **Fees Management**: Track Admission and Monthly fees.
- **Receipt System**: Generate printable receipts (Print to Paper or Save as PDF).
- **Responsive UI**: Works perfectly on Mobile, Tablet, and Laptop.

## Installation Instructions (InfinityFree)

1. **Database Setup**:
   - Log in to your InfinityFree Control Panel.
   - Go to **MySQL Databases** and create a new database.
   - Open **phpMyAdmin** for that database.
   - Import the `database.sql` file provided in this package.

2. **Configuration**:
   - Open `config.php`.
   - Update the `DB_HOST`, `DB_USER`, `DB_PASS`, and `DB_NAME` with your InfinityFree database credentials.

3. **Upload Files**:
   - Use an FTP client (like FileZilla) or the Online File Manager.
   - Upload all files from the `student_system` folder to your `htdocs` directory.
   - Ensure the `uploads/` folder has write permissions (usually 755 or 777).

4. **Login**:
   - Default Username: `admin`
   - Default Password: `admin123`

## Project Structure
- `admin/`: Contains dashboard, student management, and fees management files.
- `includes/`: Header, footer, and authentication checks.
- `uploads/`: Directory for student photos.
- `config.php`: Database connection settings.
- `database.sql`: MySQL database schema.
- `login.php`: Secure admin login page.
