<?php
// Get unread notifications count
function getUnreadNotifications(): int {
    try {
        return (int)db()->query("SELECT COUNT(*) FROM notification WHERE role_target='admin' AND is_read=0")->fetchColumn();
    } catch (Exception $e) {}
    return 0;
}

// Get ALL notifications
function getAllNotifications(): array {
    try {
        return db()->query("SELECT * FROM notification WHERE role_target='admin' ORDER BY is_read ASC, created_at DESC LIMIT 50")->fetchAll();
    } catch (Exception $e) {}
    return [];
}
?>