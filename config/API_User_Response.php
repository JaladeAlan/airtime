<?php

namespace Config;

use Config\Constants;
/**
 * System Messages Class
 *
 * PHP version 5.4
 */
class API_User_Response
{

    /**
     * Welcome message
     *
     * @var string
     */
    // General errors
    public static $methodUsedNotAllowed="Method Used is not valid";
    public static $invalidDataSent="Please send correct data";
    public static $invalidUserDetail="Invalid username or password";
    public static $invalidSubscriptionDetail="Admin user could not be found";
    public static $invalidUserCredential="Invalid username or email";
    public static $loginSuccessful="LogIn Successful";
    public static $logoutSuccessful="LogOut Successful";
    public static $unauthorized_token="Unauthorized user";
    public static $userExists="Username already exists";
    public static $companyExists="Company already exists";
    public static $userNotExist="User does not exist";
    public static $emailExists="Email Already exists";
    public static $secretWordExists="Kindly use a different secret word";
    public static $registrationSuccessful="Registration successful";
    public static $removeSuccessful="User has been removed successfully";
    public static $removeFailed ="User has not been removed";
    public static $profileUpdated = "Your profile has been updated successfully";
    public static $profileUpdateFailed = "Your profile was not updated";
    public static $passwordResetSuccessful = "Your password reset was successful";
    public static $passwordResetFailed = "Your password reset failed";
    public static $registrationFailed="Registration failed";
    public static $dataNotFound= "Data Not Found";
    public static $passwordIncorrect = "Incorrect Password";
    public static $secretWordIncorrect = "Incorrect secret word";
    public static $passwordOldIncorrect = "Incorrect Existing Password";
    public static $passwordUpdateSuccessful = "Your password has been updated successfully";
    public static $passwordUpdateFailed = "Your password was not updated";
    public static $invalidInfo = "Incorrect Information Sent";
    public static $weakPassword = "Password not strong enough";
    public static $confirmPassword = "Passwords are not matched";
    public static $invalidEmail = "Invalid Email";
    public static $invalidResetData = "Code Invalid";
    public static $resetCodeVerified = "Code is verified";
    public static $resetCodeEmailSent= "Password Reset sent to email successfully";
    public static $subscriptionSuccessful= "You have successfully subscribed";
    public static $subscriptionFailed= "Your subscription failed";
    public static $fetchNotification= "Notifications fetched successfully";
    public static $sentNotification= "Notification sent successfully";
    public static $sentNotificationFailed= "Failed to send notification";


    public static $welcomeMessage = "Welcome to " . Constants::APP_NAME;
   
    //  login fail  
    public  static $loginFailedError="one or both of the data provided is invalid";

    // forgot passwor
    public  static $forgotMailSent="Recovery Mail sent successfully, kindly check your mail";
    public  static $errorOccured="An Error occured, Please contact support";


    
}