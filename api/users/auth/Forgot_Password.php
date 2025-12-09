<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

require_once '../../../config/bootstrap_file.php';
use Config\Utility_Functions;


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $api_status_code_class_call->respondMethodNotAllowed([], $text, ["Use POST method."], "https://", $errorcode);
    exit;
}

$request_body = file_get_contents('php://input');
$data = json_decode($request_body);

$email = isset($data->email) ? $utility_class_call::escape(trim($data->email)) : "";

// Validate email format
if (!$utility_class_call::validateEmail($email)) {
    $text = $api_response_class_call::$invalidEmail;
    $errorcode = $api_error_code_class_call::$internalUserWarning;
    $api_status_code_class_call->respondBadRequest([], $text, ["Send a valid email address."], "https://", $errorcode);
    exit;
}

// Check if user exists
$user = $api_users_table_class_call::getUserByEmail($email);
if (empty($user)) {
    $text = $api_response_class_call::$userNotExist;
    $errorcode = $api_error_code_class_call::$internalUserWarning;
    $api_status_code_class_call->respondBadRequest([], $text, ["Email not found in database."], "https://", $errorcode);
    exit;
}

// Generate reset code
$resetCode = rand(100000, 999999);
$expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Store reset code
$api_users_table_class_call::storeResetCode($resetCode, $expiry, $user['email']);

$subject = "Password Reset Request";
$message = "";
$message .= "You requested a password reset.<br><br>";
$message .= "Your password reset code is:<br><strong>{$resetCode}</strong><br><br>";
$message .= "This code expires in 5 minutes.<br><br>";
$message .= "If you did not request this, please ignore this email.";

// Send email
$emailSent = Utility_Functions::send_test_email($user['email'], $subject, $message);

if ($emailSent) {
    $text = $api_response_class_call::$resetCodeEmailSent;
    $api_status_code_class_call->respondOK([], $text);
    exit;
}

// Email failed
$text = "Failed to send password reset email. Try again later.";
$errorcode = $api_error_code_class_call::$internalServerError;
$api_status_code_class_call->respondInternalError([], $text, [], "", $errorcode);
exit;

?>
