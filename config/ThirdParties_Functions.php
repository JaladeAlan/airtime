<?php

namespace Config;

use Config\CoinPaymentsAPI;
/**
 * View 
 *
 * PHP version 5.4
 */
class ThirdParties_Functions
{

// GENERAL FUNCTIONS
public static function getUserAccountName($bnkcode,$accno){
    //Get the active payment system
    $alldata="";
    $systemData =DB_Calls_Functions::selectRows("bankaccountsallowed","oneappbankcode,monifybankcode,paystackbankcode,shbankcodes,banncode,baanlistcode",
    [   
        [
            ['column' =>'sysbankcode', 'operator' =>'=', 'value' =>$bnkcode],
        ]
    ],['limit'=>1]);
    $oneappcode=$monibankcode=$psbankcode=$shbankcodes=$banbankcodes=$banlistbankcodes='';
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $bankcodes = $systemData[0];
        $oneappcode=$bankcodes['oneappbankcode'];
        $monibankcode=$bankcodes['monifybankcode'];
        $psbankcode=$bankcodes['paystackbankcode'];
        $shbankcodes=$bankcodes['shbankcodes'];
        $banbankcodes=$bankcodes['banncode'];
        $banlistbankcodes=$bankcodes['baanlistcode'];
    }
    // if(getAccountNamePayStack($psbankcode,$accno)!=""&& strpos(getAccountNamePayStack($psbankcode,$accno),"Invalid")==false){
    //     $alldata=getAccountNamePayStack($psbankcode,$accno);
    // }else     if(getAccountNameMonify($monibankcode,$accno)!=""&& strpos(getAccountNameMonify($monibankcode,$accno),"Invalid")==false){
    //     $alldata=getAccountNameMonify($monibankcode,$accno);
    // }else  
    // if(getAccountNameOneApp($oneappcode,$accno)!=""&& strpos(getAccountNameOneApp($oneappcode,$accno),"Invalid")==false){
    //     $alldata=getAccountNameOneApp($oneappcode,$accno);
    // }
    $getaccdata=self::getAccountNameSH($shbankcodes,$accno);
    if($getaccdata!=""&& strpos($getaccdata,"Invalid")==false){
        $alldata=$getaccdata;
    }
    // if(getAccountNameBannAcc($banbankcodes,$banlistbankcodes,$accno)!=""&& strpos(getAccountNameBannAcc($banbankcodes,$banlistbankcodes,$accno),"Invalid")==false){
    //     $alldata= getAccountNameBannAcc($banbankcodes,$banlistbankcodes,$accno);
    // }
    
    return $alldata;
}
public static function payUserWithAnyBankSystem($amount, $accbnkcode, $accountname, $bnkname, $acctosendto, $userbanrefcode, $transorderid){
    $alldata=false;
    $systemData =DB_Calls_Functions::selectRows("bankaccountsallowed","oneappbankcode,monifybankcode,paystackbankcode,shbankcodes,banncode,krbankcode,baanlistcode,fintavashortcode",
    [   
        [
            ['column' =>'sysbankcode', 'operator' =>'=', 'value' =>$accbnkcode],
        ]
    ],['limit'=>1]);
    $oneappcode=$monibankcode=$psbankcode=$shbankcodes=$banbankcodes=$banlistbankcodes='';
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $bankcodes = $systemData[0];
        $oneappcode=$bankcodes['oneappbankcode'];
        $monibankcode=$bankcodes['monifybankcode'];
        $psbankcode=$bankcodes['paystackbankcode'];
        $shbankcodes=$bankcodes['shbankcodes'];
        $banbankcodes=$bankcodes['banncode'];
        $banlistbankcodes=$bankcodes['baanlistcode'];
        $fnbankcode=$bankcodes['fintavashortcode'];
        $kobankcode=$bankcodes['krbankcode'];
    }


    // if ($activepaysystem==1) {// paystack
    //     $alldata=payStackSendMoney($amount,$psbankcode,$bnkname,$acctosendto,$userbanrefcode,$transorderid);
    // } elseif ($activepaysystem==2) {//monify
    //     $alldata=monifySendMoney($amount, $monibankcode, $transorderid, $bnkname, $acctosendto, $userbanrefcode, $transorderid);
    // } 
    // else if ($activepaysystem==3){//oneapp
    //     $alldata = oneAppSendMoney($amount,$oneappcode,$bnkname,$acctosendto,$userbanrefcode,$transorderid,$accountname);
    // }    
    // else if ($activepaysystem==4){//SH
          $narration=$transorderid;
        $alldata = self::transferFundFromSH($shbankcodes,$acctosendto,$amount,$transorderid,$narration);
    // }
    // else if($activepaysystem==5){//bnn
    //   $narration=$transorderid;
    //    $alldata =  sendMoneyPayOutBannAcc($banbankcodes,$banlistbankcodes,$acctosendto,$amount,$narration,$transorderid);
    // }else if($activepaysystem==6){//bnn
    //   $narration=$transorderid;
    //    $alldata =transferFundFromFINT( $fnbankcode,$acctosendto,$amount,$transorderid,$narration);
    // }else if($activepaysystem==7){
    //     $narration=$transorderid;
    //      $alldata = transferFundFromKO($kobankcode,$acctosendto,$amount,$transorderid,$narration);
    // } 
    // else {
    //     //  in case telegram is to be added add the code here and return false for the tranaction to start in wallet
    //     //  ensure to update bank and system type if telegram is added on the system
    //     $alldata=false;
    // }
     return $alldata;
}
public static function getApiKeys($data,$provider){
    $systemData=[];
    $systemData =DB_Calls_Functions::selectRows("allapicredentials",$data,
    [   
        [
            ['column' =>'provider', 'operator' =>'=', 'value' =>$provider],
            ['column' =>'status', 'operator' =>'=', 'value' =>1],
            'operator'=>'AND'
        ]
    ],['limit'=>1]);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $systemData = $systemData[0];
    }
    return $systemData;
}
// GENERAL FUNCTIONS
// SH FULL FUNCTIONS
public static function getActiveSHBearerAccessToken(){
        $systemData =self::getApiKeys("baseurl,public_key,private_key",1);
        if (!Utility_Functions::input_is_invalid($systemData)) {
            $activeshis=$systemData;
            $clientid= $activeshis['public_key'];
            $baseurl=$activeshis['baseurl'];
            $client_assertion=$activeshis['private_key'];

            $postdatais=array (
                'grant_type' => "client_credentials",
                'client_id' => $clientid,
                'client_assertion'=>$client_assertion,
                'client_assertion_type' =>"urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
            );
            $jsonpostdata=json_encode($postdatais);
            // print($jsonpostdata);
            $url ="$baseurl/oauth2/token";
            $curl = curl_init();
            curl_setopt_array(
            $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => trim($jsonpostdata),
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/json",
                    'accept: application/json',
                    
                ),
            ));
            $userdetails = curl_exec($curl);
            $err = curl_error($curl);
            
            DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'SH GET ACCESS TOKEN','jsonresp'=>$userdetails]);
            // print_r($err);
            curl_close($curl);
            $breakdata = json_decode($userdetails);
            if($err){
                return " ";
            }else{
                if(isset($breakdata->access_token)){
                    return $breakdata->access_token;
                }
            }
        }
        return " ";
}
// '22445670983'
public static function initiate_bvn_verification_with_sh($bvnNumber){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use",1);
    $otpsent=false;
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $deposit_accno=$activeshis['channel_to_use'];
    
        $postdatais=array (
                'type' => "BVN",
                    'number' => $bvnNumber,
                    'debitAccountNumber'=> $deposit_accno
        );
        $jsonpostdata=json_encode($postdatais);
        // print($jsonpostdata);
        $url ="$baseurl/identity/v2";
        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => trim($jsonpostdata),
            CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    "ClientID: $clientid",  
            ),
        ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        $breakdata = json_decode($userdetails);

        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'SH BVN VERIFY INITIATE','jsonresp'=>$userdetails]);
        if(!$err){
            if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
                $bvn_valid_id=$breakdata->data->_id;
                $otpsent=$bvn_valid_id;
            }
        }
        curl_close($curl);
    }
    return $otpsent;
}
public static function getSHbankAccList(){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key",1);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $url ="$baseurl/transfers/banks";
        $curl = curl_init();
        curl_setopt_array(
                $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    "ClientID: $clientid",
                    
                ),
            ));
        $userdetails = curl_exec($curl);
        print_r($userdetails);
    }
}
public static function getSHAllAccountDetail(){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key",1);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $url ="$baseurl/accounts";
        $curl = curl_init();
        curl_setopt_array(
                $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    "ClientID: $clientid",
                    
                ),
            ));
        $userdetails = curl_exec($curl);
        print_r($userdetails);
    }
}
public static function getSHAccountDetail($accountid){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key",1);
    $datatosend=0;
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $url ="$baseurl/accounts/$accountid";
        $curl = curl_init();
        curl_setopt_array(
                $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    "ClientID: $clientid",
                    
                ),
            ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        // print_r($err);
        curl_close($curl);
        if ($err) {
            $datatosend=0;
        } else {
            $responses = json_decode($userdetails);
            if (isset($responses->data->accountBalance)) {
                $status = $responses->statusCode;
    
                if ($status==200) {
                $acnt_name = $responses->data->accountBalance;

                    $datatosend=$acnt_name;
                } 
            }
        }
    }
    return $datatosend;

}
public static function getAccountNameSH($bankcode,$accno){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use",1);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $deposit_accno=$activeshis['channel_to_use'];
    
    $postdatais=array (
    'bankCode' => "$bankcode",
    'accountNumber' => $accno
    );
    $jsonpostdata=json_encode($postdatais);
    // print($jsonpostdata);
    $url ="$baseurl/transfers/name-enquiry";
    $curl = curl_init();
    curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => trim($jsonpostdata),
        CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
                "ClientID: $clientid",
                 
        ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    // print_r($err);
    curl_close($curl);
    // print_r($userdetails);
    
    
    DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'SH GET ACCOUNT NAME','jsonresp'=>$userdetails]);
    
    if ($err) {
        $datatosend="";
        throw new \Exception("Error getting account names: $err");
    } else {
        $responses = json_decode($userdetails);
        if (isset($responses->data->accountName)) {
            $status = $responses->statusCode;
            $acnt_name = $responses->data->accountName;

            if ($status==200) {
                $datatosend=$acnt_name;
            } else {
                $datatosend='Invalid account number';
            }
        } else {
            $datatosend='Invalid account number';
        }
    }
        return $datatosend;
    }
}
public static function getAllSHServicesDetails(){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use",1);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $deposit_accno=$activeshis['channel_to_use'];
        
        $url ="$baseurl/vas/service/61efab78b5ce7eaad3b405d0/service-categories";
        $curl = curl_init();
        curl_setopt_array(
                $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
                "ClientID: $clientid",
                 
            ),
        ));
        $userdetails = curl_exec($curl);
        print_r($userdetails);
    }
}
public static function shgenerateAccNumber($fname,$lname,$phoneno,$email,$bvn,$myrefcode,$otp,$identityno){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use",1);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $deposit_accno=$activeshis['channel_to_use'];
        $generated=['status'=>false,'ref'=>'','bank_name'=>'','acc_no'=>'','acc_name'=>'','message'=>'Error occured, try again later'];
 
        $postdatais=array (
            'firstName'=> $fname,//1year
            'lastName' =>$lname,
            'phoneNumber'=>$phoneno,
            'emailAddress'=>$email,
            'externalReference'=>$myrefcode,
            'bvn'=>$bvn,
            'identityType'=>"BVN",
            'identityNumber'=>$bvn,
            'identityId'=>$identityno,
            'otp'=>$otp,
            'autoSweep'=>true,
            'autoSweepDetails' => array(
                "schedule"=> "Instant",
                "accountNumber"=>"$deposit_accno"
            )
        );
        $jsonpostdata=json_encode($postdatais);
        // print($jsonpostdata);
        $url ="$baseurl/accounts/v2/subaccount";
        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => trim($jsonpostdata),
            CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    "ClientID: $clientid",
                    
            ),
        ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

         DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>"SH_GEN_ACC",'jsonresp'=>$userdetails]);

        if (!$err) {
            $responses = json_decode($userdetails);
            if (isset($responses->data->accountName)) {
                $status = $responses->statusCode;
                if ($status==200) {
                    $banktypename="SafeHaven Microfinance Bank";
                    $newbankaccno=$responses->data->accountNumber;
                    $newreseverref=$responses->data->_id;
                    $acctname=$responses->data->accountName;
                    $generated=['status'=>true,'ref'=>$newreseverref,'bank_name'=>$banktypename,'acc_no'=>$newbankaccno,'acc_name'=>$acctname,'message'=>"Generated"];

                }
            }else{
                $substring = " Incorrect OTP";
                if(strpos($responses->message, $substring) !== false){
                    $generated['message']='Please input correct OTP';
                }
            }
        }
    }
    return $generated;          
}
public static function shUpdategeneratedAccNumber($fname,$lname,$phoneno,$email,$bvn,$myrefcode,$otp,$identityno,$oldbanid){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use",1);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $deposit_accno=$activeshis['channel_to_use'];
        $generated=['status'=>false,'ref'=>'','bank_name'=>'','acc_no'=>'','acc_name'=>''];
 
        $postdatais=array (
            'firstName'=> $fname,//1year
            'lastName' =>$lname,
            'phoneNumber'=>$phoneno,
            'emailAddress'=>$email,
            'externalReference'=>$myrefcode,
            'identityType'=>"BVN",
            'identityNumber'=>$bvn,
            'identityId'=>$identityno,
            'otp'=>$otp,
            'autoSweep'=>true,
            'autoSweepDetails' => array(
                "schedule"=> "Instant",
                "accountNumber"=>"$deposit_accno"
            )
        );
        $jsonpostdata=json_encode($postdatais);
        // print($jsonpostdata);
        $url ="$baseurl/accounts/$oldbanid/subaccount";
        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => trim($jsonpostdata),
            CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    "ClientID: $clientid",
                    
            ),
        ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

         DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>"SH_UPDATE_GEN_ACC",'jsonresp'=>$userdetails]);

        if (!$err) {
            $responses = json_decode($userdetails);
            if (isset($responses->data->accountName)) {
                $status = $responses->statusCode;
                if ($status==200) {
                    $banktypename="SafeHaven Microfinance Bank";
                    $newbankaccno=$responses->data->accountNumber;
                    $newreseverref=$responses->data->_id;
                    $acctname=$responses->data->accountName;
                    $generated=['status'=>true,'ref'=>$newreseverref,'bank_name'=>$banktypename,'acc_no'=>$newbankaccno,'acc_name'=>$acctname];
                }
            }
        }
    }
    return $generated;          
}
public static function shgenerateMeterAccNumber($fname,$lname,$phoneno,$email,$bvn,$userid,$metertid){
    global $connect;
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use",1);

    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $deposit_accno=$activeshis['channel_to_use'];
    
     //getting uniq acc ref no
    $permitted_chars2 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $loop = 0;
    while ($loop==0) {
        $myrefcode= '';//generate_string($permitted_chars2, 6);
        $check =  $connect->prepare("SELECT id FROM bill_voucher_saved_meters WHERE accrefcode=?");
        $check->bind_param("s", $myrefcode);
        $check->execute();
        $result2 =  $check->get_result();
        if ($result2->num_rows > 0) {
            $loop = 0;
        } else {
            $loop = 1;
            break;
        }
    } 

    $postdatais=array (
    'firstName'=> $fname,//1year
    'lastName' =>$lname,
    'phoneNumber'=>$phoneno,
    'emailAddress'=>$email,
    'externalReference'=>$myrefcode,
    'bvn'=>$bvn,
    'autoSweep'=>true,
    'autoSweepDetails' => array(
        "schedule"=> "Instant",
        "accountNumber"=>"$deposit_accno"
        )
    );
    $jsonpostdata=json_encode($postdatais);
    // print($jsonpostdata);
    $url ="$baseurl/accounts/subaccount";
    $curl = curl_init();
    curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => trim($jsonpostdata),
        CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
                "ClientID: $clientid",
                 
        ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    // print_r($err);
    // print_r($userdetails);
     $allresp="$userdetails";
    $paymentidisni="SH_GEN_ACC METER";
    $orderidni="$jsonpostdata";
    $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
    $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
    $insert_data->execute();
    $insert_data->close();
        
     if ($err) {
        $datatosend="";
        throw new \Exception("Error generating account: $err");
    } else {
        $responses = json_decode($userdetails);
        if (isset($responses->data->accountName)) {
            $status = $responses->statusCode;
            $acnt_name = $responses->data->accountName;

            if ($status==200) {
                $banktypename="SafeHaven Microfinance Bank";
                $newbankaccno=$responses->data->accountNumber;
                $newreseverref=$responses->data->_id;
                $bankcode=4;
                $acctname=$responses->data->accountName;
                $expireDay="";
                $type = 4;
                $insert_data = $connect->prepare("UPDATE bill_voucher_saved_meters SET bankname=?,bankacc=?,accrefcode=?,accserverrefcode=?,banksystemtype=?,account_name=?,banktypeis=? WHERE trackid=? AND userid=?");
                $insert_data->bind_param("ssssssssi",  $banktypename, $newbankaccno,$myrefcode,$newreseverref,$bankcode,$acctname,$type,$metertid,$userid);
                $insert_data->execute();
                $insert_data->close();
                $generated=true;
            }
        }
    }
}
    return $generated;          
}
public static function transferFundFromSH($bankcode,$accno,$amount,$paymentReference,$narration,$sendFromtransfer=1){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,extra_channel,channel_to_use",1);
    $generated=['status'=>false,'paystackref'=>'','paymenttoken'=>'','jsonresp'=>''];
    
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        if($sendFromtransfer==1){
           $transfer_acc_no=$activeshis['extra_channel'];
        }else{
            $transfer_acc_no=$activeshis['channel_to_use'];
        }
        $postdatais=array (
            'bankCode' => "$bankcode",
            'accountNumber' => $accno
        );
        $jsonpostdata=json_encode($postdatais);
        $url ="$baseurl/transfers/name-enquiry";
        $curl = curl_init();
        curl_setopt_array(
        $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => trim($jsonpostdata),
            CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    "ClientID: $clientid",
                    
            ),
        ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>"SH ACC NAME",'jsonresp'=>$userdetails]);
        if (!$err) {
            $responses = json_decode($userdetails);
            if (isset($responses->data->accountName)) {
                $status = $responses->statusCode;
                if ($status==200) {
                    $accountchecksessionid=$responses->data->sessionId;
                    
                    $postdatais=array (
                    'nameEnquiryReference' => "$accountchecksessionid",
                    'debitAccountNumber' => $transfer_acc_no,
                    'beneficiaryBankCode'=>$bankcode,
                    'beneficiaryAccountNumber'=>$accno,
                    'amount'=>round($amount,2),
                    'saveBeneficiary'=>true,
                    'narration'=>$narration,
                    'paymentReference'=>$paymentReference
                    );
                    $jsonpostdata=json_encode($postdatais);
                    $url ="$baseurl/transfers";
                    $curl = curl_init();
                    curl_setopt_array(
                    $curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => trim($jsonpostdata),
                    CURLOPT_HTTPHEADER => array(
                            "Authorization: Bearer $token",
                            "content-type: application/json",
                            'accept: application/json',
                            "ClientID: $clientid",
                            
                    ),
                    ));
                    $userdetails = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
        
                    DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>"SH TRANSFER FUND",'jsonresp'=>$userdetails]);

        
                    if (!$err) {
                        $responses = json_decode($userdetails);
                        if (isset( $responses->statusCode)) {
                            
                            $status=404;
                            $paystackref="";
                            $paymenttoken="";
                            if (isset( $responses->statusCode) && ($responses->statusCode==200||$responses->statusCode==202)) {
                                $status=200;
                                $paystackref=$responses->data->_id;
                                $paymenttoken=$responses->data->sessionId;
                            }else if(empty($userdetails)){
                                // call the trans history
                                $responseisHis= self::getTransdataSH($paymentReference);
                                $breakit=explode("||",$responseisHis);
                                $itwork=$breakit[0];
                                if($itwork){
                                    $responses = json_decode($breakit[1]);
                                    $status=200;
                                    $paystackref=$responses->data->_id;
                                    $paymenttoken=$responses->data->sessionId;
                                }
                            }else if(isset( $responses->statusCode) && $responses->statusCode==400 && $responses->message=="Duplicate transaction"){
                                // call the trans history
                                $responseisHis= self::getTransdataSH($paymentReference);
                                $breakit=explode("||",$responseisHis);
                                $itwork=$breakit[0];
                                if($itwork){
                                    $responses = json_decode($breakit[1]);
                                    $status=200;
                                    $paystackref=$responses->data->_id;
                                    $paymenttoken=$responses->data->sessionId;
                                }
                            }
                            if ($status==200||$status==202) {
                                $generated=['status'=>true,'paystackref'=>$paystackref,'paymenttoken'=>$paymenttoken,'jsonresp'=>$userdetails];
                            }
                        }
                    }  
                } 
            }
        }
        return  $generated;
    }
}
public static function getTransdataSH($orderid,$tagtoCheck='paymentReference',$doneonce=0){
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use",1);

    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        
        $postdatais=array (
        "$tagtoCheck" => $orderid,
        );
        $jsonpostdata=json_encode($postdatais);
        $url ="$baseurl/transfers/status";
        $curl = curl_init();
        curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => trim($jsonpostdata),
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $token",
            "content-type: application/json",
            'accept: application/json',
            "ClientID: $clientid",
            
        ),
        ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        // print_r($userdetails);

    DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'SH VERIFY TRANS','jsonresp'=>$userdetails]);

        curl_close($curl);
        if (!$err) {
            $responses = json_decode($userdetails);
            if (isset($responses->data->_id)) {
                $status = $responses->statusCode;
                if ($status==200) {
                    $fundsent=true."||$userdetails";
                }else if($status==403 && $doneonce==0){
                    // incase token expires
                    $doneonce++;
                    self::getTransdataSH($orderid,'paymentReference', $doneonce);
                }
            }
        }

     return $fundsent;  
    }
}
public static function getAccountNoTransSH($accNo){
    global $connect;
    $token=self::getActiveSHBearerAccessToken();
    $systemData =self::getApiKeys("baseurl,public_key,channel_to_use,transfer_acc_no",1);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $clientid= $activeshis['public_key'];
        $deposit_accno=$activeshis['channel_to_use'];
    
    $transfer_acc_no=$activeshis['transfer_acc_no'];
    $fundsent='11';
    $accid='';
    $getdataemail =  $connect->prepare("SELECT accserverrefcode FROM userpersonalbnkacc WHERE accno=?");
    $getdataemail->bind_param("s",$accNo);
    $getdataemail->execute();
    $getresultemail = $getdataemail->get_result();
    if( $getresultemail->num_rows> 0){
         $getthedata= $getresultemail->fetch_assoc();
         $accid=$getthedata['accserverrefcode'];
    }
    
    $url ="$baseurl/accounts/$accid/statement?limit=100&page=0";
    $curl = curl_init();
    curl_setopt_array(
    $curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $token",
        "content-type: application/json",
        'accept: application/json',
        "ClientID: $clientid",
         
    ),
    ));
    $userdetails = curl_exec($curl);
    // print_r($userdetails);
    $err = curl_error($curl);
    curl_close($curl);
     if ($err) {
            $fundsent=false."||None";
     } else {
        $responses = json_decode($userdetails);
        if (isset($responses->statusCode)) {
            $status = $responses->statusCode;
            if ($status==200) {
                   $fundsent=true."^*$userdetails";
                //   print_r(json_decode($userdetails)->data->_id);
            }
        }
     }
    }
     return $fundsent;  
}
// SH FULL FUNCTIONS


