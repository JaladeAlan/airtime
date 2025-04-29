<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    $email = $utility_class_call::inputData($data, 'email');

    // Validate input data
    if ($utility_class_call::validate_input($email)) {
          $text = $api_response_class_call::$invalidInfo;
          $errorcode = $api_error_code_class_call::$internalUserWarning;
          $maindata = [];
          $hint = ["Ensure to send valid data to the API fields."];
          $linktosolve = "https://";
          $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
      }

    if (!$email) {
        $text = "Email is required.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Please provide email."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $user = $api_users_table_class_call::getUserByEmail($email , $data);

    if (!$user) {
        $text = $api_response_class_call::$userNotExist;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this email does not exist."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    if ($user['is_emailverified'] == 1) {
        $text = "Email already verified.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Email has already been verified."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }
    // Generate OTPs
    $email_otp = rand(100000, 999999);

    // Store OTPs
    $api_users_table_class_call::storeOtp($email, $email_otp, 'email');

    // Send OTPs
    // Utility_Functions::send_email($email, "Your Email OTP", "Your OTP is: $email_otp");

    $text = "OTP has been sent to your email.";
    $maindata = ['email' => $email];
    $api_status_code_class_call->respondOK($maindata, $text);
    exit;
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method for requesting OTPs."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
