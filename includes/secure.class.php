<?php
/*********************************************************************
    secure.class.php

    Description: Secures a page and handles

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class Secure {
	
    static function suspendThisSession(){
        $code = $_SESSION['statusCode'];
        $_SESSION = array();//destroy all of the session variables
        session_destroy();
        session_start();
        $_SESSION['statusCode'] = $code;
        session_write_close();//stupid sessions
        return 1;
    }

    function checkIfTokenExpired(){
        if(!isset($_SESSION['_user']['tokenExpire'])){return 0;}
        return (($_SESSION['_user']['tokenExpire']) < time() ? 1 : 0);
    }

    function compareIP(){
        return (($_SESSION['_user']['ip']) == ($_SERVER['REMOTE_ADDR']) ? 1 : 0);
    }
	
    function requestNewToken(){
        $sql = "SELECT `activeSessionId`, `ip` "
                 . "FROM ".TABLE_SESSIONS." "
                 . "WHERE `user_id` = ".db_input($_SESSION['_user']['id']).";";

        if(!($res=db_query($sql)) || !db_num_rows($res)){
            //problem with query? or it could just be no session. maybe someone else logged in somewhere else?
            $_SESSION['statusCode'] = 0;
            session_write_close();
            header("Location: ".SITE_URL."logout");
        }
        else{
            $result = db_fetch_row($res);
        }
        if(!isset($result[0])){$result[0] = 0;}
        if(md5(session_id()) == $result[0]){			
            if((($result[1]) == $_SERVER['REMOTE_ADDR']) || (($_SERVER['REMOTE_ADDR']) == "::1")){//REMOVE SECOND CONDITION FOR PROD. Or leave, since I doubt anyone will replicate it. I dont care.
                if($this->compareIP($result[0])){//ip good and matches DB one
                    $_SESSION['_user']['tokenExpire'] = time() + (180);//time to add to each extend, in seconds
                    return 1;
                }
                else{//ip was good, but didnt match DB. logout.
                    $_SESSION['statusCode'] = 1017;
                    $this->suspendThisSession();
                    session_write_close();
                    header("Location: ".SITE_URL."login");
                    return 0;
                }
            }
            else{//bad ip, logout
                $_SESSION['statusCode'] = 1017;
                $this->suspendThisSession();
                header("Location: ".SITE_URL."login");
                session_write_close();
                return 0;
            }
        }
        else{//wrong session id, logout
            $_SESSION['statusCode'] = 1017;
            $this->suspendThisSession();
            session_write_close();
            header("Location: ".SITE_URL."login");
            return 0;
        }
    }

    function startNewSession(){
        $sql1 = "SELECT * "
                 . "FROM ".TABLE_SESSIONS." "
                 . "WHERE `user_id` = ".db_input($_SESSION['_user']['id']).";";
        if(db_num_rows(db_query($sql1))){
            //Existing session row, delete
            $sql2 = "DELETE FROM ".TABLE_SESSIONS." WHERE `user_id` = ". db_input($_SESSION['_user']['id']).";";
            db_query($sql2);
        }
        
        //Make new session        
        $sql3 = "INSERT INTO ".TABLE_SESSIONS." "
                    . "(user_id, sessionStart, activeSessionId, ip) "
                    . " VALUES "
                    . "(".db_input($_SESSION['_user']['id']).", '".Format::currentDateTime()."', '".md5(session_id())."', '".$_SERVER['REMOTE_ADDR']."');";
        db_query($sql3);
        $_SESSION['_user']['tokenExpire'] = time() + 180;
    }
    
    function requireLogin(){
        if(!isset($_SESSION['_user'])){
            //user not logged in, send them to login.
            $_SESSION['statusCode'] = 0;
            session_write_close();
            header("Location: ".SITE_URL."login");
            exit;
        }
        else
        {
            if($this->checkIfTokenExpired()){
                //token is expired
                return($this->requestNewToken() ? 1 : 0);
            }
            else{//token is still good. party on.
                return 1;
            }
        }
     }
	
        /*
	function requireStaffLogin(){

		if($this->requireLogin()){
			if(!isset($_SESSION['_staff'])){ //user not staffn, send them to home
				$_SESSION['statusCode'] = 108;
				session_write_close();
		   		header("Location: ".SITE_URL."index.php");
	   		}
		}
	   	else
		{
			return 0;
		}
	}
	
	function checkStaffToken($token){

		if(!$_SESSION['_staff'][$token]){//doesnt have the token they need
				$_SESSION['statusCode'] = 151;
				session_write_close();
		   		header("Location: ".SITE_URL."staff/index.php");
	   	}
	   	else
		{
			return 1;
		}
	}
	
	function loadStaffTokens(){
		
		$sql = "SELECT * "
			 . "FROM ".TABLE_STAFF_PERM." "
			 . "WHERE `KC_ID` = ".db_input($_SESSION['_user']['KC_ID']).";";
			 
		if(!($res=db_query($sql)) || !db_num_rows($res)){
			//problem with query? or it could just be no session. maybe someone else logged in somewhere else?
			//TODO something?
		}
		else{
			$row = db_fetch_array($res);
			foreach($row as $key => $value){
				$_SESSION['_staff'][$key] = $value[$key];
			}
			unset($_SESSION['_staff']['KC_ID']);
		}
		if($_SESSION['_staff']['canViewReports']){
			$this->loadStaffReportTokens();
		}
		return 1;
	}
	
	function loadStaffReportTokens(){
		$sql = "SELECT * "
			 . "FROM ".TABLE_STAFF_REPORTS." "
			 . "WHERE `KC_ID` = ".db_input($_SESSION['_user']['KC_ID']).";";
			 
		if(!($res=db_query($sql)) || !db_num_rows($res)){
			//problem with query? or it could just be no session. maybe someone else logged in somewhere else?
			//TODO something?
		}
		else{
			$row = db_fetch_row($res);
			$_SESSION['_reports']['report1'] = $row[1];
		}
		return 1;
	}
         
    function checkIfHasCurrentSession(){
            #see if the user currently has a session, to determine upgrade or insert
    }
*/
    static function makePassword($password){
        //controls hashing and prepping a string into something that can
        //be compared to the DB user control value
        require INCLUDE_DIR.'password.inc.php';
        return password_hash($password, PASSWORD_DEFAULT);
    }

    static function checkPassword($password, $answer){
        //be compared to the DB user control value
        require INCLUDE_DIR.'password.inc.php';
        return password_verify($password, $answer);
    }
}