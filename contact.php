<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$success = false;
$errors  = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!verifyCsrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid token.'; }
    else {
        $name    = sanitize($_POST['name']    ?? '');
        $email   = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone   = sanitize($_POST['phone']   ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        $type    = sanitize($_POST['type']    ?? 'general');
        if(!$name)    $errors[] = t('required_field') . ': ' . t('contact_name');
        if(!$email)   $errors[] = t('required_field') . ': ' . t('contact_email');
        if(!$message) $errors[] = t('required_field') . ': ' . t('contact_message');

        if(empty($errors)) {
            $stmt = $db->prepare("INSERT INTO messages (name,email,phone,subject,message,type) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, $type]);
            $success = true;
        }
    }
}

$pageTitle = t('contact_title') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/contact-hero.jpg');"></div>
  <div class="page-hero-content">
    <h1><?= t('contact_title') ?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><span><?= t('nav_contact') ?></span></nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <!-- Contact Info -->
      <div>
        <div class="section-title animate-on-scroll" style="text-align:<?= $lang==='ar'?'right':'left'?>;">
          <div class="subtitle" style="justify-content:<?= $lang==='ar'?'flex-end':'flex-start'?>;"><?= $lang==='ar'?'تواصل معنا':'Get in Touch'?></div>
          <h2><?= t('contact_us') ?></h2>
        </div>

        <div class="contact-info-item animate-on-scroll">
          <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
          <div><h4><?= t('our_location') ?></h4><p><?= getSetting('hotel_address_'.$lang) ?></p></div>
        </div>
        <div class="contact-info-item animate-on-scroll">
          <div class="icon"><i class="fas fa-phone"></i></div>
          <div><h4><?= t('call_us') ?></h4><a href="tel:<?= getSetting('hotel_phone') ?>"><?= getSetting('hotel_phone','+967 1 433 200') ?></a></div>
        </div>
        <div class="contact-info-item animate-on-scroll">
          <div class="icon"><i class="fas fa-envelope"></i></div>
          <div><h4><?= t('email_us') ?></h4><a href="mailto:<?= getSetting('hotel_email') ?>"><?= getSetting('hotel_email','info@albustansuites.net') ?></a></div>
        </div>
        <div class="contact-info-item animate-on-scroll">
          <div class="icon"><i class="fab fa-whatsapp"></i></div>
          <div><h4>WhatsApp</h4><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('whatsapp_number')) ?>" target="_blank"><?= getSetting('whatsapp_number') ?></a></div>
        </div>
        <div class="contact-info-item animate-on-scroll">
          <div class="icon"><i class="fas fa-clock"></i></div>
          <div><h4><?= t('opening_hours') ?></h4><p><?= t('open_24h') ?></p></div>
        </div>
        <!-- Map -->
        <div class="map-container animate-on-scroll">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3784.4!2d<?= getSetting('hotel_lng','44.1910') ?>!3d<?= getSetting('hotel_lat','15.3694') ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z!5e0!3m2!1sar!2sye!4v1" allowfullscreen="" loading="lazy"></iframe>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="animate-on-scroll">
        <?php if($success): ?>
        <div style="text-align:center;padding:60px 0;">
          <div style="width:80px;height:80px;background:rgba(0,200,100,0.1);border:2px solid #00c864;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;color:#00c864;"><i class="fas fa-check"></i></div>
          <h3 style="font-family:var(--font-serif);color:var(--cream);margin-bottom:10px;"><?= $lang==='ar'?'تم إرسال رسالتك!':'Message Sent!'?></h3>
          <p style="color:var(--gray);"><?= t('message_sent') ?></p>
        </div>
        <?php else: ?>
        <?php if($errors): ?><div class="flash flash-error" style="margin-bottom:20px;"><?= implode('<br>',array_map('e',$errors))?></div><?php endif; ?>
        <form method="POST" id="contactForm" style="background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius);padding:36px;">
          <?= csrfField() ?>
          <h3 style="font-family:var(--font-serif);font-size:24px;color:var(--cream);margin-bottom:24px;"><?= $lang==='ar'?'أرسل رسالتك':'Send Your Message'?></h3>
          <input type="hidden" name="type" value="<?= e($_GET['type']??'general') ?>">
          <div class="form-row">
            <div class="form-group">
              <label><?= t('contact_name') ?> *</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
              <label><?= t('contact_email') ?> *</label>
              <input type="email" name="email" class="form-control" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label><?= t('contact_phone') ?></label>
              <input type="tel" name="phone" class="form-control">
            </div>
            <div class="form-group">
              <label><?= t('contact_subject') ?></label>
              <input type="text" name="subject" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label><?= t('contact_message') ?> *</label>
            <textarea name="message" class="form-control" rows="5" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
            <i class="fas fa-paper-plane"></i> <?= t('send_message') ?>
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
