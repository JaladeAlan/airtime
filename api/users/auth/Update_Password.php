<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;

    $user = $api_users_table_class_call::getUserByKey($user_pubkey);

    // Unauthorized user
    if (!$user) {
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Please log in to access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    $user_id = $user['user_id'];

    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract data from the request
    $password = isset($data->password) ? $utility_class_call::escape($data->password) : "";
    $new_password = isset($data->new_password) ? $utility_class_call::escape($data->new_password) : "";
    $confirm_password = isset($data->confirm_password) ? $utility_class_call::escape($data->confirm_password) : "";

    // Validate input data
    if (!$utility_class_call::validatePassword($password)) {
        $text = $api_response_class_call::$invalidUserDetail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Check if the existing password is correct
    if (!password_verify($password, $user["user_password"])) {
        $text = $api_response_class_call::$passwordOldIncorrect;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Incorrect password."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Check if the new password matches the confirm password
    if ($new_password !== $confirm_password) {
        $text = $api_response_class_call::$confirmPassword;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["New password and confirm password do not match."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Hash the new password securely
    $hashPassword = Utility_Functions::Password_encrypt($new_password);

    // Update the user's password in the database
    $updateSuccess = $api_users_table_class_call::updatePassword($user_pubkey, $hashPassword);

    if ($updateSuccess) {
        $text = $api_response_class_call::$passwordResetSuccessful;
        $api_status_code_class_call->respondOK([], $text);
    } else {
        $text = $api_response_class_call::$passwordResetFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Password reset failed. Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for password reset."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
