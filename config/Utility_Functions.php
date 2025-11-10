<?php

namespace Config;

use DatabaseCall\Users_Table;

/**
 * View 
 *
 * PHP version 5.4
 */
class Utility_Functions  extends DB_Connect
{
    public static function escape($data)
    {
        $conn = static::getDB();
        $input = $data;
        // This removes all the HTML tags from a string. This will sanitize the input string, and block any HTML tag from entering into the database.
        // filter_var($geeks, FILTER_SANITIZE_STRING);
        $input = htmlspecialchars($input);
        $input = trim($input, " \t\n\r");
        // htmlspecialchars() convert the special characters to HTML entities while htmlentities() converts all characters.
        // Convert the predefined characters "<" (less than) and ">" (greater than) to HTML entities:
        $input = htmlspecialchars($input, ENT_QUOTES,'UTF-8');
        // prevent javascript codes, Convert some characters to HTML entities:
        $input = htmlentities($input, ENT_QUOTES, 'UTF-8');
        $input = stripslashes(strip_tags($input));
        $input = mysqli_real_escape_string($conn, $input);

        return $input;
    }
   
    public static function validateEmail($email) {
        if ( filter_var($email, FILTER_VALIDATE_EMAIL) ){
            return true;
        }else{
            return false;
        }
    }

