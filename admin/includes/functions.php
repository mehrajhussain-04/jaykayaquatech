<?php
/**
 * Shared helper functions used across the admin panel.
 */

declare(strict_types=1);

// --- CSRF protection --------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Security check failed (invalid or expired form token). Please go back and try again.');
    }
}

// --- output escaping ---------------------------------------------------------

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// --- flash messages (one-shot, survive a redirect) --------------------------

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_all(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

// --- misc helpers -------------------------------------------------------------

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function post(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

function lines_to_array(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
    return array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));
}

function array_to_lines(array $items): string
{
    return implode("\n", $items);
}

/**
 * Specs are stored as [[label, value], ...]. In a textarea we show them
 * as "Label: Value" per line for easy editing.
 */
function specs_to_lines(array $specs): string
{
    $lines = [];
    foreach ($specs as $row) {
        if (is_array($row) && count($row) === 2) {
            $lines[] = $row[0] . ': ' . $row[1];
        }
    }
    return implode("\n", $lines);
}

function lines_to_specs(string $text): array
{
    $out = [];
    foreach (lines_to_array($text) as $line) {
        $parts = explode(':', $line, 2);
        if (count($parts) === 2) {
            $out[] = [trim($parts[0]), trim($parts[1])];
        } else {
            $out[] = [trim($parts[0]), ''];
        }
    }
    return $out;
}

function format_date(?string $sqlDate, string $fmt = 'd M Y, g:i A'): string
{
    if (!$sqlDate) {
        return '—';
    }
    $ts = strtotime($sqlDate);
    return $ts ? date($fmt, $ts) : '—';
}

// --- file upload validation --------------------------------------------------

/**
 * Validates and moves an uploaded image. Returns the relative URL to store
 * in the database, or null if no file was submitted. Throws RuntimeException
 * with a user-facing message on validation failure.
 */
function handle_image_upload(string $fieldName, string $destDir, string $destUrlBase): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed (error code ' . $file['error'] . ').');
    }
    if ($file['size'] > MAX_IMAGE_BYTES) {
        throw new RuntimeException('Image is too large. Maximum size is 5MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Unsupported image type. Please upload a JPG, PNG, WEBP or GIF file.');
    }

    // Re-validate with getimagesize as a second check against disguised files.
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new RuntimeException('The uploaded file does not look like a valid image.');
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }

    $ext = $allowed[$mime];
    $filename = 'img-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = rtrim($destDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }
    @chmod($destPath, 0644);

    return rtrim($destUrlBase, '/') . '/' . $filename;
}

/**
 * Validates and moves an uploaded PDF catalog file.
 */
function handle_pdf_upload(string $fieldName, string $destDir, string $destUrlBase): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed (error code ' . $file['error'] . ').');
    }
    if ($file['size'] > MAX_PDF_BYTES) {
        throw new RuntimeException('File is too large. Maximum size is 20MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if ($mime !== 'application/pdf') {
        throw new RuntimeException('Only PDF files are allowed for catalogs.');
    }

    // Basic signature check: PDFs start with "%PDF-".
    $handle = fopen($file['tmp_name'], 'rb');
    $header = $handle ? fread($handle, 5) : '';
    if ($handle) {
        fclose($handle);
    }
    if ($header !== '%PDF-') {
        throw new RuntimeException('The uploaded file does not look like a valid PDF.');
    }

    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }

    $filename = 'catalog-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.pdf';
    $destPath = rtrim($destDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }
    @chmod($destPath, 0644);

    return rtrim($destUrlBase, '/') . '/' . $filename;
}

function delete_uploaded_file(?string $relativeUrl, string $siteRoot): void
{
    if (!$relativeUrl) {
        return;
    }
    // relativeUrl looks like "../assets/images/products/xyz.jpg" from the
    // admin folder's perspective — resolve it against SITE_ROOT instead.
    $clean = preg_replace('#^\.\./#', '', $relativeUrl);
    $path = rtrim($siteRoot, '/') . '/' . ltrim((string)$clean, '/');
    $real = realpath($path);
    $siteRootReal = realpath($siteRoot);
    // Safety: only delete files that resolve inside the site root.
    if ($real && $siteRootReal && str_starts_with($real, $siteRootReal) && is_file($real)) {
        @unlink($real);
    }
}
