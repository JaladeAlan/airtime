<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate user token
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;

    // Get user data from token
    $user = $api_users_table_class_call::checkIfIsUser($user_pubkey);

    if (!$user) {
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $hint = ["Please log in to continue."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"));
    $phone = $utility_class_call::inputData($data, 'phone');

    if ($utility_class_call::validate_input($phone)) {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    if (!$phone) {
        $text = "Phone number is required.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Please provide phone number."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $user_by_phone = $api_users_table_class_call::getUserByPhone($phone, $data);
    if (!$user_by_phone) {
        $text = $api_response_class_call::$userNotExist;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["A user with this phone number does not exist."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    if ($user_by_phone['is_phonenumberverified'] == 1) {
        $text = "Phone number already verified.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["You do not need to request another phone OTP."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    // Generate and store OTP
    $phone_otp = rand(100000, 999999);
    $api_users_table_class_call::storeOtp($phone, $phone_otp, 'phone');

    // Send OTP
    // Utility_Functions::send_sms($phone, "Your phone OTP is: $phone_otp");

    $text = "OTP has been sent to your phone.";
    $maindata = ['phone' => $phone];
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
