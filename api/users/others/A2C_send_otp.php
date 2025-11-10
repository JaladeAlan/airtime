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
        $phone = $utility_class_call::inputData($data, 'phone');
        $network = $utility_class_call::inputData($data, 'network');

        if (
            $utility_class_call::validate_input($phone) ||
            $utility_class_call::validate_input($network)
        ) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send all required fields: network and phone."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI("send-resend/auto-airtime-to-cash-otp", [
            "senderNumber" => $phone,
            "network" => $network
        ]);

        if (!$maindata || !$maindata['status']) {
            $text = "OTP send failed";
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Failed to send OTP"];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $api_status_code_class_call->respondOK($maindata, "OTP sent successfully.");
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
