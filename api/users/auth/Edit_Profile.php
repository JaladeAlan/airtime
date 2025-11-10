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
require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request body
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $user_id = $api_users_table_class_call::checkIfIsUser($user_pubkey);

    // Unauthorized user
    if (!$user_id) {
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Please log in to access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract data from the request and validate input
     $new_name = "";
    if (isset($data->new_name)) {
        $new_name = $utility_class_call::escape($data->new_name);
    }
   $new_email = "";
    if (isset($data->new_email)) {
        $new_email = $utility_class_call::escape($data->new_email);
    }
     $new_department = "";
    if (isset($data->new_department)) {
        $new_department = $utility_class_call::escape($data->new_department);
    }
    $new_description = "";
    if (isset($data->new_description)) {
        $new_description = $utility_class_call::escape($data->new_description);
    }
    if (!$utility_class_call::validateEmail($new_email) || $utility_class_call::validate_input($new_name)
    || $utility_class_call::validate_input($new_department)|| $utility_class_call::validate_input($new_description)) {
        $text = $api_response_class_call::$invalidDataSent;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
    }

    // Update user profile in the database
    $result = $utility_class_call::updateUserProfile($new_name, $new_email, $new_department, $new_description, $user_pubkey);

    if ($result) {
        // Profile updated successfully
        $text = $api_response_class_call::$profileUpdated;
        $api_status_code_class_call->respondOK([], $text);
    } else {
        // Error updating profile
        $text = $api_response_class_call::$profileUpdateFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Error updating profile. Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for updating your profile."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
