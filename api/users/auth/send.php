<?php
header('Content-Type: application/json');

use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php'; // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $fullname = $data['fullname'] ?? '';
    $email = $data['email'] ?? '';
    $username = $data['username'] ?? '';
    $user_id = $data['user_id'] ?? '';
    $imagePaths = $data['imagePaths'] ?? [];

    if (!$fullname || !$email || !$username || !$user_id || empty($imagePaths)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }

    Utility_Functions::notifyTelegramKYC($fullname, $email, $username, $imagePaths, $user_id);
    $kyc = $api_users_table_class_call::submitKYC($username, $imagePaths);

    echo json_encode(['status' => 'success', 'message' => 'KYC notification sent to Telegram.']);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
?>
