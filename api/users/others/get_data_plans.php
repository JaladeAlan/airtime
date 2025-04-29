<?php
header('Content-Type: application/json');
use Config\Utility_Functions;
use Config\ThirdParties_Functions;

require_once '../../../config/bootstrap_file.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decodedToken = $api_status_code_class_call->ValidateAPITokenSentIN();
    $user_pubkey = $decodedToken->usertoken;
    try {
        
        $data = json_decode(file_get_contents("php://input"));

        $networkId = $utility_class_call::inputData($data, 'network_id');
        $dataType = $utility_class_call::inputData($data, 'data_type');
       
        if ($utility_class_call::validate_input($networkId) || $utility_class_call::validate_input($dataType)) {
         $text = $api_response_class_call::$invalidInfo;
         $errorcode = $api_error_code_class_call::$internalUserWarning;
         $maindata = [];
         $hint = ["Ensure to send valid data to the API fields."];
         $linktosolve = "https://";
         $api_status_code_class_call->respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode);
        }

        // Fetch networks from the third-party API
        $maindata = ThirdParties_Functions::fetchFromAutoPilotAPI(
            endpoint: "load/data",
            payload: ['networkId' => $networkId, 'dataType' => $dataType]
        );

        if (!$maindata) { 
            $api_status_code_class_call->respondInternalServerError("Failed to fetch data from the third-party API.");
            exit;
        }
        $api_status_code_class_call->respondOK($maindata, "Data fetched successfully.");
    } catch (\Exception $e) {
        $api_status_code_class_call->respondInternalServerError("An error occurred: " . $e->getMessage());
    }
} else {
    $text = $api_response_class_call::$methodUsedNotAllowed;
    $errorcode = $api_error_code_class_call::$internalHackerWarning;
    $maindata = [];
    $hint = ["Ensure to use the POST method for getting data plans."];
    $linktosolve = "https://";
    $api_status_code_class_call->respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode);
}
?>