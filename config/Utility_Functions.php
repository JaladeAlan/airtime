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


    public static function getSubscriptionPlan($plan_id)
    {
        $conn = static::getDB();

        $query = "SELECT * FROM subscriptions WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $plan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $plan = $result->fetch_assoc();
        $stmt->close();

        return $plan;
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

    public static function takeAttendance($user_id, $mac_address)
    {
        $connect = static::getDB();
    
        $insertAttendance = $connect->prepare("INSERT INTO attendance (user_id, mac_address, attendance_time_in, attendance_date) VALUES (?, ?, ?, ?)");
    
        // Get the current date and time
        $attendance_date = date('Y-m-d');
        $attendance_time_in = date('H:i:s');
    
        // Specify the data types for the parameters
        $insertAttendance->bind_param("isss", $user_id, $mac_address, $attendance_time_in, $attendance_date);
    
        // Execute the insert query
        if ($insertAttendance->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the insert query fails (e.g., return false or an error code)
            return false;
        }
    }
   
    public static function takeAttendanceOut($user_id)
        {
            $connect = static::getDB();
        
            $attendance_date = date('Y-m-d');
            $attendance_time_out = date('H:i:s');
        
            // Use an SQL UPDATE statement to update the existing record
            $updateAttendanceOut = $connect->prepare("UPDATE attendance SET attendance_time_out = ? WHERE attendance_date = ? AND user_id = ?");
        
            // Bind parameters and values
            $updateAttendanceOut->bind_param("ssi", $attendance_time_out, $attendance_date, $user_id);
        
            // Execute the update query
            if ($updateAttendanceOut->execute()) {
                // Return true to indicate success
                return true;
            } else {
                // Handle the case where the update query fails (e.g., return false or an error code)
                return false;
            }
        }
    
     public static function startFreeTrial($company_name)
        {
            // Input type checks if it's from a post request or just a normal function call
            $connect = static::getDB();
        
            // Calculate the end date of the free trial (7 days from the current date)
            $trialStartDate = date('Y-m-d', strtotime('+7 days'));
            $trialEndDate = date('Y-m-d', strtotime('+7 days'));
        
            // Prepare the SQL statement for updating the subscription end date
            $startFreeTrial = $connect->prepare("UPDATE companies SET subscription_end_date = ? WHERE company_name = ?");
        
            // Bind parameters and values
            $startFreeTrial->bind_param("ss",  $trialStartDate, $trialEndDate, $company_name);
        
            // Execute the update query
            if ($startFreeTrial->execute()) {
                // Return true to indicate success
                return true;
            } else {
                // Handle the case where the update query fails (e.g., return false or an error code)
                return false;
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

   public static function validateAttendanceLocation($latitude, $longitude, $validLatitude, $validLongitude)
{
    $latitude = floatval($latitude);
    $longitude = floatval($longitude);
    $validLatitude = floatval($validLatitude);
    $validLongitude = floatval($validLongitude);

    $allowedDistance = 100; // in meters
    $distance = self::calculateDistance($latitude, $longitude, $validLatitude, $validLongitude);

    return $distance <= $allowedDistance;
}


    public static function getUserAttendanceByDateRange($userid, $startDate, $endDate)
    {
        $conn = static::getDB();

        $query = "SELECT attendance_date, attendance_status FROM attendance WHERE user_id = ? AND attendance_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $userid, $startDate, $endDate);
        $stmt->execute();

        $result = $stmt->get_result();

        $attendanceData = [];
        while ($row = $result->fetch_assoc()) {
            $attendanceData[$row['attendance_date']] = $row['attendance_status'];
        }

        $stmt->close();
        
        return $attendanceData;
    }

    public static function addNotification($userid, $message)
{
    $conn = static::getDB();

    $query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userid, $message);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}


public static function getUnreadNotifications($userid)
{
    $conn = static::getDB();

    $query = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userid);
    $stmt->execute();

    $result = $stmt->get_result();

    $unreadNotifications = [];
    while ($row = $result->fetch_assoc()) {
        $unreadNotifications[] = $row;
    }

    $stmt->close();

    return $unreadNotifications;
}

public static function markNotificationsAsRead($notifications)
{
    $conn = static::getDB();

    // Check if there are notifications to mark as read
    if (empty($notifications)) {
        return; // No notifications to mark as read, so we can return early.
    }

    // Create an array of IDs from the $notifications array
    $notificationIds = array_map(function ($notification) {
        return $notification['id'];
    }, $notifications);

    // Generate placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($notificationIds), '?'));

    // Prepare the SQL query
    $query = "UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders)";

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param(str_repeat('i', count($notificationIds)), ...$notificationIds);
        $stmt->execute();
        $stmt->close();
    }
}


    


public static function getAttendanceRecords($user_id, $start_date, $end_date)
{
    $conn = static::getDB();

    $query = "SELECT * FROM attendance WHERE user_id = ? AND attendance_time_in BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance_records = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $attendance_records;
}


public static function takeAttendanceWithLocation($user_id, $latitude, $longitude)
{
    $conn = static::getDB();

    // Implement your location validation logic here (e.g., comparing with a predefined valid location)

    $is_valid_location = true; // Set to false if the location is invalid

    $query = "INSERT INTO attendance (user_id, check_in_time, latitude, longitude, is_valid_location) VALUES (?, NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iddi", $user_id, $latitude, $longitude, $is_valid_location);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}


public static function createSubscription($company_id, $plan_id, $start_date, $end_date)
{
    $conn = static::getDB();

    $query = "INSERT INTO subscriptions (company_id, plan_id, start_date, end_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $company_id, $plan_id, $start_date, $end_date);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

public static function getAdminSubscription($company_id)
{
    $conn = static::getDB();

    $query = "SELECT * FROM subscriptions WHERE company_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subscription = $result->fetch_assoc();
    $stmt->close();

    return $subscription;
}

public static function calculateSubscriptionEndDate($start_date, $duration) {
    $end_date = null;
    
    switch ($duration) {
        case 'monthly':
            $end_date = date('Y-m-d', strtotime($start_date . ' + 1 month'));
            break;
        case 'quarterly':
            $end_date = date('Y-m-d', strtotime($start_date . ' + 3 months'));
            break;
        case 'biannually':
            $end_date = date('Y-m-d', strtotime($start_date . ' + 6 months'));
            break;
        case 'annually':
            $end_date = date('Y-m-d', strtotime($start_date . ' + 1 year'));
            break;
    }
    
    return $end_date;
}
    


public static function getAttendanceByUserAndDepartment($name, $department)
{
    $conn = static::getDB();

    $query = "SELECT users.name, users.department, attendance.mac_address, attendance.attendance_time_in, attendance.attendance_time_out
              FROM attendance
              INNER JOIN users ON attendance.user_id = users.id
              WHERE users.name = ? AND users.department = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $name, $department);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance_records = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $attendance_records;
}

     public static function adminLogout($userPubkey) {
       
        $admin = Users_Table::getAdminUserByKey($userPubkey);
        if ($admin) {
            Users_Table::updateAdminUserStatus($admin['id'], 'logged_out');
        }
    }




private static function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $distance = ($miles * 1.609344) * 1000; // Convert to meters
    return $distance;
}


}


?>
