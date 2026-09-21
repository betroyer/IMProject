<?php declare(strict_types=1); require __DIR__ . '/auth.php'; $signedInUser = require_login(); $isAdmin = is_admin($signedInUser); ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="A complete workspace for startup operations.">
  <title>Startup:business management system</title>
  <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__.'/assets/style.css') ?>">
  <link rel="stylesheet" href="assets/polish.css?v=<?= filemtime(__DIR__.'/assets/polish.css') ?>">
  <link rel="stylesheet" href="assets/client-panel.css?v=<?= filemtime(__DIR__.'/assets/client-panel.css') ?>">
</head>
<body class="<?= $isAdmin ? 'role-admin' : 'role-client' ?>">
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <span class="brand-mark" aria-hidden="true">S</span>
      <div><strong>Startup:</strong><small>business management system</small></div>
      <button class="close-menu icon-btn" aria-label="Close menu">×</button>
    </div>
    <nav id="navigation" aria-label="Main navigation">
      <?php if ($isAdmin): ?>
      <p class="nav-label">Administration</p>
      <button class="nav-item active" data-view="content" data-icon="file">Content &amp; guides</button>
      <button class="nav-item" data-view="users" data-icon="users">User accounts</button>
      <button class="nav-item" data-view="performance" data-icon="activity">System performance</button>
      <button class="nav-item" data-view="notifications" data-icon="bell">Send announcements</button>
      <button class="nav-item" data-view="support" data-icon="support">Support management</button>
      <?php else: ?>
      <p class="nav-label">Menu</p>
      <button class="nav-item active" data-view="setup" data-icon="check">Setup guide</button>
      <button class="nav-item" data-view="plan" data-icon="plan">Business plan</button>
      <button class="nav-item" data-view="goals" data-icon="activity">Goals &amp; objectives</button>
      <p class="nav-label nav-group">Finance</p>
      <button class="nav-item" data-view="accounting" data-icon="wallet">Accounting journal</button>
      <button class="nav-item" data-view="reports" data-icon="file">Accounting reports</button>
      <p class="nav-label nav-group">Communication</p>
      <button class="nav-item" data-view="notifications" data-icon="bell">Notifications</button>
      <button class="nav-item" data-view="support" data-icon="support">Help &amp; support</button>
      <?php endif; ?>
    </nav>
    <div class="sidebar-foot">
      <div class="profile">
        <span class="avatar"><?= htmlspecialchars(strtoupper(substr($signedInUser['name'],0,1))) ?></span>
        <div>
          <strong><?= htmlspecialchars($signedInUser['name']) ?></strong>
          <small><?= htmlspecialchars($signedInUser['role']) ?></small>
        </div>
      </div>
      <a class="sign-out-btn" href="logout.php">Sign out</a>
    </div>
  </aside>
  <button class="scrim" aria-label="Close menu"></button>
  <div class="workspace">
    <main>
      <header class="topbar">
        <button class="menu-toggle icon-btn" aria-label="Open menu">Menu</button>
        <label class="search">
          <span aria-hidden="true">Search</span>
          <input id="global-search" placeholder="Search records" aria-label="Search records">
        </label>
        <div class="top-actions">
          <span class="role-chip"><?= htmlspecialchars($signedInUser['role']) ?></span>
          <button class="notification-button icon-btn" aria-label="Notifications"><span data-inline-icon="bell"></span></button>
          <span class="avatar top-avatar"><?= htmlspecialchars(strtoupper(substr($signedInUser['name'],0,1))) ?></span>
        </div>
      </header>
      <div class="content" id="app" aria-live="polite"></div>
    </main>
    <?php if (!$isAdmin): ?>
    <aside class="quick-rail" id="quick-rail" aria-label="Quick actions">
      <header class="quick-rail-head">
        <h2>Quick actions</h2>
      </header>
      <div class="quick-rail-body" id="quick-rail-body">
        <p class="panel-sub">Shortcuts for the work you do most often.</p>
        <div class="quick-rail-list">
          <button type="button" data-go="accounting"><span data-inline-icon="wallet"></span><div><strong>Record a transaction</strong><small>Accounting journal</small></div></button>
          <button type="button" data-go="reports"><span data-inline-icon="file"></span><div><strong>View reports</strong><small>Income and expenses</small></div></button>
          <button type="button" data-go="plan"><span data-inline-icon="plan"></span><div><strong>Update business plan</strong><small>Keep the plan current</small></div></button>
          <button type="button" data-go="support"><span data-inline-icon="support"></span><div><strong>Get help</strong><small>Open a support request</small></div></button>
        </div>
        <article class="quick-rail-card">
          <h3>Finish onboarding</h3>
          <p>Complete your business details so every module uses the right organization information.</p>
          <button type="button" class="button primary" data-go="setup">Continue setup</button>
        </article>
      </div>
    </aside>
    <?php endif; ?>
  </div>
</div>
<dialog id="record-dialog"><form method="dialog" id="record-form"><div class="dialog-head"><h2 id="dialog-title">Add record</h2><button value="cancel" formnovalidate class="icon-btn" aria-label="Close">×</button></div><div id="form-fields" class="form-grid"></div><div class="dialog-actions"><button value="cancel" formnovalidate class="button secondary">Cancel</button><button value="default" class="button primary" id="save-record">Save record</button></div></form></dialog>
<div id="toast" role="status"></div>
<script>window.CURRENT_USER=<?= json_encode($signedInUser, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;</script><script src="assets/app.js?v=<?= filemtime(__DIR__.'/assets/app.js') ?>"></script>
</body></html>
