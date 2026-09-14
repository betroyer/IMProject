<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
$error='';

try {
  $pdo = db();

  // Allow setup.php to upgrade databases created from the original schema.sql.
  // Without this guard, the first visit fails because password_hash does not yet exist.
  $pdo->exec("CREATE TABLE IF NOT EXISTS organizations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(140) NOT NULL,
    status ENUM('Active','Suspended') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
  $pdo->exec("INSERT IGNORE INTO organizations (id,name,status) VALUES (1,'Northstar Studio','Active')");
  $pdo->exec("ALTER TABLE users MODIFY role ENUM('Administrator','Client','Manager','Staff','Viewer') NOT NULL DEFAULT 'Client'");
  $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS organization_id INT NULL AFTER id");
  $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL AFTER email");
  $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS password TEXT NULL AFTER password_hash");
  $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS permissions JSON NULL AFTER status");
  $pdo->exec("UPDATE users SET organization_id=1 WHERE organization_id IS NULL AND role!='Administrator'");
  $pdo->exec("ALTER TABLE business_profile ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id");
  $pdo->exec("ALTER TABLE business_profile ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL AFTER email");
  $pdo->exec("ALTER TABLE business_profile ADD COLUMN IF NOT EXISTS address VARCHAR(220) NULL AFTER phone");
  $pdo->exec("ALTER TABLE transactions ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id");
  $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id");
  $pdo->exec("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS organization_id INT NULL AFTER id");
  $pdo->exec("ALTER TABLE rewards ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id");
  $pdo->exec("ALTER TABLE support_tickets ADD COLUMN IF NOT EXISTS organization_id INT NOT NULL DEFAULT 1 AFTER id");
  $pdo->exec("CREATE TABLE IF NOT EXISTS business_plans (
    id INT PRIMARY KEY AUTO_INCREMENT, organization_id INT NOT NULL,
    section VARCHAR(100) NOT NULL, title VARCHAR(160) NOT NULL, details TEXT NOT NULL,
    status ENUM('Draft','In progress','Complete') NOT NULL DEFAULT 'Draft',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )");
  $pdo->exec("CREATE TABLE IF NOT EXISTS goals (
    id INT PRIMARY KEY AUTO_INCREMENT, organization_id INT NOT NULL,
    title VARCHAR(160) NOT NULL, objective TEXT NOT NULL, target_date DATE NOT NULL,
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('Not started','In progress','Complete') NOT NULL DEFAULT 'Not started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  if ((int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='Administrator' AND (password IS NOT NULL OR password_hash IS NOT NULL)")->fetchColumn() > 0) {
    header('Location: login.php');
    exit;
  }
} catch (PDOException $exception) {
  $error = 'Database setup could not be completed. Confirm that MySQL is running and the database user can update tables.';
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??'');$email=strtolower(trim($_POST['email']??''));$password=(string)($_POST['password']??'');
  if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<10){$error='Enter a valid name and email, and use a password with at least 10 characters.';}
  elseif (!$error) {
    try {
      $existing=$pdo->query("SELECT id FROM users WHERE role='Administrator' AND password IS NULL AND password_hash IS NULL ORDER BY id LIMIT 1")->fetchColumn();
      if($existing){
        $stmt=$pdo->prepare("UPDATE users SET organization_id=NULL,name=?,email=?,password=?,password_hash=NULL,status='Active',permissions='{}' WHERE id=?");
        $stmt->execute([$name,$email,$password,(int)$existing]);
      } else {
        $stmt=$pdo->prepare("INSERT INTO users (organization_id,name,email,password,role,status,permissions) VALUES (NULL,?,?,?,'Administrator','Active','{}')");
        $stmt->execute([$name,$email,$password]);
      }
      header('Location: login.php');exit;
    } catch (PDOException $exception) {
      $error = $exception->getCode()==='23000' ? 'That email address is already assigned to another account.' : 'The administrator account could not be created.';
    }
  }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create administrator</title><link rel="stylesheet" href="assets/login.css"></head><body><main class="login-shell"><section class="login-intro"><div class="brand"><span>S</span><div><strong>Startup:</strong><small>business management system</small></div></div><div><h1>Create the first administrator.</h1><p>This one-time setup is disabled after an administrator has been created.</p></div></section><section class="login-panel"><form method="post"><header><h2>Administrator setup</h2><p>Use an account only you control.</p></header><?php if($error):?><div class="error" role="alert"><?=htmlspecialchars($error)?></div><?php endif;?><label>Full name<input name="name" required autocomplete="name"></label><label>Email address<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" required minlength="10" autocomplete="new-password"></label><button>Create administrator</button></form></section></main></body></html>
