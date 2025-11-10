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
        $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
        $user_pubkey = $decodedToken->usertoken;

        $data = json_decode(file_get_contents("php://input"));
        $identifier = $utility_class_call::inputData($data, 'identifier');
        $otp = $utility_class_call::inputData($data, 'otp');

        if (
            $utility_class_call::validate_input($identifier) ||
            $utility_class_call::validate_input($otp)
        ) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send all required fields: identifier and otp."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI("verify/auto-airtime-to-cash-otp", [
            "identifier" => $identifier,
            "otp" => $otp
        ]);

        if (!$maindata || !$maindata['status']) {
            $text = "OTP verification failed";
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Failed to verify OTP"];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "OTP verified successfully.");
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
    $hint = ["Ensure to use the POST method for Airtime to Cash"];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], "Use POST method.", [], "", "INVALID_METHOD");
}
?>
