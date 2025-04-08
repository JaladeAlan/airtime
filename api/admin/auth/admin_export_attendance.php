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
    
    $admin_id = $api_users_table_class_call::checkIfIsAdmin($user_pubkey);

    // Unauthorized user
    if ( !$admin_id ){
        $text = $api_response_class_call::$unauthorized_token;
        $errorcode = $api_error_code_class_call::$internalHackerWarning;
        $maindata = [];
        $hint = ["Only admins can export."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode);
    }
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $department = isset($_POST['department']) ? $_POST['department'] : '';

    if (!empty($name) && !empty($department)) {
        // Ensure that the admin has the authority to access this user's data (implement your own authorization logic)

        $attendance_records = $utility_class_call::getAttendanceByUserAndDepartment($name, $department);

        // Create and output CSV
        header('Content-Disposition: attachment; filename="attendance_export.csv"');

        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, array('Name', 'Department', 'Check In Time', 'Check Out Time', 'Is Valid Location'));

        // Add attendance records to CSV
        foreach ($attendance_records as $record) {
            fputcsv($output, $record);
        }

        fclose($output);
    } else {
        $maindata = [];
        $text = $api_response_class_call::$invalidDataSent;
        $hint = ["'User ID, start date, and end date are required parameters."];
        $linktosolve = "https://";
        $api_status_code_class_call->respondOK($maindata, $text);
    }
} else {
   // Handle invalid HTTP method
   $text = $api_response_class_call::$methodUsedNotAllowed;
   $errorcode = $api_error_code_class_call::$internalHackerWarning;
   $maindata = [];
   $hint = ["Ensure to use the POST method for exporting."];
   $linktosolve = "https://";
   $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}

?>
