<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();

$db = getDB();
$adminPage = 'room_grid';
$adminPageTitle = 'admin_room_grid';

// Fetch all room types
$rooms = $db->query("SELECT * FROM rooms WHERE is_available=1 ORDER BY sort_order")->fetchAll();

// Fetch all physical room numbers
$roomNumbers = $db->query("SELECT * FROM room_numbers ORDER BY room_id, room_number")->fetchAll();

// Group room numbers by room type
$groupedNumbers = [];
foreach($roomNumbers as $rn) {
    if(!isset($groupedNumbers[$rn['room_id']])) $groupedNumbers[$rn['room_id']] = [];
    $groupedNumbers[$rn['room_id']][] = $rn;
}

// Fetch active bookings for *today* that have an assigned room number
// A booking is active today if check_in <= today AND check_out > today AND status IN ('confirmed', 'checked_in')
$today = date('Y-m-d');
$bookingsStmt = $db->prepare("SELECT * FROM bookings WHERE status IN ('confirmed','checked_in') AND check_in <= ? AND check_out > ? AND room_number_id IS NOT NULL");
$bookingsStmt->execute([$today, $today]);
$activeBookings = $bookingsStmt->fetchAll();

// Map active bookings by room_number_id
$bookingMap = [];
foreach($activeBookings as $b) {
    $bookingMap[$b['room_number_id']] = $b; // Assumes 1 active booking per room per day max
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.room-grid-group { margin-bottom: 30px; }
.room-grid-title { font-size: 18px; font-weight: 600; color: var(--gold); margin-bottom: 12px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px; }
.room-squares { display: flex; flex-wrap: wrap; gap: 15px; }

.room-square {
    width: 90px;
    height: 90px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: bold;
    font-size: 20px;
    position: relative;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
    text-decoration: none;
}
.room-square:hover { transform: translateY(-3px); }

.room-square small {
    font-size: 11px;
    font-weight: normal;
    margin-top: 4px;
    opacity: 0.9;
    background: rgba(0,0,0,0.2);
    padding: 2px 6px;
    border-radius: 4px;
}

/* Status Colors */
.status-available { background: linear-gradient(135deg, #10b981 0%, #059669 100%); } /* Green */
.status-occupied { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); } /* Red */
.status-reserved { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); } /* Orange/Yellow */
.status-maintenance { background: linear-gradient(135deg, #4b5563 0%, #374151 100%); } /* Dark Gray */

/* Color Guide */
.color-guide {
    display: flex;
    gap: 20px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    background: var(--admin-sidebar);
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--admin-border);
}
.guide-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--cream);
}
.guide-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}
</style>

<div class="admin-header">
  <h1 class="admin-title">
    <i class="fas fa-th"></i> <?= t('admin_room_grid') ?>
  </h1>
  <div style="font-size:16px;color:var(--gray);"><i class="far fa-calendar-alt"></i> <?= t('admin_today_date') ?>: <?= formatDate($today) ?></div>
</div>

<div class="color-guide">
    <div style="width:100%;font-weight:bold;margin-bottom:5px;font-size:15px;color:var(--gold);"><i class="fas fa-info-circle"></i> <?= t('admin_color_guide') ?></div>
    <div class="guide-item"><div class="guide-box status-available"></div> <?= t('admin_room_status_available') ?></div>
    <div class="guide-item"><div class="guide-box status-reserved"></div> <?= t('admin_status_reserved') ?></div>
    <div class="guide-item"><div class="guide-box status-occupied"></div> <?= t('admin_room_status_occupied') ?></div>
    <div class="guide-item"><div class="guide-box status-maintenance"></div> <?= t('admin_room_status_maintenance') ?></div>
</div>

<div class="card">
    <div class="card-body">
        <?php if(empty($rooms)): ?>
            <p style="color:var(--gray);text-align:center;padding:40px;"><?= t('admin_no_room_numbers') ?></p>
        <?php else: ?>
            
            <?php foreach($rooms as $room): ?>
                <?php if(!isset($groupedNumbers[$room['id']]) || empty($groupedNumbers[$room['id']])) continue; ?>
                
                <div class="room-grid-group">
                    <div class="room-grid-title">
                        <?= e($room['name_'.getLang()] ?? $room['name_en']) ?>
                    </div>
                    <div class="room-squares">
                        <?php foreach($groupedNumbers[$room['id']] as $rn): ?>
                            <?php
                                // Determine effective status color and link
                                $link = '#';
                                $bookingId = null;
                                
                                if ($rn['status'] === 'maintenance') {
                                    $cssClass = 'status-maintenance';
                                    $statusLabel = t('admin_room_status_maintenance');
                                    $link = "room_numbers.php?room_id=".$room['id']."&edit=".$rn['id']; // Link to edit room number
                                } else {
                                    // Check bookings
                                    if(isset($bookingMap[$rn['id']])) {
                                        $b = $bookingMap[$rn['id']];
                                        $bookingId = $b['id'];
                                        $link = "bookings.php?edit=".$bookingId; // Link to edit booking
                                        
                                        if($b['status'] === 'checked_in') {
                                            $cssClass = 'status-occupied';
                                            $statusLabel = t('admin_room_status_occupied');
                                        } else {
                                            $cssClass = 'status-reserved';
                                            $statusLabel = t('admin_status_reserved');
                                        }
                                    } else {
                                        $cssClass = 'status-available';
                                        $statusLabel = t('admin_room_status_available');
                                        $link = "room_numbers.php?room_id=".$room['id']."&edit=".$rn['id']; // Link to edit room number
                                    }
                                }
                            ?>
                            
                            <a href="<?= $link ?>" class="room-square <?= $cssClass ?>" title="<?= e(str_replace('<br>',' ', $statusLabel)) ?>">
                                <?= e($rn['room_number']) ?>
                                <?php if($bookingId): ?>
                                    <small>#<?= e(substr($bookingMap[$rn['id']]['guest_name'], 0, 10)) ?></small>
                                <?php else: ?>
                                    <small><?= e($statusLabel) ?></small>
                                <?php endif; ?>
                            </a>
                            
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
        <?php endif; ?>
    </div>
</div>

</div></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" crossorigin="anonymous" defer></script>
</body></html>
