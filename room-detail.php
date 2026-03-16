<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$lang = getLang();
$db   = getDB();

$slug = sanitize($_GET['slug'] ?? '');
if(!$slug) { header('Location: rooms.php'); exit; }

$stmt = $db->prepare("SELECT r.*, rc.name_en as cat_en, rc.name_ar as cat_ar FROM rooms r JOIN room_categories rc ON r.category_id=rc.id WHERE r.slug=? AND r.is_available=1 LIMIT 1");
$stmt->execute([$slug]);
$room = $stmt->fetch();
if(!$room) { header('Location: rooms.php'); exit; }

$roomId = (int)$room['id'];
try {
    $amenities = $db->query("SELECT a.name_{$lang} as name, a.icon FROM room_amenities ra JOIN amenities a ON ra.amenity_id = a.id WHERE ra.room_id = $roomId")->fetchAll();
} catch (Exception $e) {
    $amenities = [];
}
$images    = decodeJson($room['images']??'');
if(empty($images) && $room['main_image']) $images = [$room['main_image']];

$pageTitle = e($room['name_'.$lang]) . ' - ' . getHotelName();
require __DIR__ . '/includes/header.php';
?>
<div class="page-hero">
  <div class="page-hero-bg" style="background-image:url('<?= SITE_URL ?>/<?= e($room['main_image'] ?? 'images/room-default.jpg') ?>')"></div>
  <div class="page-hero-content">
    <h1><?= e($room['name_'.$lang]) ?></h1>
    <nav class="breadcrumb">
      <a href="index.php"><?= t('nav_home') ?></a><span>›</span>
      <a href="rooms.php"><?= t('nav_rooms') ?></a><span>›</span>
      <span><?= e($room['name_'.$lang]) ?></span>
    </nav>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="room-detail-grid">
      <!-- Left: Gallery + Description -->
      <div class="room-detail-main">
        <!-- Gallery -->
        <div class="room-gallery">
          <div class="room-gallery-main">
            <img src="<?= SITE_URL ?>/<?= e($room['main_image'] ?? 'images/room-default.jpg') ?>" alt="<?= e($room['name_'.$lang]) ?>" id="mainRoomImg">
          </div>
          <?php if(count($images)>1): ?>
          <div class="room-gallery-thumbs">
            <?php foreach($images as $img): ?>
            <img src="<?= SITE_URL ?>/<?= e($img) ?>" alt="Room" onclick="document.getElementById('mainRoomImg').src=this.src" class="gallery-thumb">
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Details -->
        <div class="room-info-cards">
          <div class="room-info-item"><i class="fas fa-ruler-combined"></i><div><span><?= t('room_size') ?></span><strong><?= $room['size_sqm'] ?> <?= t('sqm') ?></strong></div></div>
          <div class="room-info-item"><i class="fas fa-users"></i><div><span><?= t('adults') ?></span><strong><?= $room['capacity_adults'] ?> + <?= $room['capacity_children'] ?> <?= t('children') ?></strong></div></div>
          <div class="room-info-item"><i class="fas fa-eye"></i><div><span><?= $lang==='ar'?'الإطلالة':'View' ?></span><strong><?= e($room['view_type_'.$lang]??'-') ?></strong></div></div>
          <div class="room-info-item"><i class="fas fa-building"></i><div><span><?= $lang==='ar'?'الطابق':'Floor' ?></span><strong><?= e($room['floor']??'-') ?></strong></div></div>
        </div>

        <h2 style="font-family:var(--font-serif);color:var(--cream);font-size:28px;margin:32px 0 16px;"><?= $lang==='ar'?'وصف الغرفة':'Room Description' ?></h2>
        <p style="color:var(--gray);line-height:1.9;font-size:16px;"><?= e($room['description_'.$lang]) ?></p>

        <?php if($amenities): ?>
        <h2 style="font-family:var(--font-serif);color:var(--cream);font-size:28px;margin:32px 0 16px;"><?= t('amenities') ?></h2>
        <div class="amenities-grid">
          <?php foreach($amenities as $a): ?>
          <div class="amenity-item"><i class="<?= e($a['icon']??'fas fa-check-circle') ?>"></i><?= e($a['name']) ?></div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Right: Booking sidebar -->
      <div class="room-booking-sidebar">
        <div class="booking-sidebar-card">
          <div class="price-display">
            <span class="from"><?= $lang==='ar'?'يبدأ من':'Starting from'?></span>
            <div class="big-price"><?= formatPrice($room['price_per_night'], $room['currency']) ?></div>
            <span class="per"><?= t('per_night') ?></span>
          </div>
          <form action="booking.php" method="GET" style="margin-top:24px;">
            <input type="hidden" name="room" value="<?= e($room['slug']) ?>">
            <div class="form-group">
              <label><?= t('check_in') ?></label>
              <input type="date" name="check_in" id="ci" class="form-control" min="<?= date('Y-m-d') ?>" required onchange="updateCheckout()">
            </div>
            <div class="form-group">
              <label><?= t('check_out') ?></label>
              <input type="date" name="check_out" id="co" class="form-control" required onchange="calcTotal()">
            </div>
            <div class="form-group">
              <label><?= t('guests') ?></label>
              <select name="adults" class="form-control">
                <?php for($i=1;$i<=$room['capacity_adults'];$i++): ?>
                <option value="<?=$i?>"><?=$i?> <?=$lang==='ar'?'بالغ':'Adult'.($i>1?'s':'')?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div id="availabilityResult" style="display:none;padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px;"></div>
            <div id="nightsCalc" style="display:none;background:var(--dark-3);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:16px;">
              <div class="summary-row"><span id="nightsLabel"><?= t('nights') ?></span><strong id="nightsCount">0</strong></div>
              <div class="summary-total"><span><?= t('total_price') ?></span><span id="totalPrice">-</span></div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;"><?= t('book_room') ?></button>
          </form>
          <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);text-align:center;">
            <p style="font-size:12px;color:var(--gray);display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fas fa-shield-alt" style="color:var(--gold);"></i><?= $lang==='ar'?'ضمان أفضل سعر':'Best Rate Guarantee'?></p>
          </div>
        </div>
        <a href="contact.php" style="display:flex;align-items:center;gap:10px;justify-content:center;margin-top:16px;color:var(--gray);font-size:14px;"><i class="fas fa-headset" style="color:var(--gold);"></i><?= $lang==='ar'?'هل تحتاج مساعدة؟ اتصل بنا':'Need help? Contact us'?></a>
      </div>
    </div>
  </div>
