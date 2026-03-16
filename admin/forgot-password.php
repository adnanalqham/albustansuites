<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if(isAdminLoggedIn()) { header('Location: dashboard.php'); exit; }

$step    = (int)($_GET['step'] ?? 1);
$success = false;
$error   = '';

// ── Step 1: Enter email ─────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if(!$email) { $error = t('admin_invalid_email'); }
    else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM admins WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if($admin) {
            // Generate OTP (6-digit)
            $otp     = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 900); // 15 minutes
            // Try to save OTP (add columns if missing)
            try {
                $db->prepare("UPDATE admins SET reset_otp=?, reset_expires=? WHERE id=?")
                   ->execute([$otp, $expires, $admin['id']]);
            } catch(Exception $e) {
                // Columns might not exist yet, do a safe ALTER
                try {
                    $db->exec("ALTER TABLE admins ADD COLUMN reset_otp VARCHAR(10) NULL");
                    $db->exec("ALTER TABLE admins ADD COLUMN reset_expires DATETIME NULL");
                    $db->prepare("UPDATE admins SET reset_otp=?, reset_expires=? WHERE id=?")
                       ->execute([$otp, $expires, $admin['id']]);
                } catch(Exception $e2) {}
            }
            // Show OTP on screen (since no mail server in localhost)
            $_SESSION['reset_email']   = $email;
            $_SESSION['reset_otp_dev'] = $otp; // DEV ONLY - remove in production
        }
        // Always redirect to prevent email enumeration
        header('Location: forgot-password.php?step=2'); exit;
    }
}

// ── Step 2: Verify OTP ──────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['otp'])) {
    $otp   = trim($_POST['otp'] ?? '');
    $email = $_SESSION['reset_email'] ?? '';
    if(!$email) { header('Location: forgot-password.php'); exit; }
    $db    = getDB();
    $stmt  = $db->prepare("SELECT id FROM admins WHERE email=? AND reset_otp=? AND reset_expires>NOW() LIMIT 1");
    $stmt->execute([$email, $otp]);
    $admin = $stmt->fetch();
    if($admin) {
        $_SESSION['reset_verified_id'] = $admin['id'];
        header('Location: forgot-password.php?step=3'); exit;
    } else {
        $error = t('admin_invalid_code');
        $step  = 2;
    }
}

// ── Step 3: New password ────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['new_password'])) {
    $adminId = $_SESSION['reset_verified_id'] ?? null;
    if(!$adminId) { header('Location: forgot-password.php'); exit; }
    $pw  = $_POST['new_password'] ?? '';
    $pw2 = $_POST['confirm_password'] ?? '';
    if(strlen($pw) < 6) {
        $error = t('admin_password_min_length');
        $step  = 3;
    } elseif($pw !== $pw2) {
        $error = t('admin_passwords_mismatch');
        $step  = 3;
    } else {
        $hash = password_hash($pw, PASSWORD_BCRYPT);
        $db   = getDB();
        $db->prepare("UPDATE admins SET password=?, reset_otp=NULL, reset_expires=NULL WHERE id=?")
           ->execute([$hash, $adminId]);
        unset($_SESSION['reset_email'], $_SESSION['reset_otp_dev'], $_SESSION['reset_verified_id']);
        $success = true;
        $step    = 4;
    }
}

