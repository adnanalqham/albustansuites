<?php require_once __DIR__ . '/../functions.php'; $lang = getLang(); ?>
<footer class="site-footer">
  <div class="footer-top">
    <div class="container">
      <div class="footer-grid">
        <!-- Brand -->
        <div class="footer-col footer-brand">
          <a href="<?= SITE_URL ?>/index.php" class="footer-logo">
            <img src="<?= SITE_URL ?>/images/logo.png" alt="<?= e(getHotelName()) ?>" onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'footer-logo-text\'><?= e(getHotelName()) ?></span>'">
          </a>
          <p class="footer-desc"><?= $lang==='ar' ? 'فندق البستان للأجنحة الفاخرة - 5 نجوم في قلب حدة، صنعاء. حيث تلتقي الضيافة العربية الأصيلة بالرقي العالمي.' : 'Al Bustan Luxurious Suites – 5 Stars in the heart of Haddah, Sanaa. Where authentic Arabic hospitality meets world-class elegance.' ?></p>
          <div class="footer-social">
            <?php if($fb = getSetting('facebook_url')): ?><a href="<?= e($fb) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
            <?php if($ig = getSetting('instagram_url')): ?><a href="<?= e($ig) ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
            <?php if($tw = getSetting('twitter_url')): ?><a href="<?= e($tw) ?>" target="_blank"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
            <?php if($wa = getSetting('whatsapp_number')): ?><a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$wa) ?>" target="_blank"><i class="fab fa-whatsapp"></i></a><?php endif; ?>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-col">
          <h4><?= t('quick_links') ?></h4>
          <ul>
            <li><a href="<?= SITE_URL ?>/rooms.php"><?= t('nav_rooms') ?></a></li>
            <li><a href="<?= SITE_URL ?>/restaurant.php"><?= t('nav_restaurant') ?></a></li>
            <li><a href="<?= SITE_URL ?>/facilities.php"><?= t('nav_facilities') ?></a></li>
            <li><a href="<?= SITE_URL ?>/meetings.php"><?= t('nav_meetings') ?></a></li>
            <li><a href="<?= SITE_URL ?>/offers.php"><?= t('nav_offers') ?></a></li>
            <li><a href="<?= SITE_URL ?>/gallery.php"><?= t('nav_gallery') ?></a></li>
            <li><a href="<?= SITE_URL ?>/about.php"><?= t('nav_about') ?></a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="footer-col">
          <h4><?= t('contact_info') ?></h4>
          <ul class="footer-contact">
            <li><i class="fas fa-map-marker-alt"></i><span><?= getSetting('hotel_address_'.$lang, t('address')) ?></span></li>
            <li><i class="fas fa-phone"></i><a href="tel:<?= getSetting('hotel_phone') ?>"><?= getSetting('hotel_phone', '+967 1 433 200') ?></a></li>
            <li><i class="fas fa-envelope"></i><a href="mailto:<?= getSetting('hotel_email') ?>"><?= getSetting('hotel_email','info@albustansuites.net') ?></a></li>
            <li><i class="fab fa-whatsapp"></i><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('whatsapp_number')) ?>" target="_blank">WhatsApp</a></li>
          </ul>
        </div>

        <!-- Newsletter -->
        <div class="footer-col">
          <h4><?= t('newsletter_title') ?></h4>
          <p><?= t('newsletter_desc') ?></p>
          <form class="newsletter-form" action="<?= SITE_URL ?>/api/newsletter.php" method="POST">
            <div class="newsletter-input-wrap">
              <input type="email" name="email" placeholder="<?= t('email_placeholder') ?>" required>
              <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </div>
          </form>
          <div class="footer-policies">
            <a href="<?= SITE_URL ?>/privacy.php"><?= t('privacy_policy') ?></a>
            <span>|</span>
            <a href="<?= SITE_URL ?>/terms.php"><?= t('terms') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <p>&copy; <?= date('Y') ?> <?= e(getHotelName()) ?>. <?= t('copyright') ?>.</p>
    </div>
  </div>
</footer>

<!-- WhatsApp Float Button -->
<?php if($wa = getSetting('whatsapp_number')): ?>
<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $wa) ?>" class="whatsapp-float" target="_blank" aria-label="WhatsApp">
  <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop" aria-label="Back to top">
  <i class="fas fa-chevron-up"></i>
</button>

<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('<?= SITE_URL ?>/sw.js')
        .then(reg => console.log('Service Worker registered.', reg))
        .catch(err => console.log('Service Worker registration failed: ', err));
    });
  }
</script>
<?= $extraFooter ?? '' ?>
</body>
</html>
