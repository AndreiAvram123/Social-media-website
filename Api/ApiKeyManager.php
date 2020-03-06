<?php

require_once("Data/ApiKeyDatabase.php");

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

    public function getApiKeyForAddress($ip)
    {
        $apiKey = $this->apiKeyDb->fetchApiKeyForIPAddress($ip);
        if ($apiKey == null) {

        }
    }


}