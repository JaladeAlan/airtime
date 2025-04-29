<?php
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

    if ($utility_class_call::validate_input($amount) || $utility_class_call::validate_input($payment_method)) {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Provide a valid amount and payment method."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $amount = floatval($amount);
    if ($amount < 100) {
        $text = "Minimum deposit is ₦100.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Increase the amount and try again."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    // Create reference
    $reference = uniqid("DP-");

    // Store pending deposit
    $stored = $api_users_table_class_call::storeDepositIntent($user_pubkey, $amount, $payment_method, $reference);

    if (!$stored) {
        $text = "Unable to initiate deposit.";
        $errorcode = $api_error_code_class_call::$internalServerError;
        $hint = ["Try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }
    $email = $user_data['email'];
    $fullname = $user_data['first_Name']  . ' ' . $user_data['last_Name'] ;

    if ($payment_method === 'paystack') {
        $payment_link = $api_users_table_class_call::generatePaystackLink($email, $amount, $fullname, $reference);
    } elseif ($payment_method === 'monnify') {
        $payment_link = $api_users_table_class_call::generateMonnifyLink($email, $amount, $fullname, $reference);
    } else {
        $text = "Invalid payment method.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Only 'paystack' or 'monnify' is allowed."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $maindata = [
        "reference" => $reference,
        "payment_link" => $payment_link
    ];
    $text = "Deposit initiated. Complete payment using the link.";
    $api_status_code_class_call->respondOK($maindata, $text);
    exit;

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method to make a deposit."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
