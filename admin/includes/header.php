<?php
/**
 * Shared admin page header. Every protected page sets $pageTitle
 * (and optionally $pageSubtitle) then does: require 'includes/header.php';
 * Requires require_login() to have already been called by the page.
 */
$admin = current_admin();
$pageTitle = $pageTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> · Jay Kay Aqua Tech Admin</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="../favicon.png">
<link rel="stylesheet" href="assets/admin-style.css">
</head>
<body>
<div class="admin-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="sidebar-scrim" id="sidebarScrim"></div>

  <div class="admin-main">
    <div class="admin-topbar">
      <div style="display:flex; align-items:center; gap:14px;">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>
        <h1><?= e($pageTitle) ?></h1>
      </div>
      <div class="top-actions">
        <a href="../index.html" target="_blank" class="btn btn-secondary btn-sm">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/></svg>
          View Site
        </a>
        <div class="admin-user">
          <div class="avatar"><?= e(strtoupper(substr($admin['email'] ?? 'A', 0, 1))) ?></div>
          <span><?= e($admin['email'] ?? '') ?></span>
        </div>
      </div>
    </div>

    <div class="admin-content">
      <?php foreach (flash_all() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>">
          <?php if ($f['type'] === 'success'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg>
          <?php elseif ($f['type'] === 'error'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
          <?php else: ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
          <?php endif; ?>
          <span><?= e($f['message']) ?></span>
          <button class="close-alert" aria-label="Dismiss">&times;</button>
        </div>
      <?php endforeach; ?>
