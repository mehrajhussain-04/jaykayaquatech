<?php
/**
 * Jay Kay Aqua Tech — Contact Form Handler
 * ------------------------------------------------------------------
 * Validates and sanitizes incoming contact form submissions.
 *
 * SETUP NOTES (before going live):
 * 1. Set $recipientEmail below to the real inbox that should receive
 *    enquiries. Do not hardcode credentials for any third-party mail
 *    service in this file — use environment variables or a secrets
 *    manager on the server instead.
 * 2. This file uses PHP's built-in mail() function as a placeholder.
 *    For reliable delivery, most hosts recommend SMTP via a library
 *    such as PHPMailer — swap the mail() call below for that once the
 *    hosting environment is confirmed.
 * 3. Basic honeypot + rate limiting hooks are included but should be
 *    backed by a real store (session, database, or cache) in production.
 * ------------------------------------------------------------------
 */

declare(strict_types=1);

header('Content-Type: application/json');

// Wires this form into the Phase 2 database (contact_messages table) so
// submissions appear in /admin/messages.php. Uses the same centralized
// db-config.php as the rest of the admin — no credentials duplicated here.
require_once __DIR__ . '/../admin/includes/db.php';

$recipientEmail = 'info@example.com'; // TODO: replace with real Jay Kay Aqua Tech inbox

function respond(bool $ok, string $message, array $errors = []): void {
    http_response_code($ok ? 200 : 422);
    echo json_encode(['success' => $ok, 'message' => $message, 'errors' => $errors]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// Honeypot field — real users never fill this in (kept blank + hidden in HTML if added to a form).
if (!empty($_POST['website'] ?? '')) {
    respond(true, 'Thanks for your message.'); // silently accept to not tip off bots
}

$name    = trim((string)($_POST['name'] ?? ''));
$phone   = trim((string)($_POST['phone'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

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
if ($message === '' || mb_strlen($message) < 5) {
    $errors['message'] = 'Please enter a short message.';
}

if (!empty($errors)) {
    respond(false, 'Please correct the highlighted fields.', $errors);
}

// Sanitize for safe output / email body.
$safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safePhone   = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$safeEmail   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Save to the database first — this is the record of truth the admin
// panel reads from, so it must succeed even if email delivery below fails.
$savedToDb = false;
try {
    $stmt = db()->prepare('INSERT INTO contact_messages (name, phone, email, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $phone, $email, $message]);
    $savedToDb = true;
} catch (Throwable $e) {
    error_log('Jay Kay Aqua Tech contact form: database insert failed: ' . $e->getMessage());
}

$subject = 'New Contact Form Submission — Jay Kay Aqua Tech';
$body = "Name: {$safeName}\nPhone: {$safePhone}\nEmail: {$safeEmail}\n\nMessage:\n{$safeMessage}\n";

$headers = [
    'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'jkaquatech.local'),
    'Reply-To: ' . $safeEmail,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8',
];

// Email is a best-effort notification on top of the database record —
// its failure alone should not fail the visitor's submission if the
// message was already safely stored.
$sent = @mail($recipientEmail, $subject, $body, implode("\r\n", $headers));
if (!$sent) {
    error_log('Jay Kay Aqua Tech contact form: mail() failed to send.');
}

if (!$savedToDb && !$sent) {
    // Do not leak server/database details to the client; log server-side instead.
    respond(false, 'Something went wrong sending your message. Please try again shortly.');
}

respond(true, 'Thanks — your message has been received. Our team will get back to you shortly.');
