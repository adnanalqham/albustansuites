<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'settings';
$adminPageTitle = 'Site Settings';

// Update settings
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_settings'])) {
    $stmt = $db->prepare("UPDATE settings SET value=? WHERE `key`=?");
    foreach($_POST['setting'] as $key => $value) {
        $stmt->execute([sanitize($value), $key]);
    }
    setFlash('success','Settings saved successfully!');
    header('Location: settings.php'); exit;
}

$settings = $db->query("SELECT * FROM settings ORDER BY `group`, id")->fetchAll();
$grouped  = [];
foreach($settings as $s) { $grouped[$s['group']][] = $s; }

require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash(); if($f): ?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif; ?>
<form method="POST" class="admin-form">
  <input type="hidden" name="save_settings" value="1">
  <?php foreach($grouped as $group => $items): ?>
  <div class="card">
    <div class="card-header"><span class="card-title"><?= ucfirst($group) ?> Settings</span></div>
    <?php foreach($items as $s): ?>
    <div class="form-group">
      <label><?= e($s['label_en'] ?? $s['key']) ?></label>
      <?php if($s['type']==='textarea'): ?>
      <textarea name="setting[<?= e($s['key']) ?>]" rows="3"><?= e($s['value']??'') ?></textarea>
      <?php elseif($s['type']==='boolean'): ?>
      <select name="setting[<?= e($s['key']) ?>]"><option value="1" <?= $s['value']==='1'?'selected':'' ?>>Yes</option><option value="0" <?= $s['value']!=='1'?'selected':'' ?>>No</option></select>
      <?php else: ?>
      <input type="<?= $s['type']==='number'?'number':'text' ?>" name="setting[<?= e($s['key']) ?>]" value="<?= e($s['value']??'') ?>">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  <div style="position:sticky;bottom:0;background:var(--admin-card);border-top:1px solid var(--admin-border);padding:16px;margin-top:24px;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save All Settings</button>
  </div>
</form>
</div></div></div></body></html>
