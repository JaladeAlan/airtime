<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../../config/bootstrap_file.php';

$payload = json_decode(file_get_contents("php://input"), true);
$reference = $payload['data']['reference'] ?? null;

if (!$reference) {
    http_response_code(400);
    echo json_encode(["error" => "No reference provided."]);
    exit;
}

// Fetch deposit record from DB
$deposit = $api_users_table_class_call::getDepositByReference($reference);

if (!$deposit) {
    http_response_code(404);
    echo json_encode(["error" => "Deposit not found."]);
    exit;
}

// If deposit is already completed, ignore to prevent double credit
if ($deposit['status'] === 'completed') {
    http_response_code(200);
    echo json_encode(["message" => "Deposit already completed."]);
    exit;
}

$verified = false;
$gateway = $deposit['payment_gateway']; 

if ($gateway === 'paystack') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . $reference);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer sk_test_83d7ddd7c5a80f9093a0f30102b58c292e6c3b18",
        "Cache-Control: no-cache"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $paystack_response = json_decode($response, true);

    if (isset($paystack_response['data']['status']) && $paystack_response['data']['status'] === 'success') {
        $verified = true;
    }

} elseif ($gateway === 'monnify') {
    $api_key = getenv("MONNIFY_API_KEY");
    $secret_key = getenv("MONNIFY_SECRET_KEY");
    $auth = base64_encode("$api_key:$secret_key");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.monnify.com/api/v1/transactions/$reference");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $auth",
        "Content-Type: application/json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $monnify_response = json_decode($response, true);

    if (isset($monnify_response['responseBody']['transactionStatus']) &&
        $monnify_response['responseBody']['transactionStatus'] === 'SUCCESS') {
        $verified = true;
    }
}

if ($verified) {
    $user_pubkey = $deposit['user_pubkey'];
    $amount = $deposit['amount'];

    // Update deposit status
    $api_users_table_class_call::updateDepositStatus($reference, 'completed');

    // Credit user wallet and create transaction record
    $details = "Wallet deposit via " . ucfirst($gateway);
    $api_users_table_class_call::depositToWallet($user_pubkey, $amount, $details, $reference);

    error_log("Deposit successful and wallet credited for reference: $reference");

    http_response_code(200);
    echo json_encode(["message" => "Deposit verified and wallet credited."]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Payment verification failed."]);
}
