<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'gallery';
$adminPageTitle = 'Gallery Management';

// Delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM gallery WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: gallery.php'); exit;
}
// Toggle
if(isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE gallery SET is_active=!is_active WHERE id=?")->execute([(int)$_GET['toggle']]);
    header('Location: gallery.php'); exit;
}
// Upload
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['upload'])) {
    $title_en = sanitize($_POST['title_en']??'');
    $title_ar = sanitize($_POST['title_ar']??'');
    $category = sanitize($_POST['category']??'interior');
    $sort_order = (int)($_POST['sort_order']??0);
    $uploaded = 0;
    if(!empty($_FILES['images']['name'][0])) {
        foreach($_FILES['images']['name'] as $i => $name) {
            $file = [
                'name'     => $_FILES['images']['name'][$i],
                'type'     => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error'    => $_FILES['images']['error'][$i],
                'size'     => $_FILES['images']['size'][$i],
            ];
            if($file['error']===UPLOAD_ERR_OK) {
                $path = uploadImage($file, 'gallery');
                if($path) {
                    $db->prepare("INSERT INTO gallery (title_en,title_ar,image,category,sort_order) VALUES (?,?,?,?,?)")->execute([$title_en,$title_ar,$path,$category,$sort_order]);
                    $uploaded++;
                }
            }
        }
    }
    setFlash('success', "$uploaded image(s) uploaded successfully");
    header('Location: gallery.php'); exit;
}

$filterCat = sanitize($_GET['cat']??'');
$where = $filterCat ? "WHERE category='$filterCat'" : '';
$items = $db->query("SELECT * FROM gallery $where ORDER BY sort_order, id DESC LIMIT 200")->fetchAll();

$categories = ['rooms','restaurant','facilities','events','exterior','interior'];
require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash();if($f):?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif;?>

<!-- Upload form -->
<div class="card" style="margin-bottom:24px;">
  <div class="card-header"><span class="card-title">Upload Images</span></div>
  <form method="POST" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="upload" value="1">
    <div class="form-row">
      <div class="form-group"><label>Title (English)</label><input type="text" name="title_en" placeholder="Optional caption"></div>
      <div class="form-group"><label>Title (Arabic)</label><input type="text" name="title_ar" placeholder="عنوان اختياري"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Category</label>
        <select name="category">
          <?php foreach($categories as $c):?><option value="<?=$c?>"><?=ucfirst($c)?></option><?php endforeach;?>
        </select>
      </div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
    </div>
    <div class="form-group"><label>Images (Multiple allowed)</label><input type="file" name="images[]" accept="image/*" multiple required></div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Images</button>
  </form>
</div>

<!-- Gallery items -->
<div class="card">
  <div class="card-header">
    <span class="card-title">Gallery (<?=count($items)?> items)</span>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="gallery.php" class="btn btn-sm <?=!$filterCat?'btn-primary':'btn-secondary'?>">All</a>
      <?php foreach($categories as $c):?>
      <a href="?cat=<?=$c?>" class="btn btn-sm <?=$filterCat===$c?'btn-primary':'btn-secondary'?>"><?=ucfirst($c)?></a>
      <?php endforeach;?>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-top:8px;">
    <?php foreach($items as $item):?>
    <div style="background:var(--admin-darker);border:1px solid <?=$item['is_active']?'var(--admin-border)':'rgba(255,71,87,0.3)'?>;border-radius:10px;overflow:hidden;">
      <div style="position:relative;">
        <img src="<?=SITE_URL?>/<?=e($item['image'])?>" style="width:100%;height:110px;object-fit:cover;" loading="lazy">
        <?php if(!$item['is_active']):?><div style="position:absolute;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;"><span style="color:var(--danger);font-size:11px;font-weight:700;">HIDDEN</span></div><?php endif;?>
      </div>
      <div style="padding:10px;">
        <p style="font-size:11px;color:var(--gray);margin-bottom:6px;"><?=e($item['title_en']?$item['title_en']:ucfirst($item['category']))?></p>
        <div style="display:flex;gap:6px;">
          <a href="?toggle=<?=$item['id']?>" class="btn-xs btn-secondary" title="Toggle visibility"><i class="fas fa-eye<?=$item['is_active']?'':'-slash'?>"></i></a>
          <a href="?delete=<?=$item['id']?>" class="btn-xs btn-danger" onclick="return confirm('Delete image?')"><i class="fas fa-trash"></i></a>
        </div>
      </div>
    </div>
    <?php endforeach;?>
    <?php if(empty($items)):?><div style="grid-column:1/-1;padding:40px;text-align:center;color:var(--gray);">No images uploaded yet</div><?php endif;?>
  </div>
</div>

</div></div></div></body></html>
