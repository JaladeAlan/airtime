<?php

namespace Config;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Utility_Functions;
use Config\API_Error_Code;
use Config\API_User_Response;
use DatabaseCall\System_Defaults;


/**
 * System Messages Class
 *
 * PHP version 5.4
 */
class API_Status_Code
{
    /* Status Code 201 – This is the status code that confirms that the request was successful and, as a result, a new resource was created. 
    // Typically, this is the status code that is sent after a POST/PUT request.
    // 100	Continue	[RFC7231, Section 6.2.1]
    // 101	Switching Protocols	[RFC7231, Section 6.2.2]
    // 102	Processing	[RFC2518]
    // 103	Early Hints	[RFC8297]
    // 104-199	Unassigned	
    // 200	OK	[RFC7231, Section 6.3.1]
    // 201	Created	[RFC7231, Section 6.3.2]
    // 202	Accepted	[RFC7231, Section 6.3.3]
    // 203	Non-Authoritative Information	[RFC7231, Section 6.3.4]
    // 204	No Content	[RFC7231, Section 6.3.5]
    // 205	Reset Content	[RFC7231, Section 6.3.6]
    // 206	Partial Content	[RFC7233, Section 4.1]
    // 207	Multi-Status	[RFC4918]
    // 208	Already Reported	[RFC5842]
    // 209-225	Unassigned	
    // 226	IM Used	[RFC3229]
    // 227-299	Unassigned	
    // 300	Multiple Choices	[RFC7231, Section 6.4.1]
    // 301	Moved Permanently	[RFC7231, Section 6.4.2]
    // 302	Found	[RFC7231, Section 6.4.3]
    // 303	See Other	[RFC7231, Section 6.4.4]
    // 304	Not Modified	[RFC7232, Section 4.1]
    // 305	Use Proxy	[RFC7231, Section 6.4.5]
    // 306	(Unused)	[RFC7231, Section 6.4.6]
    // 307	Temporary Redirect	[RFC7231, Section 6.4.7]
    // 308	Permanent Redirect	[RFC7538]
    // 309-399	Unassigned	
    // 400	Bad Request	[RFC7231, Section 6.5.1]
    // 401	Unauthorized	[RFC7235, Section 3.1]
    // 402	Payment Required	[RFC7231, Section 6.5.2]
    // 403	Forbidden	[RFC7231, Section 6.5.3]
    // 404	Not Found	[RFC7231, Section 6.5.4]
    // 405	Method Not Allowed	[RFC7231, Section 6.5.5]
    // 406	Not Acceptable	[RFC7231, Section 6.5.6]
    // 407	Proxy Authentication Required	[RFC7235, Section 3.2]
    // 408	Request Timeout	[RFC7231, Section 6.5.7]
    // 409	Conflict	[RFC7231, Section 6.5.8]
    // 410	Gone	[RFC7231, Section 6.5.9]
    // 411	Length Required	[RFC7231, Section 6.5.10]
    // 412	Precondition Failed	[RFC7232, Section 4.2][RFC8144, Section 3.2]
    // 413	Payload Too Large	[RFC7231, Section 6.5.11]
    // 414	URI Too Long	[RFC7231, Section 6.5.12]
    // 415	Unsupported Media Type	[RFC7231, Section 6.5.13][RFC7694, Section 3]
    // 416	Range Not Satisfiable	[RFC7233, Section 4.4]
    // 417	Expectation Failed	[RFC7231, Section 6.5.14]
    // 418-420	Unassigned	
    // 421	Misdirected Request	[RFC7540, Section 9.1.2]
    // 422	Unprocessable Entity	[RFC4918]
    // 423	Locked	[RFC4918]
    // 424	Failed Dependency	[RFC4918]
    // 425	Too Early	[RFC8470]
    // 426	Upgrade Required	[RFC7231, Section 6.5.15]
    // 427	Unassigned	
    // 428	Precondition Required	[RFC6585]
    // 429	Too Many Requests	[RFC6585]
    // 430	Unassigned	
    // 431	Request Header Fields Too Large	[RFC6585]
    // 432-450	Unassigned	
    // 451	Unavailable For Legal Reasons	[RFC7725]
    // 452-499	Unassigned	
    // 500	Internal Server Error	[RFC7231, Section 6.6.1]
    // 501	Not Implemented	[RFC7231, Section 6.6.2]
    // 502	Bad Gateway	[RFC7231, Section 6.6.3]
    // 503	Service Unavailable	[RFC7231, Section 6.6.4]
    // 504	Gateway Timeout	[RFC7231, Section 6.6.5]
    // 505	HTTP Version Not Supported	[RFC7231, Section 6.6.6]
    // 506	Variant Also Negotiates	[RFC2295]
    // 507	Insufficient Storage	[RFC4918]
    // 508	Loop Detected	[RFC5842]
    // 509	Unassigned	
    // 510	Not Extended	[RFC2774]
    // 511	Network Authentication Required	[RFC6585]
    // 512-599	Unassigned	
    */


    //  ALL RESPONSE CODE
// 405 Method Not Allowed
    function respondMethodNotAllowed($maindata, $text, $hint, $linktosolve, $errorcode)
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = Utility_Functions::getCurrentFullURL();
        $errordata = ["code" => $errorcode, "text" => "Method used is not valid", "link" => "$linktosolve", "hint" => $hint];
        $data = ["status" => false, "text" => $text, "data" => $maindata, "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 405 Method Not allowed");
        http_response_code(405);

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondBadRequest($maindata, $text, $hint, $linktosolve, $errorcode)
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = Utility_Functions::getCurrentFullURL();

