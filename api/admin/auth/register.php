<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Extract registration data
    $email = "";
    if(isset($data->email)){
        $email=$utility_class_call::escape($data->email);
    }
    
    $password = "";
    if(isset($data->password)){
        $password=$utility_class_call::escape($data->password);
        if(!$utility_class_call::validatePassword($password)){
            $text = $api_response_class_call::$weakPassword;
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Ensure to send valid data to the API fields."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }      
         //Confirm password validations
         $confirm_password = "";
         if(isset($data->confirm_password)){
             $confirm_password=$utility_class_call::escape($data->confirm_password);
         }
         if($password != $confirm_password){
          $text = $api_response_class_call::$confirmPassword;
          $errorcode = $api_error_code_class_call::$internalUserWarning;
          $maindata = [];
          $hint = ["Ensure to send valid data to the API fields."];
          $linktosolve = "https://";
          $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
         }
    }
    $fullname = "";
    if(isset($data->fullname)){
        $fullname=$utility_class_call::escape($data->fullname);
    }
    
    // Validate input data
    if ($utility_class_call::validate_input($email) || $utility_class_call::validate_input($password) || 
       $utility_class_call::validate_input($fullname)) {
        $text = $api_response_class_call::$invalidInfo;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    //validate the email
    if(!$utility_class_call::validateEmail($email)){
        $text = $api_response_class_call::$invalidEmail;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send valid data to the API fields.","pass in valid email", "all fields should not be empty"];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata,$text,$hint,$linktosolve,$errorcode);
    }

    // Check if user with the same email exists in the database
    if ($api_users_table_class_call::getAdminUserByEmail($email , $data)) {
        $text = $api_response_class_call::$emailExists;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["A user with this email already exists."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    // Hash the password securely
    $hashPassword = Utility_Functions::Password_encrypt($password);
 
    // Insert user data into the database
    $user_id = $api_users_table_class_call::insertAdminUser($hashPassword, $email, $fullname);

    if ($user_id) {
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