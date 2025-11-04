<?php
header('Content-Type: application/json');

use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
        $user_pubkey = $decodedToken->usertoken;

        $userDetails = $api_users_table_class_call::getUserByPubKey($user_pubkey);
        if (!$userDetails) {
            $api_status_code_class_call->respondUnauthorized();
        }

        $user_id = $userDetails['id'];
        $email = $userDetails['email'];
        $username = $userDetails['username'];
        $fullname = $userDetails['lname'] . ' ' . $userDetails['fname'];

        $selfie = $_FILES['selfie'] ?? null;
        $govID = $_FILES['regulatory_image'] ?? null;
        $utility = $_FILES['utility_image'] ?? null;

        // Check user level
        if ($api_users_table_class_call::getUserLevel($user_id) != 2) {
            $api_status_code_class_call->respondBadRequest("User not eligible for KYC submission.");
        }

        // Validate image files
        $requiredFiles = ['Selfie' => $selfie, 'Gov ID' => $govID, 'Utility Bill' => $utility];
        $imagePaths = [];

        foreach ($requiredFiles as $label => $file) {
            $validate = $utility_class_call::validateImageUpload($file, $label);
            if (!$validate['success']) {
                $api_status_code_class_call->respondBadRequest($validate['message']);
            }
        }

        // Upload images
        $uploadLocations = [
            'selfie' => ['dir' => '/userpassport/', 'file' => $selfie],
            'regcard' => ['dir' => '/userregulatorycards/', 'file' => $govID],
            'utility' => ['dir' => '/userutility_bill/', 'file' => $utility],
        ];

        foreach ($uploadLocations as $key => $val) {
            $result = $utility_class_call::uploadImage(
                $val['file'],
                $val['dir'],
                $username
            );
            if (!$result['success']) {
                $api_status_code_class_call->respondBadRequest("Error uploading {$key} image.");
            }
            $imagePaths[$key] = $result['imagepath'];
        }

        // Update KYC table
        $updateSuccess = $api_users_table_class_call::submitKYC($user_id, $imagePaths);
        if (!$updateSuccess) {
            $api_status_code_class_call->respondInternalServerError([], "Error saving KYC data.");
        }

        // Notify via Telegram
        $utility_class_call::notifyTelegramKYC($fullname, $email, $username, $imagePaths, $user_id);

        $api_status_code_class_call->respondOK([], "KYC submitted for review.");
    } catch (Exception $e) {
        $api_status_code_class_call->respondInternalError($utility_class_call->get_details_from_exception($e));
    }
} else {
    $api_status_code_class_call->respondMethodNotAllowed([], "Invalid method. Use POST.");
}
