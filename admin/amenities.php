<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'amenities';
$adminPageTitle = 'admin_manage_amenities';

// Auto-create tables if they don't exist
$db->exec("CREATE TABLE IF NOT EXISTS amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NOT NULL,
    icon VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$db->exec("CREATE TABLE IF NOT EXISTS room_amenities (
    room_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (room_id, amenity_id)
)");

// Delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $amId = (int)$_GET['delete'];
    $db->prepare("DELETE FROM room_amenities WHERE amenity_id=?")->execute([$amId]);
    $db->prepare("DELETE FROM amenities WHERE id=?")->execute([$amId]);
    setFlash('success','Amenity deleted'); 
    header('Location: amenities.php'); exit;
}

// Save/Update
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_amenity'])) {
    $data = [
        sanitize($_POST['name_en']), 
        sanitize($_POST['name_ar']),
        sanitize($_POST['icon'])
    ];
    
    if($_POST['amenity_id'] ?? false) {
        $db->prepare("UPDATE amenities SET name_en=?, name_ar=?, icon=? WHERE id=?")->execute([...$data, (int)$_POST['amenity_id']]);
        setFlash('success','Amenity updated');
    } else {
        $db->prepare("INSERT INTO amenities (name_en, name_ar, icon) VALUES (?, ?, ?)")->execute($data);
        setFlash('success','Amenity added');
    }
    header('Location: amenities.php'); exit;
}

$editing = null;
if(isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editS = $db->prepare("SELECT * FROM amenities WHERE id=?"); 
    $editS->execute([(int)$_GET['edit']]); 
    $editing = $editS->fetch();
}

$showForm = isset($_GET['action']) && $_GET['action']==='add' || $editing;
$amenities = $db->query("SELECT * FROM amenities ORDER BY id DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash(); if($f): ?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif; ?>

<?php if($showForm): ?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= $editing ? t('admin_edit_amenity') : t('admin_add_new_amenity') ?></span>
    <a href="amenities.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> <?= t('admin_back') ?></a>
  </div>
  <form method="POST" class="admin-form">
    <input type="hidden" name="save_amenity" value="1">
    <?php if($editing): ?><input type="hidden" name="amenity_id" value="<?= $editing['id'] ?>"><?php endif; ?>
    
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_amenity_name_en') ?></label>
        <input type="text" name="name_en" value="<?= e($editing['name_en']??'') ?>" required placeholder="e.g. Free Wi-Fi">
      </div>
      <div class="form-group">
        <label><?= t('admin_amenity_name_ar') ?></label>
        <input type="text" name="name_ar" value="<?= e($editing['name_ar']??'') ?>" required placeholder="e.g. واي فاي مجاني">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_icon') ?></label>
        <input type="text" name="icon" value="<?= e($editing['icon']??'fas fa-star') ?>" required placeholder="e.g. fas fa-wifi">
        <small style="color:var(--gray);margin-top:5px;display:block;">Find icons at <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" style="color:var(--gold);">FontAwesome</a></small>
      </div>
      <div class="form-group" style="display:flex;align-items:center;">
          <div style="font-size:30px;color:var(--gold);margin-top:20px;">
              <i class="<?= e($editing['icon']??'fas fa-star') ?>" id="iconPreview"></i>
          </div>
      </div>
    </div>
    
    <div style="margin-top:20px;"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('admin_save_amenity') ?></button></div>
  </form>
</div>
<script>
document.querySelector('input[name="icon"]').addEventListener('input', function(e) {
    document.getElementById('iconPreview').className = e.target.value;
});
</script>
<?php else: ?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_all_amenities') ?> (<?= count($amenities) ?>)</span>
    <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= t('admin_add_amenity') ?></a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th><?= t('admin_icon_table') ?></th><th><?= t('admin_amenity_name_en') ?></th><th><?= t('admin_amenity_name_ar') ?></th><th><?= t('admin_action') ?></th></tr></thead>
      <tbody>
        <?php foreach($amenities as $a): ?>
        <tr>
          <td><div style="font-size:20px;color:var(--gold);"><i class="<?= e($a['icon']) ?>"></i></div></td>
          <td><strong><?= e($a['name_en']) ?></strong></td>
          <td><span style="color:var(--gray);"><?= e($a['name_ar']) ?></span></td>
          <td style="display:flex;gap:6px;">
            <a href="?edit=<?= $a['id'] ?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
            <a href="?delete=<?= $a['id'] ?>" class="btn-xs btn-danger" onclick="return confirm('Delete this amenity?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($amenities)): ?>
        <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--gray);"><?= t('admin_no_amenities') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
</div></div></div></body></html>
