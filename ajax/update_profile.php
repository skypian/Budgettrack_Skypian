<?php
session_start();
require_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$field = $input['field'] ?? null;
$value = $input['value'] ?? null;

if (!$field || $value === null) {
    echo json_encode(['success' => false, 'message' => 'Field and value required']);
    exit;
}

// Validate allowed fields
$allowedFields = ['first_name', 'last_name', 'middle_name', 'employee_id', 'email'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field']);
    exit;
}

// Validate email format if email field
if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check if email already exists (except for current user)
if ($field === 'email') {
    $user = new User();
    $existingUser = $user->getUserByEmail($value);
    if ($existingUser && $existingUser['id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
}

try {
    $user = new User();
    $result = $user->updateUserField($_SESSION['user_id'], $field, $value);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
