<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'testimonials';
$adminPageTitle = 'Guest Testimonials';

if(isset($_GET['delete'])) { $db->prepare("DELETE FROM testimonials WHERE id=?")->execute([(int)$_GET['delete']]); header('Location: testimonials.php'); exit; }
if(isset($_GET['toggle'])) { $db->prepare("UPDATE testimonials SET is_active=!is_active WHERE id=?")->execute([(int)$_GET['toggle']]); header('Location: testimonials.php'); exit; }

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save'])) {
    $data=[sanitize($_POST['guest_name']),sanitize($_POST['country_en']),sanitize($_POST['country_ar']),(int)$_POST['rating'],sanitize($_POST['review_en']),sanitize($_POST['review_ar']),isset($_POST['is_active'])?1:0];
    if($_POST['tid']??false) $db->prepare("UPDATE testimonials SET guest_name=?,country_en=?,country_ar=?,rating=?,review_en=?,review_ar=?,is_active=? WHERE id=?")->execute([...$data,(int)$_POST['tid']]);
    else $db->prepare("INSERT INTO testimonials (guest_name,country_en,country_ar,rating,review_en,review_ar,is_active) VALUES (?,?,?,?,?,?,?)")->execute($data);
    setFlash('success','Saved'); header('Location: testimonials.php'); exit;
}

$editing=null;
if(isset($_GET['edit'])){$s=$db->prepare("SELECT * FROM testimonials WHERE id=?");$s->execute([(int)$_GET['edit']]);$editing=$s->fetch();}
$showForm=isset($_GET['action'])||$editing;
$items=$db->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<?php $f=getFlash();if($f):?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif;?>
<?php if($showForm):?>
<div class="card">
  <div class="card-header"><span class="card-title"><?=$editing?'Edit Testimonial':'Add Testimonial'?></span><a href="testimonials.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
  <form method="POST" class="admin-form">
    <input type="hidden" name="save" value="1"><?php if($editing):?><input type="hidden" name="tid" value="<?=$editing['id']?>"><?php endif;?>
    <div class="form-row">
      <div class="form-group"><label>Guest Name</label><input type="text" name="guest_name" value="<?=e($editing['guest_name']??'')?>" required></div>
      <div class="form-group"><label>Rating (1-5)</label><select name="rating"><?php for($i=5;$i>=1;$i--):?><option value="<?=$i?>" <?=($editing['rating']??5)==$i?'selected':''?>><?=$i?> Stars</option><?php endfor;?></select></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Country (English)</label><input type="text" name="country_en" value="<?=e($editing['country_en']??'')?>"></div>
      <div class="form-group"><label>Country (Arabic)</label><input type="text" name="country_ar" value="<?=e($editing['country_ar']??'')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Review (English)</label><textarea name="review_en"><?=e($editing['review_en']??'')?></textarea></div>
      <div class="form-group"><label>Review (Arabic)</label><textarea name="review_ar"><?=e($editing['review_ar']??'')?></textarea></div>
    </div>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:16px;"><input type="checkbox" name="is_active" value="1" <?=($editing['is_active']??1)?'checked':''?>> Show on Website</label>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
  </form>
</div>
<?php else:?>
<div class="card">
  <div class="card-header"><span class="card-title">All Testimonials (<?=count($items)?>)</span><a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add</a></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Guest</th><th>Country</th><th>Rating</th><th>Review (EN)</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($items as $t):?>
      <tr>
        <td><strong><?=e($t['guest_name'])?></strong></td>
        <td><small><?=e($t['country_en']??'—')?></small></td>
        <td><?=str_repeat('⭐',$t['rating'])?></td>
        <td style="max-width:300px;"><small style="color:var(--gray);">"<?=e(mb_substr($t['review_en'],0,80))?>..."</small></td>
        <td><?=$t['is_active']?'<span class="badge badge-success">Visible</span>':'<span class="badge badge-secondary">Hidden</span>'?></td>
        <td style="display:flex;gap:6px;">
          <a href="?edit=<?=$t['id']?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
          <a href="?toggle=<?=$t['id']?>" class="btn-xs btn-secondary"><i class="fas fa-eye"></i></a>
          <a href="?delete=<?=$t['id']?>" class="btn-xs btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table></div>
</div>
<?php endif;?>
</div></div></div></body></html>
