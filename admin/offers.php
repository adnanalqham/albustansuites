<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'offers';
$adminPageTitle = 'admin_manage_offers';

if(isset($_GET['delete'])) { $db->prepare("DELETE FROM offers WHERE id=?")->execute([(int)$_GET['delete']]); header('Location: offers.php'); exit; }
if(isset($_GET['toggle'])) { $db->prepare("UPDATE offers SET is_active=!is_active WHERE id=?")->execute([(int)$_GET['toggle']]); header('Location: offers.php'); exit; }

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_offer'])) {
    $img = sanitize($_POST['existing_img']??'');
    if(!empty($_FILES['image']['name'])){$u=uploadImage($_FILES['image'],'offers');if($u)$img=$u;}
    $data=[sanitize($_POST['title_en']),sanitize($_POST['title_ar']),sanitize($_POST['description_en']),sanitize($_POST['description_ar']),sanitize($_POST['discount_type']),(float)$_POST['discount_value'],sanitize($_POST['promo_code']),sanitize($_POST['valid_from'])||null,sanitize($_POST['valid_to'])||null,(int)$_POST['min_nights'],isset($_POST['is_active'])?1:0,isset($_POST['is_featured'])?1:0,$img];
    if($_POST['offer_id']??false) $db->prepare("UPDATE offers SET title_en=?,title_ar=?,description_en=?,description_ar=?,discount_type=?,discount_value=?,promo_code=?,valid_from=?,valid_to=?,min_nights=?,is_active=?,is_featured=?,image=? WHERE id=?")->execute([...$data,(int)$_POST['offer_id']]);
    else $db->prepare("INSERT INTO offers (title_en,title_ar,description_en,description_ar,discount_type,discount_value,promo_code,valid_from,valid_to,min_nights,is_active,is_featured,image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute($data);
    setFlash('success','Offer saved'); header('Location: offers.php'); exit;
}

$editing=null;
if(isset($_GET['edit'])){$s=$db->prepare("SELECT * FROM offers WHERE id=?");$s->execute([(int)$_GET['edit']]);$editing=$s->fetch();}
$showForm = isset($_GET['action']) || $editing;
$offers = $db->query("SELECT * FROM offers ORDER BY id DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash();if($f):?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif;?>
<?php if($showForm):?>
<div class="card">
  <div class="card-header"><span class="card-title"><?=$editing?t('admin_edit_offer'):t('admin_add_offer_btn')?></span><a href="offers.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> <?= t('admin_back') ?></a></div>
  <form method="POST" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="save_offer" value="1">
    <?php if($editing):?><input type="hidden" name="offer_id" value="<?=$editing['id']?>"><input type="hidden" name="existing_img" value="<?=e($editing['image']??'')?>">
    <?php endif;?>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_title_en') ?></label><input type="text" name="title_en" value="<?=e($editing['title_en']??'')?>" required></div>
      <div class="form-group"><label><?= t('admin_title_ar') ?></label><input type="text" name="title_ar" value="<?=e($editing['title_ar']??'')?>" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_desc_en') ?></label><textarea name="description_en"><?=e($editing['description_en']??'')?></textarea></div>
      <div class="form-group"><label><?= t('admin_desc_ar') ?></label><textarea name="description_ar"><?=e($editing['description_ar']??'')?></textarea></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_discount_type') ?></label><select name="discount_type"><option value="percentage" <?=($editing['discount_type']??'')==='percentage'?'selected':''?>>Percentage (%)</option><option value="fixed" <?=($editing['discount_type']??'')==='fixed'?'selected':''?>>Fixed Amount ($)</option></select></div>
      <div class="form-group"><label><?= t('admin_discount_value') ?></label><input type="number" name="discount_value" value="<?=$editing['discount_value']??''?>" step="0.01" required></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_promo_code') ?></label><input type="text" name="promo_code" value="<?=e($editing['promo_code']??'')?>" style="text-transform:uppercase" placeholder="e.g. SUMMER20"></div>
      <div class="form-group"><label><?= t('admin_min_nights') ?></label><input type="number" name="min_nights" value="<?=$editing['min_nights']??1?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label><?= t('admin_valid_from') ?></label><input type="date" name="valid_from" value="<?=$editing['valid_from']??''?>"></div>
      <div class="form-group"><label><?= t('admin_valid_to') ?></label><input type="date" name="valid_to" value="<?=$editing['valid_to']??''?>"></div>
    </div>
    <div class="form-group"><label><?= t('admin_image') ?></label><?php if(!empty($editing['image'])):?><img src="<?=SITE_URL?>/<?=e($editing['image'])?>" style="width:100px;height:70px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:block;"><?php endif;?><input type="file" name="image" accept="image/*"></div>
    <div style="display:flex;gap:20px;margin-bottom:16px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_active" value="1" <?=($editing['is_active']??1)?'checked':''?>> <?= t('admin_active') ?></label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_featured" value="1" <?=($editing['is_featured']??0)?'checked':''?>> <?= t('admin_featured_room') ?></label>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('admin_save_offer') ?></button>
  </form>
</div>
<?php else:?>
<div class="card">
  <div class="card-header"><span class="card-title"><?= t('admin_all_offers') ?> (<?=count($offers)?>)</span><a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= t('admin_add_offer_btn') ?></a></div>
  <div class="table-wrap"><table>
    <thead><tr><th><?= t('admin_image') ?></th><th><?= t('admin_title_table') ?></th><th><?= t('admin_discount_table') ?></th><th><?= t('admin_promo_code') ?></th><th><?= t('admin_valid_until') ?></th><th><?= t('admin_status') ?></th><th><?= t('admin_action') ?></th></tr></thead>
    <tbody>
      <?php foreach($offers as $o):?>
      <tr>
        <td><img src="<?=SITE_URL?>/<?=e($o['image']??'images/offer-default.jpg')?>" style="width:80px;height:55px;object-fit:cover;border-radius:6px;"></td>
        <td><strong><?=e($o['title_en'])?></strong></td>
        <td style="color:var(--gold);font-weight:600;"><?=$o['discount_type']==='percentage'?round($o['discount_value']).'%':'$'.number_format($o['discount_value'],0)?> OFF</td>
        <td><?=$o['promo_code']?'<code style="background:var(--admin-darker);padding:3px 8px;border-radius:4px;font-size:12px;">'.e($o['promo_code']).'</code>':'—'?></td>
        <td><small><?=$o['valid_to']?formatDate($o['valid_to']):t('admin_no_expiry')?></small></td>
        <td><?=$o['is_active']?'<span class="badge badge-success">'.t('admin_active').'</span>':'<span class="badge badge-secondary">'.t('admin_inactive').'</span>'?> <?=$o['is_featured']?'<span class="badge badge-warning">'.t('admin_featured_room').'</span>':''?></td>
        <td style="display:flex;gap:6px;">
          <a href="?edit=<?=$o['id']?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
          <a href="?toggle=<?=$o['id']?>" class="btn-xs btn-secondary"><i class="fas fa-power-off"></i></a>
          <a href="?delete=<?=$o['id']?>" class="btn-xs btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table></div>
</div>
<?php endif;?>
</div></div></div></body></html>
