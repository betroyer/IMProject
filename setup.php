<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
$pdo=db();
if((int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='Administrator' AND password_hash IS NOT NULL")->fetchColumn()>0){header('Location: login.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??'');$email=strtolower(trim($_POST['email']??''));$password=(string)($_POST['password']??'');
  if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<10){$error='Enter a valid name and email, and use a password with at least 10 characters.';}
  else{$stmt=$pdo->prepare("INSERT INTO users (organization_id,name,email,password_hash,role,status,permissions) VALUES (NULL,?,?,?,'Administrator','Active','{}')");$stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);header('Location: login.php');exit;}
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create administrator</title><link rel="stylesheet" href="assets/login.css"></head><body><main class="login-shell"><section class="login-intro"><div class="brand"><span>S</span><div><strong>Startup:</strong><small>business management system</small></div></div><div><h1>Create the first administrator.</h1><p>This one-time setup is disabled after an administrator has been created.</p></div></section><section class="login-panel"><form method="post"><header><h2>Administrator setup</h2><p>Use an account only you control.</p></header><?php if($error):?><div class="error" role="alert"><?=htmlspecialchars($error)?></div><?php endif;?><label>Full name<input name="name" required autocomplete="name"></label><label>Email address<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" required minlength="10" autocomplete="new-password"></label><button>Create administrator</button></form></section></main></body></html>
