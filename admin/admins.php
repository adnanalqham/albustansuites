<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();

$db = getDB();
$adminPage = 'admins';
$adminPageTitle = 'admin_staff_management';

if(($_SESSION[ADMIN_SESSION_NAME]['role'] ?? '') !== 'super_admin') { die('Access Denied'); }

// Delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if($id === $_SESSION[ADMIN_SESSION_NAME]['id']) {
        setFlash(t('admin_cannot_delete_self'), 'error');
    } else {
        $db->prepare("DELETE FROM admins WHERE id=?")->execute([$id]);
        setFlash('Staff member deleted successfully', 'success');
    }
    header('Location: admins.php'); exit;
}

// Save
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id    = (int)($_POST['admin_id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $role  = $_POST['role'] ?? 'editor';
    $pass  = $_POST['password'] ?? '';

    if($name && $email) {
        if($id > 0) {
            // Update
            if($pass) {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $db->prepare("UPDATE admins SET name=?, email=?, role=?, password=? WHERE id=?")->execute([$name, $email, $role, $hash, $id]);
            } else {
                $db->prepare("UPDATE admins SET name=?, email=?, role=? WHERE id=?")->execute([$name, $email, $role, $id]);
            }
            setFlash('Staff member updated successfully', 'success');
        } else {
            // Insert
            $hash = password_hash($pass ?: 'admin123', PASSWORD_BCRYPT); // default password if empty
            $db->prepare("INSERT INTO admins (name, email, role, password) VALUES (?, ?, ?, ?)")->execute([$name, $email, $role, $hash]);
            setFlash('Staff member added successfully', 'success');
        }
    }
    header('Location: admins.php'); exit;
}

$admins = $db->query("SELECT * FROM admins ORDER BY id DESC")->fetchAll();

$action = $_GET['action'] ?? '';
$editing = null;
if(isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM admins WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch();
    $showForm = true;
} else {
    $showForm = ($action === 'add');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
  <h1 class="admin-title">
    <i class="fas fa-user-shield"></i> <?= t('admin_staff_management') ?>
  </h1>
</div>

<?php $f=getFlash();if($f):?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif;?>

<?php if($showForm):?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= $editing ? t('admin_edit_staff') : t('admin_add_staff') ?></span>
    <a href="admins.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
  <form method="POST" class="admin-form">
    <input type="hidden" name="save" value="1">
    <?php if($editing):?><input type="hidden" name="admin_id" value="<?=$editing['id']?>"><?php endif;?>
    
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_name') ?></label>
        <input type="text" name="name" value="<?=e($editing['name']??'')?>" required>
      </div>
      <div class="form-group">
        <label><?= t('admin_email') ?></label>
        <input type="email" name="email" value="<?=e($editing['email']??'')?>" required>
      </div>
    </div>
    
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_role') ?></label>
        <select name="role" required>
          <option value="super_admin" <?=($editing['role']??'')==='super_admin'?'selected':''?>><?= t('admin_role_super') ?></option>
          <option value="editor" <?=($editing['role']??'')==='editor'?'selected':''?>><?= t('admin_role_editor') ?></option>
          <option value="data_entry" <?=($editing['role']??'')==='data_entry'?'selected':''?>><?= t('admin_role_data') ?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?= t('admin_password_leave') ?></label>
        <input type="password" name="password" <?=!$editing?'required':''?>>
      </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('admin_save_staff') ?></button>
  </form>
</div>
<?php else:?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_all_staff') ?> (<?=count($admins)?>)</span>
    <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= t('admin_add_staff') ?></a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th><?= t('admin_name') ?></th>
          <th><?= t('admin_email') ?></th>
          <th><?= t('admin_role') ?></th>
          <th><?= t('admin_action') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($admins as $a):?>
        <tr>
          <td><strong><?=e($a['name'])?></strong></td>
          <td><?=e($a['email'])?></td>
          <td>
            <?php 
              $roleLabel = t('admin_role_'.$a['role']);
              if($roleLabel === 'admin_role_'.$a['role']) $roleLabel = ucfirst(str_replace('_',' ',$a['role']));
            ?>
            <span class="badge <?= $a['role']==='super_admin'?'badge-success':( $a['role']==='editor'?'badge-warning':'badge-secondary' ) ?>">
              <?= e($roleLabel) ?>
            </span>
          </td>
          <td style="display:flex;gap:6px;">
            <a href="?edit=<?=$a['id']?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
            <?php if($a['id'] !== $_SESSION[ADMIN_SESSION_NAME]['id']): ?>
            <a href="?delete=<?=$a['id']?>" onclick="return confirm('<?= t('admin_delete') ?>?')" class="btn-xs btn-danger"><i class="fas fa-trash"></i></a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php endif;?>

</div></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" crossorigin="anonymous" defer></script>
</body></html>
