CREATE DATABASE IF NOT EXISTS budgettrack_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE budgettrack_db;

-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    role_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Departments table
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dept_name VARCHAR(100) NOT NULL,
    dept_code VARCHAR(20) NOT NULL UNIQUE,
    dept_description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    employee_id VARCHAR(20) UNIQUE,
    department_id INT,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Permissions table
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(100) NOT NULL UNIQUE,
    permission_description TEXT,
    module VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Role permissions junction table
CREATE TABLE role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id)
);

-- User sessions table for security
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Budget categories table
CREATE TABLE budget_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    category_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Budget allocations table
CREATE TABLE budget_allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    category_id INT NOT NULL,
    fiscal_year YEAR NOT NULL,
    allocated_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    utilized_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    remaining_amount DECIMAL(15,2) GENERATED ALWAYS AS (allocated_amount - utilized_amount) STORED,
    status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES budget_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_dept_category_year (department_id, category_id, fiscal_year)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    module VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default roles - Corrected hierarchy
INSERT INTO roles (role_name, role_description) VALUES
('budget', 'Budget/Finance Office - System Administrator with full control over everything'),
('school_admin', 'School Administrator - View-only access to monitor system activities'),
('offices', 'Department Offices - Manages department budget and submits PPMP');

-- Insert default departments
INSERT INTO departments (dept_name, dept_code, dept_description) VALUES
('Budget Office', 'BUDGET', 'Central budget management office'),
('Office of the Chancellor', 'OCHAN', 'Executive office'),
('Academic Affairs', 'ACAD', 'Academic programs and curriculum'),
('Student Affairs', 'STUD', 'Student services and activities'),
('Administrative Services', 'ADMIN', 'Administrative support services'),
('Research and Development', 'R&D', 'Research programs and development'),
('Extension Services', 'EXT', 'Community extension programs'),
('Information Technology', 'IT', 'IT services and support'),
('Human Resources', 'HR', 'Human resource management'),
('Finance', 'FIN', 'Financial management and accounting');

-- Insert default permissions
INSERT INTO permissions (permission_name, permission_description, module) VALUES
-- User Management
('create_users', 'Create new user accounts', 'user_management'),
('edit_users', 'Edit existing user accounts', 'user_management'),
('delete_users', 'Delete user accounts', 'user_management'),
('view_users', 'View user accounts', 'user_management'),
('assign_roles', 'Assign roles to users', 'user_management'),

-- Role Management
('create_roles', 'Create new roles', 'role_management'),
('edit_roles', 'Edit existing roles', 'role_management'),
('delete_roles', 'Delete roles', 'role_management'),
('view_roles', 'View roles', 'role_management'),
('manage_permissions', 'Manage role permissions', 'role_management'),

-- Department Management
('create_departments', 'Create new departments', 'department_management'),
('edit_departments', 'Edit existing departments', 'department_management'),
('delete_departments', 'Delete departments', 'department_management'),
('view_departments', 'View departments', 'department_management'),

-- Budget Management
('create_budget', 'Create budget allocations', 'budget_management'),
('edit_budget', 'Edit budget allocations', 'budget_management'),
('view_budget', 'View budget information', 'budget_management'),
('approve_budget', 'Approve budget requests', 'budget_management'),

-- PPMP Management
('create_ppmp', 'Create PPMP submissions', 'ppmp_management'),
('edit_ppmp', 'Edit PPMP submissions', 'ppmp_management'),
('view_ppmp', 'View PPMP submissions', 'ppmp_management'),
('approve_ppmp', 'Approve PPMP submissions', 'ppmp_management'),

-- Reports
('view_reports', 'View system reports', 'reports'),
('generate_reports', 'Generate custom reports', 'reports'),
('export_reports', 'Export reports', 'reports'),

-- Dashboard
('view_dashboard', 'View dashboard', 'dashboard'),
('view_admin_dashboard', 'View admin dashboard', 'dashboard'),

-- Notifications
('view_notifications', 'View notifications', 'notifications'),
('send_notifications', 'Send notifications', 'notifications'),

-- System Control
('control_admin', 'Control admin role permissions and access', 'system_control'),
('manage_all_roles', 'Manage all roles including admin', 'system_control'),
('system_override', 'Override any system restrictions', 'system_control');

-- Assign permissions to roles - Corrected hierarchy
-- Budget/Finance Office - FULL CONTROL (System Administrator)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- School Administrator - VIEW-ONLY ACCESS (can see everything but cannot modify)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE permission_name IN (
    'view_users',
    'view_departments', 
    'view_budget',
    'view_ppmp',
    'view_reports',
    'view_dashboard',
    'view_notifications',
    'view_allocations',
    'view_announcements'
);

-- Department Offices - Limited to their department functions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE permission_name IN (
    'view_users',
    'view_departments',
    'view_budget',
    'create_ppmp', 'edit_ppmp', 'view_ppmp',
    'view_reports',
    'view_dashboard',
    'view_notifications'
);

-- Create default budget/finance office user (password: budget123) - System Administrator
INSERT INTO users (email, password_hash, first_name, last_name, employee_id, department_id, role_id, created_by) VALUES
('budget@evsu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budget', 'Finance Office', 'BUDGET001', 1, 1, 1);

-- Create school administrator user (password: school123) - View-only access
INSERT INTO users (email, password_hash, first_name, last_name, employee_id, department_id, role_id, created_by) VALUES
('school@evsu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'School', 'Administrator', 'SCHOOL001', 1, 2, 1);

-- Create sample department office user (password: office123)
INSERT INTO users (email, password_hash, first_name, last_name, employee_id, department_id, role_id, created_by) VALUES
('office@evsu.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 'Office', 'OFFICE001', 2, 3, 1);

-- File Submissions Table
CREATE TABLE file_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department_id INT,
    submission_type ENUM('PPMP', 'LIB') NOT NULL,
    fiscal_year YEAR NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    comments TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- User Activity Log Table
CREATE TABLE user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'logout', 'password_change', 'profile_update') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    activity_details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_activity (user_id, created_at),
    INDEX idx_activity_type (activity_type, created_at)
);

