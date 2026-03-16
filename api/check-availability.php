<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$roomId   = (int)($_GET['room_id']   ?? 0);
$checkIn  = sanitize($_GET['check_in']  ?? '');
$checkOut = sanitize($_GET['check_out'] ?? '');

if(!$roomId || !$checkIn || !$checkOut) {
    echo json_encode(['error' => 'Missing parameters', 'available' => false]);
    exit;
}

if(strtotime($checkIn) >= strtotime($checkOut)) {
    echo json_encode(['error' => 'Invalid dates', 'available' => false]);
    exit;
}

$available = checkRoomAvailability($roomId, $checkIn, $checkOut);
$nights    = calculateNights($checkIn, $checkOut);

$stmt = getDB()->prepare("SELECT price_per_night, currency FROM rooms WHERE id=?");
$stmt->execute([$roomId]);
$room = $stmt->fetch();

$total = $room ? $nights * $room['price_per_night'] * (1 + (float)getSetting('tax_rate','10')/100) : 0;

echo json_encode([
    'available'    => $available,
    'nights'       => $nights,
    'price_per_night' => $room['price_per_night'] ?? 0,
    'total_price'  => round($total),
    'currency'     => $room['currency'] ?? 'USD',
]);
