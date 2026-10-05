<?php
/**
 * Jay Kay Aqua Tech — Product Enquiry Form Handler
 * ------------------------------------------------------------------
 * Validates and sanitizes product enquiry submissions from
 * contact.html and product-details.html.
 *
 * SETUP NOTES (before going live):
 * 1. Set $recipientEmail below to the real sales/enquiry inbox.
 * 2. Swap the mail() placeholder for SMTP delivery (e.g. PHPMailer)
 *    once hosting is confirmed. Never hardcode SMTP credentials in
 *    this file — use environment variables instead.
 * 3. Consider logging enquiries to a database table for a simple
 *    admin view, in addition to (or instead of) email delivery.
 * ------------------------------------------------------------------
 */

declare(strict_types=1);

header('Content-Type: application/json');

// Wires this form into the Phase 2 database (enquiries table) so
// submissions appear in /admin/enquiries.php. Uses the same centralized
// db-config.php as the rest of the admin — no credentials duplicated here.
require_once __DIR__ . '/../admin/includes/db.php';

$recipientEmail = 'sales@example.com'; // TODO: replace with real Jay Kay Aqua Tech sales inbox

function respond(bool $ok, string $message, array $errors = []): void {
    http_response_code($ok ? 200 : 422);
    echo json_encode(['success' => $ok, 'message' => $message, 'errors' => $errors]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// Honeypot field.
if (!empty($_POST['website'] ?? '')) {
    respond(true, 'Thanks for your enquiry.');
}

$name     = trim((string)($_POST['name'] ?? ''));
$phone    = trim((string)($_POST['phone'] ?? ''));
$email    = trim((string)($_POST['email'] ?? ''));
$product  = trim((string)($_POST['product'] ?? 'General enquiry'));
$quantity = trim((string)($_POST['quantity'] ?? ''));
$message  = trim((string)($_POST['message'] ?? ''));

$errors = [];

if ($name === '' || mb_strlen($name) < 2) {
    $errors['name'] = 'Please enter your full name.';
}
if ($phone === '' || !preg_match('/^[0-9+\-\s()]{7,16}$/', $phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}
if ($quantity !== '' && (!ctype_digit($quantity) || (int)$quantity < 1)) {
    $errors['quantity'] = 'Quantity must be a positive whole number.';
}
if ($message === '' || mb_strlen($message) < 5) {
    $errors['message'] = 'Please enter a short message describing what you need.';
}

if (!empty($errors)) {
    respond(false, 'Please correct the highlighted fields.', $errors);
}

$safeName     = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safePhone    = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$safeEmail    = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safeProduct  = htmlspecialchars($product, ENT_QUOTES, 'UTF-8');
$safeQuantity = htmlspecialchars($quantity !== '' ? $quantity : 'Not specified', ENT_QUOTES, 'UTF-8');
$safeMessage  = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Save to the database first — this is the record of truth the admin
// panel reads from, so it must succeed even if email delivery below fails.
$savedToDb = false;
try {
    $stmt = db()->prepare('INSERT INTO enquiries (name, phone, email, product, quantity, message) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $phone, $email, $product !== '' ? $product : null, $quantity !== '' ? $quantity : null, $message]);
    $savedToDb = true;
} catch (Throwable $e) {
    error_log('Jay Kay Aqua Tech enquiry form: database insert failed: ' . $e->getMessage());
}

$subject = 'New Product Enquiry — Jay Kay Aqua Tech';
$body = "Name: {$safeName}\nPhone: {$safePhone}\nEmail: {$safeEmail}\n"
      . "Product: {$safeProduct}\nQuantity: {$safeQuantity}\n\nMessage:\n{$safeMessage}\n";

$headers = [
    'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'jkaquatech.local'),
    'Reply-To: ' . $safeEmail,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8',
];

// Email is a best-effort notification on top of the database record —
// its failure alone should not fail the visitor's submission if the
// enquiry was already safely stored.
$sent = @mail($recipientEmail, $subject, $body, implode("\r\n", $headers));
if (!$sent) {
    error_log('Jay Kay Aqua Tech enquiry form: mail() failed to send.');
}

if (!$savedToDb && !$sent) {
    respond(false, 'Something went wrong sending your enquiry. Please try again shortly.');
}

respond(true, 'Thanks — your enquiry has been received. Our team will get back to you shortly.');
