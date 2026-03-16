<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'restaurant';
$adminPageTitle = 'Menu Management';

// Delete item
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM menu_items WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: restaurant.php'); exit;
}
// Save item
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_item'])) {
    $img = sanitize($_POST['existing_img'] ?? '');
    if(!empty($_FILES['image']['name'])) { $u=uploadImage($_FILES['image'],'menu'); if($u) $img=$u; }
    $data = [
        (int)$_POST['category_id'], sanitize($_POST['name_en']), sanitize($_POST['name_ar']),
        sanitize($_POST['description_en']), sanitize($_POST['description_ar']),
        (float)$_POST['price'], sanitize($_POST['currency']??'USD'),
        isset($_POST['is_vegetarian'])?1:0, isset($_POST['is_featured'])?1:0, isset($_POST['is_available'])?1:0,
        (int)$_POST['sort_order'], $img
    ];
    if($_POST['item_id']??false) {
        $db->prepare("UPDATE menu_items SET category_id=?,name_en=?,name_ar=?,description_en=?,description_ar=?,price=?,currency=?,is_vegetarian=?,is_featured=?,is_available=?,sort_order=?,image=? WHERE id=?")->execute([...$data,(int)$_POST['item_id']]);
    } else {
        $db->prepare("INSERT INTO menu_items (category_id,name_en,name_ar,description_en,description_ar,price,currency,is_vegetarian,is_featured,is_available,sort_order,image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")->execute($data);
    }
    setFlash('success','Menu item saved'); header('Location: restaurant.php'); exit;
}

$editing = null;
if(isset($_GET['edit'])) { $s=$db->prepare("SELECT * FROM menu_items WHERE id=?");$s->execute([(int)$_GET['edit']]);$editing=$s->fetch(); }
$showForm = isset($_GET['action']) || $editing;
$items = $db->query("SELECT mi.*, mc.name_en as cat FROM menu_items mi JOIN menu_categories mc ON mi.category_id=mc.id ORDER BY mi.category_id, mi.sort_order")->fetchAll();
$cats  = $db->query("SELECT * FROM menu_categories ORDER BY sort_order")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash();if($f):?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif;?>
<?php if($showForm): ?>
<div class="card">
  <div class="card-header"><span class="card-title"><?= $editing?'Edit Item':'Add Menu Item'?></span><a href="restaurant.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
  <form method="POST" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="save_item" value="1">
    <?php if($editing):?><input type="hidden" name="item_id" value="<?=$editing['id']?>"><input type="hidden" name="existing_img" value="<?=e($editing['image']??'')?>">
    <?php endif;?>
    <div class="form-row">
      <div class="form-group"><label>Category</label><select name="category_id"><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=($editing['category_id']??0)==$c['id']?'selected':''?>><?=e($c['name_en'])?></option><?php endforeach;?></select></div>
      <div class="form-group"><label>Price</label><input type="number" name="price" value="<?=$editing['price']??''?>" step="0.01" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Name (English)</label><input type="text" name="name_en" value="<?=e($editing['name_en']??'')?>" required></div>
      <div class="form-group"><label>Name (Arabic)</label><input type="text" name="name_ar" value="<?=e($editing['name_ar']??'')?>" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Description (English)</label><textarea name="description_en"><?=e($editing['description_en']??'')?></textarea></div>
      <div class="form-group"><label>Description (Arabic)</label><textarea name="description_ar"><?=e($editing['description_ar']??'')?></textarea></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Image</label><?php if(!empty($editing['image'])):?><img src="<?=SITE_URL?>/<?=e($editing['image'])?>" style="width:80px;height:60px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:block;"><?php endif;?><input type="file" name="image" accept="image/*"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?=$editing['sort_order']??0?>"></div>
    </div>
    <div style="display:flex;gap:20px;margin-bottom:16px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_vegetarian" <?=($editing['is_vegetarian']??0)?'checked':''?>> Vegetarian</label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_featured" <?=($editing['is_featured']??0)?'checked':''?>> Featured</label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_available" value="1" <?=($editing['is_available']??1)?'checked':''?>> Available</label>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Item</button>
  </form>
</div>
<?php else: ?>
<div class="card">
  <div class="card-header"><span class="card-title">Menu Items (<?=count($items)?>)</span><a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Item</a></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Flags</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($items as $it):?>
      <tr>
        <td><img src="<?=SITE_URL?>/<?=e($it['image']??'images/food-default.jpg')?>" style="width:60px;height:45px;object-fit:cover;border-radius:6px;"></td>
        <td><strong><?=e($it['name_en'])?></strong><br><small style="color:var(--gray);"><?=e($it['name_ar'])?></small></td>
        <td><small><?=e($it['cat'])?></small></td>
        <td style="color:var(--gold);font-weight:600;">$<?=number_format($it['price'],2)?></td>
        <td><?=$it['is_vegetarian']?'<span class="badge badge-success">Veg</span>':''?><?=$it['is_featured']?' <span class="badge badge-warning">Featured</span>':''?><?=!$it['is_available']?' <span class="badge badge-danger">Off</span>':''?></td>
        <td style="display:flex;gap:6px;">
          <a href="?edit=<?=$it['id']?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
          <a href="?delete=<?=$it['id']?>" class="btn-xs btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table></div>
</div>
<?php endif;?>
</div></div></div></body></html>
