<?php
header('Content-Type: application/json');
use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $data = json_decode(file_get_contents('php://input'));

    $userid = ""; // Replace with the actual user ID (retrieve from authentication)
    if (isset($data->userid)) {
        $userid = $utility_class_call::escape($data->userid);
    }
    // Fetch unread notifications for the user
    $unreadNotifications = Utility_Functions::getUnreadNotifications($userid);

    // Mark fetched notifications as read
    Utility_Functions::markNotificationsAsRead($unreadNotifications);

    $response = [
        'status' => 'success',
        'data' => $unreadNotifications,
    ];

    echo json_encode($response);
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the GET method for fetch notifications."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>
