<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// If the browser sends a preflight OPTIONS request, stop here and return OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');
require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract email from the request data
    $email = isset($data->email) ? $utility_class_call::escape($data->email) : "";

    // Validate input data (ensure a valid email)
    if (!$utility_class_call::validateEmail($email)) {
        // Return an error response if the email is invalid
        $text = $api_response_class_call::$invalidEmail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send a valid email address."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Check if a user with the provided email exists in the database
    $user = $api_users_table_class_call::getUserByEmail($email);
    if (empty($user)) {
        // Return an error response if the email is not found in the database
        $text = $api_response_class_call::$invalidEmail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["User with this email not found in the database."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Generate a reset code (simplified, generate a random numeric code)
    $resetCode = rand(1000, 9999);
    $expiry = date('Y-m-d H:i:s', strtotime('+5 mins'));

    // Store the reset code in the database along with an expiration time (if needed)
    $api_users_table_class_call::storeResetCode( $resetCode, $expiry, $user['email']);

    // Send an email to the user with the reset code
    $subject = "Password Reset Code";
    $message = "Your password reset code is: $resetCode";
    
  
    // Send an email to the user with a reset link containing the token
    $resetLink = "https://attendee.com/forgotPassword.php?token=" . $resetCode;
    $subject = "Password Reset Request";
    $message = "Click the following link to reset your password: $resetLink";
    // Respond with a success message
    $text = $api_response_class_call::$resetCodeEmailSent;
    $api_status_code_class_call->respondOK([], $text);
} else {
    // Handle cases where the request method is not POST
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for the forgot password request."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
