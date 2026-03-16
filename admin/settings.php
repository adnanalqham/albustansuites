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
    
    // Restaurant Group
    'restaurant_image' => ['value'=>'images/restaurant.jpg', 'type'=>'image', 'group'=>'restaurant', 'label_en'=>'Restaurant Side Image'],
    'restaurant_hero_image' => ['value'=>'images/restaurant-hero.jpg', 'type'=>'image', 'group'=>'restaurant', 'label_en'=>'Restaurant Page Hero Image'],
    'restaurant_title_ar' => ['value'=>'مطعم البستان', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Title (AR)'],
    'restaurant_title_en' => ['value'=>'Al Bustan Restaurant', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Title (EN)'],
    'restaurant_subtitle_ar' => ['value'=>'تجربة طعام استثنائية', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Subtitle (AR)'],
    'restaurant_subtitle_en' => ['value'=>'An Exceptional Dining Experience', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Subtitle (EN)'],
    'restaurant_desc_ar' => ['value'=>'تذوق أشهى الأطباق العربية والعالمية في أجواء فاخرة تجمع بين الأصالة والمعاصرة', 'type'=>'textarea', 'group'=>'restaurant', 'label_en'=>'Description (AR)'],
    'restaurant_desc_en' => ['value'=>'Taste the finest Arabic and international dishes in a luxury atmosphere combining authenticity and modernity', 'type'=>'textarea', 'group'=>'restaurant', 'label_en'=>'Description (EN)'],
    'restaurant_breakfast' => ['value'=>'6:30 AM - 10:30 AM', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Breakfast Hours'],
    'restaurant_lunch' => ['value'=>'12:30 PM - 3:00 PM', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Lunch Hours'],
    'restaurant_dinner' => ['value'=>'7:00 PM - 11:00 PM', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Dinner Hours'],
    'restaurant_service' => ['value'=>'24 Hours', 'type'=>'string', 'group'=>'restaurant', 'label_en'=>'Room Service Hours'],
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
<style>
.settings-layout { display: flex; gap: 24px; align-items: flex-start; }
.settings-sidebar {
  width: 260px; flex-shrink: 0; background: var(--admin-card);
  border-radius: 12px; overflow: hidden; border: 1px solid var(--admin-border);
  position: sticky; top: 24px;
}
.settings-tab-btn {
  display: flex; align-items: center; justify-content: space-between;
  width: 100%; text-align: start; padding: 18px 20px;
  background: transparent; border: none; border-bottom: 1px solid var(--admin-border);
  color: var(--gray); font-size: 15px; cursor: pointer; transition: all 0.2s;
  font-family: inherit;
}
.settings-tab-btn:last-child { border-bottom: none; }
.settings-tab-btn:hover { background: rgba(255,255,255,0.02); color: var(--cream); }
.settings-tab-btn.active { background: var(--gold); color: var(--dark); font-weight: bold; border-right: 4px solid #bfa63d; }
.rtl .settings-tab-btn.active { border-right: none; border-left: 4px solid #bfa63d; }
.settings-content-wrap { flex-grow: 1; max-width: 100%; overflow: hidden; }
.settings-tab-pane { display: none; }
.settings-tab-pane.active { display: block; animation: fadeIn 0.3s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 768px) {
  .settings-layout { flex-direction: column; }
  .settings-sidebar { width: 100%; display: flex; overflow-x: auto; white-space: nowrap; position: relative; top: 0; }
  .settings-tab-btn { width: auto; flex: 0 0 auto; border-bottom: none; border-right: 1px solid var(--admin-border); border-radius:0 !important; }
  .settings-tab-btn:last-child { border-right: none; }
  .settings-tab-btn.active { border: none !important; border-bottom: 4px solid var(--gold) !important; }
  .rtl .settings-tab-btn.active { border: none !important; border-bottom: 4px solid var(--gold) !important; }
}
</style>

<form method="POST" class="admin-form" enctype="multipart/form-data">
  <input type="hidden" name="save_settings" value="1">
  
  <div class="settings-layout">
    <!-- Sidebar Tabs -->
    <div class="settings-sidebar">
      <?php $i=0; foreach($grouped as $group => $items): ?>
      <button type="button" class="settings-tab-btn <?= $i===0?'active':'' ?>" onclick="openSettingsTab(event, 'tab-<?= e($group) ?>')">
        <span><?= ucfirst($group) ?> <?= t('admin_settings_group') ?></span>
        <i class="fas fa-chevron-<?= getLang()==='ar'?'left':'right' ?>" style="font-size:12px;opacity:0.5;"></i>
      </button>
      <?php $i++; endforeach; ?>
    </div>
    
    <!-- Tab Panes -->
    <div class="settings-content-wrap">
      <?php $i=0; foreach($grouped as $group => $items): ?>
      <div id="tab-<?= e($group) ?>" class="settings-tab-pane <?= $i===0?'active':'' ?>">
        <div class="card" style="margin-bottom:0; border-bottom-left-radius:0; border-bottom-right-radius:0;">
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
      </div>
      <?php $i++; endforeach; ?>
      
      <!-- Sticky Save Button -->
      <div style="position:sticky;bottom:0;background:var(--admin-card);border:1px solid var(--admin-border);border-top:none;padding:16px;border-bottom-left-radius:12px;border-bottom-right-radius:12px;box-shadow:0 -4px 10px rgba(0,0,0,0.1);z-index:10;">
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;"><i class="fas fa-save"></i> <?= t('admin_save_all_settings') ?></button>
      </div>
    </div>
  </div>
</form>

<script>
function openSettingsTab(evt, tabId) {
  document.querySelectorAll('.settings-tab-pane').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.settings-tab-btn').forEach(el => el.classList.remove('active'));
  document.getElementById(tabId).classList.add('active');
  evt.currentTarget.classList.add('active');
}
</script>
</div></div></div></body></html>
