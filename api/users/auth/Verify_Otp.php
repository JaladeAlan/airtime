<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"));

    $email = $utility_class_call::inputData($data, 'email');
    $email_otp = $utility_class_call::inputData($data, 'email_otp');
    $phone = $utility_class_call::inputData($data, 'phone');
    $phone_otp = $utility_class_call::inputData($data, 'phone_otp');

    if ($utility_class_call::validate_input($email) ||  $utility_class_call::validate_input($phone) ||
    $utility_class_call::validate_input($email_otp) ||  $utility_class_call::validate_input($phone_otp)) {
     $text = $api_response_class_call::$invalidInfo;
     $errorcode = $api_error_code_class_call::$internalUserWarning;
     $maindata = [];
     $hint = ["Ensure to send valid data to the API fields."];
     $linktosolve = "https://";
     $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    
    $userByPhone = $api_users_table_class_call::getUserByPhone($phone , $data);
    if (!$userByPhone) {
        $text = $api_response_class_call::$userNotExist;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this phone number does not exist."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    $userByEmail = $api_users_table_class_call::getUserByEmail($email , $data);
    if (!$userByEmail) {
        $text = $api_response_class_call::$userNotExist;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this email does not exist."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    if ($userByPhone['id'] !== $userByEmail['id']) {
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Details do not match"];
        $api_status_code_class_call->respondBadRequest([], "Email and phone do not belong to the same user.", ["Check the phone and email combination."], "https://", $api_error_code_class_call::$internalUserWarning);
        exit;
    }
    $both_verified = $api_users_table_class_call::verifyEmailAndPhoneOtp($email, $email_otp, $phone, $phone_otp);

    if ($both_verified) {
        $api_users_table_class_call::verifyEmailAndPhone($email, $phone);

        echo json_encode([
            'status' => true,
            'message' => ''
        ]);
        
    $text = "Email and phone verified successfully.";
    $maindata = ['phone' => $phone, 'email' => $email];
    $api_status_code_class_call->respondOK($maindata, $text);
    exit;
    } else {
        $text = "OTP verification failed.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = [];
        if (!$both_verified) $hint[] = "Invalid or expired email/ phone OTP.";
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized([], $text, $hint, $linktosolve, $errorcode);
    }
    exit;

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method for OTP verification."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
