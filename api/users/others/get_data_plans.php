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
            $hint = ["Missing or invalid API token."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondUnauthorized([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"));

        $networkId = $utility_class_call::inputData($data, 'network_id');
        $dataType  = $utility_class_call::inputData($data, 'data_type');

        if ($utility_class_call::validate_input($networkId) || $utility_class_call::validate_input($dataType)) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $hint = ["Ensure 'network_id' and 'data_type' are provided and valid."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // Fetch data plans from third-party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            endpoint: "load/data",
            payload: ['networkId' => $networkId, 'dataType' => $dataType]
        );

        // Handle third-party API failure
        if (!$maindata || (isset($maindata['status']) && !$maindata['status'])) {
            $errorMessage = $maindata['message'] ?? "Unknown error from third-party API.";
            $text = "Failed to fetch data plans: $errorMessage";
            $errorcode = $api_error_code_class_call::$internalServerError;
            $hint = ["Check network_id, data_type, and third-party API connection."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "Data plans fetched successfully.");

    } catch (\Exception $e) {
        $errorcode = $api_error_code_class_call::$internalServerError;
        $text = "An unexpected error occurred.";
        $hint = ["Try again later.", $e->getMessage()];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
    }

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method to fetch data plans."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
