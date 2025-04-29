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
        $airtimeType = $utility_class_call::inputData($data, 'airtimeType');
        $amount = $utility_class_call::inputData($data, 'amount');
        $phone = $utility_class_call::inputData($data, 'phone');
        $reference = date('YmdHi') . strtoupper(bin2hex(random_bytes(10)));
        
        // Validate inputs (Corrected logic here, assuming validate_input returns false for valid input)
        if (
            $utility_class_call::validate_input($networkId) ||
            $utility_class_call::validate_input($airtimeType) ||
            $utility_class_call::validate_input($amount) ||
            $utility_class_call::validate_input($phone) ||
            $utility_class_call::validate_input($reference)
        ) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send all required fields: networkId, airtimeType, amount, phone, and reference."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // Make request to third-party API
        try {
            $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
                endpoint: "airtime",
                payload: [
                    'networkId' => $networkId,
                    'airtimeType' => $airtimeType,
                    'amount' => $amount,
                    'phone' => $phone,
                    'reference' => $reference
                ]
            );
        } catch (\Exception $e) {
            $text = "An error occurred: " . $e->getMessage();
            $errorcode = $e->getCode() ?: "UNKNOWN_ERROR";
            $maindata = [];
            $hint = ["Failed to fetch data from the third-party API."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // Proceed if third-party API response is successful
        if (!$maindata || !$maindata['status']) {
            $text = "Failed to receive valid response from the third-party API.";
            $errorcode = "API_RESPONSE_ERROR";
            $maindata = [];
            $hint = ["Verify the response from the third-party API."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $details = "Airtime of '$amount' has been purchased successfully for '$phone'";
        $type = "airtime";
        
        // Save the transaction to the database
        $transaction_id = $api_users_table_class_call::insertTransaction($user_pubkey, $amount, $details, $reference, $type);

        if ($transaction_id) {
            $api_status_code_class_call->respondOK(
                $maindata,
                "Airtime purchased successfully and transaction saved."
            );
        } else {
            $text = "An error occurred: Failed to save transaction in the database.";
            $errorcode = "DATABASE_ERROR";
            $maindata = [];
            $hint = ["Failed to save transaction in the database."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
        }
    } catch (\Exception $e) {
        $api_status_code_class_call->respondInternalServerError("An error occurred: " . $e->getMessage());
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for purchasing airtime."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
