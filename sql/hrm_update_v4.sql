-- HRM Update v4: เพิ่ม team_id ใน sales_records
USE `hrm_db`;

ALTER TABLE `sales_records`
  ADD COLUMN IF NOT EXISTS `team_id` INT UNSIGNED NULL COMMENT 'ทีม (กรณี sales_type = team)' AFTER `user_id`,
  ADD CONSTRAINT `fk_sr_team` FOREIGN KEY IF NOT EXISTS (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL;
