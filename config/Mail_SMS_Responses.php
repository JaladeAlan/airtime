<?php

namespace Config;
use DatabaseCall\System_Defaults;
use DatabaseCall\Users_Table;

/**
 * System Messages Class
 *
 * PHP version 5.4
 */
class Mail_SMS_Responses extends DB_Connect
{
    //  this function is used to get the system specific functions
    function sendWithSenGrid($emailfrom,$subject,$toemail,$msgintext,$messageinhtml){
        $issent =false;
        $sendgriddata=System_Defaults::GetActiveSendGridApi();
        $sendgridkey =$sendgriddata['apikey'];
        $sendgridid = $sendgriddata['secreteid'];
        $emailfrom=$sendgriddata['emailfrom'];
        // If not using Composer, uncomment the above line
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($emailfrom, "$sendgridid");
        $email->setSubject($subject);
        $email->addTo($toemail);
        $email->addContent(
            "text/plain", strip_tags($msgintext)
        );
        $email->addContent(
            "text/html", $messageinhtml
        );
        $sendgrid = new \SendGrid($sendgridkey);
        try {
            $response = $sendgrid->send($email);
  
            $issent =true;
        // check response and set this well
            // print $response->statusCode() . "\n";
            // print_r($response->headers());
            // print $response->body() . "\n";
        } catch (\Exception $e) {
            $issent =false;
  
        }
      return $issent;
  
    }
    function sendWithTermi($sendto,$smstosend){
        $termidata=System_Defaults::GetActiveTermiApi();
        $smssent=false;
        $dnum = substr($sendto, 1);
        $sendto="234".$dnum;
        $channel=$termidata['smschannel'];
   
        $starttimefortoday= strtotime("6:00 PM");
        $endtimefortoday= strtotime("9:20 AM");
        $currenttimeis=time();
        // echo $currenttimeis;
        // echo "<br>";
        // check for if data is to next day 10PM-8AM
        // if($starttimefortoday>$endtimefortoday){
        //     if($currenttimeis >=$starttimefortoday || $currenttimeis <$endtimefortoday){
        //          $channel=$termidata['smschannel2']; 
        //     }
        // }else if($currenttimeis>=$starttimefortoday && $currenttimeis<=$endtimefortoday){
            $channel=$termidata['smschannel2']; 
            // }
        
        
        
        
       $arr = array(
        "to"=> $sendto,
        "sms"=>$smstosend,
       "api_key"=> $termidata['apikey'],
       "from"=> "N-Alert",//$termidata['sendfrom'],
       "type"=> $termidata['smstype'],
       "channel"=> $channel,
       );
       //below is the base url
       $url ="https://termii.com/api/sms/send";
       $params =  json_encode($arr);
       $curl = curl_init();
       curl_setopt_array($curl, array(
       //u change the url infront based on the request u want
       CURLOPT_URL => $url,
       CURLOPT_POSTFIELDS => $params,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => "",
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 30,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       //change this based on what u need post,get etc
       CURLOPT_CUSTOMREQUEST => "POST",
       CURLOPT_HTTPHEADER => array(
       "content-type: application/json",
       ),
       ));
       $resp = curl_exec($curl);
       $err = curl_error($curl);
       curl_close($curl);
    //   print($resp);
        if($err){
            $smssent=false;
        }else{
            $theresponse= json_decode($resp);
            //   print_r($theresponse);
            if(isset($theresponse->code) && $theresponse->code=="ok"){
                $smssent=true;
                $msgid= $theresponse->message_id;
                // later log sms sent
                // $systype="Termii";
                // $insert_data4 = $connect->prepare("INSERT INTO smslog(message,sentto,sentwith,messageid,sentrom) VALUES (?,?,?,?,?)");
                // $insert_data4->bind_param("sssss", $msg,$sendto,$systype,$msgid,$sendfrom);
                // $insert_data4->execute();
                // $insert_data4->close();
            }else{
                 $smssent=false;
            }
        }
    return $smssent;
    }
    function sendWithKudiSMS($sendto,$smstosend){
            $sysdata=System_Defaults::GetActiveKudiApi();
            $smssent=false;

            /*
            Sending messages using the KudiSMS API
            Requirements - PHP, file_get_contents (enabled) function
            */
            // Initialize variables ( set your variables here )
            $username = $sysdata['username'];
            $password = $sysdata['password'];
            $sender = $sysdata['sendfrom'];
            $message = $smstosend;
            // Separate multiple numbers by comma
            $mobiles = $sendto;
            // Set your domain's API URL
            $api_url = 'https://account.kudisms.net/api/';
            //Create the message data
            $data = array('username' => $username, 'password' => $password, 'sender' => $sender,
                'message' => $message, 'mobiles' => $mobiles);
                //URL encode the message data
                $data = http_build_query($data);
                //Send the message  
                $request = $api_url . '?' . $data;
                $result = file_get_contents($request);
                $result = json_decode($result);
                if (isset($result->status) && strtoupper($result->status) == 'OK') {
                // Message sent successfully, do anything here
                // echo 'Message sent at N' . $result->price;
                    $smssent=true;
                } else if (isset($result->error)) {
                    $smssent=false;
                // Message failed, check reason.
                // echo 'Message failed - error: ' . $result->error;
                } else {
                    $smssent=false;
                // Could not determine the message response.
                // echo 'Unable to process request';
                }
                return $smssent;
    }
    function sendWithSmartSolution($sendto,$smstosend){
            $sysdata=System_Defaults::GetActiveSmartSolutionApi();
            $smssent=false;
            // Initialize variables ( set your variables here )
            $sendfrom = $sysdata['sendfrom'];
            $sendtype = $sysdata['sendtype'];
            $routing = $sysdata['routing'];
            $token = $sysdata['apitoken'];
            // ref_id ADD THIS WHEN LOGGING SMS
            $message = $smstosend;
            // Separate multiple numbers by comma
            $mobiles = $sendto;
            $baseurl = 'https://smartsmssolutions.com/api/json.php?';
        
            $sms_array = array
                (
                'sender' => $sendfrom,
                'to' => $mobiles,
                'message' => $message,
                'type' => $sendtype,
                'routing' => $routing,
                'token' => $token,
            );
        
            $params = http_build_query($sms_array);
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, $baseurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        
            $resp = curl_exec($ch);
            $err = curl_error($ch);
            if($err){
                $smssent=false;
            }else{
                $theresponse= json_decode($resp);
                //   print_r($theresponse);
                if($theresponse->code==1000){
                        $smssent=true;
                        $msgid= $theresponse->message_id;
                    // $systype="Termii";
                    // $insert_data4 = $connect->prepare("INSERT INTO smslog(message,sentto,sentwith,messageid,sentrom) VALUES (?,?,?,?,?)");
                    // $insert_data4->bind_param("sssss", $msg,$sendto,$systype,$msgid,$sendfrom);
                    // $insert_data4->execute();
                    // $insert_data4->close();
                }else{
                        $smssent=false;
                }
            }
            curl_close($ch);
            return  $smssent;
    }
    function sendUserMail($subject,$toemail,$msgintext,$messageinhtml){
        // 1 sendGrid, 2
        $mailsent=false;
        $systemsettings=System_Defaults::getAllSystemSetting();
        $activemailsystem=$systemsettings['activemailsystem'];
        $emailfrom=$systemsettings['emailfrom'];
        if($activemailsystem==1){
            $mailsent=self::sendWithSenGrid($emailfrom,$subject,$toemail,$msgintext,$messageinhtml);
        }
        return $mailsent;
    }
    function sendUserSMS($sendto,$smstosend){// send to is phone number, smsto send (call the function in the smstemplate)
        // 1 Termi, 2 kudi 3 smart solution
        $smssent=false;
        $activemailsystem=System_Defaults::getAllSystemSetting()['activesmssystem'];
        if($activemailsystem==1){
            $smssent=self::sendWithTermi($sendto,$smstosend);
        }else if($activemailsystem==2){
            $smssent=self::sendWithKudiSMS($sendto,$smstosend);
        }else if($activemailsystem==3){
            $smssent=self::sendWithSmartSolution($sendto,$smstosend);
        }
        return $smssent;
    }
    //  this function is used to get the system specific functions

    







    
    // Below functions are called whenever a user requets for reset password, where they would input their mail
    function loginHTML($userid,$seescode)
    {
        $userdsatas = Users_Table::getUserByIdAndEmail($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis = $userdsatas['username'];

        $systemdata = System_Defaults::getAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp = $systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];


        $resetlink = $baseurl . "auth/reset.html?token=";
        $messagetitle = "Password recovery";
        $greetingText = "Hello $usernameis.";
        $headtext = "We received your request to reset your account password, If this wasn't you, please check your account integrity.<br>Your Password Reset code is <h5 align='center'>33</h5> <p>Kindly enter the above code or click below button to reset your password.</p>";
        $bottomtext = "If you have any questions, don't hesitate to reach us via our several support channels, or open a support ticket by sending a mail to <a href='mailto:$supportemail' style='text-decoration: none; color: #0ab930; letter-spacing: .2px; font-weight: 600;  font-size: 14px;'>$supportemail</a>.";
        // adding link and button of link use below
        $calltoaction = true; // set as true and add details below
        $calltoactionlink = "$resetlink";
        $calltoactiontext = "Reset password";
        // adding link and button of link use below
        $buttonis = "";
        if ($calltoaction == true) {
            $buttonis = ' <td align="center">
          <table role="presentation" border="0" cellpadding="0" cellspacing="0">
            <tbody>
              <tr>
                <td> <a style="background-color:#0ab930;border: solid 1px #0ab930;
                              border-radius: 5px;
                              box-sizing: border-box;
                              color: white;
                              display: inline-block;
                              font-size: 14px;
                              font-weight: bold;
                              margin: 0;
                              padding: 12px 25px;
                              text-decoration: none;
                              text-transform: capitalize;" href="' . $calltoactionlink . '" target="_blank">' . $calltoactiontext . '</a> </td>
              </tr>
            </tbody>
          </table>
        </td>';
        }

        $mailtemplate = '
      <!DOCTYPE html>
                  <html lang="en" style="--white: #fff; --green-dark: #0a6836; --green-light: #0ab930; --green-lighter: #12a733; --black: #000;">
                      <head>
                          <meta charset="utf-8">
                          <meta name="viewport" content="width=device-width, initial-scale=1.0">
                          <meta http-equiv="Content-Type" content="text/html;text/css; charset=UTF-8">
                          <title>' . $messagetitle . '</title>
                           
                      </head>
                      <body style="font-family: system-ui !important;" bgcolor="#f5f6fa">
                              <div class="wrapper" style="position: relative; background-color: #f5f6fa; min-height: 100vh;">
                              <div class="wrapper__inner" style="min-height: 100%; margin-inline: auto; padding: 2.5rem 0;max-width: 620px;margin:auto !important;">
                                  <div class="template__top d-none d-md-block" style="margin-bottom: 1.7rem;" align="start">
                                      <div class="template__top__inner logo" style="" align="center"><a href="#" style="text-decoration: none;"><img src="' . $logourl . '" alt="' . $appname . ' logo" class="img-fluid" loading="lazy" style="max-width: 200px;"></a></div>
                                  </div>
                                  <div class="template__body" style="margin-top: 3rem; background-color: #fff; padding: 1.8rem 1.5rem 1.5rem;">
                                      <div class="template__body__inner">
                                          <div class="body__content" style="color:black;">
                                              <div class="head"><h3 style="font-weight: 900; color: #0ab930; font-size: 1.3rem; letter-spacing: .4px; margin: 0;">' . $messagetitle . '</h3></div> <br>
                                              <div class="body"><p style="font-weight: bolder; font-size: 1rem; color: #000; margin: 0;">Hi ' . $usernameis . ',</p></div> <br>
                                              <div class="text__content" style="font-size: 14px; letter-spacing: -0.1px; word-spacing: 1px;">
                                                  <span>' . $headtext . '</span>
                                                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                                                    <tbody>
                                                      <tr>
                                                          ' . $buttonis . '
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                                  <span>' . $bottomtext . '</span></div> <br>
                                                  
                                              <div class="body__pre__foot" style="margin-top: 2rem; font-size: 15px;"><p style="font-weight: 600; margin: 0;">Thanks,</p></div>
                                              <div class="body__foot" style="font-size: 15px;">The ' . $appname . ' Team.</div>
                                          </div>
                                          <div class="template__footer" style="display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
                                              <div class="copyright"><small>Copyright © 2022. All rights reserved</small></div>
                                              <div class="logo">
                                                  <a href="" style="text-decoration: none;">
                                                      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M31.937 6.093a13.359 13.359 0 0 1-3.765 1.032a6.603 6.603 0 0 0 2.885-3.631a13.683 13.683 0 0 1-4.172 1.579a6.56 6.56 0 0 0-11.178 5.973c-5.453-.255-10.287-2.875-13.52-6.833a6.458 6.458 0 0 0-.891 3.303a6.555 6.555 0 0 0 2.916 5.457a6.518 6.518 0 0 1-2.968-.817v.079a6.567 6.567 0 0 0 5.26 6.437a6.758 6.758 0 0 1-1.724.229c-.421 0-.823-.041-1.224-.115a6.59 6.59 0 0 0 6.14 4.557a13.169 13.169 0 0 1-8.135 2.801a13.01 13.01 0 0 1-1.563-.088a18.656 18.656 0 0 0 10.079 2.948c12.067 0 18.661-9.995 18.661-18.651c0-.276 0-.557-.021-.839a13.132 13.132 0 0 0 3.281-3.396z"></path></svg>
                                                  </a>
                                                  <a href="" style="text-decoration: none;">
                                                      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveaspectratio="xMidYMid meet" viewbox="0 0 32 32"><path fill="currentColor" d="M0 0v32h32V0zm26.583 7.583l-1.714 1.646a.49.49 0 0 0-.193.479v12.089a.497.497 0 0 0 .193.484l1.672 1.646v.359h-8.427v-.359l1.734-1.688c.172-.172.172-.219.172-.479v-9.776l-4.828 12.26h-.651l-5.62-12.26v8.219c-.047.344.068.693.307.943l2.26 2.74v.359H5.087v-.359l2.26-2.74c.24-.25.349-.599.286-.943v-9.5A.816.816 0 0 0 7.362 10L5.357 7.583v-.365h6.229l4.818 10.568l4.234-10.568h5.943z"></path></svg>
                                                  </a>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                                  
                              </div>
                          </div>
                      </body>       
                  </html>';

        return $mailtemplate;
    }
    function loginText($userid,$seescode)
    {
        $userdsatas = Users_Table::getUserByIdAndEmail($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis = $userdsatas['username'];

        $systemdata = System_Defaults::getAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp = $systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $resetlink = $baseurl . "auth/reset.html?token=" ;

        $mailtext = "We received a request to reset your account password, if this was you, you can safely disregard this note. If this wasn't you, please check your account integrity. \n Your password reset link: $resetlink. \n Kindly click to reset your password.";

        return $mailtext;
    }
    function loginSubject($userid,$seescode)
    {
        $userdsatas = Users_Table::getUserByIdAndEmail($userid);
        // `email`, `fname`, `username`, `lname`, `password`, `phoneno`, `bal`, `refcode`, `referby`, `fcm`, `status`, `adminseen`, `userpubkey`, `created_at`, `updated_at`, `state`, `country`, `dob`, `sex`, `emailverified`, `phoneverified`, `address1`, `address2`, `nextkinfname`, `nextkinemail`, `nextkinpno`, `nextkinaddress`, `depositnotification`, `securitynotification`, `transfernotification`, `userlevel`, `lastpassupdate`
        //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $usernameis = $userdsatas['username'];

        $systemdata = System_Defaults::getAllSystemSetting();
        // `name`, `iosversion`, `androidversion`, `webversion`, `activesmssystem`, `activemailsystem`, `emailfrom`, `baseurl`, `location`, `appshortdetail`, `supportemail`, `appimgurl`, `created_at`, `updated_at`
        //  //  if you need to pick any data of the user , check above for the data field name and call it as seen below
        $appname =  $systemdata['name'];
        $baseurl =  $systemdata['baseurl'];
        $location = $systemdata['location'];
        $summaryapp = $systemdata['appshortdetail'];
        $supportemail = $systemdata['supportemail'];
        $logourl =  $systemdata['appimgurl'];

        $subject = "$appname - Password recovery";
        return $subject;
    }
    function sendLoginSMSEMail($userid,$seescode,$sendToEmail,$sendToPhoneNo){
        $subject = self::loginSubject($userid,$seescode); 
        $messageText = self::loginText($userid, $seescode);
        $messageHTML = self::loginHTML($userid, $seescode);
        self::sendUserMail($subject,$sendToEmail,$messageText, $messageHTML);
        self::sendUserSMS($sendToPhoneNo,$messageText);
    }


    
}