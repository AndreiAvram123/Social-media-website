<?php
require_once("Data/Database.php");
require_once("Data/DataEncoder.php");

class ApiKeyDatabase
{
    //create singleton
    private static $instance;
    private $_dbInstance;
    private $_dbHandler;

    public static function getInstance()
    {

        if (self::$instance == null) {
            self::$instance = new ApiKeyDatabase();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandler = $this->_dbInstance->getDatabaseConnection();
    }

    public function fetchApiKeyForIPAddress($ip)
    {
        $query = "SELECT api_key_value FROM api_keys WHERE ip_address_client = '$ip'";
        $result = $this->executeQuery($query);
        $row = $result->fetch();
        if ($row == false) {
            return null;
        } else {
            return $row['api_key_value'];
        }
    }

    public function generateApiKeyForIPAddress($ip)
    {
        $apiKey = $this->generateApiKey($ip);
        $date = time();
        $query = "INSERT INTO api_keys VALUES (NULL,'$ip','$apiKey','$date')";
        $this->executeQuery($query);
        return $apiKey;
    }

    private function generateApiKey($ip)
    {
        return DataEncoder::encodeWithSha512($ip);
    }


    private function executeQuery($query)
    {
        $result = $this->_dbHandler->prepare($query);
        $result->execute();
        return $result;
    }

    public function getLastTimeApiKeyUsed($apiKey)
    {
        $query = "SELECT  last_request_time FROM api_keys WHERE api_key_value = :apiKey";
        $result = $this->_dbHandler->prepare($query);
        $result->bindValue(':apiKey', $apiKey);
        $result->execute();
        $row = $result->fetch();
        return $row['last_request_time'];
    }
}