<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'rooms';
$adminPageTitle = 'admin_room_management';

// Delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM rooms WHERE id=?")->execute([(int)$_GET['delete']]);
    setFlash('success','Room deleted'); header('Location: rooms.php'); exit;
}
// Toggle availability
if(isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE rooms SET is_available = !is_available WHERE id=?")->execute([(int)$_GET['toggle']]);
    header('Location: rooms.php'); exit;
}

// Save/Update room
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_room'])) {
    $data = [
        sanitize($_POST['name_en']), sanitize($_POST['name_ar']),
        sanitize($_POST['slug']), sanitize($_POST['description_en']), sanitize($_POST['description_ar']),
        sanitize($_POST['short_desc_en']), sanitize($_POST['short_desc_ar']),
        (float)$_POST['price_per_night'], sanitize($_POST['currency']??'USD'),
        (int)$_POST['size_sqm'], (int)$_POST['capacity_adults'], (int)$_POST['capacity_children'],
        sanitize($_POST['view_type_en']), sanitize($_POST['view_type_ar']),
        isset($_POST['is_available'])?1:0, isset($_POST['is_featured'])?1:0,
        (int)$_POST['category_id'], (int)$_POST['sort_order']
    ];
    // Handle image upload
    $mainImage = sanitize($_POST['existing_image'] ?? '');
    if(!empty($_FILES['main_image']['name'])) {
        $uploaded = uploadImage($_FILES['main_image'], 'rooms');
        if($uploaded) $mainImage = $uploaded;
    }

    if($_POST['room_id'] ?? false) {
        $roomId = (int)$_POST['room_id'];
        $db->prepare("UPDATE rooms SET name_en=?,name_ar=?,slug=?,description_en=?,description_ar=?,short_desc_en=?,short_desc_ar=?,price_per_night=?,currency=?,size_sqm=?,capacity_adults=?,capacity_children=?,view_type_en=?,view_type_ar=?,is_available=?,is_featured=?,category_id=?,sort_order=?,main_image=? WHERE id=?")->execute([...$data, $mainImage, $roomId]);
        setFlash('success','Room updated');
    } else {
        $db->prepare("INSERT INTO rooms (name_en,name_ar,slug,description_en,description_ar,short_desc_en,short_desc_ar,price_per_night,currency,size_sqm,capacity_adults,capacity_children,view_type_en,view_type_ar,is_available,is_featured,category_id,sort_order,main_image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute([...$data, $mainImage]);
        $roomId = $db->lastInsertId();
        setFlash('success','Room added');
    }

    // Save Amenities
    try {
        $db->prepare("DELETE FROM room_amenities WHERE room_id=?")->execute([$roomId]);
        if(!empty($_POST['amenities']) && is_array($_POST['amenities'])) {
            $stmtAm = $db->prepare("INSERT INTO room_amenities (room_id, amenity_id) VALUES (?, ?)");
            foreach($_POST['amenities'] as $amId) {
                $stmtAm->execute([$roomId, (int)$amId]);
            }
        }
    } catch(Exception $e) {}

    header('Location: rooms.php'); exit;
}

$editing = null;
$roomAmenities = [];
if(isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $roomId = (int)$_GET['edit'];
    $editS = $db->prepare("SELECT * FROM rooms WHERE id=?"); $editS->execute([$roomId]); $editing = $editS->fetch();
    try { $roomAmenities = $db->prepare("SELECT amenity_id FROM room_amenities WHERE room_id=?")->execute([$roomId]) ? $db->prepare("SELECT amenity_id FROM room_amenities WHERE room_id=?") : null; 
      if($roomAmenities) { $roomAmenities->execute([$roomId]); $roomAmenities = $roomAmenities->fetchAll(PDO::FETCH_COLUMN); }
    } catch(Exception $e) { $roomAmenities=[]; }
}
$showForm = isset($_GET['action']) && $_GET['action']==='add' || $editing;
$rooms = $db->query("SELECT r.*,rc.name_en as cat FROM rooms r JOIN room_categories rc ON r.category_id=rc.id ORDER BY r.sort_order")->fetchAll();
$categories = $db->query("SELECT * FROM room_categories ORDER BY sort_order")->fetchAll();
try { $allAmenities = $db->query("SELECT * FROM amenities ORDER BY name_en")->fetchAll(); } catch(Exception $e) { $allAmenities=[]; }

require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash(); if($f): ?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif; ?>

