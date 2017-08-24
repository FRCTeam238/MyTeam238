<?php
/*********************************************************************
    config.php

    Description: System configuration

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/

    #Site URL
	#The FULL site URL for this product installation, including HTTP or HTTPS to the ROOT of system install
	#DO INCLUDE THE TRAILING SLASH
	$_SYS_URL = 'http://localhost/frc238/';
	
	#Company Website
	#Include FULL site, with HTTP or HTTPS, etc
	$_SYS_WWW = 'http://www.frc238.org';
	
	#Company Twitter Handle
	#Should NOT include the www.twitter.com part or the trailing /
	$_SYS_TWITTER = 'FRCTeam238';
	
	#Company FB Page Name
	#Should NOT include the www.facebook.com part or the trailing /
	$_SYS_FB = 'FRC238';
	
	#Full Site Name (Like "Company Name Inc"). 40 characters or less!
	$_SYS_FULLNAME = 'FIRST Robotics Competition Team 238';
	
	#Short Site Name (like "Company"). 20 characters or less!
	$_SYS_SHORTNAME = 'Team 238';
	
	#Default admin email. Used only on db connection issues and related alerts.
	#TODO - set during install
	$_SYS_ADMIN = 'web@frc238.org';
        
        #Test Mode?
        $_SYS_TESTMODE = true;
	
	#Mysql Login info
	#TODO - set during install
	$_SYS_DB_HOST = 'localhost';
	$_SYS_DB_NAME = 'frc238';
	$_SYS_DB_USER = 'root';
	$_SYS_DB_PASS = '';
	
	#Table prefix
	#prefix is optional
	$_SYS_TABLE_PREFIX = '';
?>