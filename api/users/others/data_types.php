<?php
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
        $networkId = $utility_class_call::inputData($data, 'networkId');

        if ($utility_class_call::validate_input($networkId)) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Please provide a valid 'networkId'."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        // Fetch data from third-party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            endpoint: "load/data-types",
            payload: ['networkId' => $networkId]
        );

        if (!$maindata) {
            $api_status_code_class_call->respondInternalServerError("Failed to fetch data types from the third-party API.");
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "Data types fetched successfully.");
    } catch (\Exception $e) {
        $api_status_code_class_call->respondInternalServerError("An error occurred: " . $e->getMessage());
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Only POST method is allowed for this endpoint."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
