<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'meetings';
$adminPageTitle = 'admin_manage_meetings';

// Auto-create table
$db->exec("CREATE TABLE IF NOT EXISTS meeting_halls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(255) NOT NULL,
    name_ar VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_ar TEXT,
    capacity INT DEFAULT 50,
    size_sqm INT DEFAULT 100,
    image VARCHAR(255)
)");

// Delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM meeting_halls WHERE id=?")->execute([(int)$_GET['delete']]);
    setFlash('success','Hall deleted successfully'); 
    header('Location: meetings.php'); exit;
}

// Save/Update
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_hall'])) {
    $data = [
        sanitize($_POST['name_en']), sanitize($_POST['name_ar']),
        sanitize($_POST['description_en']), sanitize($_POST['description_ar']),
        (int)($_POST['capacity']??50), (int)($_POST['size_sqm']??100)
    ];
    $image = sanitize($_POST['existing_image'] ?? '');
    if(!empty($_FILES['image']['name'])) {
        $uploaded = uploadImage($_FILES['image'], 'meetings');
        if($uploaded) $image = $uploaded;
    }
    
    if($_POST['hall_id'] ?? false) {
        $db->prepare("UPDATE meeting_halls SET name_en=?, name_ar=?, description_en=?, description_ar=?, capacity=?, size_sqm=?, image=? WHERE id=?")->execute([...$data, $image, (int)$_POST['hall_id']]);
        setFlash('success','Hall updated successfully');
    } else {
        $db->prepare("INSERT INTO meeting_halls (name_en, name_ar, description_en, description_ar, capacity, size_sqm, image) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute([...$data, $image]);
        setFlash('success','Hall added successfully');
    }
    header('Location: meetings.php'); exit;
}

$editing = null;
if(isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editS = $db->prepare("SELECT * FROM meeting_halls WHERE id=?"); $editS->execute([(int)$_GET['edit']]); $editing = $editS->fetch();
}

$showForm = isset($_GET['action']) && $_GET['action']==='add' || $editing;
$halls = $db->query("SELECT * FROM meeting_halls ORDER BY id DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash(); if($f): ?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif; ?>

<?php if($showForm): ?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= $editing ? t('admin_edit_hall').': '.e($editing['name_en']) : t('admin_add_new_hall') ?></span>
    <a href="meetings.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> <?= t('admin_back') ?></a>
  </div>
  <form method="POST" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="save_hall" value="1">
    <?php if($editing): ?>
        <input type="hidden" name="hall_id" value="<?= $editing['id'] ?>">
        <input type="hidden" name="existing_image" value="<?= e($editing['image']) ?>">
    <?php endif; ?>
    
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_hall_name_en') ?></label>
        <input type="text" name="name_en" value="<?= e($editing['name_en']??'') ?>" required placeholder="e.g. Grand Ballroom">
      </div>
      <div class="form-group">
        <label><?= t('admin_hall_name_ar') ?></label>
        <input type="text" name="name_ar" value="<?= e($editing['name_ar']??'') ?>" required placeholder="e.g. القاعة الكبرى">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_capacity_persons') ?></label>
        <input type="number" name="capacity" value="<?= $editing['capacity']??50 ?>" required>
      </div>
      <div class="form-group">
        <label><?= t('admin_size_sqm') ?></label>
        <input type="number" name="size_sqm" value="<?= $editing['size_sqm']??100 ?>" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_desc_en') ?></label>
        <textarea name="description_en" rows="4"><?= e($editing['description_en']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label><?= t('admin_desc_ar') ?></label>
        <textarea name="description_ar" rows="4"><?= e($editing['description_ar']??'') ?></textarea>
      </div>
    </div>
    
    <div class="form-group">
        <label><?= t('admin_hall_image') ?></label>
        <?php if(!empty($editing['image'])): ?>
            <img src="<?= SITE_URL ?>/<?= e($editing['image']) ?>" style="width:150px; height:100px; object-fit:cover; border-radius:8px; display:block; margin-bottom:10px;">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">
    </div>
    
    <div style="margin-top:20px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('admin_save_hall') ?></button>
    </div>
  </form>
</div>

<?php else: ?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_meeting_spaces') ?> (<?= count($halls) ?>)</span>
    <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= t('admin_add_hall') ?></a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th><?= t('admin_image') ?></th>
          <th><?= t('admin_name') ?></th>
          <th><?= t('admin_capacity_size') ?></th>
          <th><?= t('admin_action') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($halls as $h): ?>
        <tr>
          <td>
            <img src="<?= SITE_URL ?>/<?= e($h['image'] ?: 'images/room-default.jpg') ?>" style="width:80px; height:50px; object-fit:cover; border-radius:6px;">
          </td>
          <td>
            <strong><?= e($h['name_en']) ?></strong><br>
            <small style="color:var(--gray);"><?= e($h['name_ar']) ?></small>
          </td>
          <td>
            <span class="badge badge-info"><i class="fas fa-users"></i> <?= $h['capacity'] ?></span>
            <span class="badge badge-secondary" style="margin-left:5px;"><i class="fas fa-ruler-combined"></i> <?= $h['size_sqm'] ?> sqm</span>
          </td>
          <td style="display:flex;gap:6px;">
            <a href="?edit=<?= $h['id'] ?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
            <a href="?delete=<?= $h['id'] ?>" class="btn-xs btn-danger" onclick="return confirm('Delete this hall?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($halls)): ?>
        <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--gray);"><?= t('admin_no_halls') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

</div></div></div></body></html>
