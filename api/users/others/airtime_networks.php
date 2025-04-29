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
        $networks = $utility_class_call::inputData($data, 'networks');

        if ($utility_class_call::validate_input($networks)) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send valid 'networks' data to the API."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        // Fetch data from third-party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            endpoint: "load/networks",
            payload: ['networks' => $networks]
        );

        if (!$maindata) {
            $api_status_code_class_call->respondBadRequest("Failed to fetch data from the third-party API.");
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "Data fetched successfully.");
    } catch (\Exception $e) {
        $api_status_code_class_call->respondBadRequest("An error occurred: " . $e->getMessage());
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for getting network data."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
