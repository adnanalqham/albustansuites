<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Already logged in
if(isAdminLoggedIn()) { header('Location: dashboard.php'); exit; }

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $pass     = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    if($email && $pass) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if($admin && password_verify($pass, $admin['password'])) {
            $_SESSION[ADMIN_SESSION_NAME] = [
                'id'    => $admin['id'],
                'name'  => $admin['name'],
                'email' => $admin['email'],
                'role'  => $admin['role']
            ];
            $db->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")->execute([$admin['id']]);
            // Remember me cookie (30 days)
            if($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('admin_remember', $token, time() + (86400 * 30), '/', '', false, true);
                $db->prepare("UPDATE admins SET remember_token=? WHERE id=?")->execute([$token, $admin['id']]);
            }
            header('Location: dashboard.php'); exit;
        }
    }
    $errors[] = t('admin_invalid_login');
}

// Auto-create default admin if none exists
try {
    $db    = getDB();
    $count = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if($count == 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO admins (name,email,password,role) VALUES (?,?,?,?)")
           ->execute(['Super Admin','admin@albustan.com',$hash,'super_admin']);
    }
} catch(Exception $e){}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('admin_login_title') ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
<style>
body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:radial-gradient(ellipse at top,#112236 0%,#0A1628 70%);font-family:'Cairo','Inter',sans-serif;}
.login-card{background:var(--admin-card);border:1px solid var(--admin-border);border-radius:16px;padding:48px;max-width:420px;width:100%;box-shadow:0 30px 80px rgba(0,0,0,0.5);}
.login-logo{text-align:center;margin-bottom:32px;}
.login-logo img{height:60px;margin:0 auto 12px;}
.login-logo h1{font-size:22px;font-weight:700;color:var(--cream);}
.login-logo p{font-size:13px;color:var(--gray);}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:12px;color:var(--gray);letter-spacing:1px;text-transform:uppercase;margin-bottom:7px;}
.input-wrap{position:relative;}
.input-wrap input{width:100%;background:var(--admin-darker);border:1px solid var(--admin-border);border-radius:8px;padding:11px 42px 11px 14px;color:var(--cream);font-size:14px;transition:0.2s;}
.input-wrap input:focus{outline:none;border-color:var(--gold);}
.toggle-pw{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray);cursor:pointer;font-size:15px;padding:4px;transition:0.2s;}
.toggle-pw:hover{color:var(--gold);}
.error{background:rgba(255,71,87,0.1);border:1px solid rgba(255,71,87,0.3);color:var(--danger);padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.remember-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;}
.remember-label{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--gray);user-select:none;}
.remember-label input[type=checkbox]{width:16px;height:16px;accent-color:var(--gold);cursor:pointer;}
.forgot-link{font-size:13px;color:var(--gold);text-decoration:none;transition:0.2s;}
.forgot-link:hover{color:var(--gold-light);text-decoration:underline;}
.divider{border:none;border-top:1px solid var(--admin-border);margin:24px 0;}
</style>
</head>
<body>
<div class="login-card">
  <div class="login-logo">
    <img src="../images/logo.png" alt="Al Bustan" onerror="this.style.display='none'">
    <h1><?= t('admin_panel') ?></h1>
    <p>Al Bustan Luxurious Suites</p>
  </div>

  <?php if($errors): ?>
  <div class="error"><i class="fas fa-exclamation-circle"></i><?= $errors[0] ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="on">

    <div class="form-group">
      <label><?= t('admin_email_address') ?></label>
      <div class="input-wrap">
        <input type="email" name="email" required autofocus placeholder="admin@albustan.com" autocomplete="username" value="<?= htmlspecialchars($_POST['email']??'') ?>">
      </div>
    </div>

    <div class="form-group">
      <label><?= t('admin_password') ?></label>
      <div class="input-wrap">
        <input type="password" name="password" id="pw-field" required placeholder="••••••••" autocomplete="current-password">
        <button type="button" class="toggle-pw" id="toggle-pw" title="Show/hide password">
          <i class="fas fa-eye" id="pw-eye"></i>
        </button>
      </div>
    </div>

    <div class="remember-row">
      <label class="remember-label">
        <input type="checkbox" name="remember" id="remember" <?= isset($_POST['remember'])?'checked':''?>>
        <?= t('admin_remember_me') ?>
      </label>
      <a href="forgot-password.php" class="forgot-link"><?= t('admin_forgot_password') ?></a>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;font-size:15px;">
      <i class="fas fa-sign-in-alt"></i> <?= t('admin_sign_in') ?>
    </button>
  </form>

  <hr class="divider">
  <p style="text-align:center;font-size:12px;color:var(--gray);">
    <a href="../index.php" style="color:var(--gold);"><i class="fas fa-arrow-left"></i> <?= t('admin_back_website') ?></a>
  </p>
</div>

<script>
document.getElementById('toggle-pw').addEventListener('click', function() {
  const field = document.getElementById('pw-field');
  const eye   = document.getElementById('pw-eye');
  if(field.type === 'password') {
    field.type = 'text';
    eye.className = 'fas fa-eye-slash';
    this.title = 'Hide password';
  } else {
    field.type = 'password';
    eye.className = 'fas fa-eye';
    this.title = 'Show password';
  }
});
</script>
</body>
</html>
