<?php
// --- CORS & Preflight ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Content Type ---
header('Content-Type: application/json');

use Config\Utility_Functions;
require_once '../../../config/bootstrap_file.php';

// --- MAIN LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // ✅ Validate API Token
        $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
        $user_pubkey = $decodedToken->usertoken ?? null;

        if ($utility_class_call::validate_input($user_pubkey)) {
            $text = $api_response_class_call::$unauthorized_token;
            $errorcode = $api_error_code_class_call::$internalHackerWarning;
            $hint = ["Invalid or missing API token."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondUnauthorized([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // ✅ Fetch user record
        $user_data = $api_users_table_class_call::checkIfIsUser($user_pubkey);

        // ❌ If no user found
        if (!$user_data) {
            $text = $api_response_class_call::$unauthorized_token;
            $errorcode = $api_error_code_class_call::$internalHackerWarning;
            $hint = ["User not found or token invalid.", "Please log in again."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondUnauthorized([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // ✅ Remove sensitive info (optional)
        unset($user_data['password'], $user_data['pin'], $user_data['secret_key']);

        // ✅ Send successful response
        $maindata = $user_data;
        $text = $api_response_class_call::$detailsFetched;
        $api_status_code_class_call->respondOK($maindata, $text);

    } catch (\Exception $e) {
        // ❌ Handle unexpected exceptions
        $errorcode = $api_error_code_class_call::$internalServerError;
        $text = "An error occurred while fetching user details.";
        $hint = ["Please try again later.", $e->getMessage()];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
    }

} else {
    // ❌ Wrong HTTP method
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Ensure to use the GET method for fetching user details."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
