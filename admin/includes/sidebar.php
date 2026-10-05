<?php
/**
 * Sidebar navigation. Included by header.php.
 */
$currentPage = basename($_SERVER['SCRIPT_NAME']);

function nav_active(string $page, string $current): string
{
    return $page === $current ? ' class="active"' : '';
}

// Small live counts for the nav badges (kept cheap — single COUNT queries).
try {
    $newEnquiries = (int) db()->query("SELECT COUNT(*) c FROM enquiries WHERE status = 'new'")->fetch()['c'];
    $unreadMessages = (int) db()->query("SELECT COUNT(*) c FROM contact_messages WHERE is_read = 0")->fetch()['c'];
} catch (Throwable $e) {
    $newEnquiries = 0;
    $unreadMessages = 0;
}
?>
<aside class="admin-sidebar" id="adminSidebar">
  <a href="dashboard.php" class="brand">
    <img src="../assets/logo/Jay Kay Aqua Tech.png" alt="Jay Kay Aqua Tech">
    <span>JAY KAY AQUA TECH<small>Admin Panel</small></span>
  </a>

  <nav class="admin-nav">
    <a href="dashboard.php"<?= nav_active('dashboard.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      Dashboard
    </a>

    <div class="nav-label">Catalog</div>
    <a href="products.php"<?= nav_active('products.php', $currentPage) . nav_active('add-product.php', $currentPage) . nav_active('edit-product.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8V7l-3-4H6L3 7v1"/><path d="M3 8h18l-1.5 12.5A2 2 0 0 1 17.5 22h-11a2 2 0 0 1-2-1.5L3 8Z"/><path d="M9 12h6"/></svg>
      Products
    </a>
    <a href="categories.php"<?= nav_active('categories.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
      Categories
    </a>

    <div class="nav-label">Customer Activity</div>
    <a href="enquiries.php"<?= nav_active('enquiries.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Enquiries
      <?php if ($newEnquiries > 0): ?><span class="count-pill"><?= $newEnquiries ?></span><?php endif; ?>
    </a>
    <a href="messages.php"<?= nav_active('messages.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 7 9 6 9-6"/></svg>
      Messages
      <?php if ($unreadMessages > 0): ?><span class="count-pill"><?= $unreadMessages ?></span><?php endif; ?>
    </a>

    <div class="nav-label">Content</div>
    <a href="events.php"<?= nav_active('events.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>
      Events
    </a>
    <a href="catalog.php"<?= nav_active('catalog.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
      Catalog
    </a>

    <div class="nav-label">System</div>
    <a href="settings.php"<?= nav_active('settings.php', $currentPage) ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
      Settings
    </a>
  </nav>

  <div class="sidebar-foot">
    <a href="logout.php" class="view-site" data-confirm="You'll need to log in again to access the admin panel." data-confirm-title="Log out?" data-action="logout.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
      Log out
    </a>
  </div>
</aside>
