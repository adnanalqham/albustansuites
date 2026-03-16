<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

// Pre-selected room & dates from URL
$roomSlug  = sanitize($_GET['room']     ?? '');
$checkIn   = sanitize($_GET['check_in'] ?? date('Y-m-d'));
$checkOut  = sanitize($_GET['check_out']?? date('Y-m-d', strtotime('+1 day')));
$adults    = (int)($_GET['adults']      ?? 2);
$offerId   = (int)($_GET['offer']       ?? 0);

// Load room list
$roomsList = $db->query("SELECT id,slug,name_en,name_ar,price_per_night,currency FROM rooms WHERE is_available=1 ORDER BY sort_order")->fetchAll();

// Pre-selected room
$selectedRoom = null;
if($roomSlug) {
    foreach($roomsList as $r) {
        if($r['slug'] === $roomSlug) { $selectedRoom = $r; break; }
    }
}

// Handle POST: confirm booking
$bookingDone = false;
$bookingRef  = '';
$errors      = [];
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    if(!verifyCsrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid security token.'; }
    else {
        $bRoomId   = (int)$_POST['room_id'];
        $bCheckIn  = sanitize($_POST['check_in']);
        $bCheckOut = sanitize($_POST['check_out']);
        $bAdults   = (int)$_POST['adults'];
        $bChildren = (int)$_POST['children'];
        $bName     = sanitize($_POST['guest_name']);
        $bEmail    = filter_var($_POST['guest_email'], FILTER_VALIDATE_EMAIL);
        $bPhone    = sanitize($_POST['guest_phone']);
        $bNation   = sanitize($_POST['guest_nationality'] ?? '');
        $bPay      = in_array($_POST['payment_method'], ['pay_at_hotel','bank_transfer']) ? $_POST['payment_method'] : 'pay_at_hotel';
        $bRequests = sanitize($_POST['special_requests'] ?? '');

        if(!$bRoomId) $errors[] = 'Please select a room.';
        if(!$bCheckIn || !$bCheckOut) $errors[] = 'Please select dates.';
        if(!$bName) $errors[] = 'Name is required.';
        if(!$bEmail) $errors[] = 'Valid email required.';
        if(!$bPhone) $errors[] = 'Phone is required.';

        if(empty($errors)) {
            // Check availability
            if(!checkRoomAvailability($bRoomId, $bCheckIn, $bCheckOut)) {
                $errors[] = $lang==='ar' ? 'الغرفة غير متاحة في هذه التواريخ' : 'Room is not available for the selected dates.';
            } else {
                $nights = calculateNights($bCheckIn, $bCheckOut);
                $stmt   = $db->prepare("SELECT price_per_night, currency FROM rooms WHERE id=?");
                $stmt->execute([$bRoomId]);
                $roomData = $stmt->fetch();
                $roomPrice  = $roomData['price_per_night'];
                $taxRate    = (float)(getSetting('tax_rate','10'))/100;
                $totalPrice = $nights * $roomPrice * (1 + $taxRate);

                $ref = generateBookingRef();
                $userId = isUserLoggedIn() ? getUser()['id'] : null;

                $ins = $db->prepare("INSERT INTO bookings (booking_ref,user_id,room_id,guest_name,guest_email,guest_phone,guest_nationality,adults,children,check_in,check_out,nights,room_price,total_price,currency,payment_method,special_requests,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");
                $ins->execute([$ref,$userId,$bRoomId,$bName,$bEmail,$bPhone,$bNation,$bAdults,$bChildren,$bCheckIn,$bCheckOut,$nights,$roomPrice,$totalPrice,$roomData['currency'],$bPay,$bRequests]);
                $bookingDone = true;
                $bookingRef  = $ref;
            }
        }
    }
}

$pageTitle = t('booking_title') . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/images/booking-hero.jpg')"></div>
  <div class="page-hero-content">
    <h1><?= t('booking_title') ?></h1>
    <nav class="breadcrumb"><a href="index.php"><?= t('nav_home') ?></a><span>›</span><span><?= t('booking_title') ?></span></nav>
  </div>
</div>

<div class="booking-page">
  <div class="container">

