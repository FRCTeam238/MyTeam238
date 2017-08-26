<?php
/*********************************************************************
    format.class.php

    Description: Function used for formatting data and inputs

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class Format {

    static function currentDateTime(){
        return date("Y-m-d H:i:s");
    }

    static function displayDateTimeFromDB($dbIn){
        $dbOut = date("M j, Y - g:i a",strtotime($dbIn));
        return $dbOut;
    }

    static function dbDateTimeToJquery($dbIn){
        $dbOut = date("m/d/Y h:i:s a",strtotime($dbIn));
        return $dbOut;
    }

    static function sanitizeName($string, $fullname = FALSE) {//full name skips adding hyphens
        if(!$fullname){$string = str_replace(' ', '-', $string);} // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
    
    static function dateTimeSelectIn($in){
        return date("Y-m-d H:i:s",strtotime($in));
    }

    static function Filesize($size)
    {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
}