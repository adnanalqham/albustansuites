<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

// Fetch featured rooms
$featuredRooms = $db->query("SELECT * FROM rooms WHERE is_featured=1 AND is_available=1 ORDER BY sort_order LIMIT 3")->fetchAll();

// Fetch active offers
$offers = $db->query("SELECT * FROM offers WHERE is_active=1 AND is_featured=1 ORDER BY id DESC LIMIT 4")->fetchAll();

// Fetch testimonials
$testimonials = $db->query("SELECT * FROM testimonials WHERE is_active=1 ORDER BY id LIMIT 4")->fetchAll();

$pageTitle = getHotelName() . ' - ' . ($lang==='ar' ? 'فندق 5 نجوم في صنعاء' : '5 Star Hotel in Sanaa');
$pageDesc  = getSetting('meta_description_' . $lang);

$heroSlides = [
  ['bg'=>'images/hero-1.jpg','title_ar'=>'جناح فاخر بإطلالة بانورامية','title_en'=>'Panoramic Luxury Suite'],
  ['bg'=>'images/hero-2.jpg','title_ar'=>'مطعم البستان الفاخر','title_en'=>'Al Bustan Fine Dining'],
  ['bg'=>'images/hero-3.jpg','title_ar'=>'قاعات اجتماعات عالمية المستوى','title_en'=>'World-Class Meeting Halls'],
];

require __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="hero" id="home">
  <div class="hero-slider">
    <?php foreach($heroSlides as $i => $slide): ?>
    <div class="hero-slide <?= $i===0?'active':'' ?>" style="background-image:url('<?= SITE_URL ?>/<?= $slide['bg'] ?>')"></div>
    <?php endforeach; ?>
    <div class="hero-overlay"></div>
  </div>

  <div class="container hero-content">
    <div class="hero-badge animate-on-scroll"><i class="fas fa-star"></i><?= $lang==='ar'?'فندق 5 نجوم - صنعاء':'5-Star Hotel - Sanaa, Yemen'?></div>
    <h1><?= $lang==='ar'?'البستان للأجنحة <span>الفاخرة</span>':'Al Bustan <span>Luxurious</span> Suites'?></h1>
    <p><?= t('hero_desc') ?></p>
    <div class="hero-actions">
      <a href="booking.php" class="btn btn-primary btn-lg"><i class="fas fa-calendar-check"></i> <?= t('nav_book_now') ?></a>
      <a href="rooms.php" class="btn btn-outline btn-lg"><i class="fas fa-bed"></i> <?= t('nav_rooms') ?></a>
    </div>
  </div>

  <!-- Hero Indicators -->
  <div class="hero-indicators">
    <?php foreach($heroSlides as $i => $s): ?>
    <button class="hero-indicator <?= $i===0?'active':'' ?>" aria-label="Slide <?= $i+1 ?>"></button>
    <?php endforeach; ?>
  </div>

  <!-- Booking Widget -->
  <div class="hero-booking">
    <form class="booking-widget" action="booking.php" method="GET">
      <div class="booking-field">
        <label><?= t('check_in') ?></label>
        <input type="date" name="check_in" id="heroCheckin" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="booking-field">
        <label><?= t('check_out') ?></label>
        <input type="date" name="check_out" id="heroCheckout" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
      </div>
      <div class="booking-field">
        <label><?= t('guests') ?></label>
        <select name="adults">
          <?php for($i=1;$i<=6;$i++): ?><option value="<?=$i?>"><?=$i?> <?=$lang==='ar'?'بالغ':'Adult'.($i>1?'s':'')?></option><?php endfor; ?>
        </select>
      </div>
      <div class="booking-field">
        <label><?= t('room_type') ?></label>
        <select name="category">
          <option value=""><?= t('all_rooms') ?></option>
          <option value="suites"><?= $lang==='ar'?'الأجنحة':'Suites' ?></option>
          <option value="deluxe"><?= $lang==='ar'?'غرف ديلوكس':'Deluxe Rooms' ?></option>
          <option value="standard"><?= $lang==='ar'?'الغرف العادية':'Standard Rooms' ?></option>
        </select>
      </div>
      <div class="booking-field">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> <?= t('check_availability') ?></button>
      </div>
    </form>
  </div>
</section>

