<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['delete_id'])) {
        $pdo->prepare('DELETE FROM enquiries WHERE id = ?')->execute([(int)$_POST['delete_id']]);
        flash_set('success', 'Enquiry deleted.');
        redirect('enquiries.php');
    }

    if (isset($_POST['update_status_id'])) {
        $newStatus = $_POST['new_status'] ?? '';
        if (in_array($newStatus, ['new', 'contacted', 'completed'], true)) {
            $pdo->prepare('UPDATE enquiries SET status = ? WHERE id = ?')
                ->execute([$newStatus, (int)$_POST['update_status_id']]);
            flash_set('success', 'Status updated.');
        }
        redirect('enquiries.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    }
}

$statusFilter = trim((string)($_GET['status'] ?? ''));
$sql = 'SELECT * FROM enquiries WHERE 1=1';
$params = [];
if ($statusFilter !== '') {
    $sql .= ' AND status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

$pageTitle = 'Enquiries';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div><p><?= count($enquiries) ?> enquir<?= count($enquiries) === 1 ? 'y' : 'ies' ?></p></div>
</div>

<div class="filters-bar">
  <form method="GET">
    <select name="status" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New</option>
      <option value="contacted" <?= $statusFilter === 'contacted' ? 'selected' : '' ?>>Contacted</option>
      <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
    </select>
  </form>
</div>

<div class="card" style="padding:0;">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Name</th><th>Contact</th><th>Product</th><th>Qty</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (!$enquiries): ?>
          <tr class="empty-row"><td colspan="8">No enquiries yet. They'll appear here as soon as someone submits the enquiry form on the site.</td></tr>
        <?php else: foreach ($enquiries as $en): ?>
          <tr>
            <td><strong><?= e($en['name']) ?></strong></td>
            <td><?= e($en['phone']) ?><br><span style="color:rgba(17,24,39,.55); font-size:.8rem;"><?= e($en['email']) ?></span></td>
            <td><?= e($en['product'] ?: '—') ?></td>
            <td><?= e($en['quantity'] ?: '—') ?></td>
            <td style="max-width:220px;"><?= e(mb_strimwidth($en['message'], 0, 90, '…')) ?></td>
            <td><?= e(format_date($en['created_at'], 'd M Y')) ?></td>
            <td>
              <form method="POST" style="display:inline-flex; gap:6px; align-items:center;">
                <?= csrf_field() ?>
                <input type="hidden" name="update_status_id" value="<?= (int)$en['id'] ?>">
                <select name="new_status" onchange="this.form.submit()" style="padding:6px 8px; border-radius:6px; border:1.5px solid var(--border); font-size:.8rem;">
                  <option value="new" <?= $en['status'] === 'new' ? 'selected' : '' ?>>New</option>
                  <option value="contacted" <?= $en['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                  <option value="completed" <?= $en['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
              </form>
            </td>
            <td>
              <div class="row-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('viewmodal-<?= (int)$en['id'] ?>').classList.add('show')">View</button>
                <button type="button" class="btn btn-danger btn-sm"
                  data-confirm="This will permanently delete the enquiry from &quot;<?= e($en['name']) ?>&quot;."
                  data-confirm-title="Delete this enquiry?"
                  data-action="enquiries.php"
                  data-field-delete_id="<?= (int)$en['id'] ?>">Delete</button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Full-message modals (kept outside the table for valid HTML) -->
<?php foreach ($enquiries as $en): ?>
  <div class="modal-scrim" id="viewmodal-<?= (int)$en['id'] ?>" onclick="if(event.target===this) this.classList.remove('show')">
    <div class="modal-box" style="max-width:480px;">
      <h3><?= e($en['name']) ?></h3>
      <p style="margin-bottom:6px;"><strong>Phone:</strong> <?= e($en['phone']) ?> &nbsp; <strong>Email:</strong> <?= e($en['email']) ?></p>
      <p style="margin-bottom:6px;"><strong>Product:</strong> <?= e($en['product'] ?: 'General enquiry') ?> &nbsp; <strong>Qty:</strong> <?= e($en['quantity'] ?: '—') ?></p>
      <p style="white-space:pre-wrap; background:var(--sky-light); padding:12px; border-radius:8px;"><?= e($en['message']) ?></p>
      <div class="modal-actions"><button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('viewmodal-<?= (int)$en['id'] ?>').classList.remove('show')">Close</button></div>
    </div>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