//KORA pay

public static function getKObankAccList(){
    $systemData =self::getApiKeys("baseurl,public_key,private_key",15);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $baseurl=$systemData['baseurl'];
        $token= $systemData['public_key'];
        $url ="$baseurl/misc/banks?countryCode=NG";
        $curl = curl_init();
        curl_setopt_array(
                $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                    
                ),
            ));
        $userdetails = curl_exec($curl);
        print_r($userdetails);
    }
}
public static function getAccountNameKO($bankcode,$accno){
    $systemData =self::getApiKeys("baseurl,public_key,private_key",15);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $baseurl=$systemData['baseurl'];
        $token= $systemData['public_key'];
    $url ="$baseurl/misc/banks/resolve";
    $postdatais=array (
    'bank' => "$bankcode",
    'account' => "$accno"
    );
    $jsonpostdata=json_encode($postdatais);
    // print($jsonpostdata);
    $curl = curl_init();
    curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => trim($jsonpostdata),
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $token",
            "content-type: application/json",
            'accept: application/json',
        ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    // print_r($err);
    curl_close($curl);
    // print_r($userdetails);
    
    
    
    $allresp="$userdetails";
    $paymentidisni="KO ACC NAME";
    $orderidni="$jsonpostdata";
    $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
    $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
    $insert_data->execute();
    $insert_data->close();
    
    
    
    if ($err) {
        $datatosend="";
        throw new \Exception("Error getting account names: $err");
    } else {
        $responses = json_decode($userdetails);
        if (isset($responses->data->account_name)) {
            $acnt_name = $responses->data->account_name;

                $datatosend=$acnt_name;
        } else {
            $datatosend='Invalid account number';
        }
    }
        return $datatosend;
    }
}

public static function generateVirtualAccountKO($fullname,$userid,$userbvn){
    $systemData =self::getApiKeys("baseurl,public_key,private_key",15);
    $generated=['status'=>false,'ref'=>'','bank_name'=>'','acc_no'=>'','acc_name'=>''];

    if (!Utility_Functions::input_is_invalid($systemData)) {
        $baseurl=$systemData['baseurl'];
        $token= $systemData['private_key'];
        $url ="$baseurl/virtual-bank-account";
        $postdatais = [
            "account_name" => $fullname,
            "account_reference" => $userid,
            "permanent" => true,
            "bank_code" => "035",
            "customer" => [
                "name" => $fullname,
            ],
            "kyc"=>[
                "bvn"=>$userbvn
            ]
        ];
        $jsonpostdata=json_encode($postdatais);
        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => trim($jsonpostdata),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
            ),
            )
        );
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        // print_r($err);
        curl_close($curl);
        // print_r($userdetails);
        
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'KO GENERATE ACC NO','jsonresp'=>$userdetails]);
    
    
        if ($err) {
            $datatosend="";
            throw new \Exception("Error generating account: $err");
        } else {
            $responses = json_decode($userdetails);
            if (isset($responses->status)&&$responses->status==true) {
                    $banktypename=$responses->data->bank_name;
                    $newbankaccno=$responses->data->account_number;
                    $newreseverref=$responses->data->unique_id;
                    $acctname=$responses->data->account_name;
                    $generated=['status'=>true,'ref'=>$newreseverref,'bank_name'=>$banktypename,'acc_no'=>$newbankaccno,'acc_name'=>$acctname];
                
            }
        }
    }
    return $generated;
}

public static function verifyKODedicatedAccpay($reference){
    $systemData =self::getApiKeys("baseurl,public_key,private_key",15);
    $generated=['status'=>false,'json'=>''];

    if (!Utility_Functions::input_is_invalid($systemData)) {
        $baseurl=$systemData['baseurl'];
        $token= $systemData['private_key'];
        $url ="$baseurl/charges/$reference";
        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
            ),
            )
        );
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        // print_r($err);
        curl_close($curl);
        // print_r($userdetails);
        
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$reference,"name"=>'KO VALIDATE PAY','jsonresp'=>$userdetails]);
    
    
        if ($err) {
            $datatosend="";
            throw new \Exception("Error validating pay: $err");
        } else {
            $responses = json_decode($userdetails);
            if (isset($responses->status)&&$responses->status==true && ($responses->data->status=='success'||$responses->data->status=='paid')) {
                $generated=['status'=>true,'json'=>$userdetails];
            }
        }
    }
    return $generated;
}


public static function transferFundFromKO($bankcode,$accno,$amount,$paymentReference,$narration){
    global $connect;
    $amount=floatval($amount);
    $activeshis=GetActiveKOApi();
    $baseurl=$activeshis['baseurl'];
    $token= $activeshis['secret_key'];
    $url ="$baseurl/transactions/disburse";
    $fundsent=false;
    $postdatais= array(
    'reference' => $paymentReference,
    'destination' => array(
        'type' => 'bank_account',
        'amount' => "$amount",
        'currency' => 'NGN',
        'narration' => "$narration",
        'bank_account' => array(
            'bank' => "$bankcode",
            'account' => "$accno",
        ),
        'customer' => array(
            'email' => 'habnarmtech@email.com',
        ),
    ),
    );
    $jsonpostdata=json_encode($postdatais);
    $curl = curl_init();
    curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => trim($jsonpostdata),
        CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
                 
        ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    print_r($userdetails);
    
    $allresp="$userdetails";
    $paymentidisni="KO TRANSFER FUND";
    $orderidni="$jsonpostdata";
    $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
    $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
    $insert_data->execute();
    $insert_data->close();
    
    if ($err) {
        $fundsent=false;
    } else {
        $responses = json_decode($userdetails);
        if (isset($responses->data->status)) {
            $status = $responses->status;
           
            if ($status==true) {
                $paystackref="";
                $paymenttoken="";
                // generating  token
                // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                $companypayref = createUniqueToken(16,"userwallettrans","paymentref","SKO",true,true,false);
                $valid=true; 
                // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app 4 sh 5 ban 6 fint 7 KO
                $bankpaidwith=1;
                $systempaidwith=7;
                $paystatus=1;
                $status = 1;
                $time = date("h:ia, d M");
                $approvedby="Automation";
                $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,systempaidwith=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
                $checkdata->bind_param("ssssssssss",$companypayref, $paystatus,$systempaidwith,$status,$time,$allresp,$paystackref,$paymenttoken,$approvedby,$paymentReference);
                $checkdata->execute();
                
                $fundsent=true;
                            
            }
        }
        return  $fundsent;
    }
}

// KORA

//dojah FULL FUNCTIONS

public static function fetchBvnDetails($bvn){

    $systemData =self::getApiKeys("public_key,private_key",5);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
    $secret_key = $activeshis['private_key']; 
    $appid = $activeshis['public_key'];
    $url ="https://api.dojah.io/api/v1/kyc/bvn/full?bvn=$bvn";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_POSTFIELDS => '',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "content-type: application/json",
            "authorization: $secret_key",
            "appid: $appid",
            "cache-control: no-cache"
        ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $response = json_decode($userdetails);
    return $response; 
}
}
public static function verifyBvnwith_DOj($bvn,$userid){
    $results = [
        'status' => 0,
        'message' => "Error Accessing Token"
    ];
    $systemData =self::getApiKeys("public_key,private_key",5);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $secret_key =$activeshis['private_key']; 
        $appid = $activeshis['public_key'];  
        $url ="https://api.dojah.io/api/v1/kyc/bvn/full?bvn=$bvn";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            //u change the url infront based on the request u want
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //change this based on what u need post,get etc
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "authorization: $secret_key",
                "appid: $appid",
                "cache-control: no-cache"
            ),
        ));
        $userdetails = curl_exec($curl);
        // print_r($userdetails);
        $err = curl_error($curl);
        curl_close($curl);
        $response = json_decode($userdetails);
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$url,"name"=>'DOJ BVN VERIFY INITIATE','jsonresp'=>$userdetails]);


        $error="";
        if (isset($response->entity->phone_number1)) {
            $pno="".$response->entity->phone_number1."";
            $results = [
                'status' => 1,
                'message' => $userdetails
            ];
          DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$url,"user_id"=>$userid,"name"=>'DOJ BVN VERIFY INITIATE','jsonresp'=>$userdetails,'calltype'=>1]);
        } else {
            $data = json_decode($userdetails, true);
            if (isset($data['error']) && ($data['error'] === 'Your balance is low, pls visit the dashboard to top up' || $data['error'] === 'Unable to reach service')) {
                $error='Unable to reach service';
            }else  if(isset($data['error'])){
                $error=$data['error'];
            }else{
                $error="BVN or BVN Phone number Not found"; 
            }
            $results = [
                'status' => 0,
                'message' => $error
            ];
        }
    }
    return $results;
}
//dojah FULL FUNCTIONS

