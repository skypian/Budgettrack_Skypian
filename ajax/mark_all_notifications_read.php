<?php
session_start();
require_once __DIR__ . '/../classes/Notification.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $notification = new Notification();
    $result = $notification->markAllAsRead($_SESSION['user_id']);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
