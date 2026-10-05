<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();

// --- handle delete ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $id = (int)$_POST['delete_id'];

    $countStmt = $pdo->prepare('SELECT COUNT(*) c FROM products WHERE category_id = ?');
    $countStmt->execute([$id]);
    $inUse = (int)$countStmt->fetch()['c'];

    if ($inUse > 0) {
        flash_set('error', "Can't delete this category — it still has {$inUse} product(s) in it. Move or delete those products first.");
    } else {
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        flash_set('success', 'Category deleted.');
    }
    redirect('categories.php');
}

// --- handle add/edit (shared form submitted to this same page) ---------------
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$errors = [];
$values = ['name' => $editing['name'] ?? '', 'description' => $editing['description'] ?? ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    csrf_verify();
    $values['name'] = post('name');
    $values['description'] = post('description');
    $catId = (int)($_POST['category_id'] ?? 0);

    if ($values['name'] === '') {
        $errors['name'] = 'Category name is required.';
    }

    if (!$errors) {
        $slug = slugify($values['name']);
        $checkStmt = $pdo->prepare('SELECT COUNT(*) c FROM categories WHERE slug = ? AND id != ?');
        $checkStmt->execute([$slug, $catId]);
        if ((int)$checkStmt->fetch()['c'] > 0) {
            $slug .= '-' . bin2hex(random_bytes(2));
        }

        if ($catId > 0) {
            $pdo->prepare('UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?')
                ->execute([$values['name'], $slug, $values['description'], $catId]);
            flash_set('success', 'Category updated.');
        } else {
            $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)')
                ->execute([$values['name'], $slug, $values['description']]);
            flash_set('success', 'Category added.');
        }
        redirect('categories.php');
    }
}

$categories = $pdo->query('
    SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
    FROM categories c ORDER BY c.name
')->fetchAll();

$pageTitle = 'Categories';
require __DIR__ . '/includes/header.php';
?>

<div class="dash-grid" style="grid-template-columns: 1.6fr 1fr;">
  <div class="card" style="padding:0;">
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Category</th><th>Slug</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (!$categories): ?>
            <tr class="empty-row"><td colspan="4">No categories yet.</td></tr>
          <?php else: foreach ($categories as $c): ?>
            <tr>
              <td><strong><?= e($c['name']) ?></strong><?php if ($c['description']): ?><br><span style="color:rgba(17,24,39,.55); font-size:.82rem;"><?= e(mb_strimwidth($c['description'], 0, 70, '…')) ?></span><?php endif; ?></td>
              <td><code><?= e($c['slug']) ?></code></td>
              <td><span class="badge badge-sky"><?= (int)$c['product_count'] ?></span></td>
              <td>
                <div class="row-actions">
                  <a href="categories.php?edit=<?= (int)$c['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                  <button type="button" class="btn btn-danger btn-sm"
                    data-confirm="<?= $c['product_count'] > 0 ? 'This category has ' . (int)$c['product_count'] . ' product(s) and cannot be deleted until they are moved or removed.' : 'This will permanently delete the "' . e($c['name']) . '" category.' ?>"
                    data-confirm-title="Delete this category?"
                    data-action="categories.php"
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
    <h3 style="margin-bottom:16px;"><?= $editing ? 'Edit Category' : 'Add Category' ?></h3>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="save_category" value="1">
      <input type="hidden" name="category_id" value="<?= (int)($editing['id'] ?? 0) ?>">
      <div class="field">
        <label for="name">Name <span class="req">*</span></label>
        <input type="text" id="name" name="name" value="<?= e($values['name']) ?>" required>
        <?php if (!empty($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
      </div>
      <div class="field">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= e($values['description']) ?></textarea>
      </div>
      <div class="form-actions" style="border-top:none; padding-top:0;">
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Add Category' ?></button>
        <?php if ($editing): ?><a href="categories.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
