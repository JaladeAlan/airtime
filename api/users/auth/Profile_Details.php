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

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $text = $api_response_class_call::$methodUsedNotAllowed;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $hint = ["Use GET to fetch user details."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken ?? null;

    $user_data = $api_users_table_class_call::checkIfIsUser($user_pubkey);

    if (!$user_data) {
        $text = "User not found";
        $text = $api_response_class_call::$unauthorized_token;
        $hint = ["Token may have expired or been tampered with."];
        $api_status_code_class_call->respondUnauthorized([], $text, $hint, "https://", $errorcode);
        exit;
    }

    $dashboard_data = $api_users_table_class_call::getUserDashboardData($user_pubkey);
    $response = ["dashboard" => $dashboard_data];
    $text = $api_response_class_call::$detailsFetched;
    $api_status_code_class_call->respondOK($response, $text);

} catch (\Throwable $e) {
    $errorcode = $api_error_code_class_call::$internalServerError;
    $text = "An error occurred while fetching user profile.";
    $hint = [$e->getMessage()];
    $api_status_code_class_call->respondInternalServerError([], $text, $hint, "https://", $errorcode);
}
