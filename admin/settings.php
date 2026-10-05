<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';
require_login();

$pdo = db();
$admin = current_admin();

$settingsKeys = ['company_email', 'company_phone', 'whatsapp_number', 'company_address', 'facebook_url', 'instagram_url', 'youtube_url', 'linkedin_url'];

// --- save site settings ------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    csrf_verify();
    $upsert = $pdo->prepare('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)');
    foreach ($settingsKeys as $key) {
        $upsert->execute([$key, post($key)]);
    }
    flash_set('success', 'Settings saved.');
    redirect('settings.php');
}

// --- change password -----------------------------------------------------------
$pwErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    csrf_verify();
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $stmt->execute([$admin['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        $pwErrors['current_password'] = 'Current password is incorrect.';
    }
    if (strlen($new) < 8) {
        $pwErrors['new_password'] = 'New password must be at least 8 characters.';
    }
    if ($new !== $confirm) {
        $pwErrors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$pwErrors) {
        $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
        flash_set('success', 'Password changed successfully.');
        redirect('settings.php');
    }
}

$rows = $pdo->query('SELECT skey, svalue FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Settings';
require __DIR__ . '/includes/header.php';
?>

<div class="dash-grid" style="grid-template-columns: 1fr 1fr;">
  <div class="card">
    <h3 style="margin-bottom:6px;">Site Contact Information</h3>
    <p style="color:rgba(17,24,39,.6); font-size:.88rem; margin-bottom:18px;">These are stored for your own reference and future use. Leave blank if not yet confirmed — nothing here is auto-published to the public site.</p>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="save_settings" value="1">
      <div class="field"><label for="company_email">Company Email</label><input type="email" id="company_email" name="company_email" value="<?= e($rows['company_email'] ?? '') ?>"></div>
      <div class="field"><label for="company_phone">Company Phone</label><input type="text" id="company_phone" name="company_phone" value="<?= e($rows['company_phone'] ?? '') ?>"></div>
      <div class="field"><label for="whatsapp_number">WhatsApp Number</label><input type="text" id="whatsapp_number" name="whatsapp_number" value="<?= e($rows['whatsapp_number'] ?? '') ?>"></div>
      <div class="field"><label for="company_address">Address</label><textarea id="company_address" name="company_address"><?= e($rows['company_address'] ?? '') ?></textarea></div>
      <div class="field"><label for="facebook_url">Facebook URL</label><input type="text" id="facebook_url" name="facebook_url" value="<?= e($rows['facebook_url'] ?? '') ?>"></div>
      <div class="field"><label for="instagram_url">Instagram URL</label><input type="text" id="instagram_url" name="instagram_url" value="<?= e($rows['instagram_url'] ?? '') ?>"></div>
      <div class="field"><label for="youtube_url">YouTube URL</label><input type="text" id="youtube_url" name="youtube_url" value="<?= e($rows['youtube_url'] ?? '') ?>"></div>
      <div class="field"><label for="linkedin_url">LinkedIn URL</label><input type="text" id="linkedin_url" name="linkedin_url" value="<?= e($rows['linkedin_url'] ?? '') ?>"></div>
      <div class="form-actions" style="border-top:none; padding-top:0;">
        <button type="submit" class="btn btn-primary">Save Settings</button>
      </div>
    </form>
  </div>

  <div class="card">
    <h3 style="margin-bottom:6px;">Change Password</h3>
    <p style="color:rgba(17,24,39,.6); font-size:.88rem; margin-bottom:18px;">Signed in as <strong><?= e($admin['email']) ?></strong>.</p>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="change_password" value="1">
      <div class="field">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
        <?php if (!empty($pwErrors['current_password'])): ?><span class="field-error"><?= e($pwErrors['current_password']) ?></span><?php endif; ?>
      </div>
      <div class="field">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" autocomplete="new-password" required>
        <span class="hint">At least 8 characters.</span>
        <?php if (!empty($pwErrors['new_password'])): ?><span class="field-error"><?= e($pwErrors['new_password']) ?></span><?php endif; ?>
      </div>
      <div class="field">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
        <?php if (!empty($pwErrors['confirm_password'])): ?><span class="field-error"><?= e($pwErrors['confirm_password']) ?></span><?php endif; ?>
      </div>
      <div class="form-actions" style="border-top:none; padding-top:0;">
        <button type="submit" class="btn btn-primary">Change Password</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
