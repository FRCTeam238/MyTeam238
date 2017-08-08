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

    /*
    static function getUsersName($id){
            $name = '';
            
            $sql = "SELECT `prefName`, `lastName`"
                     . " FROM ".TABLE_ACCOUNTS
                     . " WHERE `KC_ID` = ".db_input($id);

            $row = db_fetch_row(db_query($sql));

            $name = $row[0]." ".$row[1];

            return $name;
    }

    static function getUsersNameEmail($email){
            $name = '';

            $sql = "SELECT `prefName`, `lastName`"
                     . " FROM ".TABLE_ACCOUNTS
                     . " WHERE `email` = ".db_input($email);

            $row = db_fetch_row(db_query($sql));

            $name = " ".$row[0]." ".$row[1];

            return $name;
    }
*/
    
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