// BANNI FULL FUNCTIONS
public static function getMoSignatureToken(){
    $baanapiis=self::getApiKeys('private_key,extra_channel,public_key',10);
    $merchantKey =$baanapiis['private_key']; 
    $tribeAccountRef = $baanapiis['extra_channel']; 
    $publicKey = $baanapiis['public_key'];
    $digest = $tribeAccountRef . $publicKey;
    $monosignature =  hash_hmac('sha256',$digest,$merchantKey);
    return $monosignature;
}
public static function createUserCustomerAccBann($customerfname,$customerlname,$customerPhoneno,$customerEmail,$customerAddress,$customerState,$customerCity,$userid){
        $userrefcode="";
        $baanapiis=self::getApiKeys('channel_to_use,baseurl',10);
        $token=$baanapiis['channel_to_use'];
        $baseurl=$baanapiis['baseurl'];
        $monosignature = self::getMoSignatureToken();
            
        $postdatais=array (
            'customer_first_name'=>$customerfname,
            'customer_last_name'=>$customerlname,
            'customer_phone'=>$customerPhoneno,
            'customer_email'=>$customerEmail,
            'customer_address'=>$customerAddress,
            'customer_state' =>$customerState,
            'customer_city' =>$customerCity
        );
        $jsonpostdata=json_encode($postdatais);
        // print($jsonpostdata);
        $url ="$baseurl/comhub/add_my_customer/";
        $curl = curl_init();
        curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => trim($jsonpostdata),
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $token",
            "content-type: application/json",
            'accept: application/json',
            "moni-signature: $monosignature",
        ),
        ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>"BAAAN CUSTOMER CREATE $userid",'jsonresp'=>$userdetails]);
        if (!$err) {
            $responses = json_decode($userdetails);
            if (isset( $responses->status)) {
                $status = $responses->status_code;
                if ($status==201) {
                    $customerref=$responses->customer_ref;
                    $userrefcode=$customerref;
                }
            }
        }
        return $userrefcode;
}
public static function generate_bnnn_virtualbankacc($customerid,$customerbvn,$accountname,$refcode){
    $baanapiis=self::getApiKeys('channel_to_use,baseurl',10);
    $token=$baanapiis['channel_to_use'];
    $baseurl=$baanapiis['baseurl'];
    $monosignature = self::getMoSignatureToken();
    $generated=['status'=>false,'ref'=>'','bank_name'=>'','acc_no'=>'','acc_name'=>''];

    $postdatais=array (
        'pay_va_step'=>'direct',
        'country_code'=>'NG',
        'pay_currency'=>'NGN',
        'holder_account_type'=>'permanent',
        'alternate_name'=>"$accountname",
        'holder_legal_number' =>"$customerbvn",
        'customer_ref' =>"$customerid",
        'pay_ext_ref' =>"$refcode",
        'bank_name'=>"9 payment service bank"
    );
    $jsonpostdata=json_encode($postdatais);
    // print($jsonpostdata);
    $url ="$baseurl/partner/collection/bank_transfer/";
    $curl = curl_init();
    curl_setopt_array(
    $curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => trim($jsonpostdata),
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $token",
        "content-type: application/json",
        'accept: application/json',
        "moni-signature: $monosignature",
    ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>"BAAAN CUSTOMER Bank Creation",'jsonresp'=>$userdetails]);
    
    if (!$err) {
        $responses = json_decode($userdetails);
        if (isset( $responses->status)) {
            $status = $responses->status_code;
            if ($status==201) {
                $newreseverref=$responses->payment_reference;
                $banktypename=$responses->holder_bank_name;
                $newbankaccno=$responses->holder_account_number;
                $acctname=$responses->account_name;

                $generated=['status'=>true,'ref'=>$newreseverref,'bank_name'=>$banktypename,'acc_no'=>$newbankaccno,'acc_name'=>$acctname];
            }
        }
    }
    return $generated;
    

}
public static function verifyBaaanDedicatedAccpay($payref, $pay_ext_ref){
    $baanapiis=self::getApiKeys('channel_to_use,baseurl',10);
    $token=$baanapiis['channel_to_use'];
    $baseurl=$baanapiis['baseurl'];
    $monosignature = self::getMoSignatureToken();
    $generated=['status'=>false,'json'=>''];

    $postdatais=array (
        'pay_ref'=>$payref,
        'pay_ext_ref'=>$pay_ext_ref,
    );
    $jsonpostdata=json_encode($postdatais);
    // print($jsonpostdata);
    $url ="$baseurl/partner/collection/pay_status_check/";
    $curl = curl_init();
    curl_setopt_array(
    $curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => trim($jsonpostdata),
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $token",
        "content-type: application/json",
        'accept: application/json',
        "moni-signature: $monosignature",
    ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>"BAAAN TRANSFER CHECK",'jsonresp'=>$userdetails]);
    
    if (!$err) {
        $responses = json_decode($userdetails);
        if (isset( $responses->status)) {
            $status = $responses->status_code;
            if ($status==200 && ($responses->data->pay_status=='activated'||$responses->data->pay_status=='paid'||$responses->data->pay_status=='completed')) {

                $generated=['status'=>true,'json'=>$userdetails];
            }
        }
    }
    return $generated;
    

}
public static function getallBankListBannAccount(){
        $baanapiis=self::getApiKeys(10);
    $token=$baanapiis['token'];
    $baseurl=$baanapiis['base_url'];
    $monosignature = self::getMoSignatureToken();

    // print($jsonpostdata);
    $url ="$baseurl/partner/list_banks/NG/";
    $curl = curl_init();
    curl_setopt_array(
    $curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $token",
        "content-type: application/json",
        'accept: application/json',
        "moni-signature: $monosignature",
    ),
    ));
    $userdetails = curl_exec($curl);
    print_r($userdetails);
}
public static function getAccountNameBannAcc($bankcode,$banklistcode,$accno){
     global $connect;
    $baanapiis=self::getApiKeys(10);
    $token=$baanapiis['token'];
    $baseurl=$baanapiis['base_url'];
    $monosignature = self::getMoSignatureToken();

    
    $postdatais=array (
        'list_code'=>$banklistcode,
        'bank_code'=>$bankcode,
        'country_code'=>'NG',
        'account_number'=>$accno,
    );
    $jsonpostdata=json_encode($postdatais);
    // print($jsonpostdata);
    $url ="$baseurl/partner/payout/verify_bank_account/";
    $curl = curl_init();
    curl_setopt_array(
    $curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => trim($jsonpostdata),
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $token",
        "content-type: application/json",
        'accept: application/json',
        "moni-signature: $monosignature",
    ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    // print_r($userdetails);
    // exit;
    
    $allresp="$userdetails";
    $paymentidisni="BAAN ACC NAME";
    $orderidni="$jsonpostdata";
    $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
    $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
    $insert_data->execute();
    $insert_data->close();
    
    if ($err) {
        $datatosend="";
        throw new \Exception("Error getting account names: $err");
    } else {
        $responses = json_decode($userdetails);
        if (isset($responses->status) && $responses->status==true) {
            $status = $responses->status_code;
            

            if ($status==200) {
                $acnt_name = $responses->account_name;
                $datatosend=$acnt_name;
            } else {
                $datatosend='Invalid account number';
            }
        } else {
            $datatosend='Invalid account number';
        }
    }
        return $datatosend;
}
public static function sendMoneyPayOutBannAcc($bankcode,$banklistcode,$accno,$amount,$narration,$orderid){
   
    global $connect;
    $baanapiis=self::getApiKeys(10);
    $token=$baanapiis['token'];
    $baseurl=$baanapiis['base_url'];
    $monosignature = self::getMoSignatureToken();
    $fundsent=false;


    $receiver_amount=$amount;
    $receiver_account_num=$accno;
    $receiver_sort_code=$bankcode;
    $receiver_account_name=self::getAccountNameBannAcc($bankcode,$banklistcode,$accno);
    $transfer_note=$narration;
    
    if(strlen($receiver_account_name)>3){
        $postdatais=array (
            'payout_step'=>'direct',
            'receiver_currency'=>'NGN',
            'sender_currency'=>'NGN',
            'transfer_method'=>'bank',
            'transfer_receiver_type'=>"personal",
            'receiver_country_code' =>"NG",
            'receiver_account_num' =>"$receiver_account_num",
            'receiver_amount'=>"$receiver_amount",
            'receiver_sort_code' =>"$receiver_sort_code",
            'receiver_account_name'=>"$receiver_account_name",
            'sender_amount'=>"$receiver_amount",
            'transfer_note'=>"$transfer_note",
            'transfer_ext_ref'=>"$orderid"
        );
        $jsonpostdata=json_encode($postdatais);
        // print($jsonpostdata);
        $url ="$baseurl/partner/payout/initiate_transfer/";
        $curl = curl_init();
        curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => trim($jsonpostdata),
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $token",
            "content-type: application/json",
            'accept: application/json',
            "moni-signature: $monosignature",
        ),
        ));
        $userdetails = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        $allresp="$userdetails";
        $paymentidisni="BAANNN TRANSFER FUND";
        $orderidni="$jsonpostdata";
        $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
        $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
        $insert_data->execute();
        $insert_data->close();
    
        if ($err) {
            $fundsent=false;
        } else {
            $responses = json_decode($userdetails);
            if (isset( $responses->status) && $responses->status==true) {
                $status = $responses->status_code;
                if ($status==201) {
                    $paystackref=$responses->payout_ref;
                    $paymenttoken=" ";
                    // generating  token
                    // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                    $companypayref = 0;//createUniqueToken(16,"userwallettrans","paymentref","BNH",true,true,false);
                    $valid=true; 
                    // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
                    $bankpaidwith=1;
                    $systempaidwith=5;
                    $paystatus=1;
                    $status = 1;
                    $time = date("h:ia, d M");
                    $approvedby="Automation";
                    $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,systempaidwith=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
                    $checkdata->bind_param("ssssssssss",$companypayref, $paystatus,$systempaidwith,$status,$time,$allresp,$paystackref,$paymenttoken,$approvedby,$orderid);
                    $checkdata->execute();
        
                    $fundsent=true;
                }
            }
        }
       
    }
     return $fundsent;
}
public static function getallPersonalBankListBannAccount(){
    $baanapiis=self::getApiKeys(10);
    $token=$baanapiis['token'];
    $baseurl=$baanapiis['base_url'];
    $monosignature = self::getMoSignatureToken();

    // print($jsonpostdata);
    $url ="$baseurl/partner/list_payment_banks/NG/";
    $curl = curl_init();
    curl_setopt_array(
    $curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $token",
        "content-type: application/json",
        'accept: application/json',
        "moni-signature: $monosignature",
    ),
    ));
    $userdetails = curl_exec($curl);
    print_r($userdetails);
}
// BANNI FULL FUNCTIONS


// PAYSTACK

public static function getAllPayStackBank(){
    $allbnkarr=[];
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['apikey'];

    $url = "https://api.paystack.co/";
    // $params = json_encode($arr);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url . "bank",
        // CURLOPT_POSTFIELDS => $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer $activepaystackapi", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache",
        ),
    ));
    $allbanks = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $allbnkarr=[];
        throw new \Exception("Error getting bank names: $err");

    } else {
        $dbanks = json_decode($allbanks);
        if($dbanks->status==true){
            $banks = $dbanks->data;
            foreach ($banks as $abc) {
                $bankname= trim($abc->name);
                $bankcoode=trim($abc->code);
                array_push($allbnkarr, array( "name"=>$bankname, "code"=>$bankcoode,"combined"=>"$bankname^$bankcoode"));
            }
        }else{
           $allbnkarr=[];
         }
    }
    return $allbnkarr;

}
public static function addUserToPayStack($fullname,$accountnumber,$bankcode){
    $refcode="";
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['secretekey'];
    $arr = array(
    "type"=> "nuban",
    "name"=> "$fullname",
    "account_number"=> "$accountnumber",
    "bank_code"=> "$bankcode"
    );
    //below is the base url
    $url ="https://api.paystack.co/";
    $params =  json_encode($arr);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url . "transferrecipient",
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer $activepaystackapi", //replace this with your own test key
        "content-type: application/json",
        "cache-control: no-cache"
    ),
    ));
    $userdetails = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $refcode="";
        // throw new \Exception("Error getting bank names: $err");

    } else {
        $ddata = json_decode($userdetails);
        $status=$ddata->status;
        
        if($status){
            $duserdet =$ddata->data;
            $refcode = $duserdet->recipient_code;
        }else{
            $refcode="";
        }
     
    }
    return $refcode;

}
public static function getAccountNamePayStack($bnkcode,$accno){
    $datatosend="";
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['secretekey'];

    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=".$accno."&bank_code=".$bnkcode."",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        "Authorization: Bearer $activepaystackapi"
        ),
    ));

    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $datatosend="";
        throw new \Exception("Error getting account names: $err");
    } else {
        // print($resp);
        $responses = json_decode($resp);
        //var_dump($responses);
        if (isset($responses->data->account_name)) {
            $status = $responses->status;
            $msg = $responses->message;
            $acnt_no = $responses->data->account_number;
            $acnt_name = $responses->data->account_name;
            $bankid = $responses->data->bank_id;

            if ($status == 'true') {
                $datatosend=$acnt_name;
            } else {
                $datatosend='Invalid account number';
            }
        } else {
            $datatosend='Invalid account number';
        }
    }
        return $datatosend;

}
public static function checkPaystackBalanceAmount($moneytosend){
    $canpay=false;
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['apikey'];

    $url ="https://api.paystack.co/";
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url . "balance",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
    "authorization: Bearer $activepaystackapi", //replace this with your own test key
    "content-type: application/json",
    "cache-control: no-cache"
    ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $canpay=false;
        throw new \Exception("Error getting account names: $err");
    } else {
        // print($resp);
        $responses = json_decode($response);
        //  var_dump($responses);
        if (isset($responses->status) && $responses->status==true) {
            $mybal= $responses->data[0]->balance;
            if(($mybal/100)>=$moneytosend){
                $canpay=true;
            } else {
                $canpay=false;
            }
        } else {
            $canpay=false;
        }
    }
        return $canpay;
}
public static function payStackSendMoney($amount,$accbnkcode,$bnkname,$acctosendto,$userbanrefcode,$transorderid){
 
    $canpay=false;
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['secretekey'];
    $banref = addUserToPayStack($bnkname,$acctosendto,$accbnkcode);
    $amount=$amount*100;
    # try to contact service sending the money
    $arr = array(
            "source"=> "balance",
            "reason"=> "services",
            "amount"=> $amount,
            "recipient"=> $banref,
        );
        //below is the base url
        $url ="https://api.paystack.co/";
        $params =  json_encode($arr);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            //u change the url infront based on the request u want
            CURLOPT_URL => $url . "transfer",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //change this based on what u need post,get etc
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer $activepaystackapi", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
        ),
        ));
        $resp = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    if ($err) {
        $canpay=false;
        throw new \Exception("Error getting account names: $err");
    } else {
        //print($resp);
        $responses = json_decode($resp);
        if (isset($responses->status) && $responses->status==true) {
            
            $paystackref= $responses->data->reference;
            $paymenttoken=$responses->data->id;
            $canpay=true;
            
            // update transaction ref as paid
            if (!empty($transorderid) && $transorderid != "") {
                // generating  token
                // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                $companypayref = createUniqueToken(16,"userwallettrans","paymentref","PS",true,true,false);
                $valid=true; 
                // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
                $bankpaidwith=1;
                $systempaidwith=1;
                $paystatus=1;
                $status = 1;
                $time = date("h:ia, d M");
                $approvedby="Automation";
                $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,systempaidwith=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
                $checkdata->bind_param("ssssssssss",$companypayref, $paystatus,$systempaidwith,$status,$time,$resp,$paystackref,$paymenttoken,$approvedby,$transorderid);
                $checkdata->execute();
            }
           
        } else {
            $canpay='false';
        }
    }
    return $canpay;
        
}
public static function PayStackVerifyBVN($bvn,$accountno,$bankcode){
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['apikey'];
    $verified=false;
    $arr = array(
    "bvn"=> "$bvn",
    "account_number"=> "$accountno",
    "bank_code"=> "$bankcode",
    );
    //below is the base url
    $url ="https://api.paystack.co/";
    $params =  json_encode($arr);
    $curl = curl_init();
    curl_setopt_array($curl, array(
    //u change the url infront based on the request u want
    CURLOPT_URL => $url . "bvn/match",
    CURLOPT_POSTFIELDS => $params,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //change this based on what u need post,get etc
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => array(
    "authorization: Bearer $activepaystackapi", //replace this with your own test key
    "content-type: application/json",
    "cache-control: no-cache"
    ),
    ));
    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $verified=false;
        throw new \Exception("Error verifying BVN: $err");
    } else {
        $decodedresponse = json_decode($resp);
        $thestatus=$decodedresponse->status;
        if ($thestatus) {
            $verified=true;
        } else {
            $verified=false;
        }
    }
    return $verified;
}
public static function verifypaystackPayment($reference){
    global $connect;
    $valid=false;
    $activepaystackapi=GetActivePayStackApi()['secretekey'];
    $verified=false;
    $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode("$reference"),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $activepaystackapi",
      "Cache-Control: no-cache",
    ),
  ));
  

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $tranx = json_decode($response);
    print_r($tranx);
    if ($tranx->status && 'success' == $tranx->data->status) {
               $valid=true;
    }
    return $valid;
}
public static function verifypaystackcardpay($reference,$useremail, $uname, $userid){
    global $connect;
    $valid=false;
    $activepaystackapi=GetActivePayStackApi()['secretekey'];
    $verified=false;
    $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode("$reference"),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer $activepaystackapi",
      "Cache-Control: no-cache",
    ),
  ));
  

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $tranx = json_decode($response);
    print_r($tranx);
    if ($tranx->status && 'success' == $tranx->data->status) {
        $paystackref=$tranx->data->reference;
        $paymenttoken=$tranx->data->id;
        $notyetpaid=0;
        $checkdata =  $connect->prepare("SELECT * FROM  userwallettrans WHERE orderid=? AND status=?  AND userid=?");
        $checkdata->bind_param("sis",$reference, $notyetpaid,$userid);
        $checkdata->execute();
        $dresult = $checkdata->get_result(); 
       if(empty($reference)) {
            $valid=false;
       } else if($dresult ->num_rows == 0){
            $valid=false;
       }else{
                 // generating  token
            // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
            $companypayref = createUniqueToken(16,"userwallettrans","paymentref","PS",true,true,false);
            
           // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
           $bankpaidwith=1;
           $systempaidwith=1;
           $paystatus=1;
           $status = 1;
           $time = date("h:ia, d M");
           $approvedby="Automation";
           $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,systempaidwith=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
           $checkdata->bind_param("ssssssssss",$companypayref, $paystatus,$systempaidwith,$status,$time,$response,$paystackref,$paymenttoken,$approvedby,$reference);
           if($checkdata->execute()){
               $valid=true;
           }else{
            echo $checkdata->error;   
           }
           //save card
           paystacksavemycard($tranx, $useremail, $uname, $userid);
       }
    }
    return $valid;
}
public static function paystacksavemycard($tranx,$dashemail,$dashuname,$dashid){
    global $connect;
       //saving card
       $authcode= $tranx->data->authorization->authorization_code;
       $cardtype= $tranx->data->authorization->card_type;
       $last4= $tranx->data->authorization->last4;
       $expm=$tranx->data->authorization->exp_month;
       $expy= $tranx->data->authorization->exp_year;
       $bin = $tranx->data->authorization->bin;
       $dbank= $tranx->data->authorization->bank;
       $dchannel= $tranx->data->authorization->channel;
       $dsig = $tranx->data->authorization->signature;
       $reuse = $tranx->data->authorization->reusable;
       $countrycode= $tranx->data->authorization->country_code;

    $checkdata =  $connect->prepare("SELECT * FROM 	user_cards  WHERE last4=? AND useremail=? AND card_type=? AND username=? AND exp_month=? AND exp_yr=?");
    $checkdata->bind_param("ssssss",$last4,$dashemail,$cardtype,$dashuname,$expm,$expy);
    $checkdata->execute();
    $dresult = $checkdata->get_result();
    if($dresult->num_rows==0){

        $permitted_chars2 = '0123456789';
        $loop = 0;
        while($loop==0){
        $myrefcode= generate_string($permitted_chars2, 7);
        $check =  $connect->prepare("SELECT * FROM  user_cards WHERE  cardtrackid = ?");
        $check->bind_param("i",$myrefcode);
        $check->execute();
        $result2 =  $check->get_result();
        if($result2->num_rows > 0){
        $loop = 0;
        }else{
        $loop = 1;
        break;  
        }
        }
        $insert_data = $connect->prepare("INSERT INTO user_cards (username,userid,card_type,last4,authorization_code,exp_month,exp_yr,bin,bank,channel,signature,reusable,country_code,useremail,cardtrackid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $insert_data->bind_param("sssssssssssssss", $dashuname,$dashid,$cardtype,$last4,$authcode,$expm,$expy,$bin,$dbank,$dchannel,$dsig,$reuse,$countrycode,$dashemail,$myrefcode);
        $insert_data->execute();
        $insert_data->close();
        $checkdata->close();
    }
}
public static function paystackPaywithCard($amount,$dashemail,$transref,$bizcall=0){
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['secretekey'];
    $authlink="";

    $amounttodeduct=$amount;
    $email = $dashemail;
    if($amounttodeduct <=2500 ){
        $dadded= $amounttodeduct *(1.5/100);
        $dtobdeducted = $dadded + $amounttodeduct;
    }else if($amounttodeduct > 2500){
        $dadded= $amounttodeduct *(1.5/100);
        $dtobdeducted = 100+ $dadded + $amounttodeduct;
    }
    $damount=$dtobdeducted*100; 
     //the amount in kobo. This value is actually NGN 300
     if($bizcall==0){
        $callback_url =BASEURL.'/dashboard/index.php';
     }else {
        $callback_url ="https://business.cardify.co/dashboard/payment_successful.php";
     }
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'amount'=>$damount,
        'email'=>$email,
        'reference' => $transref,
        'callback_url' => $callback_url,
    ]),
    CURLOPT_HTTPHEADER => [
        "authorization: Bearer $activepaystackapi", //replace this with your own test key
        "content-type: application/json",
        "cache-control: no-cache"
    ],
    ));

    $response = curl_exec($curl);
    // print_r($response);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $authlink="";
    } else {
        $tranx = json_decode($response);
        if (!$tranx->status) {
            $authlink="";
        } else {
            // redirect to page so User can pay
            $authlink=$tranx->data->authorization_url;
        }
   }
   return $authlink;
}
public static function paywithsavedcardPayStack($dsavedemail,$amount,$dsavedauth,$reference,$wallettrackid){
    global $connect;
    $activepaystackapi=GetActivePayStackApi()['apikey'];
    $sent=false;
   if($amount <=2500 ){
       $dadded= $amount *(1.5/100);
       $dtobdeducted = $dadded + $amount;
   }else if($amount > 2500){
       $dadded= $amount *(1.5/100);
       $dtobdeducted = 100+ $dadded + $amount;
   }
   $damount=$dtobdeducted*100;
   //paystack calculation
   $savecardpay= $dtobdeducted*100;
   $curl = curl_init();
   $arr =  array(
       "email" => "$dsavedemail",
       "amount" => "$damount",
       'authorization_code' => "$dsavedauth"
     );
       //below is the base url
       $url ="https://api.paystack.co/transaction/charge_authorization";
       $params =  json_encode($arr);
       $curl = curl_init();
       curl_setopt_array($curl, array(
         //u change the url infront based on the request u want
         CURLOPT_URL => $url,
         CURLOPT_POSTFIELDS => $params,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => "",
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 60,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         //change this based on what u need post,get etc
         CURLOPT_CUSTOMREQUEST => "POST",
         CURLOPT_HTTPHEADER => array(
           "authorization: Bearer $activepaystackapi", //replace this with your own test key
           "content-type: application/json",
           "cache-control: no-cache"
         ),
     ));
       $response = curl_exec($curl);
       $err = curl_error($curl);
       curl_close($curl);
       if ($err) {
          $sent=false;
       } else {
           $respondecode =  json_decode($response);
               if ($respondecode->status==true && $respondecode->data->status=="success") {
                   $sent=true;
                   $apiref=$respondecode->data->reference;
                   $paystackref=$apiref;
                   $transreffrom1app=0;

                   $bankpaidwith=1;
                   $systempaidwith=1;
                   $paystatus=1;
                   $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,systempaidwith=? WHERE orderid=?");
                   $checkdata->bind_param("ssss", $paystackref, $paystatus,$systempaidwith,$reference);
                   $checkdata->execute();

                   $update_data = $connect->prepare("UPDATE userwallet SET walletbal=walletbal+? WHERE wallettrackid=?");
                   $update_data->bind_param("ss", $amount, $wallettrackid);
                   $update_data->execute();
            }
    }
    return $sent;
}
// PAYSTACK

