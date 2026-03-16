<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

// Filters
$catSlug = sanitize($_GET['cat'] ?? '');
$whereClause = "WHERE r.is_available=1";
$params = [];
if($catSlug) {
    $whereClause .= " AND rc.slug=?";
    $params[] = $catSlug;
}
$rooms = $db->prepare("SELECT r.*, rc.name_en as cat_en, rc.name_ar as cat_ar FROM rooms r JOIN room_categories rc ON r.category_id=rc.id $whereClause ORDER BY r.sort_order");
$rooms->execute($params);
$rooms = $rooms->fetchAll();

$categories = $db->query("SELECT * FROM room_categories ORDER BY sort_order")->fetchAll();

$pageTitle = t('nav_rooms') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/rooms-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= t('nav_rooms') ?></h1>
    <nav class="breadcrumb">
      <a href="index.php"><?= t('nav_home') ?></a>
      <span>›</span>
      <span><?= t('nav_rooms') ?></span>
    </nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <!-- Category filter tabs -->
    <div class="menu-categories" style="margin-bottom:48px;">
      <a href="rooms.php" class="menu-tab <?= !$catSlug?'active':'' ?>"><?= $lang==='ar'?'الكل':'All'?></a>
      <?php foreach($categories as $cat): ?>
      <a href="?cat=<?= e($cat['slug']) ?>" class="menu-tab <?= $catSlug===$cat['slug']?'active':'' ?>">
        <?= e($cat['name_'.$lang]) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if(empty($rooms)): ?>
    <div style="text-align:center;padding:60px 0;color:var(--gray);">
      <i class="fas fa-bed" style="font-size:50px;color:var(--border);margin-bottom:16px;display:block;"></i>
      <p><?= $lang==='ar'?'لا توجد غرف متاحة حالياً':'No rooms available at the moment'?></p>
    </div>
    <?php else: ?>
    <div class="rooms-grid">
      <?php 
      try {
        foreach($rooms as $room): 
          $desc = $room['short_desc_'.$lang] ?? '';
          $desc = function_exists('mb_substr') ? mb_substr($desc, 0, 120) : substr($desc, 0, 120);
      ?>
      <div class="room-card animate-on-scroll">
        <div class="room-card-img">
          <img src="<?= SITE_URL ?>/<?= e($room['main_image'] ?? 'images/room-default.jpg') ?>" alt="<?= e($room['name_'.$lang]) ?>" loading="lazy">
          <span class="room-card-badge"><?= e($room['cat_'.$lang]) ?></span>
        </div>
        <div class="room-card-body">
          <h3><?= e($room['name_'.$lang]) ?></h3>
          <div class="room-meta">
            <?php if(!empty($room['size_sqm'])): ?><span><i class="fas fa-ruler-combined"></i><?= $room['size_sqm'] ?> <?= t('sqm') ?></span><?php endif; ?>
            <?php if(!empty($room['capacity_adults'])): ?><span><i class="fas fa-users"></i><?= $room['capacity_adults'] ?> <?= t('adults') ?></span><?php endif; ?>
            <?php if(!empty($room['view_type_'.$lang])): ?><span><i class="fas fa-mountain"></i><?= e($room['view_type_'.$lang]) ?></span><?php endif; ?>
          </div>
          <p class="room-desc"><?= e($desc) ?>...</p>
          <div class="room-card-footer">
            <div class="room-price">
              <span class="from"><?= $lang==='ar'?'يبدأ من':'Starting'?></span>
              <span class="amount"><?= formatPrice((float)($room['price_per_night']??0), $room['currency']??'USD') ?></span>
              <span class="per"><?= t('per_night') ?></span>
            </div>
            <div style="display:flex;gap:8px;">
              <a href="room-detail.php?slug=<?= e($room['slug']??'') ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
              <a href="booking.php?room=<?= e($room['slug']??'') ?>" class="btn btn-primary btn-sm"><?= t('nav_book_now') ?></a>
            </div>
          </div>
        </div>
      </div>
      <?php 
        endforeach; 
      } catch (Exception $e) {
        echo "<div style='color:red;padding:20px;text-align:center;'>Error loading rooms: " . e($e->getMessage()) . "</div>";
      } catch (TypeError $e) {
        echo "<div style='color:red;padding:20px;text-align:center;'>Type Error: " . e($e->getMessage()) . "</div>";
      }
      ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
