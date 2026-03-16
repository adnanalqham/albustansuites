<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'bookings';
$adminPageTitle = 'admin_manage_bookings';

// Status filter
$filterStatus = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['q'] ?? '');

// Update status action
if(isset($_POST['update_status'])) {
    $db->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$_POST['status'], (int)$_POST['booking_id']]);
    setFlash('success','Booking status updated');
    header('Location: bookings.php'); exit;
}

// Single booking detail
$editBooking = null;
if(isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT b.*, r.name_en as room_name FROM bookings b LEFT JOIN rooms r ON b.room_id=r.id WHERE b.id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editBooking = $stmt->fetch();
}

// Fetch bookings
$whereQ = "WHERE 1=1";
$params = [];
if($filterStatus) { $whereQ .= " AND b.status=?"; $params[] = $filterStatus; }
if($search) { $whereQ .= " AND (b.booking_ref LIKE ? OR b.guest_name LIKE ? OR b.guest_email LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
$stmt = $db->prepare("SELECT b.*, r.name_en as room_name FROM bookings b LEFT JOIN rooms r ON b.room_id=r.id $whereQ ORDER BY b.created_at DESC LIMIT 100");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<?php $f=getFlash(); if($f): ?><div class="admin-flash <?= $f['type'] ?>"><?= e($f['message']) ?></div><?php endif; ?>

<?php if($editBooking): ?>
<!-- EDIT BOOKING MODAL -->
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_booking_ref') ?>: <?= e($editBooking['booking_ref']) ?></span>
    <a href="bookings.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> <?= t('admin_back') ?></a>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div>
      <table><tbody>
        <tr><td style="color:var(--gray);padding:8px 0;width:140px;"><?= t('admin_guest_name') ?></td><td><strong><?= e($editBooking['guest_name']) ?></strong></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_email') ?></td><td><?= e($editBooking['guest_email']) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_phone') ?></td><td><?= e($editBooking['guest_phone']) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_nationality') ?></td><td><?= e($editBooking['guest_nationality']) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_room') ?></td><td><?= e($editBooking['room_name']) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_checkin') ?></td><td><?= formatDate($editBooking['check_in']) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_checkout') ?></td><td><?= formatDate($editBooking['check_out']) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_nights') ?></td><td><?= $editBooking['nights'] ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_adults') ?></td><td><?= $editBooking['adults'] ?> + <?= $editBooking['children'] ?> <?= t('admin_children') ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_payment') ?></td><td><?= e($editBooking['payment_method']) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_total') ?></td><td style="font-weight:700;color:var(--gold);">$<?= number_format($editBooking['total_price'],2) ?></td></tr>
        <tr><td style="color:var(--gray);padding:8px 0;"><?= t('admin_requests') ?></td><td><?= e($editBooking['special_requests'] ?: 'None') ?></td></tr>
      </tbody></table>
    </div>
    <div>
      <form method="POST" class="admin-form">
        <input type="hidden" name="update_status" value="1">
        <input type="hidden" name="booking_id" value="<?= $editBooking['id'] ?>">
        <div class="form-group">
          <label><?= t('admin_update_status') ?></label>
          <select name="status">
            <?php foreach(['pending','confirmed','checked_in','checked_out','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $editBooking['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('admin_update_status') ?></button>
      </form>
      <div style="margin-top:16px;padding:14px;background:var(--admin-darker);border-radius:8px;">
        <p style="font-size:12px;color:var(--gray);"><?= t('admin_current_status') ?></p>
        <?= getStatusBadge($editBooking['status']) ?>
        <p style="font-size:12px;color:var(--gray);margin-top:10px;"><?= t('admin_booked_on') ?>: <?= formatDate($editBooking['created_at']) ?></p>
      </div>
    </div>
  </div>
</div>
<?php else: ?>

<!-- BOOKINGS LIST -->
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_all_bookings') ?> (<?= count($bookings) ?>)</span>
  </div>
  <div class="filters-bar">
    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <input type="text" name="q" placeholder="<?= t('admin_search_bookings') ?>" value="<?= e($search) ?>">
      <select name="status" onchange="this.form.submit()">
        <option value=""><?= t('admin_all_statuses') ?></option>
        <?php foreach(['pending','confirmed','checked_in','checked_out','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
      <?php if($filterStatus||$search): ?><a href="bookings.php" class="btn btn-secondary btn-sm"><?= t('admin_clear') ?></a><?php endif; ?>
    </form>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th><?= t('admin_ref') ?></th><th><?= t('admin_guest') ?></th><th><?= t('admin_room') ?></th><th><?= t('admin_dates') ?></th><th><?= t('admin_nights') ?></th><th><?= t('admin_total') ?></th><th><?= t('admin_payment') ?></th><th><?= t('admin_status') ?></th><th><?= t('admin_action') ?></th></tr></thead>
      <tbody>
        <?php if(empty($bookings)): ?>
        <tr><td colspan="9" style="text-align:center;color:var(--gray);padding:30px;"><?= t('admin_no_bookings') ?></td></tr>
        <?php else: ?>
        <?php foreach($bookings as $b): ?>
        <tr>
          <td><strong style="color:var(--gold);"><?= e($b['booking_ref']) ?></strong></td>
          <td><?= e($b['guest_name']) ?><br><small style="color:var(--gray);"><?= e($b['guest_phone']) ?></small></td>
          <td><small><?= e($b['room_name']) ?></small></td>
          <td><small><?= formatDate($b['check_in']) ?><br>→ <?= formatDate($b['check_out']) ?></small></td>
          <td style="text-align:center;"><?= $b['nights'] ?></td>
          <td style="color:var(--gold);font-weight:700;">$<?= number_format($b['total_price']) ?></td>
          <td><small><?= e(str_replace('_',' ',$b['payment_method'])) ?></small></td>
          <td><?= getStatusBadge($b['status']) ?></td>
          <td><a href="bookings.php?edit=<?= $b['id'] ?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
</div></div></div></body></html>
