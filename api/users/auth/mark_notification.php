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

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;

    $data = json_decode(file_get_contents("php://input"));
    $notification_id =  $utility_class_call::inputData($data, 'notification_id');

    if ($notification_id) {
        // Mark single notification as read
        $updated = $utility_class_call::markNotificationAsRead($notification_id, $user_pubkey);
        if ($updated) {
            $text = "Notification is marked as read.";
            $maindata = [];
            $api_status_code_class_call->respondOK($maindata, $text);
        } else {
            $api_status_code_class_call->respondBadRequest([], "Notification not found or not owned by user.", [], "https://", 404);
        }
    } else {
        // Mark all notifications as read
        $updated = $utility_class_call::markAllNotificationsAsRead($user_pubkey);
        if ($updated) {
            $text = "All notifications marked as read.";
            $maindata = [];
            $api_status_code_class_call->respondOK($maindata, $text);
        } else {
            $api_status_code_class_call->respondBadRequest([], "Failed to update notifications.", [], "https://", 500);
        }
    }
    exit;
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the PATCH method for marking notifications as read."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
