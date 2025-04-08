<?php
header('Content-Type: application/json');

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);
    // Alldata sent in
    $email = "";
    if (isset($data->email)) {
        $email = $utility_class_call::escape($data->email);
    }
    $password = "";
    if (isset($data->password)) {
        $password = $utility_class_call::escape($data->password);
    }

    // Validate input
    if (!$utility_class_call::validateEmail($email) || $utility_class_call::validate_input($password)) {
        $text = $api_response_class_call::$invalidUserDetail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
    }

     // Check if user with username exists in database
     $user=$api_users_table_class_call::getUserByIdOrEmail($email);
     if (count($user) == 0) {
         $text = $api_response_class_call::$invalidUserDetail;
         $errorcode = $api_error_code_class_call::$internalUserWarning;
         $maindata = [];
         $hint = ["Ensure data sent is valid and user data is in database.","User with email not found"];
         $linktosolve = "https://";
         $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
     }

    // Verify password
    if (!password_verify($password, $user["password"])) {
        $text = $api_response_class_call::$passwordIncorrect;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure data sent is valid and user data is in database.","Invalid password"];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
    }
  $userPubkey = $user["pub_key"];
    $userid=$user["user_id"];
    // $seescode="ewdfwf";
    $sendToEmail=$user["email"];
    // $sendToPhoneNo=$user["phoneno"];
     // Generate JWT token
    $token = $api_status_code_class_call->getTokenToSendAPI($userPubkey);
    // // Send login mail
    // $api_sns_email_class_call->sendLoginSMSEMail($userid,$seescode,$sendToEmail,$sendToPhoneNo);
    // Send response with token
    $maindata = array("token" => $token);
    $text = $api_response_class_call::$loginSuccessful;
    $api_status_code_class_call->respondOK($maindata, $text);
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the method stated in the documentation."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}