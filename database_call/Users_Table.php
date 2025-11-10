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
        
    public static function insertSender($email, $name, $phone, $pubkey)
        {
            // Input type checks if its from post request or just normal function call
            $connect = static::getDB();

            // Prepare the SQL statement for inserting a new user
            $insertSender = $connect->prepare("INSERT INTO senders (sender_email, sender_name, sender_phoneNo, user_pubkey) VALUES (?, ?, ?, ?)");

            // Bind parameters and values
            $insertSender->bind_param("ssss", $email, $name, $phone, $pubkey);

            // Execute the insert query
            if ($insertSender->execute()) {
                // Return the ID of the newly inserted user
                return $connect->insert_id;
            } else {
                // Handle the case where the insert query fails (e.g., return an error code or message)
                return false;
            }
        }

        public static function insertTransaction($user_pubkey, $amount, $details, $reference, $type)
        {
            // Input type checks if it's from a post request or just a normal function call
            $connect = static::getDB();

            // Prepare the SQL statement for inserting a new transaction
            $insertTransaction = $connect->prepare("INSERT INTO transactions 
                (user_pubkey, transaction_Status, transaction_amount, transaction_details, transaction_reference, transaction_type) 
                VALUES (?, ?, ?, ?, ?, ?)");

            // Set the initial status as 'pending'
            $transactionStatus = 'completed';

            // Bind parameters and values
            $insertTransaction->bind_param("ssssss", $user_pubkey, $transactionStatus, $amount, $details, $reference, $type);

            // Execute the insert query
            if ($insertTransaction->execute()) {
                // Return the ID of the newly inserted transaction
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
        
        public static function getSenderByKey($pubKey, $email, $phone)
        {
            $connect = static::getDB();
        
            $query = "SELECT id FROM senders     WHERE user_pubkey = ? AND (sender_email = ? OR sender_phoneNo = ?)";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("sss", $pubKey, $email, $phone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
        }
   
        public static function getUserByPhone($phone= "",$data="*")
        {
            //input type checks if its from post request or just normal function call
            $connect = static::getDB();
            $alldata = [];

            $data = is_string($data) ? $data : "*";
            $checkdata = $connect->prepare("SELECT $data FROM users WHERE phoneNo = ?");
            $checkdata->bind_param("s", $phone);
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
      
        public static function updatePin($user_pubkey, $pin)
        {
            // Input type checks if it's from post request or just a normal function call
            $connect = static::getDB();
                    
        
            // Prepare the SQL statement for updating the user's pin
            $updatePin = $connect->prepare("UPDATE users SET transaction_Pin = ? WHERE pub_key = ?");
        
            // Bind parameters and values
            $updatePin->bind_param("ss", $pin, $user_pubkey);
        
            // Execute the update query
            if ($updatePin->execute()) {
                // Return true if the password was successfully updated
                return true;
            } else {
                // Handle the case where the update query fails (e.g., return an error code or message)
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
                $getResetCode = $connect->prepare("SELECT passwordResetCode AS reset_code FROM users WHERE email = ?");
            
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

            public static function storeOtp($receiver = "", $otp = "", $type = "", $expiryInMinutes = 5) 
            {
                $connect = static::getDB();
            
                $query = "
                    REPLACE INTO otps (receiver, otp, type, created_node -vat, expires_at, is_used)
                    VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? MINUTE), 0)
                ";
                $stmt = $connect->prepare($query);
                $stmt->bind_param("sssi", $receiver, $otp, $type, $expiryInMinutes);
                $stmt->execute();
            }       

            public static function verifyOtp($receiver = "", $otp = "", $type = "", $data = "*") 
            {
                $connect = static::getDB();
                $alldata = [];
            
                $data = is_string($data) ? $data : "*";
            
                $stmt = $connect->prepare("
                    SELECT $data FROM otps 
                    WHERE receiver = ? AND otp = ? AND type = ? 
                    AND expires_at >= NOW() AND is_used = 0
                ");
                $stmt->bind_param("sss", $receiver, $otp, $type);
                $stmt->execute();
                $result = $stmt->get_result();
            
                if ($result->num_rows > 0) {
                    $alldata = $result->fetch_assoc();
            
                    // Mark OTP as used
                    $updateStmt = $connect->prepare("UPDATE otps SET is_used = 1 WHERE id = ?");
                    $updateStmt->bind_param("i", $alldata['id']);
                    $updateStmt->execute();
                }
            
                return $alldata;
            }
            
        public static function verifyEmailAndPhone($email = "", $phone = "", $data = "*") 
            {
                $connect = static::getDB();
                $alldata = [];

                $data = is_string($data) ? $data : "*";

                $stmt = $connect->prepare("
                    UPDATE users 
                    SET 
                        is_emailverified = 1, 
                        is_phonenumberverified = 1, 
                        emailVerifiedAt = NOW(), 
                        phoneNoVerifiedAt = NOW() 
                    WHERE email = ? AND phoneNo = ?
                ");
                $stmt->bind_param("ss", $email, $phone);
                $stmt->execute();

                // Fetch updated user data
                $stmt = $connect->prepare("SELECT $data FROM users WHERE email = ? AND phoneNo = ?");
                $stmt->bind_param("ss", $email, $phone);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $alldata = $result->fetch_assoc();
                }

                return $alldata;
            }

            public static function verifyEmailAndPhoneOtp($email = "", $email_otp = "", $phone = "", $phone_otp = "") 
            {
                $connect = static::getDB();
            
                $stmt = $connect->prepare("
                    SELECT id, receiver, type FROM otps 
                    WHERE 
                        ((receiver = ? AND otp = ? AND type = 'email') 
                        OR 
                        (receiver = ? AND otp = ? AND type = 'phone')) 
                    AND expires_at >= NOW() AND is_used = 0
                ");
                $stmt->bind_param("ssss", $email, $email_otp, $phone, $phone_otp);
                $stmt->execute();
                $result = $stmt->get_result();
            
                $verifiedTypes = [];
                $otpIds = [];
            
                while ($row = $result->fetch_assoc()) {
                    $verifiedTypes[] = $row['type'];
                    $otpIds[] = $row['id'];
                }
            
                // If both email and phone were found and valid
                if (in_array('email', $verifiedTypes) && in_array('phone', $verifiedTypes)) {
                    // Mark both as used
                    foreach ($otpIds as $id) {
                        $markUsed = $connect->prepare("UPDATE otps SET is_used = 1 WHERE id = ?");
                        $markUsed->bind_param("i", $id);
                        $markUsed->execute();
                    }
                    return true;
                }
            
                return false;
            }
    public static function getUserBankAccountByNumber($account_number)
        {
            $connect = static::getDB();
            $stmt = $connect->prepare("SELECT * FROM bank_accounts WHERE account_number = ?");
            $stmt->bind_param("s", $account_number);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0 ? $result->fetch_assoc() : null;
        }
    
        public static function addBankAccount($user_pubkey, $account_name, $account_number, $bank_name, $bank_code, $is_default = 0) {
            $connect = static::getDB();
        
            $check = $connect->prepare("SELECT id FROM bank_accounts WHERE user_pubkey = ?");
            $check->bind_param("s", $user_pubkey);
            $check->execute();
            $result = $check->get_result();
        
            if ($result->num_rows === 0) {
                $is_default = 1;
            } elseif ($is_default == 1) {
                $reset = $connect->prepare("UPDATE bank_accounts SET is_default = 0 WHERE user_pubkey = ?");
                $reset->bind_param("s", $user_pubkey);
                $reset->execute();
            }
        
            $insert = $connect->prepare("
                INSERT INTO bank_accounts (user_pubkey, account_name, account_number, bank_name, bank_code, is_default)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insert->bind_param("sssssi", $user_pubkey, $account_name, $account_number, $bank_name, $bank_code, $is_default);
            $insert->execute();
        
            return $insert->affected_rows > 0;
        }      

    public static function storeDepositIntent($user_pubkey, $amount, $payment_gateway, $reference, $status = 'pending') {
            $connect = static::getDB();
        
            $query = "INSERT INTO deposits (user_pubkey, amount, payment_gateway, reference, status, created_at)
                      VALUES (?, ?, ?, ?, ?, NOW())";
        
            $stmt = $connect->prepare($query);
            $stmt->bind_param("sdsss", $user_pubkey, $amount, $payment_gateway, $reference, $status);
            $stmt->execute();
        
            return $stmt->affected_rows > 0;
        }
        
        public static function generatePaystackLink($email, $amount, $fullname, $reference) {
            $amountInKobo = $amount * 100;
            $callback_url = 'http://localhost/airtime/api/users/auth/Callback.php';
        
            $fields = [
                'email' => $email,
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callback_url' => $callback_url
            ];
        
            $fields_string = http_build_query($fields);
        
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer sk_test_83d7ddd7c5a80f9093a0f30102b58c292e6c3b18",
                "Content-Type: application/x-www-form-urlencoded",
                "Cache-Control: no-cache"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        
            if ($httpCode !== 200 || !$result) {
                error_log("Paystack error: $result");
                return null;
            }
        
            $response = json_decode($result, true);
        
            if (isset($response['status']) && $response['status'] === true) {
                return $response['data']['authorization_url'];
            } else {
                error_log("Paystack response error: " . json_encode($response));
                return null;
            }
        }       
        
    public static function generateMonnifyLink($email, $amount, $fullname, $reference) {
            $amount = number_format($amount, 2, '.', '');
            $contract_code = getenv("MONNIFY_CONTRACT_CODE");
            $api_key = getenv("MONNIFY_API_KEY");
            $secret_key = getenv("MONNIFY_SECRET_KEY");
            $callback_url = getenv("MONNIFY_CALLBACK_URL");
        
            $auth = base64_encode("$api_key:$secret_key");
        
            $data = [
                "amount" => $amount,
                "customerName" => $fullname,
                "customerEmail" => $email,
                "paymentReference" => $reference,
                "paymentDescription" => "Wallet Deposit",
                "currencyCode" => "NGN",
                "contractCode" => $contract_code,
                "redirectUrl" => $callback_url
            ];
        
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.monnify.com/api/v1/merchant/transactions/init-transaction");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Basic $auth",
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            $result = curl_exec($ch);
            curl_close($ch);
        
            $response = json_decode($result, true);
            return $response['responseBody']['checkoutUrl'] ?? null;
        }       

    public static function getAllBanks($country = "") {
        $url = "https://api.paystack.co/bank?country=" . $country;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer sk_test_83d7ddd7c5a80f9093a0f30102b58c292e6c3b18", 
            "Content-Type: application/json",
            "Cache-Control: no-cache"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$result) {
            error_log("Paystack get banks error: $result");
            return null;
        }

        $response = json_decode($result, true);

        if (isset($response['status']) && $response['status'] === true) {
            return $response['data']; 
        } else {
            error_log("Paystack response error: " . json_encode($response));
            return null;
        }
    }

    public static function getDepositByReference($reference)
        {
            $connect = static::getDB(); 
            $stmt = $connect->prepare("SELECT * FROM deposits WHERE reference = ?");
            $stmt->bind_param("s", $reference); 
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0 ? $result->fetch_assoc() : null;
        }
        
    public static function updateDepositStatus($reference, $status)
        {
            $connect = static::getDB(); 
            $stmt = $connect->prepare("UPDATE deposits SET status = ? WHERE reference = ?");
            $stmt->bind_param("ss", $status, $reference); 
            $stmt->execute();
            
            return $stmt->affected_rows > 0;
        }

   public static function submitKYC($username, $imagePaths)
    {
        $conn = static::getDB();

        $stmt = $conn->prepare("
            UPDATE users 
            SET 
                user_selfie = ?, 
                user_regulatory_card = ?, 
                user_utility_bill = ?, 
                user_kyc_status = '1'
            WHERE username = ?
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ssss",
            $imagePaths['selfie'],
            $imagePaths['regcard'],
            $imagePaths['utility'],
            $username
        );

        return $stmt->execute();
    }

    
        public static function updateKYCStatus($username, $status)
        {
            $conn = static::getDB();

            $stmt = $conn->prepare("
                UPDATE users 
                SET user_kyc_status = ? 
                WHERE username = ?
            ");

            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("is", $status, $username);

            return $stmt->execute();
        }

        public static function getUserLevel($user_id)
        {
            $connect = static::getDB();
            $stmt = $connect->prepare("SELECT user_level FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return (int)$row['user_level'];
            }

            return null; // user not found
        }

}    