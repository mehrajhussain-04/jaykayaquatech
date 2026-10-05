<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();

$stats = [
    'products'    => (int)$pdo->query("SELECT COUNT(*) c FROM products")->fetch()['c'],
    'categories'  => (int)$pdo->query("SELECT COUNT(*) c FROM categories")->fetch()['c'],
    'enquiries'   => (int)$pdo->query("SELECT COUNT(*) c FROM enquiries WHERE status = 'new'")->fetch()['c'],
    'messages'    => (int)$pdo->query("SELECT COUNT(*) c FROM contact_messages WHERE is_read = 0")->fetch()['c'],
    'events'      => (int)$pdo->query("SELECT COUNT(*) c FROM events")->fetch()['c'],
    'catalogs'    => (int)$pdo->query("SELECT COUNT(*) c FROM catalogs")->fetch()['c'],
];

$recentEnquiries = $pdo->query("SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div>
    <p>Overview of your Jay Kay Aqua Tech website content, straight from the database.</p>
  </div>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8V7l-3-4H6L3 7v1"/><path d="M3 8h18l-1.5 12.5A2 2 0 0 1 17.5 22h-11a2 2 0 0 1-2-1.5L3 8Z"/></svg></div>
    <div><strong><?= $stats['products'] ?></strong><span>Total Products</span></div>
  </div>
  <div class="stat-card">
    <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg></div>
    <div><strong><?= $stats['categories'] ?></strong><span>Categories</span></div>
  </div>
  <div class="stat-card">
    <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
    <div><strong><?= $stats['enquiries'] ?></strong><span>New Enquiries</span></div>
  </div>
  <div class="stat-card">
    <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 7 9 6 9-6"/></svg></div>
    <div><strong><?= $stats['messages'] ?></strong><span>Unread Messages</span></div>
  </div>
  <div class="stat-card">
    <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg></div>
    <div><strong><?= $stats['events'] ?></strong><span>Events</span></div>
  </div>
  <div class="stat-card">
    <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg></div>
    <div><strong><?= $stats['catalogs'] ?></strong><span>Catalog Files</span></div>
  </div>
</div>

<div class="dash-grid">
  <div class="panel">
    <div class="panel-head">
      <h3>Recent Enquiries</h3>
      <a href="enquiries.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="panel-body">
      <?php if (!$recentEnquiries): ?>
        <div class="empty-state">No enquiries yet. They'll show up here as soon as someone submits the enquiry form on the site.</div>
      <?php else: ?>
        <div class="mini-list">
          <?php foreach ($recentEnquiries as $en): ?>
            <div class="mini-item">
              <div>
                <strong><?= e($en['name']) ?></strong>
                <div class="meta"><?= e($en['product'] ?: 'General enquiry') ?> · <?= e(format_date($en['created_at'], 'd M, g:i A')) ?></div>
              </div>
              <div class="mi-right">
                <?php
                  $badgeClass = $en['status'] === 'new' ? 'badge-sky' : ($en['status'] === 'contacted' ? 'badge-warning' : 'badge-success');
                ?>
                <span class="badge <?= $badgeClass ?>"><?= e(ucfirst($en['status'])) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3>Recent Messages</h3>
      <a href="messages.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="panel-body">
      <?php if (!$recentMessages): ?>
        <div class="empty-state">No contact messages yet.</div>
      <?php else: ?>
        <div class="mini-list">
          <?php foreach ($recentMessages as $m): ?>
            <div class="mini-item">
              <div>
                <strong><?= e($m['name']) ?></strong>
                <div class="meta"><?= e($m['email']) ?> · <?= e(format_date($m['created_at'], 'd M, g:i A')) ?></div>
              </div>
              <div class="mi-right">
                <?php if (!$m['is_read']): ?><span class="badge badge-sky">New</span><?php else: ?><span class="badge badge-neutral">Read</span><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