// MONIFY

public static function MonifyVerifyBVN($bvn,$accountno,$bankcode){
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $apiurl=$monfydata['url'];
    $verified=false;

    $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
    # try to contact service sending the money
    //below is the base url
    $url = "$apiurl/api/v1/auth/login";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "authorization: Basic $encodekey", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache",
        ),
    ));
    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $gtdata = json_decode($resp);
    if (isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true) {
        $accestoken = $gtdata->responseBody->accessToken;

        $arr = array(
        "bvn"=> "$bvn",
        "accountNumber"=> "$accountno",
        "bankCode"=> "$bankcode",
        );
        //below is the base url
        $url ="$apiurl/api/v1/";
        $params =  json_encode($arr);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url . "vas/bvn-account-match",
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer $accestoken", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
        ),
        ));
        $resp = curl_exec($curl);
        $err = curl_error($curl);
        // print($resp)
        // curl_close($curl);
        if ($err) {
            $verified=false;
            throw new \Exception("Error verifying BVN: $err");
        } else {
            $decodedresponse = json_decode($resp);
            $thestatus=$decodedresponse->requestSuccessful;
            if ($thestatus) {
                $verified=true;
            } else {
                $verified=false;
            }
        }
    }else{
        $verified=false;
    }
    return $verified;
}
public static function getallMonifyBanklist(){ 
    $allbnkarr=[];
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $apiurl=$monfydata['url'];
    $verified=false;

    $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
        # try to contact service sending the money
        //below is the base url
        $url ="$apiurl/api/v1/auth/login";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
        "authorization: Basic $encodekey", //replace this with your own test key
        "content-type: application/json",
        "cache-control: no-cache"
        ),
        ));
        $resp = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $gtdata=json_decode($resp);
        if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
            $accestoken=$gtdata->responseBody->accessToken;

            //below is the base url
            $url ="$apiurl/api/v1/banks";
            $curl = curl_init();
            curl_setopt_array($curl, array(
         //u change the url infront based on the request u want
         CURLOPT_URL => $url,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => "",
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 60,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         //change this based on what u need post,get etc
         CURLOPT_CUSTOMREQUEST => "GET",
         CURLOPT_HTTPHEADER => array(
         "authorization: Bearer $accestoken", //replace this with your own test key
         "content-type: application/json",
         "cache-control: no-cache"
         ),
         ));
            $resp = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $allbnkarr=[];
                throw new \Exception("Error getting bank names: $err");
            } else {
                $dbanks = json_decode($resp);
                if ($dbanks->requestSuccessful==true) {
                    $banks = $dbanks->responseBody;
                    foreach ($banks as $abc) {
                        $bankname= trim($abc->name);
                        $bankcoode=trim($abc->code);
                        array_push($allbnkarr, array( "name"=>$bankname, "code"=>$bankcoode,"combined"=>"$bankname^$bankcoode"));
                    }
                } else {
                    $allbnkarr=[];
                }
            }
        }else{
            $allbnkarr=[];
        }
        return $allbnkarr;
}
public static function getAccountNameMonify($bnkcode,$accno){
        $accname="";
        global $connect;
        $monfydata=GetActiveMonifyApi();
        $activepaystackapi=$monfydata['apikey'];
        $activemonifysecrete=$monfydata['secretekey'];
        $moniaccno=$monfydata['apiaccno'];
        $apiurl=$monfydata['url'];
        $verified=false;

        $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
        # try to contact service sending the money
        //below is the base url
        $url ="$apiurl/api/v1/auth/login";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
        "authorization: Basic $encodekey", //replace this with your own test key
        "content-type: application/json",
        "cache-control: no-cache"
        ),
        ));
        $resp = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $gtdata=json_decode($resp);
        if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
            $accestoken=$gtdata->responseBody->accessToken;
            //below is the base url
            $url ="$apiurl/api/v1/disbursements/account/validate?accountNumber=$accno&bankCode=$bnkcode";
            $curl = curl_init();
            curl_setopt_array($curl, array(
            //u change the url infront based on the request u want
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //change this based on what u need post,get etc
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer $accestoken", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
            ),
            ));
            $resp = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                $accname="Account not found";
                throw new \Exception("Error getting account names: $err");
            } else {
                $alldatain = json_decode($resp);
                if ($alldatain->requestSuccessful==true) {
                    $accname=$alldatain->responseBody->accountName;
                } else {
                    $accname="Account not found";
                }
            }
        }else{
            $accname="Account not found";
        }
        return $accname;
}
public static function checkMonifyBalanceAmount($moneytosend){

    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $apiurl=$monfydata['url'];
    $canpay=false;

    $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
    # try to contact service sending the money
    //below is the base url
    $url ="$apiurl/api/v1/auth/login";
    $curl = curl_init();
    curl_setopt_array($curl, array(
    //u change the url infront based on the request u want
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //change this based on what u need post,get etc
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => array(
    "authorization: Basic $encodekey", //replace this with your own test key
    "content-type: application/json",
    "cache-control: no-cache"
    ),
    ));
    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $gtdata=json_decode($resp);
    if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
        $accestoken=$gtdata->responseBody->accessToken;

         //below is the base url
        $url ="$apiurl/api/v2/disbursements/wallet-balance?accountNumber=$moniaccno";
        $curl = curl_init();
        curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
        "authorization: Bearer $accestoken", //replace this with your own test key
        "content-type: application/json",
        "cache-control: no-cache"
        ),
        ));
        $resp = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            $canpay=false;
            throw new \Exception("Error getting account names: $err");
        } else {
            $alldatain = json_decode($resp);
            if ($alldatain->requestSuccessful==true) {
                $mybal=$alldatain->responseBody->availableBalance;
                if($mybal>=$moneytosend){
                    $canpay=true;
                }else{
                    $canpay=false;
                }
            } else {
                $canpay=false;
            }
        }

    }else{
        $canpay=false;
    }
    return $canpay;
}
public static function monifySendMoney($amount,$accbnkcode,$paymentref,$bnkname,$acctosendto,$userbanrefcode,$transorderid){
    $paymentref = $transorderid;
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $apiurl=$monfydata['url'];
    $canpay=false;

    $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
    # try to contact service sending the money
    //below is the base url
    $url ="$apiurl/api/v1/auth/login";
    $curl = curl_init();
    curl_setopt_array($curl, array(
    //u change the url infront based on the request u want
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //change this based on what u need post,get etc
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => array(
    "authorization: Basic $encodekey", //replace this with your own test key
    "content-type: application/json",
    "cache-control: no-cache"
    ),
    ));
    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $gtdata=json_decode($resp);
    if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
        $accestoken=$gtdata->responseBody->accessToken;

        if (strtolower($bnkname)==strtolower("PalmPay")) {
            $accbnkcode=100033;
        } elseif (strtolower($bnkname)==strtolower("Paycom")) {
            $accbnkcode=304;
            $acctosendto=substr($acctosendto,1);
        } elseif (strtolower($bnkname)==strtolower("ALAT by WEMA")) {
            $accbnkcode=035;
        }
        

        $naration="$paymentref"."Services";
    
        $arr = array(
            "amount"=> $amount,
            "reference"=>"$paymentref",
            "narration"=>"$naration",
            "destinationBankCode"=>"$accbnkcode",
            "destinationAccountNumber"=> "$acctosendto",
            "currency"=>"NGN",
            "sourceAccountNumber"=> "$moniaccno"
            );
        //below is the base url
   //below is the base url
   $url ="$apiurl/api/v2/disbursements/single";
   $params =  json_encode($arr);
   $curl = curl_init();
   curl_setopt_array($curl, array(
       //u change the url infront based on the request u want
       CURLOPT_URL => $url,
       CURLOPT_POSTFIELDS => $params,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => "",
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 60,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       //change this based on what u need post,get etc
       CURLOPT_CUSTOMREQUEST => "POST",
       CURLOPT_HTTPHEADER => array(
       "authorization: Bearer $accestoken", //replace this with your own test key
       "content-type: application/json",
       "cache-control: no-cache"
       ),
       ));
   $resp = curl_exec($curl);
   $err = curl_error($curl);
   curl_close($curl);
        if ($err) {
            $canpay=false;
            throw new \Exception("Error getting account names: $err");
        } 
        else {
            // print($resp);
            $alldatain = json_decode($resp);
           // print_r($alldatain);

            if (strtolower($alldatain->responseBody->status)=="failed"||$alldatain->requestSuccessful==false) {
                $canpay=false;
            } 
            else {
                $canpay=true;// update transaction ref as paid
                if (!empty($transorderid)&&$transorderid!="") {
                    // generating  token
                    // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                    $companypayref = createUniqueToken(16,"userwallettrans","paymentref","MN",true,true,false);
                    $paystackref=$alldatain->responseBody->reference;
                    $paymenttoken=" ";
                    $valid=true; 
                    // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
                    $bankpaidwith=1;
                    $systempaidwith=2;
                    $paystatus=1;
                    $status = 1;
                    $time = date("h:ia, d M");
                    $approvedby="Automation";
                    $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,systempaidwith=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
                    $checkdata->bind_param("ssssssssss",$companypayref, $paystatus,$systempaidwith,$status,$time,$resp,$paystackref,$paymenttoken,$approvedby,$transorderid);
                    $checkdata->execute();
                }
            }
        }

    }else{
        $canpay=false;
    }
    
    return $canpay;
}
public static function monifygeneratePaymentUrl($fullname,$useremail,$orderid,$amount){
        // enusure the user has filled his name before he can generate an      account number

    if($amount <=2500 ){
        $dadded= $amount *(1.5/100);
        $dtobdeducted = $dadded + $amount;
    }else if($amount > 2500){
        $dadded= $amount *(1.5/100);
        $dtobdeducted = 100+ $dadded + $amount;
    }
    $generated=false;
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $monicontractcode = $monfydata['apiwallet'];
    $apiurl=$monfydata['url'];
    $accestoken=generateMonifyAccessToken();
    if( $accestoken){
            //creating user account
            $reserveaccounturl="$apiurl/api/v1/merchant/transactions/init-transaction";

            $arr = array(
                 "amount"=>$dtobdeducted,
                "customerEmail"=> "$useremail",
                "customerName"=> "$fullname",
                "paymentReference"=>"$orderid",
                "paymentDescription"=> "Buying",
                "currencyCode"=> "NGN",
                "contractCode"=> "".$monicontractcode."",
                "redirectUrl"=> "https://business.cardify.co/dashboard/payment_successful.php",
                "paymentMethods"=>["CARD","ACCOUNT_TRANSFER"]

            );
            $params =  json_encode($arr);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                    CURLOPT_URL => $reserveaccounturl,
                    CURLOPT_POSTFIELDS => $params,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_HTTPHEADER => array(
                            "Authorization: Bearer $accestoken",
                            "Content-Type:application/json"
                    ),
            ));
    
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $myresp = json_decode($response);
            // print_r(  $response);
            $responsestatus = $myresp->requestSuccessful;
            $responsemsg = $myresp->responseMessage;
            if (($responsestatus==true ||$responsestatus==1) && $responsemsg=="success") {
                $paystackref = $myresp->responseBody->transactionReference;
                $newreseverref = $myresp->responseBody->paymentReference;
                $checkoutUrl = $myresp->responseBody->checkoutUrl;
                
                $checkdata = $connect->prepare("UPDATE userwallettrans SET apipayref=? WHERE orderid=?");
                $checkdata->bind_param("ss",$paystackref,$newreseverref);
                $checkdata->execute();
                
                $generated=$checkoutUrl;
                
            }else{
                $generated=false;
            }
    }else{
             $generated=false;
    }
    return $generated;
}
public static function monifygenerateAccNumber($fname,$lname,$useremail,$banktype,$userid){
    // enusure the user has filled his name before he can generate an account number
    // $banktype  1=Moniepoint 2=Wema Bank, 3=Sterling Bank
    $bantypecode="";
    $banktypename="";
    if($banktype==1){
        $bantypecode=50515;
        $banktypename="Moniepoint";
    }else if($banktype==2){
        $bantypecode=035;
        $banktypename="Wema Bank";

    }else if($banktype==3){
        $bantypecode=232;
        $banktypename="Sterling Bank";
    }

        $dashname="$lname $fname";
        $dashemail=$useremail;
        $generated=false;
        global $connect;
        $monfydata=GetActiveMonifyApi();
        $activepaystackapi=$monfydata['apikey'];
        $activemonifysecrete=$monfydata['secretekey'];
        $moniaccno=$monfydata['apiaccno'];
        $monicontractcode = $monfydata['apiwallet'];
        $apiurl=$monfydata['url'];
        $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
        # try to contact service sending the money
        //below is the base url
        $url ="$apiurl/api/v1/auth/login";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            //u change the url infront based on the request u want
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //change this based on what u need post,get etc
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic $encodekey", //replace this with your own test key
                "content-type: application/json",
                "cache-control: no-cache"
            ),
        ));
        $resp = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $gtdata=json_decode($resp);
        if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
                $accestoken=$gtdata->responseBody->accessToken;
                //creating user account
                $reserveaccounturl="$apiurl/api/v1/bank-transfer/reserved-accounts";
                //getting uniq acc ref no
                $permitted_chars2 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $loop = 0;
                while ($loop==0) {
                    $myrefcode= generate_string($permitted_chars2, 6);
                    $check =  $connect->prepare("SELECT id FROM userpersonalbnkacc WHERE accrefcode=?");
                    $check->bind_param("s", $myrefcode);
                    $check->execute();
                    $result2 =  $check->get_result();
                    if ($result2->num_rows > 0) {
                        $loop = 0;
                    } else {
                        $loop = 1;
                        break;
                    }
                }
                $check->close();
                $arr = array(
                    "accountReference"=> "$myrefcode",
                    "accountName"=> "$dashname",
                    "currencyCode"=> "NGN",
                    "contractCode"=> "".$monicontractcode."",
                    "customerEmail"=> "$dashemail",
                    "customerName"=> "$dashname",
                    "getAllAvailableBanks"=> false,
                    "preferredBanks"=> ["$bantypecode"]

                );
                $params =  json_encode($arr);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                        CURLOPT_URL => $reserveaccounturl,
                        CURLOPT_POSTFIELDS => $params,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 60,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_HTTPHEADER => array(
                                "Authorization: Bearer $accestoken",
                                "Content-Type:application/json"
                        ),
                ));
        
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $myresp = json_decode($response);
                // print_r(  $response);
                $responsestatus = $myresp->requestSuccessful;
                $responsemsg = $myresp->responseMessage;
                if (($responsestatus==true ||$responsestatus==1) && $responsemsg=="success") {
                    // $newbankaccno = $myresp->responseBody->accounts[0]->accountNumber;
                    $newbankaccno = $myresp->responseBody->accountNumber;
                    $newreseverref = $myresp->responseBody->reservationReference;
                    $mainprovidusref = $myresp->responseBody->accountReference;
                    $accountName = $myresp->responseBody->accountName;
        
                    $type = 2;
                    $insert_data = $connect->prepare("INSERT INTO userpersonalbnkacc (userid,bankname,accno,accrefcode,accserverrefcode,banksystemtype,acctname,banktypeis) VALUES (?,?,?,?,?,?,?,?)");
                    $insert_data->bind_param("ssssssss", $userid, $banktypename, $newbankaccno,$myrefcode,$newreseverref,$type,$accountName,$type);
                    $insert_data->execute();
                    $insert_data->close();
                    
                    
                $generated=true;
                
            }else{
                $generated=false;
            }
    }else{
             $generated=false;
    }
    return $generated;
}
public static function monifygenerateElecAccNumber($fname,$lname,$useremail,$banktype,$userid,$metertid){
    // enusure the user has filled his name before he can generate an account number
    // $banktype  1=Moniepoint 2=Wema Bank, 3=Sterling Bank
    $bantypecode="";
    $banktypename="";
    if($banktype==1){
        $bantypecode=50515;
        $banktypename="Moniepoint";
    }else if($banktype==2){
        $bantypecode=035;
        $banktypename="Wema Bank";

    }else if($banktype==3){
        $bantypecode=232;
        $banktypename="Sterling Bank";
    }

    $dashname="$lname $fname";
    $dashemail=$useremail;
    $generated=false;
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $monicontractcode = $monfydata['apiwallet'];
    $apiurl=$monfydata['url'];
    $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
    # try to contact service sending the money
    //below is the base url
    $url ="$apiurl/api/v1/auth/login";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "authorization: Basic $encodekey", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
        ),
    ));
    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $gtdata=json_decode($resp);
    if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
            $accestoken=$gtdata->responseBody->accessToken;
            //creating user account
            $reserveaccounturl="$apiurl/api/v1/bank-transfer/reserved-accounts";
            //getting uniq acc ref no
            $permitted_chars2 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $loop = 0;
            while ($loop==0) {
                $myrefcode= generate_string($permitted_chars2, 6);
                $check =  $connect->prepare("SELECT id FROM bill_voucher_saved_meters WHERE accrefcode=?");
                $check->bind_param("s", $myrefcode);
                $check->execute();
                $result2 =  $check->get_result();
                if ($result2->num_rows > 0) {
                    $loop = 0;
                } else {
                    $loop = 1;
                    break;
                }
            }
            $check->close();
            $arr = array(
                "accountReference"=> "$myrefcode",
                "accountName"=> "$dashname",
                "currencyCode"=> "NGN",
                "contractCode"=> "".$monicontractcode."",
                "customerEmail"=> "$dashemail",
                "customerName"=> "$dashname",
                "getAllAvailableBanks"=> false,
                "preferredBanks"=> ["$bantypecode"]

            );
            $params =  json_encode($arr);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                    CURLOPT_URL => $reserveaccounturl,
                    CURLOPT_POSTFIELDS => $params,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_HTTPHEADER => array(
                            "Authorization: Bearer $accestoken",
                            "Content-Type:application/json"
                    ),
            ));
    
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $myresp = json_decode($response);
            // print_r(  $response);
            $responsestatus = $myresp->requestSuccessful;
            $responsemsg = $myresp->responseMessage;
            if (($responsestatus==true ||$responsestatus==1) && $responsemsg=="success") {
                // $newbankaccno = $myresp->responseBody->accounts[0]->accountNumber;
                 $newbankaccno = $myresp->responseBody->accountNumber;
                 
                $newreseverref = $myresp->responseBody->reservationReference;
                $mainprovidusref = $myresp->responseBody->accountReference;
                $accountName = "MFY/LIGHT $dashname-" .$myresp->responseBody->accountName;
    
                $type = 2;
                
                $insert_data = $connect->prepare("UPDATE bill_voucher_saved_meters SET bankname=?,bankacc=?,accrefcode=?,accserverrefcode=?,banksystemtype=?,account_name=?,banktypeis=? WHERE trackid=? AND userid=?");
                $insert_data->bind_param("ssssssssi",  $banktypename, $newbankaccno,$myrefcode,$newreseverref,$type,$accountName,$type,$metertid,$userid);
                $insert_data->execute();
                $insert_data->close();
                
                
                $generated=true;
                
            }else{
                $generated=false;
            }
    }else{
             $generated=false;
    }
    return $generated;
}
public static function verifymonifypay($reference,$useremail, $uname, $userid,$systemtransref){
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $apiurl=$monfydata['url'];
    $verified=false;

    $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
    # try to contact service sending the money
    //below is the base url
    $url ="$apiurl/api/v1/auth/login";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
        "authorization: Basic $encodekey", //replace this with your own test key
        "content-type: application/json",
        "cache-control: no-cache"
        ),
    ));
    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $gtdata=json_decode($resp);
    if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
        $accestoken=$gtdata->responseBody->accessToken;

        //below is the base url
        $encoded = rawurlencode($reference);
        $statuscheckurl =  "$apiurl/api/v2/transactions/$encoded";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            //u change the url infront based on the request u want
            CURLOPT_URL => $statuscheckurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //change this based on what u need post,get etc
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer $accestoken", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $myresp = json_decode($response);
        // print_r($response);
        $responsestatus = $myresp->requestSuccessful;
        $responsemsg = $myresp->responseMessage;
        if (($responsestatus==true ||$responsestatus==1) && $responsemsg=="success" &&  $myresp->responseBody->paymentStatus=="PAID") {//check if transacon is good
            $valid=true; 
            // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
            $paystackref= $myresp->responseBody->transactionReference;
            $paymenttoken=$myresp->responseBody->paymentReference;
            
            $notyetpaid=1;
            $checkdata =  $connect->prepare("SELECT * FROM  userwallettrans WHERE apipayref=? AND status=?  AND userid=?");
            $checkdata->bind_param("sis",$paystackref, $notyetpaid,$userid);
            $checkdata->execute();
            $dresult = $checkdata->get_result(); 
           if(empty($reference)) {
                $valid=false;
           } else if($dresult ->num_rows > 0){
                $valid=false;
           }else{
                // generating  token
                // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                $companypayref = createUniqueToken(16,"userwallettrans","paymentref","MBANKT",true,true,false);
               $valid=true; 
               // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
               $bankpaidwith=1;
               $systempaidwith=2;
               $paystatus=1;
               $status = 1;
               $time = date("h:ia, d M");
               $approvedby="Automation";
               $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,systempaidwith=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
               $checkdata->bind_param("ssssssssss",$companypayref, $paystatus,$systempaidwith,$status,$time,$response,$paystackref,$paymenttoken,$approvedby,$systemtransref);
               $checkdata->execute();
           }
        }
    }