        $errordata = ["code" => $errorcode, "text" => "Data sent by user is not valid", "link" => "$linktosolve", "hint" => $hint];
        $data = ["status" => false, "text" => $text, "data" => $maindata, "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 400 Bad request");
        http_response_code(400);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondInternalServerError($maindata, $text, $hint, $linktosolve, $errorcode)
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = Utility_Functions::getCurrentFullURL();

        $errordata = ["code" => $errorcode, "text" => "Registration failed. Please try again later.", "link" => "$linktosolve", "hint" => $hint];
        $data = ["status" => false, "text" => $text, "data" => $maindata, "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 400 Bad request");
        http_response_code(400);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondUnauthorized($maindata, $text, $hint, $linktosolve, $errorcode)
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = Utility_Functions::getCurrentFullURL();

        $errordata = ["code" => $errorcode, "text" => "User data is wrong", "link" => "$linktosolve", "hint" => $hint];
        $data = ["status" => false, "text" => $text, "data" => $maindata, "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 401 Unauthorized");
        http_response_code(401);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    function respondOK($maindata, $text)
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = Utility_Functions::getCurrentFullURL();

        $errordata = [];
        $data = ["status" => true, "text" => $text, "data" => $maindata, "time" => date("d-m-y H:i:sA", time()), "method" => $method, "endpoint" => $endpoint, "error" => $errordata];
        header("HTTP/1.1 200 OK");
        http_response_code(200);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    // Generated a unique pub key for all users
    function getTokenToSendAPI($userPubkey)
    {

        $systemData = System_Defaults::getAPIJwtData();
        $companyprivateKey = $systemData['privatekey'];
        $minutetoend = $systemData['tokenexpiremin'];
        $serverName = $systemData['servername'];

        $issuedAt = new \DateTimeImmutable();
        $expire = $issuedAt->modify("+$minutetoend minutes")->getTimestamp();
        $username = "$userPubkey";
        $data = [
            'iat' => $issuedAt->getTimestamp(),
            // Issued at: time when the token was generated
            'iss' => $serverName,
            // Issuer
            'nbf' => $issuedAt->getTimestamp(),
            // Not before
            'exp' => $expire,
            // Expire
            'usertoken' => $username, // User name
        ];

        // Encode the array to a JWT string.
        //  get token below
        $auttokn = JWT::encode(
            $data,
            $companyprivateKey,
            'HS512'
        );
        return $auttokn;
    }
    function ValidateAPITokenSentIN()
    {
        $method = getenv('REQUEST_METHOD');
        $endpoint = Utility_Functions::getCurrentFullURL();
        $systemData = System_Defaults::getAPIJwtData();
        $companyprivateKey = $systemData['privatekey'];
        $serverName = $systemData['servername'];

        $headerName = 'Authorization';
        $headers = getallheaders();
        $signraturHeader = isset($headers[$headerName]) ? $headers[$headerName] : null;
        if ($signraturHeader == null) {
            $signraturHeader = isset($_SERVER['Authorization']) ? $_SERVER['Authorization'] : "";
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $signraturHeader = trim($_SERVER["HTTP_AUTHORIZATION"]);
        }
        try {
            if (!preg_match('/Bearer\s(\S+)/', $signraturHeader, $matches)) {
                $text = API_User_Response::$unauthorized_token;
                $errorcode = API_Error_Code::$internalUserWarning;
                $maindata = [];
                $hint = ["Check if all header values are sent correctly1.", "Follow the format stated in the documentation", "All letters in upper case must be in upper case", "Ensure the correct method is used"];
                $linktosolve = "https://";
                self::respondUnauthorized($maindata,$text,$hint,$linktosolve,$errorcode);
            }

            $jwt = $matches[1];

            if (!$jwt) {
                // No token was able to be extracted from the authorization header
                $text = API_User_Response::$unauthorized_token;
                $errorcode = API_Error_Code::$internalUserWarning;
                $maindata = [];
                $hint = ["Check if all header values are sent correctly2.", "Follow the format stated in the documentation", "All letters in upper case must be in upper case", "Ensure the correct method is used"];
                $linktosolve = "https://";
                self::respondUnauthorized($maindata,$text,$hint,$linktosolve,$errorcode);
            }
            $secretKey = $companyprivateKey;
            $token = JWT::decode($jwt, new Key($secretKey, 'HS512'));
            $now = new \DateTimeImmutable();

            if ($token->iss !== $serverName || $token->nbf > $now->getTimestamp() || $token->exp < $now->getTimestamp() || empty($token->usertoken)) {
                $text = API_User_Response::$unauthorized_token;
                $errorcode = API_Error_Code::$internalUserWarning;
                $maindata = [];
                $hint = ["Check if all header values are sent correctly3.", "Follow the format stated in the documentation", "All letters in upper case must be in upper case", "Ensure the correct method is used"];
                $linktosolve = "https://";
                self::respondUnauthorized($maindata,$text,$hint,$linktosolve,$errorcode);
            }

            return $token;
        } catch (\Exception $e) {
            $text = API_User_Response::$unauthorized_token;
            $errorcode = API_Error_Code::$internalUserWarning;
            $maindata = [];
            $hint = ["Check if all header values are sent correctly4.", "Follow the format stated in the documentation", "All letters in upper case must be in upper case", "Ensure the correct method is used"];
            $linktosolve = "https://";
            self::respondUnauthorized($maindata,$text,$hint,$linktosolve,$errorcode);
        }
    }


}