<?php if($showForm): ?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= $editing ? t('admin_edit_room').': '.e($editing['name_en']) : t('admin_add_new_room_title') ?></span>
    <a href="rooms.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> <?= t('admin_back') ?></a>
  </div>
  <form method="POST" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="save_room" value="1">
    <?php if($editing): ?><input type="hidden" name="room_id" value="<?= $editing['id'] ?>"><input type="hidden" name="existing_image" value="<?= e($editing['main_image']??'') ?>"><?php endif; ?>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_room_name_en') ?></label><input type="text" name="name_en" value="<?= e($editing['name_en']??'') ?>" required></div>
      <div class="form-group"><label><?= t('admin_room_name_ar') ?></label><input type="text" name="name_ar" value="<?= e($editing['name_ar']??'') ?>" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_slug') ?></label><input type="text" name="slug" value="<?= e($editing['slug']??'') ?>" required placeholder="e.g. royal-suite"></div>
      <div class="form-group"><label><?= t('admin_category') ?></label><select name="category_id"><?php foreach($categories as $c): ?><option value="<?=$c['id']?>" <?= ($editing['category_id']??0)==$c['id']?'selected':'' ?>><?= e($c['name_en'])?></option><?php endforeach; ?></select></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_short_desc_en') ?></label><input type="text" name="short_desc_en" value="<?= e($editing['short_desc_en']??'') ?>"></div>
      <div class="form-group"><label><?= t('admin_short_desc_ar') ?></label><input type="text" name="short_desc_ar" value="<?= e($editing['short_desc_ar']??'') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_desc_en') ?></label><textarea name="description_en"><?= e($editing['description_en']??'') ?></textarea></div>
      <div class="form-group"><label><?= t('admin_desc_ar') ?></label><textarea name="description_ar"><?= e($editing['description_ar']??'') ?></textarea></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_price') ?></label><input type="number" name="price_per_night" value="<?= $editing['price_per_night']??'' ?>" step="0.01" required></div>
      <div class="form-group"><label><?= t('admin_currency') ?></label><select name="currency"><option value="USD" <?= ($editing['currency']??'USD')==='USD'?'selected':''?>>USD</option><option value="YER">YER</option><option value="SAR">SAR</option></select></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_size') ?></label><input type="number" name="size_sqm" value="<?= $editing['size_sqm']??'' ?>"></div>
      <div class="form-group"><label><?= t('admin_max_adults') ?></label><input type="number" name="capacity_adults" value="<?= $editing['capacity_adults']??2 ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_max_children') ?></label><input type="number" name="capacity_children" value="<?= $editing['capacity_children']??1 ?>"></div>
      <div class="form-group"><label><?= t('admin_sort_order') ?></label><input type="number" name="sort_order" value="<?= $editing['sort_order']??0 ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_view_en') ?></label><input type="text" name="view_type_en" value="<?= e($editing['view_type_en']??'') ?>"></div>
      <div class="form-group"><label><?= t('admin_view_ar') ?></label><input type="text" name="view_type_ar" value="<?= e($editing['view_type_ar']??'') ?>"></div>
    </div>
    
    <div class="form-group">
        <label><?= t('admin_room_amenities') ?></label>
        <div style="display:flex; flex-wrap:wrap; gap:15px; background:var(--admin-sidebar); padding:15px; border-radius:8px; border:1px solid var(--admin-border);">
            <?php foreach($allAmenities as $am): ?>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; min-width:180px;">
                <input type="checkbox" name="amenities[]" value="<?= $am['id'] ?>" <?= in_array($am['id'], $roomAmenities) ? 'checked' : '' ?>> 
                <i class="<?= e($am['icon']) ?>" style="color:var(--gold);width:20px;text-align:center;"></i> <?= e($am['name_en']) ?>
            </label>
            <?php endforeach; ?>
            <?php if(empty($allAmenities)): ?>
            <span style="color:var(--gray);font-style:italic;">No amenities found! <a href="amenities.php" style="color:var(--gold);">Add some here.</a></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-group"><label><?= t('admin_main_image') ?></label>
      <?php if(!empty($editing['main_image'])): ?><img src="<?= SITE_URL ?>/<?= e($editing['main_image']) ?>" style="width:120px;height:80px;object-fit:cover;border-radius:8px;margin-bottom:8px;display:block;"><?php endif; ?>
      <input type="file" name="main_image" accept="image/*">
    </div>
    <div style="display:flex;gap:20px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_available" value="1" <?= ($editing['is_available']??1)?'checked':'' ?>> <?= t('admin_available') ?></label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_featured" value="1" <?= ($editing['is_featured']??0)?'checked':'' ?>> <?= t('admin_featured_room') ?></label>
    </div>
    <div style="margin-top:20px;"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('admin_save_room') ?></button></div>
  </form>
</div>
<?php else: ?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_rooms_all') ?> (<?= count($rooms) ?>)</span>
    <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= t('admin_add_room') ?></a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th><?= t('admin_image') ?></th><th><?= t('admin_room') ?></th><th><?= t('admin_category_table') ?></th><th><?= t('admin_price_night') ?></th><th><?= t('admin_capacity') ?></th><th><?= t('admin_status') ?></th><th><?= t('admin_action') ?></th></tr></thead>
      <tbody>
        <?php foreach($rooms as $r): ?>
        <tr>
          <td><img src="<?= SITE_URL ?>/<?= e($r['main_image']??'images/room-default.jpg') ?>" style="width:70px;height:50px;object-fit:cover;border-radius:6px;"></td>
          <td><strong><?= e($r['name_en']) ?></strong><br><small style="color:var(--gray);"><?= e($r['name_ar']) ?></small></td>
          <td><small><?= e($r['cat']) ?></small></td>
          <td style="color:var(--gold);font-weight:600;">$<?= number_format($r['price_per_night']) ?></td>
          <td><small><?= $r['capacity_adults'] ?> <?= t('adults') ?> + <?= $r['capacity_children'] ?> <?= t('children') ?></small></td>
          <td>
            <?php if($r['is_available']): ?><span class="badge badge-success"><?= t('admin_available') ?></span><?php else: ?><span class="badge badge-danger"><?= t('admin_unavailable') ?></span><?php endif; ?>
            <?php if($r['is_featured']): ?><span class="badge badge-warning" style="margin-left:4px;">★</span><?php endif; ?>
          </td>
          <td style="display:flex;gap:6px;">
            <a href="room_numbers.php?room_id=<?= $r['id'] ?>" class="btn-xs btn-success" title="<?= t('admin_manage_room_numbers') ?>"><i class="fas fa-list-ol"></i></a>
            <a href="?edit=<?= $r['id'] ?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
            <a href="?toggle=<?= $r['id'] ?>" class="btn-xs btn-secondary" title="<?= t('admin_status') ?>"><i class="fas fa-power-off"></i></a>
            <a href="?delete=<?= $r['id'] ?>" class="btn-xs btn-danger" onclick="return confirm('<?= t('admin_delete') ?>?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
</div></div></div></body></html>
