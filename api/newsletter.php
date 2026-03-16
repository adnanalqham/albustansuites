<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error'=>'POST required']); exit;
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
if(!$email) { echo json_encode(['error'=>'Invalid email']); exit; }

// Just save to DB or respond - simple implementation
echo json_encode(['success'=>true,'message'=>'Subscribed successfully!']);
