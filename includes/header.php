<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$lang = getLang();
$dir  = langDir();
$hotelName = getHotelName();
$isRTL = isRTL();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$siteLogo = getSetting('site_logo', 'images/logo.jpg');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? $hotelName) ?></title>
<meta name="description" content="<?= e($pageDesc ?? getSetting('meta_description_' . $lang)) ?>">
<link rel="icon" type="image/png" href="<?= SITE_URL ?>/<?= e($siteLogo) ?>">
<link rel="apple-touch-icon" href="<?= SITE_URL ?>/<?= e($siteLogo) ?>">
<link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
<meta name="theme-color" content="#0D1B2A">

<!-- Open Graph / Social Media -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?= SITE_URL ?>/<?= $currentPage ?>.php">
<meta property="og:title" content="<?= e($pageTitle ?? $hotelName) ?>">
<meta property="og:description" content="<?= e($pageDesc ?? getSetting('meta_description_' . $lang)) ?>">
<meta property="og:image" content="<?= SITE_URL ?>/<?= e($siteLogo) ?>">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?= SITE_URL ?>/<?= $currentPage ?>.php">
<meta property="twitter:title" content="<?= e($pageTitle ?? $hotelName) ?>">
<meta property="twitter:description" content="<?= e($pageDesc ?? getSetting('meta_description_' . $lang)) ?>">
<meta property="twitter:image" content="<?= SITE_URL ?>/<?= e($siteLogo) ?>">

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Main CSS -->
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=1.2">
<?php if($isRTL): ?>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/rtl.css">
<?php endif; ?>
<?= $extraHead ?? '' ?>
</head>
<body class="<?= $lang ?>-site <?= $isRTL ? 'rtl' : 'ltr' ?>">

<!-- Page Loader -->
<div class="page-loader" id="pageLoader">
  <div class="loader-inner">
    <img src="<?= SITE_URL ?>/<?= e($siteLogo) ?>" alt="<?= e($hotelName) ?>" class="loader-logo" onerror="this.style.display='none'">
    <div class="loader-dots"><span></span><span></span><span></span></div>
  </div>
</div>

<!-- Top Bar -->
<div class="top-bar">
  <div class="container">
    <div class="top-bar-left">
      <a href="tel:<?= getSetting('hotel_phone','+96714332000') ?>"><i class="fas fa-phone"></i> <?= getSetting('hotel_phone','+967 1 433 200') ?></a>
      <a href="mailto:<?= getSetting('hotel_email','info@albustansuites.net') ?>"><i class="fas fa-envelope"></i> <?= getSetting('hotel_email','info@albustansuites.net') ?></a>
    </div>
    <div class="top-bar-right">
      <div class="social-links">
        <?php if($fb = getSetting('facebook_url')): ?><a href="<?= e($fb) ?>" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
        <?php if($ig = getSetting('instagram_url')): ?><a href="<?= e($ig) ?>" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
        <?php if($tw = getSetting('twitter_url')): ?><a href="<?= e($tw) ?>" target="_blank" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
      </div>
      <a href="?lang=<?= altLang() ?>" class="lang-switcher">
        <i class="fas fa-globe"></i> <?= altLangLabel() ?>
      </a>
    </div>
  </div>
</div>

<!-- Main Navigation -->
<nav class="main-nav" id="mainNav">
  <div class="container">
    <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
      <img src="<?= SITE_URL ?>/<?= e($siteLogo) ?>" alt="<?= e($hotelName) ?>" onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'logo-text\'><?= e($hotelName) ?></span>'">
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="<?= t('nav_home') ?>">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-menu" id="navMenu">
      <li><a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage==='index'?'active':'' ?>"><?= t('nav_home') ?></a></li>
      <li class="has-dropdown">
        <a href="<?= SITE_URL ?>/rooms.php" class="<?= in_array($currentPage,['rooms','room-detail'])?'active':'' ?>"><?= t('nav_rooms') ?> <i class="fas fa-chevron-down"></i></a>
        <ul class="dropdown">
          <li><a href="<?= SITE_URL ?>/rooms.php?cat=suites"><?= $lang==='ar'?'الأجنحة':'Suites' ?></a></li>
          <li><a href="<?= SITE_URL ?>/rooms.php?cat=deluxe"><?= $lang==='ar'?'غرف ديلوكس':'Deluxe Rooms' ?></a></li>
          <li><a href="<?= SITE_URL ?>/rooms.php?cat=standard"><?= $lang==='ar'?'الغرف العادية':'Standard Rooms' ?></a></li>
        </ul>
      </li>
      <li class="has-dropdown">
        <a href="<?= SITE_URL ?>/restaurant.php" class="<?= in_array($currentPage,['restaurant','menu'])?'active':'' ?>"><?= t('nav_restaurant') ?> <i class="fas fa-chevron-down"></i></a>
        <ul class="dropdown">
          <li><a href="<?= SITE_URL ?>/restaurant.php"><?= t('nav_restaurant') ?></a></li>
          <li><a href="<?= SITE_URL ?>/menu.php"><?= t('nav_menu') ?></a></li>
        </ul>
      </li>
      <li><a href="<?= SITE_URL ?>/facilities.php" class="<?= $currentPage==='facilities'?'active':'' ?>"><?= t('nav_facilities') ?></a></li>
      <li><a href="<?= SITE_URL ?>/meetings.php" class="<?= $currentPage==='meetings'?'active':'' ?>"><?= t('nav_meetings') ?></a></li>
      <li><a href="<?= SITE_URL ?>/offers.php" class="<?= $currentPage==='offers'?'active':'' ?>"><?= t('nav_offers') ?></a></li>
      <li><a href="<?= SITE_URL ?>/gallery.php" class="<?= $currentPage==='gallery'?'active':'' ?>"><?= t('nav_gallery') ?></a></li>
      <li><a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage==='about'?'active':'' ?>"><?= t('nav_about') ?></a></li>
      <li><a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage==='contact'?'active':'' ?>"><?= t('nav_contact') ?></a></li>
      <?php if(isUserLoggedIn()): ?>
      <li class="has-dropdown">
        <a href="<?= SITE_URL ?>/profile.php"><i class="fas fa-user-circle"></i> <?= e(getUser()['name']) ?> <i class="fas fa-chevron-down"></i></a>
        <ul class="dropdown">
          <li><a href="<?= SITE_URL ?>/profile.php"><?= t('nav_my_bookings') ?></a></li>
          <li><a href="<?= SITE_URL ?>/logout.php"><?= t('nav_logout') ?></a></li>
        </ul>
      </li>
      <?php else: ?>
      <li><a href="<?= SITE_URL ?>/login.php"><i class="fas fa-user"></i> <?= t('nav_login') ?></a></li>
      <?php endif; ?>
      <li><a href="<?= SITE_URL ?>/booking.php" class="btn-book-nav"><?= t('nav_book_now') ?></a></li>
    </ul>
  </div>
</nav>
<!-- Flash Message -->
<?php if($flash = getFlash()): ?>
<div class="flash-container"><div class="flash flash-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div></div>
<?php endif; ?>
