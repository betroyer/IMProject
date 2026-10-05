<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
if (current_user()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    try {
        $stmt = db()->prepare('SELECT id, password, password_hash, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]); $user = $stmt->fetch();
        // Legacy hashes remain usable until the owner next signs in successfully.
        $valid = $user && $password !== '' && ($user['password'] !== null
            ? hash_equals((string)$user['password'], $password)
            : password_verify($password, (string)($user['password_hash'] ?? '')));
        if ($valid && $user['status'] === 'Active') {
            if ($user['password'] === null) {
                $update = db()->prepare('UPDATE users SET password=?, password_hash=NULL WHERE id=?');
                $update->execute([$password, $user['id']]);
            }
            session_regenerate_id(true); $_SESSION['user_id'] = (int)$user['id'];
            header('Location: index.php'); exit;
        }
        $error = 'Email or password is incorrect.';
    } catch (Throwable $e) { $error = 'The database needs the latest migration before sign-in can be used.'; }
}
$cssVersion = filemtime(__DIR__ . '/assets/login.css');
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign in — Launch It</title>
  <link rel="icon" href="assets/logo.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/login.css?v=<?= $cssVersion ?>">
</head>
<body>
<main class="auth-page">
  <div class="auth-card">
    <section class="auth-brand" aria-label="Launch It">
      <div class="auth-brand-inner">
        <p class="welcome-line">Welcome to</p>
        <img class="brand-logo" src="assets/logo-panel.png" alt="Launch It" width="240" height="154">
        <p class="brand-copy">Manage finances, customers, rewards, guides, accounts, and support from one clear workspace.</p>
      </div>
      <div class="auth-wave" aria-hidden="true">
        <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
      </div>
      <p class="brand-foot">Secure access for administrators and clients</p>
    </section>

    <section class="auth-panel">
      <form method="post" class="auth-form" novalidate>
        <header>
          <h1>Welcome back</h1>
          <p>Sign in to continue to your workspace.</p>
        </header>

        <?php if ($error): ?>
          <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label class="field">
          <span>Email address</span>
          <span class="field-control">
            <input type="email" name="email" autocomplete="username" required autofocus placeholder="Enter your email">
            <svg class="field-check" viewBox="0 0 20 20" aria-hidden="true"><path d="M7.8 13.2 4.6 10l-1.2 1.2 4.4 4.4L16.6 6.8 15.4 5.6z"/></svg>
          </span>
        </label>

        <label class="field">
          <span>Password</span>
          <span class="field-control">
            <input type="password" name="password" autocomplete="current-password" required placeholder="Enter your password">
            <svg class="field-check" viewBox="0 0 20 20" aria-hidden="true"><path d="M7.8 13.2 4.6 10l-1.2 1.2 4.4 4.4L16.6 6.8 15.4 5.6z"/></svg>
          </span>
        </label>

        <div class="auth-actions">
          <button type="submit" class="btn-primary">Sign in</button>
          <a class="btn-secondary" href="signup.php">Sign up</a>
        </div>
      </form>
    </section>
  </div>
</main>
</body>
</html>
