USE startup_bms;

CREATE TABLE IF NOT EXISTS organizations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(140) NOT NULL,
  status ENUM('Active','Suspended') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT IGNORE INTO organizations (id,name,status) VALUES (1,'Northstar Studio','Active');

ALTER TABLE users MODIFY role ENUM('Administrator','Client','Manager','Staff','Viewer') NOT NULL DEFAULT 'Client';
ALTER TABLE users ADD COLUMN IF NOT EXISTS organization_id INT NULL AFTER id;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL AFTER email;
ALTER TABLE users ADD COLUMN IF NOT EXISTS permissions JSON NULL AFTER status;
UPDATE users SET organization_id=1 WHERE organization_id IS NULL AND role!='Administrator';

ALTER TABLE transactions ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS organization_id INT NULL AFTER id;
ALTER TABLE rewards ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE support_tickets ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id;

-- Create the first administrator securely with setup.php after importing this migration.
