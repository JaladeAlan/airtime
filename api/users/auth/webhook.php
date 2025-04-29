<?php
header("Content-Type: application/json");
require_once '../../../config/bootstrap_file.php';

$payload = json_decode(file_get_contents("php://input"), true);
$reference = $payload['data']['reference'] ?? null;

if (!$reference) {
    http_response_code(400);
    echo json_encode(["error" => "No reference provided."]);
    exit;
}

// Verify with Paystack
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

if (
    isset($paystack_response['data']['status']) &&
    $paystack_response['data']['status'] === 'success'
) {
    $deposit = $api_users_table_class_call::getDepositByReference($reference);

    if ($deposit && $deposit['status'] !== 'completed') {
        $api_users_table_class_call::updateDepositStatus($reference, 'completed');
        // Credit user wallet logic here
        error_log("Deposit successful and updated for reference: $reference");
    }
}

http_response_code(200);
echo json_encode(["message" => "Webhook received."]);
