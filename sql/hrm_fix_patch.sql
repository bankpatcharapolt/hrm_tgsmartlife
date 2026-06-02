-- ============================================================
-- hrm_fix_patch.sql
-- รัน: mysql -u root -p hrm_db < hrm_fix_patch.sql
-- ============================================================

-- [ข้อ 7] เพิ่มคอลัมน์ work_hours ใน attendance (สำหรับ half_day + hourly)
ALTER TABLE `attendance`
  ADD COLUMN IF NOT EXISTS `work_hours` DECIMAL(4,2) DEFAULT NULL
    COMMENT 'ชั่วโมงทำงาน (ใช้สำหรับ half_day / hourly status)'
  AFTER `ot_hours`,
  ADD COLUMN IF NOT EXISTS `leave_hours` DECIMAL(4,2) DEFAULT 0
    COMMENT 'ชั่วโมงลา (ใช้เมื่อ status=leave unit=hour)'
  AFTER `work_hours`,
  ADD COLUMN IF NOT EXISTS `leave_type_id` INT UNSIGNED DEFAULT NULL
    COMMENT 'ประเภทการลา (FK leave_types)'
  AFTER `leave_hours`;

-- [ข้อ 1] ตาราง salary_slips เพิ่ม updated_at (ถ้ายังไม่มี)
ALTER TABLE `salary_slips`
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME DEFAULT NULL
  AFTER `created_at`;

-- [ข้อ 1] ตาราง tax_documents เพิ่ม updated_at (ถ้ายังไม่มี)
ALTER TABLE `tax_documents`
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME DEFAULT NULL
  AFTER `created_at`;

-- ตรวจสอบ ADD COLUMN IF NOT EXISTS (MySQL 8.0+)
-- สำหรับ MySQL 5.7 ถ้า error ให้รันแบบนี้แทน:
-- ALTER TABLE attendance ADD COLUMN work_hours DECIMAL(4,2) DEFAULT NULL;
-- ALTER TABLE attendance ADD COLUMN leave_hours DECIMAL(4,2) DEFAULT 0;
-- ALTER TABLE attendance ADD COLUMN leave_type_id INT UNSIGNED DEFAULT NULL;
