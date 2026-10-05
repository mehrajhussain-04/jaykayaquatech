<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Product not found.');
    redirect('products.php');
}

// --- delete a single additional image (small inline POST action) -----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image_id'])) {
    csrf_verify();
    $imgId = (int)$_POST['delete_image_id'];
    $imgStmt = $pdo->prepare('SELECT * FROM product_images WHERE id = ? AND product_id = ?');
    $imgStmt->execute([$imgId, $id]);
    $img = $imgStmt->fetch();
    if ($img) {
        $pdo->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imgId]);
        delete_uploaded_file($img['image_path'], SITE_ROOT);
        flash_set('success', 'Image removed.');
    }
    redirect('edit-product.php?id=' . $id);
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// Existing subcategories per category, used to power the subcategory
// suggestions dropdown below — pulled from real product data already in
// the database so suggestions always match what's actually in use.
$subcatRows = $pdo->query('SELECT category_id, subcategory FROM products WHERE subcategory IS NOT NULL AND subcategory != \'\' GROUP BY category_id, subcategory ORDER BY subcategory')->fetchAll();
$subcatsByCategory = [];
foreach ($subcatRows as $row) {
    $subcatsByCategory[(int)$row['category_id']][] = $row['subcategory'];
}

$errors = [];
$values = [
    'name' => $product['name'],
    'category_id' => (string)$product['category_id'],
    'subcategory' => $product['subcategory'] ?? '',
    'short_description' => $product['short_description'],
    'full_description' => $product['full_description'],
    'features' => array_to_lines(json_decode($product['features'] ?? '[]', true) ?: []),
    'specifications' => specs_to_lines(json_decode($product['specifications'] ?? '[]', true) ?: []),
    'applications' => array_to_lines(json_decode($product['applications'] ?? '[]', true) ?: []),
    'status' => $product['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_image_id'])) {
    csrf_verify();

    foreach (['name', 'category_id', 'subcategory', 'short_description', 'full_description', 'features', 'specifications', 'applications'] as $key) {
        $values[$key] = post($key);
    }
    $values['status'] = in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

    if ($values['name'] === '') {
        $errors['name'] = 'Product name is required.';
    }
    if ($values['category_id'] === '' || !ctype_digit($values['category_id'])) {
        $errors['category_id'] = 'Please choose a category.';
    }
    if ($values['short_description'] === '') {
        $errors['short_description'] = 'A short description is required.';
    }

    $newMainImage = null;
    try {
        $newMainImage = handle_image_upload('main_image', UPLOAD_PRODUCTS_DIR, UPLOAD_PRODUCTS_URL);
    } catch (RuntimeException $e) {
        $errors['main_image'] = $e->getMessage();
    }

    if (!$errors) {
        $mainImagePath = $product['main_image'];
        if ($newMainImage) {
            if ($product['main_image']) {
                delete_uploaded_file($product['main_image'], SITE_ROOT);
            }
            $mainImagePath = $newMainImage;
        }

        $update = $pdo->prepare("
            UPDATE products SET
                category_id = ?, subcategory = ?, name = ?, short_description = ?, full_description = ?,
                features = ?, specifications = ?, applications = ?, main_image = ?, status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $update->execute([
            (int)$values['category_id'],
            $values['subcategory'] !== '' ? $values['subcategory'] : null,
            $values['name'],
            $values['short_description'],
            $values['full_description'],
            json_encode(lines_to_array($values['features']), JSON_UNESCAPED_SLASHES),
            json_encode(lines_to_specs($values['specifications']), JSON_UNESCAPED_SLASHES),
            json_encode(lines_to_array($values['applications']), JSON_UNESCAPED_SLASHES),
            $mainImagePath,
            $values['status'],
            $id,
        ]);

        if (!empty($_FILES['additional_images']['name'][0] ?? '')) {
            $count = count($_FILES['additional_images']['name']);
            $imgStmt = $pdo->prepare('INSERT INTO product_images (product_id, image_path) VALUES (?, ?)');
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['additional_images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $_FILES['__single'] = [
                    'name' => $_FILES['additional_images']['name'][$i],
                    'type' => $_FILES['additional_images']['type'][$i],
                    'tmp_name' => $_FILES['additional_images']['tmp_name'][$i],
                    'error' => $_FILES['additional_images']['error'][$i],
                    'size' => $_FILES['additional_images']['size'][$i],
                ];
                try {
                    $path = handle_image_upload('__single', UPLOAD_PRODUCTS_DIR, UPLOAD_PRODUCTS_URL);
                    if ($path) {
                        $imgStmt->execute([$id, $path]);
                    }
                } catch (RuntimeException $e) {
                    flash_set('error', 'One extra image was skipped: ' . $e->getMessage());
                }
            }
        }

        flash_set('success', 'Product "' . $values['name'] . '" was updated.');
        redirect('edit-product.php?id=' . $id);
    }
}