<!-- ===== WHY CHOOSE US ===== -->
<section class="section" id="features">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'مميزاتنا':'Our Features'?></div>
      <h2><?= t('why_choose_us') ?></h2>
    </div>
    <div class="features-grid">
      <?php
      $features = $lang==='ar'
        ? [
            ['fas fa-map-marker-alt','الموقع المثالي','فندق البستان يقع في قلب منطقة حدة الراقية بصنعاء، على بُعد خطوات من المراكز التجارية والمطاعم.'],
            ['fas fa-concierge-bell','خدمة 5 نجوم','فريق متخصص ومدرب على أعلى مستوى يسعى دائماً لتجاوز توقعاتكم وتقديم أفضل تجربة إقامة.'],
            ['fas fa-shield-alt','أمن لا يهدأ','أنظمة أمنية متطورة تعمل على مدار الساعة تشمل كاشف المعادن وجهاز الأشعة السينية للأمتعة.'],
            ['fas fa-utensils','مطعم عالمي','استمتع بأشهى الأطباق العربية والعالمية التي يعدّها طهاة محترفون في أجواء فاخرة ومميزة.'],
            ['fas fa-handshake','قاعات مؤتمرات','قاعات اجتماعات وفعاليات مجهزة بأحدث التقنيات تلبي جميع احتياجاتكم التجارية والاحتفالية.'],
            ['fas fa-wifi','إنترنت عالي السرعة','اتصال واي فاي مجاني وعالي السرعة في جميع أرجاء الفندق لضمان تواصل دائم ومتدفق.'],
          ]
        : [
            ['fas fa-map-marker-alt','Prime Location','Located in the heart of Haddah, Sanaa\'s most upscale area, steps from shopping centers and restaurants.'],
            ['fas fa-concierge-bell','5-Star Service','Our dedicated, highly-trained team always strives to exceed your expectations and deliver the finest stay.'],
            ['fas fa-shield-alt','24/7 Security','State-of-the-art security systems operating around the clock including metal detectors and X-ray luggage scanners.'],
            ['fas fa-utensils','World-Class Dining','Enjoy the finest Arabic and international dishes prepared by professional chefs in a luxurious setting.'],
            ['fas fa-handshake','Meeting Facilities','Event halls equipped with the latest technology for all your business and celebration needs.'],
            ['fas fa-wifi','High-Speed WiFi','Free, high-speed WiFi throughout the hotel to ensure you stay always connected.'],
          ];
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

<!-- ===== STATS STRIP ===== -->
<div class="stats-strip">
  <div class="container">
    <div class="stats-grid">
      <?php
      $stats = [
        ['20+', $lang==='ar'?'سنوات من الخبرة':'Years of Experience'],
        ['500+', $lang==='ar'?'ضيف سعيد شهرياً':'Happy Guests Monthly'],
        ['8', $lang==='ar'?'أنواع من الغرف':'Room Types'],
        ['5⭐', $lang==='ar'?'تصنيف الفندق':'Hotel Rating'],
      ];
      foreach($stats as $s): ?>
      <div class="stat-item animate-on-scroll">
        <div class="stat-num counter-num"><?= $s[0] ?></div>
        <div class="stat-label"><?= $s[1] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ===== FEATURED ROOMS ===== -->
<section class="section" id="rooms">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'أجواء من الفخامة':'Luxury Awaits'?></div>
      <h2><?= t('featured_rooms') ?></h2>
      <p><?= $lang==='ar'?'اختر من بين مجموعة متميزة من الغرف والأجنحة الفاخرة المصممة خصيصاً لراحتك ورفاهيتك':'Choose from a distinguished collection of rooms and luxury suites designed exclusively for your comfort'?></p>
    </div>
    <div class="rooms-grid">
      <?php foreach($featuredRooms as $room): ?>
      <div class="room-card animate-on-scroll">
        <div class="room-card-img">
          <img src="<?= SITE_URL ?>/<?= e($room['main_image'] ?? 'images/room-default.jpg') ?>" alt="<?= e($room['name_'.$lang]) ?>" loading="lazy">
          <span class="room-card-badge"><?= $lang==='ar'?'مميز':'Featured'?></span>
        </div>
        <div class="room-card-body">
          <h3><?= e($room['name_'.$lang]) ?></h3>
          <div class="room-meta">
            <?php if($room['size_sqm']): ?><span><i class="fas fa-ruler-combined"></i><?= $room['size_sqm'].' '.t('sqm')?></span><?php endif; ?>
            <span><i class="fas fa-user"></i><?= $room['capacity_adults'].' '.t('adults')?></span>
            <?php if($room['view_type_'.$lang]): ?><span><i class="fas fa-eye"></i><?= e($room['view_type_'.$lang])?></span><?php endif; ?>
          </div>
          <p class="room-desc"><?= e(mb_substr($room['short_desc_'.$lang]??'',0,120)) ?>...</p>
          <div class="room-card-footer">
            <div class="room-price">
              <span class="from"><?= $lang==='ar'?'يبدأ من':'From'?></span>
              <span class="amount"><?= formatPrice($room['price_per_night'], $room['currency']) ?></span>
              <span class="per"><?= t('per_night') ?></span>
            </div>
            <a href="room-detail.php?slug=<?= e($room['slug']) ?>" class="btn btn-outline btn-sm"><?= t('view_details') ?></a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:48px;">
      <a href="rooms.php" class="btn btn-primary btn-lg"><i class="fas fa-bed"></i> <?= t('explore_rooms') ?></a>
    </div>
  </div>
