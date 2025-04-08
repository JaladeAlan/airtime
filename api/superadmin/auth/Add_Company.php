<?php
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST'); // Allow GET and POST requests
header('Access-Control-Allow-Headers: Content-Type'); // Allow Content-Type header
header('Access-Control-Max-Age: 3600'); // Cache preflight response for 1 hour
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $admin_id = $api_users_table_class_call::checkIfIsSuperAdmin($user_pubkey);

    // Unauthorized user
    if (!$admin_id) {
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Only admins can have access."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract registration data
    $company_name = "";
    if (isset($data->company_name)) {
        $company_name = $utility_class_call::escape($data->company_name);
    }
    $details = "";
    if (isset($data->details)) {
        $details = $utility_class_call::escape($data->details);
    }
   
    $email = "";
    if (isset($data->email)) {
        $email = $utility_class_call::escape($data->email);
    }
    
    // Validate input data
    if ($utility_class_call::validate_input($email) || 
        $utility_class_call::validate_input($company_name) || 
        $utility_class_call::validate_input($details)) {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Validate the email
    if (!$utility_class_call::validateEmail($email)) {
        $text = $api_response_class_call::$invalidEmail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields.","pass in a valid email", "all fields should not be empty"];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }
  
    // Check if a user with the same email or company name exists in the database
    if ($api_users_table_class_call::getCompanyByName($company_name, $data)) {
        $text = $api_response_class_call::$companyExists;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A company with this name already exists."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    if ($api_users_table_class_call::getCompanyByEmail($email , $data)) {
        $text = $api_response_class_call::$emailExists;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A company with this email already exists."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Insert company data into the database
    $company_id = $api_users_table_class_call::insertCompany($company_name, $details, $email);
    $company_trial = $utility_class_call::startFreeTrial($company_name);

    if ($company_id) {
        // Respond with a success message
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
