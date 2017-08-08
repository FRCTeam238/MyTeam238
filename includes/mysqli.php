<?php
/*********************************************************************
    mysli.php

    Description: mysqli requirements

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
$_DB = NULL;

function db_connect($host, $user, $passwd, $name){
    global $_DB;

    //Assert
    if(!strlen($user) || !strlen($host))
        return NULL;

    if (!($_DB = mysqli_init()))
        return NULL;

    //Connectr
    $_DB = mysqli_connect($host, $user, $passwd, $name);
    if(!@$_DB) # nolint
        return NULL;

    //set desired encoding just in case mysql charset is not UTF-8
    @$_DB->query('SET NAMES "utf8"');                          # nolint
    @$_DB->query('SET CHARACTER SET "utf8"');                  # nolint
    @$_DB->query('SET COLLATION_CONNECTION=utf8_general_ci');  # nolint

    return $_DB;
}

function db_close() {
    global $_DB;
    return @$_DB->close();
}

#################################
######### DB OPERATIONS #########
#################################

//don't call escape, call input
function db_real_escape($val, $quote = false) {
    global $_DB;

    //Magic quotes crap is taken care of in main.inc.php
    $val = $_DB->real_escape_string($val);

    return ($quote)?"'$val'":$val;
}

function db_input($var, $quote=true) {
    if(is_array($var))
        return array_map('db_input', $var, array_fill(0, count($var), $quote));
    elseif($var && preg_match("/^\d+(\.\d+)?$/", $var))
        return $var;
    return db_real_escape($var, $quote);
}

//criticals are gone, move along
function db_query($query) {
    global $_DB;
    $result = $_DB->query($query);
    if(!$result) {//error reporting
        //what to do with errors?
        //TODO
        echo($_DB->error);exit;
    }
    return $result;
}

function db_fetch_array($result, $mode=MYSQLI_ASSOC) {
    return ($result) ? db_output($result->fetch_array($mode)) : NULL; # nolint
}

function db_fetch_row($result) {
    return ($result) ? db_output($result->fetch_row()) : NULL; # nolint
}

function db_num_rows($result) {
    return ($result) ? $result->num_rows : 0; # nolint. send 0 if you have none
}

//dont call this, it is called internally
function db_output($var) {
    if(!function_exists('get_magic_quotes_runtime') || !get_magic_quotes_runtime()) //Sucker is NOT on - thanks.
        return $var;
    if (is_array($var))
        return array_map('db_output', $var);
    return (!is_numeric($var))?stripslashes($var):$var;
}