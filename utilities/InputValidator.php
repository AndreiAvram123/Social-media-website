<?php


class InputValidator
{
    public static function isNumericParameterValid($parameter)
    {
        if ($parameter === "") return false;
        if (!is_numeric($parameter)) return false;
        return true;
    }
}