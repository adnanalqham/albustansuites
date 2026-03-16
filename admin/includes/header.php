<?php
// Admin shared header
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
requireAdmin();
$admin = $_SESSION[ADMIN_SESSION_NAME];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($adminPageTitle ?? 'Admin') ?> - Al Bustan Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/admin.css?v=1.1">
<?= $adminExtraHead ?? '' ?>
</head>
<body>
<div class="admin-layout">
<!-- Sidebar -->
<aside class="admin-sidebar">
  <div class="sidebar-logo">
    <img src="<?= SITE_URL ?>/images/logo.png" alt="Logo" onerror="this.style.display='none'">
    <span>Al Bustan Admin</span>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-title">Main</div>
    <div class="sidebar-item <?= ($adminPage??'')==='dashboard'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
    </div>
    <div class="sidebar-section-title">Hotel Management</div>
    <div class="sidebar-item <?= ($adminPage??'')==='rooms'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/rooms.php"><i class="fas fa-bed"></i> Rooms</a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='bookings'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/bookings.php"><i class="fas fa-calendar-check"></i> Bookings
        <?php try { $cnt=$db->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn(); if($cnt){ echo "<span class='sidebar-badge'>$cnt</span>"; } } catch(Exception $e){} ?>
      </a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='users'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/users.php"><i class="fas fa-users"></i> Guests</a>
    </div>
    <div class="sidebar-section-title">Content</div>
    <div class="sidebar-item <?= ($adminPage??'')==='restaurant'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/restaurant.php"><i class="fas fa-utensils"></i> Menu</a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='offers'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/offers.php"><i class="fas fa-tag"></i> Offers</a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='gallery'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/gallery.php"><i class="fas fa-images"></i> Gallery</a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='meetings'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/meetings.php"><i class="fas fa-handshake"></i> Meetings</a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='messages'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/messages.php"><i class="fas fa-envelope"></i> Messages
        <?php try { $mcnt=$db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn(); if($mcnt){ echo "<span class='sidebar-badge'>$mcnt</span>"; }} catch(Exception $e){} ?>
      </a>
    </div>
    <div class="sidebar-section-title">System</div>
    <div class="sidebar-item <?= ($adminPage??'')==='settings'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
    </div>
    <div class="sidebar-item">
      <a href="<?= SITE_URL ?>/index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
    </div>
    <div class="sidebar-item">
      <a href="<?= SITE_URL ?>/admin/logout.php" style="color:var(--danger)!important"><i class="fas fa-sign-out-alt" style="color:var(--danger)!important"></i> Logout</a>
    </div>
  </nav>
</aside>
<!-- Main -->
<div class="admin-main">
  <!-- Topbar -->
  <div class="admin-topbar">
    <div class="topbar-title"><?= e($adminPageTitle ?? 'Dashboard') ?></div>
    <div class="topbar-right">
      <a href="#"><i class="fas fa-bell"></i></a>
      <div class="admin-avatar"><?= strtoupper(substr($admin['name'],0,1)) ?></div>
      <div>
        <strong style="display:block;font-size:13px;"><?= e($admin['name']) ?></strong>
        <span style="font-size:11px;color:var(--gray);"><?= e($admin['role']) ?></span>
      </div>
    </div>
  </div>
  <div class="admin-content">
