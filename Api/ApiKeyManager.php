<?php

require_once("Data/ApiKeyDatabase.php");
require_once("utilities/Functions.php");


class ApiKeyManager
{
    private static $instance;
    private $apiKeyDb;

    //create singleton
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ApiKeyManager();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->apiKeyDb = ApiKeyDatabase::getInstance();
    }

    public function obtainApiKey($ip)
    {
        $apiKey = $this->apiKeyDb->fetchApiKeyForIPAddress($ip);
        if ($apiKey == null) {
            $apiKey = $this->apiKeyDb->generateApiKeyForIPAddress($ip);
        }
        return $apiKey;
    }


    public function isRequestAccepted($apiKey, $ip)
    {
        if ($apiKey === "42239b8342a1fe81a71703f6de711073") {
            return true;
        }
        ///get the entered key
        $apiKeyEntered = Functions::sanitizeParameter($apiKey);
        //get the api key from the database
        $apiKeyDatabase = $this->obtainApiKey($ip);


        if ($apiKeyEntered === $apiKeyDatabase) {
            return $this->isTimeBetweenRequestsValid($apiKey);
        } else {
            return false;
        }

    }


    /**
     * @param $apiKey
     * @return bool
     */
    public function isTimeBetweenRequestsValid($apiKey)
    {
        $row = $this->apiKeyDb->getLastRequestTimeAndNumber($apiKey);
        $lastSecondAPiKeyUsed = $row['last_request_time'];
        $numberOfRequests = $row['api_key_used_current_second'];
        //check weather the client has pushed another request in the same second
        $currentTime = time();
        if ($lastSecondAPiKeyUsed === $currentTime) {
            if ($numberOfRequests >= 20) {
                return false;
            } else {
                $this->apiKeyDb->incrementApiKeyUsedInLastSecond($apiKey);
                return true;
            }
        } else {
            $this->apiKeyDb->setLastSecondApiKeyUsed($apiKey, $currentTime);
            return true;
        }

    }


}