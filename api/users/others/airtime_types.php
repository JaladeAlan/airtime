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
            $hint = ["Ensure to send valid 'networkId' to the API."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        // Fetch data from third-party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            endpoint: "load/airtime-types",
            payload: ['networkId' => $networkId]
        );

        if (!$maindata) {
            $api_status_code_class_call->respondInternalServerError("Failed to fetch data from the third-party API.");
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "Data fetched successfully.");
    } catch (\Exception $e) {
        $api_status_code_class_call->respondInternalServerError("An error occurred: " . $e->getMessage());
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for getting airtime types."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
