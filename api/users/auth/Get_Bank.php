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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {
        $banks = $api_users_table_class_call::getAllBanks(); 

        if (!$banks || count($banks) === 0) {
            $text = "No banks found.";
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $hint = ["Check if the bank table is populated."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondOK([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // Return banks
        $text = "Banks retrieved successfully.";
        $maindata = $banks; 
        $api_status_code_class_call->respondOK($maindata, $text);
        exit;

    } catch (Exception $e) {
        $text = "Failed to fetch banks.";
        $errorcode = $api_error_code_class_call::$internalServerError;
        $hint = ["Check server logs for DB issues."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use GET method."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