return $valid;
}
public static function giveUserTheirPayOnMonify($transref,$useremail, $uname,$userid,$wallettrackid){
    global $connect;
    $successful=false;
    // data from webhook 
    // $json = file_get_contents('php://input');
    // // Converts it into a PHP object
    // $data = json_decode($json);
    // //if nothing pass null
    // $transactionref = cleanme($data->transactionReference);
    // $paymentref = cleanme($data->paymentReference);
    // $amtpaid = cleanme($data->amountPaid);
    // $paiddate = cleanme($data->paidOn);
    // $paymentStatus = cleanme($data->paymentStatus);
    // $paymentdescription = cleanme($data->paymentDescription);
    // $transachash = cleanme($data->transactionHash);
    // $paymentmethod =  cleanme($data->paymentMethod);
    // $customerdet = $data->customer;
    // $customeremail = cleanme($customerdet->email);
    // $customername = cleanme($customerdet->name);
        
    //check if the transaction and the email coming and amount exist
    $notyetpaid=0;
    $checkdata =  $connect->prepare("SELECT * FROM  userwallettrans WHERE orderid=? AND status=?  AND userid=?");
    $checkdata->bind_param("sis",$transref, $notyetpaid,$userid);
    $checkdata->execute();
    $dresult = $checkdata->get_result(); 
    if(empty($transref)) {
            $successful=false;
   } else if($dresult ->num_rows == 0){
        $successful=false;
   }else{
       $checkdata->close();
       //get the transaction ref to use from DB
       $rr = $dresult->fetch_assoc();
       $tref = $rr['orderid'];
       $amnt = $rr['amttopay'];
       //call back action process
           if (verifymonifypay($transref,$useremail, $uname, $userid,$transref)) {
                    $successful=true;
                   $update_data = $connect->prepare("UPDATE userwallet SET walletbal=walletbal+? WHERE wallettrackid=?");
                   $update_data->bind_param("is", $amnt, $wallettrackid);
                   $update_data->execute();
            }
    }
 return $successful;
}
public static function monifycomputeSHA512TransactionHash($stringifiedData) {
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $clientSecret= $activemonifysecrete;
    $computedHash = hash_hmac('sha512', $stringifiedData, $clientSecret);
    return $computedHash;
}
public static function generateMonifyAccessToken(){
 $generated=false;
    global $connect;
    $monfydata=GetActiveMonifyApi();
    $activepaystackapi=$monfydata['apikey'];
    $activemonifysecrete=$monfydata['secretekey'];
    $moniaccno=$monfydata['apiaccno'];
    $monicontractcode = $monfydata['apiwallet'];
    $apiurl=$monfydata['url'];
    $encodekey = base64_encode("$activepaystackapi:$activemonifysecrete");
    # try to contact service sending the money
    //below is the base url
    $url ="$apiurl/api/v1/auth/login";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        //u change the url infront based on the request u want
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //change this based on what u need post,get etc
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "authorization: Basic $encodekey", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
        ),
    ));
    $resp = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $gtdata=json_decode($resp);
    if(isset($gtdata->requestSuccessful) && $gtdata->requestSuccessful==true){
             $generated=$gtdata->responseBody->accessToken;
    }
    return  $generated;
}
public static function verifymonifypayment($reference){
global $connect;
$monfydata=GetActiveMonifyApi();
$activepaystackapi=$monfydata['apikey'];
$activemonifysecrete=$monfydata['secretekey'];
$moniaccno=$monfydata['apiaccno'];
$apiurl=$monfydata['url'];
$verified=false;

$accestoken=generateMonifyAccessToken();
    if( $accestoken){

        //below is the base url
        $encoded = rawurlencode($reference);
        $statuscheckurl =  "$apiurl/api/v2/transactions/$encoded";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            //u change the url infront based on the request u want
            CURLOPT_URL => $statuscheckurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //change this based on what u need post,get etc
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "authorization: Bearer $accestoken", //replace this with your own test key
            "content-type: application/json",
            "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $myresp = json_decode($response);
        // print_r($response);
        $responsestatus = $myresp->requestSuccessful;
        $responsemsg = $myresp->responseMessage;
        if (($responsestatus==true ||$responsestatus==1) && $responsemsg=="success" &&  $myresp->responseBody->paymentStatus=="PAID") {//check if transacon is good
            $valid=true; 
        }
    }
    return $valid;
}
// MONIFY


// SD CARD

public static  function createVC_customer($userid,$customerFullname,$customertype,$customerphonenumber,$customerEmail,$customerFname, $customerLname,$customerDob,$customerBVN,$customerAddress,$customerCity,$customerState,$customerCountry,$customerPostalCode){
    $response=['status'=>false,'msg'=>'API not called','json'=>''];
    $systemData =self::getApiKeys("public_key,baseurl",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl']; 
        $token= $activeshis['public_key'];
        $postdatais=array (
            'type' => $customertype,//'individual',
            'name' => $customerFullname,
            'status' => 'active',
            'phoneNumber' => $customerphonenumber,
            'emailAddress' => $customerEmail,
            'individual' => array (
                'firstName' => $customerFname,
                'lastName' => $customerLname,
                'dob' => $customerDob,
                'identity' => array (
                    'type' => 'BVN',
                    'number' => $customerBVN,
                ),
                ),
            'billingAddress' => array (
                'line1' => $customerAddress,
                'line2' => '',
                'city' =>$customerCity,
                'state' =>$customerState,
                'country' =>$customerCountry,
                'postalCode' =>$customerPostalCode,
            ),
        );
        
        $jsonpostdata=json_encode($postdatais);
        $url ="$baseurl/customers";
        $curl = curl_init();
        curl_setopt_array(
        $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => trim($jsonpostdata),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                        
                ),
        ));
        $userdetails = curl_exec($curl);
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'SD CREATE  CUSTOMER VC','jsonresp'=>$userdetails]);
        curl_close($curl);
        $breakdata = json_decode($userdetails);
        if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
            $accountid=$breakdata->data->_id;
            $response=['status'=>true,'msg'=>$accountid,'json'=>$userdetails];
        }
    }
    return $response;
}

