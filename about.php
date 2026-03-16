<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$pageTitle = t('nav_about') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/about-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= t('about_title') ?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><span><?= t('nav_about') ?></span></nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
      <div class="animate-on-scroll">
        <div class="section-title" style="text-align:start;margin-bottom:24px;">
          <div class="subtitle" style="justify-content:flex-start;"><?= $lang==='ar'?'قصتنا':'Our Story'?></div>
          <h2><?= t('about_title') ?></h2>
        </div>
        <p style="color:var(--gray);font-size:16px;line-height:1.9;margin-bottom:20px;"><?= t('about_desc') ?></p>
        <p style="color:var(--gray);font-size:16px;line-height:1.9;margin-bottom:28px;"><?= $lang==='ar' ? 'يتميز الفندق بموقعه الاستراتيجي في منطقة حدة على بُعد خطوات من الشارع التجاري، وقربه من المراكز التجارية والمطاعم وخطوط النقل العام، مما يجعله خياراً مثالياً للمسافرين سواء لأغراض الترفيه أو الأعمال.' : 'The hotel is strategically located in Haddah area, just steps from Iran Street, near shopping centers, restaurants and public transportation lines, making it an ideal choice for both leisure and business travelers.' ?></p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <a href="booking.php" class="btn btn-primary"><i class="fas fa-calendar-check"></i> <?= t('nav_book_now') ?></a>
          <a href="contact.php" class="btn btn-outline"><i class="fas fa-phone"></i> <?= t('nav_contact') ?></a>
        </div>
      </div>
      <div class="animate-on-scroll">
        <div style="position:relative;">
          <img src="<?= SITE_URL ?>/images/about-main.jpg" alt="<?= e(getHotelName()) ?>" style="width:100%;height:480px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);">
          <div style="position:absolute;bottom:-20px;<?= $lang==='ar'?'left':'-20px' ?>:-20px;background:var(--gold);color:var(--dark);padding:20px 24px;border-radius:var(--radius-sm);text-align:center;">
            <div style="font-size:36px;font-weight:900;font-family:var(--font-serif);">20+</div>
            <div style="font-size:13px;font-weight:600;"><?= $lang==='ar'?'سنوات من التميز':'Years of Excellence'?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Features -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:28px;margin-top:80px;">
      <?php
      $pillars = $lang==='ar'
        ? [['fas fa-map-marker-alt',t('location_title'),'يقع الفندق في منطقة حدة شارع إيران، على بُعد خطوات من المراكز التجارية والمطاعم وخطوط النقل العام.'],
           ['fas fa-concierge-bell',t('service_title'),'يقدم الفندق خدمات فندقية متكاملة شاملة تأجير الغرف والأجنحة وقاعات الاجتماعات وخدمات الأفراح والولائم الداخلية والخارجية.'],
           ['fas fa-shield-alt',t('security_title'),'أنظمة أمنية متطورة تعمل على مدار الساعة طوال أيام الأسبوع، تشمل ماسح الأشعة السينية للأمتعة وجهازي الكشف عن المعادن عند المدخل.']]
        : [[  'fas fa-map-marker-alt',t('location_title'),'Located in Haddah Area off Iran Street, walking distance from shopping areas, supermarkets and Public transportation.'],
           ['fas fa-concierge-bell',t('service_title'),'We offer a wide variety of 5-star hotel services besides room and suite rentals, including Meeting halls, training seminars, Wedding services, and in-house and outside catering.'],
           ['fas fa-shield-alt',t('security_title'),'We value our clients highly. 24/7 security with up-to-date equipment including luggage X-Ray scanners and walk-in metal detectors ensures safety and peace of mind.']];
      foreach($pillars as $p): ?>
      <div class="feature-card animate-on-scroll">
        <div class="feature-icon"><i class="<?= $p[0] ?>"></i></div>
        <h3><?= $p[1] ?></h3>
        <p><?= $p[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Stats -->
<div class="stats-strip">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-item animate-on-scroll"><div class="stat-num">5⭐</div><div class="stat-label"><?= $lang==='ar'?'تصنيف الفندق':'Hotel Rating'?></div></div>
      <div class="stat-item animate-on-scroll"><div class="stat-num">8</div><div class="stat-label"><?= $lang==='ar'?'أنواع الغرف':'Room Types'?></div></div>
      <div class="stat-item animate-on-scroll"><div class="stat-num">500+</div><div class="stat-label"><?= $lang==='ar'?'ضيف شهرياً':'Guests Monthly'?></div></div>
      <div class="stat-item animate-on-scroll"><div class="stat-num">3</div><div class="stat-label"><?= $lang==='ar'?'قاعات فعاليات':'Event Halls'?></div></div>
    </div>
  </div>
</div>
<style>.stats-strip{background:linear-gradient(135deg,var(--gold-dark),var(--gold));padding:50px 0;}.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center;}.stat-num{font-size:40px;font-weight:800;color:var(--dark);font-family:var(--font-serif);line-height:1;}.stat-label{font-size:14px;color:var(--dark);margin-top:8px;opacity:0.85;}@media(max-width:768px){.stats-grid{grid-template-columns:1fr 1fr;}}</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