$extraImages = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY created_at');
$extraImages->execute([$id]);
$extraImages = $extraImages->fetchAll();

$pageTitle = 'Edit Product';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div><p><a href="products.php">&larr; Back to products</a></p></div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
    <span>Please fix the highlighted fields below.</span>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate class="card">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$id ?>">
  <div class="form-grid">
    <div class="field full">
      <label for="name">Product Name <span class="req">*</span></label>
      <input type="text" id="name" name="name" value="<?= e($values['name']) ?>" required>
      <?php if (!empty($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
    </div>

    <div class="field">
      <label for="category_id">Category <span class="req">*</span></label>
      <select id="category_id" name="category_id" required>
        <option value="">Choose a category…</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $values['category_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($errors['category_id'])): ?><span class="field-error"><?= e($errors['category_id']) ?></span><?php endif; ?>
    </div>

    <div class="field">
      <label for="subcategory">Subcategory</label>
      <input type="text" id="subcategory" name="subcategory" list="subcategoryOptions" value="<?= e($values['subcategory']) ?>" placeholder="e.g. Paddle Wheel Aerator">
      <datalist id="subcategoryOptions"></datalist>
      <span class="hint">Optional. Suggestions update based on the category chosen above — type a new one if needed.</span>
    </div>

    <div class="field">
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="active" <?= $values['status'] === 'active' ? 'selected' : '' ?>>Active (visible)</option>
        <option value="inactive" <?= $values['status'] === 'inactive' ? 'selected' : '' ?>>Inactive (hidden)</option>
      </select>
    </div>

    <div class="field full">
      <label for="short_description">Short Description <span class="req">*</span></label>
      <input type="text" id="short_description" name="short_description" value="<?= e($values['short_description']) ?>" required>
      <?php if (!empty($errors['short_description'])): ?><span class="field-error"><?= e($errors['short_description']) ?></span><?php endif; ?>
    </div>

    <div class="field full">
      <label for="full_description">Full Description / Overview</label>
      <textarea id="full_description" name="full_description"><?= e($values['full_description']) ?></textarea>
    </div>

    <div class="field full">
      <label for="features">Features</label>
      <textarea id="features" name="features"><?= e($values['features']) ?></textarea>
      <span class="hint">One feature per line.</span>
    </div>

    <div class="field full">
      <label for="specifications">Specifications</label>
      <textarea id="specifications" name="specifications"><?= e($values['specifications']) ?></textarea>
      <span class="hint">One spec per line, formatted as <code>Label: Value</code>.</span>
    </div>

    <div class="field full">
      <label for="applications">Applications</label>
      <textarea id="applications" name="applications"><?= e($values['applications']) ?></textarea>
      <span class="hint">One application per line.</span>
    </div>

    <div class="field">
      <label>Main Image</label>
      <div class="thumb-preview" id="mainImagePreview">
        <?php if ($product['main_image']): ?>
          <img src="<?= e($product['main_image']) ?>" alt="">
        <?php else: ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/></svg>
        <?php endif; ?>
      </div>
      <input type="file" name="main_image" accept="image/png,image/jpeg,image/webp,image/gif" data-preview="mainImagePreview">
      <span class="hint">Uploading a new file replaces the current image.</span>
      <?php if (!empty($errors['main_image'])): ?><span class="field-error"><?= e($errors['main_image']) ?></span><?php endif; ?>
    </div>

    <div class="field">
      <label>Additional Images</label>
      <?php if ($extraImages): ?>
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
          <?php foreach ($extraImages as $img): ?>
            <div style="position:relative;">
              <img src="<?= e($img['image_path']) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
              <form method="POST" style="position:absolute; top:-8px; right:-8px;">
                <?= csrf_field() ?>
                <input type="hidden" name="delete_image_id" value="<?= (int)$img['id'] ?>">
                <button type="submit" title="Remove image" style="width:20px;height:20px;border-radius:50%;background:#B91C1C;color:#fff;border:2px solid #fff;font-size:12px;line-height:1;cursor:pointer;">&times;</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <input type="file" name="additional_images[]" accept="image/png,image/jpeg,image/webp,image/gif" multiple>
      <span class="hint">Add more photos. Existing ones can be removed above.</span>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Save Changes</button>
    <a href="products.php" class="btn btn-secondary">Cancel</a>
  </div>
</form>

<script>
(function(){
  var SUBCATS_BY_CATEGORY = <?= json_encode($subcatsByCategory, JSON_UNESCAPED_SLASHES) ?>;
  var categorySelect = document.getElementById('category_id');
  var datalist = document.getElementById('subcategoryOptions');
  function refresh(){
    var subs = SUBCATS_BY_CATEGORY[categorySelect.value] || [];
    datalist.innerHTML = subs.map(function(s){ return '<option value="' + s.replace(/"/g,'&quot;') + '">'; }).join('');
  }
  categorySelect.addEventListener('change', refresh);
  refresh();
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
