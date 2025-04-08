<?php

namespace Config;


/**
 * System Messages Class
 *
 * PHP version 5.4
 */
class API_Error_Code
{
// Error code that starts with 1 is from us,2 is from third party

// FROM WHAT SYSTEM____FROM WHERE__ERRORTYPE

// FROM WHAT SYSTEM
// internal(our code) -1
// external(Third party API) -2

// FROM WHERE INTERNAL
// database insert error-->1
// databse update error-->2
// database delete error-->3
// user wrong action error ---> 4 (insufficient fund, empty data,authorization)
// Hacker attempt--->5 (wrong method/user not found)

// FROM WHERE EXTERNAL
// Call to API failed -->6
// Sent wrong data to API->7
// Failed to satisfy API need on their dashboard ->8(Insufficinet fund)

// ERRORTYPE
// 1--Fatal
// 2--Warning
    /**
     * Welcome message
     *
     * @var string
     */
    // General errors
    public  static $internalUserWarning=142;
    public  static $internalHackerWarning=151;
    public  static $internalServerError=116;

    
}