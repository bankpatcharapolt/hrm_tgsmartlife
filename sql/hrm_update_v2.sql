-- HRM Update v2: Shifts + Leave Hours + Attendance Edit
-- รัน SQL นี้เพิ่มเติมจาก hrm_full.sql

USE `hrm_db`;

-- ตาราง กะการทำงาน
CREATE TABLE IF NOT EXISTS `shifts` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'ชื่อกะ เช่น กะเช้า กะบ่าย กะดึก',
  `start_time` TIME NOT NULL COMMENT 'เวลาเริ่มกะ',
  `end_time` TIME NOT NULL COMMENT 'เวลาสิ้นสุดกะ',
  `break_minutes` INT NOT NULL DEFAULT 60 COMMENT 'พักกลางวัน (นาที)',
  `late_threshold_minutes` INT NOT NULL DEFAULT 15 COMMENT 'นาทีที่ถือว่าสาย',
  `ot_starts_after_minutes` INT NOT NULL DEFAULT 0 COMMENT 'OT เริ่มหลังออกงานกี่นาที',
  `is_night_shift` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'กะข้ามวัน',
  `color` VARCHAR(7) NOT NULL DEFAULT '#1a56db' COMMENT 'สีแสดงผล',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เพิ่ม shift_id ใน users
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `shift_id` INT UNSIGNED NULL COMMENT 'กะประจำ' AFTER `role_id`,
  ADD CONSTRAINT `fk_user_shift` FOREIGN KEY IF NOT EXISTS (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE SET NULL;

-- เพิ่ม shift_id ใน attendance + leave hours support
ALTER TABLE `attendance`
  ADD COLUMN IF NOT EXISTS `shift_id` INT UNSIGNED NULL COMMENT 'กะที่ทำงาน' AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `leave_hours` DECIMAL(4,2) NOT NULL DEFAULT 0 COMMENT 'ลาชั่วโมง (0=ลาเต็มวัน)' AFTER `ot_hours`,
  ADD COLUMN IF NOT EXISTS `leave_type_id` INT UNSIGNED NULL COMMENT 'ประเภทลา (ถ้าสถานะ=leave)' AFTER `leave_hours`,
  ADD COLUMN IF NOT EXISTS `modified_by` INT UNSIGNED NULL COMMENT 'แก้ไขโดย admin' AFTER `note`,
  ADD COLUMN IF NOT EXISTS `modified_at` DATETIME NULL COMMENT 'เวลาแก้ไข' AFTER `modified_by`;

-- เพิ่ม leave hours ใน leave_requests
ALTER TABLE `leave_requests`
  ADD COLUMN IF NOT EXISTS `leave_unit` ENUM('day','hour') NOT NULL DEFAULT 'day' COMMENT 'หน่วยการลา' AFTER `total_days`,
  ADD COLUMN IF NOT EXISTS `total_hours` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'จำนวนชั่วโมงลา' AFTER `leave_unit`,
  ADD COLUMN IF NOT EXISTS `start_time` TIME NULL COMMENT 'เวลาเริ่มลา (กรณีลาชั่วโมง)' AFTER `total_hours`,
  ADD COLUMN IF NOT EXISTS `end_time` TIME NULL COMMENT 'เวลาสิ้นสุดลา' AFTER `start_time`;

-- ข้อมูลตัวอย่างกะ
INSERT INTO `shifts` (`name`,`start_time`,`end_time`,`break_minutes`,`late_threshold_minutes`,`color`) VALUES
('กะเช้า', '08:00:00', '17:00:00', 60, 15, '#1a56db'),
('กะบ่าย', '14:00:00', '23:00:00', 60, 15, '#d97706'),
('กะดึก', '22:00:00', '07:00:00', 60, 15, '#7c3aed'),
('กะปกติ', '08:30:00', '17:30:00', 60, 15, '#16a34a')
ON DUPLICATE KEY UPDATE `name`=`name`;

-- เพิ่ม team_id ใน sales_records
ALTER TABLE `sales_records`
  ADD COLUMN IF NOT EXISTS `team_id` INT UNSIGNED NULL COMMENT 'ทีม (กรณี sales_type=team)' AFTER `department_id`;
