<?php
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST'); // Allow GET and POST requests
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header
header('Access-Control-Max-Age: 3600'); // Cache preflight response for 1 hour
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $admin_id = $api_users_table_class_call::checkIfIsAdmin($user_pubkey);

    // Unauthorized user
    if ( !$admin_id ){
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Only admins can have access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Get the user ID (replace with actual authentication logic)
    $userid = " "; // Replace with your authentication logic to get the user ID
    if (isset($data->userid)) {
        $userid = $utility_class_call::escape($data->userid);
    }

    $message = isset($data->message) ? $data->message : '';

    if (!empty($message)) {
        $result = Utility_Functions::addNotification($userid, $message);

        if ($result) {
            $maindata = [
                'status' => 'success',
            ];
            
            $text = $api_response_class_call::$sentNotification;
            $api_status_code_class_call->respondOK($maindata, $text);
        } else {
            $maindata= [
                'status' => 'error',
            ];
            $text = $api_response_class_call::$sentNotificationFailed;
            $api_status_code_class_call->respondOK($maindata, $text);
        }
    } else {
        $maindata = [];
        $text = $api_response_class_call::$invalidDataSent;
        $api_status_code_class_call->respondOK($maindata, $text);
    }
} else {
    // Handle invalid HTTP method
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for sending notification."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
