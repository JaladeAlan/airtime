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
use Config\ThirdParties_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate API token
        $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
        $user_pubkey = $decodedToken->usertoken;

        // Get JSON body
        $data = json_decode(file_get_contents("php://input"));

        // Extract and validate input fields
        $networks = $utility_class_call::inputData($data, 'networks');

        if ($utility_class_call::validate_input($networks)) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send a valid 'networks' field."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        // Fetch data from third-party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            endpoint: "load/networks",
            payload: ['networks' => $networks]
        );

        if (!$maindata) {
            $text = "Error occured";
            $errorcode = "Could not fetch data";
            $hint = ["Failed to fetch data from the third-party API."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "Networks fetched successfully.");
    } catch (\Exception $e) {
        $text = "An error occurred: " . $e->getMessage();
        $errorcode = $e->getCode() ?: "UNKNOWN_ERROR";
        $maindata = [];
        $hint = ["Check server logs for stack trace."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
        
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Use the POST method to load networks."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
