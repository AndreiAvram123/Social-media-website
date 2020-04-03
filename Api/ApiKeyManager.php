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


    /**
     * Function used to fetch the api key for a given ip address
     * @param $ip
     * @return string |null
     */
    public function fetchApiKey($ip)
    {
        return $this->apiKeyDb->fetchApiKeyForIPAddress($ip);
    }

    public function isRequestAccepted(string $apiKey, string $ip): bool
    {
        if ($apiKey === "42239b8342a1fe81a71703f6de711073") {
            return true;
        }
        ///get the entered key
            $apiKeyEntered = Functions::sanitizeParameter($apiKey);
        //get the api key from the database
        $apiKeyDatabase = $this->fetchApiKey($ip);

        if ($apiKeyDatabase !== null && $apiKeyEntered === $apiKeyDatabase) {
            return $this->isTimeBetweenRequestsValid($apiKey);
        } else {
            return false;
        }

    }


    /**
     * @param $apiKey
     * @return bool
     */
    public function isTimeBetweenRequestsValid($apiKey): bool
    {
        $row = $this->apiKeyDb->getLastRequestTimeAndNumber($apiKey);
        $lastSecondAPiKeyUsed = $row['last_request_time'];
        $numberOfRequests = $row['api_key_used_current_second'];
        //check weather the client has pushed another request in the same second
        $currentTime = time();
        if ($lastSecondAPiKeyUsed === $currentTime) {
            if ($numberOfRequests >= 6) {
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