<?php
/*********************************************************************
    data.class.php

    Description: Generates and controls data access (write tasks, reading 
                    only if they need it as a part of the task)

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class Data extends DataRead {
    
    function doLog($status_code, $user_id, $url, $message){
        $sql1 = "INSERT INTO `".TABLE_LOG."`(`status_id`, `user_id`, `url`, `message`) "
                . "VALUES (".$status_code.",".$user_id.",'".$url."','".$message."');";
        return db_query($sql1);
    }
    
    function doLogEmailSent($status_code, $user_id, $sent_when, $user_profile_id = NULL){
        $sql1 = "INSERT INTO `".TABLE_LOG_EMAIL."`(`status_id`, `user_id`, `sent`, `user_profile_id`) "
                . "VALUES (".$status_code.",".$user_id.",'".$sent_when."',".$user_profile_id.");";
        if($user_profile_id == NULL){
            $sql1 = substr($sql1, 0, -2);
            $sql1 .= "NULL);";
        }
        return db_query($sql1);
    }
    
    static function doGetUserProfilePicPath($user_id){
        $prof_pic_path = SITE_URL.'images/profile/';
        $sql1 = "SELECT UD.profile_pic_key FROM ".TABLE_USERDETAILS." UD "
                . "WHERE UD.user_id = ".db_input($user_id)." "
                . "AND UD.profile_pic_key IS NOT NULL "
                . "AND UD.is_deleted = 0;";
        if(db_num_rows(db_query($sql1))){
            $prof_pic_path .= db_fetch_row(db_query($sql1))[0] . '.jpg';
        }
        else{
            $prof_pic_path .= 'default.jpg';
        }
        return $prof_pic_path;
    }
    
    function doSaveUserProfilePicKey($user_id, $pic_key){
        $sql1 = "UPDATE ".TABLE_USERDETAILS." UD "
                    . "SET UD.profile_pic_key = ". db_input($pic_key)." "
                    . "WHERE UD.user_id = ". db_input($user_id);
        return db_query($sql1);
    }
    
    function doCreateAccount($email, $password, $emailKey, $access_code_used){
        //User creating a new account
        if(!isset($access_code_used) || $access_code_used == NULL){$access_code_used = 0;}
        $now = Format::currentDateTime();
        $sql1 = "SELECT * FROM ".TABLE_USERS." U WHERE U.email = ".db_input($email)."";
        if(db_num_rows(db_query($sql1))){//email in use
            $_SESSION['statusCode'] =  1001;
            return 0;
        }
        $sql2 = "START TRANSACTION;";
        $result2 = db_query($sql2);
        $sql3 = "INSERT INTO `".TABLE_USERS."`(email, password) VALUES (".db_input($email).", ".db_input($password).");";
        $result3 = db_query($sql3);
        $sql4 = "SELECT LAST_INSERT_ID();";
        $result4 = db_query($sql4);
        $insert_id = db_fetch_row($result4)[0];
        $sql5 = "INSERT INTO `".TABLE_USERDETAILS."` (user_id, emailKey, createdOn, registrationIP, account_approved) VALUES (".$insert_id.", '".$emailKey."', '".$now."','".$_SERVER['REMOTE_ADDR']."', ".$access_code_used.");";
        $result5 = db_query($sql5);
        $sql6 = "COMMIT;";
        $result6 = db_query($sql6);
        $sql7 = "ROLLBACK;";
        
        //return an array of [result, user_id]
        if($result2 && $result3 && $result4 && $result5 && $result6){ //query passes
            return [1, $insert_id];
        }
        else{
            db_query($sql7);
            return [0, 0];
        }
    }
    
    function doActivateAccount($user_id, $emailKey){
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.ID = ".db_input($user_id)." "
                . "AND UD.emailKey = ". db_input($emailKey)." "
                . "and UD.emailVerified = 0;";
        if(db_num_rows(db_query($sql1))){
            //mark activated
            $sql2 = "UPDATE ".TABLE_USERDETAILS." UD "
                    . "SET emailVerified = 1 "
                    . "WHERE UD.user_id = ". db_input($user_id);
            return db_query($sql2);
        }
        else{
            return 0;
        }
    }
    
    function doApproveAccount($user_id){
        $sql1 = "UPDATE ".TABLE_USERDETAILS." UD "
                . "SET account_approved = 1 "
                . "WHERE UD.user_id = ". db_input($user_id);
        return db_query($sql1);
    }
    
    function allowNewRegistrations(){
        $sql1 = "SELECT * FROM ".TABLE_CONFIG." S "
                . "WHERE S.allow_new_accounts = 1 "
                . "LIMIT 1;";
        return db_num_rows(db_query($sql1));
    }
    
    function doAccountLogin($season_id, $email, $password){
        require(CLASSES_DIR.'login_info.php');
        $login_info = new login_info();        
        $sql1 = "SELECT U.*, UD.* FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.email = ".db_input($email)." "
                . "AND U.password = ". db_input($password)." "
                . "AND UD.is_deleted = 0;";
        if(db_num_rows(db_query($sql1))){
            //account exists
            $login_holding = db_fetch_array(db_query($sql1));
            $login_info->account_found_valid = 1;
            $login_info->email_address = $login_holding['email'];
            $login_info->account_id = $login_holding['user_id'];
            $login_info->emailVerified = $login_holding['emailVerified'];
            $login_info->first_name = $login_holding['first_name'];
            $login_info->last_name = $login_holding['last_name'];
            $login_info->account_approved = $login_holding['account_approved'];
            $login_info->dob = $login_holding['dob'];
            $login_info->forcePwChange = $login_holding['forcePwChange'];
            $login_info->isProfileComplete();
            
            //Now we want some basic info from their season profile, if they've started it
            $login_info->preferred_first_name = "";//defaults, only change if they have a profile
            $login_info->registrant_type = 0;
            if($this->userHasProfileInActiveSeason($season_id, $login_holding['user_id'])){
                $current_profile = $this->getCurrentSeasonProfile($login_info->account_id, $season_id);
                $login_info->preferred_first_name = $current_profile->preferred_first_name;
                $login_info->registrant_type = $current_profile->registration_type;
            }
        }
        else{
            $login_info->account_found_valid = 0;            
        }
        return $login_info;
    }
    
    static function getUsersNameFromEmail($email){
        $name = '';
        $sql = "SELECT `first_name`, `last_name`"
                 . " FROM ".TABLE_USERDETAILS." UD "
                 . " JOIN ".TABLE_USERS." U ON UD.user_id = U.id "
                 . " WHERE U.email = ".db_input($email);
        $row = db_fetch_row(db_query($sql));
        $name = "";
        if(!empty($row[0])){
            $name .= $row[0];
                if(!empty($row[1])){
                    $name .= " " . $row[1];            
                }
        }
        return $name;
    }
    
    function doAllowAccountPwReset($user_email, $resetKey, $expirationDateTimeUnix){
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.email = ".db_input($user_email)." "
                . "AND UD.is_deleted = 0;";
        if(!db_num_rows(db_query($sql1))){
            //user not found
            return 0;
        }
        else{
            $sql2 = "UPDATE ".TABLE_USERDETAILS." UD "
                    . "JOIN ".TABLE_USERS." U ON UD.user_id = U.id "
                    . "SET pwResetKey = '".$resetKey."', "
                    . "pwResetExpiration = FROM_UNIXTIME(".$expirationDateTimeUnix.") "
                    . "WHERE U.email = ". db_input($user_email);
            return db_query($sql2);
        }
    }
    
    function doUpdateUserPassword($user_email, $resetKey, $newPassword){
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.email = ".db_input($user_email)." "
                . "AND UD.is_deleted = 0 "
                . "AND UD.pwResetKey = ".db_input($resetKey)." "
                . "AND UD.pwResetExpiration >= NOW();";
        if(!db_num_rows(db_query($sql1))){
            //user not found, resetkey expired or wrong
            return 0;
        }
        else{
            $sql2 = "UPDATE ".TABLE_USERDETAILS." UD, ".TABLE_USERS." U "
                    . "SET U.password = ". db_input($newPassword).", "
                    . "UD.pwResetKey = NULL, "
                    . "UD.pwResetExpiration = NULL "
                    . "WHERE U.email = ". db_input($user_email);
            return db_query($sql2);
        }
    }
    
    function doUpdateUserPassword_ViaCurrent($user_id, $user_currentpassword, $user_newpassword){//print_r($_POST);exit;
        //should only do update if current password was correct
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "WHERE U.id = ".$user_id." "
                . "AND U.password = ". db_input($user_currentpassword).";";
        if(!db_num_rows(db_query($sql1))){
            //user not found, or password wrong
            return 0;
        }
        else{
            $sql2 = "UPDATE ".TABLE_USERS." U "
                    . "SET U.password = ". db_input($user_newpassword)." "
                    . "WHERE U.id = ".$user_id.";";
            return db_query($sql2);
        }
    }
    
    function doUpdateUserDetails($user_id, $fname, $lname, $dob){
        $sql1 = "UPDATE ".TABLE_USERDETAILS." UD "
                . "SET UD.first_name = ". db_input($fname).", "
                . "UD.last_name = ". db_input($lname).", "
                . "UD.dob = ". db_input($dob)." "
                . "WHERE UD.user_id = ". db_input($user_id);
        return db_query($sql1);
    }
    
    function acceptInvitation($invite_id, $invite_key){
        $sql1 = "SELECT * FROM ".TABLE_INVITES." I "
                . "WHERE I.id = ". db_input($invite_id)." "
                . "AND I.key = ". db_input($invite_key)." "
                . "AND I.used = 0;";
        $row = db_fetch_row(db_query($sql1));
        $create = $this->doCreateAccount($row[1], "temppass", "invited", FALSE);
        if($create){
            //Now get the id we just inserted
            $sql2 = "SELECT U.id FROM ".TABLE_USERS." U "
                . "WHERE U.email = ". db_input($row[1]).";";
            $new_id = db_fetch_row(db_query($sql2))[0];
            $activate = $this->doActivateAccount($new_id, "invited");
            if($activate){
                $approved = $this->doApproveAccount($new_id); 
                $pwreset = $this->doAllowAccountPwReset($row[1], "invitedacct", (time() + 900));//you get 15 mins to set password
                if($approved && $pwreset){
                    //flag the invite as used
                    $sql2 = "UPDATE ".TABLE_INVITES." I "
                    . "SET used = 1 "
                    . "WHERE I.id = ". db_input($invite_id);
                    db_query($sql2);
                    //regardless, if we make it here, count as all good and return the email address
                    return $row[1];
                }
                else{
                    return 0;
                }
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }
    
    function insertInvitation($to_email, $to_name, $requester_id, $requester_name, $key){
        $sql1 = "INSERT INTO `".TABLE_INVITES."`(`email_invited`, `name_invited`, `requester`, `requester_user_id`, `key`) "
                . "VALUES (".db_input($to_email).",".db_input($to_name).",".db_input($requester_name).",".db_input($requester_id).",".db_input($key).")";
        db_query($sql1);
        return db_fetch_row(db_query("SELECT LAST_INSERT_ID();"))[0];
    }
    
    function referredUsers($referred_by_id){
        $sql1 = "SELECT * FROM ".TABLE_INVITES." I "
                . "WHERE I.requester_user_id = ". db_input($referred_by_id)." "
                . "AND I.used = 1;";
        $query = db_query($sql1);
        $referred_users = array();
        if(db_num_rows($query)){//at least 1 event
            while($row = db_fetch_array($query)){
                array_push($referred_users, array($row['name_invited'], $row['server_stamp']));
            }
        }
        return $referred_users;
    }
    
    function doSignBehaviorContract($season_id, $user_id, $email, $password, $fname, $lname){
        $verify = $this->doAccountLogin($season_id, $email, $password);
        if($verify->account_found_valid){
            //at this point, the password was valid
            $fullname = $this->getUsersNameFromEmail($email);
            if(strtolower($fullname) == strtolower($fname . " " . $lname)){
                $sql1 = "UPDATE ".TABLE_PROFILE." UP "
                . "SET UP.behavior_contract = 1 "
                . "WHERE UP.user_id = ". db_input($user_id);
                return db_query($sql1);
            }
        }        
        return 0;
    }
    
    function getCurrentlyActiveSeason(){
        $sql1 = "SELECT * FROM ".TABLE_SEASONS." s "
                . "WHERE s.is_active = 1 "
                . "ORDER BY year DESC "
                . "LIMIT 1;";
        require(CLASSES_DIR.'season_info.php');
        $season_info = new season_info();
        if(db_num_rows(db_query($sql1))){
            //season exists
            $season_hold = db_fetch_row(db_query($sql1));
            $season_info->season_id = $season_hold[0];
            $season_info->season_year = $season_hold[1];
            $season_info->season_name = $season_hold[2];
            $season_info->season_active = $season_hold[3];
        }
        return $season_info;
    }
    
    function joinSeason($user_id, $season_id, $pref_name, $seasonrole){
        require_once(CLASSES_DIR.'registrant_types.php');
        $seasonrole = strtoupper($seasonrole);
        if(!$this->userHasProfileInActiveSeason($season_id, $user_id) && $seasonrole != "Sponsor"){
            $sql1 = "INSERT INTO ".TABLE_PROFILE." (`season_id`, `user_id`, `registration_type`, `preferred_first_name`, `profile_started`) "
                . "VALUES (". db_input($season_id).", ". db_input($user_id).", ". db_input($seasonrole).",". db_input($pref_name).", '".Format::currentDateTime()."')";
            if(db_query($sql1)){
                $sql2 = "SELECT LAST_INSERT_ID();";
                $result2 = db_query($sql2);
                $insert_id = db_fetch_row($result2)[0];                
                if($seasonrole == RegistrantTypes::Mentor || $seasonrole == RegistrantTypes::Parent){
                    $sql2 = "INSERT INTO ".TABLE_PROFILE_MENTORPARENT." (`user_profile_id`) "
                            . "VALUES (".$insert_id.")";
                }
                else if($seasonrole == RegistrantTypes::Student){
                    $sql2 = "INSERT INTO ".TABLE_PROFILE_STUDENT." (`user_profile_id`) "
                            . "VALUES (".$insert_id.")";
                }
                else if($seasonrole == RegistrantTypes::Alumni){
                    $sql2 = "INSERT INTO ".TABLE_PROFILE_ALUMNI." (`user_profile_id`) "
                            . "VALUES (".$insert_id.")";
                }
                return db_query($sql2);
            }
        }
        else{
            return 0;
        }
    }
    
    function doUpdateEmailAddress($user_id, $email, $password, $new_email, $newverifykey){
        $verify = $this->doAccountLogin($email, $password);
        if($verify->account_found_valid){
            $sql1 = "UPDATE ".TABLE_USERS." u "
                    . "SET u.email = ".db_input($new_email)." "
                    . "WHERE u.email = ".db_input($email)." "
                    . "AND u.id = ".db_input($user_id).";";
            $result1 = db_query($sql1);
            $sql2 = "UPDATE ".TABLE_USERDETAILS." ud "
                    . "SET ud.emailVerified = 0, "
                    . "ud.emailKey = ".db_input($newverifykey)." "
                    . "WHERE ud.user_id = ".db_input($user_id).";";
            $result2 = db_query($sql2);
            if($result1 && $result2){
                return 1;
            }
        }
        return 0;
    }
    
    function doDeactivateAccount($user_id, $email, $password){
        $verify = $this->doAccountLogin($email, $password);
        if($verify->account_found_valid){
            $sql1 = "UPDATE ".TABLE_USERDETAILS." ud "
                    . "SET ud.is_deleted = 1, forcePwChange = 1 "
                    . "WHERE ud.user_id = ".db_input($user_id)." "
                    . "LIMIT 1;";
            return db_query($sql1);
        }
        return 0;
    }
    
    function doUpdateSeasonProfile($user_id, $season_id, $cell, $gender, $shirt, $bio, $addr1, $addr2, $city, $state, $zip){
        $sql1 = "UPDATE ".TABLE_PROFILE." "
                . "SET cell_phone = ". db_input($cell).", gender = ". db_input($gender).", shirt_size = ". db_input($shirt).", "
                . "biography = ". db_input($bio).", address_1 = ". db_input($addr1).", address_2 = ". db_input($addr2).", "
                . "address_city = ". db_input($city).", address_state = ". db_input($state).", address_zip = '". db_input($zip)."' " //we put single quotes around zip so it will save leading 0
                . "WHERE user_id = ". db_input($user_id)." "
                . "AND season_id = ". db_input($season_id).";";
                return db_query($sql1);
    }
    
    function doUpdateSeasonProfile_Student($user_id, $season_id, $grade, $student_id){
        $sql1 = "UPDATE ".TABLE_PROFILE_STUDENT." TPS "
                . "JOIN ".TABLE_PROFILE." TP ON TPS.user_profile_id = TP.id "
                . "SET TPS.grade_level = ". db_input($grade).", TPS.msd_student_id = ". db_input($student_id)." "
                . "WHERE TP.user_id = ". db_input($user_id)." "
                . "AND TP.season_id = ". db_input($season_id).";";
                return db_query($sql1);
    }
    
    function doUpdateSeasonProfile_Adult($user_id, $season_id, $employer, $profession){
        $sql1 = "UPDATE ".TABLE_PROFILE_MENTORPARENT." TPA "
                . "JOIN ".TABLE_PROFILE." TP ON TPA.user_profile_id = TP.id "
                . "SET TPA.employer = ". db_input($employer).", TPA.profession = ". db_input($profession)." "
                . "WHERE TP.user_id = ". db_input($user_id)." "
                . "AND TP.season_id = ". db_input($season_id).";";
                return db_query($sql1);
    }
    
    function doUpdateSeasonProfile_Alumni($user_id, $season_id, $grad_year){
        $sql1 = "UPDATE ".TABLE_PROFILE_ALUMNI." TPAL "
                . "JOIN ".TABLE_PROFILE." TP ON TPAL.user_profile_id = TP.id "
                . "SET TPAL.graduation_year = ". db_input($grad_year)." "
                . "WHERE TP.user_id = ". db_input($user_id)." "
                . "AND TP.season_id = ". db_input($season_id).";";
                return db_query($sql1);
    }
    
    function doAddRelationship($user_id, $relation_type, $relation_to){
        $sql1 = "INSERT INTO ".TABLE_RELATIONSHIPS." (`user_id_from`, `relationship`, `user_id_to`) "
                . "VALUES (". db_input($user_id).", ". db_input($relation_type).", ". db_input($relation_to).")";
            return db_query($sql1);
    }
    
    function doConfirmRelationship($user_id_from, $user_id_to){
        $sql1 = "UPDATE ".TABLE_RELATIONSHIPS." "
                . "SET accepted = 1 "
                . "WHERE user_id_from = ". db_input($user_id_from)." "
                . "AND user_id_to = ". db_input($user_id_to)." "
                . "AND is_deleted = 0;";
        return db_query($sql1);
    }
    
    function doDeleteRelationship($user_id_from, $user_id_to){
        $sql1 = "UPDATE ".TABLE_RELATIONSHIPS." "
                . "SET is_deleted = 1 "
                . "WHERE user_id_from = ". db_input($user_id_from)." "
                . "AND user_id_to = ". db_input($user_id_to).";";
        return db_query($sql1);
    }

    function doAddEmergencyContact_ById($user_id, $season_id, $emer_contact_id){
        $sql1 = "UPDATE ".TABLE_PROFILE." "
                . "SET emergency_contact_user_id = ".db_input($emer_contact_id).", emergency_contact_id = NULL "
                . "WHERE user_id = ". db_input($user_id)." "
                . "AND season_id = ". db_input($season_id).";";
        return db_query($sql1);
    }
    
    function doAddEmergencyContact_Manual($user_id, $season_id, $fname, $lname, $relation, $phone){
        $sql1 = "INSERT INTO `".TABLE_EMER_CONTACTS."`(`first_name`, `last_name`, `relationship`, `phone`) "
                . "VALUES (".db_input($fname).",".db_input($lname).",".db_input($relation).",".db_input($phone).")";
        db_query($sql1);
        $insert_id = db_fetch_row(db_query("SELECT LAST_INSERT_ID();"))[0];
        if($insert_id > 0 && $insert_id != NULL){
            $sql2 = "UPDATE ".TABLE_PROFILE." "
                . "SET emergency_contact_id = ".db_input($insert_id).", emergency_contact_user_id = NULL "
                . "WHERE user_id = ". db_input($user_id)." "
                . "AND season_id = ". db_input($season_id).";";
            return db_query($sql2);
        }
        else{
            return 0;
        }
    }
}