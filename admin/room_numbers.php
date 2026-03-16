<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();

$db = getDB();
$adminPage = 'rooms'; // Keep 'rooms' active in sidebar
$adminPageTitle = 'admin_manage_room_numbers';

$roomId = (int)($_GET['room_id'] ?? 0);
if(!$roomId) { header('Location: rooms.php'); exit; }

// Auto-create table
$db->exec("CREATE TABLE IF NOT EXISTS room_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    status ENUM('available', 'maintenance') DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Get Room Info
$stmt = $db->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$roomId]);
$room = $stmt->fetch();
if(!$room) { header('Location: rooms.php'); exit; }

// Delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM room_numbers WHERE id=? AND room_id=?")->execute([(int)$_GET['delete'], $roomId]);
    setFlash('success', 'Room number deleted');
    header('Location: room_numbers.php?room_id='.$roomId); exit;
}

// Toggle status
if(isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $n = $db->prepare("SELECT status FROM room_numbers WHERE id=?")->execute([(int)$_GET['toggle']]);
    $stmt = $db->prepare("SELECT status FROM room_numbers WHERE id=?");
    $stmt->execute([(int)$_GET['toggle']]);
    $curr = $stmt->fetchColumn();
    $newStatus = $curr === 'available' ? 'maintenance' : 'available';
    $db->prepare("UPDATE room_numbers SET status=? WHERE id=?")->execute([$newStatus, (int)$_GET['toggle']]);
    header('Location: room_numbers.php?room_id='.$roomId); exit;
}

// Save
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_number'])) {
    $numId      = (int)($_POST['number_id'] ?? 0);
    $roomNumber = trim($_POST['room_number'] ?? '');
    $status     = $_POST['status'] ?? 'available';

    if($roomNumber) {
        if($numId > 0) {
            $db->prepare("UPDATE room_numbers SET room_number=?, status=? WHERE id=?")->execute([$roomNumber, $status, $numId]);
            setFlash('success', 'Room number updated');
        } else {
            // Check duplicate
            $chk = $db->prepare("SELECT id FROM room_numbers WHERE room_id=? AND room_number=?");
            $chk->execute([$roomId, $roomNumber]);
            if($chk->fetch()) {
                setFlash('error', 'This room number already exists for this room type.');
            } else {
                $db->prepare("INSERT INTO room_numbers (room_id, room_number, status) VALUES (?, ?, ?)")->execute([$roomId, $roomNumber, $status]);
                setFlash('success', 'Room number added');
            }
        }
    }
    header('Location: room_numbers.php?room_id='.$roomId); exit;
}

$numbers = $db->prepare("SELECT * FROM room_numbers WHERE room_id=? ORDER BY room_number ASC");
$numbers->execute([$roomId]);
$numbers = $numbers->fetchAll();

$action = $_GET['action'] ?? '';
$editing = null;
if(isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM room_numbers WHERE id=? AND room_id=?");
    $stmt->execute([(int)$_GET['edit'], $roomId]);
    $editing = $stmt->fetch();
    $showForm = true;
} else {
    $showForm = ($action === 'add');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
  <h1 class="admin-title">
    <i class="fas fa-list-ol"></i> <?= t('admin_manage_room_numbers') ?>: <?= e($room['name_'.getLang()] ?? $room['name_en']) ?>
  </h1>
</div>

<?php $f=getFlash();if($f):?><div class="admin-flash <?=$f['type']?>"><?=e($f['message'])?></div><?php endif;?>

<?php if($showForm):?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= $editing ? t('admin_edit_room_number') : t('admin_add_room_number') ?></span>
    <a href="room_numbers.php?room_id=<?= $roomId ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> <?= t('admin_back') ?></a>
  </div>
  <form method="POST" class="admin-form">
    <input type="hidden" name="save_number" value="1">
    <?php if($editing):?><input type="hidden" name="number_id" value="<?=$editing['id']?>"><?php endif;?>
    
    <div class="form-row">
      <div class="form-group">
        <label><?= t('admin_room_number') ?></label>
        <input type="text" name="room_number" value="<?=e($editing['room_number']??'')?>" placeholder="e.g. 101, 102A" required>
      </div>
      <div class="form-group">
        <label><?= t('admin_status') ?></label>
        <select name="status" required>
          <option value="available" <?=($editing['status']??'')==='available'?'selected':''?>><?= t('admin_room_status_available') ?></option>
          <option value="maintenance" <?=($editing['status']??'')==='maintenance'?'selected':''?>><?= t('admin_room_status_maintenance') ?></option>
        </select>
      </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('admin_save_room_number') ?></button>
  </form>
</div>
<?php else:?>
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_room_numbers') ?> (<?=count($numbers)?>)</span>
    <div>
        <a href="rooms.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> <?= t('admin_rooms') ?></a>
        <a href="?room_id=<?= $roomId ?>&action=add" class="btn btn-primary btn-sm" style="margin-left:8px;"><i class="fas fa-plus"></i> <?= t('admin_add_room_number') ?></a>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th><?= t('admin_room_number') ?></th>
          <th><?= t('admin_status') ?></th>
          <th><?= t('admin_action') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($numbers as $n):?>
        <tr>
          <td><strong style="font-size:16px;color:var(--gold);"><?=e($n['room_number'])?></strong></td>
          <td>
            <?php if($n['status']==='available'): ?>
                <span class="badge badge-success"><?= t('admin_room_status_available') ?></span>
            <?php else: ?>
                <span class="badge badge-warning"><?= t('admin_room_status_maintenance') ?></span>
            <?php endif; ?>
          </td>
          <td style="display:flex;gap:6px;">
            <a href="?room_id=<?= $roomId ?>&edit=<?=$n['id']?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a>
            <a href="?room_id=<?= $roomId ?>&toggle=<?=$n['id']?>" class="btn-xs btn-secondary" title="Toggle Status"><i class="fas fa-sync-alt"></i></a>
            <a href="?room_id=<?= $roomId ?>&delete=<?=$n['id']?>" onclick="return confirm('<?= t('admin_delete') ?>?')" class="btn-xs btn-danger"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach;?>
        <?php if(empty($numbers)): ?>
        <tr><td colspan="3" style="text-align:center;color:var(--gray);"><?= t('admin_no_room_numbers') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif;?>

</div></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" crossorigin="anonymous" defer></script>
</body></html>
