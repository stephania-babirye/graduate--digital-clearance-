-- ========================================
-- Graduate Digital Clearance System
-- Uganda Martyrs University
-- Comprehensive Database Setup
-- ========================================
-- This consolidated script creates all tables with complete schema
-- Run this SQL script on a fresh MySQL database

CREATE DATABASE IF NOT EXISTS graduation_clearance;
USE graduation_clearance;

-- ========================================
-- USERS TABLE
-- All system users: students, officers, admins
-- ========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(50) UNIQUE,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'finance', 'library', 'ict', 'dean', 'registrar', 'admin') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- STUDENT PROFILES TABLE
-- Extended student information including faculty
-- ========================================
CREATE TABLE IF NOT EXISTS student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    program_level VARCHAR(50) DEFAULT NULL,
    course VARCHAR(255),
    faculty VARCHAR(100),
    campus ENUM('Nkozi', 'Rubaga', 'Masaka', 'Ngetta', 'Fortportal'),
    date_of_birth DATE,
    year_of_intake YEAR,
    photo_path VARCHAR(255),
    profile_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- CLEARANCE APPLICATIONS TABLE
-- Main clearance tracking across all departments
-- ========================================
CREATE TABLE IF NOT EXISTS clearance_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    finance_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    library_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    ict_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    faculty_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    registrar_status ENUM('pending', 'approved') DEFAULT 'pending',
    all_approved TINYINT(1) DEFAULT 0,
    graduation_year YEAR DEFAULT 2026,
    locked TINYINT(1) DEFAULT 0,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- DEPARTMENT APPROVALS TABLE
-- Detailed approval info with department-specific fields
-- ICT: Equipment tracking (laptop, damage)
-- Faculty: Academic verification (results, dissertation)
-- ========================================
CREATE TABLE IF NOT EXISTS department_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    department ENUM('finance', 'library', 'ict', 'faculty', 'registrar') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT,
    rejection_reason TEXT,
    notes TEXT,
    -- ICT Officer specific fields
    laptop_returned ENUM('yes', 'no', 'n/a') DEFAULT 'n/a',
    equipment_damaged ENUM('yes', 'no', 'n/a') DEFAULT 'n/a',
    damage_description TEXT,
    equipment_notes TEXT,
    -- Faculty Dean specific fields
    results_confirmed ENUM('yes', 'no', 'pending') DEFAULT 'pending',
    dissertation_approved ENUM('yes', 'no', 'n/a') DEFAULT 'n/a',
    faculty_name VARCHAR(100),
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES clearance_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- APPROVAL HISTORY TABLE
-- Audit trail of all approval actions
-- ========================================
CREATE TABLE IF NOT EXISTS approval_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    department ENUM('finance', 'library', 'ict', 'faculty', 'registrar') NOT NULL,
    action ENUM('approved', 'rejected') NOT NULL,
    officer_id INT NOT NULL,
    officer_name VARCHAR(255) NOT NULL,
    reason TEXT,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES clearance_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (officer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- GRADUATION LIST TABLE
-- Confirmed graduates with registrar notes
-- ========================================
CREATE TABLE IF NOT EXISTS graduation_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_id INT NOT NULL,
    graduation_year YEAR DEFAULT 2026,
    confirmed_by INT NOT NULL,
    confirmation_status ENUM('confirmed', 'not_confirmed') DEFAULT 'confirmed',
    notes TEXT,
    confirmed_at TIMESTAMP NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES clearance_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- SYSTEM SETTINGS TABLE
-- University and system configuration
-- ========================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_name VARCHAR(255) DEFAULT 'Uganda Martyrs University',
    university_email VARCHAR(255) DEFAULT 'info@umu.ac.ug',
    university_phone VARCHAR(50) DEFAULT '+256-414-410-611',
    graduation_year YEAR DEFAULT 2026,
    clearance_open TINYINT(1) DEFAULT 1,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- ACTIVITY LOGS TABLE
-- System-wide activity tracking
-- ========================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- DEFAULT DATA
-- Initial admin user (password: admin123)
-- IMPORTANT: Change this password immediately after installation
-- ========================================
INSERT INTO users (full_name, email, password, role) 
VALUES ('System Administrator', 'admin@umu.ac.ug', '$2y$10$7XtGLmwk/1dldSzIhh/Z1uKOZa0CS3lUs7WiPQ.ux41rIKCEsgWsa', 'admin');

-- Insert default system settings
INSERT INTO system_settings (university_name, university_email, university_phone, graduation_year, clearance_open) 
VALUES ('Uganda Martyrs University', 'info@umu.ac.ug', '+256-414-410-611', 2026, 1);