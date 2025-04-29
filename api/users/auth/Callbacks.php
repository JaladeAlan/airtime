<?php
header('Content-Type: application/json');

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents("php://input"), true);

    if (!isset($payload['data']['reference']) || !isset($payload['data']['status'])) {
        $text = "Invalid payload.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Missing reference or status."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    $reference = $payload['data']['reference'];
    $status = $payload['data']['status'];

    $deposit = $api_users_table_class_call::getDepositByReference($reference);

    if (!$deposit) {
        $text = "Deposit not found for this reference.";
        $errorcode = $api_error_code_class_call::$internalServerError;
        $hint = ["Check the reference or ensure it's stored."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
        exit;
    }

    if ($status === 'success') {
        $updated = $api_users_table_class_call::updateDepositStatus($reference, 'completed');

        if ($updated) {
            $text = "Deposit confirmed successfully.";
            $api_status_code_class_call->respondOK([], $text);
        } else {
            $text = "Deposit found, but update failed.";
            $errorcode = $api_error_code_class_call::$internalServerError;
            $hint = ["Check your database update logic."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError([], $text, $hint, $linktosolve, $errorcode);
        }
    } else {
        // Payment was not successful
        $api_users_table_class_call::updateDepositStatus($reference, 'failed');

        $text = "Payment failed or was abandoned.";
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $hint = ["Try another payment method or retry."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest([], $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = "Invalid request method.";
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $hint = ["Use POST method for Paystack callbacks."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed([], $text, $hint, $linktosolve, $errorcode);
}
