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
        $user_pubkey = $decodedToken->usertoken;

        $data = json_decode(file_get_contents("php://input"));
        $sessionId = $utility_class_call::inputData($data, 'session_id');
        $amount    = $utility_class_call::inputData($data, 'amount');
        $quantity  = $utility_class_call::inputData($data, 'quantity');
        $pin       = $utility_class_call::inputData($data, 'pin');
        $network   = $utility_class_call::inputData($data, 'network');

        $reference = date('YmdHi') . strtoupper(bin2hex(random_bytes(9)));

        // Validate Input
        if (
            $utility_class_call::validate_input($sessionId) ||
            $utility_class_call::validate_input($network)   ||
            $utility_class_call::validate_input($amount)    ||
            $utility_class_call::validate_input($pin)       ||
            $utility_class_call::validate_input($quantity)
        ) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $hint = ["Ensure to send all required fields: session_id, amount, pin, quantity, and network."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        // Process Airtime Conversion
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI("send-airtime/auto-airtime-to-cash", [
            "network"   => $network,
            "sessionId" => $sessionId,
            "amount"    => $amount,
            "quantity"  => $quantity,
            "pin"       => $pin,
            "reference" => $reference,
            // "receiver_number" => "08127600502"
        ]);

        // Validate Third-party Response
        if (!$maindata || !isset($maindata['status']) || !$maindata['status']) {
            $errorMessage = isset($maindata['message']) ? $maindata['message'] : "Unknown error from third-party API.";
            $api_status_code_class_call->respondInternalServerError(
                $maindata,
                "Airtime transfer failed: $errorMessage",
                ["Check network connection or contact support."],
                "https://",
                $api_error_code_class_call::$internalServerError
            );
            exit;
        }

        // Record Transaction
        $details = "{$amount} airtime converted successfully.";
        $type = "airtime-to-cash";
        $api_users_table_class_call::insertTransaction($user_pubkey, $amount, $details, $reference, $type);

        // Send Success Response
        $text = "Airtime received and transaction recorded successfully.";
        $api_status_code_class_call->respondOK($maindata, $text);

    } catch (\Exception $e) {
        $api_status_code_class_call->respondInternalServerError(
            [],
            "An unexpected error occurred: " . $e->getMessage(),
            ["Contact the system administrator if this persists."],
            "https://",
            $api_error_code_class_call::$internalServerError
        );
    }

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Ensure to use the POST method for Airtime to Cash operations."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
