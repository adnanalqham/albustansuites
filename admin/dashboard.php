<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'dashboard';
$adminPageTitle = 'admin_dashboard';

// Stats
$totalBookings   = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$totalRevenue    = $db->query("SELECT SUM(total_price) FROM bookings WHERE status NOT IN ('cancelled')")->fetchColumn() ?? 0;
$totalUsers      = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRooms      = $db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$unreadMsgs      = $db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn();
$todayCheckIn    = $db->query("SELECT COUNT(*) FROM bookings WHERE check_in='".date('Y-m-d')."' AND status='confirmed'")->fetchColumn();
$todayCheckOut   = $db->query("SELECT COUNT(*) FROM bookings WHERE check_out='".date('Y-m-d')."' AND status='checked_in'")->fetchColumn();

// Recent bookings
$recentBookings = $db->query("SELECT b.*, r.name_en FROM bookings b LEFT JOIN rooms r ON b.room_id=r.id ORDER BY b.created_at DESC LIMIT 8")->fetchAll();

// Revenue by month (last 6 months)
$monthlyRevenue = $db->query("SELECT DATE_FORMAT(created_at,'%b') as month, SUM(total_price) as rev FROM bookings WHERE status NOT IN ('cancelled') AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY MONTH(created_at) ORDER BY created_at")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<?php $flash = getFlash(); if($flash): ?>
<div class="admin-flash <?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card"><div class="stat-info"><small><?= t('admin_total_revenue') ?></small><h3>$<?= number_format($totalRevenue) ?></h3><p><?= t('admin_all_time') ?></p></div><div class="stat-icon gold"><i class="fas fa-dollar-sign"></i></div></div>
  <div class="stat-card"><div class="stat-info"><small><?= t('admin_total_bookings') ?></small><h3><?= $totalBookings ?></h3><p><?= $pendingBookings ?> <?= t('admin_pending') ?></p></div><div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div></div>
  <div class="stat-card"><div class="stat-info"><small><?= t('admin_registered_guests') ?></small><h3><?= $totalUsers ?></h3><p><?= t('admin_total_accounts') ?></p></div><div class="stat-icon green"><i class="fas fa-users"></i></div></div>
  <div class="stat-card"><div class="stat-info"><small><?= t('admin_unread_messages') ?></small><h3><?= $unreadMsgs ?></h3><p><?= t('admin_need_response') ?></p></div><div class="stat-icon red"><i class="fas fa-envelope"></i></div></div>
</div>

<!-- Today's Activity -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px;">
  <div class="card" style="padding:18px;text-align:center;">
    <i class="fas fa-arrow-down" style="color:var(--success);font-size:22px;margin-bottom:8px;display:block;"></i>
    <strong style="font-size:28px;color:var(--cream);"><?= $todayCheckIn ?></strong>
    <p style="color:var(--gray);font-size:13px;"><?= t('admin_today_checkins') ?></p>
  </div>
  <div class="card" style="padding:18px;text-align:center;">
    <i class="fas fa-arrow-up" style="color:var(--info);font-size:22px;margin-bottom:8px;display:block;"></i>
    <strong style="font-size:28px;color:var(--cream);"><?= $todayCheckOut ?></strong>
    <p style="color:var(--gray);font-size:13px;"><?= t('admin_today_checkouts') ?></p>
  </div>
  <div class="card" style="padding:18px;text-align:center;">
    <i class="fas fa-bed" style="color:var(--gold);font-size:22px;margin-bottom:8px;display:block;"></i>
    <strong style="font-size:28px;color:var(--cream);"><?= $totalRooms ?></strong>
    <p style="color:var(--gray);font-size:13px;"><?= t('admin_total_rooms') ?></p>
  </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom:24px;">
  <div class="card-header"><span class="card-title"><?= t('admin_quick_actions') ?></span></div>
  <div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="bookings.php?status=pending" class="btn btn-primary"><i class="fas fa-clock"></i> <?= t('admin_pending_bookings') ?> (<?= $pendingBookings ?>)</a>
    <a href="rooms.php?action=add" class="btn btn-info"><i class="fas fa-plus"></i> <?= t('admin_add_new_room') ?></a>
    <a href="offers.php?action=add" class="btn btn-success"><i class="fas fa-tag"></i> <?= t('admin_add_offer') ?></a>
    <a href="messages.php" class="btn btn-secondary"><i class="fas fa-envelope"></i> <?= t('admin_view_messages') ?> (<?= $unreadMsgs ?>)</a>
    <a href="settings.php" class="btn btn-secondary"><i class="fas fa-cog"></i> <?= t('admin_settings') ?></a>
  </div>
</div>

<!-- Recent Bookings -->
<div class="card">
  <div class="card-header">
    <span class="card-title"><?= t('admin_recent_bookings') ?></span>
    <a href="bookings.php" class="btn btn-sm btn-secondary"><?= t('admin_view_all') ?></a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th><?= t('admin_ref') ?></th><th><?= t('admin_guest') ?></th><th><?= t('admin_room') ?></th><th><?= t('admin_checkin') ?></th><th><?= t('admin_checkout') ?></th><th><?= t('admin_total') ?></th><th><?= t('admin_status') ?></th><th><?= t('admin_action') ?></th></tr></thead>
      <tbody>
        <?php foreach($recentBookings as $b): ?>
        <tr>
          <td><strong style="color:var(--gold);"><?= e($b['booking_ref']) ?></strong></td>
          <td><?= e($b['guest_name']) ?><br><small style="color:var(--gray);"><?= e($b['guest_email']) ?></small></td>
          <td><?= e($b['name_en']) ?></td>
          <td><?= formatDate($b['check_in']) ?></td>
          <td><?= formatDate($b['check_out']) ?></td>
          <td style="color:var(--gold);font-weight:600;">$<?= number_format($b['total_price']) ?></td>
          <td><?= getStatusBadge($b['status']) ?></td>
          <td><a href="bookings.php?edit=<?= $b['id'] ?>" class="btn-xs btn-info"><i class="fas fa-edit"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" crossorigin="anonymous" defer></script>
</body></html>
