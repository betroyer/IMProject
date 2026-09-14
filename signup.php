<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';

if (current_user()) { header('Location: index.php'); exit; }

$error = '';
$values = ['name'=>'', 'business_name'=>'', 'email'=>''];
if (empty($_SESSION['signup_csrf'])) $_SESSION['signup_csrf'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name'] = trim($_POST['name'] ?? '');
    $values['business_name'] = trim($_POST['business_name'] ?? '');
    $values['email'] = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmation = (string)($_POST['password_confirmation'] ?? '');
    $token = (string)($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['signup_csrf'], $token)) {
        $error = 'Your form expired. Refresh the page and try again.';
    } elseif (!$values['name'] || !$values['business_name'] || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter your name, business name, and a valid email address.';
    } elseif (strlen($password) < 10) {
        $error = 'Use a password with at least 10 characters.';
    } elseif ($password !== $confirmation) {
        $error = 'The passwords do not match.';
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO organizations (name,status) VALUES (?,'Active')");
            $stmt->execute([$values['business_name']]);
            $organizationId = (int)$pdo->lastInsertId();
            $permissions = json_encode([
                'setup'=>'view', 'plan'=>'edit', 'goals'=>'edit', 'accounting'=>'edit',
                'reports'=>'view', 'notifications'=>'view', 'support'=>'edit'
            ], JSON_THROW_ON_ERROR);
            $stmt = $pdo->prepare("INSERT INTO users (organization_id,name,email,password,role,status,permissions) VALUES (?,?,?,?, 'Client','Active',?)");
            $stmt->execute([$organizationId,$values['name'],$values['email'],$password,$permissions]);
            $userId = (int)$pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO business_profile (organization_id,business_name,owner_name,industry,email,setup_progress) VALUES (?,?,?,'Not specified',?,20)");
            $stmt->execute([$organizationId,$values['business_name'],$values['name'],$values['email']]);
            $pdo->commit();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            unset($_SESSION['signup_csrf']);
            header('Location: index.php#setup');
            exit;
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $exception->getCode() === '23000'
                ? 'An account already uses that email address. Sign in instead.'
                : 'Your account could not be created. Make sure the database setup is complete and try again.';
        }
    }
}

function old_value(array $values, string $key): string {
    return htmlspecialchars($values[$key] ?? '', ENT_QUOTES, 'UTF-8');
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create client account — Startup:business management system</title>
  <link rel="stylesheet" href="assets/login.css?v=<?= filemtime(__DIR__.'/assets/login.css') ?>">
</head>
<body><main class="login-shell">
  <section class="login-intro">
    <div class="brand"><span>S</span><div><strong>Startup:</strong><small>business management system</small></div></div>
    <div><h1>Start with the essentials.</h1><p>Create your client workspace, complete your business setup, and keep planning, accounting, notifications, and support in one place.</p></div>
    <small class="foot">Client accounts are kept separate by organization</small>
  </section>
  <section class="login-panel">
    <form method="post" class="signup-form">
      <header><h2>Create your client account</h2><p>Enter your details to open a workspace for your business.</p></header>
      <?php if ($error): ?><div class="error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['signup_csrf']) ?>">
      <div class="field-grid">
        <label>Full name<input name="name" value="<?= old_value($values,'name') ?>" autocomplete="name" required autofocus></label>
        <label>Business name<input name="business_name" value="<?= old_value($values,'business_name') ?>" autocomplete="organization" required></label>
      </div>
      <label>Email address<input type="email" name="email" value="<?= old_value($values,'email') ?>" autocomplete="email" required placeholder="you@example.com"></label>
      <div class="field-grid">
        <label>Password<input type="password" name="password" autocomplete="new-password" minlength="10" required></label>
        <label>Confirm password<input type="password" name="password_confirmation" autocomplete="new-password" minlength="10" required></label>
      </div>
      <p class="privacy-note">Use at least 10 characters. Your account will only access information stored for your business.</p>
      <button type="submit">Create client account</button>
      <p class="form-switch">Already have an account? <a href="login.php">Sign in</a></p>
    </form>
  </section>
</main></body></html>