$devOtp = $_SESSION['reset_otp_dev'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('admin_reset_password_title') ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
<style>
body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:radial-gradient(ellipse at top,#112236 0%,#0A1628 70%);font-family:'Cairo','Inter',sans-serif;}
.card{background:var(--admin-card);border:1px solid var(--admin-border);border-radius:16px;padding:44px;max-width:420px;width:100%;box-shadow:0 30px 80px rgba(0,0,0,0.5);}
.card-logo{text-align:center;margin-bottom:28px;}
.card-logo img{height:55px;margin:0 auto 10px;}
.card-logo h1{font-size:20px;font-weight:700;color:var(--cream);}
.card-logo p{font-size:13px;color:var(--gray);}
.steps{display:flex;gap:0;margin-bottom:32px;}
.step-dot{flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;position:relative;}
.step-dot::after{content:'';position:absolute;top:14px;left:50%;width:100%;height:2px;background:var(--admin-border);}
.step-dot:last-child::after{display:none;}
.dot{width:28px;height:28px;border-radius:50%;border:2px solid var(--admin-border);background:var(--admin-darker);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--gray);position:relative;z-index:1;transition:0.3s;}
.dot.active{border-color:var(--gold);background:var(--gold);color:#0a1628;}
.dot.done{border-color:var(--gold);background:transparent;color:var(--gold);}
.step-label{font-size:10px;color:var(--gray);text-align:center;white-space:nowrap;}
.form-group{margin-bottom:18px;}
label{display:block;font-size:12px;color:var(--gray);letter-spacing:1px;text-transform:uppercase;margin-bottom:7px;}
.input-wrap{position:relative;}
.input-wrap input{width:100%;background:var(--admin-darker);border:1px solid var(--admin-border);border-radius:8px;padding:11px 42px 11px 14px;color:var(--cream);font-size:14px;transition:0.2s;}
.input-wrap input:focus{outline:none;border-color:var(--gold);}
.toggle-pw{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray);cursor:pointer;font-size:14px;padding:4px;}
.toggle-pw:hover{color:var(--gold);}
input.plain{width:100%;background:var(--admin-darker);border:1px solid var(--admin-border);border-radius:8px;padding:11px 14px;color:var(--cream);font-size:14px;transition:0.2s;}
input.plain:focus{outline:none;border-color:var(--gold);}
.otp-hint{background:rgba(201,168,76,0.1);border:1px solid rgba(201,168,76,0.3);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--gold);margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.error-box{background:rgba(255,71,87,0.1);border:1px solid rgba(255,71,87,0.3);color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.success-box{text-align:center;padding:20px 0;}
.success-box i{font-size:56px;color:var(--gold);margin-bottom:16px;display:block;}
.success-box h2{font-size:24px;color:var(--cream);margin-bottom:8px;}
.success-box p{font-size:14px;color:var(--gray);}
.btn-full{display:block;width:100%;padding:13px;text-align:center;border:none;cursor:pointer;}
a.back{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--gold);margin-top:20px;}
a.back:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="card">
  <div class="card-logo">
    <img src="../images/logo.png" alt="Al Bustan" onerror="this.style.display='none'">
    <h1><?= t('admin_reset_password') ?></h1>
    <p>Al Bustan Luxurious Suites</p>
  </div>

  <!-- Progress dots -->
  <?php if($step < 4): ?>
  <div class="steps">
    <?php
    $steps = [1=>'Email', 2=>'Code', 3=>'New Password'];
    foreach($steps as $n=>$label):
      $cls = $n < $step ? 'done' : ($n === $step ? 'active' : '');
      $icon = $n < $step ? '<i class="fas fa-check"></i>' : $n;
    ?>
    <div class="step-dot">
      <div class="dot <?= $cls ?>"><?= $icon ?></div>
      <span class="step-label"><?= $label ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if($error): ?>
  <div class="error-box"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
  <?php endif; ?>

  <!-- ── STEP 1 ── -->
  <?php if($step === 1): ?>
  <form method="POST">
    <div class="form-group">
      <label><?= t('admin_email_address') ?></label>
      <input class="plain" type="email" name="email" required autofocus placeholder="admin@albustan.com">
    </div>
    <button type="submit" class="btn btn-primary btn-full">
      <i class="fas fa-paper-plane"></i> <?= t('admin_send_code') ?>
    </button>
  </form>

  <!-- ── STEP 2 ── -->
  <?php elseif($step === 2): ?>
  <?php if($devOtp): ?>
  <div class="otp-hint"><i class="fas fa-info-circle"></i> <strong>DEV MODE — Your code: <?= $devOtp ?></strong> (Remove this in production)</div>
  <?php else: ?>
  <p style="font-size:13px;color:var(--gray);margin-bottom:16px;"><?= t('admin_enter_code') ?> <strong style="color:var(--cream);"><?= htmlspecialchars($_SESSION['reset_email']??'your email') ?></strong></p>
  <?php endif; ?>
  <form method="POST">
    <input type="hidden" name="otp" id="otp-hidden">
    <div class="form-group">
      <label><?= t('admin_6_digit_code') ?></label>
      <div class="otp-boxes" style="display:flex;gap:8px;justify-content:center;margin-bottom:4px;">
        <?php for($i=0;$i<6;$i++): ?>
        <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
          style="width:48px;height:52px;text-align:center;font-size:22px;font-weight:700;background:var(--admin-darker);border:1px solid var(--admin-border);border-radius:8px;color:var(--cream);transition:0.2s;">
        <?php endfor; ?>
      </div>
    </div>
    <button type="submit" id="otp-btn" class="btn btn-primary btn-full" style="margin-top:8px;">
      <i class="fas fa-check-circle"></i> <?= t('admin_verify_code') ?>
    </button>
  </form>

  <!-- ── STEP 3 ── -->
  <?php elseif($step === 3): ?>
  <form method="POST">
    <div class="form-group">
      <label><?= t('admin_new_password') ?></label>
      <div class="input-wrap">
        <input type="password" name="new_password" id="pw1" required placeholder="Min 6 characters" autocomplete="new-password">
        <button type="button" class="toggle-pw" onclick="togglePw('pw1','eye1')"><i id="eye1" class="fas fa-eye"></i></button>
      </div>
    </div>
    <div class="form-group">
      <label><?= t('admin_confirm_password') ?></label>
      <div class="input-wrap">
        <input type="password" name="confirm_password" id="pw2" required placeholder="Repeat password" autocomplete="new-password">
        <button type="button" class="toggle-pw" onclick="togglePw('pw2','eye2')"><i id="eye2" class="fas fa-eye"></i></button>
      </div>
    </div>
    <div id="pw-match" style="font-size:12px;margin-bottom:12px;min-height:18px;"></div>
    <button type="submit" class="btn btn-primary btn-full">
      <i class="fas fa-lock"></i> <?= t('admin_reset_password') ?>
    </button>
  </form>

  <!-- ── STEP 4: Success ── -->
  <?php else: ?>
  <div class="success-box">
    <i class="fas fa-check-circle"></i>
    <h2><?= t('admin_reset_password') ?>!</h2>
    <p><?= t('admin_password_updated') ?></p>
    <a href="login.php" class="btn btn-primary" style="margin-top:24px;display:inline-flex;">
      <i class="fas fa-sign-in-alt"></i> <?= t('admin_sign_in_now') ?>
    </a>
  </div>
  <?php endif; ?>

  <?php if($step < 4): ?>
  <div style="text-align:center;margin-top:20px;">
    <a href="login.php" class="back"><i class="fas fa-arrow-left"></i> <?= t('admin_back_login') ?></a>
  </div>
  <?php endif; ?>
</div>

<script>
// OTP boxes auto-advance
const digits = document.querySelectorAll('.otp-digit');
digits.forEach((box, i) => {
  box.addEventListener('keyup', e => {
    if(e.key >= '0' && e.key <= '9') {
      if(i < digits.length - 1) digits[i+1].focus();
    } else if(e.key === 'Backspace') {
      box.value = '';
      if(i > 0) digits[i-1].focus();
    }
  });
  box.addEventListener('paste', e => {
    const text = e.clipboardData.getData('text').replace(/\D/g,'');
    [...text].forEach((ch, j) => { if(digits[i+j]) digits[i+j].value = ch; });
    e.preventDefault();
  });
  box.addEventListener('focus', () => box.style.borderColor='var(--gold)');
  box.addEventListener('blur',  () => box.style.borderColor='var(--admin-border)');
});
document.getElementById('otp-btn')?.addEventListener('click', function(e) {
  e.preventDefault();
  const code = [...digits].map(d => d.value).join('');
  document.getElementById('otp-hidden').value = code;
  this.closest('form').submit();
});

// Password toggle
function togglePw(id, eyeId) {
  const f = document.getElementById(id);
  const e = document.getElementById(eyeId);
  f.type = f.type === 'password' ? 'text' : 'password';
  e.className = f.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
}

// Password match indicator
const pw1 = document.getElementById('pw1');
const pw2 = document.getElementById('pw2');
const indicator = document.getElementById('pw-match');
if(pw2) pw2.addEventListener('input', () => {
  if(!pw2.value) { indicator.innerHTML=''; return; }
  const ok = pw1.value === pw2.value;
  indicator.innerHTML = ok
    ? '<span style="color:#00c864"><i class="fas fa-check"></i> Passwords match</span>'
    : '<span style="color:var(--danger)"><i class="fas fa-times"></i> Passwords do not match</span>';
});
</script>
</body>
</html>