public static function generate_User_VC($userid,$currency,$customerId,$amount,$companyUsdWalletid,$card_typecountry,$card_typebrand,$cardTypeName,$card_trackid,$maxdaily,$maxweekly,$maxMonthly){
        $response=['status'=>false,'msg'=>'API not called','json'=>''];
        $systemData =self::getApiKeys("public_key,private_key",12);
        if (!Utility_Functions::input_is_invalid($systemData)) {
            $activeshis = $systemData;
            $vaulturl=$activeshis['private_key']; 
            $token= $activeshis['public_key'];

            $postdatais=array (
                'customerId' => $customerId,
                'debitAccountId'=>$companyUsdWalletid,// => $walletid,
                'issuerCountry' => $card_typecountry,
                'brand' => $card_typebrand,
                'amount'=>intval($amount),
                'type' => $cardTypeName,
                'currency' => $currency,
                'status' => 'active',
                'metadata' =>  array ("trackid"=>$card_trackid),
                'spendingControls' => 
                array (
                    'channels' =>   array (
                        'atm' => true,
                        'pos' => true,
                        'web' => true,
                        'mobile' => true,
                    ),
                    'allowedCategories' => array ( ),
                    'blockedCategories' => array ( ),
                    'spendingLimits' => array (
                            0 => 
                            array (
                            'amount' => intval($maxdaily),
                            'interval' => 'daily',
                            ),
                            1 => 
                            array (
                            'amount' => intval($maxweekly),
                            'interval' => 'weekly',
                            ),
                            2 => 
                            array (
                            'amount' => intval($maxMonthly),
                            'interval' => 'monthly',
                            ),
                    ),
                ),
            );
            $jsonpostdata=json_encode($postdatais);
            $url ="$vaulturl/cards";
            $curl = curl_init();
            curl_setopt_array(
                $curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => trim($jsonpostdata),
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer $token",
                        "content-type: application/json",
                        'accept: application/json',
                    ),
            ));
            $userdetails = curl_exec($curl);

            DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'SD CREATE VC','jsonresp'=>$userdetails]);
     
            // print_r($userdetails);
            $err = curl_error($curl);
            // print_r($err);
            curl_close($curl);
                $breakdata = json_decode($userdetails);
            if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
                $response=['status'=>true,'msg'=>"Created",'json'=>$userdetails];
            }else{
                // message
                $message="An error occured when generating card";
                if(isset($breakdata->message)){
                    $message=$breakdata->message;
                }
                $message.=" $err $userid";
                $response=['status'=>false,'msg'=>$message,'json'=>''];
            }         
        }
        return $response;
}

public static function generateVC_MainAndSubWallet($walletype,$customerId,$currency,$userid){
          global $connect;
        $vc_data=GetActiveVirtualCardApi($currency);
        $success=false;
        $token=$vc_data['token'];
        $baseurl=$vc_data['base_url']; 
        $vaulturl=$vc_data['vault_url']; 
        $currency=$vc_data['currency']; 
        $accountType=$vc_data['account_type'];
        $postdatais=[];
        // $walletype 1 main wallet 2 user wallet
        if($walletype==1){
            $account="account";//"wallet";//account
            $postdatais=array (
                'currency' => $currency,
                'type' => $account,
                'accountType' => $accountType,
            );
        }else if($walletype==2){
           $account="wallet";
           $postdatais=array (
                'currency' => $currency,
                'type' => $account,
                'accountType' => $accountType,
                "customerId" => $customerId
            ); 
        }
        $jsonpostdata=json_encode($postdatais);
        $url ="$baseurl/accounts";
        $curl = curl_init();
        curl_setopt_array(
                $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => trim($jsonpostdata),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                     
                ),
            ));
        $userdetails = curl_exec($curl);
        $allresp="$userdetails";
        $paymentidisni="SD VC GEN ADDRESS";
        $orderidni="$jsonpostdata";
        $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
        $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
        $insert_data->execute();
        $insert_data->close();
        // print_r($userdetails);
        $err = curl_error($curl);
        // print_r($err);
        curl_close($curl);
        $breakdata = json_decode($userdetails);
        if($breakdata->statusCode==200){
            $accountid=$breakdata->data->_id;
            
            if($walletype==1){//main wallet
                        $name="Main $currency account";
                          // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                        $trackid= createUniqueToken(5,"vc_main_accounts","trackid","$currency",true,true,false);
                        $active=1;
                        $insert_data = $connect->prepare("INSERT INTO vc_main_accounts (name,account_id,currency,trackid,status,account_json) VALUES (?,?,?,?,?,?)");
                        $insert_data->bind_param("ssssss",$name, $accountid,$currency,$trackid,$active,$userdetails);
                        $insert_data->execute();
            }else if($walletype==2){// user wallet
                // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                $trackid= createUniqueToken(5,"vc_customer_wallets","trackid","$currency",true,true,false);
                $active=1;
                $insert_data = $connect->prepare("INSERT INTO vc_customer_wallets (user_id,customer_id,wallet_id, status,trackid, account_type,currency,json_response) VALUES (?,?,?,?,?,?,?,?)");
                $insert_data->bind_param("ssssssss",$userid,$customerId, $accountid,$active,$trackid,$accountType, $currency,$userdetails);
                $insert_data->execute();
            } 
            $success=true;
        }else{
            
        }
        
        return $success;
}

public static function getLiveNGNtoUSDRate($fund){
    $amount=0;

    $systemData =self::getApiKeys("baseurl,public_key",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $token= $activeshis['public_key'];
  
        $url ="$baseurl/accounts/transfer/rate/USDNGN";
        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
                 
            ),
        ));
        $userdetails = curl_exec($curl);
        curl_close($curl);
    
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>'',"name"=>'SD GET RATE','jsonresp'=>$userdetails]);
        $breakdata = json_decode($userdetails);
        if(isset($breakdata->statusCode) &&     $breakdata->statusCode==200){
            if($fund==1){
                // getrate
                $amount=$breakdata->data->buy; //usd to naira  
            }else{
                $amount=$breakdata->data->sell;// naira to usd
            }
        }
    }
               
 return $amount;
}

public static function getMainAccountBalance($accountid){
    $amount="0";
    $systemData =self::getApiKeys("baseurl,public_key",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl'];
        $token= $activeshis['public_key'];


       $url ="$baseurl/accounts/$accountid/balance";
       $curl = curl_init();
        curl_setopt_array(
            $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                ),
            )
        );
       $userdetails = curl_exec($curl);
       curl_close($curl);

       DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>'',"name"=>'SD GET CARD BAL','jsonresp'=>$userdetails]);
       $breakdata = json_decode($userdetails);
       if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
               $amount=strval($breakdata->data->availableBalance); 
       }
    }         
    return $amount;
}

public static function fundUserWallet($walletid,$amount,$narration,$payref,$companyUsdWalletid){
    $response=['status'=>false,'msg'=>'API not called','json'=>''];
    $systemData =self::getApiKeys("public_key,baseurl",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $baseurl=$activeshis['baseurl']; 
        $token= $activeshis['public_key'];
           
        $postdatais=array (
            'debitAccountId' => $companyUsdWalletid,
            'creditAccountId' =>$walletid ,
            'amount' =>floatval($amount),
            "narration"=> $narration,
            "paymentReference"=>$payref
        );
        $jsonpostdata=json_encode($postdatais);
        // print($jsonpostdata);
        $url ="$baseurl/accounts/transfer";
        $curl = curl_init();
        curl_setopt_array(
            $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => trim($jsonpostdata),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token",
                "content-type: application/json",
                'accept: application/json',
                
            ),
        ));
        $userdetails = curl_exec($curl);
                   
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$jsonpostdata,"name"=>'SD VC FUND','jsonresp'=>$userdetails]);

        // print_r($userdetails);
        $err = curl_error($curl);
        // print_r($err);
        curl_close($curl);
        $breakdata = json_decode($userdetails);
        if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
            $response=['status'=>true,'msg'=>'Done','json'=>$userdetails];
        }else{
            $message="An error occured when funding card";
            if(isset($breakdata->message)){
                $message=$breakdata->message;
            }
            $message.=" $err $userdetails";
            $response=['status'=>false,'msg'=>$message,'json'=>$userdetails];
        }
    }
       return $response;
}

public static function fundCompanyWallet($walletid,$amount,$narration,$payref,$currency,$userid){
   global  $connect;
       $vc_data=GetActiveVirtualCardApi($currency);
       $success=false;
       $token=$vc_data['token'];
       $baseurl=$vc_data['base_url']; 
       $currency=$vc_data['currency']; 
        
       $active=1;
       $getdataemail =  $connect->prepare("SELECT account_id,currency FROM vc_main_accounts WHERE currency=? AND status=?");
       $getdataemail->bind_param("si",$currency,$active);
       $getdataemail->execute();
       $getresultemail = $getdataemail->get_result();
       if($getresultemail->num_rows> 0){
               $getthedata= $getresultemail->fetch_assoc();
               $companyUsdWalletid=$getthedata['account_id'];
               $fundcurrency=$getthedata['currency'];
               
               $enoughfund=true;
           
               if($enoughfund){
                   $postdatais=array (
                       'debitAccountId' => $walletid,
                       'creditAccountId' => $companyUsdWalletid,
                       'amount' =>floatval($amount),
                       "narration"=> $narration,
                       "paymentReference"=>$payref
                   );
                   $jsonpostdata=json_encode($postdatais);
                   // print($jsonpostdata);
                   $url ="$baseurl/accounts/transfer";
                   $curl = curl_init();
                   curl_setopt_array(
                           $curl, array(
                           CURLOPT_URL => $url,
                           CURLOPT_RETURNTRANSFER => true,
                           CURLOPT_ENCODING => "",
                           CURLOPT_MAXREDIRS => 10,
                           CURLOPT_TIMEOUT => 60,
                           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                           CURLOPT_CUSTOMREQUEST => "POST",
                           CURLOPT_POSTFIELDS => trim($jsonpostdata),
                           CURLOPT_HTTPHEADER => array(
                               "Authorization: Bearer $token",
                               "content-type: application/json",
                               'accept: application/json',
                                
                           ),
                       ));
                   $userdetails = curl_exec($curl);
                   
                   $allresp="$userdetails";
                   $paymentidisni="SD VC UNLOAD ping";
                   $orderidni="$jsonpostdata";
                   $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
                   $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
                   $insert_data->execute();
                   $insert_data->close();
       
                   // print_r($userdetails);
                   $err = curl_error($curl);
                   // print_r($err);
                   curl_close($curl);
                   $breakdata = json_decode($userdetails);
                   if($breakdata->statusCode==200){
                       $orderidis=$breakdata->data->paymentReference;
                       $paymenttoken=$breakdata->data->_id;
                       $notyetpaid=1;
                       $checkdata =  $connect->prepare("SELECT * FROM  userwallettrans WHERE apipayref=? AND status=?  AND userid=?");
                       $checkdata->bind_param("sis",$orderidis, $notyetpaid,$userid);
                       $checkdata->execute();
                       $dresult = $checkdata->get_result(); 
                       if(empty($orderidis)) {
                                $success=false;
                      } else if($dresult ->num_rows > 0){
                            $success=false;
                      }else{
                           // generating  token
                           // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                           $companypayref = createUniqueToken(16,"userwallettrans","paymentref","UVC",true,true,false);
                           $success=true; 
                          // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
                          $bankpaidwith=1;
                          $systempaidwith=2;
                          $paystatus=1;
                          $status = 2;
                          $time = date("h:ia, d M");
                          $approvedby="Automation";
                          $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
                          $checkdata->bind_param("sssssssss",$companypayref, $paystatus,$status,$time,$userdetails,$orderidis,$paymenttoken,$approvedby,$payref);
                          $checkdata->execute();
                      }
                   }else{
                         // message
                                       $from="SUDO CARD UNLOAD ERROR $userid";
                                        $message="An error occured when unloading card";
                                       if(isset($breakdata->message)){
                                            $message=$breakdata->message;
                                       }
                                      $message.=" $err $userdetails";
                                       system_notify_crash_handler($message,$from);
                   }
               }
               
       }
       return $success;
}

public static function revealCardFullData($cardid){
    $response=['status'=>false,'data'=>''];
    $systemData =self::getApiKeys("public_key,private_key",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
       $activeshis = $systemData;
       $vaulturl=$activeshis['private_key']; 
       $token= $activeshis['public_key'];
       $url ="$vaulturl/cards/$cardid?reveal=true";
       $curl = curl_init();

       curl_setopt_array(
            $curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $token",
                    "content-type: application/json",
                    'accept: application/json',
                ),
            )
        );
       $userdetails = curl_exec($curl);
       DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$url,"name"=>'SD VC REVEL CARD','jsonresp'=>$userdetails]);
       // print_r($err);
       curl_close($curl);
       $breakdata = json_decode($userdetails);
       if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
           $response=['status'=>true,'data'=>$userdetails];
       }
    }
    return $response;
}

public static function changeCardPin($currency,$cardid,$oldpin,$newpin){
   global $connect;
       $vc_data=GetActiveVirtualCardApi($currency);
       $success=false;
       $token=$vc_data['token'];
       $baseurl=$vc_data['base_url']; 
       $vaulturl=$vc_data['vault_url']; 
       $currency=$vc_data['currency']; 
       $accountType=$vc_data['account_type'];
       
       //   $status="active";//inactive,canceled,active
       $postdatais=array (
           'oldPin' => $oldpin,
           'newPin'=>$newpin
       );
       $jsonpostdata=json_encode($postdatais);
       // print($jsonpostdata);
       $url ="$baseurl/cards/$cardid/pin";
       $curl = curl_init();
       curl_setopt_array(
               $curl, array(
               CURLOPT_URL => $url,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => "",
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 60,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => "PUT",
               CURLOPT_POSTFIELDS => trim($jsonpostdata),
               CURLOPT_HTTPHEADER => array(
                   "Authorization: Bearer $token",
                   "content-type: application/json",
                   'accept: application/json',
                    
               ),
           ));
       $userdetails = curl_exec($curl);
       
               $allresp="$userdetails";
       $paymentidisni="SD VC CHANGE CARD PIN";
       $orderidni="$jsonpostdata";
       $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
       $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
       $insert_data->execute();
       $insert_data->close();
       
       // print_r($userdetails);
       $err = curl_error($curl);
       // print_r($err);
       curl_close($curl);
       $breakdata = json_decode($userdetails);
       if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
           $success=true;
       }else{
             // message
                                       $from="SUDO CHANGE CARD PIN $cardid";
                                        $message="An error occured when trying to freeze/delete a card";
                                       if(isset($breakdata->message)){
                                            $message=$breakdata->message;
                                       }
                                      $message.=" $err $userdetails";
                                       system_notify_crash_handler($message,$from);
       }

       return $success;
}

public static function changeCardStatus($cardid,$status){
    $success=false;
    $systemData =self::getApiKeys("baseurl,public_key",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
       $activeshis = $systemData;
       $baseurl=$activeshis['baseurl']; 
       $token= $activeshis['public_key'];
       //   $status="active";//inactive,canceled,active
       $postdatais=array (
           'status' => $status,
       );
       $jsonpostdata=json_encode($postdatais);
       // print($jsonpostdata);
       $url ="$baseurl/cards/$cardid";
       $curl = curl_init();
       curl_setopt_array(
               $curl, array(
               CURLOPT_URL => $url,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => "",
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 60,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => "PUT",
               CURLOPT_POSTFIELDS => trim($jsonpostdata),
               CURLOPT_HTTPHEADER => array(
                   "Authorization: Bearer $token",
                   "content-type: application/json",
                   'accept: application/json',
                    
               ),
        ));
       $userdetails = curl_exec($curl);

       DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=> $jsonpostdata,"name"=>'SD VC CHANGE STATUS','jsonresp'=>$userdetails]);

       curl_close($curl);
       $breakdata = json_decode($userdetails);
       if(isset($breakdata->statusCode) && $breakdata->statusCode==200){
           $success=true;
       }
    }
    return $success;
}



// BC CARDS

