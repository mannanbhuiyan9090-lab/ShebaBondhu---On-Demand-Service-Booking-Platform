<?php
require_once __DIR__ . '/includes/config.php';

$creds = [
    'admin@handygo.com' => 'admin123',
    'rahela@example.com' => 'test123',
    // Add other test123 providers/customers...
];

foreach ($creds as $email => $plain) {
    $hash = password_hash($plain, PASSWORD_DEFAULT);
    db()->prepare("UPDATE users SET password = ? WHERE email = ?")
       ->execute([$hash, $email]);
    echo "✅ Updated $email → $plain (hash: " . substr($hash, 0, 20) . "...)<br>";
}

echo '<h2>All test passwords reset. Now login works!</h2>
<p><a href="login.php">Test Login</a></p>';
?>

