-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2025 at 03:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `budgettrack_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_allocations`
--

CREATE TABLE `budget_allocations` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `fiscal_year` year(4) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `utilized_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(15,2) GENERATED ALWAYS AS (`allocated_amount` - `utilized_amount`) STORED,
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_categories`
--

CREATE TABLE `budget_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_categories`
--

INSERT INTO `budget_categories` (`id`, `category_name`, `category_code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Personnel Services', 'PS', 'Salaries, wages, and benefits for employees', 1, '2025-09-21 18:39:29', '2025-09-21 18:39:29'),
(2, 'Maintenance and Other Operating Expenses', 'MOOE', 'Office supplies, utilities, maintenance, and operational expenses', 1, '2025-09-21 18:39:29', '2025-09-21 18:39:29'),
(3, 'Capital Outlay', 'CO', 'Equipment, furniture, and infrastructure investments', 1, '2025-09-21 18:39:29', '2025-09-21 18:39:29'),
(4, 'Special Purpose Fund', 'SPF', 'Special projects and programs', 1, '2025-09-21 18:39:29', '2025-09-21 18:39:29'),
(5, 'Research and Development', 'R&D', 'Research activities and development projects', 1, '2025-09-21 18:39:29', '2025-09-21 18:39:29');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `dept_code` varchar(20) NOT NULL,
  `dept_description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `dept_code`, `dept_description`, `is_active`, `created_at`, `updated_at`) VALUES
(13, 'Computer Studies', 'CS', 'Computer Studies Department', 1, '2025-09-28 19:14:10', '2025-09-28 19:14:10');

-- --------------------------------------------------------

--
-- Table structure for table `department_budgets`
--

CREATE TABLE `department_budgets` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `fiscal_year` year(4) NOT NULL,
  `total_allocated` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_utilized` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_remaining` decimal(15,2) GENERATED ALWAYS AS (`total_allocated` - `total_utilized`) STORED,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `file_submissions`
--

CREATE TABLE `file_submissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `submission_type` enum('PPMP','LIB') NOT NULL,
  `fiscal_year` year(4) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `review_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `permission_description`, `module`, `created_at`) VALUES
