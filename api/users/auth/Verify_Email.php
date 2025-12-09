<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Stop preflight requests early
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method for requesting OTPs."];
    $linktosolve = "https://";

    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = $utility_class_call::inputData($data, 'email');

// Validate input
if ($utility_class_call::validate_input($email)) {
    $text = $api_response_class_call::$invalidInfo;
    $errorcode = $api_error_code_class_call::$internalUserWarning;
    $hint = ["Ensure to send valid data to the API fields."];
    $linktosolve = "https://";

    $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
    exit;
}

if (!$email) {
    $text = "Email is required.";
    $errorcode = $api_error_code_class_call::$internalUserWarning;
    $hint = ["Please provide email."];
    $linktosolve = "https://";

    $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
    exit;
}

// Check if user exists
$user = $api_users_table_class_call::getUserByEmail($email, $data);

if (!$user) {
    $text = $api_response_class_call::$userNotExist;
    $errorcode = $api_error_code_class_call::$internalUserWarning;
    $hint = ["A user with this email does not exist."];
    $linktosolve = "https://";

    $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
    exit;
}

// Check if email already verified
if ($user['is_emailverified'] == 1) {
    $text = "Email already verified.";
    $errorcode = $api_error_code_class_call::$internalUserWarning;
    $hint = ["Email has already been verified."];
    $linktosolve = "https://";

    $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
    exit;
}

// Generate OTP
$email_otp = rand(100000, 999999);

// Store OTP
$api_users_table_class_call::storeOtp($email, $email_otp, 'email');

// Send OTP to email (PHPMailer)
$msg = "<p>Your OTP is: <strong>{$email_otp}</strong></p>";
$utility_class_call::send_email($email, "Your Email OTP", $msg);

// Success response
$text = "OTP has been sent to your email.";
$maindata = ['email' => $email];
$api_status_code_class_call->respondOK($maindata, $text);
exit;
?>
