USE startup_bms;

CREATE TABLE IF NOT EXISTS business_plans (
  id INT PRIMARY KEY AUTO_INCREMENT, organization_id INT NOT NULL,
  section VARCHAR(100) NOT NULL, title VARCHAR(160) NOT NULL, details TEXT NOT NULL,
  status ENUM('Draft','In progress','Complete') NOT NULL DEFAULT 'Draft',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS goals (
  id INT PRIMARY KEY AUTO_INCREMENT, organization_id INT NOT NULL,
  title VARCHAR(160) NOT NULL, objective TEXT NOT NULL, target_date DATE NOT NULL,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('Not started','In progress','Complete') NOT NULL DEFAULT 'Not started',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE business_profile ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE business_profile ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL AFTER email;
ALTER TABLE business_profile ADD COLUMN IF NOT EXISTS address VARCHAR(220) NULL AFTER phone;

INSERT INTO business_plans (organization_id,section,title,details,status)
SELECT 1,'Executive summary','Business overview','Describe the problem, your solution, and the opportunity.','Draft'
WHERE NOT EXISTS (SELECT 1 FROM business_plans WHERE organization_id=1);
INSERT INTO business_plans (organization_id,section,title,details,status)
SELECT 1,'Market','Target customers','Define who you serve and why they choose you.','Draft'
WHERE (SELECT COUNT(*) FROM business_plans WHERE organization_id=1)<2;
INSERT INTO goals (organization_id,title,objective,target_date,progress,status)
SELECT 1,'Complete business launch','Finish the setup, publish the first offer, and accept the first customer payment.',DATE_ADD(CURDATE(),INTERVAL 30 DAY),35,'In progress'
WHERE NOT EXISTS (SELECT 1 FROM goals WHERE organization_id=1);
