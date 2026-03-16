<?php
ob_start();
// =============================================
// Al Bustan Suites - Configuration
// =============================================

// ---- Database Settings ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'albustan_db');
define('DB_USER', 'root');
define('DB_PASS', '771603365');
define('DB_CHARSET', 'utf8mb4');

// ---- Site Settings ----
define('SITE_URL', 'http://localhost/albustansuites');
define('SITE_NAME_EN', 'Al Bustan Luxurious Suites');
define('SITE_NAME_AR', 'البستان للأجنحة الفاخرة');
define('SITE_VERSION', '1.0.0');

// ---- Admin Settings ----
define('ADMIN_EMAIL', 'admin@albustan.com');
define('ADMIN_SESSION_NAME', 'albustan_admin');
define('USER_SESSION_NAME', 'albustan_user');

// ---- Upload Settings ----
define('UPLOAD_DIR', __DIR__ . '/images/uploads/');
define('UPLOAD_URL', SITE_URL . '/images/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ---- Default Language ----
define('DEFAULT_LANG', 'ar'); // 'ar' or 'en'

// ---- Timezone ----
date_default_timezone_set('Asia/Riyadh');

// ---- Error Reporting (set to 0 in production) ----
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---- Session Start ----
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
