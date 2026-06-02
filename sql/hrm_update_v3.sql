-- HRM Update v3: เพิ่ม fields ตาม Excel Template
USE `hrm_db`;

-- เพิ่ม fields ใหม่ใน users
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `title`            VARCHAR(20) NULL COMMENT 'คำนำหน้า (นาย/นาง/นางสาว)' AFTER `employee_id`,
  ADD COLUMN IF NOT EXISTS `first_name_en`    VARCHAR(100) NULL COMMENT 'ชื่อภาษาอังกฤษ' AFTER `last_name`,
  ADD COLUMN IF NOT EXISTS `last_name_en`     VARCHAR(100) NULL COMMENT 'นามสกุลภาษาอังกฤษ' AFTER `first_name_en`,
  ADD COLUMN IF NOT EXISTS `position`         VARCHAR(100) NULL COMMENT 'ตำแหน่งงาน' AFTER `department_id`,
  ADD COLUMN IF NOT EXISTS `employee_type`    ENUM('รายเดือน','รายวัน','พาร์ทไทม์','สัญญาจ้าง') NOT NULL DEFAULT 'รายเดือน' COMMENT 'ประเภทพนักงาน' AFTER `position`,
  ADD COLUMN IF NOT EXISTS `team_id`          INT UNSIGNED NULL COMMENT 'ทีม/สาขา' AFTER `department_id`,
  ADD COLUMN IF NOT EXISTS `emergency_contact`       VARCHAR(150) NULL COMMENT 'ผู้ติดต่อฉุกเฉิน' AFTER `address`,
  ADD COLUMN IF NOT EXISTS `emergency_phone`         VARCHAR(20) NULL COMMENT 'เบอร์ติดต่อฉุกเฉิน' AFTER `emergency_contact`,
  ADD COLUMN IF NOT EXISTS `sub_district`     VARCHAR(100) NULL COMMENT 'แขวง/ตำบล' AFTER `address`,
  ADD COLUMN IF NOT EXISTS `district`         VARCHAR(100) NULL COMMENT 'เขต/อำเภอ' AFTER `sub_district`,
  ADD COLUMN IF NOT EXISTS `province`         VARCHAR(100) NULL COMMENT 'จังหวัด' AFTER `district`,
  ADD COLUMN IF NOT EXISTS `postal_code`      VARCHAR(5) NULL COMMENT 'รหัสไปรษณีย์' AFTER `province`,
  ADD COLUMN IF NOT EXISTS `salary_account`   VARCHAR(200) NULL COMMENT 'บัญชีเงินเดือนที่บันทึก' AFTER `base_salary`,
  ADD COLUMN IF NOT EXISTS `social_security_status` ENUM('ขึ้นทะเบียนประกันสังคม','ไม่ขึ้นทะเบียนประกันสังคม') NOT NULL DEFAULT 'ขึ้นทะเบียนประกันสังคม' AFTER `social_security_id`,
  ADD COLUMN IF NOT EXISTS `withholding_tax`  DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'ยอดหัก ณ ที่จ่าย ภ.ง.ด.1' AFTER `social_security_status`,
  ADD COLUMN IF NOT EXISTS `payment_channel`  VARCHAR(100) NULL COMMENT 'ช่องทางรับเงิน เช่น ธ.กสิกรไทย ออมทรัพย์' AFTER `withholding_tax`,
  ADD COLUMN IF NOT EXISTS `bank_account`     VARCHAR(20) NULL COMMENT 'เลขที่บัญชี' AFTER `payment_channel`;

-- ตาราง ทีม/สาขา
CREATE TABLE IF NOT EXISTS `teams` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `team_code`       VARCHAR(20) NOT NULL,
  `team_name`       VARCHAR(150) NOT NULL,
  `manager_emp_id`  VARCHAR(20) NULL COMMENT 'รหัสพนักงานหัวหน้าทีม',
  `location`        VARCHAR(100) NULL COMMENT 'พื้นที่/จังหวัด',
  `monthly_target`  DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'เป้ายอดขายต่อเดือน',
  `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  UNIQUE KEY `uq_team_code`(`team_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- อัปเดต leave_types ให้มี fields ครบ
ALTER TABLE `leave_types`
  ADD COLUMN IF NOT EXISTS `leave_code`       VARCHAR(10) NULL COMMENT 'รหัสการลา SL/PL/AL/ML/UL' AFTER `id`,
  ADD COLUMN IF NOT EXISTS `is_deduct_salary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'หักเงินเดือนไหม' AFTER `is_paid`,
  ADD COLUMN IF NOT EXISTS `can_leave_by_hour` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'ลาชั่วโมงได้ไหม' AFTER `requires_doc`,
  ADD COLUMN IF NOT EXISTS `is_carry_forward` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'ทบวันลาปีถัดไป' AFTER `can_leave_by_hour`,
  ADD COLUMN IF NOT EXISTS `require_doc_days` INT NOT NULL DEFAULT 0 COMMENT 'ลาติดกี่วันต้องแนบเอกสาร' AFTER `requires_doc`,
  ADD COLUMN IF NOT EXISTS `description`      TEXT NULL COMMENT 'หมายเหตุ/เงื่อนไข' AFTER `is_carry_forward`;

-- FK team
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_team` FOREIGN KEY IF NOT EXISTS (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL;

-- เพิ่ม team สำนักงานใหญ่ (default สำหรับพนักงานที่ไม่มีทีม)
INSERT INTO `teams` (`team_code`,`team_name`,`location`,`is_active`,`created_at`,`updated_at`)
VALUES ('HQ','สำนักงานใหญ่','สำนักงานใหญ่',1,NOW(),NOW())
ON DUPLICATE KEY UPDATE `team_name`=`team_name`;

-- เพิ่ม team_id ใน sales_records
ALTER TABLE `sales_records`
  ADD COLUMN IF NOT EXISTS `team_id` INT UNSIGNED NULL COMMENT 'ทีม (ถ้าเป็นยอดขายทีม)' AFTER `department_id`,
  ADD CONSTRAINT `fk_sales_team` FOREIGN KEY IF NOT EXISTS (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL;
