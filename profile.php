<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
requireUser();
$lang = getLang();
$db   = getDB();
$user = getUser();

// Fetch bookings
$bookings = $db->prepare("SELECT b.*, r.name_en as room_en, r.name_ar as room_ar, r.main_image FROM bookings b LEFT JOIN rooms r ON b.room_id=r.id WHERE b.user_id=? ORDER BY b.created_at DESC");
$bookings->execute([$user['id']]);
$bookings = $bookings->fetchAll();

// Handle profile update
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $name  = sanitize($_POST['name']  ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $db->prepare("UPDATE users SET name=?,phone=? WHERE id=?")->execute([$name,$phone,$user['id']]);
    $_SESSION[USER_SESSION_NAME]['name']  = $name;
    $_SESSION[USER_SESSION_NAME]['phone'] = $phone;
    setFlash('success', $lang==='ar'?'تم تحديث البيانات بنجاح':'Profile updated successfully');
    header('Location: profile.php'); exit;
}

// Handle booking cancel
if(isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $bid = (int)$_GET['cancel'];
    $db->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'")->execute([$bid, $user['id']]);
    setFlash('info', $lang==='ar'?'تم إلغاء الحجز':'Booking cancelled');
    header('Location: profile.php'); exit;
}

$pageTitle = t('profile_title') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>
<div class="page-hero" style="height:220px;">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/hero-1.jpg');"></div>
  <div class="page-hero-content"><h1><?= t('profile_title') ?></h1></div>
</div>

<section class="section">
  <div class="container">
    <?= renderFlash() ?>
    <div style="display:grid;grid-template-columns:280px 1fr;gap:40px;align-items:start;">
      <!-- Sidebar -->
      <div style="background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius);padding:28px;">
        <div style="text-align:center;padding-bottom:24px;border-bottom:1px solid var(--border);">
          <div style="width:80px;height:80px;background:var(--dark-3);border:2px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:32px;color:var(--gold);"><i class="fas fa-user"></i></div>
          <strong style="display:block;color:var(--cream);font-size:18px;"><?= e($user['name']) ?></strong>
          <span style="font-size:13px;color:var(--gray);"><?= e($user['email']) ?></span>
        </div>
        <nav style="margin-top:20px;display:flex;flex-direction:column;gap:6px;">
          <a href="#bookings" style="padding:10px 14px;border-radius:8px;font-size:14px;color:var(--cream);display:flex;align-items:center;gap:10px;background:rgba(201,168,76,0.1);border:1px solid var(--border);"><i class="fas fa-calendar" style="color:var(--gold);"></i><?= t('my_bookings') ?></a>
          <a href="#edit" style="padding:10px 14px;border-radius:8px;font-size:14px;color:var(--gray);display:flex;align-items:center;gap:10px;"><i class="fas fa-edit" style="color:var(--gold);"></i><?= t('edit_profile') ?></a>
          <a href="logout.php" style="padding:10px 14px;border-radius:8px;font-size:14px;color:#ff6b6b;display:flex;align-items:center;gap:10px;"><i class="fas fa-sign-out-alt"></i><?= t('nav_logout') ?></a>
        </nav>
      </div>

      <!-- Main Content -->
      <div>
        <!-- My Bookings -->
        <div id="bookings" style="background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius);padding:28px;margin-bottom:28px;">
          <h2 style="font-family:var(--font-serif);font-size:22px;color:var(--cream);margin-bottom:20px;"><?= t('my_bookings') ?> (<?= count($bookings) ?>)</h2>
          <?php if(empty($bookings)): ?>
          <div style="text-align:center;padding:40px 0;color:var(--gray);">
            <i class="fas fa-calendar-xmark" style="font-size:40px;color:var(--border);display:block;margin-bottom:14px;"></i>
            <?= t('no_bookings') ?><br><br>
            <a href="booking.php" class="btn btn-primary btn-sm"><?= t('nav_book_now') ?></a>
          </div>
          <?php else: ?>
          <?php foreach($bookings as $b): ?>
          <div style="background:var(--dark-3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:20px;margin-bottom:16px;display:grid;grid-template-columns:70px 1fr auto;gap:16px;align-items:center;">
            <img src="<?= SITE_URL ?>/<?= e($b['main_image']??'images/room-default.jpg') ?>" style="width:70px;height:60px;object-fit:cover;border-radius:6px;">
            <div>
              <strong style="color:var(--cream);display:block;"><?= e($b['room_'.$lang]) ?></strong>
              <span style="font-size:13px;color:var(--gray);"><i class="fas fa-calendar"></i> <?= formatDate($b['check_in']) ?> → <?= formatDate($b['check_out']) ?></span><br>
              <span style="font-size:12px;color:var(--gold);"><?= t('booking_ref') ?>: <?= e($b['booking_ref']) ?></span>
            </div>
            <div style="text-align:end;">
              <?= getStatusBadge($b['status']) ?><br>
              <span style="font-size:16px;font-weight:700;color:var(--gold);display:block;margin-top:6px;"><?= formatPrice($b['total_price'],$b['currency']) ?></span>
              <?php if($b['status']==='pending'): ?>
              <a href="?cancel=<?= $b['id'] ?>" class="btn btn-sm" style="background:rgba(220,50,50,0.1);border:1px solid rgba(220,50,50,0.3);color:#ff6b6b;border-radius:20px;padding:5px 14px;font-size:12px;margin-top:6px;display:inline-block;" onclick="return confirm('<?= $lang==='ar'?'هل أنت متأكد من إلغاء الحجز؟':'Are you sure you want to cancel?'?>')"><?= t('cancel_booking') ?></a>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Edit Profile -->
        <div id="edit" style="background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius);padding:28px;">
          <h2 style="font-family:var(--font-serif);font-size:22px;color:var(--cream);margin-bottom:20px;"><?= t('edit_profile') ?></h2>
          <form method="POST">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-row">
              <div class="form-group"><label><?= t('full_name') ?></label><input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>"></div>
              <div class="form-group"><label><?= t('phone') ?></label><input type="tel" name="phone" class="form-control" value="<?= e($user['phone']??'') ?>"></div>
            </div>
            <div class="form-group"><label><?= t('email') ?></label><input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= t('save') ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
