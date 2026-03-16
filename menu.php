<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$categories = $db->query("SELECT * FROM menu_categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();
$menuItems  = $db->query("SELECT mi.*, mc.name_en as cat_name_en, mc.name_ar as cat_name_ar FROM menu_items mi JOIN menu_categories mc ON mi.category_id=mc.id WHERE mi.is_available=1 ORDER BY mi.category_id, mi.sort_order")->fetchAll();

// Group by category
$grouped = [];
foreach($menuItems as $item) {
    $grouped[$item['category_id']][] = $item;
}

$pageTitle = t('nav_menu') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>
<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/menu-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= t('nav_menu') ?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><a href="restaurant.php"><?= t('nav_restaurant') ?></a><span>›</span><span><?= t('nav_menu') ?></span></nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'تذوق الأصالة':'Taste Authenticity'?></div>
      <h2><?= $lang==='ar'?'قائمة طعامنا':'Our Menu'?></h2>
      <p><?= $lang==='ar'?'أشهى الأطباق العربية والعالمية يعدها طهاتنا بأفضل المكونات الطازجة':'The finest Arabic and international dishes prepared by our chefs with the best fresh ingredients'?></p>
    </div>

    <!-- Category Tabs -->
    <div class="menu-categories animate-on-scroll">
      <?php foreach($categories as $i => $cat): ?>
      <button class="menu-tab <?= $i===0?'active':'' ?>" data-cat="cat_<?= $cat['id'] ?>">
        <?php if($cat['icon']): ?><i class="fas <?= e($cat['icon']) ?>"></i><?php endif; ?>
        <?= e($cat['name_'.$lang]) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Menu Panels -->
    <?php foreach($categories as $i => $cat): ?>
    <div class="menu-panel" data-cat="cat_<?= $cat['id'] ?>" style="display:<?= $i===0?'grid':'none'?>;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
      <?php if(isset($grouped[$cat['id']])): ?>
        <?php foreach($grouped[$cat['id']] as $item): ?>
        <div class="menu-item-card animate-on-scroll">
          <div class="menu-item-img">
            <img src="<?= SITE_URL ?>/<?= e($item['image'] ?? 'images/food-default.jpg') ?>" alt="<?= e($item['name_'.$lang]) ?>" loading="lazy">
          </div>
          <div class="menu-item-body">
            <h4><?= e($item['name_'.$lang]) ?></h4>
            <?php if($item['description_'.$lang]): ?><p><?= e(mb_substr($item['description_'.$lang],0,90)) ?>...</p><?php endif; ?>
            <div class="menu-item-footer">
              <span class="menu-price"><?= formatPrice($item['price'], $item['currency']) ?></span>
              <?php if($item['is_vegetarian']): ?><span class="veg-badge">🌿 <?= $lang==='ar'?'نباتي':'Veg'?></span><?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--gray);"><?= $lang==='ar'?'لا توجد أصناف متاحة حالياً':'No items available at the moment'?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Restaurant CTA -->
<section style="background:var(--dark-2);border-top:1px solid var(--border);padding:60px 0;text-align:center;">
  <div class="container animate-on-scroll">
    <h3 style="font-family:var(--font-serif);font-size:30px;color:var(--cream);margin-bottom:12px;"><?= $lang==='ar'?'احجز طاولتك الآن':'Reserve Your Table Now'?></h3>
    <p style="color:var(--gray);margin-bottom:24px;"><?= $lang==='ar'?'استمتع بتجربة طعام لا تُنسى في أجواء من الفخامة والراحة':'Experience unforgettable dining in an atmosphere of luxury and comfort'?></p>
    <a href="contact.php?type=restaurant" class="btn btn-primary btn-lg"><i class="fas fa-utensils"></i> <?= $lang==='ar'?'احجز طاولة':'Book a Table'?></a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
