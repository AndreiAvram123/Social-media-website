<?php


class Functions
{

    public static function sanitizeParameter($query)
    {
        $query = htmlentities($query);
        $query = str_replace("%", "", $query);
        return $query;
    }
    public static function encodeWithSha512($text){
        return  hash('sha512', $text);
    }
}
?>