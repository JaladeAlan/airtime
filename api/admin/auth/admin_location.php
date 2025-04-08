<?php
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST'); // Allow GET and POST requests
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header
header('Access-Control-Max-Age: 3600'); // Cache preflight response for 1 hour
header('Content-Type: application/json');
require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $user_id = $api_users_table_class_call::checkIfIsAdmin($user_pubkey);
    // Unauthorized user
    if ( !$user_id ){
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Please log in to access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    $latitude = isset($data->latitude) ? $data->latitude : "";
    $longitude = isset($data->longitude) ? $data->longitude : "";

    // Validate input
    if ($utility_class_call::validate_input($latitude) || $utility_class_call::validate_input($longitude)) {
        $text = $api_response_class_call::$invalidDataSent;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields: latitude, and longitude."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Insert admin location into the database
    $result = $api_users_table_class_call::updateUserLocation($user_pubkey, $latitude, $longitude);

    if ($result) {
        // Location inserted successfully
        $text = $api_response_class_call::$locationInserted;
        $api_status_code_class_call->respondOK([], $text);
    } else {
        // Error inserting location
        $text = $api_response_class_call::$locationInsertFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Error inserting location. Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for inserting company location."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
