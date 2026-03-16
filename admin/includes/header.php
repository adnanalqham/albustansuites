<?php
// Admin shared header
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
requireAdmin();
$admin = $_SESSION[ADMIN_SESSION_NAME];
$adminRole = $admin['role'] ?? 'super_admin';
$lang = getLang();
$dir = langDir();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($adminPageTitle ?? 'Admin') ?> - Al Bustan Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/admin/assets/css/admin.css?v=1.1">
<?php if($dir === 'rtl'): ?>
<style>
.admin-layout { direction: rtl; }
.admin-sidebar { left: auto; right: 0; border-right: none; border-left: 1px solid var(--admin-border); }
.admin-main { margin-left: auto; margin-right: var(--sidebar-w); }
.topbar-right { margin-left: 0; margin-right: auto; }
.sidebar-item a i { margin-right: 0; margin-left: 12px; }
.sidebar-badge { margin-left: 0; margin-right: auto; }
@media(max-width:900px){ .admin-sidebar{right:-var(--sidebar-w);left:auto;} .admin-main{margin-right:0;} }
</style>
<?php endif; ?>
<?= $adminExtraHead ?? '' ?>
</head>
<body>
<div class="admin-layout">
<!-- Sidebar -->
<aside class="admin-sidebar">
  <div class="sidebar-logo">
    <img src="<?= SITE_URL ?>/images/logo.jpg" alt="Logo" onerror="this.style.display='none'">
    <span>Al Bustan Admin</span>
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-title"><?= t('admin_main') ?></div>
    <div class="sidebar-item <?= ($adminPage??'')==='dashboard'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/dashboard.php"><i class="fas fa-th-large"></i> <?= t('admin_dashboard') ?></a>
    </div>
    <div class="sidebar-section-title"><?= t('admin_hotel_management') ?></div>
    <div class="sidebar-item <?= ($adminPage??'')==='rooms'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/rooms.php"><i class="fas fa-bed"></i> <?= t('admin_rooms') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='room_grid'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/room_grid.php"><i class="fas fa-th"></i> <?= t('admin_room_grid') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='amenities'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/amenities.php"><i class="fas fa-concierge-bell"></i> <?= t('admin_amenities') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='bookings'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/bookings.php"><i class="fas fa-calendar-check"></i> <?= t('admin_bookings') ?>
        <?php try { $cnt=$db->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn(); if($cnt){ echo "<span class='sidebar-badge'>$cnt</span>"; } } catch(Exception $e){} ?>
      </a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='users'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/users.php"><i class="fas fa-users"></i> <?= t('admin_guests') ?></a>
    </div>
    <div class="sidebar-section-title"><?= t('admin_content') ?></div>
    <div class="sidebar-item <?= ($adminPage??'')==='restaurant'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/restaurant.php"><i class="fas fa-utensils"></i> <?= t('admin_menu') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='offers'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/offers.php"><i class="fas fa-tag"></i> <?= t('admin_offers') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='gallery'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/gallery.php"><i class="fas fa-images"></i> <?= t('admin_gallery') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='meetings'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/meetings.php"><i class="fas fa-handshake"></i> <?= t('admin_meetings') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='testimonials'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/testimonials.php"><i class="fas fa-star"></i> <?= t('admin_guest_testimonials') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='messages'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/messages.php"><i class="fas fa-envelope"></i> <?= t('admin_messages') ?>
        <?php try { $mcnt=$db->query("SELECT COUNT(*) FROM messages WHERE is_read=0")->fetchColumn(); if($mcnt){ echo "<span class='sidebar-badge'>$mcnt</span>"; }} catch(Exception $e){} ?>
      </a>
    </div>
    <?php if($adminRole === 'super_admin'): ?>
    <div class="sidebar-section-title"><?= t('admin_system') ?></div>
    <div class="sidebar-item <?= ($adminPage??'')==='admins'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/admins.php"><i class="fas fa-user-shield"></i> <?= t('admin_staff_management') ?></a>
    </div>
    <div class="sidebar-item <?= ($adminPage??'')==='settings'?'active':'' ?>">
      <a href="<?= SITE_URL ?>/admin/settings.php"><i class="fas fa-cog"></i> <?= t('admin_settings') ?></a>
    </div>
    <?php endif; ?>
    <div class="sidebar-item">
      <a href="<?= SITE_URL ?>/index.php" target="_blank"><i class="fas fa-external-link-alt"></i> <?= t('admin_view_website') ?></a>
    </div>
    <div class="sidebar-item">
      <a href="<?= SITE_URL ?>/admin/logout.php" style="color:var(--danger)!important"><i class="fas fa-sign-out-alt" style="color:var(--danger)!important"></i> <?= t('admin_logout') ?></a>
    </div>
  </nav>
</aside>
<!-- Main -->
<div class="admin-main">
  <!-- Topbar -->
  <div class="admin-topbar">
    <div class="topbar-title"><?= e(t($adminPageTitle ?? 'admin_dashboard')) ?></div>
    <div class="topbar-right">
      <a href="?lang=<?= altLang() ?>" style="font-weight:bold; color:var(--cream); background:var(--dark-3); padding:4px 8px; border-radius:4px; text-decoration:none;">
        <i class="fas fa-globe"></i> <?= altLangLabel() ?>
      </a>
      <a href="#"><i class="fas fa-bell"></i></a>
      <div class="admin-avatar"><?= strtoupper(substr($admin['name'],0,1)) ?></div>
      <div>
        <strong style="display:block;font-size:13px;"><?= e($admin['name']) ?></strong>
        <span style="font-size:11px;color:var(--gray);"><?= e($admin['role']) ?></span>
      </div>
    </div>
  </div>
  <div class="admin-content">
