<?php
$folderPath = realpath(dirname(__DIR__));

require_once $folderPath . "/vendor/autoload.php";
// manual autoload to ignore case senstive
spl_autoload_register(function ($class) {
    // Define the directory where your class files are located
    $directory = realpath(dirname(__DIR__)); // Change this to your actual directory
    
    // Replace any backslashes with forward slashes in the class name
    $class = str_replace('\\', '/', $class);

    // print($class);
    // Split the class name into parts using the slash as a delimiter
    $parts = explode('/', $class);

    // Convert the first part to lowercase
   
    $parts[0] = strtolower($parts[0]);
    // $class1=$parts[0];
    // for($i=1;$i<count($parts);$i++){
    //     $class1.="/". ucfirst($parts[$i]);
    // }
    
    // Reconstruct the class name with modified first part
    $class = implode('/', $parts);

    // Construct an array of possible file paths based on class name variations
    $possibleFilePaths = [
        $directory . '/' . $class . '.php',          // Original class name
        // $directory . '/' . $class1 . '.php', // Capitalized first letter after /
        $directory . '/' . strtolower($class) . '.php', // All lowercase
        $directory . '/' . ucfirst($class) . '.php', // Capitalized first letter
    ];
    // print_r($possibleFilePaths);
    // Loop through possible file paths and include the first one that exists
    foreach ($possibleFilePaths as $file) {
        if (file_exists($file)) {
            include $file;
            return; // Class found and included, exit the loop
        }
    }
});
// Initialize an instance of the UtilsFunctions class
$utility_class_call = new Config\Utility_Functions;
$api_response_class_call = new Config\API_User_Response;
$api_status_code_class_call = new Config\API_Status_Code;
$api_error_code_class_call= new Config\API_Error_Code;
$api_users_table_class_call= new DatabaseCall\Users_Table;
$api_sns_email_class_call= new Config\Mail_SMS_Responses;

// Set the error and exception handlers using the instance of UtilsFunctions
error_reporting(E_ALL);
set_error_handler([$utility_class_call, 'errorHandler']);
set_exception_handler([$utility_class_call, 'exceptionHandler']);


// MTHOD YOU ARE TO USE INCLUDE POST,GET,PATCH, and DELETE

// SAVERTECH CODE STRUCTURE
// Seprate class file for Mail/sms to send
// Sperate class file for payment functions
// Seprate class file for error/error code to display to user
// Seprate class file for constant data
// Seprate class file for conection to db
// Seprate class file for db calls
// Seprate class file for utility functions
// Single function to show error in UI


?>