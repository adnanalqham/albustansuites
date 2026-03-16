<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();

$pageTitle = t('nav_facilities') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/rooms-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= t('nav_facilities') ?></h1>
    <nav class="breadcrumb">
      <a href="index.php"><?= t('nav_home') ?></a>
      <span>›</span>
      <span><?= t('nav_facilities') ?></span>
    </nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'رفاهية لا تُضاهى':'Unmatched Luxury'?></div>
      <h2><?= t('our_facilities') ?></h2>
      <p style="max-width:700px;margin:0 auto;color:var(--gray);font-size:16px;">
        <?= $lang==='ar'?'نحن في البستان للأجنحة الفاخرة نلتزم بتقديم مرافق عالمية المستوى لضمان إقامة لا تُنسى تلبي جميع احتياجات ضيوفنا الكرام.':'At Al Bustan Luxurious Suites, we are committed to providing world-class facilities to ensure an unforgettable stay that caters to all the needs of our esteemed guests.'?>
      </p>
    </div>

    <!-- Detailed Facilities List -->
    <div class="facilities-grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; margin-top: 50px;">
      <?php
      $extended_facilities = $lang === 'ar' ? [
        ['fas fa-swimming-pool', 'مسبح داخلي دافئ', 'مسبح مصمم بأعلى المعايير، مثالي للاسترخاء في أي وقت من اليوم.'],
        ['fas fa-dumbbell', 'نادي صحي ورياضي', 'مجهز بأحدث أجهزة اللياقة البدنية للحفاظ على نشاطك.'],
        ['fas fa-spa', 'سبا ومركز مساج', 'جلسات تدليك واسترخاء تمنحك تجديداً كاملاً للطاقة.'],
        ['fas fa-utensils', 'مطعم وكافيه البستان', 'أشهى المأكولات الشرقية والعالمية يومياً.'],
        ['fas fa-handshake', 'قاعات مؤتمرات واجتماعات', 'مجهزة بأحدث التقنيات الصوتية والمرئية لنجاح أعمالك.'],
        ['fas fa-car', 'خدمة صف السيارات (فاليه)', 'خدمة مريحة لتسهيل وصولك بأناقة وسلاسة.'],
        ['fas fa-shield-alt', 'أمن وحماية 24/7', 'طاقم أمني محترف، وكاميرات مراقبة لتشعر بالأمان التام.'],
        ['fas fa-wifi', 'واي فاي عالي السرعة مجاني', 'إنترنت سريع ومجاني داخل جميع الغرف والمرافق.'],
        ['fas fa-concierge-bell', 'خدمة الغرف 24 ساعة', 'تلبية كافة احتياجاتك وتوصيل الطلبات لغرفتك في أي وقت.'],
        ['fas fa-tshirt', 'خدمة غسيل وكي الملابس', 'رعاية فائقة لملابسك لتبدو بأفضل حلة دائماً.'],
      ] : [
        ['fas fa-swimming-pool', 'Heated Indoor Pool', 'A beautifully designed pool, perfect for relaxation at any time of the day.'],
        ['fas fa-dumbbell', 'Fitness Center', 'Equipped with the latest gym and fitness equipment to keep you active.'],
        ['fas fa-spa', 'Spa & Massage Center', 'Relaxing massage sessions that give you a complete energy renewal.'],
        ['fas fa-utensils', 'Al Bustan Restaurant', 'Delicious Oriental and international cuisine daily.'],
        ['fas fa-handshake', 'Conference Rooms', 'Equipped with the latest audio-visual technologies for your successful business events.'],
        ['fas fa-car', 'Valet Parking', 'Convenient service to make your arrival stylish and smooth.'],
        ['fas fa-shield-alt', '24/7 Security', 'Professional security staff and surveillance cameras for your total safety.'],
        ['fas fa-wifi', 'Free High-Speed Wi-Fi', 'Fast and free internet access in all rooms and facilities.'],
        ['fas fa-concierge-bell', '24-Hour Room Service', 'Meeting all your needs and delivering orders to your room anytime.'],
        ['fas fa-tshirt', 'Laundry & Dry Cleaning', 'Superior care for your clothes so you always look your best.'],
      ];
      
      foreach($extended_facilities as $f): ?>
      <div class="feature-card animate-on-scroll" style="text-align:center; padding: 40px 20px;">
        <div class="feature-icon" style="font-size:40px; color:var(--gold); margin-bottom:20px; display:inline-block;"><i class="<?= $f[0] ?>"></i></div>
        <h3 style="font-size:20px; margin-bottom:15px; color:var(--cream);"><?= $f[1] ?></h3>
        <p style="color:var(--gray); font-size:14px; line-height:1.6;"><?= $f[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Additional feature section -->
<section style="background:linear-gradient(135deg,var(--dark-3),var(--dark-2)); padding:80px 0; border-top:1px solid var(--border);">
  <div class="container animate-on-scroll">
    <div style="display:flex; flex-wrap:wrap; gap:40px; align-items:center;">
      <div style="flex:1; min-width:300px;">
        <h2 style="font-family:var(--font-serif);font-size:36px;color:var(--cream);margin-bottom:20px;">
            <?= $lang==='ar'?'الراحة تنبع من التفاصيل':'True Comfort Lies in the Details'?>
        </h2>
        <p style="color:var(--gray); font-size:16px; line-height:1.8; margin-bottom:30px;">
            <?= $lang==='ar'?'لقد صممنا فندق البستان ليكون ملاذكم الآمن والمريح. لا نكتفي بتقديم غرفة فندقية، بل نقدم تجربة متكاملة تبدأ من لحظة دخولكم وتستمر في كل تفاصيل الإقامة بفضل مرافقنا الشاملة وفريق العمل الاستثنائي.':'We designed Al Bustan Hotel to be your safe and comfortable haven. We don\'t just offer a hotel room; we provide a complete experience that begins the moment you enter and continues in every detail of your stay, thanks to our comprehensive facilities and exceptional team.'?>
        </p>
        <a href="booking.php" class="btn btn-primary btn-lg"><i class="fas fa-calendar-check"></i> <?= t('nav_book_now') ?></a>
      </div>
      <div style="flex:1; min-width:300px;">
        <img src="<?= SITE_URL ?>/images/hero-3.jpg" alt="<?= t('nav_facilities') ?>" style="width:100%; border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,0.4);">
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
