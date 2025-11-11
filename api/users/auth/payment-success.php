<?php
header("Content-Type: text/html; charset=UTF-8");
require_once '../../../config/bootstrap_file.php';

// Get reference from query string (Paystack redirects with this)
$reference = $_GET['reference'] ?? null;

if (!$reference) {
    echo "<h3 style='color:red;'>No payment reference provided.</h3>";
    exit;
}

// Verify transaction directly with Paystack API
$secret = getenv("PAYSTACK_SECRET_KEY");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . urlencode($reference));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secret",
    "Cache-Control: no-cache"
]);
$response = curl_exec($ch);
curl_close($ch);

$paystack_response = json_decode($response, true);

// Ensure verification succeeded
if (!isset($paystack_response['data']['status'])) {
    echo "<h3 style='color:red;'>Invalid Paystack response.</h3>";
    exit;
}

$status = $paystack_response['data']['status'];

if ($status === 'success') {
    // Find deposit record
    $deposit = $api_users_table_class_call::getDepositByReference($reference);
    if ($deposit) {
        if ($deposit['status'] !== 'completed') {
            // Mark as completed
            $api_users_table_class_call::updateDepositStatus($reference, 'successful');

            // Credit wallet
            $walletResult = $api_users_table_class_call::depositToWallet(
                $deposit['user_pubkey'],
                $deposit['amount'],
                'Wallet top-up via Paystack',
                $reference
            );

            if ($walletResult['status']) {
                echo "<h2 style='color:green;'>✅ Payment successful and wallet credited!</h2>";
            } else {
                echo "<h3 style='color:orange;'>Payment verified but wallet credit failed.</h3>";
            }
        } else {
            echo "<h3 style='color:green;'>This deposit was already processed.</h3>";
        }
    } else {
        echo "<h3 style='color:red;'>Deposit record not found for reference: $reference</h3>";
    }
} else {
    // Payment failed
    $api_users_table_class_call::updateDepositStatus($reference, 'failed');
    echo "<h3 style='color:red;'>Payment failed or not verified.</h3>";
}
?>
