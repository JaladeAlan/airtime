<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    
    $admin_id = $api_users_table_class_call::checkIfIsAdmin($user_pubkey);

    // Unauthorized user
    if ( !$admin_id ){
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Only admins can subscribe to plans."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    // Get the request body
    $request_body = file_get_contents('php://input');
    $data = json_decode($request_body);

    // Check if the data is an array of user records
    if (!is_array($data)) {
        $text = $api_response_class_call::$invalidDataSent;
        $errorcode = $api_error_code_class_call::$internalUserWarning;
        $maindata = [];
        $hint = ["Ensure to send an array of user records in JSON format."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
    }

    $successCount = 0;
    $errorCount = 0;

    // Loop through each user record
    foreach ($data as $userRecord) {
        // Extract user data from the record
        $email = isset($userRecord->email) ? $utility_class_call::escape($userRecord->email) : "";
        $username = isset($userRecord->username) ? $utility_class_call::escape($userRecord->username) : "";
        $password = isset($userRecord->password) ? $utility_class_call::escape($userRecord->password) : "";
        $fullname = isset($userRecord->fullname) ? $utility_class_call::escape($userRecord->fullname) : "";
        $gender = isset($userRecord->gender) ? $utility_class_call::escape($userRecord->gender) : "";
        $description = isset($userRecord->description) ? $utility_class_call::escape($userRecord->description) : "";

        // Perform validations for each user record
        if (
            !$utility_class_call::validateEmail($email) ||
            !$utility_class_call::validatePassword($password) ||
            !$utility_class_call::validate_input($fullname) ||
            !$utility_class_call::validate_input($username) ||
            !$utility_class_call::validate_input($gender) ||
            !$utility_class_call::validate_input($description)
        ) {
            // If validation fails, increment the error count and continue to the next record
            $errorCount++;
            continue;
        }

            // Check if user with the same email or username exists in the database
            if ($api_users_table_class_call::getUserByUsername($username, $data) || $api_users_table_class_call::getUserByEmail($email, $data)) {
                // If user already exists, increment the error count and continue to the next record
                $errorCount++;
                continue;
            }

            // Hash the password securely
            $hashPassword = Utility_Functions::Password_encrypt($password);

            // Insert the user record into the database
            $user_id = $api_users_table_class_call::insertUser($userid, $username, $hashPassword, $email, $fullname);

            if ($user_id) {
                // If insertion is successful, increment the success count
                $successCount++;
            } else {
                // If insertion fails, increment the error count
                $errorCount++;
            }
        }

        // Respond with the results of the bulk registration
        $maindata = [
            "success_count" => $successCount,
            "error_count" => $errorCount
        ];

        $text = "Bulk registration completed. $successCount user(s) registered successfully, $errorCount user(s) registration failed.";
        $api_status_code_class_call->respondOK($maindata, $text);
    } else {
        $text = $api_response_class_call::$methodUsedNotAllowed;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Ensure to use the POST method for bulk user registration."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    ?>