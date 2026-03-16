<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$admin = $_SESSION[ADMIN_SESSION_NAME] ?? null;
unset($_SESSION[ADMIN_SESSION_NAME]);

if($admin) {
    session_regenerate_id(true);
    if(isset($_COOKIE['admin_remember'])) {
        try {
            $db = getDB();
            $db->prepare("UPDATE admins SET remember_token=NULL WHERE id=?")->execute([$admin['id']]);
        } catch(Exception $e){}
        setcookie('admin_remember', '', time() - 3600, '/');
    }
}

header('Location: login.php'); exit;
