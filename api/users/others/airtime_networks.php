<?php
// --- CORS & Preflight ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

use Config\Utility_Functions;
use Config\ThirdParties_Functions;
require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
        $user_pubkey = $decodedToken->usertoken ?? null;

        if ($utility_class_call::validate_input($user_pubkey)) {
            $text = $api_response_class_call::$unauthorized_token;
            $errorcode = $api_error_code_class_call::$internalHackerWarning;
            $hint = ["Missing or invalid token."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondUnauthorized([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));
        $networks = $utility_class_call::inputData($data, 'networks');

        if ($utility_class_call::validate_input($networks)) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["'networks' field is required and must not be empty."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // Fetch Data from Third-Party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            "load/networks",
            ['networks' => $networks]
        );

        if (!$maindata || (isset($maindata['status']) && !$maindata['status'])) {
            $errorMessage = $maindata['message'] ?? "Failed to fetch data from third-party API.";
            $errorcode = $api_error_code_class_call::$internalServerError;
            $api_status_code_class_call->respondInternalServerError(
                [],
                $errorMessage,
                ["Please try again later."],
                "https://",
                $errorcode
            );
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "Network data fetched successfully.");

    } catch (\Exception $e) {
        $errorcode = $api_error_code_class_call::$internalServerError;
        $text = "An unexpected error occurred while fetching network data.";
        $hint = [$e->getMessage()];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
    }

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for fetching network data."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
