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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;

    $user_data = $api_users_table_class_call::checkIfIsUser($user_pubkey);
    if (!$user_data) {
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $hint = ["Login required."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"));

    $account_name = $utility_class_call::inputData($data, 'account_name');
    $account_number = $utility_class_call::inputData($data, 'account_number');
    $bank_name = $utility_class_call::inputData($data, 'bank_name');
    $bank_code = $utility_class_call::inputData($data, 'bank_code');
    $is_default = $utility_class_call::inputData($data, 'is_default');
    
    if ($utility_class_call::validate_input($account_name) ||  $utility_class_call::validate_input($account_number) 
    ||$utility_class_call::validate_input($is_default) ||
    $utility_class_call::validate_input($bank_code) ||  $utility_class_call::validate_input($bank_name)) {
     $text = $api_response_class_call::$invalidInfo;
     $errorcode = $api_error_code_class_call::$internalUserWarning;
     $maindata = [];
     $hint = ["Ensure to send valid data to the API fields."];
     $linktosolve = "https://";
     $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    $existing = $api_users_table_class_call::getUserBankAccountByNumber($account_number);
    if ($existing) {
        $text = "Bank account already exists.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Check if this account number is already used."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $inserted = $api_users_table_class_call::addBankAccount($user_pubkey, $account_name, $account_number, $bank_name, $bank_code, $is_default);

    if ($inserted) {
    
        $text = "Bank account added successefully";
        $maindata = [];
        $api_status_code_class_call->respondOK($maindata, $text);
        exit;
    
    } else {
        $text = "Failed to save bank account.";
        $errorcode = $api_error_code_class_call::$internalServerError;
        $hint = ["Check server logs for DB issues."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
    }

    exit;
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