</section>

<!-- ===== RESTAURANT ===== -->
<section class="section restaurant-section" id="restaurant">
  <div class="container">
    <div class="restaurant-inner">
      <div class="restaurant-img animate-on-scroll">
        <img src="<?= SITE_URL ?>/images/restaurant.jpg" alt="<?= $lang==='ar'?'مطعم البستان':'Al Bustan Restaurant'?>">
      </div>
      <div class="restaurant-content">
        <div class="section-title animate-on-scroll">
          <div class="subtitle"><i class="fas fa-utensils"></i> <?= $lang==='ar'?'تجربة طعام فاخرة':'Fine Dining Experience'?></div>
          <h2><?= t('our_restaurant') ?></h2>
          <p><?= t('restaurant_desc') ?></p>
        </div>
        <ul class="restaurant-hours animate-on-scroll">
          <li><i class="fas fa-coffee"></i> <?= $lang==='ar'?'الإفطار: 6:30 صباحاً - 10:30 صباحاً':'Breakfast: 6:30 AM - 10:30 AM'?></li>
          <li><i class="fas fa-sun"></i><?= $lang==='ar'?'الغداء: 12:30 ظهراً - 3:00 مساءً':'Lunch: 12:30 PM - 3:00 PM'?></li>
          <li><i class="fas fa-moon"></i><?= $lang==='ar'?'العشاء: 7:00 مساءً - 11:00 مساءً':'Dinner: 7:00 PM - 11:00 PM'?></li>
          <li><i class="fas fa-clock"></i><?= $lang==='ar'?'غرفة الخدمة: 24 ساعة':'Room Service: 24 Hours'?></li>
        </ul>
        <div class="hero-actions animate-on-scroll">
          <a href="menu.php" class="btn btn-primary"><i class="fas fa-book-open"></i> <?= t('explore_menu') ?></a>
          <a href="restaurant.php" class="btn btn-outline"><?= $lang==='ar'?'تعرف على مطعمنا':'About Our Restaurant'?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== SPECIAL OFFERS ===== -->
<?php if($offers): ?>
<section class="section" id="offers">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'عروض حصرية':'Exclusive Deals'?></div>
      <h2><?= t('special_offers') ?></h2>
    </div>
    <div class="offers-grid">
      <?php foreach($offers as $offer): ?>
      <div class="offer-card animate-on-scroll">
        <div class="offer-img">
          <img src="<?= SITE_URL ?>/<?= e($offer['image'] ?? 'images/offer-default.jpg') ?>" alt="<?= e($offer['title_'.$lang]) ?>">
          <div class="offer-discount">
            <?= $offer['discount_type']==='percentage' ? round($offer['discount_value']).'%' : formatPrice($offer['discount_value']) ?><br><small><?= $lang==='ar'?'خصم':'OFF'?></small>
          </div>
        </div>
        <div class="offer-body">
          <h3><?= e($offer['title_'.$lang]) ?></h3>
          <p><?= e(mb_substr($offer['description_'.$lang]??'',0,100)).'...' ?></p>
          <?php if($offer['promo_code']): ?><span class="offer-code"><i class="fas fa-tag"></i> <?= e($offer['promo_code']) ?></span><?php endif; ?>
          <br><br>
          <a href="booking.php?offer=<?= $offer['id'] ?>" class="btn btn-primary btn-sm"><?= t('nav_book_now') ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="offers.php" class="btn btn-outline btn-lg"><?= t('view_all_offers') ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== FACILITIES ===== -->