<?php if($bookingDone): ?>
  <!-- SUCCESS -->
  <div style="max-width:600px;margin:0 auto;text-align:center;padding:60px 0;">
    <div style="width:90px;height:90px;background:rgba(0,200,100,0.1);border:2px solid #00c864;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 28px;font-size:40px;color:#00c864;"><i class="fas fa-check"></i></div>
    <h2 style="font-family:var(--font-serif);font-size:36px;color:var(--cream);margin-bottom:12px;"><?= t('booking_success') ?></h2>
    <p style="color:var(--gray);margin-bottom:28px;"><?= $lang==='ar'?'شكراً لك! تم تأكيد حجزك بنجاح. ستصلك رسالة تأكيد على بريدك الإلكتروني قريباً.':'Thank you! Your booking has been confirmed. A confirmation will be sent to your email shortly.'?></p>
    <div style="background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:32px;">
      <p style="font-size:14px;color:var(--gray);"><?= t('booking_ref') ?></p>
      <p style="font-size:32px;font-weight:800;color:var(--gold);letter-spacing:2px;"><?= e($bookingRef) ?></p>
    </div>
    <div style="display:flex;gap:16px;justify-content:center;">
      <a href="index.php" class="btn btn-outline"><?= t('nav_home') ?></a>
      <?php if(isUserLoggedIn()): ?>
      <a href="profile.php" class="btn btn-primary"><?= t('my_bookings') ?></a>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>
  <!-- BOOKING FORM -->
  <?php if($errors): ?>
  <div class="flash flash-error" style="max-width:800px;margin:0 auto 24px;"><?= implode('<br>', array_map('e', $errors)) ?></div>
  <?php endif; ?>

  <div class="booking-steps">
    <div class="step active"><div class="step-num">1</div><div class="step-label"><?= t('step_dates') ?></div></div>
    <div class="step-line"></div>
    <div class="step"><div class="step-num">2</div><div class="step-label"><?= t('step_guest') ?></div></div>
    <div class="step-line"></div>
    <div class="step"><div class="step-num">3</div><div class="step-label"><?= t('step_confirm') ?></div></div>
  </div>

  <form method="POST" id="bookingForm">
    <?= csrfField() ?>
    <div class="booking-layout">
      <!-- Left: Form -->
      <div>
        <!-- Step 1: Dates & Room -->
        <div class="step-panel" id="step1">
          <h3 style="font-family:var(--font-serif);font-size:22px;color:var(--cream);margin-bottom:24px;"><?= t('step_dates') ?></h3>
          <div class="form-row">
            <div class="form-group">
              <label><?= t('check_in') ?></label>
              <input type="date" name="check_in" id="checkIn" class="form-control" value="<?= e($checkIn) ?>" min="<?= date('Y-m-d') ?>" required onchange="calcBookingTotal()">
            </div>
            <div class="form-group">
              <label><?= t('check_out') ?></label>
              <input type="date" name="check_out" id="checkOut" class="form-control" value="<?= e($checkOut) ?>" min="<?= date('Y-m-d',strtotime('+1 day')) ?>" required onchange="calcBookingTotal()">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label><?= t('adults') ?></label>
              <select name="adults" class="form-control">
                <?php for($i=1;$i<=6;$i++): ?><option value="<?=$i?>" <?=$adults===$i?'selected':''?>><?=$i?> <?=$lang==='ar'?'بالغ':'Adult'.($i>1?'s':'')?></option><?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label><?= t('children') ?></label>
              <select name="children" class="form-control">
                <?php for($i=0;$i<=4;$i++): ?><option value="<?=$i?>"><?=$i?></option><?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label><?= t('room_type') ?></label>
            <select name="room_id" id="roomSelect" class="form-control" required onchange="calcBookingTotal()">
              <option value=""><?= $lang==='ar'?'-- اختر الغرفة --':'-- Select Room --' ?></option>
              <?php foreach($roomsList as $r): ?>
              <option value="<?= $r['id'] ?>" data-price="<?= $r['price_per_night'] ?>"
                <?= ($selectedRoom && $selectedRoom['slug']===$r['slug']) ? 'selected' : '' ?>>
                <?= e($r['name_'.$lang]) ?> - <?= formatPrice($r['price_per_night'],$r['currency']) ?> / <?= t('night') ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <h3 style="font-family:var(--font-serif);font-size:22px;color:var(--cream);margin:32px 0 24px;"><?= t('step_guest') ?></h3>
          <div class="form-row">
            <div class="form-group">
              <label><?= t('full_name') ?> *</label>
              <input type="text" name="guest_name" class="form-control" required value="<?= isUserLoggedIn()?e(getUser()['name']):'' ?>">
            </div>
            <div class="form-group">
              <label><?= t('email') ?> *</label>
              <input type="email" name="guest_email" class="form-control" required value="<?= isUserLoggedIn()?e(getUser()['email']):'' ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label><?= t('phone') ?> *</label>
              <input type="tel" name="guest_phone" class="form-control" required value="<?= isUserLoggedIn()?e(getUser()['phone']??''):'' ?>">
            </div>
            <div class="form-group">
              <label><?= t('nationality') ?></label>
              <input type="text" name="guest_nationality" class="form-control">
            </div>
          </div>

          <h3 style="font-family:var(--font-serif);font-size:22px;color:var(--cream);margin:32px 0 24px;"><?= t('payment_method') ?></h3>
          <div class="payment-methods">
            <label class="payment-option">
              <input type="radio" name="payment_method" value="pay_at_hotel" checked>
              <div class="payment-card"><i class="fas fa-hotel"></i><div><strong><?= t('pay_at_hotel') ?></strong><span><?= $lang==='ar'?'ادفع عند وصولك إلى الفندق':'Pay in full upon arrival at the hotel'?></span></div></div>
            </label>
            <label class="payment-option">
              <input type="radio" name="payment_method" value="bank_transfer">
              <div class="payment-card"><i class="fas fa-university"></i><div><strong><?= t('bank_transfer') ?></strong><span><?= $lang==='ar'?'تحويل بنكي مسبق':'Bank transfer in advance'?></span></div></div>
            </label>
          </div>

          <div class="form-group" style="margin-top:24px;">
            <label><?= t('special_requests') ?></label>
            <textarea name="special_requests" class="form-control" rows="3" placeholder="<?= $lang==='ar'?'مثال: طابق عالٍ، سرير إضافي، احتفال بعيد الميلاد...':'e.g. High floor, extra bed, birthday celebration...'?>"></textarea>
          </div>

          <button type="submit" name="confirm_booking" value="1" class="btn btn-primary btn-lg" style="margin-top:8px;"><i class="fas fa-check-circle"></i> <?= t('confirm_booking') ?></button>
        </div>
      </div>

      <!-- Right: Summary -->
      <div class="booking-summary">
        <h3><?= $lang==='ar'?'ملخص الحجز':'Booking Summary'?></h3>
        <div id="summaryContent">
          <p style="color:var(--gray);font-size:14px;"><?= $lang==='ar'?'اختر الغرفة والتواريخ لعرض ملخص الحجز':'Select room and dates to view summary'?></p>
        </div>
        <div id="priceSummary" style="display:none;">
          <div class="summary-row"><span><?= $lang==='ar'?'الغرفة':'Room'?></span><strong id="sRoomName">-</strong></div>
          <div class="summary-row"><span><?= t('check_in') ?></span><strong id="sCheckIn">-</strong></div>
          <div class="summary-row"><span><?= t('check_out') ?></span><strong id="sCheckOut">-</strong></div>
          <div class="summary-row"><span><?= t('nights') ?></span><strong id="sNights">-</strong></div>
          <div class="summary-row"><span><?= t('room_price') ?></span><strong id="sRoomPrice">-</strong></div>
          <div class="summary-row"><span><?= t('taxes_fees') ?> (<?= getSetting('tax_rate','10') ?>%)</span><strong id="sTax">-</strong></div>
          <div class="summary-total"><span><?= t('total_price') ?></span><span id="sTotal">-</span></div>
        </div>
        <?php if(!isUserLoggedIn()): ?>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
          <p style="font-size:13px;color:var(--gray);margin-bottom:10px;"><?= $lang==='ar'?'سجل الدخول لحفظ حجوزاتك في حسابك:':'Sign in to save bookings to your account:'?></p>
          <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;"><?= t('sign_in') ?></a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </form>
