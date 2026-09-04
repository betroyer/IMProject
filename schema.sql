CREATE DATABASE IF NOT EXISTS startup_bms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE startup_bms;

CREATE TABLE IF NOT EXISTS business_profile (
  id INT PRIMARY KEY AUTO_INCREMENT, business_name VARCHAR(120) NOT NULL,
  owner_name VARCHAR(120) NOT NULL, industry VARCHAR(100) NOT NULL,
  email VARCHAR(160) NOT NULL, setup_progress TINYINT UNSIGNED NOT NULL DEFAULT 20,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(120) NOT NULL, email VARCHAR(160) UNIQUE NOT NULL,
  role ENUM('Administrator','Manager','Staff','Viewer') NOT NULL DEFAULT 'Staff',
  status ENUM('Active','Invited','Disabled') NOT NULL DEFAULT 'Active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS transactions (
  id INT PRIMARY KEY AUTO_INCREMENT, reference VARCHAR(30) UNIQUE NOT NULL, customer VARCHAR(120) NOT NULL,
  type ENUM('Income','Expense') NOT NULL, amount DECIMAL(12,2) NOT NULL, category VARCHAR(80) NOT NULL,
  status ENUM('Paid','Pending','Overdue') NOT NULL DEFAULT 'Pending', transaction_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS payments (
  id INT PRIMARY KEY AUTO_INCREMENT, reference VARCHAR(30) UNIQUE NOT NULL, customer VARCHAR(120) NOT NULL,
  method ENUM('GCash','Maya','Bank Transfer','Cash','Card') NOT NULL, amount DECIMAL(12,2) NOT NULL,
  status ENUM('Completed','Pending','Failed') NOT NULL DEFAULT 'Pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS notifications (
  id INT PRIMARY KEY AUTO_INCREMENT, title VARCHAR(150) NOT NULL, message TEXT NOT NULL,
  audience VARCHAR(80) NOT NULL DEFAULT 'All customers', channel ENUM('Email','SMS','In-app') NOT NULL DEFAULT 'In-app',
  status ENUM('Draft','Scheduled','Sent') NOT NULL DEFAULT 'Draft', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS rewards (
  id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(120) NOT NULL, points INT UNSIGNED NOT NULL,
  redemptions INT UNSIGNED NOT NULL DEFAULT 0, status ENUM('Active','Paused') NOT NULL DEFAULT 'Active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS contents (
  id INT PRIMARY KEY AUTO_INCREMENT, title VARCHAR(160) NOT NULL, content_type ENUM('Guide','Article','Announcement') NOT NULL,
  status ENUM('Draft','Published','Archived') NOT NULL DEFAULT 'Draft', updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS support_tickets (
  id INT PRIMARY KEY AUTO_INCREMENT, subject VARCHAR(160) NOT NULL, customer VARCHAR(120) NOT NULL,
  priority ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium', status ENUM('Open','In progress','Resolved') NOT NULL DEFAULT 'Open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO business_profile (business_name,owner_name,industry,email,setup_progress)
SELECT 'Northstar Studio','Jamie Dela Cruz','Digital Services','hello@northstar.ph',82
WHERE NOT EXISTS (SELECT 1 FROM business_profile);
INSERT IGNORE INTO users (id,name,email,role,status) VALUES
(1,'Jamie Dela Cruz','jamie@northstar.ph','Administrator','Active'),
(2,'Alex Santos','alex@northstar.ph','Manager','Active'),(3,'Mia Reyes','mia@northstar.ph','Staff','Invited');
INSERT IGNORE INTO transactions (id,reference,customer,type,amount,category,status,transaction_date) VALUES
(1,'INV-2048','Acme Retail','Income',18500,'Sales','Paid',CURDATE()),
(2,'INV-2049','Northstar Coffee Co.','Income',12600,'Services','Pending',CURDATE()),
(3,'EXP-0891','CloudHost PH','Expense',3200,'Software','Paid',CURDATE() - INTERVAL 2 DAY),
(4,'INV-2047','Luna Goods','Income',9800,'Sales','Overdue',CURDATE() - INTERVAL 5 DAY);
INSERT IGNORE INTO payments (id,reference,customer,method,amount,status) VALUES
(1,'PAY-1048','Acme Retail','GCash',18500,'Completed'),(2,'PAY-1049','Northstar Coffee Co.','Bank Transfer',12600,'Pending');
INSERT IGNORE INTO notifications (id,title,message,audience,channel,status) VALUES
(1,'September offer','Enjoy 10% off your next order.','All customers','Email','Scheduled'),
(2,'Payment received','Your payment has been recorded.','Paying customers','In-app','Sent');
INSERT IGNORE INTO rewards (id,name,points,redemptions,status) VALUES
(1,'Free delivery voucher',250,42,'Active'),(2,'₱200 account credit',500,18,'Active');
INSERT IGNORE INTO contents (id,title,content_type,status) VALUES
(1,'Getting started with your account','Guide','Published'),(2,'September product update','Announcement','Draft');
INSERT IGNORE INTO support_tickets (id,subject,customer,priority,status) VALUES
(1,'Unable to download invoice','Luna Goods','High','Open'),(2,'Update billing email','Acme Retail','Medium','In progress'),(3,'Reward points inquiry','Northstar Coffee Co.','Low','Resolved');