<section class="section" style="background:var(--dark-2);" id="facilities">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'لمسة فخامة في كل مكان':'Luxury at Every Corner'?></div>
      <h2><?= t('our_facilities') ?></h2>
    </div>
    <div class="facilities-grid">
      <?php
      $facilities = [
        ['fas fa-swimming-pool', t('facility_pool')],
        ['fas fa-dumbbell', t('facility_gym')],
        ['fas fa-spa', t('facility_spa')],
        ['fas fa-briefcase', t('facility_business')],
        ['fas fa-parking', t('facility_parking')],
        ['fas fa-shield-alt', t('facility_security')],
        ['fas fa-wifi', t('facility_wifi')],
        ['fas fa-concierge-bell', t('facility_room_service')],
        ['fas fa-tshirt', t('facility_laundry')],
        ['fas fa-bell-concierge', t('facility_concierge')],
      ];
      foreach($facilities as $f): ?>
      <div class="facility-item animate-on-scroll">
        <i class="<?= $f[0] ?>"></i>
        <span><?= $f[1] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:48px;">
      <a href="facilities.php" class="btn btn-outline btn-lg"><?= $lang==='ar'?'اكتشف جميع المرافق':'Discover All Facilities'?></a>
    </div>
  </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="section" id="reviews">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'رأي ضيوفنا':'Guest Stories'?></div>
      <h2><?= t('guest_reviews') ?></h2>
    </div>
    <div class="testimonials-grid">
      <?php foreach($testimonials as $t_row): ?>
      <div class="testimonial-card animate-on-scroll">
        <div class="stars">
          <?php for($s=0;$s<5;$s++): ?><i class="fas fa-star<?= $s>=$t_row['rating']?'-o':''?>"></i><?php endfor; ?>
        </div>
        <p class="testimonial-text">"<?= e($t_row['review_'.$lang] ?? $t_row['review_en']) ?>"</p>
        <div class="testimonial-footer">
          <div class="testimonial-avatar"><i class="fas fa-user"></i></div>
          <div class="testimonial-info">
            <strong><?= e($t_row['guest_name']) ?></strong>
            <span><?= e($t_row['country_'.$lang] ?? $t_row['country_en']) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== CTA ===== -->
<section style="background:linear-gradient(135deg,var(--dark-3),var(--dark-2));border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:80px 0;text-align:center;">
  <div class="container animate-on-scroll">
    <div class="subtitle" style="display:inline-flex;align-items:center;gap:10px;color:var(--gold);font-size:13px;letter-spacing:3px;text-transform:uppercase;margin-bottom:16px;justify-content:center;">
      <span style="display:block;width:40px;height:1px;background:var(--gold)"></span>
      <?= $lang==='ar'?'حجز فوري':'Instant Booking'?>
      <span style="display:block;width:40px;height:1px;background:var(--gold)"></span>
    </div>
    <h2 style="font-family:var(--font-serif);font-size:42px;color:var(--cream);margin-bottom:16px;"><?= $lang==='ar'?'احجز إقامتك المثالية اليوم':'Book Your Perfect Stay Today'?></h2>
    <p style="color:var(--gray);max-width:560px;margin:0 auto 36px;font-size:16px;"><?= $lang==='ar'?'احصل على أفضل الأسعار عند الحجز المباشر معنا - ضمان أفضل سعر':'Get the best rates when booking directly with us - Best Rate Guarantee'?></p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
      <a href="booking.php" class="btn btn-primary btn-lg"><i class="fas fa-calendar-check"></i> <?= t('nav_book_now') ?></a>
      <a href="contact.php" class="btn btn-outline btn-lg"><i class="fas fa-phone"></i> <?= t('nav_contact') ?></a>
    </div>
  </div>
</section>

<style>
/* Homepage-specific extras */
.stats-strip{background:linear-gradient(135deg,var(--gold-dark),var(--gold));padding:50px 0;}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center;}
.stat-num{font-size:40px;font-weight:800;color:var(--dark);font-family:var(--font-serif);line-height:1;}
.stat-label{font-size:14px;color:var(--dark);margin-top:8px;opacity:0.85;font-weight:500;}
.hero-indicators{position:absolute;bottom:50px;left:50%;transform:translateX(-50%);display:flex;gap:8px;z-index:2;}
.hero-indicator{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.4);border:none;cursor:pointer;transition:all 0.3s;}
.hero-indicator.active{width:28px;border-radius:4px;background:var(--gold);}
@media(max-width:768px){.stats-grid{grid-template-columns:1fr 1fr;gap:20px;}.stat-num{font-size:28px;}}
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
