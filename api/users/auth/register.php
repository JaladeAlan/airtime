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

use Config\Utility_Functions;
require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract registration data
    $email = $utility_class_call::inputData($data, 'email');
    $username = $utility_class_call::inputData($data, 'username');
    $password = $utility_class_call::inputData($data, 'password');

    if(!$utility_class_call::validatePassword($password)){
        $text = $api_response_class_call::$weakPassword;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    $confirm_password = $utility_class_call::inputData($data, 'confirm_password');
    if($password != $confirm_password){
        $text = $api_response_class_call::$confirmPassword;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    $firstname = $utility_class_call::inputData($data, 'firstname');
    $lastname = $utility_class_call::inputData($data, 'lastname');
    $phone = $utility_class_call::inputData($data, 'phone');
    $bankname = $utility_class_call::inputData($data, 'bankname');
    $accountno = $utility_class_call::inputData($data, 'accountno');
    $referrer = $utility_class_call::inputData($data, 'referrer') ?: null;

    // Validate input data
    if ($utility_class_call::validate_input($email) || $utility_class_call::validate_input($password) || 
        $utility_class_call::validate_input($firstname) ||  $utility_class_call::validate_input($lastname) ||
        $utility_class_call::validate_input($bankname) ||  $utility_class_call::validate_input($phone) ||
        $utility_class_call::validate_input($accountno) ||  $utility_class_call::validate_input($referrer) ||
        $utility_class_call::validate_input($username) ) {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    if(!$utility_class_call::validateEmail($email)){
        $text = $api_response_class_call::$invalidEmail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields.","pass in valid email", "all fields should not be empty"];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
    }

    // Duplicate checks
    if ($api_users_table_class_call::getUserByUsername($username, $data)) {
        $text = $api_response_class_call::$userExists;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this username already exists."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    if ($api_users_table_class_call::getUserByPhone($phone, $data)) {
        $text = $api_response_class_call::$phoneExists;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this Phone number already exists."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    if ($api_users_table_class_call::getUserByEmail($email , $data)) {
        $text = $api_response_class_call::$emailExists;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this email already exists."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Hash and insert
    $hashPassword = Utility_Functions::Password_encrypt($password);
    $user_id = $api_users_table_class_call::insertUser($username, $hashPassword, $email, $firstname, $lastname,
     $accountno, $bankname, $phone, $referrer);

    if ($user_id) {
        $maindata = [];
        $text = $api_response_class_call::$registrationSuccessful;
        $api_status_code_class_call->respondOK($maindata, $text);
    } else {
        $text = $api_response_class_call::$registrationFailed;
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Registration failed. Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for registration."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
