<?php
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST'); // Allow GET and POST requests
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header
header('Access-Control-Max-Age: 3600'); // Cache preflight response for 1 hour
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate admin access
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    $admin_id = $api_users_table_class_call::checkIfIsSuperAdmin($user_pubkey);

    // Unauthorized user
    if (!$admin_id) {
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Only superadmins can have access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract data from the request
    $admin_id_from_request = isset($data->admin_id) ? intval($data->admin_id) : 0;
    $subscription_type = isset($data->subscription_type) ? $utility_class_call::escape($data->subscription_type) : "";
    $currency = isset($data->currency) ? $utility_class_call::escape($data->currency) : "";
    $start_date = date('Y-m-d'); // Subscription start date is the current date

    // Validate input data
    if ($admin_id_from_request !== $admin_id || $admin_id <= 0 || empty($subscription_type) || empty($currency)) {
        $text = $api_response_class_call::$invalidDataSent;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields: admin_id, subscription_type, and currency."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Check if admin exists
    $admin = Utility_Functions::getAdminSubscription($admin_id);
    if (!$admin) {
        $text = $api_response_class_call::$invalidSubscriptionDetail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Admin not found."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Calculate subscription end date based on the selected type
    $end_date = $utility_class_call::calculateSubscriptionEndDate($start_date, $subscription_type);

    // Save subscription data to the database
    $subscription_id = Utility_Functions::createSubscription($admin_id, $subscription_type, $currency, $start_date, $end_date);

    if ($subscription_id) {
        $text = $api_response_class_call::$subscriptionSuccessful;
        $maindata = ["subscription_id" => $subscription_id];
        $api_status_code_class_call->respondOK($maindata, $text);
    } else {
        $text = $api_response_class_call::$subscriptionFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Error creating subscription. Please try again."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for Super Admin subscription."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
