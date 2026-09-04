<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
if (current_user()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    try {
        $stmt = db()->prepare('SELECT id, password_hash, status FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]); $user = $stmt->fetch();
        if ($user && $user['status'] === 'Active' && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true); $_SESSION['user_id'] = (int)$user['id'];
            header('Location: index.php'); exit;
        }
        $error = 'Email or password is incorrect.';
    } catch (Throwable $e) { $error = 'The database needs the latest migration before sign-in can be used.'; }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in — Startup:business management system</title><link rel="stylesheet" href="assets/login.css"></head>
<body><main class="login-shell"><section class="login-intro"><div class="brand"><span>S</span><div><strong>Startup:</strong><small>business management system</small></div></div><div><h1>Run your business from one clear workspace.</h1><p>Manage finances, customers, rewards, guides, accounts, and support without switching between tools.</p></div><small class="foot">Secure access for administrators and clients</small></section><section class="login-panel"><form method="post"><header><h2>Welcome back</h2><p>Sign in to continue to your workspace.</p></header><?php if ($error): ?><div class="error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?><label>Email address<input type="email" name="email" autocomplete="username" required autofocus placeholder="you@example.com"></label><label>Password<input type="password" name="password" autocomplete="current-password" required placeholder="Enter your password"></label><button type="submit">Sign in</button></form></section></main></body></html>
