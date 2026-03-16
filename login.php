<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!verifyCsrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid token'; }
    else {
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $pass  = $_POST['password'] ?? '';
        if($email && $pass) {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if($user && password_verify($pass, $user['password'])) {
                $_SESSION[USER_SESSION_NAME] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'phone'=>$user['phone']];
                $redirect = urldecode($_GET['redirect'] ?? 'index.php');
                if(!str_starts_with($redirect,'/') && !str_starts_with($redirect,'http')) $redirect='index.php';
                header('Location: ' . $redirect); exit;
            }
        }
        $errors[] = t('login_error');
    }
}

$pageTitle = t('login_title') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>
<div class="auth-section">
  <div class="auth-card">
    <div class="auth-logo"><img src="<?= SITE_URL ?>/images/logo.png" alt="<?= e(getHotelName()) ?>" onerror="this.style.display='none'"></div>
    <h1><?= t('login_title') ?></h1>
    <p class="subtitle"><?= $lang==='ar'?'مرحباً بعودتك! سجّل دخولك لمتابعة حجوزاتك':'Welcome back! Sign in to manage your bookings'?></p>
    <?php if($errors): ?><div class="flash flash-error"><?= e($errors[0]) ?></div><?php endif; ?>
    <?php echo renderFlash(); ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="form-group"><label><?= t('email') ?></label><input type="email" name="email" class="form-control" required autofocus></div>
      <div class="form-group"><label><?= t('password') ?></label><input type="password" name="password" class="form-control" required></div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;"><?= t('sign_in') ?></button>
    </form>
    <hr class="auth-divider">
    <p class="auth-footer"><?= t('no_account') ?><a href="register.php"><?= t('sign_up') ?></a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
