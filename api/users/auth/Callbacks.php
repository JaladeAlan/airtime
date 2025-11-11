<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Paystack-Signature, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

require_once '../../../config/bootstrap_file.php';

// Paystack secret key for webhook verification
$paystack_secret = 'sk_test_83d7ddd7c5a80f9093a0f30102b58c292e6c3b18'; // keep secret

// Get the signature from headers
$headers = getallheaders();
if (!isset($headers['X-Paystack-Signature'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No signature provided.']);
    exit;
}

$signature = $headers['X-Paystack-Signature'];

// Get the request body
$payload = @file_get_contents('php://input');

// Verify signature
$hash = hash_hmac('sha512', $payload, $paystack_secret);
if ($hash !== $signature) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature.']);
    exit;
}

// Parse payload
$data = json_decode($payload, true);

if (!isset($data['data']['reference']) || !isset($data['data']['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload.']);
    exit;
}

$reference = $data['data']['reference'];
$status = $data['data']['status'];

// Fetch deposit
$deposit = $api_users_table_class_call::getDepositByReference($reference);
if (!$deposit) {
    http_response_code(404);
    echo json_encode(['error' => 'Deposit not found.']);
    exit;
}

// Idempotency: do not process completed deposits
if ($deposit['status'] === 'completed') {
    http_response_code(200);
    echo json_encode(['message' => 'Deposit already processed.']);
    exit;
}

if ($status === 'success') {
    // Update deposit status first
    $updated = $api_users_table_class_call::updateDepositStatus($reference, 'completed');
    if ($updated) {
        // Credit user wallet
        $walletResult = $api_users_table_class_call::depositToWallet($deposit['user_pubkey'], $deposit['amount'], 'Wallet top-up via Paystack', $reference);
        
        if ($walletResult['status']) {
            http_response_code(200);
            echo json_encode(['message' => 'Deposit confirmed and wallet credited.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Deposit updated but wallet credit failed.', 'details' => $walletResult['message']]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update deposit status.']);
    }
} else {
    // Payment failed
    $api_users_table_class_call::updateDepositStatus($reference, 'failed');
    http_response_code(400);
    echo json_encode(['error' => 'Payment failed or abandoned.']);
}
