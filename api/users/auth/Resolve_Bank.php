<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(file_get_contents("php://input"), true);
    $bank_code = $input['bank_code'] ?? null;
    $account_number = $input['account_number'] ?? null;

    if (!$bank_code || !$account_number) {
        $text = "Bank code and account number are required.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Provide both bank_code and account_number in the request body."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondOK([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    try {
    
        $paystack_secret_key = getenv('PAYSTACK_SECRET_KEY'); 
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number={$account_number}&bank_code={$bank_code}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$paystack_secret_key}",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception($err);
        }

        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === true && !empty($result['data']['account_name'])) {
            $account_name = $result['data']['account_name'];

            // Success response
            $text = "Account resolved successfully.";
            $maindata = ["account_name" => $account_name];
            $api_status_code_class_call->respondOK($maindata, $text);
            exit;

        } else {
            $text = "Unable to verify account.";
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $hint = ["Check if the account number and bank code are correct."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondOK([], $text, $hint, $linktosolve, $errorcode);
            exit;
        }

    } catch (Exception $e) {
        $text = "Failed to resolve account.";
        $errorcode = $api_error_code_class_call::$internalServerError;
        $hint = ["Check server logs for Paystack API or network issues."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
?>
