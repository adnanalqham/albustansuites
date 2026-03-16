<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

try {
    $db = getDB();
    
    // Check if super_admin exists
    $stmt = $db->query("SELECT * FROM admins WHERE role = 'super_admin' LIMIT 1");
    $admin = $stmt->fetch();
    
    $newEmail = 'admin@albustan.com';
    $newPassword = 'admin'; // simple password
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    
    if ($admin) {
        // Update existing
        $db->prepare("UPDATE admins SET email = ?, password = ? WHERE id = ?")
           ->execute([$newEmail, $hash, $admin['id']]);
        echo "<h2>Password Reset Successful!</h2>";
        echo "<p>Updated existing Super Admin.</p>";
    } else {
        // Create new
        $db->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)")
           ->execute(['Super Admin', $newEmail, $hash, 'super_admin']);
        echo "<h2>Password Reset Successful!</h2>";
        echo "<p>Created new Super Admin.</p>";
    }
    
    echo "<p><strong>Email:</strong> {$newEmail}</p>";
    echo "<p><strong>Password:</strong> {$newPassword}</p>";
    echo '<p><a href="admin/login.php">Go to Login</a></p>';
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
