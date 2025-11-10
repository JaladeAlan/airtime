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
        $networkId = $utility_class_call::inputData($data, 'networkId');
        $dataType = $utility_class_call::inputData($data, 'dataType');
        $planId = $utility_class_call::inputData($data, 'planId');
        $phone = $utility_class_call::inputData($data, 'phone');
        $reference = $utility_class_call::inputData($data, 'reference');

        // Validate inputs
        if (
            $utility_class_call::validate_input($networkId) ||
            $utility_class_call::validate_input($dataType) ||
            $utility_class_call::validate_input($planId) ||
            $utility_class_call::validate_input($phone) ||
            $utility_class_call::validate_input($reference)
        ) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send all required fields: networkId, dataType, planId, phone, and reference."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // Make request to third-party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            endpoint: "load/data",
            payload: [
                'networkId' => $networkId,
                'dataType' => $dataType,
                'planId' => $planId,
                'phone' => $phone,
                'reference' => $reference
            ]
        );

        if (!$maindata || !$maindata['status']) {
            $text = "Failed to fetch data from the third-party API.";
            $errorcode = "API_CALL_FAILED";
            $maindata = [];
            $hint = ["Verify the third-party API response and try again."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $amount = isset($maindata['amount']) ? $maindata['amount'] : 0; 

        $details = "Data plan '$dataType' has been purchased successfully for '$phone'";
        $type = "data";

        // Save the transaction to the database
        $transaction_id = $api_users_table_class_call::insertTransaction($user_pubkey, $amount, $details, $reference, $type);

        if ($transaction_id) {
            $api_status_code_class_call->respondOK(
                $maindata,
                "Data plan purchased successfully and transaction saved."
            );
        } else {
            $text = "Failed to save transaction in the database.";
            $errorcode = "DATABASE_ERROR";
            $maindata = [];
            $hint = ["Verify database connection and transaction handling."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
        }
    } catch (\Exception $e) {
        // General error handling for any other exception
        $text = "An error occurred: " . $e->getMessage();
        $errorcode = "EXCEPTION_ERROR";
        $maindata = [];
        $hint = ["Check the error message and troubleshoot the issue."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    // Handle non-POST method request
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for purchasing data."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
