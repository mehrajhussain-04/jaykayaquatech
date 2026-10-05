<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();

// --- delete ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare('SELECT image_path FROM events WHERE id = ?');
    $stmt->execute([$id]);
    $ev = $stmt->fetch();
    $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
    if ($ev && $ev['image_path']) {
        delete_uploaded_file($ev['image_path'], SITE_ROOT);
    }
    flash_set('success', 'Event deleted.');
    redirect('events.php');
}

// --- add / edit -------------------------------------------------------------
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$errors = [];
$values = [
    'title' => $editing['title'] ?? '',
    'description' => $editing['description'] ?? '',
    'event_date' => $editing['event_date'] ?? '',
    'status' => $editing['status'] ?? 'active',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    csrf_verify();
    $values['title'] = post('title');
    $values['description'] = post('description');
    $values['event_date'] = post('event_date');
    $values['status'] = in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active';
    $eventId = (int)($_POST['event_id'] ?? 0);

    if ($values['title'] === '') {
        $errors['title'] = 'Event title is required.';
    }

    $newImage = null;
    try {
        $newImage = handle_image_upload('image', UPLOAD_EVENTS_DIR, UPLOAD_EVENTS_URL);
    } catch (RuntimeException $e) {
        $errors['image'] = $e->getMessage();
    }

    if (!$errors) {
        $eventDate = $values['event_date'] !== '' ? $values['event_date'] : null;

        if ($eventId > 0) {
            $imagePath = $editing['image_path'] ?? null;
            if ($newImage) {
                if ($imagePath) {
                    delete_uploaded_file($imagePath, SITE_ROOT);
                }
                $imagePath = $newImage;
            }
            $pdo->prepare("UPDATE events SET title=?, description=?, event_date=?, image_path=?, status=?, updated_at=NOW() WHERE id=?")
                ->execute([$values['title'], $values['description'], $eventDate, $imagePath, $values['status'], $eventId]);
            flash_set('success', 'Event updated.');
        } else {
            $pdo->prepare('INSERT INTO events (title, description, event_date, image_path, status) VALUES (?, ?, ?, ?, ?)')
                ->execute([$values['title'], $values['description'], $eventDate, $newImage, $values['status']]);
            flash_set('success', 'Event added.');
        }
        redirect('events.php');
    }
}

$events = $pdo->query('SELECT * FROM events ORDER BY COALESCE(event_date, created_at) DESC')->fetchAll();

$pageTitle = 'Events';
require __DIR__ . '/includes/header.php';
?>

<div class="dash-grid" style="grid-template-columns: 1.6fr 1fr;">
  <div class="card" style="padding:0;">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Image</th><th>Title</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (!$events): ?>
            <tr class="empty-row"><td colspan="5">No events yet. Add your first event using the form.</td></tr>
          <?php else: foreach ($events as $ev): ?>
            <tr>
              <td>
                <?php if ($ev['image_path']): ?>
                  <img class="cell-thumb" src="<?= e($ev['image_path']) ?>" alt="">
                <?php else: ?>
                  <div class="cell-thumb" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18"/></svg></div>
                <?php endif; ?>
              </td>
              <td><strong><?= e($ev['title']) ?></strong></td>
              <td><?= $ev['event_date'] ? e(date('d M Y', strtotime($ev['event_date']))) : '—' ?></td>
              <td><?php if ($ev['status'] === 'active'): ?><span class="badge badge-success">Active</span><?php else: ?><span class="badge badge-neutral">Inactive</span><?php endif; ?></td>
              <td>
                <div class="row-actions">
                  <a href="events.php?edit=<?= (int)$ev['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                  <button type="button" class="btn btn-danger btn-sm"
                    data-confirm="This will permanently delete &quot;<?= e($ev['title']) ?>&quot;."
                    data-confirm-title="Delete this event?"
                    data-action="events.php"
                    data-field-delete_id="<?= (int)$ev['id'] ?>">Delete</button>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom:16px;"><?= $editing ? 'Edit Event' : 'Add Event' ?></h3>
    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="save_event" value="1">
      <input type="hidden" name="event_id" value="<?= (int)($editing['id'] ?? 0) ?>">

      <div class="field">
        <label for="title">Title <span class="req">*</span></label>
        <input type="text" id="title" name="title" value="<?= e($values['title']) ?>" required>
        <?php if (!empty($errors['title'])): ?><span class="field-error"><?= e($errors['title']) ?></span><?php endif; ?>
      </div>
      <div class="field">
        <label for="event_date">Date</label>
        <input type="date" id="event_date" name="event_date" value="<?= e($values['event_date'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= e($values['description']) ?></textarea>
      </div>
      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="active" <?= $values['status'] === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $values['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="field">
        <label>Image</label>
        <div class="thumb-preview" id="eventImagePreview">
          <?php if (!empty($editing['image_path'])): ?>
            <img src="<?= e($editing['image_path']) ?>" alt="">
          <?php else: ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/></svg>
          <?php endif; ?>
        </div>
        <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif" data-preview="eventImagePreview">
        <?php if (!empty($errors['image'])): ?><span class="field-error"><?= e($errors['image']) ?></span><?php endif; ?>
      </div>
      <div class="form-actions" style="border-top:none; padding-top:0;">
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add Event' ?></button>
        <?php if ($editing): ?><a href="events.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
