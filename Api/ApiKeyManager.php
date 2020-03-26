<?php

require_once("Data/ApiKeyDatabase.php");

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
     * @return |null
     */
    public function fetchApiKey($ip)
    {
        return $this->apiKeyDb->fetchApiKeyForIPAddress($ip);
    }

    public function isRequestAccepted($apiKey)
    {
        $response = (int)$this->apiKeyDb->getLastTimeApiKeyUsed($apiKey);
        $currentTime = time();
        return $response + $this->timeBetweenRequest < $currentTime;
    }

    public function setLastRequestTime($apiKey)
    {
        $this->apiKeyDb->setLastRequestTime($apiKey,time());

    }


}