public static function createBCVC_customer($userid,$currency){
    global $connect;
       $valid=false;
       $vc_data=GetActiveBCVirtualCardApi($currency);
       $success=false;
       $authkey=$vc_data['authkey'];
       $secretekey=$vc_data['secretekey'];
       $issueid=$vc_data['issueid'];
       $baseurl=$vc_data['baseurl']; 
       $currency=$vc_data['currency']; 
    
       
       $active=1;
       $getdataemail =  $connect->prepare("SELECT * FROM kyc_details WHERE user_id=?");
       $getdataemail->bind_param("s",$userid);
       $getdataemail->execute();
       $getresultemail = $getdataemail->get_result();
       if( $getresultemail->num_rows> 0){
               $getthedata= $getresultemail->fetch_assoc();
               
                 // create customer 
               $customerFname=$getthedata['fname'];
               $customerLname=$getthedata['lname'];
               $customerFullname=$getthedata['fullname'];
               $customerAddress=$getthedata['full_address'];
               $customerCity=$getthedata['city'];
               $customerState=$getthedata['stateorigin'];
               $customerCountry=$getthedata['country'];
               $customerPostalCode=$getthedata['postalcode'];
               $customerhouse_number=$getthedata['house_number'];
               $customerreg_id_number=$getthedata['reg_id_number'];
               $customerreg_type=$getthedata['reg_type'];
               
               $customerphonenumber=$getthedata['phoneno'];
               $customerEmail=$getthedata['email'];
               $customerDob=$getthedata['dob'];
               $customerBVN=$getthedata['bvn'];
               $customerfront_regcard=$getthedata['vc_verify_img'];
               $redcardUrl="https://app.cardify.co/assets/images/userregulatorycards/$customerfront_regcard";
               $customertype='individual';
               if(empty($customerfront_regcard)){
                   $valid=false;
               }else{
                   $identify_array=array();
                   
                   if(strtolower($customerCountry)=="nigeria"){
                       $regtypetext="";
                       if($customerreg_type==1){
                          $regtypetext="NIGERIAN_NIN"; 
                       }else if($customerreg_type==2){
                          $regtypetext="NIGERIAN_DRIVERS_LICENSE"; 
                       }if($customerreg_type==3){
                          $regtypetext="NIGERIAN_PVC"; 
                       }if($customerreg_type==4){
                          $regtypetext="NIGERIAN_INTERNATIONAL_PASSPORT"; 
                       }
                       // you are to send 1 for National id card, 2 for drivers license 3 for Voters card and 4 for international passport
                       // "NIGERIAN_NIN" or "NIGERIAN_INTERNATIONAL_PASSPORT" or "NIGERIAN_PVC" or "NIGERIAN_DRIVERS_LICENSE",
                       $identify_array=  array (
                           'id_type' => $regtypetext,
                           'id_no' => $customerreg_id_number,
                           'id_image' => $redcardUrl,
                           'bvn' => $customerBVN,
                       );
                   }
                   
                   $postdatais=array (
                               'first_name' => $customerFname,
                               'last_name' => $customerLname,
                               'address' => array (
       'address' =>$customerAddress,
       'city' => $customerCity,
       'state' => $customerState,
       'country' =>  $customerCountry,
       'postal_code' => $customerPostalCode,
       'house_no' => $customerhouse_number,
     ),
                               'phone' => $customerphonenumber,
                               'email_address' => $customerEmail,
                               'identity' => $identify_array,
                               'meta_data' => array (
       'userid' => $userid,
     ),
                   );
                   $jsonpostdata=json_encode($postdatais);
                   //  print($jsonpostdata);
                   $url ="$baseurl/cardholder/register_cardholder";
                   $curl = curl_init();
                   curl_setopt_array(
                   $curl, array(
                           CURLOPT_URL => $url,
                           CURLOPT_RETURNTRANSFER => true,
                           CURLOPT_ENCODING => "",
                           CURLOPT_MAXREDIRS => 10,
                           CURLOPT_TIMEOUT => 60,
                           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                           CURLOPT_CUSTOMREQUEST => "POST",
                           CURLOPT_POSTFIELDS => trim($jsonpostdata),
                           CURLOPT_HTTPHEADER => array(
                               "token: Bearer  $authkey",
                               "content-type: application/json",
                               'accept: application/json',
                           ),
                       ));
                   $userdetails = curl_exec($curl);
                   
                   
                           $allresp="$userdetails";
       $paymentidisni="BC CREATE CUST";
       $orderidni="$jsonpostdata";
       $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
       $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
       $insert_data->execute();
       $insert_data->close();
       
       
                   // print_r($userdetails);
                   $err = curl_error($curl);
                   // print_r($err);
                   curl_close($curl);
                   $breakdata = json_decode($userdetails);
                   if(isset($breakdata->status) && $breakdata->status=="success"){
                       $valid=true;
                       $cardhlderidis=$breakdata->data->cardholder_id;
                       // save data in a tabel for webhook reference
                       // start here
                       $doneis=2;
                       $update_data = $connect->prepare("UPDATE users SET vc_card_token=?,vc_card_verified=? WHERE id=?");
                       $update_data->bind_param("sss",$cardhlderidis,$doneis,$userid);
                       $update_data->execute();
                       $update_data->close();
                       
                   }else  if(isset($breakdata->message) && $breakdata->message=="A cardholder already exists with this BVN"){
                       $valid=true;
                       $cardhlderidis=$breakdata->data->cardholder_id;
                       $accountid=$cardhlderidis;
                       // save data in a tabel for webhook reference
                       // start here
                       $done=1;
                       $update_data = $connect->prepare("UPDATE users SET vc_card_token=? ,vc_card_verified=? WHERE id=?");
                       $update_data->bind_param("sis",$cardhlderidis, $done,$userid);
                       $update_data->execute();
                       $update_data->close();
                       
                       
                       $trackid= createUniqueToken(5,"vc_customers","trackid","USD",true,true,false);
                       $active=1;
                       $supplier=2;
                       $customertype="individual";
                       $insert_data = $connect->prepare("INSERT INTO  vc_customers (user_id,customer_id,customer_type,trackid,status,json,supplier) VALUES (?,?,?,?,?,?,?)");
                       $insert_data->bind_param("sssssss",$userid, $accountid,$customertype,$trackid,$active,$userdetails,$supplier);
                       $insert_data->execute();
                   }else{
                       if(isset($breakdata->message)){
                           $valid= $breakdata->message;
                       }else{
                         $valid="Opps an error occured, try again later";  
                       }
                   }
               }
       }
       return $valid;
}

public static function generate_User_BcVC($userid,$currency,$cardtype_tid,$customerId,$amount){
       global $connect;
       $valid=false;
       $vc_data=GetActiveBCVirtualCardApi($currency);
       $success=false;
       $authkey=$vc_data['authkey'];
       $secretekey=$vc_data['secretekey'];
       $issueid=$vc_data['issueid'];
       $baseurl=$vc_data['baseurl']; 
       $relay_url=$vc_data['relay_url'];
       $currency=$vc_data['currency']; 
       $creationfee=1;
           
       $active=1;
       $getdataemail =  $connect->prepare("SELECT cardbrand,country,cardtype,daily_limit,monthly_limit,weekly_limit,currency,need_activation FROM vc_type WHERE trackid=? AND status=?");
       $getdataemail->bind_param("si",$cardtype_tid,$active);
       $getdataemail->execute();
       $getresultemail = $getdataemail->get_result();
       if($getresultemail->num_rows> 0){
               $getthedata= $getresultemail->fetch_assoc();
               
               $need_activation=$getthedata['need_activation'];
               $brand="Visa2";
               if($need_activation==0){
                   $brand="Visa";
               }
               // $brand=$getthedata['cardbrand'];
               
                $mainbrand=$getthedata['cardbrand'];
               $country=$getthedata['country'];
               $cardType=$getthedata['cardtype'];
               $maxdaily=$getthedata['daily_limit'];
               $maxMonthly=$getthedata['monthly_limit'];
               $maxweekly=$getthedata['weekly_limit'];
               $currency=$getthedata['currency'];
               // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
               $trackid= createUniqueToken(5,"vc_customer_card","trackid","$currency",true,true,false);    
               $postdatais=array (
                   'cardholder_id' => $customerId,
                   'card_type' => $cardType,
                   'card_brand' => $brand,
                   'card_currency' => $currency,
                   'meta_data' => array (
                       'user_id' => $userid,
                     ),
               );
               $jsonpostdata=json_encode($postdatais);
               $url ="$baseurl/cards/create_card";
               $curl = curl_init();
               curl_setopt_array(
                       $curl, array(
                       CURLOPT_URL => $url,
                       CURLOPT_RETURNTRANSFER => true,
                       CURLOPT_ENCODING => "",
                       CURLOPT_MAXREDIRS => 10,
                       CURLOPT_TIMEOUT => 60,
                       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                       CURLOPT_CUSTOMREQUEST => "POST",
                       CURLOPT_POSTFIELDS => trim($jsonpostdata),
                       CURLOPT_HTTPHEADER => array(
                           "token: Bearer  $authkey",
                           "content-type: application/json",
                           'accept: application/json',
                            
                       ),
                   ));
               $userdetails = curl_exec($curl);
               
                       $allresp="$userdetails";
       $paymentidisni="BC GEN VC";
       $orderidni="$jsonpostdata";
       $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
       $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
       $insert_data->execute();
       $insert_data->close();
       
               // print_r($userdetails);
               $err = curl_error($curl);
               // print_r($err);
               curl_close($curl);
                 $breakdata = json_decode($userdetails);
               if(isset($breakdata->status) && $breakdata->status== "success"){

                               $accountid=$breakdata->data->card_id;
                               $valid=$trackid;
                              
                               // GET CARD DETAILS API
                               $url ="$relay_url/cards/get_card_details?card_id=$accountid";
                               $curl = curl_init();
                               curl_setopt_array(
                               $curl, array(
                               CURLOPT_URL => $url,
                               CURLOPT_RETURNTRANSFER => true,
                               CURLOPT_ENCODING => "",
                               CURLOPT_MAXREDIRS => 10,
                               CURLOPT_TIMEOUT => 60,
                               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                               CURLOPT_CUSTOMREQUEST => "GET",
                               CURLOPT_HTTPHEADER => array(
                                   "token: Bearer  $authkey",
                                   "content-type: application/json",
                                   'accept: application/json',
                               ),
                               ));
                               $userdetails = curl_exec($curl);
                               // print_r($userdetails);
                               $breakdata = json_decode($userdetails);
                               if(isset($breakdata->status) && $breakdata->status== "success"){
                                       $walletid=$breakdata->data->issuing_app_id;
                                       $last4=$breakdata->data->last_4;
                                       $cvv="***";
                                       $maskedPan=$breakdata->data->card_number;
                                       $expiryMonth=$breakdata->data->expiry_month;
                                       if(strlen($expiryMonth)==1){
                                           $expiryMonth="0$expiryMonth";
                                       }
                                       $expiryYear=$breakdata->data->expiry_year;
                                  
                                       $maskedPan=substr_replace($maskedPan,"*",6,6);
                                       $breakitup=explode("*",$maskedPan);
                                       $maskedPan=$breakitup[0]."******".$breakitup[1];
                                       //  $expiryYear=substr_replace($expiryYear,"",0,2);
                               }else{
                                       $walletid=$issueid;
                                       $last4="****";
                                       $cvv="***";
                                       $maskedPan="419292*******44566";
                                       $expiryMonth="02";
                                       $expiryYear=date("Y")+4;
                               }
                                // GET JSON FORMAT OF CARD DETAILS ENCRYPTED
                               $url ="$baseurl/cards/get_card_details?card_id=$accountid";
                               $curl = curl_init();
                               curl_setopt_array(
                               $curl, array(
                               CURLOPT_URL => $url,
                               CURLOPT_RETURNTRANSFER => true,
                               CURLOPT_ENCODING => "",
                               CURLOPT_MAXREDIRS => 10,
                               CURLOPT_TIMEOUT => 60,
                               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                               CURLOPT_CUSTOMREQUEST => "GET",
                               CURLOPT_HTTPHEADER => array(
                                   "token: Bearer  $authkey",
                                   "content-type: application/json",
                                   'accept: application/json',
                               ),
                               ));
                               $userdetails = curl_exec($curl);
                               $breakdata = json_decode($userdetails);
                               if(isset($breakdata->status) && $breakdata->status== "success"){
                                     $last4=$breakdata->data->last_4;
                                }
                               
                               $active=1;
                               $empty=0;
                               $activated=0;
                               $cansetpin=1;
                               if($need_activation==0){
                                   $activated=1;
                                   $cansetpin=0;
                               }
                               
                               $insert_data = $connect->prepare("INSERT INTO  vc_customer_card (vc_card_id,user_id,customer_id,wallet_id,status,trackid,balance,vc_type_tid,json_response,brand,last4,cvv,pan,expireMonth,expireyear,freeze,activated,cansetpin,deleted) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                               $insert_data->bind_param("sssssssssssssssssss",$accountid,$userid,$customerId,$walletid,$active,$trackid,$empty,$cardtype_tid,$userdetails, $mainbrand,$last4, $cvv,$maskedPan,$expiryMonth,$expiryYear,$empty,$activated,$cansetpin,$empty);
                               $insert_data->execute();
                               
                               // FUND CARD
                               $usdvalue=$amount;
                               $cardid=$accountid;
                               $amount=strval($usdvalue*100);//amount(in cents) 1usd = 100 cent
                               $orderid="$trackid";
 
                               $postdatais=array (
                                   'card_id' => $cardid,
                                   'amount' =>$amount,
                                    'currency' =>$currency,
                                      'transaction_reference' =>$orderid,
                               );
                               $jsonpostdata=json_encode($postdatais);
                               $url ="$baseurl/cards/fund_card";
                               //  $url ="$baseurl/cards/fund_issuing_wallet";
                               $curl = curl_init();
                               curl_setopt_array(
                               $curl, array(
                       CURLOPT_URL => $url,
                       CURLOPT_RETURNTRANSFER => true,
                       CURLOPT_ENCODING => "",
                       CURLOPT_MAXREDIRS => 10,
                       CURLOPT_TIMEOUT => 60,
                       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                       CURLOPT_CUSTOMREQUEST => "PATCH",
                       CURLOPT_POSTFIELDS => trim($jsonpostdata),
                       CURLOPT_HTTPHEADER => array(
                           "token: Bearer  $authkey",
                           "content-type: application/json",
                           'accept: application/json',
                            
                       ),
                   ));
                               $userdetails = curl_exec($curl);
                               
                                       $allresp="$userdetails";
       $paymentidisni="BC FUND VC";
       $orderidni="$jsonpostdata";
       $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
       $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
       $insert_data->execute();
       $insert_data->close();
       
                               // print_r($userdetails);
                               $err = curl_error($curl);
                               // print_r($err);
                               curl_close($curl);
               }else{
                     // message
                                       $from="BC CARD CREATION $userid";
                                        $message="An error occured when generating card";
                                       if(isset($breakdata->message)){
                                            $message=$breakdata->message;
                                       }
                                      $message.=" $err $userdetails";
                                       system_notify_crash_handler($message,$from);
               }
                                  
   }

       
       return $valid;
}

public static function revealBCCardFullData($cardid){
    $response=['status'=>false,'data'=>''];
    $systemData =self::getApiKeys("extra_channel,public_key",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $relay_url=$activeshis['extra_channel'];
        $authkey=$activeshis['public_key'];
        
            // GET CARD DETAILS API
        $url ="$relay_url/cards/get_card_details?card_id=$cardid";
        $curl = curl_init();
        curl_setopt_array(
        $curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "token: Bearer  $authkey",
                "content-type: application/json",
                'accept: application/json',
            ),
        ));
        $userdetails = curl_exec($curl);
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$url,"name"=>'BC REVEAL VC','jsonresp'=>$userdetails]);

        // print_r($userdetails);
        $breakdata = json_decode($userdetails);
        if(isset($breakdata->status) &&$breakdata->status== "success"){
            $response=['status'=>true,'data'=>$userdetails];
        }
    }
    return $response;
}

public static function activate_update_pin($cardid,$userpin){
      
       $valid=false;
       $currency="USD";
       $vc_data=GetActiveBCVirtualCardApi($currency);
       $success=false;
       $authkey=$vc_data['authkey'];
       $secretekey=$vc_data['secretekey'];
       $issueid=$vc_data['issueid'];
       $baseurl=$vc_data['baseurl']; 
       $relay_url=$vc_data['relay_url'];
       $currency=$vc_data['currency']; 
       $creationfee=1;
       
       $Cardpinencrypt = AES256::encrypt($userpin, $secretekey);

       $postdatais=array (
           'card_id' => $cardid,
           'card_pin' =>$Cardpinencrypt,
       );
       $jsonpostdata=json_encode($postdatais);
       $url ="$baseurl/cards/set_3d_secure_pin";
       $curl = curl_init();
       curl_setopt_array(
               $curl, array(
               CURLOPT_URL => $url,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => "",
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 60,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => "POST",
               CURLOPT_POSTFIELDS => trim($jsonpostdata),
               CURLOPT_HTTPHEADER => array(
                   "token: Bearer  $authkey",
                   "content-type: application/json",
                   'accept: application/json',
                    
               ),
           ));
       $userdetails = curl_exec($curl);
       // print_r($userdetails);
       $err = curl_error($curl);
       // print_r($err);
       curl_close($curl);
         $breakdata = json_decode($userdetails);
       if(isset($breakdata->status) && $breakdata->status== "success"){
           $valid=true;
       }
           return $valid;
}

