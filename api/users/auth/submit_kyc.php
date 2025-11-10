<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Content type header ---
header('Content-Type: application/json');

use Config\Utility_Functions;
require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate Token
        $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
        $user_pubkey = $decodedToken->usertoken;

        // Get user details
        $userDetails = $api_users_table_class_call::getUserByKey($user_pubkey);
        if (!$userDetails) {
            $text = $api_response_class_call::$unauthorized_token;
            $errorcode = $api_error_code_class_call::$internalHackerWarning;
            $maindata = [];
            $hint = ["Login required."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        $user_id = $userDetails['id'];
        $email = $userDetails['email'];
        $username = $userDetails['username'];
        $fullname = $userDetails['lname'] . ' ' . $userDetails['fname'];

        // Uploaded files
        $selfie = $_FILES['selfie'] ?? null;
        $govID = $_FILES['regulatory_image'] ?? null;
        $utility = $_FILES['utility_image'] ?? null;

        // --- Eligibility check ---
        if ($api_users_table_class_call::getUserLevel($user_id) != 2) {
            $text = "User not eligible for KYC submission.";
            $errorcode = $api_error_code_class_call::$internalUserWarning;
            $maindata = [];
            $hint = ["Only level 2 users can submit KYC."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        // --- Validate uploaded images ---
        $requiredFiles = [
            'Selfie' => $selfie,
            'Government ID' => $govID,
            'Utility Bill' => $utility
        ];

        foreach ($requiredFiles as $label => $file) {
            $validate = $utility_class_call::validateImageUpload($file, $label);
            if (!$validate['success']) {
                $text = $validate['message'] ?? "Invalid {$label} image.";
                $errorcode = $api_error_code_class_call::$internalUserWarning;
                $maindata = [];
                $hint = ["Ensure to provide valid image for {$label}."];
                $linktosolve = "https://";
                $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
            }
        }

        // --- Upload images ---
        $uploadLocations = [
            'selfie' => ['dir' => '/userpassport/', 'file' => $selfie],
            'regcard' => ['dir' => '/userregulatorycards/', 'file' => $govID],
            'utility' => ['dir' => '/userutility_bill/', 'file' => $utility],
        ];

        $imagePaths = [];

        foreach ($uploadLocations as $key => $val) {
            $result = $utility_class_call::uploadImage($val['file'], $val['dir'], $username);
            if (!$result['success']) {
                $text = "Error uploading {$key} image.";
                $errorcode = $api_error_code_class_call::$internalServerError;
                $maindata = [];
                $hint = ["Please retry uploading the KYC images."];
                $linktosolve = "https://";
                $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
            }
            $imagePaths[$key] = $result['imagepath'];
        }

        $updateSuccess = $api_users_table_class_call::submitKYC($user_id, $imagePaths);
        if (!$updateSuccess) {
            $text = "Error saving KYC data.";
            $errorcode = $api_error_code_class_call::$internalServerError;
            $maindata = [];
            $hint = ["Please try again later."];
            $linktosolve = "https://";
            $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        // --- Notify Telegram ---
        $utility_class_call::notifyTelegramKYC($fullname, $email, $username, $imagePaths, $user_id);

        // --- Success response ---
        $maindata = [];
        $text = "KYC submitted for review.";
        $api_status_code_class_call->respondOK($maindata, $text);

    } catch (Exception $e) {
        $text = "Internal server error.";
        $errorcode = $api_error_code_class_call::$internalServerError;
        $maindata = [];
        $hint = ["Please try again later."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode);
    }

} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for KYC submission."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
