<?php 
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
        $amount = $utility_class_call::inputData($data, 'amount');
        $quantity = $utility_class_call::inputData($data, 'quantity');
        $pin = $utility_class_call::inputData($data, 'pin');
        $network = $utility_class_call::inputData($data, 'network');

        $reference = date('YmdHi') . strtoupper(bin2hex(random_bytes(9)));

        if (
            $utility_class_call::validate_input($sessionId) ||  $utility_class_call::validate_input($network) ||
            $utility_class_call::validate_input($amount) ||  $utility_class_call::validate_input($pin) ||
            $utility_class_call::validate_input($quantity)) {
            $text = $api_response_class_call::$invalidInfo;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send all required fields: session_id, amount, pin, quantity, network."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
            exit;
        }

        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI("send-airtime/auto-airtime-to-cash", [
            "network" => $network,
            "sessionId" => $sessionId,
            "amount" => $amount,
            "quantity"=> $quantity,
            "pin"=> $pin,
            "reference" => $reference,
            // "receiver_number" => '08127600502'
        ]);

        if (!$maindata || !$maindata['status']) {
            $errorMessage = isset($maindata['message']) ? $maindata['message'] : 'Unknown error from third-party API.';
            $api_status_code_class_call->respondInternalServerError(
                $maindata,
                "Airtime transfer failed: $errorMessage",
                [],
                "",
                "TRANSFER_FAILED"
            );
            exit;
        }        

        $details = "$amount airtime converted to ₦";
        $type = "airtime-to-cash";
        $api_users_table_class_call::insertTransaction($user_pubkey, $amount, $details, $reference, $type);

        $api_status_code_class_call->respondOK($maindata, "Airtime received and transaction recorded.");
    } catch (\Exception $e) {
        $api_status_code_class_call->respondInternalServerError([], $e->getMessage());
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
