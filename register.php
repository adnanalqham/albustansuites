<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();

$errors  = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!verifyCsrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid token'; }
    else {
        $name  = sanitize($_POST['name']  ?? '');
        $email = filter_var($_POST['email']?? '', FILTER_VALIDATE_EMAIL);
        $phone = sanitize($_POST['phone'] ?? '');
        $pass  = $_POST['password']        ?? '';
        $pass2 = $_POST['confirm_password']?? '';
        if(!$name)  $errors[] = $lang==='ar'?'الاسم مطلوب':'Name is required';
        if(!$email) $errors[] = $lang==='ar'?'بريد إلكتروني صحيح مطلوب':'Valid email is required';
        if(strlen($pass)<6) $errors[] = $lang==='ar'?'كلمة المرور يجب أن تكون 6 أحرف على الأقل':'Password must be at least 6 characters';
        if($pass !== $pass2) $errors[] = $lang==='ar'?'كلمتا المرور غير متطابقتين':'Passwords do not match';

        if(empty($errors)) {
            $db = getDB();
            $exists = $db->prepare("SELECT id FROM users WHERE email=?");
            $exists->execute([$email]);
            if($exists->fetch()) {
                $errors[] = $lang==='ar'?'البريد الإلكتروني مسجل مسبقاً':'Email already registered';
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $db->prepare("INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)")->execute([$name,$email,$phone,$hash]);
                setFlash('success', t('register_success'));
                header('Location: login.php'); exit;
            }
        }
    }
}

$pageTitle = t('register_title') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>
<div class="auth-section">
  <div class="auth-card">
    <div class="auth-logo"><img src="<?= SITE_URL ?>/images/logo.png" alt="<?= e(getHotelName()) ?>" onerror="this.style.display='none'"></div>
    <h1><?= t('register_title') ?></h1>
    <p class="subtitle"><?= $lang==='ar'?'أنشئ حسابك للوصول إلى حجوزاتك والعروض الحصرية':'Create your account to access your bookings and exclusive offers'?></p>
    <?php if($errors): ?><div class="flash flash-error"><?= implode('<br>',array_map('e',$errors))?></div><?php endif; ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="form-group"><label><?= t('full_name') ?> *</label><input type="text" name="name" class="form-control" required></div>
      <div class="form-group"><label><?= t('email') ?> *</label><input type="email" name="email" class="form-control" required></div>
      <div class="form-group"><label><?= t('phone') ?></label><input type="tel" name="phone" class="form-control"></div>
      <div class="form-group"><label><?= t('password') ?> *</label><input type="password" name="password" class="form-control" required minlength="6"></div>
      <div class="form-group"><label><?= t('confirm_password') ?> *</label><input type="password" name="confirm_password" class="form-control" required></div>
      <button type="submit" class="btn btn-primary" style="width:100%;"><?= t('sign_up') ?></button>
    </form>
    <hr class="auth-divider">
    <p class="auth-footer"><?= t('have_account') ?><a href="login.php"><?= t('sign_in') ?></a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