</section>

<style>
.room-detail-grid{display:grid;grid-template-columns:1fr 360px;gap:48px;align-items:start;}
.room-gallery-main{border-radius:var(--radius);overflow:hidden;margin-bottom:12px;aspect-ratio:16/10;}
.room-gallery-main img{width:100%;height:100%;object-fit:cover;}
.room-gallery-thumbs{display:flex;gap:10px;flex-wrap:wrap;}
.gallery-thumb{width:80px;height:60px;object-fit:cover;border-radius:6px;cursor:pointer;border:2px solid transparent;transition:0.3s;}
.gallery-thumb:hover{border-color:var(--gold);}
.room-info-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:28px 0;}
.room-info-item{background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px;display:flex;align-items:center;gap:12px;}
.room-info-item i{color:var(--gold);font-size:20px;}
.room-info-item span{display:block;font-size:11px;color:var(--gray);text-transform:uppercase;letter-spacing:1px;}
.room-info-item strong{display:block;font-size:14px;color:var(--cream);margin-top:3px;}
.amenities-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;}
.amenity-item{display:flex;align-items:center;gap:10px;font-size:14px;color:var(--cream-dark);}
.amenity-item i{color:var(--gold);}
.booking-sidebar-card{background:var(--dark-2);border:1px solid var(--border);border-radius:var(--radius);padding:28px;position:sticky;top:90px;}
.price-display{text-align:center;padding-bottom:20px;border-bottom:1px solid var(--border);}
.price-display .from{font-size:12px;color:var(--gray);text-transform:uppercase;letter-spacing:1px;display:block;}
.big-price{font-size:44px;font-weight:800;color:var(--gold);font-family:var(--font-serif);}
.price-display .per{font-size:13px;color:var(--gray);}
.availability-box{padding:10px 14px;border-radius:8px;font-size:13px;display:flex;align-items:center;gap:8px;}
.availability-box.available{background:rgba(0,200,100,0.1);color:#00c864;border:1px solid rgba(0,200,100,0.2);}
.availability-box.unavailable{background:rgba(220,50,50,0.1);color:#ff6b6b;border:1px solid rgba(220,50,50,0.2);}
@media(max-width:900px){.room-detail-grid{grid-template-columns:1fr;}.room-info-cards{grid-template-columns:1fr 1fr;}}
</style>

<script>
const pricePerNight=<?= $room['price_per_night'] ?>;
const taxRate=<?= getSetting('tax_rate',10) ?>/100;
function updateCheckout(){
  const ci=document.getElementById('ci').value;
  if(ci){const co=new Date(ci);co.setDate(co.getDate()+1);document.getElementById('co').min=co.toISOString().split('T')[0];}
  calcTotal();
}
function calcTotal(){
  const ci=document.getElementById('ci').value;
  const co=document.getElementById('co').value;
  if(!ci||!co)return;
  const nights=Math.max(1,Math.round((new Date(co)-new Date(ci))/86400000));
  const subtotal=nights*pricePerNight;
  const total=subtotal*(1+taxRate);
  document.getElementById('nightsLabel').textContent=nights+' <?= t("nights") ?>';
  document.getElementById('nightsCount').textContent='';
  document.getElementById('totalPrice').textContent='<?= getSetting("currency_symbol","$") ?>'+Math.round(total).toLocaleString();
  document.getElementById('nightsCalc').style.display='block';
  checkAvailabilityAjax(<?= $room['id'] ?>,ci,co);
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
