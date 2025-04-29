<?php
header('Content-Type: application/json');

use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $user_id = $api_users_table_class_call::checkIfIsUser($user_pubkey);
         // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract data
    $pin= $utility_class_call::inputData($data, 'pin');
    $confirm_pin = $utility_class_call::inputData($data, 'confirm_pin');
    $password = $utility_class_call::inputData($data, 'password');

        // Validate input data
    if ($utility_class_call::validate_input($pin) || $utility_class_call::validate_input($password) || 
       $utility_class_call::validate_input($confirm_pin) ) {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    if($pin != $confirm_pin){
        $text = $api_response_class_call::$confirmPin;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
       }

    // Verify password
    if (!password_verify($password, $user_id["user_password"])) {
        $text = $api_response_class_call::$passwordIncorrect;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure data sent is valid and user data is in database.","Invalid password"];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
    }
 
    // Insert user data into the database
    $user= $api_users_table_class_call::updatePin($user_pubkey, $pin);

    if ($user) {
        // Respond with a success message
        $maindata = [];
        $text = $api_response_class_call::$setPin;
        $api_status_code_class_call->respondOK($maindata, $text);
    } else {
        $text = $api_response_class_call::$registrationFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Registration failed. Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for creating pin."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>