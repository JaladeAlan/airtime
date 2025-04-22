<?php

namespace DatabaseCall;


use Config;
    use Config\Utility_Functions;
    use Config\Constants;
    use Config\DB_Connect;
/**
 * Post model
 *
 * PHP version 5.4
 */
class Users_Table extends Config\DB_Connect
{
    /**
     * Get all the posts as an associative array
     *
     * @return array
     */

    /*
    If a data is not needed send empty to it, bank name and namk code should be join as bankname^bankcode

     */
    // APi functions
    public static function insertUser($username, $password, $email, $firstname, $lastname, $accountno, $bankname, $phone, $referrer)
    {
        $connect = static::getDB();

        // Generate a unique public key for the user
        $user_pub_key = Utility_Functions::generateUniquePubKey("users", "pub_key");

        // Generate a unique referral code
        $referral_code = self::generateUniqueReferralCode($connect);

        // Prepare the SQL statement with referral_code included
        $insertUser = $connect->prepare("
            INSERT INTO users 
            (username, user_password, email, first_Name, last_Name, account_No, bankname, phoneNo, referred_by, pub_key, referral_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insertUser->bind_param("sssssssssss", $username, $password, $email, $firstname, $lastname, $accountno, $bankname, $phone, $referrer, $user_pub_key, $referral_code);

        if ($insertUser->execute()) {
            return $connect->insert_id;
        } else {
            return false;
        }
    }

        private static function generateUniqueReferralCode($connect, $length = 8)
        {
            do {
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $code = '';
                for ($i = 0; $i < $length; $i++) {
                    $code .= $characters[random_int(0, strlen($characters) - 1)];
                }

                $stmt = $connect->prepare("SELECT id FROM users WHERE referral_code = ?");
                $stmt->bind_param("s", $code);
                $stmt->execute();
                $stmt->store_result();
            } while ($stmt->num_rows > 0);

            return $code;
        }

    public static function removeUser($email)
        {
            // Input type checks if it's from post request or just a normal function call
            $connect = static::getDB();

            // Prepare the SQL statement for removing a user
            $removeUser = $connect->prepare("DELETE FROM users WHERE email = ?");

            // Bind the username parameter
            $removeUser->bind_param("s", $email);

            // Execute the delete query
            if ($removeUser->execute()) {
                // Return true if the user was successfully removed
                return true;
            } else {
                // Handle the case where the delete query fails (e.g., return an error code or message)
                return false;
            }
        }
        
    public static function insertAdminUser($password, $email, $fullname)
        {
            // Input type checks if its from post request or just normal function call
            $connect = static::getDB();

            // Prepare the SQL statement for inserting a new user
            $insertAdminUser = $connect->prepare("INSERT INTO admins (ad_password, email, fullname) VALUES (?, ?, ?)");

            // Bind parameters and values
            $insertAdminUser->bind_param("sss", $password, $email, $fullname);

            // Execute the insert query
            if ($insertAdminUser->execute()) {
                // Return the ID of the newly inserted user
                return $connect->insert_id;
            } else {
                // Handle the case where the insert query fails (e.g., return an error code or message)
                return false;
            }
        }
   
    public static function getUserByUsername($username= "",$data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM users WHERE username = ?");
            $checkdata->bind_param("s", $username);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;

        }

    public static function checkIfIsAdmin($user_pubkey, $data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM admins WHERE pub_key = ?");
            $checkdata->bind_param("s", $user_pubkey);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;

        }
        
        public static function checkIfIsUser($pubKey)
        {
            // Input type checks if it's from a post request or just a normal function call
            $connect = static::getDB();
            $alldata = [];
        
            $checkdata = $connect->prepare("SELECT * FROM users WHERE pub_key = ?");
            $checkdata->bind_param("s", $pubKey);  // Bind the parameter and specify its type (s for string)
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
        
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
        
            return $alldata;
        }
     
    public static function getUserByEmail($email= "",$data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM users WHERE email = ?");
            $checkdata->bind_param("s", $email);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;

        }
    public static function getUserByKey($pubkey= "",$data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM users WHERE pub_key = ?");
            $checkdata->bind_param("s", $pubkey);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;

        }
    public static function getAdminUserByKey($pubkey= "",$data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM admins WHERE pub_key = ?");
            $checkdata->bind_param("s", $pubkey);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;

        }
  
    public static function getAdminUserByUsername($admin_id= "",$data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM admins WHERE admin_id = ?");
            $checkdata->bind_param("i", $admin_id);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;

        }
    public static function getAdminUserByEmail($email= "",$data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM admins WHERE email = ?");
            $checkdata->bind_param("s", $email);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;

        }
   
        public static function getUserByIdOrEmail($user_id= ""){
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];
    
            $checkdata = $connect->prepare("SELECT  * FROM users WHERE user_id = ? || email=?");
            $checkdata->bind_param("ss", $user_id, $user_id);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;
    
        }
        public static function getAdminUserByIdorEmail($admin_id = ""){
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];
    
            $checkdata = $connect->prepare("SELECT  * FROM admins WHERE admin_id = ? || email=?");
            $checkdata->bind_param("ss", $admin_id, $admin_id);
            $checkdata->execute();
            $getresultemail = $checkdata->get_result();
            if ($getresultemail->num_rows > 0) {
                $getthedata = $getresultemail->fetch_assoc();
                $alldata = $getthedata;
            }
            return $alldata;
    
        }
 
