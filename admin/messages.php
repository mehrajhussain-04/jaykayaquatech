<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['delete_id'])) {
        $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([(int)$_POST['delete_id']]);
        flash_set('success', 'Message deleted.');
        redirect('messages.php');
    }

    if (isset($_POST['toggle_read_id'])) {
        $id = (int)$_POST['toggle_read_id'];
        $current = $pdo->prepare('SELECT is_read FROM contact_messages WHERE id = ?');
        $current->execute([$id]);
        $row = $current->fetch();
        if ($row) {
            $pdo->prepare('UPDATE contact_messages SET is_read = ? WHERE id = ?')
                ->execute([$row['is_read'] ? 0 : 1, $id]);
        }
        redirect('messages.php');
    }
}

$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Messages';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div><p><?= count($messages) ?> message<?= count($messages) === 1 ? '' : 's' ?> total</p></div>
</div>

<div class="card" style="padding:0;">
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th>From</th><th>Contact</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (!$messages): ?>
          <tr class="empty-row"><td colspan="6">No contact messages yet.</td></tr>
        <?php else: foreach ($messages as $m): ?>
          <tr>
            <td><strong><?= e($m['name']) ?></strong></td>
            <td><?= e($m['email']) ?><?php if ($m['phone']): ?><br><span style="color:rgba(17,24,39,.55); font-size:.8rem;"><?= e($m['phone']) ?></span><?php endif; ?></td>
            <td style="max-width:280px;"><?= e(mb_strimwidth($m['message'], 0, 100, '…')) ?></td>
            <td><?= e(format_date($m['created_at'], 'd M Y')) ?></td>
            <td><?php if ($m['is_read']): ?><span class="badge badge-neutral">Read</span><?php else: ?><span class="badge badge-sky">New</span><?php endif; ?></td>
            <td>
              <div class="row-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('msgmodal-<?= (int)$m['id'] ?>').classList.add('show')">View</button>
                <form method="POST" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="toggle_read_id" value="<?= (int)$m['id'] ?>">
                  <button type="submit" class="btn btn-secondary btn-sm"><?= $m['is_read'] ? 'Mark Unread' : 'Mark Read' ?></button>
                </form>
                <button type="button" class="btn btn-danger btn-sm"
                  data-confirm="This will permanently delete the message from &quot;<?= e($m['name']) ?>&quot;."
                  data-confirm-title="Delete this message?"
                  data-action="messages.php"
                  data-field-delete_id="<?= (int)$m['id'] ?>">Delete</button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php foreach ($messages as $m): ?>
  <div class="modal-scrim" id="msgmodal-<?= (int)$m['id'] ?>" onclick="if(event.target===this) this.classList.remove('show')">
    <div class="modal-box" style="max-width:480px;">
      <h3><?= e($m['name']) ?></h3>
      <p style="margin-bottom:6px;"><strong>Email:</strong> <?= e($m['email']) ?><?php if ($m['phone']): ?> &nbsp; <strong>Phone:</strong> <?= e($m['phone']) ?><?php endif; ?></p>
      <p style="white-space:pre-wrap; background:var(--sky-light); padding:12px; border-radius:8px;"><?= e($m['message']) ?></p>
      <div class="modal-actions"><button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('msgmodal-<?= (int)$m['id'] ?>').classList.remove('show')">Close</button></div>
    </div>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
