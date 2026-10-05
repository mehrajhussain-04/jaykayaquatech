<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare('SELECT file_path FROM catalogs WHERE id = ?');
    $stmt->execute([$id]);
    $cat = $stmt->fetch();
    $pdo->prepare('DELETE FROM catalogs WHERE id = ?')->execute([$id]);
    if ($cat && $cat['file_path']) {
        delete_uploaded_file($cat['file_path'], SITE_ROOT);
    }
    flash_set('success', 'Catalog deleted.');
    redirect('catalog.php');
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM catalogs WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$errors = [];
$values = ['title' => $editing['title'] ?? '', 'description' => $editing['description'] ?? ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_catalog'])) {
    csrf_verify();
    $values['title'] = post('title');
    $values['description'] = post('description');
    $catalogId = (int)($_POST['catalog_id'] ?? 0);

    if ($values['title'] === '') {
        $errors['title'] = 'Catalog title is required.';
    }

    $newFile = null;
    try {
        $newFile = handle_pdf_upload('file', UPLOAD_CATALOG_DIR, UPLOAD_CATALOG_URL);
    } catch (RuntimeException $e) {
        $errors['file'] = $e->getMessage();
    }

    if ($catalogId === 0 && !$newFile && !$errors) {
        $errors['file'] = 'Please choose a PDF file to upload.';
    }

    if (!$errors) {
        if ($catalogId > 0) {
            $filePath = $editing['file_path'];
            if ($newFile) {
                if ($filePath) {
                    delete_uploaded_file($filePath, SITE_ROOT);
                }
                $filePath = $newFile;
            }
            $pdo->prepare("UPDATE catalogs SET title=?, description=?, file_path=?, updated_at=NOW() WHERE id=?")
                ->execute([$values['title'], $values['description'], $filePath, $catalogId]);
            flash_set('success', 'Catalog updated.');
        } else {
            $pdo->prepare('INSERT INTO catalogs (title, description, file_path) VALUES (?, ?, ?)')
                ->execute([$values['title'], $values['description'], $newFile]);
            flash_set('success', 'Catalog uploaded.');
        }
        redirect('catalog.php');
    }
}

$catalogs = $pdo->query('SELECT * FROM catalogs ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Catalog';
require __DIR__ . '/includes/header.php';
?>

<div class="dash-grid" style="grid-template-columns: 1.6fr 1fr;">
  <div class="card" style="padding:0;">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Title</th><th>File</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (!$catalogs): ?>
            <tr class="empty-row"><td colspan="4">No catalog files yet. Upload the first one using the form.</td></tr>
          <?php else: foreach ($catalogs as $c): ?>
            <tr>
              <td><strong><?= e($c['title']) ?></strong><?php if ($c['description']): ?><br><span style="color:rgba(17,24,39,.55); font-size:.82rem;"><?= e(mb_strimwidth($c['description'], 0, 70, '…')) ?></span><?php endif; ?></td>
              <td><a href="<?= e($c['file_path']) ?>" target="_blank" class="btn btn-secondary btn-sm">View PDF</a></td>
              <td><?= e(format_date($c['updated_at'], 'd M Y')) ?></td>
              <td>
                <div class="row-actions">
                  <a href="catalog.php?edit=<?= (int)$c['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                  <button type="button" class="btn btn-danger btn-sm"
                    data-confirm="This will permanently delete &quot;<?= e($c['title']) ?>&quot;."
                    data-confirm-title="Delete this catalog?"
                    data-action="catalog.php"
                    data-field-delete_id="<?= (int)$c['id'] ?>">Delete</button>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom:16px;"><?= $editing ? 'Edit Catalog' : 'Upload Catalog' ?></h3>
    <form method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="save_catalog" value="1">
      <input type="hidden" name="catalog_id" value="<?= (int)($editing['id'] ?? 0) ?>">

      <div class="field">
        <label for="title">Title <span class="req">*</span></label>
        <input type="text" id="title" name="title" value="<?= e($values['title']) ?>" required>
        <?php if (!empty($errors['title'])): ?><span class="field-error"><?= e($errors['title']) ?></span><?php endif; ?>
      </div>
      <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= e($values['description']) ?></textarea>
      </div>
      <div class="field">
        <label>PDF File <?= $editing ? '' : '<span class="req">*</span>' ?></label>
        <?php if ($editing && $editing['file_path']): ?>
          <p style="font-size:.85rem; margin-bottom:8px;"><a href="<?= e($editing['file_path']) ?>" target="_blank">Current file &rarr;</a></p>
        <?php endif; ?>
        <input type="file" name="file" accept="application/pdf">
        <span class="hint">PDF only. Max 20MB. <?= $editing ? 'Leave empty to keep the current file.' : '' ?></span>
        <?php if (!empty($errors['file'])): ?><span class="field-error"><?= e($errors['file']) ?></span><?php endif; ?>
      </div>
      <div class="form-actions" style="border-top:none; padding-top:0;">
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Upload Catalog' ?></button>
        <?php if ($editing): ?><a href="catalog.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
