-- Add approval fields to companies table (ignore errors if already exist)
ALTER TABLE `companies`
  ADD COLUMN `is_approved` TINYINT(1) DEFAULT 0,
  ADD COLUMN `approval_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  ADD COLUMN `approval_date` TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN `rejection_reason` TEXT DEFAULT NULL,
  ADD COLUMN `admin_id` INT DEFAULT NULL,
  ADD CONSTRAINT `fk_companies_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`);

-- Create admin_actions table to log admin activities (if not exists)
CREATE TABLE IF NOT EXISTS `admin_actions` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL,
  `action_type` ENUM('company_approval', 'company_rejection', 'other') NOT NULL,
  `target_id` INT NOT NULL,
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`)
);

-- Indexes for faster queries (ignore errors if already exist)
CREATE INDEX `idx_company_approval_status` ON `companies`(`approval_status`);
CREATE INDEX `idx_admin_actions_type` ON `admin_actions`(`action_type`);

-- Create default admin user (email: admin@externit.com, password: admin1234)
DELETE FROM users WHERE email = 'admin@externit.com' AND user_type = 'admin';

INSERT INTO users (email, password, user_type)
VALUES (
  'admin@externit.com',
  '$2y$10$wH8QwQwQwQwQwQwQwQwQOeQwQwQwQwQwQwQwQwQwQwQwQwQW',
  'admin'
);