<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$offers = $db->query("SELECT * FROM offers WHERE is_active=1 ORDER BY id DESC")->fetchAll();
$pageTitle = t('special_offers') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>
<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/offers-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= t('special_offers') ?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><span><?= t('special_offers') ?></span></nav>
  </div>
</div>
<section class="section">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'عروض حصرية':'Exclusive Deals'?></div>
      <h2><?= t('special_offers') ?></h2>
    </div>
    <?php if(empty($offers)): ?>
    <p style="text-align:center;color:var(--gray);padding:60px 0;"><?= $lang==='ar'?'لا توجد عروض متاحة حالياً':'No offers available at the moment'?></p>
    <?php else: ?>
    <div class="offers-grid">
      <?php foreach($offers as $offer): ?>
      <div class="offer-card animate-on-scroll">
        <div class="offer-img">
          <img src="<?= SITE_URL ?>/<?= e($offer['image']??'images/offer-default.jpg') ?>" alt="<?= e($offer['title_'.$lang]) ?>">
          <div class="offer-discount"><?= $offer['discount_type']==='percentage'?round($offer['discount_value']).'%':formatPrice($offer['discount_value'])?><br><small><?= $lang==='ar'?'خصم':'OFF'?></small></div>
        </div>
        <div class="offer-body">
          <h3><?= e($offer['title_'.$lang]) ?></h3>
          <p><?= e($offer['description_'.$lang]??'') ?></p>
          <?php if($offer['promo_code']): ?><div style="margin:12px 0;"><span class="offer-code"><i class="fas fa-tag"></i> <?= e($offer['promo_code']) ?></span></div><?php endif; ?>
          <?php if($offer['valid_to']): ?><p style="font-size:12px;color:var(--gray);"><i class="fas fa-calendar"></i> <?= $lang==='ar'?'ينتهي في:':'Expires:'?> <?= formatDate($offer['valid_to']) ?></p><?php endif; ?>
          <?php if($offer['min_nights'] > 1): ?><p style="font-size:12px;color:var(--gray);"><i class="fas fa-moon"></i> <?= $lang==='ar'?'الحد الأدنى':'Minimum'?>: <?= $offer['min_nights'] ?> <?= t('nights') ?></p><?php endif; ?>
          <a href="booking.php?offer=<?= $offer['id'] ?>" class="btn btn-primary" style="margin-top:20px;width:100%;justify-content:center;"><?= t('nav_book_now') ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
