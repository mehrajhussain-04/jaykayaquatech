<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();

// --- handle delete (POST from the shared confirm modal) --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    csrf_verify();
    $id = (int)$_POST['delete_id'];

    $imgStmt = $pdo->prepare('SELECT main_image FROM products WHERE id = ?');
    $imgStmt->execute([$id]);
    $prod = $imgStmt->fetch();

    $extraImgs = $pdo->prepare('SELECT image_path FROM product_images WHERE product_id = ?');
    $extraImgs->execute([$id]);
    $extraPaths = $extraImgs->fetchAll(PDO::FETCH_COLUMN);

    $del = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $del->execute([$id]);

    if ($prod && $prod['main_image']) {
        delete_uploaded_file($prod['main_image'], SITE_ROOT);
    }
    foreach ($extraPaths as $p) {
        delete_uploaded_file($p, SITE_ROOT);
    }

    flash_set('success', 'Product deleted.');
    redirect('products.php');
}

// --- filters -----------------------------------------------------------------
$search = trim((string)($_GET['q'] ?? ''));
$catFilter = trim((string)($_GET['category'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));

$sql = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND p.name LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($catFilter !== '') {
    $sql .= ' AND c.slug = ?';
    $params[] = $catFilter;
}
if ($statusFilter !== '') {
    $sql .= ' AND p.status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT slug, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Products';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div><p><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> found</p></div>
  <a href="add-product.php" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
    Add Product
  </a>
</div>

<div class="filters-bar">
  <form method="GET">
    <input type="search" name="q" placeholder="Search products…" value="<?= e($search) ?>">
    <select name="category" onchange="this.form.submit()">
      <option value="">All Categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= e($c['slug']) ?>" <?= $catFilter === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
      <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <?php if ($search || $catFilter || $statusFilter): ?>
      <a href="products.php" class="btn btn-secondary btn-sm">Clear</a>
    <?php endif; ?>
  </form>
</div>

<div class="card" style="padding:0;">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Product</th>
          <th>Category</th>
          <th>Subcategory</th>
          <th>Status</th>
          <th>Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$products): ?>
          <tr class="empty-row"><td colspan="7">No products match your filters.</td></tr>
        <?php else: foreach ($products as $p): ?>
          <tr>
            <td>
              <?php if ($p['main_image']): ?>
                <img class="cell-thumb" src="<?= e($p['main_image']) ?>" alt="">
              <?php else: ?>
                <div class="cell-thumb" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/></svg></div>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= e($p['name']) ?></strong>
              <?php if ($p['is_demo']): ?><br><span class="badge badge-neutral" style="margin-top:4px;">Demo data</span><?php endif; ?>
            </td>
            <td><?= e($p['category_name']) ?></td>
            <td><?= $p['subcategory'] ? e($p['subcategory']) : '<span style="color:#9CA3AF;">—</span>' ?></td>
            <td>
              <?php if ($p['status'] === 'active'): ?>
                <span class="badge badge-success">Active</span>
              <?php else: ?>
                <span class="badge badge-neutral">Inactive</span>
              <?php endif; ?>
            </td>
            <td><?= e(format_date($p['updated_at'], 'd M Y')) ?></td>
            <td>
              <div class="row-actions">
                <a href="edit-product.php?id=<?= (int)$p['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                <button type="button" class="btn btn-danger btn-sm"
                  data-confirm="This will permanently delete &quot;<?= e($p['name']) ?>&quot; and its images."
                  data-confirm-title="Delete this product?"
                  data-action="products.php"
                  data-field-delete_id="<?= (int)$p['id'] ?>">Delete</button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
