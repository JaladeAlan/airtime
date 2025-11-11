<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
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

    $amount = $utility_class_call::inputData($data, 'amount');
    $payment_method = $utility_class_call::inputData($data, 'payment_method');
    $account_details = $utility_class_call::inputData($data, 'account_details'); // e.g., bank info

    if ($utility_class_call::validate_input($amount) || 
        $utility_class_call::validate_input($payment_method) || 
        $utility_class_call::validate_input($account_details)) {

        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Provide a valid amount, payment method, and account details."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $amount = floatval($amount);
    $wallet_balance = floatval($user_data['balance']);

    if ($amount < 100) {
        $text = "Minimum withdrawal is ₦100.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Increase the amount and try again."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    if ($amount > $wallet_balance) {
        $text = "Insufficient wallet balance.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Reduce withdrawal amount or fund wallet."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    // Create unique withdrawal reference
    $reference = uniqid("WD-");

    // Store withdrawal request
    $stored = $api_users_table_class_call::storeWithdrawalRequest($user_pubkey, $amount, $payment_method, $account_details, $reference);

    if (!$stored) {
        $text = "Unable to initiate withdrawal.";
        $errorcode = $api_error_code_class_call::$internalServerError;
        $hint = ["Try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $maindata = [
        "reference" => $reference,
        "amount" => $amount,
        "payment_method" => $payment_method
    ];
    $text = "Withdrawal request submitted. Processing may take some time.";
    $api_status_code_class_call->respondOK($maindata, $text);
    exit;

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method to request a withdrawal."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
