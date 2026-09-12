<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('startup_bms_session');
    session_start();
}

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) return $user;
    $id = (int)($_SESSION['user_id'] ?? 0);
    if (!$id) return $user = null;
    $stmt = db()->prepare('SELECT id, organization_id, name, email, role, status, permissions FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found || $found['status'] !== 'Active') {
        session_destroy();
        return $user = null;
    }
    $found['permissions'] = json_decode($found['permissions'] ?: '{}', true) ?: [];
    return $user = $found;
}

function require_login(bool $json = false): array
{
    $user = current_user();
    if ($user) return $user;
    if ($json) json_response(['ok' => false, 'message' => 'Your session has ended. Please sign in again.'], 401);
    header('Location: login.php');
    exit;
}

function is_admin(array $user): bool { return $user['role'] === 'Administrator'; }

function permission_level(array $user, string $module): string
{
    if (is_admin($user)) return in_array($module, ['content','users','performance','notifications','support'], true) ? 'edit' : 'none';
    $defaults = ['setup'=>'view','plan'=>'edit','goals'=>'edit','accounting'=>'edit','reports'=>'view','notifications'=>'view','support'=>'edit'];
    return $user['permissions'][$module] ?? ($defaults[$module] ?? 'none');
}

function require_permission(array $user, string $module, string $needed = 'view'): void
{
    $level = permission_level($user, $module);
    if ($level === 'none' || ($needed === 'edit' && $level !== 'edit')) {
        json_response(['ok'=>false,'message'=>'You do not have permission to perform this action.'], 403);
    }
}
