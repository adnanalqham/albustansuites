<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();
$db = getDB();
$adminPage = 'dashboard';
$adminPageTitle = 'Dashboard';

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
  <div class="stat-card"><div class="stat-info"><small>Total Revenue</small><h3>$<?= number_format($totalRevenue) ?></h3><p>All time</p></div><div class="stat-icon gold"><i class="fas fa-dollar-sign"></i></div></div>
  <div class="stat-card"><div class="stat-info"><small>Total Bookings</small><h3><?= $totalBookings ?></h3><p><?= $pendingBookings ?> pending</p></div><div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div></div>
  <div class="stat-card"><div class="stat-info"><small>Registered Guests</small><h3><?= $totalUsers ?></h3><p>Total accounts</p></div><div class="stat-icon green"><i class="fas fa-users"></i></div></div>
  <div class="stat-card"><div class="stat-info"><small>Unread Messages</small><h3><?= $unreadMsgs ?></h3><p>Need response</p></div><div class="stat-icon red"><i class="fas fa-envelope"></i></div></div>
</div>

<!-- Today's Activity -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px;">
  <div class="card" style="padding:18px;text-align:center;">
    <i class="fas fa-arrow-down" style="color:var(--success);font-size:22px;margin-bottom:8px;display:block;"></i>
    <strong style="font-size:28px;color:var(--cream);"><?= $todayCheckIn ?></strong>
    <p style="color:var(--gray);font-size:13px;">Today's Check-ins</p>
  </div>
  <div class="card" style="padding:18px;text-align:center;">
    <i class="fas fa-arrow-up" style="color:var(--info);font-size:22px;margin-bottom:8px;display:block;"></i>
    <strong style="font-size:28px;color:var(--cream);"><?= $todayCheckOut ?></strong>
    <p style="color:var(--gray);font-size:13px;">Today's Check-outs</p>
  </div>
  <div class="card" style="padding:18px;text-align:center;">
    <i class="fas fa-bed" style="color:var(--gold);font-size:22px;margin-bottom:8px;display:block;"></i>
    <strong style="font-size:28px;color:var(--cream);"><?= $totalRooms ?></strong>
    <p style="color:var(--gray);font-size:13px;">Total Rooms</p>
  </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom:24px;">
  <div class="card-header"><span class="card-title">Quick Actions</span></div>
  <div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="bookings.php?status=pending" class="btn btn-primary"><i class="fas fa-clock"></i> Pending Bookings (<?= $pendingBookings ?>)</a>
    <a href="rooms.php?action=add" class="btn btn-info"><i class="fas fa-plus"></i> Add New Room</a>
    <a href="offers.php?action=add" class="btn btn-success"><i class="fas fa-tag"></i> Add Offer</a>
    <a href="messages.php" class="btn btn-secondary"><i class="fas fa-envelope"></i> View Messages (<?= $unreadMsgs ?>)</a>
    <a href="settings.php" class="btn btn-secondary"><i class="fas fa-cog"></i> Settings</a>
  </div>
</div>

<!-- Recent Bookings -->
<div class="card">
  <div class="card-header">
    <span class="card-title">Recent Bookings</span>
    <a href="bookings.php" class="btn btn-sm btn-secondary">View All</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Ref #</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
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
