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
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create administrator — Launch It</title>
  <link rel="icon" href="assets/logo.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/login.css?v=<?= filemtime(__DIR__.'/assets/login.css') ?>">
</head>
<body>
<main class="auth-page">
  <div class="auth-card">
    <section class="auth-brand" aria-label="Launch It">
      <div class="auth-brand-inner">
        <p class="welcome-line">Welcome to</p>
        <img class="brand-logo brand-logo--full" src="assets/logo.png" alt="Launch It" width="220" height="220">
        <p class="brand-copy">This one-time setup is disabled after an administrator has been created.</p>
      </div>
      <p class="brand-foot">Administrator setup</p>
    </section>
    <section class="auth-panel">
      <form method="post" class="auth-form">
        <header>
          <h1>Create administrator</h1>
          <p>Use an account only you control.</p>
        </header>
        <?php if ($error): ?><div class="error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <label class="field"><span>Full name</span><span class="field-control"><input name="name" required autocomplete="name" placeholder="Enter your name"></span></label>
        <label class="field"><span>Email address</span><span class="field-control"><input type="email" name="email" required autocomplete="email" placeholder="Enter your email"></span></label>
        <label class="field"><span>Password</span><span class="field-control"><input type="password" name="password" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters"></span></label>
        <div class="auth-actions">
          <button type="submit" class="btn-primary">Create administrator</button>
        </div>
      </form>
    </section>
  </div>
</main>
</body>
</html>
