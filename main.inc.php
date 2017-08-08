<?php
/*********************************************************************
    main.inc.php

    Description: System magic to include on ALL pages!

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
	
    #Disable direct access.
    if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__))) die('nice try!');

    #Disable Globals if enabled....before loading config info
    if(ini_get('register_globals')) {
       ini_set('register_globals',0);
       foreach($_REQUEST as $key=>$val)
           if(isset($$key))
               unset($$key);
    }

    #Disable url fopen && url include
    ini_set('allow_url_fopen', 0);
    ini_set('allow_url_include', 0);

    #Disable session ids on url.
    ini_set('session.use_trans_sid', 0);
	
    #Start dat session yo
    session_start();
	
    #Error reporting...Good idea to ENABLE error reporting to a file. i.e display_errors should be set to false
/*
    $error_reporting = E_ALL & ~E_NOTICE;
    if (defined('E_STRICT')) # 5.4.0
        $error_reporting &= ~E_STRICT;
    if (defined('E_DEPRECATED')) # 5.3.0
        $error_reporting &= ~(E_DEPRECATED | E_USER_DEPRECATED);
    error_reporting($error_reporting); //Respect whatever is set in php.ini (sysadmin knows better)
    #Don't display errors
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
*/
    #Default timezone
    date_default_timezone_set('America/New_York');//we just use EST yo
	
    #The big one, get us to where we are installed
    define('ROOT_DIR',realpath(dirname(__FILE__)));

    define('INCLUDE_DIR',ROOT_DIR.'/includes/'); //Change this if include is moved outside the web path.
    define('STATIC_DIR',ROOT_DIR.'/static/');
    define('CLASSES_DIR',ROOT_DIR.'/classes/');
    #define('PDF_DIR',ROOT_DIR.'/static/pdf/');//if in use, PDF directory
    #define('UPDATES_DIR',ROOT_DIR.'/staff/updates/');//user details update control folder

    #load config info	
    if(!file_exists(STATIC_DIR.'config.php')) die('<b>Error loading system configuration. Contact helpdesk.</b>');
    else{require(STATIC_DIR.'config.php');}
	
    #System Info
    define('SITE_URL',$_SYS_URL);
    define('SITE_FULLNAME',$_SYS_FULLNAME);
    define('SITE_SHORTNAME',$_SYS_SHORTNAME);
    #Footer Info
    define('SOCIAL_TWITTER',$_SYS_TWITTER);
    define('SOCIAL_FB',$_SYS_FB);
    define('SOCIAL_WWW',$_SYS_WWW);
    #System Secret
    define('ADMIN_EMAIL',$_SYS_ADMIN);
    #System Database
    define('DBHOST',$_SYS_DB_HOST); 
    define('DBNAME',$_SYS_DB_NAME);
    define('DBUSER',$_SYS_DB_USER);
    define('DBPASS',$_SYS_DB_PASS);
    define('TABLE_PREFIX',$_SYS_TABLE_PREFIX);
	
    #include required files
    require(INCLUDE_DIR.'buildpage.class.php');
    #require(INCLUDE_DIR.'buildid.class.php');
    require(INCLUDE_DIR.'format.class.php');
    require(INCLUDE_DIR.'email.class.php');
    require(INCLUDE_DIR.'secure.class.php');
    #require(INCLUDE_DIR.'staff.class.php');
    require(INCLUDE_DIR.'data.class.php');
    require(INCLUDE_DIR.'mysqli.php');

    #CURRENT EXECUTING SCRIPT.
    define('THISURI', $_SERVER['REQUEST_URI']);

    #Tables being used sytem wide
    #User and System Settings
    define('TABLE_CONFIG',TABLE_PREFIX.'settings');
    define('TABLE_CODES',TABLE_PREFIX.'status_codes');
    define('TABLE_SESSIONS',TABLE_PREFIX.'user_sessions');
    define('TABLE_USERS',TABLE_PREFIX.'users');
    define('TABLE_USERDETAILS',TABLE_PREFIX.'user_details');
    define('TABLE_INVITES',TABLE_PREFIX.'registration_invitations');
    define('TABLE_LOG_EMAIL',TABLE_PREFIX.'server_email_log');
    define('TABLE_LOG',TABLE_PREFIX.'server_action_log');
    #define('TABLE_CONTROL',TABLE_PREFIX.'user_control');
	
    #Special Staff Settings
    #define('TABLE_STAFF_PERM',TABLE_PREFIX.'staff_permissions');
    #define('TABLE_STAFF_REPORTS',TABLE_PREFIX.'staff_reports');

    #Event Controls
    #define('TABLE_EVENTS',TABLE_PREFIX.'events');
    #define('TABLE_EVENTS_TIME',TABLE_PREFIX.'event_timing');
    #define('TABLE_SPORTS',TABLE_PREFIX.'sports');

    #Tent Controls
    #define('TABLE_TENTS',TABLE_PREFIX.'tents');
    #define('TABLE_TENTS_DQ',TABLE_PREFIX.'tents_dq');
    #define('TABLE_TENTS_CHECKS',TABLE_PREFIX.'tent_checks');
    #define('TABLE_TENTS_CHECKS_DETAIL',TABLE_PREFIX.'tent_check_detail');
    #define('TABLE_TENTS_GROUPS',TABLE_PREFIX.'tent_groups');
    #define('TABLE_TENTS_INVITATIONS',TABLE_PREFIX.'tent_invitations');

    #Connect to the DB
    if(!db_connect(DBHOST,DBUSER,DBPASS,DBNAME)){
       #Unable to connect to the database!
       die('DB UNAVAILABLE');
    }
	
    #Cleanup magic quotes crap.
    if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){
        $_POST=Format::strip_slashes($_POST);
        $_GET=Format::strip_slashes($_GET);
        $_REQUEST=Format::strip_slashes($_REQUEST);
    }