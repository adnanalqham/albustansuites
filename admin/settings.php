<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'settings';
$adminPageTitle = 'admin_site_settings';
if(($_SESSION[ADMIN_SESSION_NAME]['role'] ?? '') !== 'super_admin') { die('Access Denied'); }
// Initialize hero settings and site logo if not exist
$heroKeys = [
    'site_logo' => ['value'=>'images/logo.jpg', 'type'=>'image', 'group'=>'general', 'label_en'=>'Site Logo (PNG/JPG)'],
    'hero_slide1_image' => ['value'=>'images/hero-1.jpg', 'type'=>'image', 'group'=>'hero', 'label_en'=>'Slide 1 Image'],
    'hero_slide1_title_ar' => ['value'=>'جناح فاخر بإطلالة بانورامية', 'type'=>'string', 'group'=>'hero', 'label_en'=>'Slide 1 Title (AR)'],
    'hero_slide1_title_en' => ['value'=>'Panoramic Luxury Suite', 'type'=>'string', 'group'=>'hero', 'label_en'=>'Slide 1 Title (EN)'],
    'hero_slide2_image' => ['value'=>'images/hero-2.jpg', 'type'=>'image', 'group'=>'hero', 'label_en'=>'Slide 2 Image'],
    'hero_slide2_title_ar' => ['value'=>'مطعم البستان الفاخر', 'type'=>'string', 'group'=>'hero', 'label_en'=>'Slide 2 Title (AR)'],
    'hero_slide2_title_en' => ['value'=>'Al Bustan Fine Dining', 'type'=>'string', 'group'=>'hero', 'label_en'=>'Slide 2 Title (EN)'],
    'hero_slide3_image' => ['value'=>'images/hero-3.jpg', 'type'=>'image', 'group'=>'hero', 'label_en'=>'Slide 3 Image'],
    'hero_slide3_title_ar' => ['value'=>'قاعات اجتماعات عالمية المستوى', 'type'=>'string', 'group'=>'hero', 'label_en'=>'Slide 3 Title (AR)'],
    'hero_slide3_title_en' => ['value'=>'World-Class Meeting Halls', 'type'=>'string', 'group'=>'hero', 'label_en'=>'Slide 3 Title (EN)'],
];
foreach($heroKeys as $k => $v) {
    $db->prepare("INSERT IGNORE INTO settings (`key`,`value`,`type`,`group`,`label_en`,`label_ar`) VALUES (?,?,?,?,?,?)")
       ->execute([$k, $v['value'], $v['type'], $v['group'], $v['label_en'], $v['label_en']]);
}

// Update settings
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_settings'])) {
    $stmt = $db->prepare("UPDATE settings SET value=? WHERE `key`=?");
    
    // Normal text/boolean settings
    if(isset($_POST['setting'])) {
        foreach($_POST['setting'] as $key => $value) {
            $stmt->execute([sanitize($value), $key]);
        }
    }
    
    // Image uploads
    if(isset($_FILES['setting_file'])) {
        foreach($_FILES['setting_file']['tmp_name'] as $key => $tmp) {
            if(!empty($tmp)) {
                // Reconstruct a single file array for uploadImage()
                $file = [
                    'name' => $_FILES['setting_file']['name'][$key],
                    'type' => $_FILES['setting_file']['type'][$key],
                    'tmp_name' => $tmp,
                    'error' => $_FILES['setting_file']['error'][$key],
                    'size' => $_FILES['setting_file']['size'][$key]
                ];
                $uploadedPath = uploadImage($file, 'hero');
                if($uploadedPath) {
                    $stmt->execute([$uploadedPath, $key]);
                }
            }
        }
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
<form method="POST" class="admin-form" enctype="multipart/form-data">
  <input type="hidden" name="save_settings" value="1">
  <?php foreach($grouped as $group => $items): ?>
  <div class="card">
    <div class="card-header"><span class="card-title"><?= ucfirst($group) ?> <?= t('admin_settings_group') ?></span></div>
    <?php foreach($items as $s): ?>
    <div class="form-group">
      <label><?= e(getLang() === 'ar' && !empty($s['label_ar']) && $s['label_ar'] !== $s['label_en'] ? $s['label_ar'] : ($s['label_en'] ?? $s['key'])) ?></label>
      <?php if($s['type']==='textarea'): ?>
      <textarea name="setting[<?= e($s['key']) ?>]" rows="3"><?= e($s['value']??'') ?></textarea>
      <?php elseif($s['type']==='boolean'): ?>
      <select name="setting[<?= e($s['key']) ?>]"><option value="1" <?= $s['value']==='1'?'selected':'' ?>><?= t('yes') ?></option><option value="0" <?= $s['value']!=='1'?'selected':'' ?>><?= t('no') ?></option></select>
      <?php elseif($s['type']==='image'): ?>
      <?php if(!empty($s['value'])): ?>
          <img src="<?= SITE_URL ?>/<?= e($s['value']) ?>" style="max-height:80px; display:block; margin-bottom:10px; border-radius:4px; box-shadow:0 2px 5px rgba(0,0,0,0.2);">
      <?php endif; ?>
      <input type="file" name="setting_file[<?= e($s['key']) ?>]" accept="image/*">
      <?php else: ?>
      <input type="<?= $s['type']==='number'?'number':'text' ?>" name="setting[<?= e($s['key']) ?>]" value="<?= e($s['value']??'') ?>">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  <div style="position:sticky;bottom:0;background:var(--admin-card);border-top:1px solid var(--admin-border);padding:16px;margin-top:24px;">
    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> <?= t('admin_save_all_settings') ?></button>
  </div>
</form>
</div></div></div></body></html>
