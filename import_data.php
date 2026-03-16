<?php
require_once 'config.php';

// Turn on errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting Data Migration from www.albustansuites.net...<br>\n";

$baseUrl = "http://www.albustansuites.net/";

$roomsData = [
    [
        'url' => 'r_rsuites.html',
        'name_en' => 'Royal Suite',
        'name_ar' => 'الجناح الملكي',
        'price' => 300,
        'capacity' => 6
    ],
    [
        'url' => 'r_prsuites.html',
        'name_en' => 'Presidential Suite',
        'name_ar' => 'الجناح الرئاسي',
        'price' => 250,
        'capacity' => 4
    ],
    [
        'url' => 'r_exsuites.html',
        'name_en' => 'Executive Suite',
        'name_ar' => 'الجناح التنفيذي',
        'price' => 180,
        'capacity' => 4
    ],
    [
        'url' => 'r_jsuites.html',
        'name_en' => 'Junior Suite',
        'name_ar' => 'جناح جونيور',
        'price' => 120,
        'capacity' => 2
    ],
    [
        'url' => 'r_frvrooms.html',
        'name_en' => 'Deluxe Room Front View',
        'name_ar' => 'غرفة ديلوكس إطلالة أمامية',
        'price' => 90,
        'capacity' => 2
    ],
    [
        'url' => 'r_bkvrooms.html',
        'name_en' => 'Deluxe Room Back View',
        'name_ar' => 'غرفة ديلوكس إطلالة خلفية',
        'price' => 80,
        'capacity' => 2
    ],
    [
        'url' => 'r_srpre.html',
        'name_en' => 'Single Room Premium',
        'name_ar' => 'غرفة مفردة بريميوم',
        'price' => 60,
        'capacity' => 1
    ],
    [
        'url' => 'r_sreco.html',
        'name_en' => 'Single Room Economic',
        'name_ar' => 'غرفة مفردة اقتصادية',
        'price' => 50,
        'capacity' => 1
    ]
];

$uploadDir = __DIR__ . '/uploads/rooms/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Ensure the db connection is ready
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Prepare insert statement
$stmt = $pdo->prepare("INSERT INTO rooms (
    name_en, name_ar, slug, description_en, description_ar, 
    short_desc_en, short_desc_ar, price_per_night, currency, size_sqm, 
    capacity_adults, capacity_children, view_type_en, view_type_ar,
    is_available, is_featured, category_id, sort_order, main_image
) VALUES (
    ?, ?, ?, ?, ?, 
    ?, ?, ?, 'USD', 50, 
    ?, 0, 'City View', 'إطلالة المدينة',
    1, 1, 1, 0, ?
)");

foreach ($roomsData as $index => $room) {
    echo "Processing {$room['name_en']}...<br>\n";
    $html = @file_get_contents($baseUrl . $room['url']);
    
    if (!$html) {
        echo "Failed to fetch {$room['url']} - skipping.<br>\n";
        continue;
    }
    
    // Using DOMDocument to parse
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    
    // Find images
    $images = $xpath->query('//img');
    $mainImageName = '';
    
    // We try to find the largest/main image or just the first content image
    // Usually on these old sites, it's inside some div id="content" or class="flexslider"
    foreach ($images as $img) {
        $src = $img->getAttribute('src');
        if (strpos($src, 'images/') !== false && strpos($src, 'logo') === false && strpos($src, 'bg') === false) {
            // Found a good image candidate
            $imgUrl = $baseUrl . ltrim($src, '/');
            $imgData = @file_get_contents($imgUrl);
            if ($imgData) {
                $ext = pathinfo($src, PATHINFO_EXTENSION);
                if (!$ext) $ext = 'jpg';
                $mainImageName = 'room_' . time() . '_' . rand(100,999) . '.' . $ext;
                file_put_contents($uploadDir . $mainImageName, $imgData);
                echo "--> Downloaded image: $mainImageName<br>\n";
                break; // Just grab the first good image as main_image
            }
        }
    }
    
    // Default image if none found
    if (!$mainImageName) {
        $mainImageName = 'default-room.jpg';
    }
    
    // Find text content
    // Usually in <p> tags inside the main container
    $paragraphs = $xpath->query('//p');
    $desc = '';
    foreach ($paragraphs as $p) {
        $text = trim($p->textContent);
        if (strlen($text) > 30) {
            $desc .= $text . "\n\n";
        }
    }
    
    if (empty($desc)) {
        $desc = "A beautiful and luxurious room in Al Bustan Suites.";
    }
    
    $shortDesc = substr($desc, 0, 100) . '...';
    
    // Generate slug
    $slug = strtolower(str_replace(' ', '-', $room['name_en'])) . '-' . rand(100,999);
    
    // Insert into DB
    try {
        $stmt->execute([
            $room['name_en'],
            $room['name_ar'],
            $slug,
            $desc, // English description
            $desc, // Put same for Arabic for now or we can Google translate later
            $shortDesc,
            $shortDesc,
            $room['price'],
            $room['capacity'],
            $mainImageName
        ]);
        echo "<span style='color:green'>Inserted successfully!</span><br><br>\n";
    } catch (Exception $e) {
        echo "<span style='color:red'>DB Error: " . $e->getMessage() . "</span><br><br>\n";
    }
}

echo "Migration Complete!<br>\n";
?>
