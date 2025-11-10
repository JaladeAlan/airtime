<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// If the browser sends a preflight OPTIONS request, stop here and return OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
     // Validate API token
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $user_id = $api_users_table_class_call::checkIfIsUser($user_pubkey);

         // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    $email = $utility_class_call::inputData($data, 'email');
    $name = $utility_class_call::inputData($data, 'sender_name');
    $phone = $utility_class_call::inputData($data, 'phone');

        // Validate input data
    if ($utility_class_call::validate_input($email) ||  $utility_class_call::validate_input($phone) ||
       $utility_class_call::validate_input($name) ) {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    //validate the email
    if(!$utility_class_call::validateEmail($email)){
        $text = $api_response_class_call::$invalidEmail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields.","pass in valid email", "all fields should not be empty"];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
    }
    // Check if user with the same email exists in the database
    if ($api_users_table_class_call::getSenderBykey($user_pubkey, $email, $phone)) {
        $text = "Sender profile with email or phone number already exists";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this phone or email already exists."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    // Insert user data into the database
    $user_id = $api_users_table_class_call::insertSender($email, $name, $phone, $user_pubkey);

    if ($user_id) {
        // Respond with a success message
        $maindata = [];
        $text = 'Sender Profile created successfully';
        $api_status_code_class_call->respondOK($maindata, $text);
    } else {
        $text = 'Sender Profile could not be created';
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Registration of Sender Profile failed. Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for sender profile registration."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>