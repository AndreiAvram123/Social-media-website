<?php


class DataEncoder
{
    public static function encodeWithSha512($text)
    {
        return hash('sha512', $text);
    }
}