    public static function validatePassword($password){
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\?]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
           return false;
        }else{
            return true;
}

    }
    public static function generateShortKey($strength){
        $input = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $output = static::generate_string($input, $strength);

        return $output;
    }
    public static function generate_string($input, $strength){
        $input_length = strlen($input);
        $random_string = '';
        for ($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
    
        return $random_string;
    }
    public static function checkIfCodeisInDB($tableName, $field ,$pubkey) {
        $connect = static::getDB();
        $alldata = [];
        // Check if the email or phone number is already in the database
        $query = "SELECT $field FROM $tableName WHERE $field = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("s", $pubkey);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            return true;
        }

        return false;
        
    }
    public static function generateUniqueShortKey($tableName, $field){
        $loop = 0;
        while ($loop == 0){
            $userKey = "SVT".static::generateShortKey(5);
            if ( static::checkIfCodeisInDB($tableName, $field ,$userKey) ){
                $loop = 0;
            }else {
                $loop = 1;
                break;
            }
        }

        return $userKey;
    }
    
    public static function generatePubKey($strength){
        $input = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $output = static::generate_string($input, $strength);

        return $output;
    }
    public static function generateUniquePubKey($tableName, $field){
        //add role to your generate function
        //if user (checkIfPubKeyisInDB), return $userkey
        //else if admin (checkIfIsAdmin), return $adminkey
        //else (checkIfPubKeyisInDB), so we wont edit all api
        $loop = 0;
        while ($loop == 0){
            $userKey = "SVT".static::generatePubKey(37). $tableName;
            if ( static::checkIfCodeisInDB($tableName,$field,$userKey) ){
                $loop = 0;
            }else {
                $loop = 1;
                break;
            }
        }

        return $userKey;
    }
    public static function getCurrentFullURL(){
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
        // Get the server name and port
        $servername = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        // Get the path to the current script
        $path = $_SERVER['PHP_SELF'];
        // Combine the above to form the full URL
        $endpoint = $protocol . $servername . ":" . $port . $path;
        return $endpoint;
    }
    public static function validate_input($data)
    {
        $incorrectdata=false;
        if(strlen($data)==0){
            $incorrectdata=true;
        }else if($data==null){
            $incorrectdata=true;
        }else if(empty($data)){
            $incorrectdata=true;
        }

        return $incorrectdata;
    }

    public static function Password_encrypt($password){
        $BlowFish_Format="$2y$10$";
        $salt_len=24;
        $salt= static::Get_Salt($salt_len);
        $the_format=$BlowFish_Format . $salt;
        
        $hash_pass=crypt($password, $the_format);
        return $hash_pass;
    }
   
    public static  function Get_Salt($size){
        $Random_string= md5(uniqid(mt_rand(), true));
        
        $Base64_String= base64_encode($Random_string);
        
        $change_string=str_replace('+', '.', $Base64_String);
        
        $salt=substr($change_string, 0, $size);
        
        return $salt;
    }
   
    public static function inputData($data, $key) {
        return isset($data->$key) ? self::escape($data->$key) : "";
    }   
   
    public static  function greetUsers(){
        $welcome_string="Welcome!";
        $numeric_date=date("G");

        //Start conditionals based on military time
        if($numeric_date>=0&&$numeric_date<=11)
        $welcome_string="🌅 Good Morning";
        else if($numeric_date>=12&&$numeric_date<=17)
        $welcome_string="☀️ Good Afternoon";
        else if($numeric_date>=18&&$numeric_date<=23)
        $welcome_string="😴 Good Evening";

        return $welcome_string;
    }
    public static function exceptionHandler($exception)
    {
        // Code is 404 (not found) or 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500; 
        }
        http_response_code($code);

        $error = error_get_last();
        $errno   ="";
        $errfile = "";
        $errline = "";
        $errstr  = "";
        if ($error !== null) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];
        }
 
        if (Constants::SHOW_ERRORS) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();
            $message .=  "\nOTHER ERRORS'" .$errno." ".$errfile." ".$errline." ". $errstr;

            error_log($message);
        }
    }
    public static function errorHandler($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) {  // to keep the @ operator working
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }
  
    public static function updateUserProfile($new_name, $new_email, $new_deparment, $new_description, $user_pubkey)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE users SET fullname = ?, email = ?, department = ?, job_description = ? WHERE pub_key = ?");

        
        // Bind parameters and values
        $updateProfile->bind_param("sssss", $new_name, $new_email, $new_deparment,  $new_description, $user_pubkey);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }

    public static function adminUpdateUserProfile($newName, $newEmail, $newDepartment, $newGender, $newStatus, $user_pubkey)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE users SET fullname = ?, email = ?, department = ?, gender = ?,status = ? WHERE pub_key = ?");

      
        // Bind parameters and values
        $updateProfile->bind_param("ssssss", $newName, $newEmail, $newDepartment, $newGender, $newStatus, $user_pubkey);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }
    public static function updateAdminProfilePic($photopath, $email)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE admins SET profile_pic = ? WHERE email = ?");

      
        // Bind parameters and values
        $updateProfile->bind_param("ss", $photopath, $email);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }
    public static function updateUserProfilePic($photopath, $email)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE users SET profile_pic = ? WHERE email = ?");

      
        // Bind parameters and values
        $updateProfile->bind_param("ss", $photopath, $email);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }

    public static function restrictUser($userid)
    {
        $conn = static::getDB();
        $query = "UPDATE users SET status = 0 WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        // Bind the parameter to the statement
        $stmt->bind_param("s", $userid);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
    public static function restrictAdmin($userid)
    {
        $conn = static::getDB();
        $query = "UPDATE admins SET status = 0 WHERE company_id = ?";
        $stmt = $conn->prepare($query);
        // Bind the parameter to the statement
        $stmt->bind_param("s", $userid);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public static function addNotification($user_pubkey, $title, $message, $type = 'info')
    {
        $connect = static::getDB();
    
        $query = $connect->prepare("INSERT INTO notifications (user_pubkey, title, message, type) VALUES (?, ?, ?, ?)");
        $query->bind_param("ssss", $user_pubkey, $title, $message, $type);
    
        return $query->execute();
    }
    
    public static function addMessage($userid, $message, $email)
    {
        $conn = static::getDB();

        $query = "INSERT INTO messages (user_id, message, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $userid, $message, $email);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public static function getAdminMessages($email)
    {
        $conn = static::getDB();

        $query = "SELECT * FROM messages WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        $unreadNotifications = [];
        while ($row = $result->fetch_assoc()) {
            $unreadNotifications[] = $row;
        }

        $stmt->close();

        return $unreadNotifications;
    }
  
    public static function getUserNotifications($user_pubkey)
    {
        $connect = static::getDB();
        $notifications = [];
    
        $stmt = $connect->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_pubkey = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $user_pubkey);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
            $row['is_read'] = (bool) $row['is_read']; 
            $notifications[] = $row;
        }
    
        return $notifications;
    }
    
    public static function markNotificationAsRead($notification_id, $user_pubkey)
    {
        $connect = static::getDB();
        $query = $connect->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_pubkey = ? AND is_read = 0");
        $query->bind_param("is", $notification_id, $user_pubkey);
        $query->execute();
    
        return $query->affected_rows > 0;
    }
    

    public static function markAllNotificationsAsRead($user_pubkey)
    {
        $connect = static::getDB();
        $query = $connect->prepare("UPDATE notifications SET is_read = 1 WHERE user_pubkey = ? AND is_read = 0");
        $query->bind_param("s", $user_pubkey);
        $query->execute();
    
        return $query->affected_rows > 0;
    }
    
    
    public static function deleteNotification($notification_id, $user_pubkey)
    {
        $connect = static::getDB();
        $stmt = $connect->prepare("DELETE FROM notifications WHERE id = ? AND user_pubkey = ?");
        $stmt->bind_param("is", $notification_id, $user_pubkey);
        return $stmt->execute();
    }

    // public static function adminLogout($userPubkey) {
        
    //         $admin = Users_Table::getAdminUserByKey($userPubkey);
    //         if ($admin) {
    //             Users_Table::updateAdminUserStatus($admin['id'], 'logged_out');
    //         }
    //     }
    
   public static function notifyTelegramKYC($fullname, $email, $username, $imagePaths, $user_id)
{
    $baseUrl = "https://testairtime.infy.uk";
    $botToken = "8123455910:AAFpus_vOT6zRYfYhHV1oZ1QW9nCO2dxQkI";
    $chatId = "1062760501";

    // Escape function for MarkdownV2
    $escape = function ($text) {
        return preg_replace('/([\\_*\\[\\]()~`>#+=|{}.!-])/', '\\\\$1', $text);
    };

    $message = "🚨 *New KYC Submission* 🚨\n\n";
    $message .= "*Full Name:* " . $escape($fullname) . "\n";
    $message .= "*Email:* " . $escape($email) . "\n";
    $message .= "*Username:* @" . $escape($username) . "\n";
    $message .= "*User ID:* " . $escape($user_id) . "\n";
    $message .= "*Images Submitted:*\n";
    $message .= "\\- [Selfie](" . $baseUrl . $imagePaths['selfie'] . ")\n";
    $message .= "\\- [Regulatory Card](" . $baseUrl . $imagePaths['regcard'] . ")\n";
    $message .= "\\- [Utility Bill](" . $baseUrl . $imagePaths['utility'] . ")\n";

    $inline_keyboard = [
        [
            ['text' => '✅ Approve', 'callback_data' => 'approve|' . $username],
            ['text' => '❌ Reject', 'callback_data' => 'reject|' . $username],
        ]
    ];

    $payload = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'MarkdownV2',
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode(['inline_keyboard' => $inline_keyboard])
    ];

    $ch = curl_init("https://api.telegram.org/bot" . $botToken . "/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents("kyc_telegram_log.txt", $response);
}


  public static function handleKYCApprovalRejection($callback_data, $username) {
    global $api_users_table_class_call;

    // Define log file path
    $log_file = __DIR__ . '/kyc_errors.txt';

    if ($callback_data == 'approve') {
        // Update the KYC status to Verified (2)
        $update_kyc_status = $api_users_table_class_call::updateKYCStatus($username, 2);

        if ($update_kyc_status) {
            return [
                'status' => 'success',
                'message' => 'KYC has been approved!'
            ];
        } else {
            file_put_contents($log_file, "[".date('Y-m-d H:i:s')."] Failed to approve KYC for user: $username\n", FILE_APPEND);
            return [
                'status' => 'error',
                'message' => 'Failed to approve KYC.'
            ];
        }
    } elseif ($callback_data == 'reject') {
        // Update the KYC status to Not Submitted (0)
        $update_kyc_status = $api_users_table_class_call::updateKYCStatus($username, 0);

        if ($update_kyc_status) {
            return [
                'status' => 'success',
                'message' => 'KYC has been rejected.'
            ];
        } else {
            file_put_contents($log_file, "[".date('Y-m-d H:i:s')."] Failed to reject KYC for user: $username\n", FILE_APPEND);
            return [
                'status' => 'error',
                'message' => 'Failed to reject KYC.'
            ];
        }
    } else {
        file_put_contents($log_file, "[".date('Y-m-d H:i:s')."] Invalid KYC action '$callback_data' for user: $username\n", FILE_APPEND);
        return [
            'status' => 'error',
            'message' => 'Invalid action.'
        ];
    }
}

    // Function to send a message to Telegram user
    public static function sendTelegramMessage($chat_id, $message) {
        $telegram_api_url = "https://api.telegram.org/bot8123455910:AAFpus_vOT6zRYfYhHV1oZ1QW9nCO2dxQkI/sendMessage";
        $params = [
            'chat_id' => $chat_id,
            'text' => $message
        ];

        // Send the request to Telegram API to send the message
        file_get_contents($telegram_api_url . '?' . http_build_query($params));
    }

    // Validate uploaded image
    public static function validateImageUpload($file, $label)
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => "{$label} not uploaded correctly."
            ];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'message' => "{$label} must be a JPG or PNG image."
            ];
        }

        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'message' => "{$label} must not exceed 5MB."
            ];
        }

        return ['success' => true];
    }

    // Upload image and return saved path
    public static function uploadImage($file, $directory, $username)
    {
        // Define upload directory
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads' . $directory;

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $username . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'success' => true,
                'imagepath' => '/uploads' . $directory . $filename
            ];
        }

        return [
            'success' => false,
            'message' => "Failed to upload {$file['name']}"
        ];
    }

}

    ?>
