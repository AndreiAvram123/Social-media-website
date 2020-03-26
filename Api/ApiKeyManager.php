<?php

require_once("Data/ApiKeyDatabase.php");
require_once("utilities/Functions.php");


class ApiKeyManager
{
    private static $instance;
    private $apiKeyDb;
    private $timeBetweenRequest = 0.3;

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

    public function setLastRequestTime($apiKey)
    {
        $this->apiKeyDb->setLastRequestTime($apiKey, time());

    }

    /**
     * @param $apiKey
     * @return bool
     */
    public function isTimeBetweenRequestsValid($apiKey): bool
    {
        $lastTimeRequest = (int)$this->apiKeyDb->getLastTimeApiKeyUsed($apiKey);
        $currentTime = time();
        return $lastTimeRequest + $this->timeBetweenRequest < $currentTime;
    }


}