public static function fundUserBCWallet($walletid,$amount,$narration,$payref,$currency,$userid){
       global  $connect;
       $vc_data=GetActiveBCVirtualCardApi($currency);
       $success=false;//0= not started at all,1=success, 2= no fund,3 = server error 4 empty order id 5 duplicate trans blocked
       $authkey=$vc_data['authkey'];
       $secretekey=$vc_data['secretekey'];
       $issueid=$vc_data['issueid'];
       $baseurl=$vc_data['baseurl']; 
       $relay_url=$vc_data['relay_url'];
       $currency=$vc_data['currency']; 
     
               
               // get account balance
               $enoughfund=false;
               $ourmainwalletbal=getMainAccountBcBalance($currency);
               if($ourmainwalletbal>=$amount){
                   $enoughfund=true;
               }
          
            
               if($enoughfund){
                   $usdvalue=$amount;
                   $cardid=$walletid;
                   $amount=strval($usdvalue*100);//amount(in cents) 1usd = 100 cent
                   $orderid=$payref;

                   $postdatais=array (
                       'card_id' => $cardid,
                       'amount' =>$amount,
                       'currency' =>$currency,
                       'transaction_reference' =>$orderid,
                   );
                   $jsonpostdata=json_encode($postdatais);
                   $url ="$baseurl/cards/fund_card";
                   //  $url ="$baseurl/cards/fund_issuing_wallet";
                   $curl = curl_init();
                   curl_setopt_array(
                           $curl, array(
                           CURLOPT_URL => $url,
                           CURLOPT_RETURNTRANSFER => true,
                           CURLOPT_ENCODING => "",
                           CURLOPT_MAXREDIRS => 10,
                           CURLOPT_TIMEOUT => 60,
                           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                           CURLOPT_CUSTOMREQUEST => "PATCH",
                           CURLOPT_POSTFIELDS => trim($jsonpostdata),
                           CURLOPT_HTTPHEADER => array(
                               "token: Bearer  $authkey",
                               "content-type: application/json",
                               'accept: application/json',
                                
                           ),
                       ));
                   $userdetails = curl_exec($curl);
                   
                   $allresp="$userdetails";
                   $paymentidisni="BC VC FUND";
                   $orderidni="$jsonpostdata";
                   $insert_data = $connect->prepare("INSERT INTO jsonresponsefromcallback (orderid,payid,jsonresp) VALUES (?,?,?)");
                   $insert_data->bind_param("sss", $orderidni, $paymentidisni, $allresp);
                   $insert_data->execute();
                   $insert_data->close();
       
                   // print_r($userdetails);
                   $err = curl_error($curl);
                   // print_r($err);
                   curl_close($curl);
                   $breakdata = json_decode($userdetails);
                   if( isset($breakdata->status) && $breakdata->status== "success"){
                       $orderidis=$breakdata->data->transaction_reference;
                       $paymenttoken="  ";
                       $notyetpaid=1;
                       $checkdata =  $connect->prepare("SELECT * FROM  userwallettrans WHERE apipayref=? AND status=?  AND userid=?");
                       $checkdata->bind_param("sis",$orderidis, $notyetpaid,$userid);
                       $checkdata->execute();
                       $dresult = $checkdata->get_result(); 
                       if(empty($orderidis)) {
                               //  $success=false;
                                $success=4;
                      } else if($dresult ->num_rows > 0){
                           //  $success=false;
                            $success=5;
                      }else{
                           // generating  token
                           // $length,$tablename,$tablecolname,$tokentag,$addnumbers,$addcapitalletters,$addsmalllletters
                           $companypayref = createUniqueToken(16,"userwallettrans","paymentref","FVC",true,true,false);
                           $success=1; 
                          // $syspaytype=1; // systemtype 1 paystack,2 monify 3 1app
                          $bankpaidwith=1;
                          $systempaidwith=2;
                          $paystatus=1;
                          $status = 1;
                          $time = date("h:ia, d M");
                          $approvedby="Automation";
                          $checkdata = $connect->prepare("UPDATE userwallettrans SET paymentref=?,paymentstatus=?,status=?,confirmtime=?,payapiresponse=?,apipayref=?,apiorderid=?,approvedby=?  WHERE orderid=?");
                          $checkdata->bind_param("sssssssss",$companypayref, $paystatus,$status,$time,$userdetails,$orderidis,$paymenttoken,$approvedby,$payref);
                          $checkdata->execute();
                      }
                   }else{
                            // message
                                       $from="BC FUND CARD";
                                        $message="An error occured when trying to fund card";
                                       if(isset($breakdata->message)){
                                            $message=$breakdata->message;
                                       }
                                       $success=3;
                                      $message.=" $err $userdetails";
                                       system_notify_crash_handler($message,$from);
                   }
               }else{
                        // message
                        $success=2;
                                       $from="BC FUND CARD";
                                        $message="Insufficient fund to fund card";
                                      
                                       system_notify_crash_handler($message,$from);
               }
               
       
       return $success;
}

public static function getMainAccountBcBalance($currency){
          global  $connect;
       $vc_data=GetActiveBCVirtualCardApi($currency);
       $balance=0;
       $authkey=$vc_data['authkey'];
       $secretekey=$vc_data['secretekey'];
       $issueid=$vc_data['issueid'];
       $baseurl=$vc_data['baseurl']; 
       $relay_url=$vc_data['relay_url'];
       $currency=$vc_data['currency']; 
       
       $url ="$baseurl/cards/get_issuing_wallet_balance";
       $curl = curl_init();
       curl_setopt_array(
               $curl, array(
               CURLOPT_URL => $url,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => "",
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 60,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => "GET",
               CURLOPT_HTTPHEADER => array(
                   "token: Bearer  $authkey",
                   "content-type: application/json",
                   'accept: application/json',
                    
               ),
           ));
       $userdetails = curl_exec($curl);
       // print_r($userdetails);
       $err = curl_error($curl);
       // print_r($err);
       curl_close($curl);
         $breakdata = json_decode($userdetails);
       if(isset($breakdata->status) && $breakdata->status== "success"){
            $balance=$breakdata->data->issuing_balance_USD/100;
       }
               
               return  $balance;
}

public static function freezeCardbc_card($cardid,$status){
    $valid=false;
    $systemData =self::getApiKeys("extra_channel,public_key",12);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeshis = $systemData;
        $relay_url=$activeshis['extra_channel'];
        $authkey=$activeshis['public_key'];
        // GET CARD DETAILS API
        if($status==0){
            $url ="$relay_url/cards/freeze_card?card_id=$cardid";
        }else{
            $url ="$relay_url/cards/unfreeze_card?card_id=$cardid";  
        }
        $curl = curl_init();
        curl_setopt_array(
        $curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PATCH",
        CURLOPT_HTTPHEADER => array(
            "token: Bearer  $authkey",
            "content-type: application/json",
            'accept: application/json',
        ),
        ));
        $userdetails = curl_exec($curl);
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$url,"name"=>'BC FREEZE CARD','jsonresp'=>$userdetails]);

        $breakdata = json_decode($userdetails);
        if(isset($breakdata->status) && $breakdata->status== "success"){
            $valid=true;
        }
    }
    return $valid;
}


// BILLS

public static  function buyUserAirtime($amount, $networkid, $order_id,$phone){// send to is phone number, smsto send (call the function in the smstemplate)
    // 1 1app, 2 klub 3 SH, 4 vtpass
    $smssent=['status'=>false,'ref'=>'','json'=>''];
    $activemailsystem=1;
    if($activemailsystem==1){
       $smssent= self::buy1appAirtime($amount, $networkid, $order_id, $phone);
    }else if($activemailsystem==2){
    }else if($activemailsystem==3){
    }else if($activemailsystem==4){
    }
    return $smssent;
}
public static function buyUserData($datacode,$networkid, $phoneno, $reference){// send to is phone number, smsto send (call the function in the smstemplate)
    // 1 1app, 2 klub 3 SH, 4 vtpass
    $smssent=false;
    $activemailsystem=1;
    if($activemailsystem==1){
       $smssent= self::buyDataWith1app($datacode,$networkid, $phoneno, $reference);
    }else if($activemailsystem==2){
    }else if($activemailsystem==3){
        // $smssent= buyDataeSH($datacode,$networkid, $phoneno, $reference,'');
    }else if($activemailsystem==4){
    }
    return $smssent;
}
public static function buyUserElectricity($meterno, $metername, $provider, $vendtype, $amount,$activemailsystem){// send to is phone number, smsto send (call the function in the smstemplate)
    // 1 1app, 2 SH, 3 vtpass
    $smssent=['status'=>false,'token'=>'','json'=>''];
    if($activemailsystem==1){
       $smssent=self::vendElectricity1App($meterno, $metername, $provider, $vendtype, $amount);
    }else if($activemailsystem==2){
    //    $smssent=vendElectricitySH($meterno, $metername, $provider, $vendtype, $amount,$order_id);
    }else if($activemailsystem==3){
    }else if($activemailsystem==4){
    }
    return $smssent;
}

public static function getMeterDetails($meterno,  $provider,$activemailsystem){// send to is phone number, smsto send (call the function in the smstemplate)
    // 1 1app, 2 SH, 3 vtpass
    $smssent= ['status'=>false,'name' => '','address' => '','minvend' => '', 'maxvend' => '','msg'=>'']    ;
    if($activemailsystem==1){
       $smssent=self::validateMeterNo1App($meterno, $provider);
    }else if($activemailsystem==2){
    //    $smssent=validateMeterNoSH($meterno, $provider);
    }else if($activemailsystem==3){
    }else if($activemailsystem==4){
    }
    return $smssent;
}
    // 1app

public static  function buy1appAirtime($amount, $networkid, $order_id, $phone){
    $bought=['status'=>false,'ref'=>'','json'=>''];
    $systemData =self::getApiKeys("baseurl,private_key",13);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $secret = $systemData['private_key'];
        $baseurl=$systemData['baseurl'];
        $jsonpostdata=array('phoneno' => $phone,'network_id' => $networkid,'reference' => $order_id,'amount' => $amount);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$baseurl/airtime",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonpostdata,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $secret"
            ),
        ));
        $userdetails = curl_exec($curl);
        curl_close($curl);
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>json_encode($jsonpostdata),"name"=>"1APP AIRTIME",'jsonresp'=>$userdetails]);
        $response = json_decode($userdetails);
        if (isset($response->status) && $response->status==true ){
            $paystackref=$response->txref;
            $bought=['status'=>true,'ref'=>$paystackref,'json'=>$userdetails];
        }
    }
    return $bought;
}
public static function buyDataWith1app($datacode,$networkid, $phoneno, $reference){
        $bought=['status'=>false,'ref'=>'','json'=>''];
        $systemData =self::getApiKeys("baseurl,private_key",13);
        if (!Utility_Functions::input_is_invalid($systemData)) {
            $secret = $systemData['private_key'];
            $baseurl=$systemData['baseurl'];
            $curl = curl_init();
            $jsonpostdata=array('datacode' => $datacode ,'network_id' =>$networkid,'phoneno' => $phoneno,'reference' => $reference);
            curl_setopt_array($curl, array(
                CURLOPT_URL => "$baseurl/databundle",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>$jsonpostdata,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $secret"
                ),
            ));
            $userdetails = curl_exec($curl);
            curl_close($curl);
            
            DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>json_encode($jsonpostdata),"name"=>"1APP DATA",'jsonresp'=>$userdetails]);
            $response = json_decode($userdetails);
            if (isset($response->status) && $response->status==true ){
                $paystackref=$response->txref;
                $bought=['status'=>true,'ref'=>$paystackref,'json'=>$userdetails];
            }
        }
        return $bought;
}
function getAll1AppDataPlans($network){
    $curl = curl_init();

    $oneapp_data = GetActive1APPApi();

    if ( $oneapp_data ){
        $public_key = $oneapp_data['key']; //pb -publickey
    }
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.oneappgo.com/v1/getdataplans?provider=$network",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $public_key"
    ),
    ));

    $response = curl_exec($curl);
    $response = json_decode($response);
    if($response->status){
        return $response->data;
    }
    return false;

    curl_close($curl);    
}

function get1AppBalance(){
    $curl = curl_init();

    $oneapp_data = GetActive1APPApi();
    $secret = $oneapp_data['secretekey']; //pb -publickey
    
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.oneappgo.com/v1/balance",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $secret"
    ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);  

    $response = json_decode($response);
    if ( isset($response->status) && $response->status ){
        return $response->available_bal;
    }
    return 0;

}
public static function vendElectricity1App($meterno, $metername, $provider, $vendtype, $amount){

    $bought=['status'=>false,'token'=>'','json'=>''];
    $systemData =self::getApiKeys("baseurl,private_key",13);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeOneApp = $systemData['private_key'];
        $baseurl=$systemData['baseurl'];
        $curl = curl_init();
        $jsonpostdata=array('meterno' => $meterno,'metername' => $metername,'provider' => $provider,'amount' => $amount,'vendtype' => $vendtype);
        curl_setopt_array($curl, array(
                CURLOPT_URL => "$baseurl/electricity",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>  $jsonpostdata,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer $activeOneApp"
                ),
        ));

        $userdetails = curl_exec($curl);
        curl_close($curl);
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>json_encode($jsonpostdata),"name"=>"1APP ELECTRICITY",'jsonresp'=>$userdetails]);
        $response = json_decode($userdetails);
        // echo $response;
        if(empty($userdetails)){
            $bought=['status'=>true,'token'=>'','json'=>$userdetails];

        }else if (isset($response->status)&&$response->status==true ){
                $value = $response->token;
                $token = trim(explode("Token:", $value)[1]);
                $bought=['status'=>true,'token'=>$token,'json'=>$userdetails];

        }
    }
        return $bought;
 }
 public static function validateMeterNo1App($meterno, $provider){
    $data=['status'=>false,'name' => '','address' => '','minvend' => '', 'maxvend' => '','msg'=>''];
    $systemData =self::getApiKeys("baseurl,public_key",13);
    if (!Utility_Functions::input_is_invalid($systemData)) {
        $activeOneApp= $systemData['public_key'];
        $baseurl=$systemData['baseurl']; 
    
        $curl = curl_init();
        $url="$baseurl/verifyelect?meterno=$meterno&provider=$provider";
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $activeOneApp"
            ),
        ));
        $userdetails = curl_exec($curl);
        curl_close($curl);
        DB_Calls_Functions::insertRow("responsesfromapicalllog",["jsonbody"=>$url,"name"=>"1APP ELECTRICITY DETAIL CHECK",'jsonresp'=>$userdetails]);

         $response = json_decode($userdetails);
        if (isset($response->status)&&$response->status==true ){
            $data = [
                'status'=>true,
                'name' => $response->name,
                'address' => $response->address,
                'minvend' => $response->minvend,  
                'maxvend' => $response->maxvend,
                'msg'=>'Found'
            ];
        }else {
            $string =$response->msg;
            $substring = "currently offline";
         
            if(strpos($string, $substring) !== false||strpos($string, "unexpected error") !== false){
                $data = [
                    'status'=>false,
                    'name' => $response->name,
                    'address' => $response->address,
                    'minvend' => $response->minvend,  
                    'maxvend' => $response->maxvend,'msg'=>'Server is presently experiencing a temporary downtime, kindly retry shortly'
                ];
            }else{
                $data = [
                    'status'=>false,
                    'name' => isset($response->name)?$response->name:'',
                    'address' => isset($response->address)?$response->address:'',
                    'minvend' => isset($response->minvend)?$response->minvend:'',  
                    'maxvend' => isset($response->maxvend)?$response->maxvend:'',
                    'msg'=>'Detail not found'
                ];
            }
        }
 
    }
    
    return $data;

}

public static function fetchFromAutoPilotAPI($endpoint, $payload)
{
    $baseUrl = "https://autopilotng.com/api/live/v1/";
    $url = $baseUrl . $endpoint;

    $headers = [
        "Authorization: Bearer live_0dedb79a3af94af49d0a2ffafe6a4ea4yq08dk11",
        "Content-Type: application/json",
        "Accept: application/json",
    ];

    $postData = json_encode($payload);

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => $postData,
            'ignore_errors' => true,
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        throw new RuntimeException("Failed to connect to the third-party API.");
    }

    $responseData = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON response from third-party API.");
    }

    return $responseData;
}


}