(1, 'create_users', 'Create new user accounts', 'user_management', '2025-09-21 04:41:14'),
(2, 'edit_users', 'Edit existing user accounts', 'user_management', '2025-09-21 04:41:14'),
(3, 'delete_users', 'Delete user accounts', 'user_management', '2025-09-21 04:41:14'),
(4, 'view_users', 'View user accounts', 'user_management', '2025-09-21 04:41:14'),
(5, 'assign_roles', 'Assign roles to users', 'user_management', '2025-09-21 04:41:14'),
(6, 'create_roles', 'Create new roles', 'role_management', '2025-09-21 04:41:14'),
(7, 'edit_roles', 'Edit existing roles', 'role_management', '2025-09-21 04:41:14'),
(8, 'delete_roles', 'Delete roles', 'role_management', '2025-09-21 04:41:14'),
(9, 'view_roles', 'View roles', 'role_management', '2025-09-21 04:41:14'),
(10, 'manage_permissions', 'Manage role permissions', 'role_management', '2025-09-21 04:41:14'),
(11, 'create_departments', 'Create new departments', 'department_management', '2025-09-21 04:41:14'),
(12, 'edit_departments', 'Edit existing departments', 'department_management', '2025-09-21 04:41:14'),
(13, 'delete_departments', 'Delete departments', 'department_management', '2025-09-21 04:41:14'),
(14, 'view_departments', 'View departments', 'department_management', '2025-09-21 04:41:14'),
(15, 'create_budget', 'Create budget allocations', 'budget_management', '2025-09-21 04:41:14'),
(16, 'edit_budget', 'Edit budget allocations', 'budget_management', '2025-09-21 04:41:14'),
(17, 'view_budget', 'View budget information', 'budget_management', '2025-09-21 04:41:14'),
(18, 'approve_budget', 'Approve budget requests', 'budget_management', '2025-09-21 04:41:14'),
(19, 'create_ppmp', 'Create PPMP submissions', 'ppmp_management', '2025-09-21 04:41:14'),
(20, 'edit_ppmp', 'Edit PPMP submissions', 'ppmp_management', '2025-09-21 04:41:14'),
(21, 'view_ppmp', 'View PPMP submissions', 'ppmp_management', '2025-09-21 04:41:14'),
(22, 'approve_ppmp', 'Approve PPMP submissions', 'ppmp_management', '2025-09-21 04:41:14'),
(23, 'view_reports', 'View system reports', 'reports', '2025-09-21 04:41:14'),
(24, 'generate_reports', 'Generate custom reports', 'reports', '2025-09-21 04:41:14'),
(25, 'export_reports', 'Export reports', 'reports', '2025-09-21 04:41:14'),
(26, 'view_dashboard', 'View dashboard', 'dashboard', '2025-09-21 04:41:14'),
(27, 'view_admin_dashboard', 'View admin dashboard', 'dashboard', '2025-09-21 04:41:14'),
(28, 'view_notifications', 'View notifications', 'notifications', '2025-09-21 04:41:14'),
(29, 'send_notifications', 'Send notifications', 'notifications', '2025-09-21 04:41:14'),
(30, 'control_admin', 'Control admin role permissions and access', 'system_control', '2025-09-21 04:41:14'),
(31, 'manage_all_roles', 'Manage all roles including admin', 'system_control', '2025-09-21 04:41:14'),
(32, 'system_override', 'Override any system restrictions', 'system_control', '2025-09-21 04:41:14');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `role_description`, `created_at`, `updated_at`) VALUES
(1, 'budget', 'Budget/Finance Office - System Administrator with full control over everything', '2025-09-21 07:35:15', '2025-09-21 07:35:15'),
(2, 'school_admin', 'School Administrator - View-only access to monitor system activities', '2025-09-21 07:35:15', '2025-09-21 07:35:15'),
(3, 'offices', 'Department Offices - Manages department budget and submits PPMP', '2025-09-21 07:35:15', '2025-09-21 07:35:15');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(227, 1, 18, '2025-09-21 07:35:15'),
(228, 1, 22, '2025-09-21 07:35:15'),
(229, 1, 5, '2025-09-21 07:35:15'),
(230, 1, 30, '2025-09-21 07:35:15'),
(231, 1, 15, '2025-09-21 07:35:15'),
(232, 1, 11, '2025-09-21 07:35:15'),
(233, 1, 19, '2025-09-21 07:35:15'),
(234, 1, 6, '2025-09-21 07:35:15'),
(235, 1, 1, '2025-09-21 07:35:15'),
(236, 1, 13, '2025-09-21 07:35:15'),
(237, 1, 8, '2025-09-21 07:35:15'),
(238, 1, 3, '2025-09-21 07:35:15'),
(239, 1, 16, '2025-09-21 07:35:15'),
(240, 1, 12, '2025-09-21 07:35:15'),
(241, 1, 20, '2025-09-21 07:35:15'),
(242, 1, 7, '2025-09-21 07:35:15'),
(243, 1, 2, '2025-09-21 07:35:15'),
(244, 1, 25, '2025-09-21 07:35:15'),
(245, 1, 24, '2025-09-21 07:35:15'),
(246, 1, 31, '2025-09-21 07:35:15'),
(247, 1, 10, '2025-09-21 07:35:15'),
(248, 1, 29, '2025-09-21 07:35:15'),
(249, 1, 32, '2025-09-21 07:35:15'),
(250, 1, 27, '2025-09-21 07:35:15'),
(251, 1, 17, '2025-09-21 07:35:15'),
(252, 1, 26, '2025-09-21 07:35:15'),
(253, 1, 14, '2025-09-21 07:35:15'),
(254, 1, 28, '2025-09-21 07:35:15'),
(255, 1, 21, '2025-09-21 07:35:15'),
(256, 1, 23, '2025-09-21 07:35:15'),
(257, 1, 9, '2025-09-21 07:35:15'),
(258, 1, 4, '2025-09-21 07:35:15'),
(290, 2, 17, '2025-09-21 07:35:15'),
(291, 2, 26, '2025-09-21 07:35:15'),
(292, 2, 14, '2025-09-21 07:35:15'),
(293, 2, 28, '2025-09-21 07:35:15'),
(294, 2, 21, '2025-09-21 07:35:15'),
(295, 2, 23, '2025-09-21 07:35:15'),
(296, 2, 4, '2025-09-21 07:35:15'),
(297, 3, 19, '2025-09-21 07:35:15'),
(298, 3, 20, '2025-09-21 07:35:15'),
(299, 3, 17, '2025-09-21 07:35:15'),
(300, 3, 26, '2025-09-21 07:35:15'),
(301, 3, 14, '2025-09-21 07:35:15'),
(302, 3, 28, '2025-09-21 07:35:15'),
(303, 3, 21, '2025-09-21 07:35:15'),
(304, 3, 23, '2025-09-21 07:35:15'),
(305, 3, 4, '2025-09-21 07:35:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `first_name`, `last_name`, `middle_name`, `employee_id`, `department_id`, `role_id`, `is_active`, `last_login`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 'budget@evsu.edu.ph', '$2y$10$eRpG2g0Gs5IfknmV/c4fDeOn0bAEE8QS0kc26TmRWWtaAZ3zL7Kem', 'Budget', 'Finance Office', NULL, 'BUDGET001', NULL, 1, 1, '2025-09-28 19:21:45', NULL, '2025-09-21 07:35:15', '2025-09-28 19:21:45'),
(6, 'school@evsu.edu.ph', '$2y$10$7Luu7x783EwZ/jkwGxPs2O8XUokckK2VUyBq2/5jlsPZgdrkJ.LFu', 'School', 'Administrator', NULL, 'SCHOOL001', NULL, 2, 1, '2025-09-21 18:01:52', 5, '2025-09-21 07:35:15', '2025-09-21 18:01:52'),
(7, 'office@evsu.edu.ph', '$2y$10$G6csVN4pyTyrP9271Jr7HulApNSQNlVz8Dw3YFgQ8bV9KZ/mavrZC', 'Department', 'Office', '', 'OFFICE001', NULL, 3, 1, '2025-09-21 11:44:54', 5, '2025-09-21 07:35:16', '2025-09-28 18:26:52'),
(11, 'nino.boholst@evsu.edu.ph', '$2y$10$uvzo6tX33lKK.JLYYaLMIOZCk4BVSybNA7kCCErRSHzngkhN0athq', 'Niño', 'Boholst', 'Matuguina', '2022-31138', NULL, 3, 1, '2025-09-28 19:21:34', 5, '2025-09-23 11:10:51', '2025-09-28 19:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dept_category_year` (`department_id`,`category_id`,`fiscal_year`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `budget_categories`
--
ALTER TABLE `budget_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_code` (`category_code`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dept_code` (`dept_code`);

--
-- Indexes for table `department_budgets`
--
ALTER TABLE `department_budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dept_year` (`department_id`,`fiscal_year`);

--
-- Indexes for table `file_submissions`
--
ALTER TABLE `file_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_file_submissions_user` (`user_id`),
  ADD KEY `idx_file_submissions_dept` (`department_id`),
  ADD KEY `idx_file_submissions_type` (`submission_type`),
  ADD KEY `idx_file_submissions_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `budget_categories`
--
ALTER TABLE `budget_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `department_budgets`
--
ALTER TABLE `department_budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `file_submissions`
--
ALTER TABLE `file_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=315;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `budget_allocations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_allocations_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `budget_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_allocations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `department_budgets`
--
ALTER TABLE `department_budgets`
  ADD CONSTRAINT `department_budgets_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_submissions`
--
ALTER TABLE `file_submissions`
  ADD CONSTRAINT `file_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_submissions_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_submissions_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
