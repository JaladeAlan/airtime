<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;

    // Get user data using public key
    $user_data = $api_users_table_class_call::checkIfIsUser($user_pubkey);

    // Unauthorized user
    if (!$user_data) {
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Please log in to access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $maindata = $user_data;
    $text = $api_response_class_call::$detailsFetched;
    $api_status_code_class_call->respondOK($maindata, $text);

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the GET method for fetch notifications."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