<?php endif; ?>
  </div>
</div>

<style>
.payment-methods{display:flex;flex-direction:column;gap:12px;}
.payment-option{cursor:pointer;}
.payment-option input{display:none;}
.payment-card{display:flex;align-items:center;gap:16px;padding:16px;background:var(--dark-3);border:2px solid var(--border);border-radius:var(--radius-sm);transition:0.3s;}
.payment-card i{font-size:24px;color:var(--gold);width:32px;text-align:center;}
.payment-card strong{display:block;color:var(--cream);font-size:15px;}
.payment-card span{font-size:13px;color:var(--gray);}
.payment-option input:checked + .payment-card{border-color:var(--gold);background:rgba(201,168,76,0.05);}
</style>

<script>
const taxRate=<?= (float)getSetting('tax_rate','10') ?>/100;
const sym='<?= getSetting("currency_symbol","$") ?>';
function calcBookingTotal(){
  const ci=document.getElementById('checkIn')?.value;
  const co=document.getElementById('checkOut')?.value;
  const sel=document.getElementById('roomSelect');
  const opt=sel?.options[sel.selectedIndex];
  if(!ci||!co||!opt||!opt.value)return;
  const nights=Math.max(1,Math.round((new Date(co)-new Date(ci))/86400000));
  const price=parseFloat(opt.dataset.price)||0;
  const tax=nights*price*taxRate;
  const total=nights*price+tax;
  document.getElementById('sRoomName').textContent=opt.text.split(' - ')[0];
  document.getElementById('sCheckIn').textContent=ci;
  document.getElementById('sCheckOut').textContent=co;
  document.getElementById('sNights').textContent=nights;
  document.getElementById('sRoomPrice').textContent=sym+Math.round(nights*price).toLocaleString();
  document.getElementById('sTax').textContent=sym+Math.round(tax).toLocaleString();
  document.getElementById('sTotal').textContent=sym+Math.round(total).toLocaleString();
  document.getElementById('priceSummary').style.display='block';
  document.getElementById('summaryContent').style.display='none';
}
document.getElementById('roomSelect')?.addEventListener('change',calcBookingTotal);
window.addEventListener('load',calcBookingTotal);
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
