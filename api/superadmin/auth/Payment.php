<?php
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST'); // Allow GET and POST requests
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header
header('Access-Control-Max-Age: 3600'); // Cache preflight response for 1 hour
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $admin_id = $api_users_table_class_call::checkIfIsSuperAdmin($user_pubkey);

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

    // Extract data from the request
    $admin_id = isset($data->admin_id) ? intval($data->admin_id) : 0;
    $amount = isset($data->amount) ? floatval($data->amount) : 0.0;
    $currency = isset($data->currency) ? $utility_class_call::escape($data->currency) : "";

    // Validate input data
    if ($admin_id <= 0 || $amount <= 0 || empty($currency)) {
        $text = $api_response_class_call::$invalidDataSent;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields: admin_id, amount, and currency."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Call the payment gateway API to initiate a transaction
    $payment_status = initiatePayment($admin_id, $amount, $currency);

    if ($payment_status) {
        // Payment initiation successful
        $text = $api_response_class_call::$paymentInitiated;
        $maindata = ["payment_status" => $payment_status];
        $api_status_code_class_call->respondOK($maindata, $text);
    } else {
        // Error initiating payment
        $text = $api_response_class_call::$paymentFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Error initiating payment. Please try again."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for Super Admin payments."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}

// Function to initiate payment using the payment gateway API
function initiatePayment($admin_id, $amount, $currency) {
    // Implement the logic to call the payment gateway API here
    // You'll need to refer to the documentation of the chosen payment gateway (e.g., Flutterwave, Paystack)
    // to understand how to initiate a payment transaction.
    // Example: You might make an API request to the payment gateway's endpoint.

    // For demonstration purposes, let's assume a successful payment initiation
    return true;
}

?>

