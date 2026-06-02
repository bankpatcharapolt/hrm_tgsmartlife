-- ระบบ HRM - Full Database Schema
-- Import: mysql -u root -p < hrm_full.sql
-- Default login: username=owner / password=Admin@1234

SET NAMES utf8mb4; SET FOREIGN_KEY_CHECKS=0;
CREATE DATABASE IF NOT EXISTS `hrm_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hrm_db`;

CREATE TABLE `departments` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `can_checkin` TINYINT(1) DEFAULT 0,
  `can_view_own_salary` TINYINT(1) DEFAULT 0,
  `can_approve_leave` TINYINT(1) DEFAULT 0,
  `can_manage_employees` TINYINT(1) DEFAULT 0,
  `can_view_sales` TINYINT(1) DEFAULT 0,
  `can_send_notifications` TINYINT(1) DEFAULT 0,
  `can_manage_salary` TINYINT(1) DEFAULT 0,
  `can_upload_documents` TINYINT(1) DEFAULT 0,
  `can_view_reports` TINYINT(1) DEFAULT 0,
  `can_monitor_attendance` TINYINT(1) DEFAULT 0,
  `is_full_access` TINYINT(1) DEFAULT 0,
  `work_start_time` TIME DEFAULT '08:30:00',
  `work_end_time` TIME DEFAULT '17:30:00',
  `leave_quota_sick` INT DEFAULT 30,
  `leave_quota_personal` INT DEFAULT 3,
  `leave_quota_vacation` INT DEFAULT 6,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`), UNIQUE KEY`uq_slug`(`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `employee_id` VARCHAR(20) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `department_id` INT UNSIGNED,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `nickname` VARCHAR(50),
  `gender` ENUM('male','female','other') DEFAULT 'male',
  `date_of_birth` DATE,
  `id_card_number` VARCHAR(13),
  `phone` VARCHAR(20),
  `email` VARCHAR(150),
  `address` TEXT,
  `photo` VARCHAR(255),
  `start_date` DATE NOT NULL,
  `end_date` DATE,
  `base_salary` DECIMAL(12,2) DEFAULT 0.00,
  `tax_id` VARCHAR(13),
  `social_security_id` VARCHAR(20),
  `status` ENUM('active','inactive','suspended') DEFAULT 'active',
  `last_login` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`), UNIQUE KEY`uq_eid`(`employee_id`), UNIQUE KEY`uq_un`(`username`),
  CONSTRAINT `fk_ur` FOREIGN KEY(`role_id`) REFERENCES `roles`(`id`),
  CONSTRAINT `fk_ud` FOREIGN KEY(`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `check_in_time` DATETIME,
  `check_out_time` DATETIME,
  `is_late` TINYINT(1) DEFAULT 0,
  `late_minutes` INT DEFAULT 0,
  `ot_hours` DECIMAL(4,2) DEFAULT 0,
  `status` ENUM('present','absent','leave','holiday','half_day') DEFAULT 'present',
  `note` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`), UNIQUE KEY`uq_att`(`user_id`,`date`),
  CONSTRAINT `fk_au` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_types` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `quota_per_year` INT DEFAULT 0,
  `is_paid` TINYINT(1) DEFAULT 1,
  `requires_doc` TINYINT(1) DEFAULT 0,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `leave_type_id` INT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_days` DECIMAL(4,1) DEFAULT 1,
  `reason` TEXT NOT NULL,
  `document_path` VARCHAR(255),
  `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` INT UNSIGNED,
  `approved_at` DATETIME,
  `approver_note` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  CONSTRAINT `fk_lru` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`),
  CONSTRAINT `fk_lrt` FOREIGN KEY(`leave_type_id`) REFERENCES `leave_types`(`id`),
  CONSTRAINT `fk_lra` FOREIGN KEY(`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salary_records` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `salary_year` YEAR NOT NULL,
  `salary_month` TINYINT NOT NULL,
  `base_salary` DECIMAL(12,2) DEFAULT 0,
  `commission` DECIMAL(12,2) DEFAULT 0,
  `ot_pay` DECIMAL(12,2) DEFAULT 0,
  `monthly_bonus` DECIMAL(12,2) DEFAULT 0,
  `special_bonus` DECIMAL(12,2) DEFAULT 0,
  `other_income` DECIMAL(12,2) DEFAULT 0,
  `social_security_deduct` DECIMAL(12,2) DEFAULT 0,
  `tax_deduct` DECIMAL(12,2) DEFAULT 0,
  `other_deduct` DECIMAL(12,2) DEFAULT 0,
  `absent_deduct` DECIMAL(12,2) DEFAULT 0,
  `late_deduct` DECIMAL(12,2) DEFAULT 0,
  `gross_salary` DECIMAL(12,2) DEFAULT 0,
  `net_salary` DECIMAL(12,2) DEFAULT 0,
  `payment_status` ENUM('draft','processed','paid') DEFAULT 'draft',
  `payment_date` DATE,
  `note` TEXT,
  `created_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`), UNIQUE KEY`uq_sal`(`user_id`,`salary_year`,`salary_month`),
  CONSTRAINT `fk_salu` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `annual_bonuses` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `bonus_year` YEAR NOT NULL,
  `amount` DECIMAL(12,2) DEFAULT 0,
  `remarks` TEXT,
  `payment_date` DATE,
  `created_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  CONSTRAINT `fk_abu` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salary_slips` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `slip_year` YEAR NOT NULL,
  `slip_month` TINYINT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT,
  `uploaded_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  CONSTRAINT `fk_ssu` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tax_documents` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `tax_year` YEAR NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT,
  `uploaded_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`), UNIQUE KEY`uq_tax`(`user_id`,`tax_year`),
  CONSTRAINT `fk_tdu` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sales_records` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED,
  `department_id` INT UNSIGNED,
  `record_year` YEAR NOT NULL,
  `record_month` TINYINT NOT NULL,
  `sales_type` ENUM('individual','team') DEFAULT 'individual',
  `target_amount` DECIMAL(15,2) DEFAULT 0,
  `actual_amount` DECIMAL(15,2) DEFAULT 0,
  `achievement_pct` DECIMAL(6,2) DEFAULT 0,
  `note` TEXT,
  `created_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  CONSTRAINT `fk_sru` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `sender_id` INT UNSIGNED,
  `type` ENUM('leave_request','leave_approved','leave_rejected','salary_paid','bonus_paid','document_uploaded','late_checkin','general','meeting','holiday','target') DEFAULT 'general',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255),
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`), KEY`idx_nu`(`user_id`),
  CONSTRAINT `fk_nu` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `holidays` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `holiday_date` DATE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `type` ENUM('national','company') DEFAULT 'national',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`), UNIQUE KEY`uq_hd`(`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT,
  `user_id` INT UNSIGNED,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  CONSTRAINT `fk_alu` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======== Seed Data ========
INSERT INTO `departments`(`name`,`description`) VALUES
('ฝ่ายบุคคล','จัดการงานบุคคลและทรัพยากรมนุษย์'),
('ฝ่ายขาย','ดูแลงานขายและลูกค้าสัมพันธ์'),
('ฝ่ายการตลาด','วางแผนและดำเนินกิจกรรมการตลาด'),
('ฝ่ายบัญชี','จัดการงานบัญชีและการเงิน'),
('ฝ่ายปฏิบัติการ','ดูแลงานปฏิบัติการ');

INSERT INTO `roles`(`name`,`slug`,`can_checkin`,`can_view_own_salary`,`can_approve_leave`,`can_manage_employees`,`can_view_sales`,`can_send_notifications`,`can_manage_salary`,`can_upload_documents`,`can_view_reports`,`can_monitor_attendance`,`is_full_access`) VALUES
('พนักงาน','employee',1,1,0,0,0,0,0,0,0,0,0),
('หัวหน้างาน','manager',1,1,1,0,0,0,0,0,0,1,0),
('แอดมิน','admin',1,1,1,1,1,1,1,1,1,1,0),
('เจ้าของ','owner',1,1,1,1,1,1,1,1,1,1,1);

INSERT INTO `leave_types`(`name`,`quota_per_year`,`is_paid`,`requires_doc`) VALUES
('ลาป่วย',30,1,0),('ลากิจ',3,1,0),('ลาพักร้อน',6,1,0),
('ลาคลอด',98,1,1),('ลาบวช',15,1,1),('ลาไม่รับค่าจ้าง',0,0,0);

INSERT INTO `holidays`(`holiday_date`,`name`) VALUES
('2025-01-01','วันขึ้นปีใหม่'),('2025-04-13','วันสงกรานต์'),('2025-04-14','วันสงกรานต์'),
('2025-04-15','วันสงกรานต์'),('2025-05-01','วันแรงงาน'),('2025-07-28','วันเฉลิมพระชนมพรรษา ร.10'),
('2025-08-12','วันแม่แห่งชาติ'),('2025-10-23','วันปิยมหาราช'),
('2025-12-05','วันพ่อแห่งชาติ'),('2025-12-31','วันสิ้นปี');

-- password = Admin@1234
INSERT INTO `users`(`employee_id`,`username`,`password`,`role_id`,`first_name`,`last_name`,`phone`,`start_date`,`status`)
VALUES('OWN001','owner','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',4,'เจ้าของ','ระบบ','099-999-9999',CURDATE(),'active');

-- Sample employees (password = Test@1234)
INSERT INTO `users`(`employee_id`,`username`,`password`,`role_id`,`department_id`,`first_name`,`last_name`,`nickname`,`phone`,`start_date`,`base_salary`,`status`) VALUES
('ADM001','admin1','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',3,1,'สมชาย','ใจดี','ชาย','081-111-1111','2023-01-01',35000,'active'),
('MGR001','manager1','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',2,2,'สมหญิง','รักงาน','หญิง','082-222-2222','2022-06-01',28000,'active'),
('EMP001','emp001','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,2,'วิชัย','มั่นคง','วิ','083-333-3333','2023-03-15',18000,'active'),
('EMP002','emp002','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,3,'นภา','สวยงาม','ภา','084-444-4444','2023-07-01',20000,'active'),
('EMP003','emp003','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,4,'ธนา','เก่งกาจ','นา','085-555-5555','2024-01-10',22000,'active');

SET FOREIGN_KEY_CHECKS=1;
