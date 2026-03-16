<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$galleryItems = $db->query("SELECT * FROM gallery WHERE is_active=1 ORDER BY sort_order LIMIT 60")->fetchAll();
$pageTitle = $lang==='ar'?'معرض الصور':'Gallery' . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>
<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/gallery-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= $lang==='ar'?'معرض الصور':'Photo Gallery'?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><span><?= $lang==='ar'?'المعرض':'Gallery'?></span></nav>
  </div>
</div>
<section class="section">
  <div class="container">
    <div class="gallery-grid">
      <?php foreach($galleryItems as $item): ?>
      <div class="gallery-item animate-on-scroll">
        <img src="<?= SITE_URL ?>/<?= e($item['image']) ?>" alt="<?= e($item['title_'.$lang]??'') ?>" loading="lazy">
        <div class="gallery-overlay"><span><?= e($item['title_'.$lang]??'') ?></span></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<!-- Lightbox -->
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:9999;align-items:center;justify-content:center;">
  <button id="lightboxClose" style="position:absolute;top:20px;right:24px;background:none;border:none;color:#fff;font-size:32px;cursor:pointer;">&times;</button>
  <button id="lightboxPrev" style="position:absolute;left:20px;background:rgba(255,255,255,0.1);border:none;color:#fff;font-size:24px;cursor:pointer;width:50px;height:50px;border-radius:50%;">&lsaquo;</button>
  <img id="lightboxImg" src="" style="max-width:90vw;max-height:85vh;object-fit:contain;border-radius:8px;">
  <button id="lightboxNext" style="position:absolute;right:20px;background:rgba(255,255,255,0.1);border:none;color:#fff;font-size:24px;cursor:pointer;width:50px;height:50px;border-radius:50%;">&rsaquo;</button>
</div>
<style>.gallery-item{cursor:pointer;}.gallery-item:hover .gallery-overlay{opacity:1;}</style>
<script>document.getElementById('lightbox').style.display='flex';document.getElementById('lightbox').style.display='none';</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
