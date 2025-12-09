<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Initialize variables for password and email
    $new_password = "";
    $email = "";

    // Check if email and new_password are present in the request data
    if (isset($data->email) && isset($data->new_password)) {
        $email = $utility_class_call::escape($data->email);
        $new_password = $utility_class_call::escape($data->new_password);

        // Validate password and confirm_password
        if (!$utility_class_call::validatePassword($new_password)) {
            $text = $api_response_class_call::$weakPassword;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send a valid password."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        $confirm_new_password = isset($data->confirm_new_password) ? $utility_class_call::escape($data->confirm_new_password) : "";

        if ($new_password !== $confirm_new_password) {
            $text = $api_response_class_call::$confirmPassword;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Passwords do not match."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }
    } else {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send email and a valid password."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Hash the password securely
    $hashPassword = Utility_Functions::Password_encrypt($new_password);

    // Update the user's password in the database
    $user_id = $api_users_table_class_call::updateForgotPassword($email, $hashPassword);

    if ($user_id) {
        // Respond with a success message
        $maindata = [];
        $text = $api_response_class_call::$passwordUpdateSuccessful;
        $api_status_code_class_call->respondOK($maindata, $text);
    } else {
        $text = $api_response_class_call::$passwordUpdateFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Password update failed. Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for password update."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
