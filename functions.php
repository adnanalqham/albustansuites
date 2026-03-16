<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

// =============================================
// LANGUAGE HELPERS
// =============================================
function getLang(): string {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar','en'])) {
        $_SESSION['lang'] = $_GET['lang'];
        setcookie('lang', $_GET['lang'], time() + (86400 * 365), '/');
    }
    if (isset($_SESSION['lang'])) return $_SESSION['lang'];
    if (isset($_COOKIE['lang']))  return $_COOKIE['lang'];
    return DEFAULT_LANG;
}

function t(string $key, array $vars = []): string {
    static $translations = null;
    if ($translations === null) {
        $lang = getLang();
        $file = __DIR__ . "/lang/{$lang}.php";
        $translations = file_exists($file) ? require $file : [];
    }
    $value = $translations[$key] ?? $key;
    foreach ($vars as $k => $v) {
        $value = str_replace("{{$k}}", $v, $value);
    }
    return $value;
}

function isRTL(): bool {
    return getLang() === 'ar';
}

function langDir(): string {
    return isRTL() ? 'rtl' : 'ltr';
}

function langClass(): string {
    return isRTL() ? 'rtl' : 'ltr';
}

function altLang(): string {
    return getLang() === 'ar' ? 'en' : 'ar';
}

function altLangLabel(): string {
    return getLang() === 'ar' ? 'EN' : 'عربي';
}

// =============================================
// SETTINGS
// =============================================
function getSetting(string $key, string $default = ''): string {
    static $settings = null;
    if ($settings === null) {
        try {
            $stmt = getDB()->query("SELECT `key`, `value` FROM settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

function getHotelName(): string {
    $lang = getLang();
    return $lang === 'ar' ? getSetting('hotel_name_ar', SITE_NAME_AR) : getSetting('hotel_name_en', SITE_NAME_EN);
}

// =============================================
// AUTH HELPERS
// =============================================
function isUserLoggedIn(): bool {
    return isset($_SESSION[USER_SESSION_NAME]);
}

function getUser(): ?array {
    if (!isUserLoggedIn()) return null;
    return $_SESSION[USER_SESSION_NAME];
}

function isAdminLoggedIn(): bool {
    if (isset($_SESSION[ADMIN_SESSION_NAME])) {
        return true;
    }
    
    // Check remember me cookie
    if (isset($_COOKIE['admin_remember'])) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM admins WHERE remember_token = ? LIMIT 1");
            $stmt->execute([$_COOKIE['admin_remember']]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $_SESSION[ADMIN_SESSION_NAME] = [
                    'id'    => $admin['id'],
                    'name'  => $admin['name'],
                    'email' => $admin['email'],
                    'role'  => $admin['role']
                ];
                $db->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")->execute([$admin['id']]);
                return true;
            }
        } catch (Exception $e) {
            // Ignore DB errors here, just return false
        }
    }
    
    return false;
}

function requireUser(): void {
    if (!isUserLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

// =============================================
// FLASH MESSAGES
// =============================================
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $icons = ['success' => '✓', 'error' => '✕', 'info' => 'ℹ', 'warning' => '⚠'];
    $icon = $icons[$flash['type']] ?? '';
    return "<div class=\"flash flash-{$flash['type']}\"><span class=\"flash-icon\">{$icon}</span> {$flash['message']}</div>";
}

// =============================================
// BOOKING HELPERS
// =============================================
function generateBookingRef(): string {
    return 'AB-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Y');
}

function checkRoomAvailability(int $roomId, string $checkIn, string $checkOut): bool {
    $db = getDB();
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE room_id = ? 
            AND status NOT IN ('cancelled','checked_out')
            AND (check_in < ? AND check_out > ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$roomId, $checkOut, $checkIn]);
    return (int)$stmt->fetchColumn() === 0;
}

function calculateNights(string $checkIn, string $checkOut): int {
    $ci = new DateTime($checkIn);
    $co = new DateTime($checkOut);
    $diff = $ci->diff($co);
    return max(1, $diff->days);
}

// =============================================
// PAGINATION
// =============================================
function paginate(int $total, int $perPage, int $page): array {
    $totalPages = (int)ceil($total / $perPage);
    $page = max(1, min($page, $totalPages ?: 1));
    $offset = ($page - 1) * $perPage;
    return ['total' => $total, 'per_page' => $perPage, 'page' => $page,
            'total_pages' => $totalPages, 'offset' => $offset];
}

// =============================================
// SECURITY
// =============================================
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize(string $str): string {
    return trim(strip_tags($str));
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// =============================================
// UPLOAD
// =============================================
function uploadImage(array $file, string $subfolder = ''): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($file['type'], $allowed)) return false;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($ext);
    $dir = UPLOAD_DIR . ($subfolder ? $subfolder . '/' : '');
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $path = $dir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $path)) return false;
    return 'images/uploads/' . ($subfolder ? $subfolder . '/' : '') . $filename;
}

// =============================================
// FORMATTING
// =============================================
function formatPrice(float $amount, string $currency = 'USD'): string {
    $symbols = ['USD' => '$', 'YER' => 'ر.ي', 'SAR' => 'ر.س', 'AED' => 'د.إ'];
    $symbol = $symbols[$currency] ?? $currency . ' ';
    if (getLang() === 'ar') {
        return number_format($amount, 0) . ' ' . ($symbols[$currency] ?? $currency);
    }
    return $symbol . number_format($amount, 0);
}

function formatDate(string $date): string {
    return date('d M Y', strtotime($date));
}

function getStatusBadge(string $status): string {
    $classes = [
        'pending'    => 'badge-warning',
        'confirmed'  => 'badge-success',
        'checked_in' => 'badge-info',
        'checked_out'=> 'badge-secondary',
        'cancelled'  => 'badge-danger',
    ];
    $labels = [
        'pending'    => getLang()==='ar' ? 'في الانتظار' : 'Pending',
        'confirmed'  => getLang()==='ar' ? 'مؤكد'        : 'Confirmed',
        'checked_in' => getLang()==='ar' ? 'تسجيل دخول'  : 'Checked In',
        'checked_out'=> getLang()==='ar' ? 'تسجيل خروج'  : 'Checked Out',
        'cancelled'  => getLang()==='ar' ? 'ملغي'        : 'Cancelled',
    ];
    $class = $classes[$status] ?? 'badge-secondary';
    $label = $labels[$status] ?? $status;
    return "<span class=\"badge {$class}\">{$label}</span>";
}

// =============================================
// JSON helpers for rooms amenities
// =============================================
function decodeJson(?string $json): array {
    if (!$json) return [];
    // Try JSON first (for backwards compatibility)
    $decoded = json_decode($json, true);
    if (is_array($decoded)) return $decoded;
    // Fall back to comma-separated string
    return array_map('trim', explode(',', $json));
}
