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
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract email and reset code from the request data
    $email = isset($data->email) ? $utility_class_call::escape($data->email) : "";
    $resetCode = isset($data->reset_code) ? intval($data->reset_code) : 0;

    // Validate input data (ensure a valid reset code)
    if (!is_numeric($resetCode) || strlen($resetCode) != 4) {
        // Return an error response if the reset code is invalid
        $text = $api_response_class_call::$invalidResetData;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send a valid 4-digit reset code."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        // Exit the script after sending the response
        exit;
    }

   

    // Verify the provided reset code against the stored reset code
    $storedResetCode = $api_users_table_class_call::getStoredResetCode($email);

    if ($resetCode !== $storedResetCode) {
        // Return an error response if the reset code does not match
        $text = $api_response_class_call::$invalidResetData;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["The provided reset code is incorrect."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        // Exit the script after sending the response
        exit;
    }

    // Respond with a success message
    $text = $api_response_class_call::$resetCodeVerified;
    $api_status_code_class_call->respondOK([], $text);
} else {
    // Handle cases where the request method is not POST
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for the reset code verification request."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
