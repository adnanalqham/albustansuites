<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$meetings = $db->query("SELECT * FROM meetings WHERE is_active=1 ORDER BY sort_order")->fetchAll();

$pageTitle = ($lang==='ar'?'قاعات الاجتماعات':'Meetings & Events') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/meetings-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= $lang==='ar'?'قاعات الاجتماعات والفعاليات':'Meeting & Event Halls'?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><span><?= $lang==='ar'?'الاجتماعات':'Meetings'?></span></nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="section-title animate-on-scroll">
      <div class="subtitle"><?= $lang==='ar'?'لكل مناسبة مكان':'A Space for Every Occasion'?></div>
      <h2><?= $lang==='ar'?'قاعاتنا للفعاليات':'Our Event Spaces'?></h2>
      <p><?= $lang==='ar'?'نقدم مجموعة من القاعات المجهزة بأحدث التقنيات لاستيعاب مختلف الفعاليات من مؤتمرات الأعمال إلى حفلات الأعراس الفاخرة':'We offer state-of-the-art spaces equipped with the latest technology for all occasions from business conferences to luxury weddings'?></p>
    </div>

    <?php foreach($meetings as $m): ?>
    <div class="animate-on-scroll" style="background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:36px;">
      <div style="display:grid;grid-template-columns:1fr 1.5fr;min-height:350px;">
        <div style="background-image:url('<?= SITE_URL ?>/<?= e($m['image']??'images/meeting-default.jpg') ?>');background-size:cover;background-position:center;min-height:280px;"></div>
        <div style="padding:40px;">
          <h3 style="font-family:var(--font-serif);font-size:28px;color:var(--cream);margin-bottom:8px;"><?= e($m['name_'.$lang]) ?></h3>
          <p style="color:var(--gray);font-size:15px;line-height:1.8;margin-bottom:24px;"><?= e($m['description_'.$lang]) ?></p>
          <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:24px;">
            <div><i class="fas fa-users" style="color:var(--gold);margin-right:6px;"></i><span style="font-size:14px;color:var(--cream);"><?= $m['capacity'] ?> <?= $lang==='ar'?'مقعد':'Seats'?></span></div>
            <?php if($m['area_sqm']): ?><div><i class="fas fa-ruler-combined" style="color:var(--gold);margin-right:6px;"></i><span style="font-size:14px;color:var(--cream);"><?= $m['area_sqm'] ?> <?= t('sqm') ?></span></div><?php endif; ?>
            <?php if($m['price_per_day']): ?><div><i class="fas fa-tag" style="color:var(--gold);margin-right:6px;"></i><span style="font-size:14px;color:var(--gold);font-weight:700;"><?= formatPrice($m['price_per_day'],'USD') ?>/<?= $lang==='ar'?'يوم':'day'?></span></div><?php endif; ?>
          </div>
          <?php $setups = decodeJson($m['setup_types_'.$lang]); if($setups): ?>
          <div style="margin-bottom:20px;">
            <p style="font-size:12px;color:var(--gray);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;"><?= $lang==='ar'?'أنواع الإعداد':'Setup Types'?></p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <?php foreach($setups as $s): ?><span style="background:rgba(201,168,76,0.1);border:1px solid var(--border);border-radius:20px;padding:4px 12px;font-size:12px;color:var(--cream);"><?= e($s) ?></span><?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <?php $amenities = decodeJson($m['amenities_'.$lang]); if($amenities): ?>
          <div style="margin-bottom:24px;">
            <p style="font-size:12px;color:var(--gray);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;"><?= t('amenities') ?></p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <?php foreach(array_slice($amenities,0,6) as $a): ?><div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--cream-dark);"><i class="fas fa-check" style="color:var(--gold);font-size:11px;"></i><?= e($a) ?></div><?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <a href="contact.php?type=meeting_inquiry&hall=<?= $m['id'] ?>" class="btn btn-primary"><i class="fas fa-envelope"></i> <?= $lang==='ar'?'استفسر عن الحجز':'Enquire About Booking'?></a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if(empty($meetings)): ?>
    <p style="text-align:center;color:var(--gray);padding:60px 0;"><?= $lang==='ar'?'سيتم إضافة معلومات القاعات قريباً':'Meeting hall information will be added soon'?></p>
    <?php endif; ?>
  </div>
</section>

<!-- CTA -->
<section style="background:var(--dark-2);border-top:1px solid var(--border);padding:60px 0;text-align:center;">
  <div class="container animate-on-scroll">
    <h3 style="font-family:var(--font-serif);font-size:32px;color:var(--cream);margin-bottom:12px;"><?= $lang==='ar'?'خطط لفعاليتك معنا':'Plan Your Event With Us'?></h3>
    <p style="color:var(--gray);max-width:540px;margin:0 auto 28px;"><?= $lang==='ar'?'سواء كانت مؤتمراً تجارياً أو حفل زفاف أو تجمعاً عائلياً، فريقنا جاهز لجعل مناسبتك استثنائية':'Whether a business conference, wedding, or family gathering, our team is ready to make your event exceptional'?></p>
    <a href="contact.php?type=meeting_inquiry" class="btn btn-primary btn-lg"><i class="fas fa-phone"></i> <?= $lang==='ar'?'تواصل معنا':'Contact Us Today'?></a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