    public static function editUserProfile($user_id, $fullname, $department, $gender)
        {
            // Input type checks if it's from a post request or just a normal function call
            $connect = static::getDB();
        
            //update the user's profile
            $updateProfile = $connect->prepare("UPDATE users SET fullname = ?, department = ?, gender = ? WHERE user_id = ?");
            $updateProfile->bind_param("sssi", $fullname, $department, $gender, $user_id);
        
            // Execute the SQL statement
            if ($updateProfile->execute()) {
                // The update was successful
                return true;
            } else {
                // The update failed
                return false;
            }
        }
    public static function updatePassword($user_pubkey, $hashPassword)
        {
            // Input type checks if it's from post request or just a normal function call
            $connect = static::getDB();
                    
        
            // Prepare the SQL statement for updating the user's password
            $updatePassword = $connect->prepare("UPDATE users SET user_password = ? WHERE pub_key = ?");
        
            // Bind parameters and values
            $updatePassword->bind_param("ss", $hashPassword, $user_pubkey);
        
            // Execute the update query
            if ($updatePassword->execute()) {
                // Return true if the password was successfully updated
                return true;
            } else {
                // Handle the case where the update query fails (e.g., return an error code or message)
                return false;
            }
        }
    public static function updateAdminPassword($user_pubkey, $hashPassword)
        {
            // Input type checks if it's from post request or just a normal function call
            $connect = static::getDB();
                    
        
            // Prepare the SQL statement for updating the user's password
            $updatePassword = $connect->prepare("UPDATE admins SET ad_password = ? WHERE pub_key = ?");
        
            // Bind parameters and values
            $updatePassword->bind_param("ss", $hashPassword, $user_pubkey);
        
            // Execute the update query
            if ($updatePassword->execute()) {
                // Return true if the password was successfully updated
                return true;
            } else {
                // Handle the case where the update query fails (e.g., return an error code or message)
                return false;
            }
        }
            
    public static function storeResetCode($code, $expiry, $email)
            {
                $connect = static::getDB();

                // Prepare the SQL statement for updating the user profile with a reset code
                $storeResetCode = $connect->prepare("UPDATE users SET passwordResetCode = ?, passwordResetCodeExpires = ? WHERE email = ?");

                // Bind parameters and values
                $storeResetCode->bind_param("sss", $code, $expiry, $email);

                // Execute the update query
                if ($storeResetCode->execute()) {
                    // Return true to indicate success
                    return true;
                } else {
                    // Handle the case where the update query fails (e.g., return false or an error code)
                    return false;
                }
            }
    public static function getStoredResetCode($email)
            {
                $connect = static::getDB();
            
                // Prepare the SQL statement for fetching the reset code and expiration time
                $getResetCode = $connect->prepare("SELECT passwordResetCode FROM users WHERE email = ?");
            
                // Bind the email parameter
                $getResetCode->bind_param("s", $email);
            
                // Execute the query
                $getResetCode->execute();
            
                // Get the result
                $result = $getResetCode->get_result();
            
                // Check if a row is found
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // Return the stored reset code
                    return $row['reset_code'];
                } else {
                    // Return null or handle the case where the email is not found
                    return null;
                }
            }

            
    public static function storeAdminResetCode($code, $expiry, $email)
            {
                $connect = static::getDB();

                // Prepare the SQL statement for updating the user profile with a reset code
                $storeResetCode = $connect->prepare("UPDATE admins SET reset_code = ?, reset_code_expires_at = ? WHERE email = ?");

                // Bind parameters and values
                $storeResetCode->bind_param("sss", $code, $expiry, $email);

                // Execute the update query
                if ($storeResetCode->execute()) {
                    // Return true to indicate success
                    return true;
                } else {
                    // Handle the case where the update query fails (e.g., return false or an error code)
                    return false;
                }
            } 
    public static function getAdminStoredResetCode($email)
            {
                $connect = static::getDB();
            
                // Prepare the SQL statement for fetching the reset code and expiration time
                $getResetCode = $connect->prepare("SELECT reset_code FROM admins WHERE email = ?");
            
                // Bind the email parameter
                $getResetCode->bind_param("s", $email);
            
                // Execute the query
                $getResetCode->execute();
            
                // Get the result
                $result = $getResetCode->get_result();
            
                // Check if a row is found
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // Return the stored reset code
                    return $row['reset_code'];
                } else {
                    // Return null or handle the case where the email is not found
                    return null;
                }
            }
            
       
}    