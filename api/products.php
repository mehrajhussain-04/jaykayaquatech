<?php
/**
 * Public product API.
 * Reads the same MySQL database used by the admin panel so products added
 * through /admin/add-product.php appear on the public website automatically.
 */
declare(strict_types=1);

require_once __DIR__ . '/../admin/includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function asset_path(?string $path): ?string
{
    if (!$path) return null;
    $path = trim((string)$path);
    if ($path === '') return null;
    if (preg_match('#^(?:https?:)?//#i', $path)) return $path;

    // Database values may have been created from the admin folder as
    // ../assets/... or from seed data as assets/.... Normalize slashes and
    // remove any leading relative segments first.
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#^(?:\.\./)+#', '', $path);
    $path = ltrim($path, '/');

    // Build a URL rooted at this installation. This is important when the
    // project folder has been renamed (for example, "jay kay 20"). A plain
    // "assets/..." URL can otherwise be resolved incorrectly from nested
    // pages or deployments.
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/api/products.php'));
    $base = str_replace('\\', '/', dirname($scriptDir));
    $base = rtrim($base, '/');
    return ($base ? $base . '/' : '/') . $path;
}

function decode_json_array(?string $value): array
{
    if (!$value) return [];
    $decoded = json_decode($value, true);
    return is_array($decoded) ? $decoded : [];
}

try {
    $pdo = db();
    $slug = trim((string)($_GET['slug'] ?? ''));
    $category = trim((string)($_GET['category'] ?? ''));

    if ($slug !== '') {
        $stmt = $pdo->prepare('
            SELECT p.id, p.category_id, p.subcategory, p.name, p.slug,
                   p.short_description, p.full_description, p.features,
                   p.specifications, p.applications, p.main_image,
                   c.slug AS category_slug, c.name AS category_name
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            WHERE p.slug = ? AND p.status = \'active\'
            LIMIT 1
        ');
        $stmt->execute([$slug]);
        $p = $stmt->fetch();

        if (!$p) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        $imgStmt = $pdo->prepare('SELECT image_path FROM product_images WHERE product_id = ? ORDER BY id ASC');
        $imgStmt->execute([(int)$p['id']]);
        $images = [];
        foreach ($imgStmt->fetchAll(PDO::FETCH_COLUMN) as $img) {
            $img = asset_path((string)$img);
            if ($img) $images[] = $img;
        }
        $main = asset_path($p['main_image']);
        if ($main && !in_array($main, $images, true)) {
            array_unshift($images, $main);
        }

        echo json_encode([
            'success' => true,
            'product' => [
                'id' => (int)$p['id'],
                'categoryId' => (int)$p['category_id'],
                'categorySlug' => $p['category_slug'],
                'categoryName' => $p['category_name'],
                'subcategory' => $p['subcategory'] ?? '',
                'name' => $p['name'],
                'slug' => $p['slug'],
                'short' => $p['short_description'] ?? '',
                'full' => $p['full_description'] ?? '',
                'features' => decode_json_array($p['features']),
                'specs' => decode_json_array($p['specifications']),
                'applications' => decode_json_array($p['applications']),
                'images' => $images,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = '
        SELECT p.id, p.category_id, p.subcategory, p.name, p.slug,
               p.short_description, p.main_image,
               p.features, p.specifications,
               c.slug AS category_slug, c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE p.status = \'active\'
    ';
    $params = [];
    if ($category !== '') {
        $sql .= ' AND c.slug = ?';
        $params[] = $category;
    }
    $sql .= ' ORDER BY p.created_at DESC, p.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $imagesByProduct = [];
    if ($rows) {
        $ids = array_map(static fn($r) => (int)$r['id'], $rows);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $imgStmt = $pdo->prepare("SELECT product_id, image_path FROM product_images WHERE product_id IN ($placeholders) ORDER BY id ASC");
        $imgStmt->execute($ids);
        foreach ($imgStmt->fetchAll() as $imgRow) {
            $img = asset_path($imgRow['image_path']);
            if ($img) $imagesByProduct[(int)$imgRow['product_id']][] = $img;
        }
    }

    $products = [];
    foreach ($rows as $p) {
        $id = (int)$p['id'];
        $main = asset_path($p['main_image']);
        $images = $imagesByProduct[$id] ?? [];
        if ($main && !in_array($main, $images, true)) array_unshift($images, $main);
        $products[] = [
            'id' => $id,
            'categoryId' => (int)$p['category_id'],
            'categorySlug' => $p['category_slug'],
            'categoryName' => $p['category_name'],
            'subcategory' => $p['subcategory'] ?? '',
            'name' => $p['name'],
            'slug' => $p['slug'],
            'short' => $p['short_description'] ?? '',
            'features' => decode_json_array($p['features']),
            'specs' => decode_json_array($p['specifications']),
            'images' => $images,
        ];
    }

    $catRows = $pdo->query('SELECT slug, name FROM categories ORDER BY name')->fetchAll();
    echo json_encode(['success' => true, 'categories' => $catRows, 'products' => $products], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Jay Kay Aqua Tech public product API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load products right now.']);
}
