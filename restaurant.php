<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$pageTitle = t('nav_restaurant') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/<?= e(getSetting('restaurant_hero_image', 'images/restaurant-hero.jpg')) ?>')"></div>
  <div class="page-hero-content">
    <h1><?= t('nav_restaurant') ?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><span><?= t('nav_restaurant') ?></span></nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
      <div class="animate-on-scroll">
        <div class="section-title" style="text-align:start;margin-bottom:24px;">
          <div class="subtitle" style="justify-content:flex-start;"><?= e(getSetting('restaurant_subtitle_'.$lang) ?: ($lang==='ar'?'تجربة طعام استثنائية':'An Exceptional Dining Experience')) ?></div>
          <h2><?= e(getSetting('restaurant_title_'.$lang) ?: t('our_restaurant')) ?></h2>
        </div>
        <p style="color:var(--gray);font-size:16px;line-height:1.9;margin-bottom:20px;"><?= e(getSetting('restaurant_desc_'.$lang) ?: t('restaurant_desc')) ?></p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:28px 0;">
          <div style="padding:16px;background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius-sm);">
            <i class="fas fa-coffee" style="color:var(--gold);font-size:20px;margin-bottom:8px;display:block;"></i>
            <strong style="color:var(--cream);display:block;margin-bottom:4px;"><?= $lang==='ar'?'الإفطار':'Breakfast'?></strong>
            <span style="font-size:13px;color:var(--gray);"><?= e(getSetting('restaurant_breakfast', '6:30 AM - 10:30 AM')) ?></span>
          </div>
          <div style="padding:16px;background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius-sm);">
            <i class="fas fa-sun" style="color:var(--gold);font-size:20px;margin-bottom:8px;display:block;"></i>
            <strong style="color:var(--cream);display:block;margin-bottom:4px;"><?= $lang==='ar'?'الغداء':'Lunch'?></strong>
            <span style="font-size:13px;color:var(--gray);"><?= e(getSetting('restaurant_lunch', '12:30 PM - 3:00 PM')) ?></span>
          </div>
          <div style="padding:16px;background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius-sm);">
            <i class="fas fa-moon" style="color:var(--gold);font-size:20px;margin-bottom:8px;display:block;"></i>
            <strong style="color:var(--cream);display:block;margin-bottom:4px;"><?= $lang==='ar'?'العشاء':'Dinner'?></strong>
            <span style="font-size:13px;color:var(--gray);"><?= e(getSetting('restaurant_dinner', '7:00 PM - 11:00 PM')) ?></span>
          </div>
          <div style="padding:16px;background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius-sm);">
            <i class="fas fa-clock" style="color:var(--gold);font-size:20px;margin-bottom:8px;display:block;"></i>
            <strong style="color:var(--cream);display:block;margin-bottom:4px;"><?= $lang==='ar'?'خدمة الغرف':'Room Service'?></strong>
            <span style="font-size:13px;color:var(--gray);"><?= e(getSetting('restaurant_service', '24 Hours')) ?></span>
          </div>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <a href="menu.php" class="btn btn-primary"><i class="fas fa-book-open"></i> <?= t('explore_menu') ?></a>
          <a href="contact.php?type=restaurant" class="btn btn-outline"><i class="fas fa-phone"></i> <?= $lang==='ar'?'احجز طاولة':'Reserve a Table'?></a>
        </div>
      </div>
      <div class="animate-on-scroll">
        <img src="<?= SITE_URL ?>/<?= e(getSetting('restaurant_image', 'images/restaurant.jpg')) ?>" alt="<?= e(getSetting('restaurant_title_'.$lang) ?: 'Restaurant') ?>" loading="lazy" style="width:100%;height:480px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);">
      </div>
    </div>
  </div>
</section>

<!-- Features -->
<section class="section" style="background:var(--dark-2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
  <div class="container">
    <div class="features-grid">
      <?php
      $features = $lang==='ar'
        ? [['fas fa-utensils','مطبخ عالمي وعربي','يجمع مطعمنا بين أشهى الأطباق العربية الأصيلة والمأكولات العالمية الراقية المعدة بأجود المكونات الطازجة على يد طهاة محترفين.'],
           ['fas fa-leaf','مكونات طازجة','نحرص على انتقاء أجود المكونات الطازجة المحلية والمستوردة لضمان تقديم أفضل تجربة طعام.'],
           ['fas fa-wine-glass','أجواء فاخرة','استمتع بتجربة طعام لا تُنسى في أجواء من الأناقة والفخامة مع إضاءة دافئة وديكور راقي.'],
           ['fas fa-concierge-bell','خدمة غرف 24/7','خدمة غرف على مدار الساعة لضمان حصولك على وجبتك المفضلة في أي وقت تشاء.']]
        : [['fas fa-utensils','International & Arabic Cuisine','Our restaurant combines authentic Arabic dishes with international haute cuisine prepared with the finest fresh ingredients by professional chefs.'],
           ['fas fa-leaf','Fresh Ingredients','We carefully select the finest local and imported fresh ingredients to ensure the best dining experience.'],
           ['fas fa-wine-glass','Luxurious Ambiance','Enjoy an unforgettable dining experience in an elegant and luxurious atmosphere with warm lighting and refined decor.'],
           ['fas fa-concierge-bell','24/7 Room Service','Round-the-clock room service to ensure you enjoy your favorite meal anytime you desire.']];
      foreach($features as $f): ?>
      <div class="feature-card animate-on-scroll">
        <div class="feature-icon"><i class="<?= $f[0] ?>"></i></div>
        <h3><?= $f[1] ?></h3>
        <p><?= $f[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA to Menu -->
<section style="padding:60px 0;text-align:center;">
  <div class="container animate-on-scroll">
    <h3 style="font-family:var(--font-serif);font-size:36px;color:var(--cream);margin-bottom:14px;"><?= $lang==='ar'?'تصفح قائمة طعامنا':'Browse Our Menu'?></h3>
    <p style="color:var(--gray);max-width:540px;margin:0 auto 28px;"><?= $lang==='ar'?'اكتشف مجموعة واسعة من الأطباق العربية والعالمية الشهية التي يعدّها طهاتنا المحترفون':'Discover a wide range of delicious Arabic and international dishes prepared by our professional chefs'?></p>
    <a href="menu.php" class="btn btn-primary btn-lg"><i class="fas fa-book-open"></i> <?= $lang==='ar'?'اعرض القائمة كاملة':'View Full Menu'?></a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
